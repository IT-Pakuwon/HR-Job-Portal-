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
use App\Models\TrVoucherTaxi;
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
use App\Http\Controllers\Traits\HasAutonbr;


class VoucherTaxiController extends Controller
{

    use HasAutonbr;
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $cpnyIds = is_string($user->cpny_id)
            ? array_filter(array_map('trim', explode(',', $user->cpny_id)))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_filter(array_map('trim', explode(',', $user->department_id)))
            : (array) $user->department_id;

        $q = TrVoucherTaxi::query()
            ->whereIn('cpny_id', $cpnyIds)
            ->whereIn('department_id', $deptIds);

        $all        = (clone $q)->count();
        $onProgress = (clone $q)->where('status', 'P')->count();
        $reject     = (clone $q)->where('status', 'R')->count();
        $revise     = (clone $q)->where('status', 'D')->count();
        $completed  = (clone $q)->where('status', 'C')->count();
     
        $usercpny = Usercpny::where('username', $user->username)
            ->get();       
        
        $usercpny2 = Usercpny::where('username', $user->username)
            ->first();
        $userdept = Userdept::where('username', $user->username)
            ->get();
        $userdept2 = Userdept::where('username', $user->username)
            ->first(); 
        $company = MsCompany::where('status', 'A')
            ->select('cpny_id', 'cpny_name')
            ->get(); 
            

        $requesters = User::query()
            ->whereNotNull('username')
            ->whereIn('department_id', $deptIds)
            ->where('status', 'A')
            ->select('username')
            ->orderBy('username')
            ->get();

