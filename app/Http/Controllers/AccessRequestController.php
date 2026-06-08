<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsCategory;
use App\Models\TrAccess;
use App\Models\TrAccessDetail;
use App\Models\TrApproval;
use App\Models\TrAttachment;
use App\Models\TrMessage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Vinkla\Hashids\Facades\Hashids;

class AccessRequestController extends Controller
{
    use HasAutonbr;

    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (is_string($user->cpny_id)) {
            $cpnyIds = array_map('trim', explode(',', $user->cpny_id));
        } else {
            $cpnyIds = (array) $user->cpny_id;
        }

        if (is_string($user->department_id)) {
            $deptIds = array_map('trim', explode(',', $user->department_id));
        } else {
            $deptIds = (array) $user->department_id;
        }

        $companies = is_string($user->cpny_id)
            ? array_map('trim', explode(',', $user->cpny_id))
            : (array) $user->cpny_id;

        $departments = is_string($user->department_id)
            ? array_map('trim', explode(',', $user->department_id))
            : (array) $user->department_id;

        $allQuery = TrAccess::where('status', '<>', 'X');

        if (
            !$user->hasRole('ITHARDWARE')
            && !$user->hasRole('ITSOFTWARE')
        ) {
            $allQuery->whereIn('cpny_id', $cpnyIds)
                ->whereIn('department_id', $deptIds);
        }

        $all = $allQuery->count();

        $isItRole = $user->hasRole('ITHARDWARE') || $user->hasRole('ITSOFTWARE');

        $pendingQuery = TrAccess::where('status', 'P');
        $completedQuery = TrAccess::where('status', 'C');
        $rejectQuery = TrAccess::where('status', 'R');
        $reviseQuery = TrAccess::where('status', 'D');
        $finishedQuery = TrAccess::where('status', 'F');

        if (!$isItRole) {
            $pendingQuery->whereIn('cpny_id', $cpnyIds)->whereIn('department_id', $deptIds);
            $completedQuery->whereIn('cpny_id', $cpnyIds)->whereIn('department_id', $deptIds);
            $rejectQuery->whereIn('cpny_id', $cpnyIds)->whereIn('department_id', $deptIds);
            $reviseQuery->whereIn('cpny_id', $cpnyIds)->whereIn('department_id', $deptIds);
            $finishedQuery->whereIn('cpny_id', $cpnyIds)->whereIn('department_id', $deptIds);
        }

        $pending = $pendingQuery->count();
        $completed = $completedQuery->count();
        $reject = $rejectQuery->count();
        $revise = $reviseQuery->count();
        $finished = $finishedQuery->count();

        $modalType = null;
        $modalAccess = null;

        if (request()->is('processhardwareaccess/*')) {

            $modalType = 'process-hardware';

            $modalAccess = request()->route('eid');

        }

        if (request()->is('processsoftwareaccess/*')) {

            $modalType = 'process-software';

            $modalAccess = request()->route('eid');

        }

        if (request()->is('showaccessrequest/*')) {

            $modalType = 'detail';

            $modalAccess = request()->route('eid');

        }

        if (request()->is('editaccessrequest/*')) {

            $modalType = 'edit';

            $modalAccess = request()->route('eid');

        }

