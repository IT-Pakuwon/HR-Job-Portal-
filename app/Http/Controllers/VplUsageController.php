<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\Attachment;
use App\Models\MsCategory;
use App\Models\MsVplProduct;
use App\Models\MsVplProductDetail;
use App\Models\MsVplWarehouseDept;
use App\Models\TrApproval;
use App\Models\TrMessage;
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

class VplUsageController extends Controller
{
    use HasAutonbr;

    public const DOCTYPE = 'VPU';
    public const DOCTYPE_DSC = 'Voucher Product Usage';

    // -------------------------------------------------------
    // INDEX — serves view page OR DataTable AJAX
    // -------------------------------------------------------
    public function index(Request $request, $eid = null)
    {
        $user = Auth::user();
        $multicpnyid = Usercpny::where('username', $user->username)->where('status', 'A')->pluck('cpny_id')->toArray();
        $multidept = Userdept::where('username', $user->username)->pluck('department_id')->toArray();

        $isVpAccess = $user->hasRole('VPACCESS');

        if ($request->ajax()) {
            $status = $request->input('status', 'ALL');

            $base = TrxVplUsage::query();
            if ($user->role !== 'admin' && !$isVpAccess) {
                $base->whereIn('cpnyid', $multicpnyid)->whereIn('department', $multidept);
            }
            if ($status !== 'ALL') {
                $base->where('status', $status);
            }
            $data = $base->orderByDesc('created_at')->get();

            if ($status === 'P' && $data->isNotEmpty()) {
                $approverMap = TrApproval::whereIn('refnbr', $data->pluck('usage_id'))
                    ->where('aprv_doctype', self::DOCTYPE)
                    ->where('status', 'P')
                    ->whereNotNull('aprv_datebefore')
                    ->pluck('aprv_name', 'refnbr');

                $data->each(fn ($row) => $row->waiting = $approverMap->get($row->usage_id, ''));
            }

            return \DataTables::of($data)
                ->addColumn('status_badge', fn ($r) => $this->statusBadge($r->status))
                ->addColumn('usage_date_fmt', fn ($r) => $r->usage_date ? Carbon::parse($r->usage_date)->format('Y-m-d') : '')
                ->addColumn('vp_type_label', fn ($r) => match (strtoupper($r->vp_type ?? '')) {
                    'V'     => 'Voucher',
                    'P'     => 'Product',
                    default => $r->vp_type ?? '',
                })
                ->addColumn('usagetype_label', fn ($r) => match ($r->usagetype) {
                    'Usage' => 'Usage',
                    'Return' => 'Return',
                    default => '',
                })
                ->addColumn('action', fn ($r) => '<button type="button" class="btn-view-usage inline-flex w-36 justify-center rounded bg-gray-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-gray-700" data-id="'.$r->id.'">'.$r->usage_id.'</button>'
                )
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        // Status count cards
        $qCount = TrxVplUsage::query();
        if ($user->role !== 'admin' && !$isVpAccess) {
            $qCount->whereIn('cpnyid', $multicpnyid)->whereIn('department', $multidept);
        }
        $counts = [
            'all' => (clone $qCount)->count(),
            'progress' => (clone $qCount)->where('status', 'P')->count(),
            'completed' => (clone $qCount)->where('status', 'C')->count(),
            'rejected' => (clone $qCount)->where('status', 'R')->count(),
            'cancelled' => (clone $qCount)->where('status', 'X')->count(),
            'hold' => (clone $qCount)->where('status', 'D')->count(),
        ];

        $usercpny = Usercpny::where('username', $user->username)->get();
        $usercpny2 = Usercpny::where('username', $user->username)->first();
        $userdept = Userdept::where('username', $user->username)->get();
        $userdept2 = Userdept::where('username', $user->username)->first();

        $initialId = $eid ? (Hashids::decode($eid)[0] ?? null) : null;

        return view('pages.voucher_product.usage', compact(
            'user', 'usercpny', 'usercpny2', 'userdept', 'userdept2', 'counts', 'initialId'
        ));
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
        $usage = TrxVplUsage::find($id);

        if (!$usage) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $details = TrxVplUsageDetail::join('ms_vpl_product', 'tr_vpl_usage_detail.product_id', '=', 'ms_vpl_product.product_id')
            ->select('tr_vpl_usage_detail.*', 'ms_vpl_product.product_name', 'ms_vpl_product.product_uom')
            ->where('usage_id', $usage->usage_id)
            ->orderBy('linenbr')
            ->get();

        $approvals = TrApproval::where('refnbr', $usage->usage_id)
            ->where('aprv_doctype', self::DOCTYPE)
            ->where('status', '<>', 'X')
            ->orderByRaw('CAST(aprv_leveling AS numeric) ASC')
            ->get();

        $attachments = Attachment::where('docid', $usage->usage_id)->where('status', 'A')->get();
        $messages = TrMessage::where('refnbr', $usage->usage_id)->where('doctype', self::DOCTYPE)->orderBy('created_at')->get();

        $statusMap = ['R' => 'Rejected', 'C' => 'Completed', 'D' => 'Hold', 'X' => 'Cancelled', 'P' => 'On Progress'];
        $statusLabel = $statusMap[$usage->status] ?? 'On Progress';
        $vpLabel = strtoupper($usage->vp_type) === 'V' ? 'Voucher' : 'Product';
        $usagetypeLabel = $usage->usagetype === 'Return' ? 'Return' : 'Usage';

        $can_approve = $can_reject = $can_revise = false;
        if ($usage->status === 'P') {
            $can_approve = TrApproval::where('refnbr', $usage->usage_id)
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

        $anyApproved = TrApproval::where('refnbr', $usage->usage_id)
            ->where('aprv_doctype', self::DOCTYPE)
            ->where('status', 'A')
            ->exists();

        $can_edit = $usage->status === 'D' && $usage->created_user === $user->name;
        $can_cancel = $usage->created_user === $user->name
            && ($usage->status === 'D' || ($usage->status === 'P' && !$anyApproved));

        return response()->json([
            'usage' => $usage,
            'hash' => Hashids::encode($usage->id),
            'status_label' => $statusLabel,
            'vp_label' => $vpLabel,
            'usagetype_label' => $usagetypeLabel,
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
    // STORE — create new Usage / Return Usage document
    // -------------------------------------------------------
    public function store(Request $request)
    {
        $user = Auth::user();
        $dt = Carbon::now();
        $vp_type = strtoupper($request->vp_type);       // 'V' | 'P'
        $usagetype = $request->usagetype;                 // 'Usage' | 'Return'

        if ($usagetype === 'Return' && empty($request->ref_usage_id)) {
            return response()->json(['error' => 'Reference Usage Doc is required for Return.'], 422);
        }

        // CUSTOMERSERVICE Usage docs may be backdated up to H-14 (e.g. logging usage
        // recorded late); every other department/type is always dated "today".
        $usageDate = $dt->copy();
        if ($request->department === 'CUSTOMERSERVICE' && $usagetype === 'Usage' && $request->filled('usage_date')) {
            $usageDate = Carbon::parse($request->usage_date)->startOfDay();
            $today = $dt->copy()->startOfDay();
            if ($usageDate->lt($today->copy()->subDays(14)) || $usageDate->gt($today)) {
                return response()->json(['error' => 'Usage Date must be within H-14 to today.'], 422);
            }
        }

        $conditionName = $this->resolveConditionName($vp_type, $usagetype);
        $category = MsCategory::where('doctype', self::DOCTYPE)
            ->where('categoryid', 'condition')
            ->where('category_name', $conditionName)
            ->where('status', 'A')
            ->first();

        if (!$category) {
            return response()->json(['error' => 'Category condition "'.$conditionName.'" not found. Please contact IT!'], 422);
        }

        // Validate returnable qty per line before touching anything
        if ($usagetype === 'Return' && $request->has('addmore')) {
            foreach ($request->addmore as $detail) {
                if (empty($detail['product_id']) || empty($detail['qty'])) {
                    continue;
                }
                $exp = $detail['expired_date'] ?: '1900-01-01';
                $origin = TrxVplUsageDetail::where('usage_id', $request->ref_usage_id)
                    ->where('product_id', $detail['product_id'])
                    ->where('expired_date', $exp)
                    ->where('whs_id', $detail['whs_id'])
                    ->first();
                $remaining = $origin ? ($origin->qty_usage - ($origin->qty_settlement ?? 0)) : 0;
                if ($detail['qty'] > $remaining) {
                    $productName = MsVplProduct::where('product_id', $detail['product_id'])->value('product_name') ?? $detail['product_id'];
                    return response()->json(['error' => 'Return qty for '.$productName.' exceeds the remaining returnable qty ('.$remaining.').'], 422);
                }
            }
        }

        // CUSTOMERSERVICE Usage docs skip the normal ms_approval chain entirely —
        // there is only ever one "approver": the creator, auto-approved on submit.
        $isAutoApprove = $request->department === 'CUSTOMERSERVICE' && $usagetype === 'Usage';

        $autonbr = $this->nextAutonbr(
            self::DOCTYPE,
            $dt->year,
            (string) $dt->month,
            $user->username,
            self::DOCTYPE_DSC
        );
        $tglbln = substr((string) $dt->year, 2).sprintf('%02d', (int) $autonbr['month']);
        $docid = self::DOCTYPE.$tglbln.sprintf('%03d', $autonbr['next']);

        try {
            DB::connection('pgsql5')->transaction(function () use ($request, $user, $dt, $usageDate, $docid, $vp_type, $usagetype, $isAutoApprove, &$usage) {
                $usage = TrxVplUsage::create([
                    'usage_id' => $docid,
                    'usage_date' => $usageDate->format('Y-m-d'),
                    'cpnyid' => $request->cpnyid,
                    'department' => $request->department,
                    'user_peminta' => $user->username,
                    'vp_type' => $vp_type,
                    'usagetype' => $usagetype,
                    'usage_remark' => $request->usage_remark,
                    'ref_usage_id' => $request->ref_usage_id ?? null,
                    'status' => 'P',
                    'created_user' => $user->name,
                ]);

                if ($request->has('addmore')) {
                    $line = 1;
                    foreach ($request->addmore as $detail) {
                        if (empty($detail['product_id']) || empty($detail['qty']) || empty($detail['whs_id'])) {
                            continue;
                        }
                        $exp = $detail['expired_date'] ?: '1900-01-01';
                        TrxVplUsageDetail::create([
                            'usage_id' => $docid,
                            'linenbr' => $line++,
                            'product_id' => $detail['product_id'],
                            'expired_date' => $exp,
                            'whs_id' => $detail['whs_id'],
                            'qty_usage' => $usagetype === 'Usage' ? $detail['qty'] : 0,
                            'qty_return_usage' => $usagetype === 'Return' ? $detail['qty'] : 0,
                            'purpose_id' => $detail['purpose_id'] ?? null,
                            'purpose_remark' => $detail['purpose_remark'] ?? null,
                            'ref_usage_id' => $request->ref_usage_id ?? null,
                            'status' => 'P',
                            'created_user' => $user->username,
                            'created_at' => $dt->toDateTimeString(),
                        ]);
                    }
                }

                $this->saveAttachments($request, $docid, $dt->year, $user);

                // Hold stock: Usage reserves, Return releases the hold
                $this->adjustReservation($docid, +1);

                if ($isAutoApprove) {
                    $this->autoApproveCustomerService($usage, $user, $dt);
                }
            });
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        if ($isAutoApprove) {
            return response()->json(['success' => 'Usage document saved and auto-approved.']);
        }

        // Generate approvals after the detail/reservation transaction commits
        $approvalCondition = trim($category->groups ?? '') ?: $conditionName;
        $ctx = ['approval_conditions' => [$approvalCondition]];

        $approvalCtl = app(ApprovalController::class);
        $approvalCtl->generateForDocument(
            $docid,
            self::DOCTYPE,
            $request->cpnyid,
            $request->department,
            $user->username,
            $ctx,
            $dt
        );

        $approvalCtl->notifyFirstApprover(
            $docid,
            self::DOCTYPE,
            'P',
            self::DOCTYPE_DSC,
            route('usagevp.show', $usage->id),
            ['info' => $request->usage_remark ?? '', 'createdby' => $user->name]
        );

        return response()->json(['success' => 'Usage document saved successfully.']);
    }

    // -------------------------------------------------------
    // UPDATE — resubmit from Hold/Revise
    // -------------------------------------------------------
    public function update(Request $request, int $id)
    {
        $user = Auth::user();
        $dt = Carbon::now();
        $usage = TrxVplUsage::find($id);

        if ($request->has('addmore')) {
            $line = TrxVplUsageDetail::where('usage_id', $usage->usage_id)->max('linenbr') ?? 0;
            foreach ($request->addmore as $detail) {
                if (empty($detail['product_id']) || empty($detail['qty']) || empty($detail['whs_id'])) {
                    continue;
                }
                $exp = $detail['expired_date'] ?: '1900-01-01';

                $newDetail = TrxVplUsageDetail::create([
                    'usage_id' => $usage->usage_id,
                    'linenbr' => ++$line,
                    'product_id' => $detail['product_id'],
                    'expired_date' => $exp,
                    'whs_id' => $detail['whs_id'],
                    'qty_usage' => $usage->usagetype === 'Usage' ? $detail['qty'] : 0,
                    'qty_return_usage' => $usage->usagetype === 'Return' ? $detail['qty'] : 0,
                    'purpose_id' => $detail['purpose_id'] ?? null,
                    'purpose_remark' => $detail['purpose_remark'] ?? null,
                    'ref_usage_id' => $usage->ref_usage_id,
                    'status' => 'P',
                    'created_user' => $user->username,
                    'created_at' => $dt->toDateTimeString(),
                ]);
                $this->reserveDetail($newDetail, $usage->usagetype, +1);
            }
        }

        $this->saveAttachments($request, $usage->usage_id, $dt->year, $user);

        $conditionName = $this->resolveConditionName($usage->vp_type, $usage->usagetype);
        $category = MsCategory::where('doctype', self::DOCTYPE)
            ->where('categoryid', 'condition')
            ->where('category_name', $conditionName)
            ->where('status', 'A')
            ->first();
        $approvalCondition = trim($category->groups ?? '') ?: $conditionName;
        $ctx = ['approval_conditions' => [$approvalCondition]];

        $approvalCtl = app(ApprovalController::class);
        $approvalCtl->generateForDocument(
            $usage->usage_id,
            self::DOCTYPE,
            $request->cpnyid ?? $usage->cpnyid,
            $request->department ?? $usage->department,
            $user->username,
            $ctx,
            $dt
        );

        $usage->usage_remark = $request->usage_remark ?? $usage->usage_remark;
        $usage->status = 'P';
        $usage->updated_user = $user->name;
        $usage->updated_at = $dt->toDateTimeString();
        $usage->save();

        $approvalCtl->notifyFirstApprover(
            $usage->usage_id,
            self::DOCTYPE,
            'P',
            self::DOCTYPE_DSC,
            route('usagevp.show', $id),
            ['info' => $usage->usage_remark ?? '', 'createdby' => $user->name]
        );

        return response()->json(['success' => 'Usage document resubmitted successfully.']);
    }

    // -------------------------------------------------------
    // APPROVE
    // -------------------------------------------------------
    public function approve(int $id)
    {
        $user = Auth::user();
        $usage = TrxVplUsage::find($id);

        if ($usage->usagetype === 'Usage') {
            foreach (TrxVplUsageDetail::where('usage_id', $usage->usage_id)->get() as $detail) {
                $stock = MsVplProductDetail::where('product_id', $detail->product_id)
                    ->where('expired_date', $detail->expired_date)
                    ->where('whs_id', $detail->whs_id)
                    ->first();
                if (!$stock || $stock->qty_available < $detail->qty_usage) {
                    $productName = MsVplProduct::where('product_id', $detail->product_id)->value('product_name') ?? $detail->product_id;
                    return response()->json(['error' => 'Approval failed! '.$productName.' (Expired: '.$detail->expired_date.') has insufficient stock.'], 422);
                }
            }
        }

        $approvalCtl = app(ApprovalController::class);

        $result = $approvalCtl->approveStep(
            $usage->usage_id,
            self::DOCTYPE,
            $user->username,
            $user->name,
            function ($refnbr, $now) use ($usage, $user, $id) {
                $usage->status = 'C';
                $usage->completed_user = $user->username;
                $usage->completed_at = $now;
                $usage->save();
                $this->finalizeStock($id);
            },
            function ($next, $now) use ($usage, $id) {
                app(ApprovalController::class)->notifyFirstApprover(
                    $usage->usage_id,
                    self::DOCTYPE,
                    'P',
                    self::DOCTYPE_DSC,
                    route('usagevp.show', $id),
                    ['info' => $usage->usage_remark ?? '']
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
        $usage = TrxVplUsage::find($id);

        $approvalCtl = app(ApprovalController::class);

        $result = $approvalCtl->rejectStep(
            $usage->usage_id,
            self::DOCTYPE,
            $user->username,
            $user->name,
            function ($refnbr, $now) use ($usage, $request, $user, $id) {
                $usage->status = 'R';
                $usage->save();
                $this->adjustReservation($usage->usage_id, -1);
                $this->saveMessage($usage, $request->message, $user);
                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $usage->usage_id,
                    self::DOCTYPE_DSC,
                    'R',
                    $usage->user_peminta,
                    route('usagevp.show', $id),
                    ['info' => $request->message]
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
        $usage = TrxVplUsage::find($id);

        $approvalCtl = app(ApprovalController::class);

        $result = $approvalCtl->reviseStep(
            $usage->usage_id,
            self::DOCTYPE,
            $user->username,
            $user->name,
            function ($refnbr, $now) use ($usage, $request, $user, $id) {
                $usage->status = 'D';
                $usage->updated_user = $user->name;
                $usage->updated_at = $now;
                $usage->save();
                // Unlike Transfer, Usage also releases the pending hold on revise —
                // the creator may substantially change lines before resubmitting.
                $this->adjustReservation($usage->usage_id, -1);
                $this->saveMessage($usage, $request->message, $user);
                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $usage->usage_id,
                    self::DOCTYPE_DSC,
                    'D',
                    $usage->user_peminta,
                    route('usagevp.show', $id),
                    ['info' => $request->message.' (Silahkan revisi dokumen ini)']
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
        $usage = TrxVplUsage::find($id);

        $this->adjustReservation($usage->usage_id, -1);

        $usage->status = 'X';
        $usage->updated_user = $user->name;
        $usage->save();

        TrApproval::where('refnbr', $usage->usage_id)
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
        $usage = TrxVplUsage::find($id);

        $this->saveMessage($usage, $request->message, $user);

        return response()->json(['success' => 'Message sent.']);
    }

    // -------------------------------------------------------
    // DELETE DETAIL / ATTACHMENT
    // -------------------------------------------------------
    public function deleteDetail(Request $request)
    {
        $detail = TrxVplUsageDetail::find($request->detail_id);
        if (!$detail) {
            return response()->json(['error' => 'Not found.'], 404);
        }
        $usage = TrxVplUsage::where('usage_id', $detail->usage_id)->first();
        if ($usage) {
            $this->reserveDetail($detail, $usage->usagetype, -1);
        }
        $detail->delete();

        return response()->json(['success' => 'Detail deleted.']);
    }

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
     * A department can have more than one warehouse assigned for the same
     * company/vp_type/activity_type — return all of them so the form can
     * either auto-fill (single match) or let the user choose (multiple).
     */
    public function getUsageWarehouse(Request $request)
    {
        $vp_type = strtoupper($request->vp_type);

        $whs = MsVplWarehouseDept::where('cpnyid', $request->cpnyid)
            ->where('department_id', $request->department)
            ->where('vp_type', $vp_type)
            ->where('activity_type', 'USAGE')
            ->where('status', 'A')
            ->orderBy('whs_id')
            ->get(['whs_id']);

        return response()->json($whs);
    }

    public function getUsageProducts(Request $request)
    {
        $vp_type = strtoupper($request->vp_type);
        $whsId = $request->whs_id;

        $products = MsVplProduct::join('ms_vpl_product_detail', 'ms_vpl_product.product_id', '=', 'ms_vpl_product_detail.product_id')
            ->select(
                'ms_vpl_product.product_id',
                DB::raw("CONCAT(ms_vpl_product.product_name,' / ',ms_vpl_product.product_value,' / ',ms_vpl_product.product_uom) AS product_name"),
                'ms_vpl_product_detail.expired_date',
                'ms_vpl_product_detail.qty_available',
                DB::raw('COALESCE(ms_vpl_product_detail.qty_reserved, 0) AS qty_reserved'),
                DB::raw('(ms_vpl_product_detail.qty_available - COALESCE(ms_vpl_product_detail.qty_reserved, 0)) AS qty_pickable'),
                'ms_vpl_product_detail.whs_id'
            )
            ->where('ms_vpl_product.cpnyid', $request->cpnyid)
            ->where('ms_vpl_product.product_type', $vp_type)
            ->where('ms_vpl_product_detail.whs_id', $whsId)
            ->whereRaw('(ms_vpl_product_detail.qty_available - COALESCE(ms_vpl_product_detail.qty_reserved, 0)) > 0')
            ->orderBy('ms_vpl_product_detail.expired_date')
            ->get();

        return response()->json($products);
    }

    public function getReturnRefOptions(Request $request)
    {
        $refs = TrxVplUsage::where('status', 'C')
            ->where('usagetype', 'Usage')
            ->where('cpnyid', $request->cpnyid)
            ->where('department', $request->department)
            ->where('vp_type', strtoupper($request->vp_type))
            ->pluck('usage_id');

        return response()->json($refs);
    }

    public function getReturnRefDetails(Request $request)
    {
        $lines = TrxVplUsageDetail::join('ms_vpl_product', 'tr_vpl_usage_detail.product_id', '=', 'ms_vpl_product.product_id')
            ->select(
                'tr_vpl_usage_detail.*',
                'ms_vpl_product.product_name',
                DB::raw('(tr_vpl_usage_detail.qty_usage - COALESCE(tr_vpl_usage_detail.qty_settlement, 0)) AS qty_remaining')
            )
            ->where('tr_vpl_usage_detail.usage_id', $request->ref_usage_id)
            ->whereRaw('(tr_vpl_usage_detail.qty_usage - COALESCE(tr_vpl_usage_detail.qty_settlement, 0)) > 0')
            ->orderBy('tr_vpl_usage_detail.linenbr')
            ->get();

        return response()->json($lines);
    }

    // -------------------------------------------------------
    // PRIVATE HELPERS
    // -------------------------------------------------------
    private function statusBadge(string $status): string
    {
        // Matches the badge style used in budgets.blade.php for visual consistency across modules
        return match ($status) {
            'P' => '<span class="w-32 bg-orange-200/60 text-orange-800 dark:bg-orange-300/40 dark:text-orange-900 pointer-events-none border border-orange-600/40 font-semibold px-4 py-2 text-center rounded">On Progress</span>',
            'C' => '<span class="w-32 bg-green-200/60 text-green-800 dark:bg-green-300/40 dark:text-green-900 pointer-events-none border border-green-600/40 font-semibold px-4 py-2 text-center rounded">Completed</span>',
            'R' => '<span class="w-32 bg-red-200/60 text-red-800 dark:bg-red-300/40 dark:text-red-900 pointer-events-none border border-red-600/40 font-semibold px-4 py-2 text-center rounded">Rejected</span>',
            'X' => '<span class="w-32 bg-red-200/60 text-red-800 dark:bg-red-300/40 dark:text-red-900 pointer-events-none border border-red-600/40 font-semibold px-4 py-2 text-center rounded">Cancel</span>',
            default => '<span class="w-32 bg-amber-200/60 text-amber-800 dark:bg-amber-300/40 dark:text-amber-900 pointer-events-none border border-amber-600/40 font-semibold px-4 py-2 text-center rounded">Revise</span>',
        };
    }

    /**
     * Derive the ms_category condition name from vp_type + usagetype.
     * e.g. 'P' + 'Usage'  -> 'Usage Product'
     *      'V' + 'Return' -> 'Return Usage Voucher'
     */
    private function resolveConditionName(string $vp_type, string $usagetype): string
    {
        $vpLabel = strtoupper($vp_type) === 'V' ? 'Voucher' : 'Product';
        return $usagetype === 'Return' ? 'Return Usage '.$vpLabel : 'Usage '.$vpLabel;
    }

    private function saveAttachments(Request $request, string $docid, int $year, $user): void
    {
        if (!$request->hasFile('attachment')) {
            return;
        }

        foreach ($request->file('attachment') as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }
            $rand = random_int(10000000, 99999999);
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $attachfile = md5((string) $rand).'-'.str_replace('%', '', $file->getClientOriginalName());
            $folder = public_path('attachment/'.$year);
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }
            $file->move($folder, $attachfile);

            Attachment::create([
                'docid' => $docid,
                'name' => $filename,
                'attachfile' => $attachfile,
                'status' => 'A',
                'extention' => $file->getClientOriginalExtension(),
                'created_user' => $user->name,
            ]);
        }
    }

    private function saveMessage(TrxVplUsage $usage, string $message, $user): void
    {
        TrMessage::create([
            'refnbr' => $usage->usage_id,
            'doctype' => self::DOCTYPE,
            'username' => $user->username,
            'name' => $user->name,
            'message' => $message,
            'created_by' => $user->name,
        ]);
    }

    /**
     * CUSTOMERSERVICE Usage docs have no ms_approval chain — the creator is the
     * sole, automatic approver. Records a single approved TrApproval row (for
     * audit/history parity with the normal flow), completes the document, and
     * finalizes stock immediately. Throws (rolling back the transaction) if
     * stock is insufficient, mirroring the check normally done in approve().
     */
    private function autoApproveCustomerService(TrxVplUsage $usage, $user, Carbon $dt): void
    {
        foreach (TrxVplUsageDetail::where('usage_id', $usage->usage_id)->get() as $detail) {
            $stock = MsVplProductDetail::where('product_id', $detail->product_id)
                ->where('expired_date', $detail->expired_date)
                ->where('whs_id', $detail->whs_id)
                ->first();
            if (!$stock || $stock->qty_available < $detail->qty_usage) {
                $productName = MsVplProduct::where('product_id', $detail->product_id)->value('product_name') ?? $detail->product_id;
                throw new \RuntimeException('Insufficient stock for '.$productName.' (Expired: '.$detail->expired_date.').');
            }
        }

        TrApproval::create([
            'refnbr' => $usage->usage_id,
            'aprv_leveling' => '1',
            'aprv_doctype' => self::DOCTYPE,
            'aprv_cpnyid' => $usage->cpnyid,
            'aprv_departementid' => $usage->department,
            'aprv_username' => $user->username,
            'aprv_name' => $user->name,
            'aprv_type' => 'Normal',
            'aprv_datebefore' => $dt,
            'aprv_dateafter' => $dt,
            'status' => 'A',
            'created_by' => $user->name,
        ]);

        $usage->status = 'C';
        $usage->completed_user = $user->username;
        $usage->completed_at = $dt->toDateTimeString();
        $usage->save();

        $this->finalizeStock($usage->id);
    }

    /**
     * Apply/undo the pending-approval stock hold for every line of a document.
     * $sign = +1 on create/resubmit (apply the hold), -1 on reject/revise/cancel (undo it).
     */
    private function adjustReservation(string $usageId, int $sign): void
    {
        $usage = TrxVplUsage::where('usage_id', $usageId)->first();
        if (!$usage) {
            return;
        }
        TrxVplUsageDetail::where('usage_id', $usageId)->each(
            fn ($detail) => $this->reserveDetail($detail, $usage->usagetype, $sign)
        );
    }

    /**
     * Usage  -> qty_reserved += (sign * qty)
     * Return -> qty_reserved -= (sign * qty)
     */
    private function reserveDetail(TrxVplUsageDetail $detail, string $usagetype, int $sign): void
    {
        $qty = $usagetype === 'Usage' ? $detail->qty_usage : $detail->qty_return_usage;
        $delta = $usagetype === 'Usage' ? $sign : -$sign;

        $stock = MsVplProductDetail::where('product_id', $detail->product_id)
            ->where('expired_date', $detail->expired_date)
            ->where('whs_id', $detail->whs_id)
            ->first();

        if ($stock) {
            $stock->qty_reserved = max(0, ($stock->qty_reserved ?? 0) + ($delta * $qty));
            $stock->save();
        }
    }

    /**
     * Final stock movement on completion.
     * Usage  -> qty_available -= qty, qty_reserved -= qty (consumes stock, releases hold)
     * Return -> qty_available += qty, qty_reserved += qty (restocks); bumps qty_settlement
     *           on the referenced original Usage line so it can't be double-returned.
     */
    private function finalizeStock(int $id): void
    {
        $user = Auth::user();
        $datestamp = Carbon::now()->toDateTimeString();
        $usage = TrxVplUsage::find($id);
        $details = TrxVplUsageDetail::where('usage_id', $usage->usage_id)->get();

        foreach ($details as $detail) {
            $stock = MsVplProductDetail::where('product_id', $detail->product_id)
                ->where('expired_date', $detail->expired_date)
                ->where('whs_id', $detail->whs_id)
                ->first();

            if ($usage->usagetype === 'Usage') {
                $qty = $detail->qty_usage;
                if ($stock) {
                    $stock->qty_available -= $qty;
                    $stock->qty_reserved = max(0, ($stock->qty_reserved ?? 0) - $qty);
                    $stock->updated_user = $user->username;
                    $stock->updated_at = $datestamp;
                    $stock->save();
                }
            } else {
                $qty = $detail->qty_return_usage;
                if ($stock) {
                    $stock->qty_available += $qty;
                    $stock->qty_reserved += $qty;
                    $stock->updated_user = $user->username;
                    $stock->updated_at = $datestamp;
                    $stock->save();
                } else {
                    MsVplProductDetail::create([
                        'product_id' => $detail->product_id,
                        'expired_date' => $detail->expired_date,
                        'cpnyid' => $usage->cpnyid,
                        'whs_id' => $detail->whs_id,
                        'qty_available' => $qty,
                        'qty_reserved' => $qty,
                        'status' => 'A',
                        'created_user' => $user->username,
                        'updated_user' => $user->username,
                    ]);
                }

                // Cap the original usage line so it can't be returned twice
                if ($usage->ref_usage_id) {
                    $origin = TrxVplUsageDetail::where('usage_id', $usage->ref_usage_id)
                        ->where('product_id', $detail->product_id)
                        ->where('expired_date', $detail->expired_date)
                        ->where('whs_id', $detail->whs_id)
                        ->first();
                    if ($origin) {
                        $origin->qty_settlement = ($origin->qty_settlement ?? 0) + $qty;
                        $origin->updated_user = $user->username;
                        $origin->save();
                    }
                }
            }
        }
    }
}
