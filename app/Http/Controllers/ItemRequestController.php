<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Autonbr;
use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\Usercpny;
use App\Models\Userdept;
use App\Models\User;
use App\Models\Site;
use App\Models\Division;
use App\Models\TrItemRequest;
use App\Models\MsLocation;
use App\Models\MsSubLocation;
use Mail;
use Illuminate\Support\Facades\Log;
use PDF;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Controllers\TrAttachmentController;
use Illuminate\Support\Facades\Response;
use App\Models\TrAttachment;
use Illuminate\Support\Str;
use Google\Cloud\Storage\StorageClient;
use App\Http\Controllers\ApprovalController;
use App\Models\TrApproval;
use App\Models\SysUserRole;

class ItemRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $cpnyIds = is_string($user->cpny_id)
            ? array_map('trim', explode(',', $user->cpny_id))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_map('trim', explode(',', $user->department_id))
            : (array) $user->department_id;

        $q = TrItemRequest::query()
            ->whereIn('cpny_id', $cpnyIds)
            ->whereIn('department_id', $deptIds);

        $all        = (clone $q)->count();
        $onProgress = (clone $q)->where('status', 'P')->count();
        $reject     = (clone $q)->where('status', 'R')->count();
        $revise     = (clone $q)->where('status', 'D')->count();
        $completed  = (clone $q)->where('status', 'C')->count();

        return view('pages.itemrequest.itemreq', compact('all', 'onProgress', 'reject', 'revise', 'completed'));
    }

    public function json(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $cpnyIds = is_string($user->cpny_id)
            ? array_map('trim', explode(',', $user->cpny_id))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_map('trim', explode(',', $user->department_id))
            : (array) $user->department_id;

        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));
        $status = (string) $request->query('status', '');

        // Kolom untuk ordering DataTables (sesuaikan dengan kolom tabel tr_item_req)
        $columns = [
            0 => 'ir.irid',
            1 => 'ir.irdate',
            2 => 'ir.cpny_id',
            3 => 'ir.department_id',
            4 => 'ir.inventory_descr_req',
            5 => 'ir.pic_item_req',
            6 => 'ir.status',
        ];

        $orderIdx = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'ir.irid';

        $baseTable = (new TrItemRequest)->getTable();

        $base = TrItemRequest::from($baseTable . ' as ir')
            ->whereIn('ir.cpny_id', $cpnyIds)
            ->whereIn('ir.department_id', $deptIds);

        if ($status !== '') {
            $base->where('ir.status', $status);
        }

        // Total sebelum search
        $recordsTotal = (clone $base)->count();

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('ir.irid',                 'ilike', "%{$search}%")
                ->orWhere('ir.cpny_id',            'ilike', "%{$search}%")
                ->orWhere('ir.department_id',      'ilike', "%{$search}%")
                ->orWhere('ir.inventory_descr_req','ilike', "%{$search}%")
                ->orWhere('ir.inventoryid',        'ilike', "%{$search}%")
                ->orWhere('ir.pic_item_req',       'ilike', "%{$search}%")
                ->orWhere('ir.pic_completed_item_req','ilike', "%{$search}%")
                ->orWhere('ir.status',             'ilike', "%{$search}%")
                ->orWhere('ir.created_by',         'ilike', "%{$search}%");
            });
        }

        // Total setelah search
        $recordsFiltered = (clone $base)->count();

        $data = $base->select([
                'ir.id',
                'ir.irid',
                'ir.irdate',
                'ir.cpny_id',
                'ir.department_id',
                'ir.inventory_descr_req',
                'ir.inventoryid',
                'ir.pic_item_req',
                'ir.pic_completed_item_req',
                'ir.status',
                'ir.created_by',
            ])
            ->orderBy($orderCol, $orderDir)
            ->orderBy('ir.irid', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        // Add encrypted ID
        $data->transform(function ($row) {
            $row->eid = Hashids::encode($row->id);
            unset($row->id);
            return $row;
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }


    
    public function createItemReq()
    {        
        $user = Auth::user();       

        if (!$user) {
            return redirect()->route('login');
        }

        $usercpny = Usercpny::where('username', $user->username)
            ->get();       
        
        $usercpny2 = Usercpny::where('username', $user->username)
            ->first();
        $userdept = Userdept::where('username', $user->username)
            ->get();
        $userdept2 = Userdept::where('username', $user->username)
            ->first();                   
       
        $akses_stock = SysUserRole::where('username', $user->username)
            ->where('role_id','WHSACCESS')
            ->first();

        return view('pages.itemrequest.createitemreq', compact('usercpny','usercpny2','userdept','userdept2','akses_stock'));
    }
       
    public function storeItemReq(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'cpny_id'               => 'required|string',
            'department_id'         => 'required|string',
            'inventory_descr_req'   => 'required|string|min:5',
            'attachments.*'         => 'nullable|file|max:10240',
        ]);

        $user = $request->user();
        $username = $user->username ?? 'system';
        $dt    = now();
        $year  = $dt->year;
        $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $doctype = 'SR'; // Item Request

        $cpny_id = $request->cpny_id;
        $department_id = $request->department_id;

        // ===== generate TrApproval dari MsApproval sesuai context =====
        $approvalCtl = app(ApprovalController::class);

        // Pastikan line approval ada (kalau mau validasi awal sebelum simpan detail, panggil loadLines)
        $approvalCtl->loadLines($doctype, $cpny_id, $department_id);

        DB::beginTransaction();
        try {

            /* =========================
            * Generate IRID (Autonumber)
            * ========================= */           

            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $doctype,
                    'year'    => $year,
                    'month'   => $month,
                    'status'  => 'A',
                    'number'  => 1,
                ]);
                $seq = 1;
            } else {
                $seq = $autonbr->number + 1;
                $autonbr->update(['number' => $seq]);
            }

            $docid = $doctype . substr($year, 2) . $month . sprintf('%04d', $seq);

            /* =========================
            * Insert HEADER
            * ========================= */
            $itemReq = new TrItemRequest();
            $itemReq->irid                  = $docid;
            $itemReq->irdate                = $dt->toDateString();
            $itemReq->cpny_id               = $cpny_id;
            $itemReq->department_id         = $department_id;
            $itemReq->inventory_type        = $request->inventory_type;
            $itemReq->inventory_descr_req   = $request->inventory_descr_req;
            $itemReq->pic_item_req          = $username;
            $itemReq->status                = 'P'; // default: On Progress
            $itemReq->created_by            = $username;
            $itemReq->save();

            // =========================
            // CTX approval
            // =========================
            $invType = strtoupper(trim($request->inventory_type)); // STOCK | NONSTOCK

            $ctx = [
                'ignore_nominal' => true,
                // pilih jalur approval berdasarkan inventory_type
                // pastikan string ini sesuai yang ada di MsApproval (condition)
                'approval_conditions' => [
                    $invType === 'STOCK' ? 'STOCK' : 'NONSTOCK'
                ],
            ];

            // Generate TrApproval
            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $docid,
                $doctype,
                $cpny_id,
                $department_id,
                $username,
                $ctx,
                $dt
            );

            // (opsional) simpan hint approver pertama di itemReq seperti sebelumnya
            if ($firstApprovalUsernames) {
                $itemReq->completed_by = $firstApprovalUsernames;
                $itemReq->completed_at = $dt;
                $itemReq->save();
            }

            /* =========================
            * Upload Attachments (optional)
            * ========================= */
            if ($request->hasFile('attachments')) {

                $meta = [
                    'refnbr'        => $docid,
                    'doctype'       => $doctype,
                    'cpnyid'        => $request->cpny_id,
                    'departementid' => $request->department_id,
                    'base_folder'   => 'att-item-request',
                    'created_by'    => $username,
                ];

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploader->uploadInternal($meta, (array) $request->file('attachments'));
                } catch (\Throwable $e) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to upload attachment',
                        'error'   => $e->getMessage(),
                    ], 500);
                }
            }

            $eid = Hashids::encode($itemReq->id);

            $approvalCtl->notifyFirstApprover(
                    $docid,
                    $doctype,
                    $itemReq->status,                 // 'P' | 'R' | 'D' | 'A' | 'C'
                    'Item Request',
                    url('/showitemreq/' . $eid),
                    [
                        'info'      => $request->inventory_descr_req,
                        'createdby' => $itemReq->created_by,
                        'date'      => $dt->toDateTimeString(),
                    ]
                );

            DB::commit();

            return response()->json([
                'message' => 'Item Request created successfully',
                'irid'    => $docid,
                'eid'     => Hashids::encode($itemReq->id),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'message' => 'Failed to create Item Request',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

   
    public function editItemReq($hash)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        // === HEADER (tr_item_req) ===
        $itemReq = TrItemRequest::findOrFail($id);

        // === master user scope ===
        $usercpny  = Usercpny::where('username', $user->username)->get();
        $usercpny2 = Usercpny::where('username', $user->username)->first();
        $userdept  = Userdept::where('username', $user->username)->get();
        $userdept2 = Userdept::where('username', $user->username)->first();

        // === attachments: refnbr = IRID, doctype = IR (lebih aman kalau ada field doctype) ===
        $rows = TrAttachment::where('refnbr', $itemReq->irid)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();
     
        // === Signed URL (GCS) ===
        $attachments = collect();
        if ($rows->count()) {
            $config      = config('filesystems.disks.gcs');
            $keyFilePath = $config['key_file'];

            if (!Str::startsWith($keyFilePath, ['/', 'C:\\', 'D:\\'])) {
                $keyFilePath = base_path($keyFilePath);
            }

            $storage = new StorageClient([
                'projectId'   => $config['project_id'],
                'keyFilePath' => $keyFilePath,
            ]);

            $bucket = $storage->bucket($config['bucket']);

            $attachments = $rows->map(function ($r) use ($bucket) {
                $objectPath = rtrim($r->folder, '/') . '/' . $r->filename;
                $object     = $bucket->object($objectPath);

                $signedUrl = null;
                try {
                    $signedUrl = $object->signedUrl(
                        new \DateTimeImmutable('+10 minutes'),
                        ['version' => 'v4']
                    );
                } catch (\Throwable $e) {
                    \Log::warning('Signed URL gagal', [
                        'path'  => $objectPath,
                        'error' => $e->getMessage()
                    ]);
                }

                return (object) [
                    'id'           => $r->id,
                    'display_name' => $r->attachment_name,
                    'created_by'   => $r->created_by,
                    'created_at'   => $r->created_at,
                    'url'          => $signedUrl,
                    'folder'       => $r->folder,
                    'filename'     => $r->filename,
                    'extention'    => $r->extention,
                    'size'         => $r->filesize,
                ];
            });
        }

        return view('pages.itemrequest.edititemreq', compact(
            'itemReq', 'usercpny', 'usercpny2', 'userdept', 'userdept2', 'attachments', 'hash'
        ));
    }



    public function updateItemReq(Request $request, $hash)
    {
        $request->validate([
            'cpny_id'               => 'required|string',
            'department_id'         => 'required|string',
            'inventory_descr_req'   => 'required|string|min:5',
            'attachments.*'         => 'nullable|file|max:10240',
        ]);

        $user     = $request->user();
        $username = $user->username ?? 'system';
        $dt       = now();
        $doctype  = 'SR';

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        DB::beginTransaction();
        try {

            $itemReq = TrItemRequest::lockForUpdate()->findOrFail($id);

            /* =========================
            * APPROVAL STATUS CHECK
            * ========================= */
            if (in_array($itemReq->status, ['C', 'A'])) {
                return response()->json([
                    'message' => 'Item Request sudah selesai / approved dan tidak bisa diedit.'
                ], 422);
            }

            $cpny_id       = $request->cpny_id;
            $department_id = $request->department_id;

            /* =========================
            * UPDATE HEADER
            * ========================= */
            $itemReq->cpny_id             = $cpny_id;
            $itemReq->department_id       = $department_id;
            $itemReq->inventory_type      = $request->inventory_type;
            $itemReq->inventory_descr_req = $request->inventory_descr_req;
            $itemReq->status              = 'P';          // balik ke On Progress
            $itemReq->updated_by          = $username;
            $itemReq->save();

            /* =========================
            * RE-GENERATE APPROVAL
            * ========================= */
            $approvalCtl = app(ApprovalController::class);

            // 1️⃣ hapus approval lama
            TrApproval::where('refnbr', $itemReq->irid)->delete();

            // 2️⃣ load rule approval
            $approvalCtl->loadLines($doctype, $cpny_id, $department_id);

            // =========================
            // CTX approval
            // =========================
            $invType = strtoupper(trim($request->inventory_type)); // STOCK | NONSTOCK

            $ctx = [
                'ignore_nominal' => true,
                // pilih jalur approval berdasarkan inventory_type
                // pastikan string ini sesuai yang ada di MsApproval (condition)
                'approval_conditions' => [
                    $invType === 'STOCK' ? 'STOCK' : 'NONSTOCK'
                ],
            ];

            // 3️⃣ generate approval baru
            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $itemReq->irid,
                $doctype,
                $cpny_id,
                $department_id,
                $username,
                $ctx,
                $dt
            );

            // simpan hint approver pertama
            if ($firstApprovalUsernames) {
                $itemReq->completed_by = $firstApprovalUsernames;
                $itemReq->completed_at = $dt;
                $itemReq->save();
            }

            /* =========================
            * UPLOAD ATTACHMENTS (optional)
            * ========================= */
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $itemReq->irid,
                    'doctype'       => $doctype,
                    'cpnyid'        => $cpny_id,
                    'departementid' => $department_id,
                    'base_folder'   => 'att-item-request',
                    'created_by'    => $username,
                ];

                $uploader = app(TrAttachmentController::class);
                $uploader->uploadInternal($meta, (array) $request->file('attachments'));
            }

            /* =========================
            * NOTIFY FIRST APPROVER
            * ========================= */
            $eid = Hashids::encode($itemReq->id);

            $approvalCtl->notifyFirstApprover(
                $itemReq->irid,
                $doctype,
                $itemReq->status,          // P
                'Item Request',
                url('/showitemreq/' . $eid),
                [
                    'info'      => $itemReq->inventory_descr_req,
                    'createdby' => $itemReq->created_by,
                    'date'      => $dt->toDateTimeString(),
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'Item Request updated successfully',
                'irid'    => $itemReq->irid,
                'eid'     => $eid,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'message' => 'Failed to update Item Request',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
          

    public function showItemReq($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // ===== HEADER (SR) =====
        $itemReq = TrItemRequest::with([
            'creator:username,name', // pastikan relasi creator ada di model
            'inventory:inventoryid,inventory_descr'
        ])->findOrFail($id);

        // ===== (opsional) DETAIL =====
        // Kalau SR memang tidak punya detail table, hapus ini.
        // Kalau tabel detail ada tapi doc key-nya irid, sesuaikan where-nya.
        $itemReqDetail = collect(); // default kosong

        // ===== ATTACHMENTS (tr_attachment) =====
        $rows = TrAttachment::where('refnbr', $itemReq->irid)
            ->where('doctype', 'SR')              // <- PENTING: pastikan kamu simpan doctype 'SR' di attachment
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        $config      = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];
        if (!Str::startsWith($keyFilePath, ['/','C:\\','D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }

        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]);
        $bucket = $storage->bucket($config['bucket']);

        $attachments = $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/').'/'.$r->filename;
            $object     = $bucket->object($objectPath);

            $signedUrl = null;
            try {
                $signedUrl = $object->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }

            return (object) [
                'id'           => $r->id,
                'display_name' => $r->attachment_name,
                'created_by'   => $r->created_by,
                'created_at'   => $r->created_at,
                'url'          => $signedUrl,
                'folder'       => $r->folder,
                'filename'     => $r->filename,
                'extention'    => $r->extention,
                'size'         => $r->filesize,
            ];
        });

        $loginUsername = $user->username ?? $user->name ?? null;
        $canUpload     = ($itemReq->created_by === $loginUsername);

        return view('pages.itemrequest.showitemreq', compact(
            'itemReq',
            'itemReqDetail',
            'attachments',
            'hash',
            'canUpload'
        ));
    }

      
    public function approveItemReq(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'SR';

        $itemReq = TrItemRequest::with('creator')->where('irid', $docid)->first();
        if (!$itemReq) return response()->json(['success'=>false,'message'=>'Item Request not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($itemReq->id);
        $docUrl   = url('/showitemreq/' . $eid);
        $fullname = data_get($itemReq, 'creator.name') ?: $itemReq->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->approveStep(
            $itemReq->irid,
            $doctype,
            $user->username,
            $user->name,

            // complete: update header/detail + email creator complete
            function (string $refnbr, \Carbon\Carbon $now) use ($itemReq, $fullname, $docUrl) {
                $itemReq->status       = 'C';
                $itemReq->completed_by = $itemReq->completed_by ?: auth()->user()->username;
                $itemReq->completed_at = $now;
                $itemReq->save();               

                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $itemReq->irid,
                    'Item Request',
                    'C',
                    $itemReq->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $itemReq->cpny_id ?? $itemReq->cpnyid ?? '',
                        'deptname' => $itemReq->department_id ?? $itemReq->departementid ?? '',
                        'date'     => $itemReq->itemReqdate,
                        'info'     => $itemReq->inventory_descr_req,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname, 
                    ]
                );
            },

            // notify next approver
            function ($next, \Carbon\Carbon $now) use ($itemReq, $docUrl) {
                app(\App\Http\Controllers\ApprovalController::class)->notifyFirstApprover(
                    $itemReq->irid,
                    'SR',
                    'P',
                    'Item Request',
                    $docUrl,
                    [
                        'info'      => $itemReq->inventory_descr_req,
                        'createdby' => $itemReq->created_by,
                        'date'      => $now->toDateTimeString(),
                    ]
                );

                // jejak terakhir diproses (optional)
                $itemReq->completed_by = auth()->user()->username;
                $itemReq->completed_at = $now;
                $itemReq->save();
            }
        );

        if (!$result['ok']) {
            return response()->json(['success'=>false,'message'=>$result['message'] ?? 'Approve failed'], 403);
        }

        return response()->json(['success'=>true,'message'=>'Task approved successfully']);
    }

    public function rejectItemReq(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'SR';

        $itemReq = \App\Models\TrItemRequest::with('creator')->where('irid', $docid)->first();
        if (!$itemReq) return response()->json(['success'=>false,'message'=>'Item Request not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($itemReq->id);
        $docUrl   = url('/showitemreq/' . $eid);
        $fullname = data_get($itemReq, 'creator.name') ?: $itemReq->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->rejectStep(
            $itemReq->irid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($itemReq, $fullname, $docUrl) {
                $itemReq->status       = 'R';
                $itemReq->completed_by = auth()->user()->username;
                $itemReq->completed_at = $now;
                $itemReq->save();

                // optional: tandai detail R
                // \App\Models\TrItemRequestdetail::where('irid', $itemReq->irid)->update(['status' => 'R']);

                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $itemReq->irid,
                    'Item Request',
                    'R',
                    $itemReq->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $itemReq->cpny_id ?? $itemReq->cpnyid ?? '',
                        'deptname' => $itemReq->department_id ?? $itemReq->departementid ?? '',
                        'date'     => $now->toDateString(),
                        'info'     => $itemReq->inventory_descr_req,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname, 
                    ]
                );

                // simpan komentar (jika ada)
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($itemReq->id, 'SR', request());
                } catch (\Throwable $e) {}
            }
        );

        if (!$result['ok']) {
            return response()->json(['success'=>false,'message'=>$result['message'] ?? 'Reject failed'], 403);
        }

        return response()->json(['success'=>true,'message'=>'Item Request rejected successfully']);
    }

    public function reviseItemReq(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'SR';

        $itemReq = \App\Models\TrItemRequest::with('creator')->where('irid', $docid)->first();
        if (!$itemReq) return response()->json(['success'=>false,'message'=>'Item Request not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($itemReq->id);
        $docUrl   = url('/showitemreq/' . $eid);
        $fullname = data_get($itemReq, 'creator.name') ?: $itemReq->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->reviseStep(
            $itemReq->irid,            // refnbr
            $doctype,                 // PT
            $user->username,          // actor
            $user->name,              // actor
            function (string $refnbr, \Carbon\Carbon $now) use ($itemReq, $fullname, $docUrl) {
                // === HEADER Item Request -> D ===
                $itemReq->status       = 'D';
                $itemReq->completed_by = auth()->user()->username;
                $itemReq->completed_at = $now;
                $itemReq->save();

                // (opsional) DETAIL -> D
                // \App\Models\TrItemRequestdetail::where('irid', $itemReq->irid)->update(['status' => 'D']);

                // === Email ke requester ===
                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $itemReq->irid,
                    'Item Request',
                    'D',
                    $itemReq->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $itemReq->cpny_id ?? $itemReq->cpnyid ?? '',
                        'deptname' => $itemReq->department_id ?? $itemReq->departementid ?? '',
                        'date'     => $now->toDateString(),
                        'info'     => $itemReq->inventory_descr_req,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname,   // <<< tambahkan ini
                    ]
                );


                // === Simpan komentar (jika ada) ===
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($itemReq->id, 'SR', request());
                } catch (\Throwable $e) {}
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success'=>false,
                'message'=>$result['message'] ?? 'Revise failed'
            ], 403);
        }

        return response()->json(['success'=>true,'message'=>'Item Request revised successfully']);
    }

    // public function approveItemRequest(Request $request, $docid)
    // {
    //     $now  = Carbon::now();
    //     $user = $request->user();
    //     $doctype = 'SR';

    //     // Ambil header + creator
    //     $itemReq = TrItemRequest::with('creator')->where('irid', $docid)->first();
    //     if (!$itemReq) {
    //         return response()->json(['success' => false, 'message' => 'Item Request not found'], 404);
    //     }
    //     $fullname = data_get($itemReq, 'creator.name') ?: $itemReq->created_by;

    //     // Cari row approval PENDING level terendah yang sudah "aktif" (aprv_datebefore != null)
    //     // Lalu pastikan user saat ini termasuk dalam daftar aprv_username (support ; atau ,)
    //     $currentPending = TrApproval::query()
    //         ->where('refnbr', $itemReq->irid)
    //         ->where('aprv_doctype', $doctype)
    //         ->where('status', 'P')
    //         ->whereNotNull('aprv_datebefore')
    //         ->orderByRaw("CAST(aprv_leveling AS numeric) ASC")
    //         ->first();

    //     if (!$currentPending) {
    //         return response()->json(['success' => false, 'message' => "No active approval step."], 403);
    //     }

    //     // Apakah user berhak approve di step ini?
    //     $list = preg_split('/[;,]/', (string)$currentPending->aprv_username);
    //     $list = array_filter(array_map('trim', (array)$list));
    //     $canApprove = in_array(strtolower($user->username), array_map('strtolower', $list), true);

    //     if (!$canApprove) {
    //         return response()->json(['success' => false, 'message' => "You can't approve!"], 403);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // 1) Set current approver -> Approved
    //         $currentPending->status        = 'A';
    //         $currentPending->aprv_dateafter= $now;
    //         // opsional: cap keberadaan approver aktual
    //         $currentPending->aprv_username = $user->username;
    //         $currentPending->aprv_name     = $user->name;
    //         $currentPending->save();

    //         // Update header informasi "terakhir diproses"
    //         $itemReq->completed_by = $user->username;
    //         $itemReq->completed_at = $now;
    //         $itemReq->save();

    //         // 2) Masih ada pending lain?
    //         $pendingCount = TrApproval::query()
    //             ->where('refnbr', $itemReq->irid)
    //             ->where('aprv_doctype', $doctype)
    //             ->where('status', 'P')
    //             ->count();

    //         $eid = Hashids::encode($itemReq->id);
    //         $subjectMap = [
    //             'P' => 'Waiting Approval',
    //             'R' => 'Rejected Approval',
    //             'D' => 'Revise Approval',
    //             'A' => 'Approved',
    //             'C' => 'Completed',
    //         ];

    //         if ($pendingCount === 0) {
    //             // 3) Tidak ada approver lagi -> dokumen complete
    //             $itemReq->status       = 'C';
    //             $itemReq->completed_by = $user->username;
    //             $itemReq->completed_at = $now;
    //             $itemReq->save();

    //             // Close semua detail
    //             TrItemRequestdetail::where('irid', $itemReq->irid)->update(['status' => 'C']);

    //             // Kirim email ke requester (creator)
    //             $status        = 'C';
    //             $subjectSuffix = $subjectMap[$status] ?? 'Notification';

    //             $data = [
    //                 'docid'     => $itemReq->irid,
    //                 'cpnyid'    => $itemReq->cpny_id ?? $itemReq->cpnyid ?? '',
    //                 'deptname'  => $itemReq->department_id ?? $itemReq->departementid ?? '',
    //                 'date'      => $itemReq->itemReqdate,
    //                 'fullname'  => $fullname,
    //                 'name'      => $fullname,
    //                 'createdby' => $fullname,
    //                 'docname'   => 'Item Request',
    //                 'info'      => $itemReq->inventory_descr_req,
    //                 'status'    => $status,
    //                 'url'       => url('/showitemrequests/' . $eid),
    //             ];

    //             $recipients = User::where('username', $itemReq->created_by)
    //                 ->where('status', 'A')
    //                 ->get();

    //             foreach ($recipients as $rcp) {
    //                 try {
    //                     $to = $rcp->notification_email ?? $rcp->email;
    //                     Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    //                         $message->to($to)
    //                             ->subject($data['docid'] . ' - ' . $subjectSuffix . ' Item Request')
    //                             ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //                     });
    //                 } catch (\Throwable $e) {
    //                     Log::error('Failed sending Item Request completion email', ['error' => $e->getMessage()]);
    //                 }
    //             }

    //         } else {
    //             // 4) Masih ada approver berikutnya -> aktifkan step berikutnya (level terendah)
    //             $next = TrApproval::query()
    //                 ->where('refnbr', $itemReq->irid)
    //                 ->where('aprv_doctype', $doctype)
    //                 ->where('status', 'P')
    //                 ->orderByRaw("CAST(aprv_leveling AS numeric) ASC")
    //                 ->first();

    //             if ($next) {
    //                 // Stempel "datebefore" untuk approver berikutnya
    //                 if (empty($next->aprv_datebefore)) {
    //                     $next->aprv_datebefore = $now;
    //                     $next->save();
    //                 }

    //                 // Kirim email ke approver level berikutnya via ApprovalController (reusable)
    //                 app(ApprovalController::class)->notifyFirstApprover(
    //                     $itemReq->irid,
    //                     $doctype,
    //                     'P',
    //                     'Item Request',
    //                     url('/showitemrequests/' . $eid),
    //                     [
    //                         'info'      => $itemReq->inventory_descr_req,
    //                         'createdby' => $itemReq->created_by,
    //                         'date'      => $now->toDateTimeString(),
    //                     ]
    //                 );
    //             }
    //         }

    //         DB::commit();
    //         return response()->json(['success' => true, 'message' => 'Task approved successfully']);

    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Approve Item Request failed', ['error' => $e->getMessage()]);
    //         return response()->json(['success' => false, 'message' => 'Approve failed'], 500);
    //     }
    // }
    
    // public function rejectItemRequest(Request $request, $docid)
    // {
    //     $now     = Carbon::now();
    //     $user    = $request->user();
    //     $doctype = 'SR';

    //     // Header + creator
    //     $itemReq = TrItemRequest::with('creator')->where('irid', $docid)->first();
    //     if (!$itemReq) {
    //         return response()->json(['success' => false, 'message' => 'Task not found'], 404);
    //     }
    //     $fullname = data_get($itemReq, 'creator.name') ?: $itemReq->created_by;

    //     // Row approval aktif (pending + sudah "dibuka" datebefore)
    //     $currentPending = TrApproval::query()
    //         ->where('refnbr', $itemReq->irid)
    //         ->where('aprv_doctype', $doctype)
    //         ->where('status', 'P')
    //         ->whereNotNull('aprv_datebefore')
    //         ->orderByRaw("CAST(aprv_leveling AS numeric) ASC")
    //         ->first();

    //     if (!$currentPending) {
    //         return response()->json(['success' => false, 'message' => "No active approval step."], 403);
    //     }

    //     // Cek apakah user termasuk approver di step ini
    //     $list = preg_split('/[;,]/', (string)$currentPending->aprv_username);
    //     $list = array_filter(array_map('trim', (array)$list));
    //     $canReject = in_array(strtolower($user->username), array_map('strtolower', $list), true);

    //     if (!$canReject) {
    //         return response()->json(['success' => false, 'message' => "You can't reject!"], 403);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // 1) Tandai approval saat ini sebagai Rejected
    //         $currentPending->status         = 'R';
    //         $currentPending->aprv_dateafter = $now;
    //         // catat siapa yang mengeksekusi
    //         $currentPending->aprv_username  = $user->username;
    //         $currentPending->aprv_name      = $user->name;
    //         $currentPending->save();

    //         // 2) Update header Item Request -> Rejected
    //         $itemReq->status       = 'R';
    //         $itemReq->completed_by = $user->username;
    //         $itemReq->completed_at = $now;
    //         $itemReq->save();

    //         // 3) Batalkan semua approval yang masih pending (status 'X')
    //         TrApproval::query()
    //             ->where('refnbr', $itemReq->irid)
    //             ->where('aprv_doctype', $doctype)
    //             ->where('status', 'P')
    //             ->update(['status' => 'X']);

    //         DB::commit();
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Reject Item Request failed', ['docid' => $docid, 'error' => $e->getMessage()]);
    //         return response()->json(['success' => false, 'message' => 'Reject failed'], 500);
    //     }

    //     // 4) Kirim Email ke requester (creator) -> Rejected
    //     try {
    //         $status       = 'R';
    //         $subjectMap   = [
    //             'P' => 'Waiting Approval',
    //             'R' => 'Rejected Approval',
    //             'D' => 'Revise Approval',
    //             'A' => 'Approved',
    //             'C' => 'Completed',
    //         ];
    //         $subjectSuffix = $subjectMap[$status] ?? 'Notification';
    //         $eid           = Hashids::encode($itemReq->id);

    //         $data = [
    //             'docid'     => $itemReq->irid,
    //             'cpnyid'    => $itemReq->cpny_id ?? $itemReq->cpnyid ?? '',
    //             'deptname'  => $itemReq->department_id ?? $itemReq->departementid ?? '',
    //             'date'      => $now->toDateString(),
    //             'fullname'  => $fullname,
    //             'name'      => $fullname,
    //             'createdby' => $fullname,
    //             'docname'   => 'Item Request',
    //             'info'      => $itemReq->inventory_descr_req,
    //             'status'    => $status,
    //             'url'       => url('/showitemrequests/' . $eid),
    //         ];

    //         $recipients = User::where('username', $itemReq->created_by)
    //             ->where('status', 'A')
    //             ->get();

    //         foreach ($recipients as $rcp) {
    //             $to = $rcp->notification_email ?? $rcp->email;
    //             if (!$to) continue;

    //             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    //                 $message->to($to)
    //                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' Item Request')
    //                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //             });
    //         }
    //     } catch (\Throwable $e) {
    //         Log::error('Failed sending Item Request rejected email', [
    //             'docid' => $itemReq->irid,
    //             'error' => $e->getMessage()
    //         ]);
    //     }

    //     // 5) Simpan komentar penolakan (jika ada)
    //     try {
    //         app('App\Http\Controllers\SendCommentController')->sendmsg($itemReq->id, $doctype, $request);
    //     } catch (\Throwable $e) {
    //         Log::warning('SendComment after reject failed', [
    //             'docid' => $itemReq->irid,
    //             'error' => $e->getMessage()
    //         ]);
    //     }

    //     return response()->json(['success' => true, 'message' => 'Item Request rejected successfully']);
    // }

    // public function reviseItemRequest(Request $request, $docid)
    // {
    //     $now     = Carbon::now();
    //     $user    = $request->user();
    //     $doctype = 'SR';

    //     // 1) Ambil header + creator
    //     $itemReq = TrItemRequest::with('creator')->where('irid', $docid)->first();
    //     if (!$itemReq) {
    //         return response()->json(['success' => false, 'message' => 'Item Request not found'], 404);
    //     }
    //     $fullname = data_get($itemReq, 'creator.name') ?: $itemReq->created_by;

    //     // 2) Validasi: user harus approver aktif (status P) pada step terendah yang sudah "dibuka" (aprv_datebefore != null)
    //     $currentPending = TrApproval::query()
    //         ->where('refnbr', $itemReq->irid)
    //         ->where('aprv_doctype', $doctype)
    //         ->where('status', 'P')
    //         ->whereNotNull('aprv_datebefore')
    //         ->orderByRaw("CAST(aprv_leveling AS numeric) ASC")
    //         ->first();

    //     if (!$currentPending) {
    //         return response()->json(['success' => false, 'message' => "No active approval step."], 403);
    //     }

    //     // 3) Cek user termasuk approver di step ini (mendukung ; atau ,)
    //     $list = preg_split('/[;,]/', (string)$currentPending->aprv_username);
    //     $list = array_filter(array_map('trim', (array)$list));
    //     $canRevise = in_array(strtolower($user->username), array_map('strtolower', $list), true);

    //     if (!$canRevise) {
    //         return response()->json(['success' => false, 'message' => "You can't revise!"], 403);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // 4) Tandai approval saat ini sebagai Revise (D)
    //         $currentPending->status         = 'D';
    //         $currentPending->aprv_dateafter = $now;
    //         // catat eksekutor aktual
    //         $currentPending->aprv_username  = $user->username;
    //         $currentPending->aprv_name      = $user->name;
    //         $currentPending->save();

    //         // 5) Update header Item Request -> D (Revise)
    //         $itemReq->status       = 'D';
    //         $itemReq->completed_by = $user->username;
    //         $itemReq->completed_at = $now;
    //         $itemReq->save();

    //         // (opsional) tandai detail sebagai D juga kalau mau:
    //         // TrItemRequestdetail::where('irid', $itemReq->irid)->update(['status' => 'D']);

    //         // 6) Batalkan semua approval lain yang masih pending (status 'X')
    //         TrApproval::query()
    //             ->where('refnbr', $itemReq->irid)
    //             ->where('aprv_doctype', $doctype)
    //             ->where('status', 'P')
    //             ->update(['status' => 'X']);

    //         DB::commit();
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Revise Item Request failed', ['docid' => $docid, 'error' => $e->getMessage()]);
    //         return response()->json(['success' => false, 'message' => 'Revise failed'], 500);
    //     }

    //     // 7) Kirim email ke requester (creator) -> Revise
    //     try {
    //         $status        = 'D';
    //         $subjectMap    = ['P'=>'Waiting Approval','R'=>'Rejected Approval','D'=>'Revise Approval','A'=>'Approved','C'=>'Completed'];
    //         $subjectSuffix = $subjectMap[$status] ?? 'Notification';
    //         $eid           = Hashids::encode($itemReq->id);

    //         $data = [
    //             'docid'     => $itemReq->irid,
    //             'cpnyid'    => $itemReq->cpny_id ?? $itemReq->cpnyid ?? '',
    //             'deptname'  => $itemReq->department_id ?? $itemReq->departementid ?? '',
    //             'date'      => $now->toDateString(), // atau pakai $currentPending->aprv_dateafter
    //             'fullname'  => $fullname,
    //             'name'      => $fullname,
    //             'createdby' => $fullname,
    //             'docname'   => 'Item Request',
    //             'info'      => $itemReq->inventory_descr_req,
    //             'status'    => $status,
    //             'url'       => url('/showitemrequests/' . $eid),
    //         ];

    //         $recipients = User::where('username', $itemReq->created_by)
    //             ->where('status', 'A')
    //             ->get();

    //         foreach ($recipients as $rcp) {
    //             $to = $rcp->notification_email ?? $rcp->email;
    //             if (!$to) continue;

    //             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    //                 $message->to($to)
    //                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' Item Request')
    //                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //             });
    //         }
    //     } catch (\Throwable $e) {
    //         Log::error('Failed sending Item Request revise email', [
    //             'docid' => $itemReq->irid,
    //             'error' => $e->getMessage()
    //         ]);
    //     }

    //     // 8) Simpan komentar revisi (jika ada)
    //     try {
    //         app('App\Http\Controllers\SendCommentController')->sendmsg($itemReq->id, $doctype, $request);
    //     } catch (\Throwable $e) {
    //         Log::warning('SendComment after revise failed', [
    //             'docid' => $itemReq->irid,
    //             'error' => $e->getMessage()
    //         ]);
    //     }

    //     return response()->json(['success' => true, 'message' => 'Item Request revised successfully']);
    // }
    

    
    public function tracking($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $itemReq = TrItemRequest::findOrFail($id);

        $getName = function (?string $username) {
            if (!$username) return null;
            $u = \App\Models\User::where('username', $username)->first();
            return $u->name ?? $username;
        };

        $createdByName = $getName($itemReq->created_by ?? null);
        $createdAt     = $itemReq->created_at ? \Carbon\Carbon::parse($itemReq->created_at)->format('Y-m-d H:i') : null;

        $completedByName = $getName($itemReq->completed_by ?? null);
        $completedAt     = $itemReq->completed_at ? \Carbon\Carbon::parse($itemReq->completed_at)->format('Y-m-d H:i') : null;

        // kolom opsional, kalau tidak ada biarkan null
        $rejectedByName  = $getName($itemReq->rejected_by ?? null);
        $rejectedAt      = isset($itemReq->rejected_at) ? \Carbon\Carbon::parse($itemReq->rejected_at)->format('Y-m-d H:i') : null;

        $revisedByName   = $getName($itemReq->revised_by ?? null);
        $revisedAt       = isset($itemReq->revised_at) ? \Carbon\Carbon::parse($itemReq->revised_at)->format('Y-m-d H:i') : null;

        $status = (string) ($itemReq->status ?? '');
        $labelMap = [
            'P' => 'Waiting approval',
            'R' => 'Rejected',
            'D' => 'Revise',
            'C' => 'Completed',
        ];
        $statusLabel = $labelMap[$status] ?? $status;

        // selalu mulai dari Submitted
        $steps = [[
            'key'          => 'submitted',
            'title'        => 'Item Request',
            'status'       => 'C',              // dibuat = completed
            'status_label' => 'Submitted',
            'by'           => $createdByName,
            'at'           => $createdAt,
        ]];

        switch ($status) {
            case 'P':
                // masih menunggu/berjalan → tampilkan Approval saja
                $steps[] = [
                    'key'          => 'approval',
                    'title'        => 'Approval',
                    'status'       => 'P',
                    'status_label' => 'Waiting approval',
                    'by'           => $completedByName,
                    'at'           => $completedAt,
                ];
                break;

            case 'R':
                // DITOLAK → langsung Submitted → Rejected (tanpa Approval)
                $steps[] = [
                    'key'          => 'rejected',
                    'title'        => 'Rejected',
                    'status'       => 'R',
                    'status_label' => 'Rejected',
                    'by'           => $completedByName,
                    'at'           => $completedAt,
                ];
                break;

            case 'D':
                // REVISE → Submitted → Revise
                $steps[] = [
                    'key'          => 'revise',
                    'title'        => 'Revise',
                    'status'       => 'D',
                    'status_label' => 'Revise',
                    'by'           => $completedByName,
                    'at'           => $completedAt,
                ];
                break;

            case 'C':
                // SELESAI → bisa langsung Submitted → Completed
                // (kalau kamu ingin menampilkan Approval yang sudah dilalui,
                // tambahkan step 'approval' sebelum 'completed')
                $steps[] = [
                    'key'          => 'completed',
                    'title'        => 'Completed',
                    'status'       => 'C',
                    'status_label' => 'Completed',
                    'by'           => $completedByName,
                    'at'           => $completedAt,
                ];
                break;

            default:
                // status tidak dikenal → biarkan hanya Submitted
                break;
        }

        return response()->json([
            'doc'   => $itemReq->irid ?? (string)$itemReq->id,
            'steps' => $steps,
            'status'=> $status,
            'status_label' => $statusLabel,
        ]);
    }

    public function printItemRequest($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Ambil Item Request + relasi yang dibutuhkan
        $itemReq = TrItemRequest::with([
                'requestType:requesttypeid,requesttype_name',
                'creator:username,name',
            ])
            ->findOrFail($id);

        // Detail baris Item Request
        $itemReqdetail = TrItemRequestdetail::with([
                'location:location_id,location_name',
                'subLocation:sub_location_id,sub_location_name',
            ])
            ->where('irid', $itemReq->irid)
            ->get();

        // Approval list (non-cancelled)
        // $approval = T_approval::where('docid', $itemReq->irid)
        //     ->where('status', '<>', 'X')
        //     ->orderBy('aprvid')
        //     ->orderBy('created_at')
        //     ->get();
        $approval = TrApproval::query()
            ->where('refnbr', $itemReq->irid)          // dulu: docid
            ->where('status', '<>', 'X')           
            ->orderByRaw('CAST(aprv_leveling AS numeric) ASC')
            ->orderBy('created_at', 'ASC')            // tie-breaker kalau leveling sama
            ->get();

        $approve_count = $approval->count();

        // Company (handle null)
        $company = MsCompany::where('cpny_id', $itemReq->cpny_id)->first();

        // Mapping status dokumen
        switch ($itemReq->status) {
            case 'R':
                $status_doc = 'Rejected';
                break;
            case 'C':
                $status_doc = 'Completed';
                break;
            case 'D':
                $status_doc = 'Hold';
                break;
            case 'X':
                $status_doc = 'Cancel';
                break;
            default:
                $status_doc = 'On Progress';
                break;
        }

        $data = [
            'title'               => 'Surat Permintaan Pembelian Barang',
            'doc_type'            => 'Item Request',
            'docid'               => $itemReq->irid,
            'department_id'       => $itemReq->department_id,
            'cpnyname'            => optional($company)->cpny_name,
            'parent'              => optional($company)->parent,
            'project'             => optional($company)->project,
            // identitas & tanggal
            'created_by_username' => $itemReq->created_by,
            'created_by_name'     => ucwords(strtolower(optional($itemReq->creator)->name)),
            'created_at_fmt'      => optional($itemReq->created_at)->format('d F Y'),
            'req_date_fmt'        => optional($itemReq->created_at)->format('d M Y H:i'),
            'itemReqdate'            => \Carbon\Carbon::parse($itemReq->itemReqdate)->format('d F Y'),
            // konten
            'keperluan'           => $itemReq->inventory_descr_req,
            'status_doc'          => $status_doc,
            'requesttype_name'    => optional($itemReq->requestType)->requesttype_name,
        ];

        // Kirim ke view
        $pdf = \PDF::loadView(
            'pages.itemrequests.pdf_itemrequests',
            array_merge($data, [
                'detail'         => $itemReqdetail,
                'approval'       => $approval,
                'approve_count'  => $approve_count,
            ])
        );

        // Portrait jika <= 5 approver, else landscape
        $pdf->setPaper('A4', ($approve_count <= 5) ? 'portrait' : 'landscape');

        return $pdf->stream("pdf_itemrequests_{$itemReq->irid}.pdf");
    }





    






}
