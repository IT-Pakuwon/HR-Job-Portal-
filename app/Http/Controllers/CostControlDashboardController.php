<?php

namespace App\Http\Controllers;

use App\Models\Autonbr;
use App\Models\BudgetDetail;
use App\Models\StagingIfcaIcStkIssue;
use App\Models\StagingIfcaPoApprove;
use App\Models\TrIMBudget;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class CostControlDashboardController extends Controller
{
    protected ApprovalDashboardController $approvalController;

    public function __construct(
        ApprovalDashboardController $approvalController
    ) {
        $this->approvalController = $approvalController;
    }

    private function getAllowedCpny(): array
    {
        $user = auth()->user();
        $msUser = User::query()->where('username', optional($user)->username)->first();

        return collect(explode(',', (string) optional($msUser)->cpny_id))
            ->map(fn ($v) => strtoupper(trim($v)))
            ->filter()
            ->values()
            ->all();
    }

    public function summaryJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $allowedCpny = $this->getAllowedCpny();

        $pendingPo = StagingIfcaPoApprove::query()
            ->where('status', 'D')
            ->when(!empty($allowedCpny), fn ($q) => $q->whereIn('cpny_id', $allowedCpny))
            ->distinct('order_no')
            ->count('order_no');

        $pendingIssue = StagingIfcaIcStkIssue::query()
            ->where('status', 'D')
            ->when(!empty($allowedCpny), fn ($q) => $q->whereIn('cpny_id', $allowedCpny))
            ->distinct('issue_id')
            ->count('issue_id');

        $imBudget = TrIMBudget::query()
            ->where('status', 'P')
            ->when(!empty($allowedCpny), fn ($q) => $q->whereIn('cpny_id', $allowedCpny))
            ->count();

        $username = strtolower(trim($user->username));

        $waitingApproval = DB::connection('pgsql2')
            ->table('tr_approval')
            ->whereRaw(
                "(',' || lower(regexp_replace(coalesce(aprv_username,''), '\s+', '', 'g')) || ',') like ?",
                ['%,'.$username.',%']
            )
            ->where('status', 'P')
            ->whereNotNull('aprv_datebefore')
            ->count();

        $budget = BudgetDetail::query()
            ->select([
                'totalbudget',
                'totalbudget_add',
                'total_reserve',
                'total_used',
            ])
            ->where('status', 'C')
            ->when(!empty($allowedCpny), fn ($q) => $q->whereIn('cpny_id', $allowedCpny))
            ->get()
            ->sum(function ($row) {
                return
                    (float) ($row->totalbudget ?? 0)
                    + (float) ($row->totalbudget_add ?? 0)
                    - (float) ($row->total_reserve ?? 0)
                    - (float) ($row->total_used ?? 0);
            });

        return response()->json([
            'success' => true,
            'data' => [
                'waiting_approval' => $waitingApproval,
                'pending_po' => $pendingPo,
                'pending_issue' => $pendingIssue,
                'budget' => round($budget),
                'im_budget' => $imBudget,
            ],
        ]);
    }

    public function waitingApprovalJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return app(ApprovalDashboardController::class)
            ->waitingJson($request);
    }

    public function approvalHistoryJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return app(ApprovalDashboardController::class)
            ->approveJson($request);
    }

    public function pendingPoJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $data = StagingIfcaPoApprove::query()
            ->select([
                'cpny_id',
                'order_no',
                'order_date',
                'department_id',
                'user_peminta',
                'purchaser',
                'status',
            ])
            ->where('status', 'D')
            ->groupBy(
                'cpny_id',
                'order_no',
                'order_date',
                'department_id',
                'user_peminta',
                'purchaser',
                'status'
            )
            ->orderByDesc('order_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function pendingIssueJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $data = StagingIfcaIcStkIssue::query()
            ->select([
                'cpny_id',
                'issue_id',
                'issue_date',
                'department_id',
                'user_peminta',
                'keeper',
                'status',
            ])
            ->where('status', 'D')
            ->groupBy(
                'cpny_id',
                'issue_id',
                'issue_date',
                'department_id',
                'user_peminta',
                'keeper',
                'status'
            )
            ->orderByDesc('issue_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function budgetJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $msUser = User::query()
            ->where('username', $user->username)
            ->first();

        $allowedCpny = collect(explode(',', (string) optional($msUser)->cpny_id))
            ->map(fn ($v) => strtoupper(trim($v)))
            ->filter()
            ->values()
            ->all();

        $rows = BudgetDetail::query()
            ->select([
                'cpny_id',
                'business_unit_id',
                'department_fin_id',
                'account_id',
                'activity_descr',
                'totalbudget',
                'totalbudget_add',
                'total_reserve',
                'total_used',
            ])
            ->where('status', 'C')
            ->when(!empty($allowedCpny), function ($q) use ($allowedCpny) {
                $q->whereIn('cpny_id', $allowedCpny);
            })
            ->orderBy('cpny_id')
            ->orderBy('business_unit_id')
            ->limit(500)
            ->get()
            ->map(function ($row) {
                $remaining =
                    (float) ($row->totalbudget ?? 0)
                    + (float) ($row->totalbudget_add ?? 0)
                    - (float) ($row->total_reserve ?? 0)
                    - (float) ($row->total_used ?? 0);

                return [
                    'cpny_id' => $row->cpny_id,
                    'business_unit_id' => $row->business_unit_id,
                    'department_fin_id' => $row->department_fin_id,
                    'account_id' => $row->account_id,
                    'activity_descr' => $row->activity_descr,
                    'remaining_budget' => $remaining,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    public function imBudgetJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $data = TrIMBudget::query()
            ->select([
                'id',
                'imbudgetid',
                'imbudgetdate',
                'cpny_id',
                'department_id',
                'user_peminta',
                'csid',
                'total_budget_requested',
                'status',
            ])
            ->where('status', 'P')
            ->orderByDesc('imbudgetdate')
            ->get()
            ->map(function ($row) {
                return [
                    'eid' => Hashids::encode($row->id),
                    'imbudgetid' => $row->imbudgetid,
                    'imbudgetdate' => optional($row->imbudgetdate)->format('d M Y'),
                    'cpny_id' => $row->cpny_id,
                    'department_id' => $row->department_id,
                    'user_peminta' => $row->user_peminta,
                    'csid' => $row->csid,
                    'total_budget_requested' => $row->total_budget_requested,
                    'status' => $row->status,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

     public function approvalDocTypes(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $data = collect(
            $this->approvalController
                ->waitingJson($request)
                ->getData(true)['data'] ?? []
        )->merge(
            collect(
                $this->approvalController
                    ->approveJson($request)
                    ->getData(true)['data'] ?? []
            )
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
