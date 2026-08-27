<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Http\Controllers\Traits\UploadsVplAttachment;
use App\Models\Attachment;
use App\Models\MsCategory;
use App\Models\MsVplProduct;
use App\Models\TrApproval;
use App\Models\TrMessage;
use App\Models\TrxVplSettlement;
use App\Models\TrxVplSettlementDetail;
use App\Models\TrxVplUsage;
use App\Models\TrxVplUsageDetail;
use App\Models\User;
use App\Models\Usercpny;
use App\Models\Userdept;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class VplSettlementController extends Controller
{
    use HasAutonbr;
    use UploadsVplAttachment;

    public const DOCTYPE = 'VPS';
    public const DOCTYPE_DSC = 'Voucher Product Settlement';

    // Statuses that mean "this usage doc is still spoken for" by a settlement.
    private const ACTIVE_SETTLEMENT_STATUSES = ['P', 'D', 'C'];

    // -------------------------------------------------------
    // INDEX — serves view page OR DataTable AJAX
    // -------------------------------------------------------
    public function index(Request $request, $eid = null)
    {
        $user = Auth::user();
        $multicpnyid = Usercpny::where('username', $user->username)->where('status', 'A')->pluck('cpny_id')->toArray();
        $multidept = Userdept::where('username', $user->username)->pluck('department_id')->toArray();

        // "Settlement All" — admin-only, system-wide view (ignores company/department
        // scoping) with its own Type/Status dropdown filters. Every other tab keeps
        // admin scoped to their own company/department just like any other user.
        $isAdmin = $user->isPrimaryAdmin();

        // DIRECTORACCESS sees every transaction unscoped everywhere, on every tab —
        // distinct from the admin-only "Settlement All" tab, which stays admin-exclusive.
        $hasFullScope = $user->hasFullDataScope();

        if ($request->ajax()) {
            $status = $request->input('status', 'ALL');
            $adminAll = $isAdmin && $status === 'ADMINALL';

            $base = TrxVplSettlement::query();
            if (!$adminAll && !$hasFullScope) {
                $base->whereIn('cpnyid', $multicpnyid)->whereIn('department', $multidept);
            }

            if ($adminAll) {
                if ($request->filled('filter_vp_type')) {
                    $base->where('vp_type', $request->filter_vp_type);
                }
                if ($request->filled('filter_doc_status') && $request->filter_doc_status !== 'ALL') {
                    $base->where('status', $request->filter_doc_status);
                }
            } elseif ($status !== 'ALL') {
                $base->where('status', $status);
            }

            $data = $base->orderByDesc('created_at')->get();

            $waitingStatus = $adminAll ? $request->input('filter_doc_status') : $status;

            if ($waitingStatus === 'P' && $data->isNotEmpty()) {
                $approverMap = TrApproval::whereIn('refnbr', $data->pluck('settlement_id'))
                    ->where('aprv_doctype', self::DOCTYPE)
                    ->where('status', 'P')
                    ->whereNotNull('aprv_datebefore')
                    ->pluck('aprv_name', 'refnbr');

                $data->each(fn ($row) => $row->waiting = $approverMap->get($row->settlement_id, ''));
            }

            return \DataTables::of($data)
                ->addColumn('status_badge', fn ($r) => $this->statusBadge($r->status))
                ->addColumn('settlement_date_fmt', fn ($r) => $r->settlement_date ? Carbon::parse($r->settlement_date)->format('Y-m-d') : '')
                ->addColumn('vp_type_label', fn ($r) => match (strtoupper($r->vp_type ?? '')) {
                    'V'     => 'Voucher',
                    'P'     => 'Product',
                    default => $r->vp_type ?? '',
                })
                ->addColumn('action', fn ($r) => '<button type="button" class="btn-view-settlement inline-flex w-36 justify-center rounded bg-gray-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-gray-700" data-id="'.$r->id.'">'.$r->settlement_id.'</button>'
                )
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        // Status count cards — scoped to the user's own company/department, admin
        // included; DIRECTORACCESS holders count across everything instead.
        $qCount = $hasFullScope
            ? TrxVplSettlement::query()
            : TrxVplSettlement::query()->whereIn('cpnyid', $multicpnyid)->whereIn('department', $multidept);
        $counts = [
            'progress' => (clone $qCount)->where('status', 'P')->count(),
            'completed' => (clone $qCount)->where('status', 'C')->count(),
            'rejected' => (clone $qCount)->where('status', 'R')->count(),
            'cancelled' => (clone $qCount)->where('status', 'X')->count(),
            'hold' => (clone $qCount)->where('status', 'D')->count(),
        ];

        // "Settlement All" card — system-wide total, admin-only.
        if ($isAdmin) {
            $counts['admin_all'] = TrxVplSettlement::count();
        }

        // "Job List" card — Completed Usage docs from non-CUSTOMERSERVICE departments
        // still waiting on someone to create their settlement. Empty for Admin/
        // DIRECTORACCESS — see jobListBaseQuery().
        $counts['joblist'] = $this->jobListBaseQuery($user)->count();

        $usercpny = Usercpny::where('username', $user->username)->get();
        $usercpny2 = Usercpny::where('username', $user->username)->first();
        $userdept = Userdept::where('username', $user->username)->get();
        $userdept2 = Userdept::where('username', $user->username)->first();

        $initialId = $eid ? (Hashids::decode($eid)[0] ?? null) : null;

        return view('pages.voucher_product.settlement', compact(
            'user', 'usercpny', 'usercpny2', 'userdept', 'userdept2', 'counts', 'initialId'
        ));
    }

    // -------------------------------------------------------
    // JOB LIST — Completed Usage docs (non-CUSTOMERSERVICE) with no settlement yet
    // -------------------------------------------------------
    /**
     * Usage docs still owing a settlement: Completed, created by a department other
     * than CUSTOMERSERVICE (CS settles differently and isn't part of this queue), and
     * not already claimed by an active settlement (P/D/C — same "spoken for" rule as
     * getSettleableUsageOptions()). Filtering to usagetype 'Usage' skips Return docs,
     * which always carry qty_usage = 0 and so never have anything settleable anyway.
     *
     * Job List is a per-department action queue, not a reporting view — unlike every
     * other tab, Admin and DIRECTORACCESS don't get a merged/system-wide version of
     * it, because it isn't "their" job to settle another department's vouchers. It
     * stays empty for those two roles; everyone else sees just their own company/
     * department, exactly like the regular status tabs.
     */
    private function jobListBaseQuery($user)
    {
        if ($user->isPrimaryAdmin() || $user->hasFullDataScope()) {
            return TrxVplUsage::whereRaw('1 = 0');
        }

        $multicpnyid = Usercpny::where('username', $user->username)->where('status', 'A')->pluck('cpny_id')->toArray();
        $multidept = Userdept::where('username', $user->username)->pluck('department_id')->toArray();
        $settledIds = TrxVplSettlement::whereIn('status', self::ACTIVE_SETTLEMENT_STATUSES)->pluck('usage_id');

        return TrxVplUsage::where('usagetype', 'Usage')
            ->where('status', 'C')
            ->where('department', '<>', 'CUSTOMERSERVICE')
            ->whereNotIn('usage_id', $settledIds)
            ->whereIn('cpnyid', $multicpnyid)
            ->whereIn('department', $multidept);
    }

    public function jobList(Request $request)
    {
        $user = Auth::user();

        $data = $this->jobListBaseQuery($user)
            ->orderByDesc('usage_date')
            ->get();

        return \DataTables::of($data)
            ->addColumn('usage_date_fmt', fn ($r) => $r->usage_date ? Carbon::parse($r->usage_date)->format('Y-m-d') : '')
            ->addColumn('vp_type_label', fn ($r) => match (strtoupper($r->vp_type ?? '')) {
                'V'     => 'Voucher',
                'P'     => 'Product',
                default => $r->vp_type ?? '',
            })
            ->addColumn('action', fn ($r) => '<button type="button" class="btn-create-from-job inline-flex items-center justify-center gap-1 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500"'
                .' data-usage-id="'.$r->usage_id.'" data-cpnyid="'.$r->cpnyid.'" data-department="'.$r->department.'" data-vp-type="'.$r->vp_type.'">'
                .'<i class="fa-solid fa-plus text-[10px]"></i> Settle</button>'
            )
            ->rawColumns(['action'])
            ->make(true);
    }

    // -------------------------------------------------------
    // STUB ALIASES — all list views redirect to index
    // -------------------------------------------------------
    public function waiting(Request $request)   { return $this->index($request); }
    public function completed(Request $request) { return $this->index($request); }
    public function rejected(Request $request)  { return $this->index($request); }
    public function all(Request $request)       { return $this->index($request); }
    public function add()                       { return $this->index(request()); }
    public function show(int $id)               { return $this->index(request()); }
    public function edit(int $id)               { return $this->index(request()); }

    // -------------------------------------------------------
    // SHOW DATA — JSON payload for the view modal
    // -------------------------------------------------------
    public function showData(int $id)
    {
        $user = Auth::user();
        $settlement = TrxVplSettlement::find($id);

        if (!$settlement) {
            return response()->json(['error' => 'Not found'], 404);
        }
        if (!$this->hasDepartmentAccess($user, $settlement->cpnyid, $settlement->department) && !$user->hasFullDataScope()) {
            return response()->json(['error' => 'You do not have access to view this document.'], 403);
        }

        $details = TrxVplSettlementDetail::join('ms_vpl_product', 'tr_vpl_settlement_detail.product_id', '=', 'ms_vpl_product.product_id')
            ->select('tr_vpl_settlement_detail.*', 'ms_vpl_product.product_name', 'ms_vpl_product.product_uom')
            ->where('settlement_id', $settlement->settlement_id)
            ->orderBy('linenbr')
            ->get();

        $approvals = TrApproval::where('refnbr', $settlement->settlement_id)
            ->where('aprv_doctype', self::DOCTYPE)
            ->where('status', '<>', 'X')
            ->orderByRaw('CAST(aprv_leveling AS numeric) ASC')
            ->get();

        $attachments = Attachment::where('docid', $settlement->settlement_id)->where('status', 'A')->get();
        $messages = TrMessage::where('refnbr', $settlement->settlement_id)->where('doctype', self::DOCTYPE)->orderBy('created_at')->get();

        $statusMap = ['R' => 'Rejected', 'C' => 'Completed', 'D' => 'Hold', 'X' => 'Cancelled', 'P' => 'On Progress'];
        $statusLabel = $statusMap[$settlement->status] ?? 'On Progress';
        $vpLabel = strtoupper($settlement->vp_type) === 'V' ? 'Voucher' : 'Product';

        $can_approve = $can_reject = $can_revise = false;
        if ($settlement->status === 'P') {
            $can_approve = TrApproval::where('refnbr', $settlement->settlement_id)
                ->where('aprv_doctype', self::DOCTYPE)
                ->where('status', 'P')
                ->whereNotNull('aprv_datebefore')
                ->where(function ($q) use ($user) {
                    $q->where('aprv_username', $user->username)
                      ->orWhere('aprv_username', 'like', '%'.$user->username.'%');
                })
                ->exists();
            $can_reject = $can_approve;
            $can_revise = $can_approve;
        }

        $anyApproved = TrApproval::where('refnbr', $settlement->settlement_id)
            ->where('aprv_doctype', self::DOCTYPE)
            ->where('status', 'A')
            ->exists();

        $can_edit = $settlement->status === 'D' && $settlement->created_user === $user->name;
        $can_cancel = $settlement->created_user === $user->name
            && ($settlement->status === 'D' || ($settlement->status === 'P' && !$anyApproved));

        return response()->json([
            'settlement' => $settlement,
            'hash' => Hashids::encode($settlement->id),
            'status_label' => $statusLabel,
            'vp_label' => $vpLabel,
            'details' => $details,
            'approvals' => $approvals->map(fn ($ap) => [
                'aprvid' => $ap->aprv_leveling,
                'name' => $ap->aprv_name,
                'aprvusername' => $ap->aprv_username,
                'aprvdatebefore' => $ap->aprv_datebefore,
                'aprvdateafter' => $ap->aprv_dateafter,
                'status' => $ap->status,
            ]),
            'attachments' => $attachments->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'attachfile' => $a->attachfile,
                'extention' => $a->extention,
                'created_user' => $a->created_user,
                'year' => $a->created_at?->year,
                'created_at' => $a->created_at?->format('Y-m-d H:i'),
            ]),
            'messages' => $messages->map(fn ($m) => [
                'name' => $m->name,
                'message' => $m->message,
                'created_at' => $m->created_at?->format('Y-m-d H:i'),
                'is_mine' => $m->name === $user->name,
            ]),
            'can_approve' => $can_approve,
            'can_reject' => $can_reject,
            'can_revise' => $can_revise,
            'can_edit' => $can_edit,
            'can_cancel' => $can_cancel,
            'current_user' => $user->name,
        ]);
    }

    // -------------------------------------------------------
    // STORE — create new Settlement document from a Completed Usage doc
    // -------------------------------------------------------
    public function store(Request $request)
    {
        $user = Auth::user();
        $dt = Carbon::now();

        if (!$request->filled('usage_id')) {
            return response()->json(['error' => 'Usage Doc is required.'], 422);
        }
        if (!$request->filled('settlement_remark')) {
            return response()->json(['error' => 'Remark is required.'], 422);
        }

        $usage = TrxVplUsage::where('usage_id', $request->usage_id)->first();
        if (!$usage || $usage->status !== 'C') {
            return response()->json(['error' => 'Referenced Usage document must be Completed.'], 422);
        }

        if (!$this->hasDepartmentAccess($user, $usage->cpnyid, $usage->department)) {
            return response()->json(['error' => 'You do not have access to settle documents for this company/department.'], 403);
        }

        if (TrxVplSettlement::where('usage_id', $usage->usage_id)->whereIn('status', self::ACTIVE_SETTLEMENT_STATUSES)->exists()) {
            return response()->json(['error' => 'This Usage document already has a settlement.'], 422);
        }

        $lines = $request->input('lines', []);
        if (empty($lines)) {
            return response()->json(['error' => 'Please provide at least one settlement line.'], 422);
        }

        // Validate every line against its source usage detail before touching anything.
        // A running per-line claim total catches duplicate usage_detail_id lines in one
        // request that would otherwise each pass independently and jointly over-claim.
        $usageDetails = TrxVplUsageDetail::where('usage_id', $usage->usage_id)->get()->keyBy('id');
        $claimTracker = [];
        foreach ($lines as $line) {
            $detail = $usageDetails->get($line['usage_detail_id'] ?? null);
            if (!$detail) {
                return response()->json(['error' => 'Invalid settlement line.'], 422);
            }
            $qtySettlement = (float) ($line['qty_settlement'] ?? 0);
            $remaining = $detail->qty_usage - ($detail->qty_return_usage ?? 0);
            $claimed = $claimTracker[$detail->id] ?? 0;
            if ($qtySettlement < 0 || ($qtySettlement + $claimed) > $remaining) {
                $productName = MsVplProduct::where('product_id', $detail->product_id)->value('product_name') ?? $detail->product_id;
                return response()->json(['error' => 'Qty Settlement for '.$productName.' must be between 0 and '.max(0, $remaining - $claimed).'.'], 422);
            }
            $claimTracker[$detail->id] = $claimed + $qtySettlement;
        }

        $conditionName = $this->resolveConditionName($usage->vp_type);
        $category = MsCategory::where('doctype', self::DOCTYPE)
            ->where('categoryid', 'condition')
            ->where('category_name', $conditionName)
            ->where('status', 'A')
            ->first();

        if (!$category) {
            return response()->json(['error' => 'Category condition "'.$conditionName.'" not found. Please contact IT!'], 422);
        }

        $autonbr = $this->nextAutonbr(
            self::DOCTYPE,
            $dt->year,
            (string) $dt->month,
            $user->username,
            self::DOCTYPE_DSC
        );
        $tglbln = substr((string) $dt->year, 2).sprintf('%02d', (int) $autonbr['month']);
        $docid = self::DOCTYPE.$tglbln.sprintf('%04d', $autonbr['next']);

        $approvalCondition = trim($category->groups ?? '') ?: $conditionName;
        $ctx = ['approval_conditions' => [$approvalCondition]];

        try {
            DB::connection('pgsql5')->transaction(function () use ($request, $user, $dt, $docid, $usage, $lines, $usageDetails, $ctx, &$settlement) {
                $settlement = TrxVplSettlement::create([
                    'settlement_id' => $docid,
                    'settlement_date' => $dt->format('Y-m-d'),
                    'usage_id' => $usage->usage_id,
                    'cpnyid' => $usage->cpnyid,
                    'department' => $usage->department,
                    'user_peminta' => $user->username,
                    'vp_type' => $usage->vp_type,
                    'settlement_remark' => $request->settlement_remark,
                    'status' => 'P',
                    'created_user' => $user->name,
                ]);

                foreach ($lines as $line) {
                    $detail = $usageDetails->get($line['usage_detail_id']);
                    $qtySettlement = (float) ($line['qty_settlement'] ?? 0);
                    $qtyRemain = $detail->qty_usage - ($detail->qty_return_usage ?? 0) - $qtySettlement;

                    TrxVplSettlementDetail::create([
                        'settlement_id' => $docid,
                        'usage_id' => $usage->usage_id,
                        'linenbr' => $detail->linenbr,
                        'product_id' => $detail->product_id,
                        'expired_date' => $detail->expired_date,
                        'whs_id' => $detail->whs_id,
                        'qty_usage' => $detail->qty_usage,
                        'qty_settlement' => $qtySettlement,
                        'qty_remain' => $qtyRemain,
                        'settlement_remark' => $line['settlement_remark'] ?? null,
                        'status' => 'P',
                        'created_user' => $user->name,
                    ]);

                    $detail->qty_settlement = $qtySettlement;
                    $detail->qty_remain = $qtyRemain;
                    $detail->updated_user = $user->name;
                    $detail->save();
                }

                $this->saveAttachments($request, $docid, $dt->year, $user);

                // Throws if no approval rule matches, rolling back the whole document
                // so it's never left without an approval chain.
                app(ApprovalController::class)->generateForDocument(
                    $docid,
                    self::DOCTYPE,
                    $usage->cpnyid,
                    $usage->department,
                    $user->username,
                    $ctx,
                    $dt
                );
            });
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        app(ApprovalController::class)->notifyFirstApprover(
            $docid,
            self::DOCTYPE,
            'P',
            self::DOCTYPE_DSC,
            route('settlementvp.show', $settlement->id),
            ['info' => $request->settlement_remark ?? '', 'createdby' => $user->name]
        );

        return response()->json(['success' => 'Settlement document saved successfully.']);
    }

    // -------------------------------------------------------
    // UPDATE — resubmit from Hold/Revise
    // -------------------------------------------------------
    public function update(Request $request, int $id)
    {
        $user = Auth::user();
        $dt = Carbon::now();
        $settlement = TrxVplSettlement::find($id);

        if (!$request->filled('settlement_remark')) {
            return response()->json(['error' => 'Remark is required.'], 422);
        }

        $lines = $request->input('lines', []);
        $settlementDetails = TrxVplSettlementDetail::where('settlement_id', $settlement->settlement_id)->get()->keyBy('id');
        $usageDetails = TrxVplUsageDetail::where('usage_id', $settlement->usage_id)->get()->keyBy('id');

        // Validate every line before touching anything — same two-phase pattern as
        // store() so a bad line further down the list can't leave earlier lines
        // already committed (there is no transaction wrapping the loop itself).
        $claimTracker = [];
        foreach ($lines as $line) {
            $sDetail = $settlementDetails->get($line['settlement_detail_id'] ?? null);
            if (!$sDetail) {
                return response()->json(['error' => 'Invalid settlement line.'], 422);
            }
            $uDetail = $usageDetails->firstWhere('linenbr', $sDetail->linenbr);
            if (!$uDetail) {
                continue;
            }
            $qtySettlement = (float) ($line['qty_settlement'] ?? 0);
            $remaining = $uDetail->qty_usage - ($uDetail->qty_return_usage ?? 0);
            $claimed = $claimTracker[$uDetail->id] ?? 0;
            if ($qtySettlement < 0 || ($qtySettlement + $claimed) > $remaining) {
                $productName = MsVplProduct::where('product_id', $uDetail->product_id)->value('product_name') ?? $uDetail->product_id;
                return response()->json(['error' => 'Qty Settlement for '.$productName.' must be between 0 and '.max(0, $remaining - $claimed).'.'], 422);
            }
            $claimTracker[$uDetail->id] = $claimed + $qtySettlement;
        }

        $conditionName = $this->resolveConditionName($settlement->vp_type);
        $category = MsCategory::where('doctype', self::DOCTYPE)
            ->where('categoryid', 'condition')
            ->where('category_name', $conditionName)
            ->where('status', 'A')
            ->first();

        if (!$category) {
            return response()->json(['error' => 'Category condition "'.$conditionName.'" not found. Please contact IT!'], 422);
        }

        $approvalCondition = trim($category->groups ?? '') ?: $conditionName;
        $ctx = ['approval_conditions' => [$approvalCondition]];

        try {
            DB::connection('pgsql5')->transaction(function () use ($request, $user, $dt, $settlement, $lines, $settlementDetails, $usageDetails, $ctx) {
                foreach ($lines as $line) {
                    $sDetail = $settlementDetails->get($line['settlement_detail_id'] ?? null);
                    $uDetail = $usageDetails->firstWhere('linenbr', $sDetail->linenbr);
                    if (!$uDetail) {
                        continue;
                    }
                    $qtySettlement = (float) ($line['qty_settlement'] ?? 0);
                    $remaining = $uDetail->qty_usage - ($uDetail->qty_return_usage ?? 0);
                    $qtyRemain = $remaining - $qtySettlement;

                    $sDetail->qty_settlement = $qtySettlement;
                    $sDetail->qty_remain = $qtyRemain;
                    $sDetail->settlement_remark = $line['settlement_remark'] ?? $sDetail->settlement_remark;
                    $sDetail->updated_user = $user->name;
                    $sDetail->save();

                    $uDetail->qty_settlement = $qtySettlement;
                    $uDetail->qty_remain = $qtyRemain;
                    $uDetail->updated_user = $user->name;
                    $uDetail->save();
                }

                $this->saveAttachments($request, $settlement->settlement_id, $dt->year, $user);

                // Throws if no approval rule matches, rolling back the detail changes
                // so the document isn't left without an approval chain.
                app(ApprovalController::class)->generateForDocument(
                    $settlement->settlement_id,
                    self::DOCTYPE,
                    $settlement->cpnyid,
                    $settlement->department,
                    $user->username,
                    $ctx,
                    $dt
                );

                $settlement->settlement_remark = $request->settlement_remark ?? $settlement->settlement_remark;
                $settlement->status = 'P';
                $settlement->updated_user = $user->name;
                $settlement->updated_at = $dt->toDateTimeString();
                $settlement->save();
            });
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        app(ApprovalController::class)->notifyFirstApprover(
            $settlement->settlement_id,
            self::DOCTYPE,
            'P',
            self::DOCTYPE_DSC,
            route('settlementvp.show', $id),
            ['info' => $settlement->settlement_remark ?? '', 'createdby' => $user->name]
        );

        return response()->json(['success' => 'Settlement document resubmitted successfully.']);
    }

    // -------------------------------------------------------
    // APPROVE
    // -------------------------------------------------------
    public function approve(int $id)
    {
        $user = Auth::user();
        $settlement = TrxVplSettlement::find($id);

        $approvalCtl = app(ApprovalController::class);

        $result = $approvalCtl->approveStep(
            $settlement->settlement_id,
            self::DOCTYPE,
            $user->username,
            $user->name,
            function ($refnbr, $now) use ($settlement, $user) {
                // Stock, ledger, and product_bal are computed entirely by
                // sp_process_vpl, not this code. Wrapped in its own transaction so a
                // failure inside the procedure can't leave the header marked Completed
                // with no corresponding ledger/balance effect.
                DB::connection('pgsql5')->transaction(function () use ($settlement, $user, $now) {
                    $settlement->status = 'C';
                    $settlement->completed_user = $user->username;
                    $settlement->completed_at = $now;
                    $settlement->save();
                    DB::connection('pgsql5')->statement(
                        'CALL sp_process_vpl(?, ?, ?, ?, ?)',
                        ['VPS', $settlement->settlement_id, $settlement->cpnyid, 'Submit', $user->username]
                    );
                });
            },
            function ($next, $now) use ($settlement, $id) {
                app(ApprovalController::class)->notifyFirstApprover(
                    $settlement->settlement_id,
                    self::DOCTYPE,
                    'P',
                    self::DOCTYPE_DSC,
                    route('settlementvp.show', $id),
                    ['info' => $settlement->settlement_remark ?? '', 'createdby' => $settlement->created_user]
                );
            },
            function ($refnbr, $now) use ($settlement, $id) {
                // Notify requester the document is fully approved
                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $settlement->settlement_id,
                    self::DOCTYPE_DSC,
                    'C',
                    $settlement->user_peminta,
                    route('settlementvp.show', $id),
                    ['cpnyid' => $settlement->cpnyid, 'deptname' => $settlement->department]
                );
            }
        );

        if (!$result['ok']) {
            return response()->json(['error' => $result['message']], 403);
        }

        return response()->json(['success' => 'Document approved.']);
    }

    // -------------------------------------------------------
    // REJECT
    // -------------------------------------------------------
    public function reject(Request $request, int $id)
    {
        if (empty($request->message)) {
            return response()->json(['error' => 'Reason is required.'], 422);
        }

        $user = Auth::user();
        $settlement = TrxVplSettlement::find($id);

        $approvalCtl = app(ApprovalController::class);

        $result = $approvalCtl->rejectStep(
            $settlement->settlement_id,
            self::DOCTYPE,
            $user->username,
            $user->name,
            function ($refnbr, $now) use ($settlement, $request, $user, $id) {
                DB::connection('pgsql5')->transaction(function () use ($settlement, $user) {
                    $settlement->status = 'R';
                    $settlement->save();
                    $this->rollbackUsageDetailSettlement($settlement, $user);
                });
                $this->saveMessage($settlement, $request->message, $user);
                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $settlement->settlement_id,
                    self::DOCTYPE_DSC,
                    'R',
                    $settlement->user_peminta,
                    route('settlementvp.show', $id),
                    ['info' => $request->message, 'cpnyid' => $settlement->cpnyid, 'deptname' => $settlement->department]
                );
            }
        );

        if (!$result['ok']) {
            return response()->json(['error' => $result['message']], 403);
        }

        return response()->json(['success' => 'Document rejected.']);
    }

    // -------------------------------------------------------
    // REVISE
    // -------------------------------------------------------
    public function revise(Request $request, int $id)
    {
        if (empty($request->message)) {
            return response()->json(['error' => 'Reason is required.'], 422);
        }

        $user = Auth::user();
        $settlement = TrxVplSettlement::find($id);

        $approvalCtl = app(ApprovalController::class);

        $result = $approvalCtl->reviseStep(
            $settlement->settlement_id,
            self::DOCTYPE,
            $user->username,
            $user->name,
            function ($refnbr, $now) use ($settlement, $request, $user, $id) {
                DB::connection('pgsql5')->transaction(function () use ($settlement, $user, $now) {
                    $settlement->status = 'D';
                    $settlement->updated_user = $user->name;
                    $settlement->updated_at = $now;
                    $settlement->save();
                    $this->rollbackUsageDetailSettlement($settlement, $user);
                });
                $this->saveMessage($settlement, $request->message, $user);
                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $settlement->settlement_id,
                    self::DOCTYPE_DSC,
                    'D',
                    $settlement->user_peminta,
                    route('settlementvp.show', $id),
                    ['info' => $request->message.' (Silahkan revisi dokumen ini)', 'cpnyid' => $settlement->cpnyid, 'deptname' => $settlement->department]
                );
            }
        );

        if (!$result['ok']) {
            return response()->json(['error' => $result['message']], 403);
        }

        return response()->json(['success' => 'Document sent for revision.']);
    }

    // -------------------------------------------------------
    // CANCEL
    // -------------------------------------------------------
    public function cancel(int $id)
    {
        $user = Auth::user();
        $settlement = TrxVplSettlement::find($id);

        if (!$settlement) {
            return response()->json(['error' => 'Not found.'], 404);
        }

        if (!$this->canCancel($settlement, $user)) {
            return response()->json(['error' => 'You are not allowed to cancel this document.'], 403);
        }

        DB::connection('pgsql5')->transaction(function () use ($settlement, $user) {
            $this->rollbackUsageDetailSettlement($settlement, $user);

            $settlement->status = 'X';
            $settlement->updated_user = $user->name;
            $settlement->save();
        });

        TrApproval::where('refnbr', $settlement->settlement_id)
            ->where('aprv_doctype', self::DOCTYPE)
            ->where('status', 'P')
            ->update(['status' => 'X', 'aprv_datebefore' => null]);

        return response()->json(['success' => 'Document cancelled.']);
    }

    // -------------------------------------------------------
    // SEND MESSAGE
    // -------------------------------------------------------
    public function sendMessage(Request $request, int $id)
    {
        $user = Auth::user();
        $settlement = TrxVplSettlement::find($id);

        $this->saveMessage($settlement, $request->message, $user);

        return response()->json(['success' => 'Message sent.']);
    }

    // -------------------------------------------------------
    // DELETE ATTACHMENT
    // -------------------------------------------------------
    public function deleteAttachment(Request $request)
    {
        $attach = Attachment::find($request->detail_id);
        if (!$attach) {
            return response()->json(['error' => 'Not found.'], 404);
        }
        $attach->delete();

        return response()->json(['success' => 'Attachment deleted.']);
    }

    // -------------------------------------------------------
    // AJAX HELPERS
    // -------------------------------------------------------
    /**
     * Completed Usage docs, scoped to company/department/vp_type, that don't
     * already have an active (non-rejected/cancelled) settlement. Restricted to
     * usagetype 'Usage' — a Return doc's lines always carry qty_usage = 0, so it
     * never has anything settleable and would otherwise show up as a dead-end pick
     * (same restriction Job List's eligibility query already applies).
     */
    public function getSettleableUsageOptions(Request $request)
    {
        $user = Auth::user();
        if (!$this->hasDepartmentAccess($user, $request->cpnyid, $request->department)) {
            return response()->json([]);
        }

        $settledIds = TrxVplSettlement::whereIn('status', self::ACTIVE_SETTLEMENT_STATUSES)->pluck('usage_id');

        $refs = TrxVplUsage::where('usagetype', 'Usage')
            ->where('status', 'C')
            ->where('cpnyid', $request->cpnyid)
            ->where('department', $request->department)
            ->where('vp_type', strtoupper($request->vp_type))
            ->whereNotIn('usage_id', $settledIds)
            ->orderByDesc('usage_date')
            ->pluck('usage_id');

        return response()->json($refs);
    }

    /**
     * Detail lines of the selected Usage doc, with the max settleable qty
     * (qty_usage - qty_return_usage) for the create form to enforce.
     */
    public function getUsageLinesForSettlement(Request $request)
    {
        $user = Auth::user();
        $usage = TrxVplUsage::where('usage_id', $request->usage_id)->first();
        if (!$usage || !$this->hasDepartmentAccess($user, $usage->cpnyid, $usage->department)) {
            return response()->json([]);
        }

        $lines = TrxVplUsageDetail::join('ms_vpl_product', 'tr_vpl_usage_detail.product_id', '=', 'ms_vpl_product.product_id')
            ->select(
                'tr_vpl_usage_detail.*',
                'ms_vpl_product.product_name',
                DB::raw('(tr_vpl_usage_detail.qty_usage - COALESCE(tr_vpl_usage_detail.qty_return_usage, 0)) AS remaining')
            )
            ->where('tr_vpl_usage_detail.usage_id', $request->usage_id)
            ->orderBy('tr_vpl_usage_detail.linenbr')
            ->get();

        return response()->json($lines);
    }

    // -------------------------------------------------------
    // PRIVATE HELPERS
    // -------------------------------------------------------
    /**
     * Settlement eligibility follows the Usage doc's own company/department —
     * anyone with access to the department that created the Usage (not just
     * the literal creator) may settle it, mirroring index()'s visibility scope.
     */
    private function hasDepartmentAccess($user, ?string $cpnyid, ?string $department): bool
    {
        if ($user->isPrimaryAdmin()) {
            return true;
        }

        $hasCpny = Usercpny::where('username', $user->username)->where('status', 'A')->where('cpny_id', $cpnyid)->exists();
        $hasDept = Userdept::where('username', $user->username)->where('department_id', $department)->exists();

        return $hasCpny && $hasDept;
    }

    /**
     * Cancel: creator only, and only while still Hold (D) or On Progress with
     * nothing yet approved (P and no 'A' rows) — mirrors the can_cancel flag
     * showData() sends the UI, re-checked here since the client can't be trusted.
     */
    private function canCancel(TrxVplSettlement $settlement, $user): bool
    {
        $anyApproved = TrApproval::where('refnbr', $settlement->settlement_id)
            ->where('aprv_doctype', self::DOCTYPE)
            ->where('status', 'A')
            ->exists();

        return $settlement->created_user === $user->name
            && ($settlement->status === 'D' || ($settlement->status === 'P' && !$anyApproved));
    }

    private function statusBadge(string $status): string
    {
        return match ($status) {
            'P' => '<span class="w-32 bg-orange-200/60 text-orange-800 dark:bg-orange-300/40 dark:text-orange-900 pointer-events-none border border-orange-600/40 font-semibold px-4 py-2 text-center rounded">On Progress</span>',
            'C' => '<span class="w-32 bg-green-200/60 text-green-800 dark:bg-green-300/40 dark:text-green-900 pointer-events-none border border-green-600/40 font-semibold px-4 py-2 text-center rounded">Completed</span>',
            'R' => '<span class="w-32 bg-red-200/60 text-red-800 dark:bg-red-300/40 dark:text-red-900 pointer-events-none border border-red-600/40 font-semibold px-4 py-2 text-center rounded">Rejected</span>',
            'X' => '<span class="w-32 bg-red-200/60 text-red-800 dark:bg-red-300/40 dark:text-red-900 pointer-events-none border border-red-600/40 font-semibold px-4 py-2 text-center rounded">Cancel</span>',
            default => '<span class="w-32 bg-amber-200/60 text-amber-800 dark:bg-amber-300/40 dark:text-amber-900 pointer-events-none border border-amber-600/40 font-semibold px-4 py-2 text-center rounded">Revise</span>',
        };
    }

    /**
     * Derive the ms_category condition name from vp_type.
     * e.g. 'P' -> 'Settlement Product', 'V' -> 'Settlement Voucher'
     */
    private function resolveConditionName(string $vp_type): string
    {
        $vpLabel = strtoupper($vp_type) === 'V' ? 'Voucher' : 'Product';
        return 'Settlement '.$vpLabel;
    }

    private function saveAttachments(Request $request, string $docid, int $year, $user): void
    {
        $this->saveVplAttachments($request, $docid, 'att-vpl/vps-attachment', $year, $user);
    }

    private function saveMessage(TrxVplSettlement $settlement, string $message, $user): void
    {
        TrMessage::create([
            'refnbr' => $settlement->settlement_id,
            'doctype' => self::DOCTYPE,
            'username' => $user->username,
            'name' => $user->name,
            'message' => $message,
            'created_by' => $user->name,
        ]);
    }

    /**
     * Undo this settlement's effect on tr_vpl_usage_detail (Reject/Revise/Cancel).
     * Since only one settlement can ever be active per usage doc, resetting to
     * "unsettled" is safe — there's no other settlement's contribution to preserve.
     */
    private function rollbackUsageDetailSettlement(TrxVplSettlement $settlement, $user): void
    {
        $lines = TrxVplSettlementDetail::where('settlement_id', $settlement->settlement_id)->get();

        foreach ($lines as $line) {
            TrxVplUsageDetail::where('usage_id', $settlement->usage_id)
                ->where('linenbr', $line->linenbr)
                ->update([
                    'qty_settlement' => 0,
                    'qty_remain' => null,
                    'updated_user' => $user->name,
                ]);
        }
    }

}
