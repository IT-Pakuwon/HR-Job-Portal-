<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Http\Controllers\Traits\UploadsVplAttachment;
use App\Models\Attachment;
use App\Models\MsCategory;
use App\Models\MsVplProduct;
use App\Models\MsVplProductDetail;
use App\Models\MsVplWarehouseDept;
use App\Models\TrApproval;
use App\Models\TrMessage;
use App\Models\TrxVplSettlement;
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
    use UploadsVplAttachment;

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

        // "Usage All" — admin-only, system-wide view (ignores company/department
        // scoping) with its own Type/Doctype/Status dropdown filters. Every other tab
        // keeps admin scoped to their own company/department just like any other user.
        $isAdmin = $user->isPrimaryAdmin();

        // DIRECTORACCESS sees every transaction unscoped everywhere, on every tab —
        // distinct from the admin-only "Usage All" tab, which stays admin-exclusive.
        $hasFullScope = $user->hasFullDataScope();

        if ($request->ajax()) {
            $status = $request->input('status', 'ALL');
            $adminAll = $isAdmin && $status === 'ADMINALL';

            $base = TrxVplUsage::query();
            if (!$adminAll && !$hasFullScope) {
                $base->whereIn('cpnyid', $multicpnyid)->whereIn('department', $multidept);
            }

            if ($adminAll) {
                if ($request->filled('filter_vp_type')) {
                    $base->where('vp_type', $request->filter_vp_type);
                }
                if ($request->filled('filter_doctype')) {
                    $base->where('usagetype', $request->filter_doctype);
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

        // Status count cards — scoped to the user's own company/department, admin
        // included; DIRECTORACCESS holders count across everything instead.
        $qCount = $hasFullScope
            ? TrxVplUsage::query()
            : TrxVplUsage::query()->whereIn('cpnyid', $multicpnyid)->whereIn('department', $multidept);
        $counts = [
            'all' => (clone $qCount)->count(),
            'progress' => (clone $qCount)->where('status', 'P')->count(),
            'completed' => (clone $qCount)->where('status', 'C')->count(),
            'rejected' => (clone $qCount)->where('status', 'R')->count(),
            'cancelled' => (clone $qCount)->where('status', 'X')->count(),
            'hold' => (clone $qCount)->where('status', 'D')->count(),
        ];

        // "Usage All" card — system-wide total, admin-only.
        if ($isAdmin) {
            $counts['admin_all'] = TrxVplUsage::count();
        }

        $usercpny = Usercpny::where('username', $user->username)->get();
        $usercpny2 = Usercpny::where('username', $user->username)->first();
        $userdept = Userdept::where('username', $user->username)->get();
        $userdept2 = Userdept::where('username', $user->username)->first();

        $initialId = $eid ? (Hashids::decode($eid)[0] ?? null) : null;

        $purposes = MsCategory::where('doctype', self::DOCTYPE)
            ->where('categoryid', 'type')
            ->where('groups', 'PURPOSE')
            ->where('status', 'A')
            ->pluck('category_name');

        return view('pages.voucher_product.usage', compact(
            'user', 'usercpny', 'usercpny2', 'userdept', 'userdept2', 'counts', 'initialId', 'purposes'
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
        // A VPACCESS holder can create a Usage doc outside their own scope (see store()),
        // so let the creator keep viewing/tracking it even though it falls outside their
        // normal company/department access. Likewise, a user assigned as an approver on
        // this document (via TrApproval) needs to view it even outside their scope — the
        // approval dashboard links them straight here regardless of membership.
        $isAssignedApprover = TrApproval::where('refnbr', $usage->usage_id)
            ->where('aprv_doctype', self::DOCTYPE)
            ->where('status', '<>', 'X')
            ->where(function ($q) use ($user) {
                $q->where('aprv_username', $user->username)
                  ->orWhere('aprv_username', 'like', '%' . $user->username . '%');
            })
            ->exists();
        if (!$this->hasDepartmentAccess($user, $usage->cpnyid, $usage->department)
            && $usage->created_user !== $user->name
            && !$isAssignedApprover
            && !$user->hasFullDataScope()) {
            return response()->json(['error' => 'You do not have access to view this document.'], 403);
        }

        $details = TrxVplUsageDetail::join('ms_vpl_product', 'tr_vpl_usage_detail.product_id', '=', 'ms_vpl_product.product_id')
            ->select('tr_vpl_usage_detail.*', 'ms_vpl_product.product_name', 'ms_vpl_product.product_uom')
            ->where('usage_id', $usage->usage_id)
            ->orderBy('linenbr')
            ->get();

        $approvals = TrApproval::where('refnbr', $usage->usage_id)
            ->where('aprv_doctype', self::DOCTYPE)
            ->where('status', '<>', 'X')
            ->orderBy('created_at', 'asc')
            ->orderByRaw('CAST(aprv_leveling AS numeric) ASC')
            ->get();

        $attachments = Attachment::where('docid', $usage->usage_id)->where('status', 'A')->get();
        $messages = TrMessage::where('refnbr', $usage->usage_id)->where('doctype', self::DOCTYPE)->orderBy('created_at', 'asc')->get();

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

        // VPACCESS lets a user (e.g. HR) create a Usage doc for a company/department
        // they don't otherwise belong to; everyone else needs normal membership.
        if (!$user->hasRole('VPACCESS') && !$this->hasDepartmentAccess($user, $request->cpnyid, $request->department)) {
            return response()->json(['error' => 'You do not have access to create a Usage document for this company/department.'], 403);
        }

        if ($usagetype === 'Return' && empty($request->ref_usage_id)) {
            return response()->json(['error' => 'Reference Usage Doc is required for Return.'], 422);
        }

        // A settlement still in flight (Pending/Hold) locks out Returns entirely — its
        // qty_settlement is provisional and could still change or get rejected. Once
        // it's Completed, a Return is allowed again for whatever qty is still
        // returnable — returnableUsageQty() (used below, per line) computes that off
        // qty_return_usage rather than the shared qty_settlement field, so it stays
        // correct regardless of settlement state.
        if ($usagetype === 'Return' && TrxVplSettlement::where('usage_id', $request->ref_usage_id)->whereIn('status', ['P', 'D'])->exists()) {
            return response()->json(['error' => 'Cannot return: the referenced Usage document has a settlement in progress.'], 422);
        }

        if (!$request->filled('usage_remark')) {
            return response()->json(['error' => 'Remark is required.'], 422);
        }

        // CUSTOMERSERVICE usage/return docs have no supporting document to attach
        // (no event of their own — see the event_date exemption below), so attachment
        // is optional for them; every other department must attach proof of usage.
        if ($request->department !== 'CUSTOMERSERVICE') {
            $hasValidAttachment = collect($request->file('attachment', []))->filter(fn ($f) => $f && $f->isValid())->isNotEmpty();
            if (!$hasValidAttachment) {
                return response()->json(['error' => 'Attachment is required.'], 422);
            }
        }

        // Every department other than CUSTOMERSERVICE must record the date of the
        // event the usage relates to; CUSTOMERSERVICE has no such event and uses
        // usage_date (backdate) instead. Unlike usage_date, event_date can never be
        // backdated — it can only be today or in the future. Not applicable to
        // Return Usage — a return has no event of its own to date.
        if ($request->department !== 'CUSTOMERSERVICE' && $usagetype !== 'Return') {
            if (!$request->filled('event_date')) {
                return response()->json(['error' => 'Event Date is required.'], 422);
            }
            if (Carbon::parse($request->event_date)->startOfDay()->lt($dt->copy()->startOfDay())) {
                return response()->json(['error' => 'Event Date cannot be backdated.'], 422);
            }
        }

        // CUSTOMERSERVICE Usage/Return docs may be backdated up to H-3 (e.g. logging
        // usage recorded late); every other department is always dated "today".
        $usageDate = $dt->copy();
        if ($request->department === 'CUSTOMERSERVICE' && in_array($usagetype, ['Usage', 'Return'], true) && $request->filled('usage_date')) {
            $usageDate = Carbon::parse($request->usage_date)->startOfDay();
            $today = $dt->copy()->startOfDay();
            if ($usageDate->lt($today->copy()->subDays(3)) || $usageDate->gt($today)) {
                return response()->json(['error' => 'Usage Date must be within H-3 to today.'], 422);
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

        // Validate qty per line before touching anything: Usage lines can't exceed
        // pickable warehouse stock, Return lines can't exceed the origin line's
        // remaining returnable qty. A running per-key claim total catches duplicate
        // lines in one request that would otherwise each pass independently and
        // jointly over-claim.
        if ($request->has('addmore')) {
            $claimTracker = [];
            foreach ($request->addmore as $detail) {
                if (empty($detail['product_id']) || empty($detail['qty']) || empty($detail['whs_id'])) {
                    continue;
                }
                if (empty($detail['purpose_id'])) {
                    $productName = MsVplProduct::where('product_id', $detail['product_id'])->value('product_name') ?? $detail['product_id'];
                    return response()->json(['error' => 'Purpose is required for '.$productName.'.'], 422);
                }
                $exp     = $detail['expired_date'] ?: '1900-01-01';
                $qty     = (float) $detail['qty'];
                $key     = $detail['product_id'].'|'.$exp.'|'.$detail['whs_id'];
                $claimed = $claimTracker[$key] ?? 0;

                if ($usagetype === 'Usage') {
                    $pickable = $this->pickableQty($detail['product_id'], $exp, $detail['whs_id']);
                    if ($qty + $claimed > $pickable) {
                        $productName = MsVplProduct::where('product_id', $detail['product_id'])->value('product_name') ?? $detail['product_id'];
                        return response()->json(['error' => 'Usage qty for '.$productName.' exceeds available quantity ('.max(0, $pickable - $claimed).').'], 422);
                    }
                } else {
                    $remaining = $this->returnableUsageQty($request->ref_usage_id, $detail['product_id'], $exp, $detail['whs_id']);
                    if ($qty + $claimed > $remaining) {
                        $productName = MsVplProduct::where('product_id', $detail['product_id'])->value('product_name') ?? $detail['product_id'];
                        return response()->json(['error' => 'Return qty for '.$productName.' exceeds the remaining returnable qty ('.max(0, $remaining - $claimed).').'], 422);
                    }
                }

                $claimTracker[$key] = $claimed + $qty;
            }
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
            DB::connection('pgsql5')->transaction(function () use ($request, $user, $dt, $usageDate, $docid, $vp_type, $usagetype, $ctx, &$usage) {
                $usage = TrxVplUsage::create([
                    'usage_id' => $docid,
                    'usage_date' => $usageDate->format('Y-m-d'),
                    'event_date' => $request->filled('event_date') ? $request->event_date : null,
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

                // Throws if no approval rule matches, rolling back the whole document
                // so it's never left without an approval chain.
                app(ApprovalController::class)->generateForDocument(
                    $docid,
                    self::DOCTYPE,
                    $request->cpnyid,
                    $request->department,
                    $user->username,
                    $ctx,
                    $dt
                );
            });
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        // Send notification after the transaction commits so a mail failure doesn't roll back the document
        app(ApprovalController::class)->notifyFirstApprover(
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

        if (!$request->filled('usage_remark')) {
            return response()->json(['error' => 'Remark is required.'], 422);
        }

        if ($usage->department !== 'CUSTOMERSERVICE') {
            $existingAttachCount = Attachment::where('docid', $usage->usage_id)->where('status', 'A')->count();
            $hasValidAttachment = collect($request->file('attachment', []))->filter(fn ($f) => $f && $f->isValid())->isNotEmpty();
            if ($existingAttachCount === 0 && !$hasValidAttachment) {
                return response()->json(['error' => 'Attachment is required.'], 422);
            }
        }

        if ($usage->department !== 'CUSTOMERSERVICE' && $usage->usagetype !== 'Return') {
            if (!$request->filled('event_date')) {
                return response()->json(['error' => 'Event Date is required.'], 422);
            }
            if (Carbon::parse($request->event_date)->startOfDay()->lt($dt->copy()->startOfDay())) {
                return response()->json(['error' => 'Event Date cannot be backdated.'], 422);
            }
        }

        if ($usage->usagetype === 'Return' && TrxVplSettlement::where('usage_id', $usage->ref_usage_id)->whereIn('status', ['P', 'D'])->exists()) {
            return response()->json(['error' => 'Cannot return: the referenced Usage document has a settlement in progress.'], 422);
        }

        $conditionName = $this->resolveConditionName($usage->vp_type, $usage->usagetype);
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
            DB::connection('pgsql5')->transaction(function () use ($request, $user, $dt, $usage, $ctx) {
                // update() is only reachable from status D, and revise() always released
                // the hold for every line the document had at that point (adjustReservation
                // -1). Restore it here for whatever survives before any new lines are added
                // below, otherwise a resubmit that doesn't touch existing lines leaves them
                // permanently unreserved even though they still carry real qty_usage.
                //
                // For Usage, re-validate against current pickable stock first — while this
                // doc sat on hold with its claim released, another document could have
                // legitimately claimed that same stock. Restoring blindly would push
                // qty_reserved past qty_available, which silently hides the product from
                // the Add Product picker for everyone (pickableQty()/getUsageProducts()
                // treat available-minus-reserved <= 0 as "nothing to pick").
                $existingDetails = TrxVplUsageDetail::where('usage_id', $usage->usage_id)->get();
                if ($usage->usagetype === 'Usage') {
                    $restoreClaim = [];
                    foreach ($existingDetails as $detail) {
                        if ($detail->qty_usage <= 0) {
                            continue;
                        }
                        $key = $detail->product_id.'|'.$detail->expired_date.'|'.$detail->whs_id;
                        $claimed = $restoreClaim[$key] ?? 0;
                        $pickable = $this->pickableQty($detail->product_id, $detail->expired_date, $detail->whs_id);
                        if ($detail->qty_usage + $claimed > $pickable) {
                            $productName = MsVplProduct::where('product_id', $detail->product_id)->value('product_name') ?? $detail->product_id;
                            throw new \RuntimeException('Cannot resubmit: '.$productName.' no longer has enough available stock ('.max(0, $pickable - $claimed).' left) — it was likely claimed by another document while this one was on hold.');
                        }
                        $restoreClaim[$key] = $claimed + $detail->qty_usage;
                    }
                }
                $existingDetails->each(fn ($detail) => $this->reserveDetail($detail, $usage->usagetype, +1));

                if ($request->has('addmore')) {
                    // Validate against pickable/returnable qty only AFTER the restore above —
                    // otherwise this check sees the surviving existing lines as still
                    // unreserved (revise() released them) and lets a new line double-claim
                    // the same stock, pushing qty_reserved past qty_available once both
                    // land. Same two-phase intent as store(), just re-anchored to the
                    // post-restore snapshot since update() has existing lines to restore.
                    $claimTracker = [];
                    foreach ($request->addmore as $detail) {
                        if (empty($detail['product_id']) || empty($detail['qty']) || empty($detail['whs_id'])) {
                            continue;
                        }
                        if (empty($detail['purpose_id'])) {
                            $productName = MsVplProduct::where('product_id', $detail['product_id'])->value('product_name') ?? $detail['product_id'];
                            throw new \RuntimeException('Purpose is required for '.$productName.'.');
                        }
                        $exp     = $detail['expired_date'] ?: '1900-01-01';
                        $qty     = (float) $detail['qty'];
                        $key     = $detail['product_id'].'|'.$exp.'|'.$detail['whs_id'];
                        $claimed = $claimTracker[$key] ?? 0;

                        if ($usage->usagetype === 'Usage') {
                            $pickable = $this->pickableQty($detail['product_id'], $exp, $detail['whs_id']);
                            if ($qty + $claimed > $pickable) {
                                $productName = MsVplProduct::where('product_id', $detail['product_id'])->value('product_name') ?? $detail['product_id'];
                                throw new \RuntimeException('Usage qty for '.$productName.' exceeds available quantity ('.max(0, $pickable - $claimed).').');
                            }
                        } else {
                            $remaining = $this->returnableUsageQty($usage->ref_usage_id, $detail['product_id'], $exp, $detail['whs_id'], $usage->id);
                            if ($qty + $claimed > $remaining) {
                                $productName = MsVplProduct::where('product_id', $detail['product_id'])->value('product_name') ?? $detail['product_id'];
                                throw new \RuntimeException('Return qty for '.$productName.' exceeds the remaining returnable qty ('.max(0, $remaining - $claimed).').');
                            }
                        }

                        $claimTracker[$key] = $claimed + $qty;
                    }

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

                if (TrxVplUsageDetail::where('usage_id', $usage->usage_id)->count() === 0) {
                    throw new \RuntimeException('At least one detail line is required.');
                }

                $this->saveAttachments($request, $usage->usage_id, $dt->year, $user);

                // update() only runs on a revised (Hold) document, so the previous approval
                // cycle's rows (Approved/Revised) are stale leftovers. Cancel them first so
                // generateForDocument()'s fresh chain doesn't sit alongside them and show as
                // duplicate levels in the workflow panel.
                // TrApproval::where('refnbr', $usage->usage_id)
                //     ->where('aprv_doctype', self::DOCTYPE)
                //     ->where('status', '<>', 'X')
                //     ->update(['status' => 'X']);

                // Throws if no approval rule matches, rolling back the detail/reservation
                // changes so the document isn't left without an approval chain.
                app(ApprovalController::class)->generateForDocument(
                    $usage->usage_id,
                    self::DOCTYPE,
                    $request->cpnyid ?? $usage->cpnyid,
                    $request->department ?? $usage->department,
                    $user->username,
                    $ctx,
                    $dt
                );

                $usage->usage_remark = $request->usage_remark ?? $usage->usage_remark;
                if ($request->filled('event_date')) {
                    $usage->event_date = $request->event_date;
                }
                $usage->status = 'P';
                $usage->updated_user = $user->name;
                $usage->updated_at = $dt->toDateTimeString();
                $usage->save();
            });
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        app(ApprovalController::class)->notifyFirstApprover(
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
                // Wrapped so a mid-loop stock failure can't leave the header marked
                // Completed while only some lines' stock actually moved. Both 'Usage'
                // and 'Return' delegate ledger/balance/qty_available to sp_process_vpl;
                // only the qty_reserved hold-release (Usage) stays direct-PHP, since
                // the SP never touches qty_reserved for any doctype. Return already
                // released its hold at creation (adjustReservation in store()/update()),
                // so a completed Return needs no further qty_reserved write — the
                // restocked qty_available it gets from the SP is immediately pickable.
                DB::connection('pgsql5')->transaction(function () use ($usage, $user, $now) {
                    $usage->status = 'C';
                    $usage->completed_user = $user->username;
                    $usage->completed_at = $now;
                    $usage->save();

                    if ($usage->usagetype === 'Usage') {
                        $this->adjustReservation($usage->usage_id, -1);
                    }

                    DB::connection('pgsql5')->statement(
                        'CALL sp_process_vpl(?, ?, ?, ?, ?)',
                        ['VPU', $usage->usage_id, $usage->cpnyid, 'Submit', $user->username]
                    );
                });
            },
            function ($next, $now) use ($usage, $id) {
                app(ApprovalController::class)->notifyFirstApprover(
                    $usage->usage_id,
                    self::DOCTYPE,
                    'P',
                    self::DOCTYPE_DSC,
                    route('usagevp.show', $id),
                    ['info' => $usage->usage_remark ?? '', 'createdby' => $usage->created_user]
                );
            },
            function ($refnbr, $now) use ($usage, $id) {
                // Notify requester the document is fully approved
                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $usage->usage_id,
                    self::DOCTYPE_DSC,
                    'C',
                    $usage->user_peminta,
                    route('usagevp.show', $id),
                    ['cpnyid' => $usage->cpnyid, 'deptname' => $usage->department]
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
                DB::connection('pgsql5')->transaction(function () use ($usage, $now) {
                    $usage->status = 'R';
                    $usage->save();
                    $this->adjustReservation($usage->usage_id, -1);
                });
                $this->saveMessage($usage, $request->message, $user);
                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $usage->usage_id,
                    self::DOCTYPE_DSC,
                    'R',
                    $usage->user_peminta,
                    route('usagevp.show', $id),
                    ['info' => $request->message, 'cpnyid' => $usage->cpnyid, 'deptname' => $usage->department]
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
                DB::connection('pgsql5')->transaction(function () use ($usage, $user, $now) {
                    $usage->status = 'D';
                    $usage->updated_user = $user->name;
                    $usage->updated_at = $now;
                    $usage->save();
                    // Unlike Transfer, Usage also releases the pending hold on revise —
                    // the creator may substantially change lines before resubmitting.
                    $this->adjustReservation($usage->usage_id, -1);
                });
                $this->saveMessage($usage, $request->message, $user);
                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $usage->usage_id,
                    self::DOCTYPE_DSC,
                    'D',
                    $usage->user_peminta,
                    route('usagevp.show', $id),
                    ['info' => $request->message.' (Silahkan revisi dokumen ini)', 'cpnyid' => $usage->cpnyid, 'deptname' => $usage->department]
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

        if (!$usage) {
            return response()->json(['error' => 'Not found.'], 404);
        }

        if (!$this->canCancel($usage, $user)) {
            return response()->json(['error' => 'You are not allowed to cancel this document.'], 403);
        }

        DB::connection('pgsql5')->transaction(function () use ($usage, $user) {
            $this->adjustReservation($usage->usage_id, -1);

            $usage->status = 'X';
            $usage->updated_user = $user->name;
            $usage->save();
        });

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

    /**
     * Aggregated per-product stock list for the "Select Product" picker.
     * Batches are summed across expiry — the user picks a product + total
     * qty, and pickFefoStock() works out which batches to draw from.
     */
    public function getUsageProducts(Request $request)
    {
        $vp_type = strtoupper($request->vp_type);
        $whsId = $request->whs_id;

        $products = MsVplProduct::join('ms_vpl_product_detail', 'ms_vpl_product.product_id', '=', 'ms_vpl_product_detail.product_id')
            ->select(
                'ms_vpl_product.product_id',
                'ms_vpl_product.product_name',
                'ms_vpl_product.product_value',
                'ms_vpl_product.product_uom',
                DB::raw('SUM(ms_vpl_product_detail.qty_available) AS qty_available'),
                DB::raw('SUM(COALESCE(ms_vpl_product_detail.qty_reserved, 0)) AS qty_reserved'),
                DB::raw('SUM(ms_vpl_product_detail.qty_available - COALESCE(ms_vpl_product_detail.qty_reserved, 0)) AS qty_pickable'),
                DB::raw('MIN(ms_vpl_product_detail.expired_date) AS nearest_expired_date')
            )
            ->where('ms_vpl_product.cpnyid', $request->cpnyid)
            ->where('ms_vpl_product.product_type', $vp_type)
            ->where('ms_vpl_product_detail.whs_id', $whsId)
            ->groupBy('ms_vpl_product.product_id', 'ms_vpl_product.product_name', 'ms_vpl_product.product_value', 'ms_vpl_product.product_uom')
            ->havingRaw('SUM(ms_vpl_product_detail.qty_available - COALESCE(ms_vpl_product_detail.qty_reserved, 0)) > 0')
            ->orderBy('ms_vpl_product.product_id')
            ->get();

        $products->transform(fn ($p) => tap($p, fn ($p) => $p->product_name = $p->product_name.' / '.number_format($p->product_value, 0, ',', '.').' / '.$p->product_uom
        ));

        return response()->json($products);
    }

    /**
     * FEFO auto-split: given a product + desired total qty, draws from the
     * nearest-expiry batch first, only moving to the next batch once the
     * current one is exhausted. Returns the per-batch breakdown (no DB
     * writes — Usage builds its detail lines client-side until submit).
     */
    public function pickFefoStock(Request $request)
    {
        $vp_type = strtoupper($request->vp_type);
        $whsId = $request->whs_id;
        $productId = $request->product_id;
        $qtyNeeded = (float) $request->qty;

        if ($qtyNeeded <= 0) {
            return response()->json(['error' => 'Qty must be greater than 0.'], 422);
        }

        $product = MsVplProduct::where('product_id', $productId)
            ->where('cpnyid', $request->cpnyid)
            ->where('product_type', $vp_type)
            ->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found.'], 404);
        }

        $productName = trim($product->product_name.' / '.number_format($product->product_value, 0, ',', '.').' / '.$product->product_uom);

        // Qty per expiry date already staged in the current (unsubmitted) draft for
        // this product/warehouse — sent by the client from its "Added to this
        // document" rows. Without this, a second Add-Product pass for the same
        // product recomputes FEFO off unchanged DB stock (nothing is reserved until
        // the document is actually saved) and happily re-offers a batch the first
        // pass already claimed, letting the draft silently over-claim it.
        $staged = [];
        if ($request->filled('staged')) {
            $decoded = json_decode($request->staged, true);
            if (is_array($decoded)) {
                $staged = $decoded;
            }
        }

        $batches = MsVplProductDetail::where('product_id', $productId)
            ->where('whs_id', $whsId)
            ->orderBy('expired_date', 'ASC')
            ->get();

        $remaining = $qtyNeeded;
        $breakdown = [];

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }
            $expKey = $batch->expired_date->format('Y-m-d');
            $pickable = $batch->qty_available - ($batch->qty_reserved ?? 0) - (float) ($staged[$expKey] ?? 0);
            if ($pickable <= 0) {
                continue;
            }
            $take = min($pickable, $remaining);
            $breakdown[] = [
                'product_id' => $productId,
                'product_name' => $productName,
                'expired_date' => $batch->expired_date->format('Y-m-d'),
                'whs_id' => $batch->whs_id,
                'qty' => $take,
                'qty_stock' => $batch->qty_available,
                'qty_available' => $pickable,
            ];
            $remaining -= $take;
        }

        if ($remaining > 0) {
            return response()->json(['error' => 'Insufficient stock for '.$productName.'. Short by '.$remaining.'.'], 422);
        }

        return response()->json($breakdown);
    }

    /**
     * Completed Usage docs eligible as a Return's reference — excludes any doc
     * that's already fully returned (nothing left for returnableUsageQty() to give
     * out), since offering it would only lead to getReturnRefDetails() coming back
     * empty. Aggregated at doc level (total qty_usage vs total qty_return_usage
     * already claimed by sibling P/C Return docs) rather than checked per line —
     * safe because store()/update() never let a single line's returns exceed its
     * own qty_usage, so summing across lines can't hide a still-returnable one.
     */
    public function getReturnRefOptions(Request $request)
    {
        $usageIds = TrxVplUsage::where('status', 'C')
            ->where('usagetype', 'Usage')
            ->where('cpnyid', $request->cpnyid)
            ->where('department', $request->department)
            ->where('vp_type', strtoupper($request->vp_type))
            ->orderByDesc('usage_date')
            ->pluck('usage_id');

        if ($usageIds->isEmpty()) {
            return response()->json([]);
        }

        $totalUsage = TrxVplUsageDetail::whereIn('usage_id', $usageIds)
            ->selectRaw('usage_id, SUM(qty_usage) as total')
            ->groupBy('usage_id')
            ->pluck('total', 'usage_id');

        $totalReturned = TrxVplUsageDetail::join('tr_vpl_usage', 'tr_vpl_usage_detail.usage_id', '=', 'tr_vpl_usage.usage_id')
            ->whereIn('tr_vpl_usage_detail.ref_usage_id', $usageIds)
            ->whereIn('tr_vpl_usage.status', ['P', 'C'])
            ->selectRaw('tr_vpl_usage_detail.ref_usage_id, SUM(tr_vpl_usage_detail.qty_return_usage) as total')
            ->groupBy('tr_vpl_usage_detail.ref_usage_id')
            ->pluck('total', 'ref_usage_id');

        $refs = $usageIds->filter(fn ($usageId) => (float) ($totalUsage[$usageId] ?? 0) - (float) ($totalReturned[$usageId] ?? 0) > 0)
            ->values();

        return response()->json($refs);
    }

    /**
     * Detail lines of a Usage doc still eligible for Return, with the true
     * remaining-returnable qty (returnableUsageQty() — counts sibling Return docs
     * with status P or C, not just qty_settlement) so the picker's displayed/max
     * qty always matches what store()/update() will actually accept.
     */
    public function getReturnRefDetails(Request $request)
    {
        $refUsageId = $request->ref_usage_id;
        if (!$refUsageId) {
            return response()->json([]);
        }

        $excludeId = $request->exclude_usage_id ? (int) $request->exclude_usage_id : null;

        $origLines = TrxVplUsageDetail::join('ms_vpl_product', 'tr_vpl_usage_detail.product_id', '=', 'ms_vpl_product.product_id')
            ->select('tr_vpl_usage_detail.*', 'ms_vpl_product.product_name')
            ->where('tr_vpl_usage_detail.usage_id', $refUsageId)
            ->orderBy('tr_vpl_usage_detail.linenbr')
            ->get()
            ->unique(fn ($l) => $l->product_id.'|'.$l->expired_date.'|'.$l->whs_id);

        $result = [];
        foreach ($origLines as $line) {
            $exp = $line->expired_date->format('Y-m-d');
            $remaining = $this->returnableUsageQty($refUsageId, $line->product_id, $exp, $line->whs_id, $excludeId);
            if ($remaining <= 0) {
                continue;
            }
            $line->qty_remaining = $remaining;
            $result[] = $line;
        }

        return response()->json($result);
    }

    // -------------------------------------------------------
    // PRIVATE HELPERS
    // -------------------------------------------------------
    /**
     * Cancel: creator only, and only while still Hold (D) or On Progress with
     * nothing yet approved (P and no 'A' rows) — mirrors the can_cancel flag
     * showData() sends the UI, re-checked here since the client can't be trusted.
     */
    private function canCancel(TrxVplUsage $usage, $user): bool
    {
        $anyApproved = TrApproval::where('refnbr', $usage->usage_id)
            ->where('aprv_doctype', self::DOCTYPE)
            ->where('status', 'A')
            ->exists();

        return $usage->created_user === $user->name
            && ($usage->status === 'D' || ($usage->status === 'P' && !$anyApproved));
    }

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
        $this->saveVplAttachments($request, $docid, 'att-vpl/vpu-attachment', $year, $user);
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
     * Current pickable qty (available - already reserved) for a product/expiry/warehouse.
     */
    private function pickableQty(string $productId, string $expiredDate, string $whsId): float
    {
        $stock = MsVplProductDetail::where('product_id', $productId)
            ->where('expired_date', $expiredDate)
            ->where('whs_id', $whsId)
            ->first();

        if (!$stock) {
            return 0;
        }

        return (float) $stock->qty_available - (float) ($stock->qty_reserved ?? 0);
    }

    /**
     * How much of a product/expiry/warehouse line from a Usage doc can still be
     * returned: qty_usage minus everything already claimed by OTHER Return Usage
     * documents referencing it (status P or C — a pending return already holds its
     * claim, mirroring VplTransferController::returnableQty()). Unlike the older
     * qty_usage - qty_settlement check, this counts pending (not just completed)
     * sibling returns, so two returns submitted before either is approved can't
     * jointly claim more than the line ever had. $excludeUsageDbId excludes a
     * document's own detail rows from the "already claimed" sum, so re-opening
     * that document for edit doesn't count its own lines against itself.
     */
    private function returnableUsageQty(string $refUsageId, string $productId, string $expiredDate, string $whsId, ?int $excludeUsageDbId = null): float
    {
        $originalQty = (float) TrxVplUsageDetail::where('usage_id', $refUsageId)
            ->where('product_id', $productId)
            ->where('expired_date', $expiredDate)
            ->where('whs_id', $whsId)
            ->sum('qty_usage');

        $returnedQty = (float) TrxVplUsageDetail::join('tr_vpl_usage', 'tr_vpl_usage_detail.usage_id', '=', 'tr_vpl_usage.usage_id')
            ->where('tr_vpl_usage_detail.ref_usage_id', $refUsageId)
            ->where('tr_vpl_usage_detail.product_id', $productId)
            ->where('tr_vpl_usage_detail.expired_date', $expiredDate)
            ->where('tr_vpl_usage_detail.whs_id', $whsId)
            ->whereIn('tr_vpl_usage.status', ['P', 'C'])
            ->when($excludeUsageDbId, fn ($q) => $q->where('tr_vpl_usage.id', '<>', $excludeUsageDbId))
            ->sum('tr_vpl_usage_detail.qty_return_usage');

        return max(0, $originalQty - $returnedQty);
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

}
