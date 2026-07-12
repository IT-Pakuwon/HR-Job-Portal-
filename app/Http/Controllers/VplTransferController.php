<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\TrxVplTransfer;
use App\Models\TrxVplTransferDetail;
use App\Models\MsVplProduct;
use App\Models\MsVplProductDetail;
use App\Models\MsVplWarehouseDept;
use App\Models\TrApproval;
use App\Models\TrMessage;
use App\Models\Attachment;
use App\Models\Company;
use App\Models\User;
use App\Models\Usercpny;
use App\Models\Userdept;
use App\Models\Site;
use Vinkla\Hashids\Facades\Hashids;

use DataTables;
use Mail;

class VplTransferController extends Controller
{
    use HasAutonbr;

    public const DOCTYPE     = 'VPT';
    public const DOCTYPE_DSC = 'Voucher Product Transfer';

    // -------------------------------------------------------
    // INDEX — serves the main page OR DataTable AJAX
    // -------------------------------------------------------

    public function index(Request $request, $eid = null)
    {
        $user        = Auth::user();
        $multicpnyid = Usercpny::where('username', $user->username)->where('status', 'A')->pluck('cpny_id')->toArray();
        $multidept   = Userdept::where('username', $user->username)->pluck('department_id')->toArray();
        $isVpAccess  = $user->hasRole('VPACCESS');

        if ($request->ajax()) {
            $status = $request->input('status', 'ALL');

            $base = TrxVplTransfer::query();
            if ($user->role !== 'admin' && !$isVpAccess) {
                $base->whereIn('cpnyid', $multicpnyid)->whereIn('department', $multidept);
            }
            if ($status !== 'ALL') {
                $base->where('status', $status);
            }
            $data = $base->orderByDesc('created_at')->get();

            return DataTables::of($data)
                ->addColumn('status_badge', fn ($r) => $this->statusBadge($r->status))
                ->addColumn('transfer_date_fmt', fn ($r) => $r->transfer_date
                    ? Carbon::parse($r->transfer_date)->format('Y-m-d') : '')
                ->addColumn('vp_type_label', fn ($r) => match ($r->vp_type) {
                    'V'     => 'Voucher',
                    'P'     => 'Product',
                    default => $r->vp_type ?? '',
                })
                ->addColumn('transfertype_label', fn ($r) => match ($r->transfertype) {
                    'Transfer' => 'Transfer',
                    'ReturnTf' => 'Return Transfer',
                    default    => '',
                })
                ->addColumn('action', fn ($r) =>
                    '<button type="button" class="btn-view-transfer inline-flex w-36 justify-center rounded bg-gray-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-gray-700" data-id="' . $r->id . '">' . $r->transfer_id . '</button>'
                )
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        // Status count cards
        $qCount = TrxVplTransfer::query();
        if ($user->role !== 'admin' && !$isVpAccess) {
            $qCount->whereIn('cpnyid', $multicpnyid)->whereIn('department', $multidept);
        }
        $counts = [
            'all'       => (clone $qCount)->count(),
            'progress'  => (clone $qCount)->where('status', 'P')->count(),
            'completed' => (clone $qCount)->where('status', 'C')->count(),
            'rejected'  => (clone $qCount)->where('status', 'R')->count(),
            'cancelled' => (clone $qCount)->where('status', 'X')->count(),
            'hold'      => (clone $qCount)->where('status', 'D')->count(),
        ];

        $usercpny  = Usercpny::where('username', $user->username)->get();
        $usercpny2 = Usercpny::where('username', $user->username)->first();
        $userdept  = Userdept::where('username', $user->username)->get();
        $userdept2 = Userdept::where('username', $user->username)->first();

        $initialId = $eid ? (Hashids::decode($eid)[0] ?? null) : null;

        return view('pages.voucher_product.transfer', compact(
            'user', 'usercpny', 'usercpny2', 'userdept', 'userdept2', 'counts', 'initialId'
        ));
    }

    // -------------------------------------------------------
    // STUB ALIASES — all list views redirect to index
    // -------------------------------------------------------
    public function waiting(Request $request)  { return $this->index($request); }
    public function completed(Request $request){ return $this->index($request); }
    public function rejected(Request $request) { return $this->index($request); }
    public function all(Request $request)      { return $this->index($request); }
    public function add()                      { return $this->index(request()); }
    public function show($id)
    {
        // Accepts either the numeric row id or the human-readable transfer_id (e.g. "VPT2607004")
        $transfer = is_numeric($id)
            ? TrxVplTransfer::find($id)
            : TrxVplTransfer::where('transfer_id', $id)->first();

        $eid = $transfer ? Hashids::encode($transfer->id) : null;

        return $this->index(request(), $eid);
    }
    public function edit(int $id)              { return $this->index(request()); }

    // -------------------------------------------------------
    // SHOW DATA — JSON payload for the view modal
    // -------------------------------------------------------
    public function showData(int $id)
    {
        $user     = Auth::user();
        $transfer = TrxVplTransfer::find($id);

        if (!$transfer) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $details = TrxVplTransferDetail::join('ms_vpl_product', 'tr_vpl_transfer_detail.product_id', '=', 'ms_vpl_product.product_id')
            ->select('tr_vpl_transfer_detail.*', 'ms_vpl_product.product_name', 'ms_vpl_product.product_uom')
            ->where('transfer_id', $transfer->transfer_id)
            ->orderBy('linenbr')
            ->get();

        $approvals = TrApproval::where('refnbr', $transfer->transfer_id)
            ->where('aprv_doctype', self::DOCTYPE)
            ->where('status', '<>', 'X')
            ->orderByRaw("CAST(aprv_leveling AS numeric) ASC")
            ->get();

        $attachments = Attachment::where('docid', $transfer->transfer_id)
            ->where('status', 'A')
            ->get();

        $messages = TrMessage::where('refnbr', $transfer->transfer_id)
            ->where('doctype', self::DOCTYPE)
            ->orderBy('created_at')
            ->get();

        $statusMap = [
            'R' => 'Rejected', 'C' => 'Completed',
            'D' => 'Hold',     'X' => 'Cancelled', 'P' => 'On Progress',
        ];
        $statusLabel       = $statusMap[$transfer->status] ?? 'On Progress';
        $transferTypeLabel = match ($transfer->transfertype) {
            'Transfer' => 'Transfer',
            'ReturnTf' => 'Return Transfer',
            default    => $transfer->transfertype ?? '',
        };

        // Approval action flags
        $can_approve = $can_reject = $can_revise = false;
        if ($transfer->status === 'P') {
            $can_approve = TrApproval::where('refnbr', $transfer->transfer_id)
                ->where('aprv_doctype', self::DOCTYPE)
                ->where('status', 'P')
                ->whereNotNull('aprv_datebefore')
                ->where(function ($q) use ($user) {
                    $q->where('aprv_username', $user->username)
                      ->orWhere('aprv_username', 'like', '%' . $user->username . '%');
                })
                ->exists();
            $can_reject = $can_approve;
            $can_revise = $can_approve;
        }

        $anyApproved = TrApproval::where('refnbr', $transfer->transfer_id)
            ->where('aprv_doctype', self::DOCTYPE)
            ->where('status', 'A')
            ->exists();

        $can_edit   = $transfer->status === 'D' && $transfer->created_user === $user->name;
        $can_cancel = $transfer->created_user === $user->name
            && ($transfer->status === 'D' || ($transfer->status === 'P' && !$anyApproved));

        return response()->json([
            'transfer'            => $transfer,
            'hash'                => Hashids::encode($transfer->id),
            'status_label'        => $statusLabel,
            'transfer_type_label' => $transferTypeLabel,
            'details'             => $details,
            'approvals'           => $approvals->map(fn ($ap) => [
                'aprvid'         => $ap->aprv_leveling,
                'name'           => $ap->aprv_name,
                'aprvusername'   => $ap->aprv_username,
                'aprvdatebefore' => $ap->aprv_datebefore,
                'aprvdateafter'  => $ap->aprv_dateafter,
                'status'         => $ap->status,
            ]),
            'attachments'         => $attachments->map(fn ($a) => [
                'id'           => $a->id,
                'name'         => $a->name,
                'attachfile'   => $a->attachfile,
                'extention'    => $a->extention,
                'created_user' => $a->created_user,
                'year'         => $a->created_at?->year,
                'created_at'   => $a->created_at?->format('Y-m-d H:i'),
            ]),
            'messages'            => $messages->map(fn ($m) => [
                'name'       => $m->name,
                'message'    => $m->message,
                'created_at' => $m->created_at?->format('Y-m-d H:i'),
                'is_mine'    => $m->name === $user->name,
            ]),
            'can_approve'         => $can_approve,
            'can_reject'          => $can_reject,
            'can_revise'          => $can_revise,
            'can_edit'            => $can_edit,
            'can_cancel'          => $can_cancel,
            'current_user'        => $user->name,
        ]);
    }

    // -------------------------------------------------------
    // STORE — create new transfer
    // -------------------------------------------------------
    public function store(Request $request)
    {
        $user         = Auth::user();
        $dt           = Carbon::now();
        $vp_type      = strtoupper($request->vp_type);   // 'V' or 'P'
        $transfertype = $request->transfertype;            // 'Transfer' or 'ReturnTf'

        // Ref number is required for return transfers
        if ($transfertype === 'ReturnTf' && empty($request->ref_transfer_id)) {
            return response()->json(['error' => 'Reference Transfer ID is required for Return Transfer.'], 422);
        }

        // Autonbr — self-healing: auto-creates the year/month row if it doesn't exist yet
        $autonbr = $this->nextAutonbr(
            self::DOCTYPE,
            $dt->year,
            (string) $dt->month,
            $user->username,
            self::DOCTYPE_DSC
        );
        $tglbln = substr((string) $dt->year, 2) . sprintf('%02d', (int) $autonbr['month']);
        $docid  = self::DOCTYPE . $tglbln . sprintf('%03d', $autonbr['next']);

        $transfer = TrxVplTransfer::create([
            'transfer_id'     => $docid,
            'cpnyid'          => $request->cpnyid,
            'department'      => $request->department,
            'vp_type'         => $vp_type,
            'transfer_date'   => $dt->format('Y-m-d'),
            'transfertype'    => $transfertype,
            'transfer_remark' => $request->transfer_remark,
            'ref_transfer_id' => $request->ref_transfer_id ?? null,
            'user_transfer'   => $user->username,
            'status'          => 'P',
            'created_user'    => $user->name,
        ]);

        // Details
        if ($request->has('addmore')) {
            $line = 1;
            foreach ($request->addmore as $detail) {
                if (empty($detail['product_id']) || empty($detail['qty_transfer']) || empty($detail['to_whs_id'])) {
                    continue;
                }
                TrxVplTransferDetail::create([
                    'transfer_id'    => $docid,
                    'linenbr'        => $line++,
                    'product_id'     => $detail['product_id'],
                    'qty_available'  => $detail['qty_available'] ?? 0,
                    'qty_transfer'   => $detail['qty_transfer'],
                    'expired_date'   => $detail['expired_date'] ?: '1900-01-01',
                    'from_whs_id'    => $detail['from_whs_id'],
                    'to_whs_id'      => $detail['to_whs_id'],
                    'ref_transfer_id'=> $request->ref_transfer_id ?? null,
                    'status'         => 'P',
                    'created_user'   => $user->username,
                    'created_at'     => $dt->toDateTimeString(),
                ]);
            }
        }

        $this->saveAttachments($request, $docid, $dt->year, $user);

        // Reserve stock in source warehouse for each detail line
        $this->adjustReserved($docid, +1);

        // Generate approval records via ApprovalController
        $conditionName = $this->resolveConditionName($vp_type, $transfertype);
        $ctx           = ['approval_conditions' => [$conditionName]];

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
            route('transfervp.show', $transfer->id),
            ['info' => $request->transfer_remark ?? '', 'createdby' => $user->name]
        );

        return response()->json(['success' => 'Transfer saved successfully.']);
    }

