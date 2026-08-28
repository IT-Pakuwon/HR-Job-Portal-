<?php

namespace App\Http\Controllers;

use App\Models\Autonbr;
use App\Models\MsVplProductDetail;
use App\Models\TrxVplSettlement;
use App\Models\TrxVplUsage;
use App\Models\Usercpny;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

/**
 * Shared logic for the VPL (Voucher/Product) dashboards — Collection, Promotion,
 * Loyalty — identical everywhere except which product_type(s)/warehouse they scope to.
 */
abstract class VplDashboardController extends Controller
{
    // Settlement statuses that mean a Usage doc is still "spoken for" — mirrors VplSettlementController::jobListBaseQuery().
    private const ACTIVE_SETTLEMENT_STATUSES = ['P', 'D', 'C'];

    // Outer alert band for the Expired list (days remaining before expiry).
    private const EXPIRY_WINDOW_DAYS = 60;

    // Inner alert band — batches inside this many days are labelled "H-30" instead of "H-60".
    private const EXPIRY_URGENT_DAYS = 30;

    protected ApprovalDashboardController $approvalController;

    public function __construct(ApprovalDashboardController $approvalController)
    {
        $this->approvalController = $approvalController;
    }

    /** ms_vpl_product.product_type values the Expired list covers — ['V'], ['P'], or ['V','P'] for Loyalty. */
    abstract protected function expiryProductTypes(): array;

    /** The stock warehouse the Expired list is scoped to (mirrors VplReportController::WHS_COLLECTION/WHS_PROMOTION). */
    abstract protected function expiryWarehouseId(): string;

    /** Extra summaryJson() stats a subclass wants included — e.g. Collection/Promotion add 'waiting_settlement'. */
    protected function additionalSummaryStats(Request $request): array
    {
        return [];
    }

    private function getAllowedCpny(): array
    {
        $user = Auth::user();

        return Usercpny::where('username', optional($user)->username)
            ->where('status', 'A')
            ->pluck('cpny_id')
            ->toArray();
    }

    public function summaryJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $approvalSummary = $this->approvalController
            ->summaryJson($request)
            ->getData(true)['data'] ?? [];

