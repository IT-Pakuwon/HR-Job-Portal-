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
use App\Models\TrxVplReceive;
use App\Models\TrxVplReceiveDetail;
use App\Models\User;
use App\Models\Usercpny;
use App\Models\Userdept;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VplReceiveController extends Controller
{
    use HasAutonbr;

    public const DOCTYPE = 'VPR';
    public const DOCTYPE_DSC = 'Voucher Product Receive';

    // -------------------------------------------------------
    // INDEX — serves view page OR DataTable AJAX
    // -------------------------------------------------------
    public function index(Request $request)
    {
        $user = Auth::user();
        $multicpnyid = Usercpny::where('username', $user->username)->where('status', 'A')->pluck('cpny_id')->toArray();
        $multidept = Userdept::where('username', $user->username)->pluck('department_id')->toArray();

        $isVpAccess = $user->hasRole('VPACCESS');

        if ($request->ajax()) {
            $status = $request->input('status', 'ALL');

            // TrxVplReceive (pgsql5) and TrApproval (pgsql2) are on different connections
            // so we cannot JOIN — fetch approver names separately
            $base = TrxVplReceive::query();
            if ($user->role !== 'admin' && !$isVpAccess) {
                $base->whereIn('cpnyid', $multicpnyid)->whereIn('department', $multidept);
            }
            if ($status !== 'ALL') {
                $base->where('status', $status);
            }

            $data = $base->get();

            // For "On Progress" — attach current approver name from pgsql2
            if ($status === 'P' && $data->isNotEmpty()) {
                $approverMap = TrApproval::whereIn('refnbr', $data->pluck('receive_id'))
                    ->where('aprv_doctype', self::DOCTYPE)
                    ->where('status', 'P')
                    ->whereNotNull('aprv_datebefore')
                    ->pluck('aprv_name', 'refnbr');

                $data->each(fn ($row) => $row->waiting = $approverMap->get($row->receive_id, ''));
            }

            return \DataTables::of($data)
                ->addColumn('status_badge', fn ($r) => $this->statusBadge($r->status))
                ->addColumn('receive_date', fn ($r) => $r->receive_date ? Carbon::parse($r->receive_date)->format('Y-m-d') : '')
                ->addColumn('action', fn ($r) => '<button type="button" class="btn-view inline-flex w-40 justify-center rounded bg-gray-500 px-3 py-1.5 text-sm font-semibold text-white hover:bg-gray-700" data-id="'.$r->id.'">'.$r->receive_id.'</button>'
                )
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        // Status count cards (scoped by user)
        $qCount = TrxVplReceive::query();
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

        // Source of Receive options from ms_category (pgsql2)
        $sourceOptions = MsCategory::where('doctype', self::DOCTYPE)
            ->where('categoryid', 'type')
            ->where('groups', 'SOURCE')
            ->where('status', 'A')
            ->orderBy('category_name')
            ->pluck('category_name');

        return view('pages.voucher_product.receive', compact(
            'user', 'usercpny', 'usercpny2', 'userdept', 'userdept2', 'counts', 'sourceOptions'
        ));
    }

    // -------------------------------------------------------
    // SHOW DATA — JSON for view modal
    // -------------------------------------------------------
    public function showData(int $id)
    {
        $user = Auth::user();
        $receive = TrxVplReceive::find($id);
        if (!$receive) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $details = TrxVplReceiveDetail::join('ms_vpl_product', 'trx_vpl_receive_detail.product_id', '=', 'ms_vpl_product.product_id')
            ->select('trx_vpl_receive_detail.*', 'ms_vpl_product.product_name', 'ms_vpl_product.product_uom', 'ms_vpl_product.product_source_tenant')
            ->where('receive_id', $receive->receive_id)
            ->get();

        $approvals = TrApproval::where('refnbr', $receive->receive_id)
            ->where('aprv_doctype', self::DOCTYPE)
            ->where('status', '<>', 'X')
            ->orderBy('aprv_leveling')
            ->get();

        $attachments = Attachment::where('docid', $receive->receive_id)->where('status', 'A')->get();
        $messages = TrMessage::where('refnbr', $receive->receive_id)->where('doctype', self::DOCTYPE)->get();

        $statusMap = ['R' => 'Rejected', 'C' => 'Completed', 'D' => 'Hold', 'X' => 'Cancelled', 'P' => 'On Progress'];
        $statusLabel = $statusMap[$receive->status] ?? 'On Progress';
        $vpLabel = ucfirst($receive->vp_type ?? '');

        // Approval action flags
        $can_approve = $can_reject = $can_revise = false;
        if ($receive->status === 'P') {
            $can_approve = TrApproval::where('refnbr', $receive->receive_id)
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

        // Cancel: creator only
        // - status D (hold/revise): always allowed (approver sent back for revision)
        // - status P: only if NO approval step has been approved yet
        $anyApproved = TrApproval::where('refnbr', $receive->receive_id)
            ->where('aprv_doctype', self::DOCTYPE)
            ->where('status', 'A')
            ->exists();

        $can_cancel = $receive->created_user === $user->name
            && ($receive->status === 'D' || ($receive->status === 'P' && !$anyApproved));

        // Edit: creator only when D
        $can_edit = $receive->status === 'D' && $receive->created_user === $user->name;

        return response()->json([
            'receive' => $receive,
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
            'can_cancel' => $can_cancel,
            'can_edit' => $can_edit,
            'current_user' => $user->name,
        ]);
    }

    // -------------------------------------------------------
    // STORE
    // -------------------------------------------------------
    public function store(Request $request)
    {
        $user = Auth::user();
        $dt = Carbon::now();

        // vp_type is sent as 'voucher' or 'product' from the form
        $vpTypeName = $request->vp_type; // 'voucher' | 'product'

        // Read ms_category to get approval condition
        $category = MsCategory::where('doctype', self::DOCTYPE)
            ->where('categoryid', 'condition')
            ->where('category_name', $vpTypeName)
            ->where('status', 'A')
            ->first();

        if (!$category) {
            return response()->json(['error' => 'Category condition not found for '.strtoupper($vpTypeName).'. Please contact IT!'], 422);
        }

        // Generate docid: VPR + YY + M + NNN
        $autonbr = $this->nextAutonbr(
            self::DOCTYPE,
            $dt->year,
            (string) $dt->month,   // non-zero-padded, e.g. '6' or '12'
            $user->username,
            self::DOCTYPE_DSC
        );
        $tglbln = substr((string) $dt->year, 2).$autonbr['month'];
        $docid = self::DOCTYPE.$tglbln.sprintf('%03d', $autonbr['next']);

        try {
            DB::connection('pgsql5')->transaction(function () use ($request, $user, $dt, $docid, $vpTypeName, $category, &$receive) {
                $receive = TrxVplReceive::create([
                    'receive_id' => $docid,
                    'cpnyid' => $request->cpnyid,
                    'department' => $request->department,
                    'vp_type' => $vpTypeName,
                    'receive_date' => $dt->format('Y-m-d'),
                    'receive_type' => $request->receive_type,
                    'receive_company' => $request->receive_company,
                    'receive_tenant' => strtoupper($request->product_source_tenant ?? ''),
                    'source_receive_id' => $request->source_receive_id,
                    'source_receive_dept' => $request->source_receive_dept,
                    'receive_remark' => $request->receive_remark,
                    'user_penerima' => $user->username,
                    'status' => 'P',
                    'created_user' => $user->name,
                ]);

                if ($request->has('addmore')) {
                    $line = 1;
                    foreach ($request->addmore as $detail) {
                        $exp = !empty($detail['expired_date']) ? $detail['expired_date'] : '1900-01-01';
                        TrxVplReceiveDetail::create([
                            'receive_id' => $docid,
                            'linenbr' => $line++,
                            'product_id' => $detail['product_name'],
                            'qty_receive' => $detail['qty'],
                            'expired_date' => $exp,
                            'whs_id' => $detail['whs_id'],
                            'status' => 'P',
                            'created_user' => $user->username,
                            'created_at' => $dt->toDateTimeString(),
                        ]);
                    }
                }

                $this->saveAttachments($request, $docid, $dt->year, $user);

                // Generate approvals — if this throws, the whole transaction rolls back
                $approvalCondition = trim($category->groups ?? '') ?: $vpTypeName;
                $ctx = ['approval_conditions' => [$approvalCondition]];

                $approvalCtl = app(ApprovalController::class);
                [$firstApproverUsername, $linesCount] = $approvalCtl->generateForDocument(
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
                    url('/vpl/showreceivevp/'.$receive->id),
                    ['info' => $request->receive_remark ?? '', 'createdby' => $user->name]
                );
            });
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json(['success' => 'Receive saved successfully.']);
    }

    // -------------------------------------------------------
    // UPDATE
    // -------------------------------------------------------
    public function update(Request $request, int $id)
    {
        $user = Auth::user();
        $dt = Carbon::now();
        $receive = TrxVplReceive::find($id);

        try {
            if ($request->has('addmore')) {
                foreach ($request->addmore as $detail) {
                    if (empty($detail['product_name']) || empty($detail['qty']) || empty($detail['whs_id'])) {
                        continue;
                    }
                    $exp = !empty($detail['expired_date']) ? $detail['expired_date'] : '1900-01-01';
                    $row = TrxVplReceiveDetail::where('receive_id', $receive->receive_id)
                        ->where('product_id', $detail['product_name'])
                        ->where('expired_date', $exp)
                        ->where('whs_id', $detail['whs_id'])->first();

                    if ($row) {
                        $row->qty_receive += $detail['qty'];
                        $row->updated_at = $dt->toDateTimeString();
                        $row->save();
                    } else {
                        TrxVplReceiveDetail::create([
                            'receive_id' => $receive->receive_id,
                            'linenbr' => 0,
                            'product_id' => $detail['product_name'],
                            'qty_receive' => $detail['qty'],
                            'expired_date' => $exp,
                            'whs_id' => $detail['whs_id'],
                            'status' => 'P',
                            'created_user' => $user->username,
                            'created_at' => $dt->toDateTimeString(),
                        ]);
                    }
                }
                $line = 1;
                foreach (TrxVplReceiveDetail::where('receive_id', $receive->receive_id)->orderBy('created_at')->get() as $d) {
                    $d->linenbr = $line++;
                    $d->save();
                }
            }

            $this->saveAttachments($request, $receive->receive_id, $dt->year, $user);

            // Read category condition — vp_type already stored as 'voucher'/'product'
            $vpTypeName = $receive->vp_type;
            $category = MsCategory::where('doctype', self::DOCTYPE)
                ->where('categoryid', 'condition')
                ->where('category_name', $vpTypeName)
                ->where('status', 'A')
                ->first();

            $approvalCondition = trim($category->groups ?? '') ?: $vpTypeName;
            $ctx = ['approval_conditions' => [$approvalCondition]];

            $approvalCtl = app(ApprovalController::class);
            $approvalCtl->generateForDocument(
                $receive->receive_id,
                self::DOCTYPE,
                $receive->cpnyid,
                $receive->department,
                $user->username,
                $ctx,
                $dt
            );

            $receive->receive_type = $request->receive_type;
            $receive->receive_tenant = strtoupper($request->product_source_tenant ?? '');
            $receive->source_receive_dept = $request->source_receive_dept;
            $receive->receive_remark = $request->receive_remark;
            $receive->status = 'P';
            $receive->updated_user = $user->name;
            $receive->updated_at = $dt->toDateTimeString();
            $receive->save();

            $approvalCtl->notifyFirstApprover(
                $receive->receive_id,
                self::DOCTYPE,
                'P',
                self::DOCTYPE_DSC,
                url('/vpl/showreceivevp/'.$id),
                ['info' => $request->receive_remark ?? '', 'createdby' => $user->name]
            );
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json(['success' => 'Receive updated successfully.']);
    }

    // -------------------------------------------------------
    // APPROVE
    // -------------------------------------------------------
    public function approve(int $id)
    {
        $user = Auth::user();
        $receive = TrxVplReceive::find($id);

        $approvalCtl = app(ApprovalController::class);

        $result = $approvalCtl->approveStep(
            $receive->receive_id,
            self::DOCTYPE,
            $user->username,
            $user->name,
            function ($refnbr, $now) use ($receive, $user) {
                // All approvals done → complete document
                $receive->status = 'C';
                $receive->completed_user = $user->username;
                $receive->completed_at = $now;
                $receive->save();
                $this->insertMsProductDetail($receive->id);
            },
            function ($next, $now) use ($receive, $id) {
                // Notify next approver
                app(ApprovalController::class)->notifyFirstApprover(
                    $receive->receive_id,
                    self::DOCTYPE,
                    'P',
                    self::DOCTYPE_DSC,
                    url('/vpl/showreceivevp/'.$id),
                    ['info' => $receive->receive_remark ?? '']
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
        $receive = TrxVplReceive::find($id);

        $approvalCtl = app(ApprovalController::class);

        $result = $approvalCtl->rejectStep(
            $receive->receive_id,
            self::DOCTYPE,
            $user->username,
            $user->name,
            function ($refnbr, $now) use ($receive, $request, $user, $id) {
                $receive->status = 'R';
                $receive->save();
                $this->saveMessage($receive, $request->message, $user);
                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $receive->receive_id,
                    self::DOCTYPE_DSC,
                    'R',
                    $receive->user_penerima,
                    url('/vpl/showreceivevp/'.$id),
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
        $receive = TrxVplReceive::find($id);

        $approvalCtl = app(ApprovalController::class);

        $result = $approvalCtl->reviseStep(
            $receive->receive_id,
            self::DOCTYPE,
            $user->username,
            $user->name,
            function ($refnbr, $now) use ($receive, $request, $user, $id) {
                $receive->status = 'D';
                $receive->updated_user = $user->name;
                $receive->updated_at = $now;
                $receive->save();
                $this->saveMessage($receive, $request->message, $user);
                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $receive->receive_id,
                    self::DOCTYPE_DSC,
                    'D',
                    $receive->user_penerima,
                    url('/vpl/showreceivevp/'.$id),
                    ['info' => $request->message.' (Silahkan revisi dokumen ini)']
                );
            }
        );

        if (!$result['ok']) {
            return response()->json(['error' => $result['message']], 403);
        }

        return response()->json(['success' => 'Document set for revision.']);
    }

    // -------------------------------------------------------
    // CANCEL
    // -------------------------------------------------------
    public function cancel(int $id)
    {
        $user = Auth::user();
        $receive = TrxVplReceive::find($id);

        $receive->status = 'X';
        $receive->updated_user = $user->name;
        $receive->save();

        TrApproval::where('refnbr', $receive->receive_id)
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
        $receive = TrxVplReceive::find($id);

        TrMessage::create([
            'refnbr' => $receive->receive_id,
            'doctype' => self::DOCTYPE,
            'username' => $user->username,
            'name' => $user->name,
            'message' => $request->message,
            'created_by' => $user->name,
        ]);

        return response()->json(['success' => 'Message sent.']);
    }

    // -------------------------------------------------------
    // DELETE DETAIL / ATTACHMENT
    // -------------------------------------------------------
    public function deleteDetail(Request $request)
    {
        $detail = TrxVplReceiveDetail::find($request->detail_id);
        if (!$detail) {
            return response()->json(['error' => 'Not found.'], 404);
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
    public function getProducts(Request $request)
    {
        $vpCode = match (strtolower($request->vp_type)) {
            'voucher' => 'V',
            'product' => 'P',
            default => $request->vp_type,
        };

        $products = MsVplProduct::select('id', 'product_id', 'product_check_exp', 'product_name', 'product_value', 'product_uom', 'product_source_tenant')
            ->where('cpnyid', $request->cpnyid)
            ->where('product_type', $vpCode)
            ->where('status', 'A')
            ->orderBy('product_name')
            ->get();

        // Keep original uom before transforming name
        $products->transform(fn ($p) => tap($p, fn ($p) => $p->product_label = $p->product_name.' / '.number_format($p->product_value, 0, '.', ',').' / '.$p->product_uom
        ));

        return response()->json($products);
    }

    public function getWarehouse(Request $request)
    {
        $vpCode = match (strtolower($request->vp_type)) {
            'voucher' => 'V',
            'product' => 'P',
            default => $request->vp_type,
        };

        return response()->json(
            MsVplWarehouseDept::where('cpnyid', $request->cpnyid)
                ->where('department_id', $request->department)
                ->where('activity_type', 'RECEIVE')
                ->where('vp_type', $vpCode)
                ->where('status', 'A')
                ->get(['whs_id'])
        );
    }

    public function getTenants(Request $request)
    {
        return response()->json(
            MsVplProduct::where('cpnyid', $request->cpnyid)
                ->where('status', 'A')
                ->select('product_source_tenant')
                ->distinct()->get()
        );
    }

    public function getProductDetails(Request $request)
    {
        return response()->json(
            MsVplProduct::where('product_id', $request->product_id)
                ->select('product_id', 'product_check_exp')->first()
        );
    }

    // -------------------------------------------------------
    // STUB — existing GET routes remain valid
    // -------------------------------------------------------
    public function waiting(Request $request)
    {
        return $this->index($request);
    }

    public function completed(Request $request)
    {
        return $this->index($request);
    }

    public function rejected(Request $request)
    {
        return $this->index($request);
    }

    public function all(Request $request)
    {
        return $this->index($request);
    }

    public function add()
    {
        return $this->index(request());
    }

    public function show(int $id)
    {
        return $this->index(request());
    }

    public function edit(int $id)
    {
        return $this->index(request());
    }

    // -------------------------------------------------------
    // PRIVATE HELPERS
    // -------------------------------------------------------
    private function statusBadge(string $status): string
    {
        return match ($status) {
            'P' => '<span class="inline-block w-28 items-center rounded bg-yellow-300/30 px-3 py-1.5 text-sm font-semibold text-yellow-600">On Progress</span>',
            'C' => '<span class="inline-block w-28 rounded bg-green-300/30 px-3 py-1.5 text-sm font-semibold text-green-600">Completed</span>',
            'R' => '<span class="inline-block w-24 rounded bg-red-300/30 px-3 py-1.5 text-sm font-semibold text-red-600">Rejected</span>',
            'X' => '<span class="inline-block w-24 rounded bg-red-300/30 px-3 py-1.5 text-sm font-semibold text-red-600">Cancelled</span>',
            default => '<span class="inline-block w-24 rounded bg-blue-300/30 px-3 py-1.5 text-sm font-semibold text-blue-600">Hold / Revise</span>',
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
            $rand = random_int(10000000, 99999999);
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $attachfile = md5($rand).'-'.str_replace('%', '', $file->getClientOriginalName());
            $folder = public_path('attachment/'.$year);
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }
            $file->move($folder, $attachfile);

            $attach = new Attachment();
            $attach->docid = $docid;
            $attach->name = $filename;
            $attach->attachfile = $attachfile;
            $attach->status = 'A';
            $attach->extention = $file->getClientOriginalExtension();
            $attach->created_user = $user->name;
            $attach->save();
        }
    }

    private function saveMessage($receive, string $message, $user): void
    {
        TrMessage::create([
            'refnbr' => $receive->receive_id,
            'doctype' => self::DOCTYPE,
            'username' => $user->username,
            'name' => $user->name,
            'message' => $message,
            'created_by' => $user->name,
        ]);
    }

    private function insertMsProductDetail(int $id): void
    {
        $user = Auth::user();
        $datestamp = Carbon::now()->toDateTimeString();
        $receive = TrxVplReceive::find($id);

        foreach (TrxVplReceiveDetail::where('receive_id', $receive->receive_id)->get() as $detail) {
            $row = MsVplProductDetail::where('product_id', $detail->product_id)
                ->where('expired_date', $detail->expired_date)
                ->where('whs_id', $detail->whs_id)
                ->first();

            if ($row) {
                $row->qty_available += $detail->qty_receive;
                $row->updated_user = $user->username;
                $row->updated_at = $datestamp;
                $row->save();
            } else {
                MsVplProductDetail::create([
                    'product_id' => $detail->product_id,
                    'expired_date' => $detail->expired_date,
                    'cpnyid' => $receive->cpnyid,
                    'qty_available' => $detail->qty_receive,
                    'qty_reserved' => 0,
                    'whs_id' => $detail->whs_id,
                    'status' => 'A',
                    'created_user' => $receive->created_user,
                    'created_at' => $receive->created_at,
                    'updated_user' => $user->username,
                    'updated_at' => $datestamp,
                ]);
            }
        }
    }
}