    // -------------------------------------------------------
    // UPDATE — resubmit from Hold/Revise
    // -------------------------------------------------------
    public function update(Request $request, int $id)
    {
        $user     = Auth::user();
        $dt       = Carbon::now();
        $transfer = TrxVplTransfer::find($id);

        // New detail lines
        if ($request->has('addmore')) {
            $line = TrxVplTransferDetail::where('transfer_id', $transfer->transfer_id)->max('linenbr') ?? 0;
            foreach ($request->addmore as $detail) {
                if (empty($detail['product_id']) || empty($detail['qty_transfer']) || empty($detail['to_whs_id'])) {
                    continue;
                }
                $exp = $detail['expired_date'] ?: '1900-01-01';

                $existing = TrxVplTransferDetail::where('transfer_id', $transfer->transfer_id)
                    ->where('product_id', $detail['product_id'])
                    ->where('expired_date', $exp)
                    ->where('to_whs_id', $detail['to_whs_id'])
                    ->first();

                if ($existing) {
                    $existing->qty_transfer += $detail['qty_transfer'];
                    $existing->updated_user  = $user->username;
                    $existing->save();
                    $this->reserveDetail($existing, +1);
                } else {
                    $newDetail = TrxVplTransferDetail::create([
                        'transfer_id'    => $transfer->transfer_id,
                        'linenbr'        => ++$line,
                        'product_id'     => $detail['product_id'],
                        'qty_available'  => $detail['qty_available'] ?? 0,
                        'qty_transfer'   => $detail['qty_transfer'],
                        'expired_date'   => $exp,
                        'from_whs_id'    => $detail['from_whs_id'],
                        'to_whs_id'      => $detail['to_whs_id'],
                        'ref_transfer_id'=> $request->ref_transfer_id ?? null,
                        'status'         => 'P',
                        'created_user'   => $user->username,
                        'created_at'     => $dt->toDateTimeString(),
                    ]);
                    $this->reserveDetail($newDetail, +1);
                }
            }
        }

        $this->saveAttachments($request, $transfer->transfer_id, $dt->year, $user);

        // Re-generate approval records
        $conditionName = $this->resolveConditionName($transfer->vp_type, $transfer->transfertype);
        $ctx           = ['approval_conditions' => [$conditionName]];

        $approvalCtl = app(ApprovalController::class);
        $approvalCtl->generateForDocument(
            $transfer->transfer_id,
            self::DOCTYPE,
            $request->cpnyid ?? $transfer->cpnyid,
            $request->department ?? $transfer->department,
            $user->username,
            $ctx,
            $dt
        );

        $transfer->transfer_remark = $request->transfer_remark ?? $transfer->transfer_remark;
        $transfer->status          = 'P';
        $transfer->updated_user    = $user->name;
        $transfer->updated_at      = $dt->toDateTimeString();
        $transfer->save();

        $approvalCtl->notifyFirstApprover(
            $transfer->transfer_id,
            self::DOCTYPE,
            'P',
            self::DOCTYPE_DSC,
            route('transfervp.show', $id),
            ['info' => $transfer->transfer_remark ?? '', 'createdby' => $user->name]
        );

        return response()->json(['success' => 'Transfer resubmitted successfully.']);
    }