        return response()->json([
            'data' => array_merge([
                'waiting_approval' => $approvalSummary['waiting'] ?? 0,
                'approved_today' => $approvalSummary['approved_today'] ?? 0,
                'expired' => $this->expiredQuery()->count(),
            ], $this->additionalSummaryStats($request)),
        ]);
    }

    public function waitingApprovalJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return $this->approvalController->waitingJson($request);
    }

    public function approvalHistoryJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return $this->approvalController->approveJson($request);
    }

    private function expiredQuery()
    {
        $allowedCpny = $this->getAllowedCpny();

        return MsVplProductDetail::query()
            ->join('ms_vpl_product', 'ms_vpl_product.product_id', '=', 'ms_vpl_product_detail.product_id')
            ->whereIn('ms_vpl_product.product_type', $this->expiryProductTypes())
            ->where('ms_vpl_product_detail.whs_id', $this->expiryWarehouseId())
            ->whereNotNull('ms_vpl_product_detail.expired_date')
            ->whereRaw('(ms_vpl_product_detail.qty_available - COALESCE(ms_vpl_product_detail.qty_reserved, 0)) > 0')
            ->when(!empty($allowedCpny), fn ($q) => $q->whereIn('ms_vpl_product_detail.cpnyid', $allowedCpny))
            ->whereRaw(
                '(ms_vpl_product_detail.expired_date::date - CURRENT_DATE) BETWEEN 0 AND ?',
                [self::EXPIRY_WINDOW_DAYS]
            );
    }

    // Expired/expiring batches (H-60 / H-30) for this dashboard's product_type(s)/warehouse.
    public function expiredJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $data = $this->expiredQuery()
            ->select(
                'ms_vpl_product_detail.id',
                'ms_vpl_product_detail.product_id',
                'ms_vpl_product_detail.expired_date',
                'ms_vpl_product_detail.cpnyid',
                'ms_vpl_product_detail.whs_id',
                DB::raw('(ms_vpl_product_detail.qty_available - COALESCE(ms_vpl_product_detail.qty_reserved, 0)) AS qty_pickable'),
                DB::raw('(ms_vpl_product_detail.expired_date::date - CURRENT_DATE) AS days_left'),
                'ms_vpl_product.product_name',
                'ms_vpl_product.product_type'
            )
            ->orderBy('days_left')
            ->get()
            ->map(function ($row) {
                $daysLeft = (int) $row->days_left;

                return [
                    'id' => $row->id,
                    'product_id' => $row->product_id,
                    'product_name' => $row->product_name,
                    'product_type' => $row->product_type,
                    'product_type_label' => strtoupper($row->product_type) === 'V' ? 'Voucher' : 'Product',
                    'expired_date' => optional($row->expired_date)->format('Y-m-d') ?? $row->expired_date,
                    'cpnyid' => $row->cpnyid,
                    'whs_id' => $row->whs_id,
                    'qty_pickable' => $row->qty_pickable,
                    'days_left' => $daysLeft,
                    'bucket' => $daysLeft <= self::EXPIRY_URGENT_DAYS ? 'H-30' : 'H-60',
                ];
            })
            ->values();

        return response()->json(['data' => $data]);
    }

    // Completed Usage docs (this dashboard's product type(s)) that still have no active settlement,
    // across every department in the user's allowed companies — unlike VplSettlementController's
    // "Job List" (a per-department action queue), this dashboard tab is company-wide visibility,
    // so it isn't restricted to the user's own department(s), and Admin/DIRECTORACCESS aren't
    // specially excluded either.
    protected function waitingSettlementQuery()
    {
        $user = Auth::user();

        if (!$user) {
            return TrxVplUsage::whereRaw('1 = 0');
        }

        $allowedCpny = $this->getAllowedCpny();
        $settledIds = TrxVplSettlement::whereIn('status', self::ACTIVE_SETTLEMENT_STATUSES)->pluck('usage_id');

        return TrxVplUsage::where('usagetype', 'Usage')
            ->where('status', 'C')
            ->where('department', '<>', 'CUSTOMERSERVICE')
            ->whereIn('vp_type', $this->expiryProductTypes())
            ->whereNotIn('usage_id', $settledIds)
            ->when(!empty($allowedCpny), fn ($q) => $q->whereIn('cpnyid', $allowedCpny));
    }

    public function waitingSettlementJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $data = $this->waitingSettlementQuery()
            ->select('id', 'usage_id', 'usage_date', 'event_date', 'cpnyid', 'department', 'user_peminta', 'usage_remark')
            ->orderByDesc('usage_date')
            ->get()
            ->map(function ($row) {
                return [
                    'eid' => Hashids::encode($row->id),
                    'usage_id' => $row->usage_id,
                    'usage_date' => optional($row->usage_date)->format('Y-m-d') ?? $row->usage_date,
                    'event_date' => optional($row->event_date)->format('Y-m-d') ?? $row->event_date,
                    'cpnyid' => $row->cpnyid,
                    'department' => $row->department,
                    'user_peminta' => $row->user_peminta,
                    'usage_remark' => $row->usage_remark,
                    'url' => '/showusagevp',
                ];
            })
            ->values();

        return response()->json(['data' => $data]);
    }

    public function approvalDocTypes(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $data = collect(
            $this->approvalController->waitingJson($request)->getData(true)['data'] ?? []
        )->merge(
            collect($this->approvalController->approveJson($request)->getData(true)['data'] ?? [])
        );

        $docids = $data
            ->pluck('docid')
            ->map(function ($docid) {
                preg_match('/^[A-Z]+/', $docid, $match);

                return $match[0] ?? null;
            })
            ->filter()
            ->unique()
            ->values();

        $rows = Autonbr::query()
            ->select('doctype', 'doctype_descr')
            ->whereIn('doctype', $docids)
            ->orderBy('doctype')
            ->distinct()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }
}