        return view('pages.vouchertaxi.vouchertaxi', compact(
            'all',
            'onProgress',
            'reject',
            'revise',
            'completed',
            'usercpny',
            'usercpny2',
            'userdept',
            'userdept2',
            'requesters',
            'company'

        ));
    }

    public function json(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $cpnyIds = is_string($user->cpny_id)
            ? array_filter(array_map('trim', explode(',', $user->cpny_id)))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_filter(array_map('trim', explode(',', $user->department_id)))
            : (array) $user->department_id;

        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));
        $status = (string) $request->query('status', '');

        $columns = [
            0 => 'vt.docid',
            1 => 'vt.vaucher_date',
            2 => 'vt.date_used',
            3 => 'vt.cpny_id',
            4 => 'vt.department_id',
            5 => 'vt.user_peminta',
            6 => 'vt.to',
            7 => 'vt.perpose',
            8 => 'vt.status',
        ];

        $orderIdx = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'vt.docid';

        $baseTable = (new TrVoucherTaxi)->getTable();

        $base = TrVoucherTaxi::from($baseTable . ' as vt')
            ->whereIn('vt.cpny_id', $cpnyIds)
            ->whereIn('vt.department_id', $deptIds);

        if ($status !== '') {
            $base->where('vt.status', $status);
        }

        $recordsTotal = (clone $base)->count();

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('vt.docid', 'ilike', "%{$search}%")
                    ->orWhere('vt.vaucher_date', 'ilike', "%{$search}%")
                    ->orWhere('vt.date_used', 'ilike', "%{$search}%")
                    ->orWhere('vt.cpny_id', 'ilike', "%{$search}%")
                    ->orWhere('vt.department_id', 'ilike', "%{$search}%")
                    ->orWhere('vt.user_peminta', 'ilike', "%{$search}%")
                    ->orWhere('vt.to', 'ilike', "%{$search}%")
                    ->orWhere('vt.perpose', 'ilike', "%{$search}%")
                    ->orWhere('vt.status', 'ilike', "%{$search}%")
                    ->orWhere('vt.created_by', 'ilike', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $base)->count();

        $data = $base->select([
                'vt.id',
                'vt.docid',
                'vt.vaucher_date',
                'vt.date_used',
                'vt.cpny_id',
                'vt.department_id',
                'vt.user_peminta',
                'vt.to',
                'vt.perpose',
                'vt.type_trip',
                'vt.cpny_id_expense',
                'vt.user_topup',
                'vt.status',
                'vt.created_by',
            ])
            ->orderBy($orderCol, $orderDir)
            ->orderBy('vt.docid', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

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

    public function storeVoucher(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $validated = $request->validate([
            'cpny_id'          => ['required'],
            'department_id'    => ['required'],
            'user_peminta'     => ['required'],
            'date_used'        => ['required', 'date'],
            'type_trip'        => ['required'],
            'to'               => ['required'],
            'perpose'          => ['required'],
            'cpny_id_expense'  => ['required'],
            'user_topup'       => ['required'],
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $dt       = now();
            $year     = (int) $dt->year;
            $month    = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
            $doctype  = 'VCR';
            $username = $user->username;

            $cpny_id       = $validated['cpny_id_expense'];
            $department_id = $validated['department_id'];

            $approvalCtl = app(\App\Http\Controllers\ApprovalController::class);

            // validasi setup approval exist
            $approvalCtl->loadLines($doctype, $cpny_id, $department_id);

            // autonbr
            $auto = $this->nextAutonbr(
                $doctype,
                $year,
                $month,
                $username,
                'Voucher Taxi'
            );

            $urutan = (int) $auto['next'];
            $tglbln = substr((string) $year, 2) . $month;
            $docid  = $doctype . $tglbln . sprintf('%03d', $urutan);

            $voucher = TrVoucherTaxi::create([
                'docid'           => $docid,
                'vaucher_date'    => $dt->toDateString(),
                'cpny_id'         => $validated['cpny_id'],
                'department_id'   => $validated['department_id'],
                'user_peminta'    => $validated['user_peminta'],
                'to'              => $validated['to'],
                'perpose'         => $validated['perpose'],
                'date_used'       => $validated['date_used'],
                'cpny_id_expense' => $validated['cpny_id_expense'],
                'type_trip'       => $validated['type_trip'],
                'user_topup'      => $validated['user_topup'],
                'status'          => 'P',
                'created_by'      => $username,
                'created_at'      => now(),
                'updated_by'      => $username,
                'updated_at'      => now(),
            ]);

            $ctx = ['ignore_nominal' => true];

            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $docid,
                $doctype,
                $cpny_id,
                $department_id,
                $username,
                $ctx,
                $dt
            );

            $eid = Hashids::encode($voucher->id);

            $approvalCtl->notifyFirstApprover(
                $docid,
                $doctype,
                $voucher->status,
                'Voucher Taxi',
                url('/showvouchertaxi/' . $eid),
                [
                    'info'      => $voucher->perpose,
                    'createdby' => $voucher->created_by,
                    'date'      => $dt->toDateTimeString(),
                ]
            );

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Voucher Taxi berhasil dibuat',
                'redirect' => url('/showvouchertaxi/' . Hashids::encode($voucher->id))
            ]);

        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
   
    public function editVoucherTaxi($hash)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        // === HEADER (tr_item_req) ===
        $itemReq = TrVoucherTaxi::findOrFail($id);

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



    public function updateVoucherTaxi(Request $request, $docid)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validated = $request->validate([
            'cpny_id'          => ['required'],
            'department_id'    => ['required'],
            'user_peminta'     => ['required'],
            'date_used'        => ['required', 'date'],
            'type_trip'        => ['required'],
            'to'               => ['required'],
            'perpose'          => ['required'],
            'cpny_id_expense'  => ['required'],
            'user_topup'       => ['required'],
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $voucher = TrVoucherTaxi::where('docid', $docid)->firstOrFail();

            if ($voucher->status !== 'D') {
                throw new \Exception('Voucher Taxi hanya bisa diedit saat status Revise / Draft.');
            }

            if ($voucher->created_by !== $user->username) {
                throw new \Exception('Anda tidak berhak edit Voucher Taxi ini.');
            }

            $doctype       = 'VCR';
            $dt            = now();
            $username      = $user->username;
            $cpny_id       = $validated['cpny_id_expense'];
            $department_id = $validated['department_id'];

            $approvalCtl = app(\App\Http\Controllers\ApprovalController::class);

            // Validasi approval setup baru
            $approvalCtl->loadLines($doctype, $cpny_id, $department_id);

            $voucher->cpny_id          = $validated['cpny_id'];
            $voucher->department_id    = $validated['department_id'];
            $voucher->user_peminta     = $validated['user_peminta'];
            $voucher->to               = $validated['to'];
            $voucher->perpose          = $validated['perpose'];
            $voucher->date_used        = $validated['date_used'];
            $voucher->cpny_id_expense  = $validated['cpny_id_expense'];
            $voucher->type_trip        = $validated['type_trip'];
            $voucher->user_topup       = $validated['user_topup'];
            $voucher->status           = 'P';
            // $voucher->completed_by     = null;
            // $voucher->completed_at     = null;
            $voucher->updated_by       = $username;
            $voucher->updated_at       = $dt;
            $voucher->save();

            // TrApproval::where('refnbr', $voucher->docid)
            //     ->where('doctype', $doctype)
            //     ->delete();

            $ctx = [
                'ignore_nominal' => true
            ];

            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $voucher->docid,
                $doctype,
                $cpny_id,
                $department_id,
                $username,
                $ctx,
                $dt
            );

            if ($firstApprovalUsernames) {
                $voucher->completed_by = is_array($firstApprovalUsernames)
                    ? implode(',', $firstApprovalUsernames)
                    : $firstApprovalUsernames;

                $voucher->completed_at = $dt;
                $voucher->save();
            }

            $eid = Hashids::encode($voucher->id);

            $approvalCtl->notifyFirstApprover(
                $docid,
                $doctype,
                $voucher->status,
                'Voucher Taxi',
                url('/showvouchertaxi/' . $eid),
                [
                    'info'      => $voucher->perpose,
                    'createdby' => $voucher->created_by,
                    'date'      => $dt->toDateTimeString(),
                ]
            );


            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Voucher Taxi berhasil diupdate dan dikirim ulang approval.',
                'redirect' => url('/showvouchertaxi/' . Hashids::encode($voucher->id))
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
            

    public function showVoucherTaxi($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $voucher = TrVoucherTaxi::with([
            'creator:username,name',
        ])->findOrFail($id);

        $company = MsCompany::where('status', 'A')
            ->select('cpny_id', 'cpny_name')
            ->get();

        $canProcessGaAdvice = TrApproval::where('refnbr', $voucher->docid)            
            ->where('aprv_leveling', 2)
            ->where('status', 'P')
            ->whereNotNull('aprv_datebefore')
            ->exists();

        return view('pages.vouchertaxi.showvouchertaxi', compact(
            'voucher',
            'hash',
            'company',
            'canProcessGaAdvice'
        ));
    }

    public function updateGaAdvice(Request $request, $docid)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validated = $request->validate([
            'max_budget'      => ['required', 'numeric', 'min:0'],
            'max_trip'        => ['required', 'numeric', 'min:0'],
            'cpny_id_expense' => ['required', 'string'],
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $voucher = TrVoucherTaxi::where('docid', $docid)->firstOrFail();

            $voucher->max_budget      = $validated['max_budget'];
            $voucher->max_trip        = $validated['max_trip'];
            $voucher->cpny_id_expense = $validated['cpny_id_expense'];
            $voucher->checked_by      = $user->username;
            $voucher->checked_at      = now();
            $voucher->updated_by      = $user->username;
            $voucher->updated_at      = now();
            $voucher->save();

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'GA Advice berhasil disimpan.',
                'data' => [
                    'max_budget'      => $voucher->max_budget,
                    'max_trip'        => $voucher->max_trip,
                    'cpny_id_expense' => $voucher->cpny_id_expense,
                    'checked_by'      => $voucher->checked_by,
                ]
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

      
    public function approveVoucherTaxi(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'VCR';

        $voucher = TrVoucherTaxi::with('creator')
            ->where('docid', $docid)
            ->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher Taxi not found'
            ], 404);
        }

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($voucher->id);
        $docUrl   = url('/showvouchertaxi/' . $eid);
        $fullname = data_get($voucher, 'creator.name') ?: $voucher->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->approveStep(
            $voucher->docid,
            $doctype,
            $user->username,
            $user->name,

            // complete: update header + email creator complete
            function (string $refnbr, \Carbon\Carbon $now) use ($voucher, $fullname, $docUrl) {
                $voucher->status       = 'C';
                $voucher->completed_by = $voucher->completed_by ?: auth()->user()->username;
                $voucher->completed_at = $now;
                $voucher->updated_by   = auth()->user()->username;
                $voucher->updated_at   = $now;
                $voucher->save();

                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $voucher->docid,
                    'Voucher Taxi',
                    'C',
                    $voucher->created_by,
                    $docUrl,
                    [
                        'cpnyid'    => $voucher->cpny_id ?? '',
                        'deptname'  => $voucher->department_id ?? '',
                        'date'      => $voucher->vaucher_date,
                        'info'      => $voucher->perpose,
                        'fullname'  => $fullname,
                        'name'      => $fullname,
                        'createdby' => $fullname,
                    ]
                );
            },

            // notify next approver
            function ($next, \Carbon\Carbon $now) use ($voucher, $docUrl) {
                app(\App\Http\Controllers\ApprovalController::class)->notifyFirstApprover(
                    $voucher->docid,
                    'VCR',
                    'P',
                    'Voucher Taxi',
                    $docUrl,
                    [
                        'info'      => $voucher->perpose,
                        'createdby' => $voucher->created_by,
                        'date'      => $now->toDateTimeString(),
                    ]
                );

                // jejak terakhir diproses
                $voucher->completed_by = auth()->user()->username;
                $voucher->completed_at = $now;
                $voucher->updated_by   = auth()->user()->username;
                $voucher->updated_at   = $now;
                $voucher->save();
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Approve failed'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Voucher Taxi approved successfully'
        ]);
    }

    public function rejectVoucherTaxi(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'VCR';

        $voucher = TrVoucherTaxi::with('creator')
            ->where('docid', $docid)
            ->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher Taxi not found'
            ], 404);
        }

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($voucher->id);
        $docUrl   = url('/showvouchertaxi/' . $eid);
        $fullname = data_get($voucher, 'creator.name') ?: $voucher->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->rejectStep(
            $voucher->docid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($voucher, $fullname, $docUrl) {
                $voucher->status       = 'R';
                $voucher->completed_by = auth()->user()->username;
                $voucher->completed_at = $now;
                $voucher->updated_by   = auth()->user()->username;
                $voucher->updated_at   = $now;
                $voucher->save();

                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $voucher->docid,
                    'Voucher Taxi',
                    'R',
                    $voucher->created_by,
                    $docUrl,
                    [
                        'cpnyid'    => $voucher->cpny_id ?? '',
                        'deptname'  => $voucher->department_id ?? '',
                        'date'      => $now->toDateString(),
                        'info'      => $voucher->perpose ?? '',
                        'fullname'  => $fullname,
                        'name'      => $fullname,
                        'createdby' => $fullname,
                    ]
                );

                try {
                    app(\App\Http\Controllers\SendCommentController::class)
                        ->sendmsg($voucher->id, 'VCR', request());
                } catch (\Throwable $e) {
                    \Log::warning('Send reject comment Voucher Taxi failed', [
                        'docid' => $voucher->docid,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Reject failed'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Voucher Taxi rejected successfully'
        ]);
    }

    public function reviseVoucherTaxi(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'VCR';

        $voucher = TrVoucherTaxi::with('creator')
            ->where('docid', $docid)
            ->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher Taxi not found'
            ], 404);
        }

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($voucher->id);
        $docUrl   = url('/showvouchertaxi/' . $eid);
        $fullname = data_get($voucher, 'creator.name') ?: $voucher->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->reviseStep(
            $voucher->docid,          // refnbr
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($voucher, $fullname, $docUrl) {

                // === HEADER -> D (Revise) ===
                $voucher->status       = 'D';
                $voucher->completed_by = auth()->user()->username;
                $voucher->completed_at = $now;
                $voucher->updated_by   = auth()->user()->username;
                $voucher->updated_at   = $now;
                $voucher->save();

                // === Email ke requester ===
                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $voucher->docid,
                    'Voucher Taxi',
                    'D',
                    $voucher->created_by,
                    $docUrl,
                    [
                        'cpnyid'    => $voucher->cpny_id ?? '',
                        'deptname'  => $voucher->department_id ?? '',
                        'date'      => $now->toDateString(),
                        'info'      => $voucher->perpose ?? '',
                        'fullname'  => $fullname,
                        'name'      => $fullname,
                        'createdby' => $fullname,
                    ]
                );

                // === Simpan komentar ===
                try {
                    app(\App\Http\Controllers\SendCommentController::class)
                        ->sendmsg($voucher->id, 'VCR', request());
                } catch (\Throwable $e) {
                    \Log::warning('Send revise comment Voucher Taxi failed', [
                        'docid' => $voucher->docid,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Revise failed'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Voucher Taxi revised successfully'
        ]);
    }

        
    public function tracking($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $itemReq = TrVoucherTaxi::findOrFail($id);

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

    public function printVoucherTaxiuest($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Ambil Item Request + relasi yang dibutuhkan
        $itemReq = TrVoucherTaxi::with([
                'requestType:requesttypeid,requesttype_name',
                'creator:username,name',
            ])
            ->findOrFail($id);

        // Detail baris Item Request
        $itemReqdetail = TrVoucherTaxidetail::with([
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