    // -------------------------------------------------------
    // APPROVE
    // -------------------------------------------------------
    public function approve(int $id)
    {
        $user     = Auth::user();
        $transfer = TrxVplTransfer::find($id);

        // Validate stock availability before approving
        $details = TrxVplTransferDetail::where('transfer_id', $transfer->transfer_id)->get();
        foreach ($details as $detail) {
            $stock = MsVplProductDetail::where('product_id', $detail->product_id)
                ->where('expired_date', $detail->expired_date)
                ->where('whs_id', $detail->from_whs_id)
                ->first();

            if (!$stock || $stock->qty_available < $detail->qty_transfer) {
                $productName = MsVplProduct::where('product_id', $detail->product_id)->value('product_name') ?? $detail->product_id;
                return response()->json([
                    'error' => 'Approval failed! ' . $productName . ' (Expired: ' . $detail->expired_date . ') has insufficient stock.',
                ], 422);
            }
        }

        $approvalCtl = app(ApprovalController::class);

        $result = $approvalCtl->approveStep(
            $transfer->transfer_id,
            self::DOCTYPE,
            $user->username,
            $user->name,
            function ($refnbr, $now) use ($transfer, $user, $id) {
                // All steps done → complete document
                $transfer->status         = 'C';
                $transfer->completed_user = $user->username;
                $transfer->completed_at   = $now;
                $transfer->save();
                $this->processTransferStock($id);
            },
            function ($next, $now) use ($transfer, $id) {
                // Notify next approver
                app(ApprovalController::class)->notifyFirstApprover(
                    $transfer->transfer_id,
                    self::DOCTYPE,
                    'P',
                    self::DOCTYPE_DSC,
                    route('transfervp.show', $id),
                    ['info' => $transfer->transfer_remark ?? '']
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

        $user     = Auth::user();
        $transfer = TrxVplTransfer::find($id);

        $approvalCtl = app(ApprovalController::class);

        $result = $approvalCtl->rejectStep(
            $transfer->transfer_id,
            self::DOCTYPE,
            $user->username,
            $user->name,
            function ($refnbr, $now) use ($transfer, $request, $user, $id) {
                $transfer->status = 'R';
                $transfer->save();
                $this->saveMessage($transfer, $request->message, $user);
                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $transfer->transfer_id,
                    self::DOCTYPE_DSC,
                    'R',
                    $transfer->user_transfer,
                    route('transfervp.show', $id),
                    ['info' => $request->message]
                );
            },
            function ($refnbr, $now) use ($transfer) {
                $this->adjustReserved($transfer->transfer_id, -1);
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

        $user     = Auth::user();
        $transfer = TrxVplTransfer::find($id);

        $approvalCtl = app(ApprovalController::class);

        $result = $approvalCtl->reviseStep(
            $transfer->transfer_id,
            self::DOCTYPE,
            $user->username,
            $user->name,
            function ($refnbr, $now) use ($transfer, $request, $user, $id) {
                $transfer->status       = 'D';
                $transfer->updated_user = $user->name;
                $transfer->updated_at   = $now;
                $transfer->save();
                $this->saveMessage($transfer, $request->message, $user);
                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $transfer->transfer_id,
                    self::DOCTYPE_DSC,
                    'D',
                    $transfer->user_transfer,
                    route('transfervp.show', $id),
                    ['info' => $request->message . ' (Silahkan revisi dokumen ini)']
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
        $user     = Auth::user();
        $transfer = TrxVplTransfer::find($id);

        $this->adjustReserved($transfer->transfer_id, -1);

        $transfer->status       = 'X';
        $transfer->updated_user = $user->name;
        $transfer->save();

        TrApproval::where('refnbr', $transfer->transfer_id)
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
        $user     = Auth::user();
        $transfer = TrxVplTransfer::find($id);

        TrMessage::create([
            'refnbr'     => $transfer->transfer_id,
            'doctype'    => self::DOCTYPE,
            'username'   => $user->username,
            'name'       => $user->name,
            'message'    => $request->message,
            'created_by' => $user->name,
        ]);

        return response()->json(['success' => 'Message sent.']);
    }

    // -------------------------------------------------------
    // DELETE DETAIL / ATTACHMENT
    // -------------------------------------------------------
    public function deleteDetail(Request $request)
    {
        $detail = TrxVplTransferDetail::find($request->detail_id);
        if (!$detail) {
            return response()->json(['error' => 'Not found.'], 404);
        }
        $this->reserveDetail($detail, -1);
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
     * Returns FROM warehouse candidates for the given transfer type.
     * Transfer   → central warehouse (activity_type = 'TRANSFER')
     * ReturnTf   → department's transfer-receiving warehouse (activity_type = 'TRANSFER_RECEIVE')
     *
     * 'TRANSFER_RECEIVE' is distinct from 'RECEIVE': 'RECEIVE' gates whether a
     * department can create a Goods Receipt document (VplReceiveController),
     * while 'TRANSFER_RECEIVE' gates whether a department can be the destination
     * of an internal Transfer / source of a Return Transfer. A department may be
     * allowed one without the other.
     *
     * A department can have more than one warehouse assigned to the same
     * activity_type/vp_type — the frontend auto-fills when exactly one
     * candidate is returned, or shows a picker when there are several.
     */
    public function getFromWhs(Request $request)
    {
        $cpnyid       = $request->cpnyid;
        $department   = $request->department;
        $vp_type      = strtoupper($request->vp_type);
        $transfertype = $request->transfertype;

        // Both Transfer and ReturnTf filter by department — each dept has its own assigned warehouse
        $activityType = $transfertype === 'Transfer' ? 'TRANSFER' : 'TRANSFER_RECEIVE';

        $list = MsVplWarehouseDept::where('cpnyid', $cpnyid)
            ->where('department_id', $department)
            ->where('vp_type', $vp_type)
            ->where('activity_type', $activityType)
            ->where('status', 'A')
            ->get(['whs_id', 'department_id']);

        return response()->json($list);
    }

    /**
     * Returns TO warehouse options.
     * Transfer   → department transfer-receiving warehouses (activity_type = 'TRANSFER_RECEIVE', excluding FROM)
     * ReturnTf   → central transfer warehouse
     */
    public function getToWhs(Request $request)
    {
        $cpnyid       = $request->cpnyid;
        $department   = $request->department;
        $vp_type      = strtoupper($request->vp_type);
        $transfertype = $request->transfertype;
        $fromWhsId    = $request->from_whs_id;

        if ($transfertype === 'Transfer') {
            // All TRANSFER_RECEIVE warehouses across all depts (open destination), excluding the FROM whs
            $list = MsVplWarehouseDept::where('cpnyid', $cpnyid)
                ->where('vp_type', $vp_type)
                ->where('activity_type', 'TRANSFER_RECEIVE')
                ->where('status', 'A')
                ->when($fromWhsId, fn ($q) => $q->where('whs_id', '<>', $fromWhsId))
                ->get(['whs_id', 'department_id']);
        } else {
            // ReturnTf: back to the dept's own TRANSFER (central) warehouse
            $list = MsVplWarehouseDept::where('cpnyid', $cpnyid)
                ->where('department_id', $department)
                ->where('vp_type', $vp_type)
                ->where('activity_type', 'TRANSFER')
                ->where('status', 'A')
                ->get(['whs_id', 'department_id']);
        }

        return response()->json($list);
    }

    /**
     * Returns products for the detail table.
     * Transfer   → stock in the FROM warehouse
     * ReturnTf   → detail lines of the reference transfer
     */
    public function getTransferProducts(Request $request)
    {
        $cpnyid       = $request->cpnyid;
        $vp_type      = strtoupper($request->vp_type);
        $transfertype = $request->transfertype;
        $fromWhsId    = $request->from_whs_id;
        $refId        = $request->ref_transfer_id;

        if ($transfertype === 'Transfer') {
            $products = MsVplProduct::join('ms_vpl_product_detail', 'ms_vpl_product.product_id', '=', 'ms_vpl_product_detail.product_id')
                ->select(
                    'ms_vpl_product.product_id',
                    DB::raw("CONCAT(ms_vpl_product.product_name,' / ',ms_vpl_product.product_value,' / ',ms_vpl_product.product_uom) AS product_name"),
                    'ms_vpl_product_detail.expired_date',
                    'ms_vpl_product_detail.qty_available',
                    DB::raw('COALESCE(ms_vpl_product_detail.qty_reserved, 0) AS qty_reserved'),
                    DB::raw('(ms_vpl_product_detail.qty_available - COALESCE(ms_vpl_product_detail.qty_reserved, 0)) AS qty_pickable'),
                    DB::raw('0 AS qty_transfer'),
                    'ms_vpl_product_detail.whs_id'
                )
                ->where('ms_vpl_product.cpnyid', $cpnyid)
                ->where('ms_vpl_product.product_type', $vp_type)
                ->where('ms_vpl_product_detail.whs_id', $fromWhsId)
                ->whereRaw('(ms_vpl_product_detail.qty_available - COALESCE(ms_vpl_product_detail.qty_reserved, 0)) > 0')
                ->orderBy('ms_vpl_product_detail.expired_date')
                ->get();
        } else {
            $products = TrxVplTransferDetail::join('tr_vpl_transfer', 'tr_vpl_transfer_detail.transfer_id', '=', 'tr_vpl_transfer.transfer_id')
                ->join('ms_vpl_product', 'tr_vpl_transfer_detail.product_id', '=', 'ms_vpl_product.product_id')
                ->leftJoin('ms_vpl_product_detail', function ($join) {
                    $join->on('tr_vpl_transfer_detail.product_id', '=', 'ms_vpl_product_detail.product_id')
                         ->on('tr_vpl_transfer_detail.expired_date', '=', 'ms_vpl_product_detail.expired_date')
                         ->on('tr_vpl_transfer_detail.to_whs_id', '=', 'ms_vpl_product_detail.whs_id');
                })
                ->select(
                    'tr_vpl_transfer_detail.product_id',
                    DB::raw("CONCAT(ms_vpl_product.product_name,' / ',ms_vpl_product.product_value,' / ',ms_vpl_product.product_uom) AS product_name"),
                    'tr_vpl_transfer_detail.expired_date',
                    'ms_vpl_product_detail.qty_available',
                    'tr_vpl_transfer_detail.qty_transfer',
                    'tr_vpl_transfer_detail.to_whs_id AS whs_id'
                )
                ->where('tr_vpl_transfer.transfer_id', $refId)
                ->where('ms_vpl_product.product_type', $vp_type)
                ->orderBy('tr_vpl_transfer_detail.expired_date')
                ->get();
        }

        return response()->json($products);
    }

    /**
     * Returns completed transfer IDs that can be referenced for ReturnTf.
     */
    public function getRefOptions(Request $request)
    {
        if ($request->transfertype !== 'ReturnTf') {
            return response()->json([]);
        }

        $refs = TrxVplTransfer::where('status', 'C')
            ->where('cpnyid', $request->cpnyid)
            ->where('department', $request->department)
            ->where('vp_type', strtoupper($request->vp_type))
            ->pluck('transfer_id');

        return response()->json($refs);
    }

    // -------------------------------------------------------
    // PRIVATE HELPERS
    // -------------------------------------------------------

    /**
     * Derive condition name from vp_type + transfertype.
     * e.g. 'V' + 'Transfer' → 'Transfer Voucher'
     *      'P' + 'ReturnTf' → 'Return Product'
     */
    private function resolveConditionName(string $vp_type, string $transfertype): string
    {
        $typeLabel = $transfertype === 'Transfer' ? 'Transfer' : 'Return';
        $vpLabel   = strtoupper($vp_type) === 'V' ? 'Voucher' : 'Product';
        return $typeLabel . ' ' . $vpLabel;
    }

    private function reserveDetail(TrxVplTransferDetail $detail, int $delta): void
    {
        $stock = MsVplProductDetail::where('product_id', $detail->product_id)
            ->where('expired_date', $detail->expired_date)
            ->where('whs_id', $detail->from_whs_id)
            ->first();
        if ($stock) {
            $stock->qty_reserved = max(0, ($stock->qty_reserved ?? 0) + ($delta * $detail->qty_transfer));
            $stock->save();
        }
    }

    private function adjustReserved(string $transferId, int $delta): void
    {
        TrxVplTransferDetail::where('transfer_id', $transferId)->each(
            fn ($detail) => $this->reserveDetail($detail, $delta)
        );
    }

    private function statusBadge(string $status): string
    {
        // Matches the badge style used in budgets.blade.php for visual consistency across modules
        return match ($status) {
            'P'     => '<span class="w-32 bg-orange-200/60 text-orange-800 dark:bg-orange-300/40 dark:text-orange-900 pointer-events-none border border-orange-600/40 font-semibold px-4 py-2 text-center rounded">On Progress</span>',
            'C'     => '<span class="w-32 bg-green-200/60 text-green-800 dark:bg-green-300/40 dark:text-green-900 pointer-events-none border border-green-600/40 font-semibold px-4 py-2 text-center rounded">Completed</span>',
            'R'     => '<span class="w-32 bg-red-200/60 text-red-800 dark:bg-red-300/40 dark:text-red-900 pointer-events-none border border-red-600/40 font-semibold px-4 py-2 text-center rounded">Rejected</span>',
            'X'     => '<span class="w-32 bg-red-200/60 text-red-800 dark:bg-red-300/40 dark:text-red-900 pointer-events-none border border-red-600/40 font-semibold px-4 py-2 text-center rounded">Cancel</span>',
            default => '<span class="w-32 bg-amber-200/60 text-amber-800 dark:bg-amber-300/40 dark:text-amber-900 pointer-events-none border border-amber-600/40 font-semibold px-4 py-2 text-center rounded">Revise</span>',
        };
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
            $rand       = random_int(10000000, 99999999);
            $filename   = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $attachfile = md5($rand) . '-' . str_replace('%', '', $file->getClientOriginalName());
            $folder     = public_path('attachment/' . $year);

            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }
            $file->move($folder, $attachfile);

            Attachment::create([
                'docid'        => $docid,
                'name'         => $filename,
                'attachfile'   => $attachfile,
                'status'       => 'A',
                'extention'    => $file->getClientOriginalExtension(),
                'created_user' => $user->name,
            ]);
        }
    }

    private function saveMessage(TrxVplTransfer $transfer, string $message, $user): void
    {
        TrMessage::create([
            'refnbr'     => $transfer->transfer_id,
            'doctype'    => self::DOCTYPE,
            'username'   => $user->username,
            'name'       => $user->name,
            'message'    => $message,
            'created_by' => $user->name,
        ]);
    }

    private function processTransferStock(int $id): void
    {
        $user      = Auth::user();
        $datestamp = Carbon::now()->toDateTimeString();
        $transfer  = TrxVplTransfer::find($id);
        $details   = TrxVplTransferDetail::where('transfer_id', $transfer->transfer_id)->get();

        foreach ($details as $detail) {
            // Deduct from source
            $from = MsVplProductDetail::where('product_id', $detail->product_id)
                ->where('expired_date', $detail->expired_date)
                ->where('whs_id', $detail->from_whs_id)
                ->first();

            if ($from) {
                $from->qty_available -= $detail->qty_transfer;
                $from->qty_reserved   = max(0, ($from->qty_reserved ?? 0) - $detail->qty_transfer);
                $from->updated_user   = $user->username;
                $from->updated_at     = $datestamp;
                $from->save();
            }

            // Add to destination
            $to = MsVplProductDetail::where('product_id', $detail->product_id)
                ->where('expired_date', $detail->expired_date)
                ->where('whs_id', $detail->to_whs_id)
                ->first();

            if ($to) {
                $to->qty_available += $detail->qty_transfer;
                $to->updated_user   = $user->username;
                $to->updated_at     = $datestamp;
                $to->save();
            } else {
                MsVplProductDetail::create([
                    'product_id'    => $detail->product_id,
                    'expired_date'  => $detail->expired_date,
                    'cpnyid'        => $transfer->cpnyid,
                    'whs_id'        => $detail->to_whs_id,
                    'qty_available' => $detail->qty_transfer,
                    'status'        => 'A',
                    'created_user'  => $user->username,
                    'updated_user'  => $user->username,
                ]);
            }
        }
    }
}