        return view('pages.access-requests.access-requests', compact(
            'all',
            'pending',
            'completed',
            'reject',
            'revise',
            'finished',
            'companies',
            'departments',
            'modalType',
            'modalAccess'
        ));
    }

    public function json(Request $request)
    {
        $user = Auth::user();

        if (is_string($user->cpny_id)) {
            $cpnyIds = array_map('trim', explode(',', $user->cpny_id));
        } else {
            $cpnyIds = (array) $user->cpny_id;
        }

        if (is_string($user->department_id)) {
            $deptIds = array_map('trim', explode(',', $user->department_id));
        } else {
            $deptIds = (array) $user->department_id;
        }

        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));
        $status = (string) $request->query('status', '');

        $baseTable = (new TrAccess())->getTable();

        $columns = [
            0 => 'ta.docid',
            1 => 'ta.access_date',
            2 => 'ta.cpny_id',
            3 => 'ta.department_id',
            4 => 'ta.user_peminta',
            5 => 'ta.user_assign',
            6 => 'ta.keperluan',
            7 => 'ta.access_type',
            8 => 'ta.status',
            9 => 'ta.created_at',
        ];

        $orderIdx = (int) $request->input('order.0.column', 0);

        $orderDir = $request->input('order.0.dir', 'desc') === 'asc'
            ? 'asc'
            : 'desc';

        $orderCol = $columns[$orderIdx] ?? 'ta.docid';

        $base = TrAccess::from($baseTable.' as ta')
            ->where('ta.status', '<>', 'X');

        if (
            !$user->hasRole('ITHARDWARE')
            && !$user->hasRole('ITSOFTWARE')
        ) {
            $base->whereIn('ta.cpny_id', $cpnyIds)
                ->whereIn('ta.department_id', $deptIds);
        }

        if ($status !== '') {
            $base->where('ta.status', $status);
        }

        $recordsTotal = (clone $base)
            ->distinct('ta.docid')
            ->count('ta.docid');

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('ta.docid', 'ilike', "%{$search}%")
                    ->orWhere('ta.cpny_id', 'ilike', "%{$search}%")
                    ->orWhere('ta.department_id', 'ilike', "%{$search}%")
                    ->orWhere('ta.user_peminta', 'ilike', "%{$search}%")
                    ->orWhere('ta.user_assign', 'ilike', "%{$search}%")
                    ->orWhere('ta.keperluan', 'ilike', "%{$search}%")
                    ->orWhere('ta.access_type', 'ilike', "%{$search}%")
                    ->orWhere('ta.status', 'ilike', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $base)
            ->distinct('ta.docid')
            ->count('ta.docid');

        $data = $base->select(
            'ta.id',
            'ta.docid',
            'ta.access_date',
            'ta.cpny_id',
            'ta.department_id',
            'ta.user_peminta',
            'ta.user_assign',
            'ta.keperluan',
            'ta.access_type',
            'ta.status',
            'ta.created_by',
            'ta.created_at'
        )
            ->orderBy($orderCol, $orderDir)
            ->orderBy('ta.docid', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        $data->transform(function ($row) {
            $user = Auth::user();

            $row->eid = Hashids::encode($row->id);

            $detailGroups = TrAccessDetail::where('docid', $row->docid)
                ->pluck('group_category')
                ->map(fn ($x) => strtoupper(trim($x)))
                ->unique()
                ->values();

            $row->groups = $detailGroups;

            $row->total_detail = TrAccessDetail::where('docid', $row->docid)
                ->count();

            $row->total_completed = TrAccessDetail::where('docid', $row->docid)
                ->where('status', 'C')
                ->count();

            $row->can_process_hardware =
                $row->status === 'C'
                && $user->hasRole('ITHARDWARE')
                && TrAccessDetail::where('docid', $row->docid)
                ->where('group_category', 'HARDWARE')
                ->where('status', 'P')
                ->exists();

            $row->can_process_software =
                $row->status === 'C'
                && $user->hasRole('ITSOFTWARE')
                && TrAccessDetail::where('docid', $row->docid)
                ->where('group_category', 'SOFTWARE')
                ->where('status', 'P')
                ->exists();

            unset($row->id);

            return $row;
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $doctype = 'ACR';

        $user = $request->user();

        $username = $user->username ?? 'system';

        $dt = Carbon::now();

        $year = (int) $dt->year;

        $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);

        $request->validate([
            'cpny_id' => ['required', 'string'],
            'department_id' => ['required', 'string'],
            'location_id' => ['nullable', 'string'],
            'keperluan' => ['required', 'string'],
            'access_type' => ['nullable', 'string'],
            'user_assign' => ['nullable', 'string'],

            'details' => ['required', 'array', 'min:1'],
            'details.*.categoryid' => ['required', 'string'],
        ]);

        DB::beginTransaction();

        try {
            $auto = $this->nextAutonbr(
                $doctype,
                $year,
                $month,
                $username,
                'SPB'
            );

            $urutan = (int) $auto['next'];

            $tglbln = substr((string) $year, 2).$month;

            $docid = $doctype.$tglbln.sprintf('%04d', $urutan);

            $access = new TrAccess();

            $access->docid = $docid;
            $access->access_date = $dt;
            $access->cpny_id = $request->cpny_id;
            $access->department_id = $request->department_id;
            $access->location_id = $request->location_id;
            $access->user_peminta = $username;
            $access->user_assign = $request->user_assign;
            $access->keperluan = $request->keperluan;
            $access->access_type = $request->access_type;
            $access->status = 'P';
            $access->created_by = $username;

            $access->save();

            $approvalConditions = [];

            foreach ($request->details as $row) {
                $category = MsCategory::where('doctype', 'ACR')
                    ->where('categoryid', $row['categoryid'])
                    ->where('status', 'A')
                    ->first();

                if (!$category) {
                    throw new \Exception('Category not found : '.$row['categoryid']);
                }

                TrAccessDetail::create([
                    'docid' => $docid,

                    'access_id' => $category->categoryid,

                    'access_descr' => $category->category_name,

                    'access_response' => null,

                    'access_username' => null,

                    'access_password' => null,

                    // process flag
                    'access_process' => 'f',

                    // approval complete nanti isi
                    'access_startdate' => null,

                    // IT complete nanti isi
                    'access_enddate' => null,

                    // IT PIC nanti isi
                    'access_pic' => null,

                    'group_category' => strtoupper(
                        trim($category->groups)
                    ),

                    'status' => 'P',

                    'created_by' => $username,

                    'updated_by' => $username,
                ]);

                $approvalConditions[] = strtoupper(
                    trim($category->groups)
                );
            }

            $approvalConditions = collect($approvalConditions)
                ->unique()
                ->values()
                ->toArray();

            $approvalCtl = app(ApprovalController::class);

            $approvalCtl->loadLines(
                $doctype,
                $request->cpny_id,
                $request->department_id
            );

            $ctx = [
                'approval_conditions' => $approvalConditions,
            ];

            [$firstApprovalUsernames, $linesCount] =
                $approvalCtl->generateForDocument(
                    $docid,
                    $doctype,
                    $request->cpny_id,
                    $request->department_id,
                    $username,
                    $ctx,
                    $dt
                );

            $uploadResult = null;

            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr' => $docid,
                    'doctype' => $doctype,
                    'cpnyid' => $request->cpny_id,
                    'departementid' => $request->department_id,
                    'base_folder' => 'att-access-request/'.strtolower($doctype),
                    'created_by' => $username,
                ];

                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);

                    $uploadResult = $uploader->uploadInternal(
                        $meta,
                        $files
                    );
                } catch (\Throwable $e) {
                    DB::rollBack();

                    return response()->json([
                        'message' => 'Failed upload attachment',
                        'error' => $e->getMessage(),
                    ], 500);
                }
            }

            $eid = Hashids::encode($access->id);

            $approvalCtl->notifyFirstApprover(
                $docid,
                $doctype,
                $access->status,
                'Access Request',
                url('/showaccessrequest/'.$eid),
                [
                    'info' => $access->keperluan,
                    'createdby' => $access->created_by,
                    'date' => $dt->toDateTimeString(),
                ]
            );

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Access Request created successfully',
                'id' => $access->id,
                'docid' => $docid,
                'attachments' => $uploadResult,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return response()->json([
                'message' => 'Failed create Access Request',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }

    public function update(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $doctype = 'ACR';

        $user = $request->user();

        $username = $user->username ?? 'system';

        $dt = Carbon::now();

        $request->validate([
            'cpny_id' => ['required', 'string'],
            'department_id' => ['required', 'string'],
            'location_id' => ['nullable', 'string'],
            'keperluan' => ['required', 'string'],
            'access_type' => ['nullable', 'string'],
            'user_assign' => ['nullable', 'string'],

            'details' => ['required', 'array', 'min:1'],
            'details.*.categoryid' => ['required', 'string'],
        ]);

        DB::beginTransaction();

        try {
            $access = TrAccess::lockForUpdate()
                ->findOrFail($id);

            if ($access->status !== 'D') {
                return response()->json([
                    'message' => 'Document cannot be updated',
                ], 403);
            }

            if ($access->created_by !== $username) {
                return response()->json([
                    'message' => 'Only creator can update document',
                ], 403);
            }

            $docid = $access->docid;

            $access->cpny_id = $request->cpny_id;
            $access->department_id = $request->department_id;
            $access->location_id = $request->location_id;
            $access->user_assign = $request->user_assign;
            $access->keperluan = $request->keperluan;
            $access->access_type = $request->access_type;
            $access->status = 'P';
            $access->updated_by = $username;

            $access->save();

            TrAccessDetail::where('docid', $docid)
                ->delete();

            $approvalConditions = [];

            foreach ($request->details as $row) {
                $category = MsCategory::where('doctype', 'ACR')
                    ->where('categoryid', $row['categoryid'])
                    ->where('status', 'A')
                    ->first();

                if (!$category) {
                    throw new \Exception('Category not found : '.$row['categoryid']);
                }

                TrAccessDetail::create([
                    'docid' => $docid,

                    'access_id' => $category->categoryid,

                    'access_descr' => $category->category_name,

                    'access_response' => null,

                    'access_username' => null,

                    'access_password' => null,

                    'access_process' => 'f',

                    'access_startdate' => null,

                    'access_enddate' => null,

                    'access_pic' => null,

                    'group_category' => strtoupper(
                        trim($category->groups)
                    ),

                    'status' => 'P',

                    'created_by' => $access->created_by,

                    'updated_by' => $username,
                ]);

                $approvalConditions[] = strtoupper(
                    trim($category->groups)
                );
            }

            $approvalConditions = collect($approvalConditions)
                ->unique()
                ->values()
                ->toArray();

            TrApproval::where('refnbr', $docid)
                ->where('aprv_doctype', $doctype)
                ->where('status', 'P')
                ->update([
                    'status' => 'X',
                    'updated_by' => $username,
                    'updated_at' => now(),
                ]);

            $approvalCtl = app(ApprovalController::class);

            $approvalCtl->loadLines(
                $doctype,
                $request->cpny_id,
                $request->department_id
            );

            $ctx = [
                'approval_conditions' => $approvalConditions,
            ];

            [$firstApprovalUsernames, $linesCount] =
                $approvalCtl->generateForDocument(
                    $docid,
                    $doctype,
                    $request->cpny_id,
                    $request->department_id,
                    $username,
                    $ctx,
                    $dt
                );

            $uploadResult = null;

            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr' => $docid,
                    'doctype' => $doctype,
                    'cpnyid' => $request->cpny_id,
                    'departementid' => $request->department_id,
                    'base_folder' => 'att-access-request/'.strtolower($doctype),
                    'created_by' => $username,
                ];

                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);

                    $uploadResult = $uploader->uploadInternal(
                        $meta,
                        $files
                    );
                } catch (\Throwable $e) {
                    DB::rollBack();

                    return response()->json([
                        'message' => 'Failed upload attachment',
                        'error' => $e->getMessage(),
                    ], 500);
                }
            }

            $eid = Hashids::encode($access->id);

            $approvalCtl->notifyFirstApprover(
                $docid,
                $doctype,
                $access->status,
                'Access Request',
                url('/showaccessrequest/'.$eid),
                [
                    'info' => $access->keperluan,
                    'createdby' => $access->created_by,
                    'updatedby' => $username,
                    'date' => $dt->toDateTimeString(),
                ]
            );

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Access Request updated successfully',
                'id' => $access->id,
                'docid' => $docid,
                'attachments' => $uploadResult,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return response()->json([
                'message' => 'Failed update Access Request',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }

    public function uploadAttachment(Request $request)
    {
        try {
            $request->validate([
                'docid' => 'required',
               'attachments.*' => [
                    'file',
                    'mimes:jpg,jpeg,png,pdf,xlsx,doc,docx',
                    'max:5120'
                ],
            ]);

            $access = TrAccess::where(
                'docid',
                $request->docid
            )->firstOrFail();

            if ($access->created_by !== auth()->user()->username) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            if (!in_array($access->status, ['P', 'D'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document cannot upload attachment',
                ], 422);
            }

            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $access->docid,
                    'doctype'       => 'ACR',
                    'cpny_id'       => $access->cpny_id,
                    'department_id' => $access->department_id,
                    'base_folder'   => 'att-access-request/acr',
                    'created_by'    => auth()->user()->username,
                ];

                app(TrAttachmentController::class)->uploadInternal(
                    $meta,
                    (array) $request->file('attachments')
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Attachment uploaded successfully',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function cancel($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $user = Auth::user();

        $username = $user->username ?? 'system';

        DB::beginTransaction();

        try {
            $access = TrAccess::lockForUpdate()
                ->findOrFail($id);

            if ($access->status !== 'D') {
                DB::rollBack();

                return response()->json([
                    'message' => 'Document cannot be cancelled',
                ], 403);
            }
            if ($access->created_by !== $username) {
                DB::rollBack();

                return response()->json([
                    'message' => 'Only creator can cancel document',
                ], 403);
            }
            $access->status = 'X';
            $access->updated_by = $username;
            $access->completed_by = $username;
            $access->completed_at = Carbon::now();

            $access->save();

            TrAccessDetail::where('docid', $access->docid)
                ->update([
                    'status' => 'X',
                    'updated_by' => $username,
                ]);

            TrApproval::where('refnbr', $access->docid)
                ->where('aprv_doctype', 'ACR')
                ->where('status', 'P')
                ->update([
                    'status' => 'X',
                ]);

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Access Request cancelled successfully',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return response()->json([
                'message' => 'Failed cancel Access Request',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }

    public function detail($hash)
    {
        try {
            $id = Hashids::decode($hash)[0] ?? null;

            abort_if(!$id, 404);

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 401);
            }

            $access = TrAccess::findOrFail($id);

            $access->eid = Hashids::encode($access->id);

            $details = TrAccessDetail::where('docid', $access->docid)
                ->orderBy('group_category')
                ->get();
            $approvals = [];
            try {
                // $approvals = TrApproval::where('refnbr', $access->docid)
                //     ->where('aprv_doctype', 'ACR')
                //     ->where('status', '<>', 'X')
                //     ->orderBy('aprv_leveling')
                //     ->orderBy('id')
                //     ->get();

                $approvals = TrApproval::where('refnbr', $access->docid)
                    ->where('aprv_doctype', 'ACR')
                    ->where('status', '<>', 'X')
                    ->orderBy('aprv_leveling')
                    ->orderBy('id')
                    ->get();
            } catch (\Exception $e) {
                Log::warning('Failed to fetch approvals for detail', [
                    'docid' => $access->docid,
                    'error' => $e->getMessage(),
                ]);
            }

            $completed = $details->where('status', 'C')->count();
            $total = $details->count();

            $attachmentResponse = app(TrAttachmentController::class)
                ->listAttachments(request(), 'ACR', $access->docid);

            $attachments = collect(
                $attachmentResponse->getData(true)['attachments'] ?? []
            );

            $comments = TrMessage::query()
                ->where('refnbr', $access->docid)
                ->where('doctype', 'ACR')
                ->orderBy('created_at')
                ->get();

            $hardwareDetails = $details
                ->where('group_category', 'HARDWARE')
                ->values();

            $softwareDetails = $details
                ->where('group_category', 'SOFTWARE')
                ->values();

            $canProcessHardware = false;
            $canProcessSoftware = false;

            if (
                $access->status === 'C'
                && $user->hasRole('ITHARDWARE')
            ) {
                $canProcessHardware = $hardwareDetails
                    ->where('status', 'P')
                    ->count() > 0;
            }

            if (
                $access->status === 'C'
                && $user->hasRole('ITSOFTWARE')
            ) {
                $canProcessSoftware = $softwareDetails
                    ->where('status', 'P')
                    ->count() > 0;
            }

            $canViewPassword =
                $access->created_by === $user->username
                || $user->hasRole('ITHARDWARE')
                || $user->hasRole('ITSOFTWARE');

            return response()->json([
                'success' => true,
                'access' => $access,
                'details' => $details,
                'approvals' => $approvals,
                'attachments' => $attachments,
                'comments' => $comments,

                'can_view_password' => $canViewPassword,

                'permissions' => [
                    'can_process_hardware' => $canProcessHardware,
                    'can_process_software' => $canProcessSoftware,
                ],

                'can_approve' => TrApproval::where('refnbr', $access->docid)
                    ->where('aprv_doctype', 'ACR')
                    ->where('aprv_username', auth()->user()->username)
                    ->where('status', 'P')
                    ->exists(),

                'summary' => [
                    'total' => $total,
                    'completed' => $completed,
                    'hardware' => $details->where('group_category', 'HARDWARE')->count(),
                    'software' => $details->where('group_category', 'SOFTWARE')->count(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Detail method error', [
                'hash' => $hash,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'Failed to load detail',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }

    // tracking() CLEAN VERSION

    public function tracking($hash)
    {
        try {
            $id = Hashids::decode($hash)[0] ?? null;

            abort_if(!$id, 404);

            $access = TrAccess::findOrFail($id);

            $getName = function (?string $username) {
                if (!$username) {
                    return null;
                }

                return User::where('username', $username)
                    ->value('name') ?? $username;
            };

            $steps = [];

            // =========================
            // SUBMITTED
            // =========================

            $steps[] = [
                'key' => 'submitted',

                'title' => 'Access Request',

                'status' => 'C',

                'status_label' => 'Submitted',

                'by' => $getName($access->created_by),

                'at' => optional($access->created_at)
                    ->format('Y-m-d H:i'),
            ];

            // =========================
            // APPROVALS
            // =========================

            $approvals = TrApproval::where('refnbr', $access->docid)
                ->where('aprv_doctype', 'ACR')
                ->where('status', '<>', 'X')
                ->orderBy('aprv_leveling')
                ->orderBy('id')
                ->get();
            $reasons = TrMessage::where('doctype', 'ACR')
                ->where('refnbr', $access->docid)
                ->orderByDesc('created_at')
                ->get([
                    'created_by',
                    'message',
                    'created_at',
                ]);

            foreach ($approvals as $aprv) {
                $steps[] = [
                    'key' => 'approval_'.$aprv->aprv_leveling,

                    'title' => $aprv->aprv_name
                        ?: ('Approval Level '.$aprv->aprv_leveling),

                    'status' => $aprv->status,

                    'status_label' => match ($aprv->status) {
                        'P' => 'Waiting approval',
                        'A' => 'Approved',
                        'R' => 'Rejected',
                        'D' => 'Revise',
                        default => '-',
                    },

                    // approver username
                    'aprv_username' => $aprv->aprv_username,

                    // display processed by
                    'by' => $aprv->status === 'P'
                        ? null
                        : $getName(
                            $aprv->updated_by ?: $aprv->aprv_username
                        ),

                    // approval datetime
                    'at' => $aprv->updated_at
                        ? Carbon::parse($aprv->updated_at)
                        ->format('Y-m-d H:i')
                        : null,

                    // revise / reject reason
                    'reason' => $reasons->first()?->message,
                ];
            }

            $latestComment = TrMessage::where('doctype', 'ACR')
                ->where('refnbr', $access->docid)
                ->latest('created_at')
                ->first();

            return response()->json([
                'success' => true,

                'doc' => $access->docid,

                'steps' => $steps,

                'status' => $access->status,

                'status_label' => match ($access->status) {
                    'P' => 'Pending',
                    'C' => 'Approved',
                    'R' => 'Rejected',
                    'D' => 'Revise',
                    'F' => 'Finished',
                    'X' => 'Cancelled',
                    default => '-',
                },

                'revise_reason' => $latestComment?->message,

                'reasons' => $reasons,
            ]);
        } catch (\Throwable $e) {
            \Log::error('TRACKING ERROR', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    // approve() CLEAN VERSION

    public function approve($docid, Request $request)
    {
        $user = $request->user();

        $doctype = 'ACR';

        $access = TrAccess::with('creator')
            ->where('docid', $docid)
            ->first();

        if (!$access) {
            return response()->json([
                'success' => false,
                'message' => 'Access Request not found',
            ], 404);
        }

        $eid = Hashids::encode($access->id);

        $docUrl = url('/showaccessrequest/'.$eid);

        $fullname = data_get($access, 'creator.name')
            ?: $access->created_by;

        $result = app(ApprovalController::class)->approveStep(
            $access->docid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, Carbon $now) use ($access, $fullname, $docUrl) {
                $access->status = 'C';
                $access->updated_by = auth()->user()->username;
                $access->updated_at = $now;

                $access->save();

                TrAccessDetail::where('docid', $access->docid)
                ->whereNull('access_startdate')
                ->update([
                    'access_startdate' => now(),

                    'access_process' => 'f',

                    'updated_by' => auth()->user()->username,

                    'updated_at' => now(),
                ]);

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $access->docid,
                    'Access Request',
                    'C',
                    $access->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $access->cpny_id ?? '',
                        'deptname' => $access->department_id ?? '',
                        'date' => optional($access->access_date)
                            ->format('Y-m-d'),
                        'info' => $access->keperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                    ]
                );
            },

            function ($next, Carbon $now) use ($access, $docUrl) {
                app(ApprovalController::class)->notifyFirstApprover(
                    $access->docid,
                    'ACR',
                    'P',
                    'Access Request',
                    $docUrl,
                    [
                        'info' => $access->keperluan,
                        'createdby' => $access->created_by,
                        'date' => $now->toDateTimeString(),
                    ]
                );

                $access->updated_by = auth()->user()->username;
                $access->updated_at = $now;

                $access->save();
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
                    ?? 'Approve failed',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Access Request approved successfully',
        ]);
    }

    public function reject(Request $request, $docid)
    {
        $request->validate([
            'reason' => ['required', 'string'],
        ]);

        $user = $request->user();

        $doctype = 'ACR';

        $access = TrAccess::with('creator')
            ->where('docid', $docid)
            ->first();

        if (!$access) {
            return response()->json([
                'success' => false,
                'message' => 'Access Request not found',
            ], 404);
        }

        $eid = Hashids::encode($access->id);

        $docUrl = url('/showaccessrequest/'.$eid);

        $fullname = data_get($access, 'creator.name')
            ?: $access->created_by;

        $result = app(ApprovalController::class)->rejectStep(
            $access->docid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, Carbon $now) use ($access, $fullname, $docUrl, $doctype, $request) {
                $access->status = 'R';
                $access->completed_by = auth()->user()->username;
                $access->completed_at = $now;
                $access->updated_by = auth()->user()->username;
                $access->updated_at = $now;

                $access->save();

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $access->docid,
                    'Access Request',
                    'R',
                    $access->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $access->cpny_id ?? '',
                        'deptname' => $access->department_id ?? '',
                        'date' => $now->toDateString(),
                        'info' => $access->keperluan ?? '',
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                    ]
                );

                try {
                    app(SendCommentController::class)
                        ->sendmsg($access->id, $doctype, $request);
                } catch (\Throwable $e) {
                    \Log::warning('Failed save reject reason', [
                        'docid' => $access->docid,
                        'doctype' => $doctype,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
                    ?? 'Reject failed',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Access Request rejected successfully',
        ]);
    }

    public function revise(Request $request, $docid)
    {
        $request->validate([
            'reason' => ['required', 'string'],
        ]);

        $user = $request->user();

        $doctype = 'ACR';

        $access = TrAccess::with('creator')
            ->where('docid', $docid)
            ->first();

        if (!$access) {
            return response()->json([
                'success' => false,
                'message' => 'Access Request not found',
            ], 404);
        }

        $eid = Hashids::encode($access->id);

        $docUrl = url('/showaccessrequest/'.$eid);

        $fullname = data_get($access, 'creator.name')
            ?: $access->created_by;

        $result = app(ApprovalController::class)->reviseStep(
            $access->docid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, Carbon $now) use ($access, $fullname, $docUrl, $doctype, $request) {
                $access->status = 'D';
                $access->completed_by = auth()->user()->username;
                $access->completed_at = $now;
                $access->updated_by = auth()->user()->username;
                $access->updated_at = $now;

                $access->save();

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $access->docid,
                    'Access Request',
                    'D',
                    $access->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $access->cpny_id ?? '',
                        'deptname' => $access->department_id ?? '',
                        'date' => $now->toDateString(),
                        'info' => $access->keperluan ?? '',
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                    ]
                );

                try {
                    app(SendCommentController::class)
                        ->sendmsg($access->id, $doctype, $request);
                } catch (\Throwable $e) {
                    \Log::warning('Failed save revise reason', [
                        'docid' => $access->docid,
                        'doctype' => $doctype,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
                    ?? 'Revise failed',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Access Request revised successfully',
        ]);
    }

    public function processHardware(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $user = Auth::user();

        if (!$user->hasRole('ITHARDWARE')) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $username = $user->username ?? 'system';

        $access = TrAccess::findOrFail($id);

        $request->validate([
            'details' => ['required', 'array', 'min:1'],

            'details.*.id' => ['required', 'integer'],

            'details.*.action' => [
                'required',
                Rule::in(['SAVE', 'DONE']),
            ],

            'details.*.access_response' => [
                'nullable',
                'string',
            ],

            'details.*.access_username' => [
                'nullable',
                'string',
            ],

            'details.*.access_password' => [
                'nullable',
                'string',
            ],
        ]);

        DB::beginTransaction();

        try {
            $access = TrAccess::lockForUpdate()
                ->findOrFail($id);

            if ($access->status === 'P') {
                return response()->json([
                    'message' => 'Document not approved yet',
                ], 403);
            }

            if (in_array($access->status, ['R', 'D', 'X'])) {
                return response()->json([
                    'message' => 'Document cannot be processed',
                ], 403);
            }

            foreach ($request->details as $row) {
                $detail = TrAccessDetail::where('id', $row['id'])
                    ->where('docid', $access->docid)
                    ->where('group_category', 'HARDWARE')
                    ->lockForUpdate()
                    ->first();

                if (!$detail) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | ALREADY COMPLETED
                |--------------------------------------------------------------------------
                */

                if ($detail->status === 'C') {
                    continue;
                }

                $action = strtoupper(
                    $row['action'] ?? 'SAVE'
                );

                /*
                |--------------------------------------------------------------------------
                | SAVE PROGRESS
                |--------------------------------------------------------------------------
                */

                $detail->access_response =
                    $row['access_response'] ?? null;

                $detail->access_username =
                    $row['access_username'] ?? null;

                $detail->access_password =
                    $row['access_password'] ?? null;

                /*
                |--------------------------------------------------------------------------
                | START PROCESS
                |--------------------------------------------------------------------------
                */

                if (!$detail->access_startdate) {
                    $detail->access_startdate = now();
                }

                /*
                |--------------------------------------------------------------------------
                | SAVE ONLY
                |--------------------------------------------------------------------------
                */

                if ($action === 'SAVE') {
                    $detail->access_process = 'f';

                    $detail->status = 'P';
                }

                /*
                |--------------------------------------------------------------------------
                | DONE / COMPLETE
                |--------------------------------------------------------------------------
                */

                if ($action === 'DONE') {
                    $detail->access_process = 't';

                    $detail->status = 'C';

                    $detail->access_enddate = now();

                    $detail->access_pic = $username;
                }

                $detail->updated_by = $username;

                $detail->updated_at = now();

                $detail->save();
            }

            /*
            |--------------------------------------------------------------------------
            | AUTO FINISH HEADER
            |--------------------------------------------------------------------------
            */

            $remaining = TrAccessDetail::where('docid', $access->docid)
                ->where('status', 'P')
                ->count();

            if ($remaining <= 0) {
                $access->status = 'F';

                $access->completed_by = $username;

                $access->completed_at = now();

                $access->updated_by = $username;

                $access->updated_at = now();

                $access->save();
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Hardware access processed successfully',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return response()->json([
                'message' => 'Failed process hardware access',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }

    public function processSoftware(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $user = Auth::user();

        if (!$user->hasRole('ITSOFTWARE')) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $username = $user->username ?? 'system';

        $access = TrAccess::findOrFail($id);

        $request->validate([
            'details' => ['required', 'array', 'min:1'],

            'details.*.id' => ['required', 'integer'],

            'details.*.action' => [
                'required',
                Rule::in(['SAVE', 'DONE']),
            ],

            'details.*.access_response' => [
                'nullable',
                'string',
            ],

            'details.*.access_username' => [
                Rule::requiredIf(
                    strtoupper($access->access_type ?? '') === 'NEW'
                ),

                'nullable',
                'string',
            ],

            'details.*.access_password' => [
                Rule::requiredIf(
                    strtoupper($access->access_type ?? '') === 'NEW'
                ),

                'nullable',
                'string',
            ],
        ]);

        DB::beginTransaction();

        try {
            $access = TrAccess::lockForUpdate()
                ->findOrFail($id);

            if ($access->status === 'P') {
                return response()->json([
                    'message' => 'Document not approved yet',
                ], 403);
            }

            if (in_array($access->status, ['R', 'D', 'X'])) {
                return response()->json([
                    'message' => 'Document cannot be processed',
                ], 403);
            }

            foreach ($request->details as $row) {
                $detail = TrAccessDetail::where('id', $row['id'])
                    ->where('docid', $access->docid)
                    ->where('group_category', 'SOFTWARE')
                    ->lockForUpdate()
                    ->first();

                if (!$detail) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | ALREADY COMPLETED
                |--------------------------------------------------------------------------
                */

                if ($detail->status === 'C') {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | PROCESS COMPLETE
                |--------------------------------------------------------------------------
                */

                $action = strtoupper(
                    $row['action'] ?? 'SAVE'
                );

                /*
                |--------------------------------------------------------------------------
                | SAVE PROGRESS
                |--------------------------------------------------------------------------
                */

                $detail->access_response =
                    $row['access_response'] ?? null;

                $detail->access_username =
                    $row['access_username'] ?? null;

                $detail->access_password =
                    $row['access_password'] ?? null;

                /*
                |--------------------------------------------------------------------------
                | START PROCESS
                |--------------------------------------------------------------------------
                */

                if (!$detail->access_startdate) {
                    $detail->access_startdate = now();
                }

                /*
                |--------------------------------------------------------------------------
                | SAVE ONLY
                |--------------------------------------------------------------------------
                */

                if ($action === 'SAVE') {
                    $detail->access_process = 'f';

                    $detail->status = 'P';
                }

                /*
                |--------------------------------------------------------------------------
                | DONE / COMPLETE
                |--------------------------------------------------------------------------
                */

                if ($action === 'DONE') {
                    $detail->access_process = 't';

                    $detail->status = 'C';

                    $detail->access_enddate = now();

                    $detail->access_pic = $username;
                }

                $detail->updated_by = $username;

                $detail->updated_at = now();

                $detail->save();
            }

            /*
            |--------------------------------------------------------------------------
            | AUTO FINISH HEADER
            |--------------------------------------------------------------------------
            */

            $remaining = TrAccessDetail::where('docid', $access->docid)
                ->where('status', 'P')
                ->count();

            if ($remaining <= 0) {
                $access->status = 'F';

                $access->completed_by = $username;

                $access->completed_at = now();

                $access->updated_by = $username;

                $access->updated_at = now();

                $access->save();
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Software access processed successfully',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return response()->json([
                'message' => 'Failed process software access',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }

    public function comments($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $access = TrAccess::findOrFail($id);

        $comments = TrMessage::query()
            ->where('refnbr', $access->docid)
            ->where('doctype', 'ACR')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $comments,
        ]);
    }

    public function comment(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $request->validate([
            'message' => ['required', 'string'],
        ]);

        $access = TrAccess::findOrFail($id);

        try {
            $request->merge([
                'reason' => $request->message,
                'docid' => $access->docid,
            ]);

            app(SendCommentController::class)
                ->sendmsg(
                    $access->id,
                    'ACR',
                    $request
                );

            /*
            |------------------------------------------------------------------
            | Comment notification
            |------------------------------------------------------------------
            */
            try {
                $authUser = Auth::user();

                $usernames = collect([
                    $access->user_peminta,
                    $access->user_assign,
                    $access->created_by,
                ])->filter()->unique();

                $approverUsernames = TrApproval::where('refnbr', $access->docid)
                    ->pluck('aprv_username');

                $usernames = $usernames->merge($approverUsernames)
                    ->filter()
                    ->unique()
                    ->reject(fn($u) => $u === $authUser->username);

                $commenterEmail = $authUser->notification_email ?: $authUser->email;
                $commenterName  = $authUser->name ?? $authUser->username;

                foreach ($usernames as $username) {
                    $recipient = User::where('username', $username)->first();
                    $email = $recipient?->notification_email ?: $recipient?->email;

                    if (!$email || $email === $commenterEmail) {
                        continue;
                    }

                    try {
                        Mail::to($email)->send(
                            new \App\Mail\CommentNotificationMail(
                                'ACR',
                                $access->docid,
                                $commenterName,
                                $request->message,
                                'ACR'
                            )
                        );
                    } catch (\Throwable $e) {
                        Log::warning('ACR Comment Mail Failed', [
                            'docid' => $access->docid,
                            'email' => $email,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('ACR comment notification failed', [
                    'docid' => $access->docid,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Comment sent successfully',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Failed send comment',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }

    public function categorySearch(Request $request)
    {
        $search = trim((string) $request->get('q', ''));

        $query = MsCategory::query()
            ->where('doctype', 'ACR')
            ->where('status', 'A');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('categoryid', 'ilike', "%{$search}%")
                    ->orWhere('category_name', 'ilike', "%{$search}%")
                    ->orWhere('groups', 'ilike', "%{$search}%");
            });
        }

        $rows = $query
            ->orderBy('groups')
            ->orderBy('category_name')
            ->limit(50)
            ->get();

        return response()->json([
            'results' => $rows->map(function ($row) {
                return [
                    'id' => $row->categoryid,
                    'text' => $row->category_name,
                    'categoryid' => $row->categoryid,
                    'category_name' => $row->category_name,
                    'group_category' => strtoupper(trim($row->groups)),
                ];
            }),
        ]);
    }

    public function print($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $access = TrAccess::with([
            'creator:username,name',
            'details',
        ])->findOrFail($id);

        $details = TrAccessDetail::where('docid', $access->docid)
            ->orderByRaw("
                CASE
                    WHEN group_category = 'HARDWARE' THEN 1
                    WHEN group_category = 'SOFTWARE' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('id')
            ->get();

        $approvals = TrApproval::where('refnbr', $access->docid)
            ->where('aprv_doctype', 'ACR')
            ->where('status', '<>', 'X')
            ->orderBy('aprv_leveling')
            ->orderBy('id')
            ->get();

        $comments = TrMessage::query()
            ->where('refnbr', $access->docid)
            ->where('doctype', 'ACR')
            ->orderBy('created_at')
            ->get();

        $pdf = \PDF::loadView(
            'pages.access-requests.pdf_accessrequest',
            [
                'access' => $access,
                'details' => $details,
                'approvals' => $approvals,
                'comments' => $comments,
            ]
        )->setPaper('a4', 'portrait');

        $filename =
            'ACCESS-REQUEST-'.
            $access->docid.
            '.pdf';

        return $pdf->stream($filename);
    }
}
