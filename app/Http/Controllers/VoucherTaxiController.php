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
use App\Models\SysRole;
use App\Models\TrMessage;


class VoucherTaxiController extends Controller
{

    use HasAutonbr;
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // 🔹 Normalize company & department
        $cpnyIds = is_string($user->cpny_id)
            ? array_filter(array_map('trim', explode(',', $user->cpny_id)))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_filter(array_map('trim', explode(',', $user->department_id)))
            : (array) $user->department_id;

        // 🔥 CORRECT ROLE CHECK (same as json)
        $isGA = $user->hasRole('GAACCESS');

        // 🔹 Base query
        $q = TrVoucherTaxi::query();

        // 🔥 APPLY FILTER ONLY IF NOT GA
        if (!$isGA) {
            if (!empty($cpnyIds)) {
                $q->whereIn(DB::raw('TRIM(cpny_id)'), $cpnyIds);
            }

            if (!empty($deptIds)) {
                $q->whereIn(DB::raw('TRIM(department_id)'), $deptIds);
            }
        }

        // 🔹 Counts
        $all        = (clone $q)->count();
        $onProgress = (clone $q)->where('status', 'P')->count();
        $reject     = (clone $q)->where('status', 'R')->count();
        $revise     = (clone $q)->where('status', 'D')->count();
        $completed  = (clone $q)->where('status', 'C')->count();

        // 🔹 Other data (unchanged)
        $usercpny = Usercpny::where('username', $user->username)->get();
        $usercpny2 = Usercpny::where('username', $user->username)->first();

        $userdept = Userdept::where('username', $user->username)->get();
        $userdept2 = Userdept::where('username', $user->username)->first();

        $company = MsCompany::where('status', 'A')
            ->select('cpny_id', 'cpny_name')
            ->get();

        $requesters = User::query()
            ->whereNotNull('username')
            ->where('status', 'A')
            ->select('username', 'name', 'department_id')
            ->orderBy('name')
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
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        // =========================================
        // NORMALIZE COMPANY & DEPARTMENT
        // =========================================

        $cpnyIds = is_string($user->cpny_id)
            ? array_filter(array_map('trim', explode(',', $user->cpny_id)))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_filter(array_map('trim', explode(',', $user->department_id)))
            : (array) $user->department_id;

        // =========================================
        // ROLE CHECK
        // =========================================

        $isGA = $user->hasRole('GAACCESS');

        // =========================================
        // DATATABLE PARAMS
        // =========================================

        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);

        $search = trim((string) $request->input('search.value', ''));

        $status = trim((string) $request->query('status', ''));

        // =========================================
        // COLUMN MAP
        // =========================================

        $columns = [
            0 => 'vt.docid',
            1 => 'vt.voucher_date',
            2 => 'vt.date_used',
            3 => 'vt.cpny_id',
            4 => 'vt.department_id',
            5 => 'vt.user_peminta',
            6 => 'vt.origin',
            7 => 'vt.destination',
            8 => 'vt.purpose',
            9 => 'vt.status',
        ];

        $orderIdx = (int) $request->input('order.0.column', 0);

        $orderDir = $request->input('order.0.dir', 'desc') === 'asc'
            ? 'asc'
            : 'desc';

        $orderCol = $columns[$orderIdx] ?? 'vt.docid';

        // =========================================
        // BASE QUERY
        // =========================================

        $base = TrVoucherTaxi::from('tr_voucher_taxi as vt');

        // =========================================
        // STATUS FILTER
        // ONLY SHOW ACTIVE STATUS
        // =========================================

        $base->whereIn('vt.status', ['P', 'C', 'D', 'R']);

        // =========================================
        // ROLE FILTER
        // =========================================

        // 👨‍💼 GAACCESS
        // can see everything

        if (!$isGA) {

            $base->where(function ($q) use ($user, $cpnyIds, $deptIds) {

                // =====================================
                // OWN VOUCHER
                // =====================================

                $q->whereRaw(
                    'LOWER(TRIM(vt.created_by)) = ?',
                    [strtolower(trim($user->username))]
                );

                // =====================================
                // SAME COMPANY + SAME DEPARTMENT
                // =====================================

                $q->orWhere(function ($sub) use ($cpnyIds, $deptIds) {

                    if (!empty($cpnyIds)) {
                        $sub->whereIn(
                            DB::raw('TRIM(vt.cpny_id)'),
                            $cpnyIds
                        );
                    }

                    if (!empty($deptIds)) {
                        $sub->whereIn(
                            DB::raw('TRIM(vt.department_id)'),
                            $deptIds
                        );
                    }
                });
            });
        }

        // =========================================
        // EXTRA STATUS FILTER FROM REQUEST
        // =========================================

        if ($status !== '') {
            $base->where('vt.status', $status);
        }

        // =========================================
        // TOTAL BEFORE SEARCH
        // =========================================

        $recordsTotal = (clone $base)->count();

        // =========================================
        // SEARCH
        // =========================================

        if ($search !== '') {

            $base->where(function ($q) use ($search) {

                $q->where('vt.docid', 'like', "%{$search}%")
                    ->orWhere('vt.voucher_date', 'like', "%{$search}%")
                    ->orWhere('vt.date_used', 'like', "%{$search}%")
                    ->orWhere('vt.cpny_id', 'like', "%{$search}%")
                    ->orWhere('vt.department_id', 'like', "%{$search}%")
                    ->orWhere('vt.user_peminta', 'like', "%{$search}%")
                    ->orWhere('vt.origin', 'like', "%{$search}%")
                    ->orWhere('vt.destination', 'like', "%{$search}%")
                    ->orWhere('vt.purpose', 'like', "%{$search}%")
                    ->orWhere('vt.status', 'like', "%{$search}%")
                    ->orWhere('vt.created_by', 'like', "%{$search}%");
            });
        }

        // =========================================
        // TOTAL AFTER SEARCH
        // =========================================

        $recordsFiltered = (clone $base)->count();

        // =========================================
        // FETCH DATA
        // =========================================

        $data = $base
            ->select([
                'vt.id',
                'vt.docid',
                'vt.voucher_date',
                'vt.date_used',
                'vt.cpny_id',
                'vt.department_id',
                'vt.user_peminta',
                'vt.origin',
                'vt.destination',
                'vt.purpose',
                'vt.type_trip',
                'vt.cpny_id_expense',
                'vt.user_topup',
                'vt.status',
                'vt.created_by',
                'vt.actual_budget',
                'vt.max_budget',
            ])
            ->orderBy($orderCol, $orderDir)
            ->orderBy('vt.docid', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        // =========================================
        // TRANSFORM DATA
        // =========================================

        $data->transform(function ($row) {

            $row->eid = Hashids::encode($row->id);

            $row->extendedProps = [
                'eid' => $row->eid
            ];

            unset($row->id);

            return $row;
        });

        // =========================================
        // RESPONSE
        // =========================================

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
            // 'to'               => ['required'],
            'purpose'          => ['required'],
            'cpny_id_expense'  => ['required'],
            'user_topup'       => ['required'],

            // ✅ ADD THIS
            'origin'           => ['required'],
            'destination'      => ['required'],
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
                'voucher_date'    => $dt->toDateString(),
                'cpny_id'         => $validated['cpny_id'],
                'department_id'   => $validated['department_id'],
                'user_peminta'    => $validated['user_peminta'],

                // ✅ ADD HERE
                'origin'          => $validated['origin'],
                'destination'     => $validated['destination'],

                // 'to'              => $validated['to'],
                'purpose'         => $validated['purpose'],
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
                url('/vouchertaxi#' . $eid),
                [
                    'info'      => $voucher->purpose,
                    'createdby' => $voucher->created_by,
                    'date'      => $dt->toDateTimeString(),
                ]
            );

            DB::connection('pgsql5')->commit();

            // return response()->json([
            //     'success' => true,
            //     'message' => 'Voucher Taxi berhasil dibuat',
            //     'redirect' => url('/showvouchertaxi/' . Hashids::encode($voucher->id))
            // ]);

            return response()->json([
                'success' => true,
                'message' => 'Voucher Taxi berhasil dibuat',
                'data' => [
                    'docid' => $voucher->docid
                ]
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
            // 'to'               => ['required'],
            'purpose'          => ['required'],
            'cpny_id_expense'  => ['required'],
            'user_topup'       => ['required'],
            'origin'      => ['required'],
            'destination' => ['required'],
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
            // $voucher->to               = $validated['to'];
            $voucher->purpose          = $validated['purpose'];
            $voucher->date_used        = $validated['date_used'];
            $voucher->cpny_id_expense  = $validated['cpny_id_expense'];
            $voucher->type_trip        = $validated['type_trip'];
            $voucher->user_topup       = $validated['user_topup'];
            $voucher->origin      = $validated['origin'];
            $voucher->destination = $validated['destination'];
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
                url('/vouchertaxi#' . $eid),
                [
                    'info'      => $voucher->purpose,
                    'createdby' => $voucher->created_by,
                    'date'      => $dt->toDateTimeString(),
                ]
            );


            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Voucher Taxi berhasil diupdate dan dikirim ulang approval.',
                'data' => [
                        'docid' => $voucher->docid
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

    public function cancel(Request $request, $docid)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        DB::beginTransaction();

        try {

            $voucher = TrVoucherTaxi::where('docid', $docid)
                ->firstOrFail();

            // 🔥 only creator
            if (
                strtolower(trim($voucher->created_by)) !==
                strtolower(trim($user->username))
            ) {

                return response()->json([
                    'success' => false,
                    'message' => 'You cannot cancel this request'
                ], 403);
            }

            // 🔥 only revise
            if ($voucher->status !== 'D') {

                return response()->json([
                    'success' => false,
                    'message' => 'Only revise document can be cancelled'
                ], 400);
            }

            // 🔥 cancel header
            $voucher->status       = 'X';
            $voucher->updated_by   = $user->username;
            $voucher->updated_at   = now();
            $voucher->completed_by = $user->username;
            $voucher->completed_at = now();
            $voucher->save();

            // 🔥 cancel remaining approvals
            TrApproval::where('refnbr', $voucher->docid)
                ->where('status', 'P')
                ->update([
                    'status' => 'X',
                    'updated_by' => $user->username,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Voucher request cancelled successfully'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

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

    public function detail($eid)
    {
        $id = Hashids::decode($eid)[0] ?? null;

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid voucher ID'
            ], 404);
        }

        $voucher = TrVoucherTaxi::find($id);

        if (!$voucher) {

            return response()->json([
                'success' => false,
                'message' => 'Voucher not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [

                'docid' => $voucher->docid,
                'eid' => $eid,

                'status' => $voucher->status,

                'user_peminta' => $voucher->user_peminta,
                'created_by' => $voucher->created_by,

                'date_used' => $voucher->date_used,

                'origin' => $voucher->origin,
                'destination' => $voucher->destination,

                'purpose' => $voucher->purpose,

                'type_trip' => $voucher->type_trip,

                'cpny_id' => $voucher->cpny_id,
                'department_id' => $voucher->department_id,

                'cpny_id_expense' => $voucher->cpny_id_expense,
                'user_topup' => $voucher->user_topup,

                'actual_budget' => $voucher->actual_budget,
                'max_budget' => $voucher->max_budget,

                'revise_reason' => $voucher->revise_reason,
            ]
        ]);
    }

    // public function updateGaAdvice(Request $request, $docid)
    // {
    //     $user = Auth::user();

    //     if (!$user) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Unauthorized'
    //         ], 401);
    //     }

    //     $validated = $request->validate([
    //         'max_budget'      => ['required', 'numeric', 'min:0'],
    //         'max_trip'        => ['required', 'numeric', 'min:0'],
    //         'cpny_id_expense' => ['required', 'string'],
    //     ]);

    //     DB::connection('pgsql5')->beginTransaction();

    //     try {
    //         $voucher = TrVoucherTaxi::where('docid', $docid)->firstOrFail();

    //         $voucher->max_budget      = $validated['max_budget'];
    //         $voucher->max_trip        = $validated['max_trip'];
    //         $voucher->cpny_id_expense = $validated['cpny_id_expense'];
    //         $voucher->checked_by      = $user->username;
    //         $voucher->checked_at      = now();
    //         $voucher->updated_by      = $user->username;
    //         $voucher->updated_at      = now();
    //         $voucher->save();

    //         DB::connection('pgsql5')->commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'GA Advice berhasil disimpan.',
    //             'data' => [
    //                 'max_budget'      => $voucher->max_budget,
    //                 'max_trip'        => $voucher->max_trip,
    //                 'cpny_id_expense' => $voucher->cpny_id_expense,
    //                 'checked_by'      => $voucher->checked_by,
    //             ]
    //         ]);
    //     } catch (\Throwable $e) {
    //         DB::connection('pgsql5')->rollBack();

    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function updateGaAdvice(Request $request, $docid)
    {
        $request->merge([
            'actual_budget' => str_replace('.', '', $request->actual_budget)
        ]);
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // 🔥 ONLY GA
        if (!$user->hasRole('GAACCESS')) {

            return response()->json([
                'success' => false,
                'message' => 'Forbidden'
            ], 403);
        }

        $validated = $request->validate([
            'actual_budget' => ['required', 'numeric', 'min:0'],
        ]);

        DB::beginTransaction();

        try {

            $voucher = TrVoucherTaxi::where('docid', $docid)
                ->firstOrFail();

            // 🔥 MUST BE COMPLETED FIRST
            if ($voucher->status !== 'C') {

                return response()->json([
                    'success' => false,
                    'message' => 'Voucher not completed yet'
                ], 400);
            }

            $voucher->update([
                'actual_budget' => $validated['actual_budget'],
                'checked_by'    => $user->username,
                'checked_at'    => now(),
                'updated_by'    => $user->username,
                'updated_at'    => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Actual budget saved',
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

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
        $docUrl   = url('/vouchertaxi#' . $eid);
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
                        'date'      => $voucher->voucher_date,
                        'info'      => $voucher->purpose,
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
                        'info'      => $voucher->purpose,
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

        $request->validate([
            'comment' => ['required', 'string']
        ]);

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
        $docUrl   = url('/vouchertaxi#' . $eid);
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
                        'info'      => $voucher->purpose ?? '',
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

        $request->validate([
            'comment' => ['required', 'string']
        ]);

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
        $docUrl   = url('/vouchertaxi#' . $eid);
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
                        'info'      => $voucher->purpose ?? '',
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

    public function findByHash($eid)
    {
        $id = Hashids::decode($eid)[0] ?? null;

        abort_if(!$id, 404);

        $voucher = TrVoucherTaxi::findOrFail($id);

        return response()->json($voucher);
    }



    // public function tracking($hash)
    // {
    //     $id = Hashids::decode($hash)[0] ?? null;
    //     abort_if(!$id, 404);

    //     $voucher = TrVoucherTaxi::findOrFail($id);

    //     $getName = function (?string $username) {
    //         if (!$username) return null;

    //         $u = \App\Models\User::where('username', $username)->first();

    //         return $u->name ?? $username;
    //     };

    //     $createdByName = $getName($voucher->created_by ?? null);

    //     $createdAt = $voucher->created_at
    //         ? \Carbon\Carbon::parse($voucher->created_at)->format('Y-m-d H:i')
    //         : null;

    //     $completedByName = $getName($voucher->completed_by ?? null);

    //     $completedAt = $voucher->completed_at
    //         ? \Carbon\Carbon::parse($voucher->completed_at)->format('Y-m-d H:i')
    //         : null;

    //     $status = (string) ($voucher->status ?? '');

    //     $labelMap = [
    //         'P' => 'Waiting approval',
    //         'R' => 'Rejected',
    //         'D' => 'Revise',
    //         'C' => 'Completed',
    //     ];

    //     $statusLabel = $labelMap[$status] ?? $status;

    //     $steps = [[
    //         'key'          => 'submitted',
    //         'title'        => 'Voucher Taxi',
    //         'status'       => 'C',
    //         'status_label' => 'Submitted',
    //         'by'           => $createdByName,
    //         'at'           => $createdAt,
    //     ]];

    //     switch ($status) {

    //         case 'P':

    //             $steps[] = [
    //                 'key'          => 'approval',
    //                 'title'        => 'Approval',
    //                 'status'       => 'P',
    //                 'status_label' => 'Waiting approval',
    //                 'by'           => $completedByName,
    //                 'at'           => $completedAt,
    //             ];

    //             break;

    //         case 'R':

    //             $steps[] = [
    //                 'key'          => 'rejected',
    //                 'title'        => 'Rejected',
    //                 'status'       => 'R',
    //                 'status_label' => 'Rejected',
    //                 'by'           => $completedByName,
    //                 'at'           => $completedAt,
    //             ];

    //             break;

    //         case 'D':

    //             $steps[] = [
    //                 'key'          => 'revise',
    //                 'title'        => 'Revise',
    //                 'status'       => 'D',
    //                 'status_label' => 'Revise',
    //                 'by'           => $completedByName,
    //                 'at'           => $completedAt,
    //             ];

    //             break;

    //         case 'C':

    //             $steps[] = [
    //                 'key'          => 'completed',
    //                 'title'        => 'Completed',
    //                 'status'       => 'C',
    //                 'status_label' => 'Completed',
    //                 'by'           => $completedByName,
    //                 'at'           => $completedAt,
    //             ];

    //             break;
    //     }

    //     return response()->json([
    //         'doc'          => $voucher->docid,
    //         'steps'        => $steps,
    //         'status'       => $status,
    //         'status_label' => $statusLabel,
    //     ]);
    // }
    public function tracking($hash)
    {
        try {

            $id = Hashids::decode($hash)[0] ?? null;

            abort_if(!$id, 404);

            $voucher = TrVoucherTaxi::findOrFail($id);

            $getName = function (?string $username) {

                if (!$username) {
                    return null;
                }

                return \App\Models\User::where('username', $username)
                    ->value('name') ?? $username;
            };

            $steps = [];

            // =========================
            // SUBMITTED
            // =========================

            $steps[] = [
                'key'          => 'submitted',
                'title'        => 'Voucher Taxi',
                'status'       => 'C',
                'status_label' => 'Submitted',
                'by'           => $getName($voucher->created_by),
                'at'           => optional($voucher->created_at)
                    ->format('Y-m-d H:i'),
            ];

            // =========================
            // APPROVALS
            // =========================

            $approvals = TrApproval::query()
                ->where('refnbr', $voucher->docid)
                ->where('status', '<>', 'X')
                ->orderByRaw('CAST(aprv_leveling AS INTEGER)')
                ->get();

                              $comment = null;

                    try {

                        $comment = DB::table('tr_comment')
                            ->where('refid', $voucher->id)
                            ->where('doctype', 'VCR')
                            ->latest('created_at')
                            ->value('comment');

                    } catch (\Throwable $e) {

                        // ignore if table not exists
                    }

            $comments = TrMessage::where('doctype', 'VCR')
                ->where('refnbr', $voucher->docid)
                ->orderByDesc('message_date')
                ->get([
                    'username',
                    'name',
                    'message',
                    'message_date'
                ]);

            foreach ($approvals as $aprv) {

                $steps[] = [

                    'key' => 'approval_' . $aprv->aprv_leveling,

                    'title' => $aprv->aprv_name
                        ?: ('Approval Level ' . $aprv->aprv_leveling),

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
                    'at' => $aprv->aprv_dateafter
                        ? \Carbon\Carbon::parse($aprv->aprv_dateafter)
                            ->format('Y-m-d H:i')
                        : null,


                    'comment' => $comments->first()?->message,
                ];
            }


           $latestComment = TrMessage::where('doctype', 'VCR')
            ->where('refnbr', $voucher->docid)
            ->latest('message_date')
            ->first();

            return response()->json([
                'success' => true,

                'doc' => $voucher->docid,

                'steps' => $steps,

                'status' => $voucher->status,

                'status_label' => match ($voucher->status) {
                    'P' => 'Pending',
                    'C' => 'Completed',
                    'R' => 'Rejected',
                    'D' => 'Revise',
                    default => '-',
                },

                'revise_reason' => $latestComment?->message,
                'comments'      => $comments,
            ]);

        } catch (\Throwable $e) {

            \Log::error('TRACKING ERROR', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // public function printVoucherTaxiuest($hash)
    // {
    //     $id = Hashids::decode($hash)[0] ?? null;
    //     abort_if(!$id, 404);

    //     $authUser = Auth::user();
    //     if (!$authUser) {
    //         return redirect()->route('login');
    //     }

    //     // Ambil Item Request + relasi yang dibutuhkan
    //     $itemReq = TrVoucherTaxi::with([
    //             'requestType:requesttypeid,requesttype_name',
    //             'creator:username,name',
    //         ])
    //         ->findOrFail($id);

    //     // Detail baris Item Request
    //     $itemReqdetail = TrVoucherTaxidetail::with([
    //             'location:location_id,location_name',
    //             'subLocation:sub_location_id,sub_location_name',
    //         ])
    //         ->where('irid', $itemReq->irid)
    //         ->get();

    //     // Approval list (non-cancelled)
    //     // $approval = T_approval::where('docid', $itemReq->irid)
    //     //     ->where('status', '<>', 'X')
    //     //     ->orderBy('aprvid')
    //     //     ->orderBy('created_at')
    //     //     ->get();
    //     $approvals = TrApproval::query()
    //         ->where('refnbr', $itemReq->docid)
    //         ->where('status', '<>', 'X')
    //         ->orderByRaw('CAST(aprv_leveling AS INTEGER)')
    //         ->get();

    //     $approve_count = $approvals->count();

    //     // Company (handle null)
    //     $company = MsCompany::where('cpny_id', $itemReq->cpny_id)->first();

    //     // Mapping status dokumen
    //     switch ($itemReq->status) {
    //         case 'R':
    //             $status_doc = 'Rejected';
    //             break;
    //         case 'C':
    //             $status_doc = 'Completed';
    //             break;
    //         case 'D':
    //             $status_doc = 'Hold';
    //             break;
    //         case 'X':
    //             $status_doc = 'Cancel';
    //             break;
    //         default:
    //             $status_doc = 'On Progress';
    //             break;
    //     }

    //     $data = [
    //         'title'               => 'Surat Permintaan Pembelian Barang',
    //         'doc_type'            => 'Item Request',
    //         'docid'               => $itemReq->irid,
    //         'department_id'       => $itemReq->department_id,
    //         'cpnyname'            => optional($company)->cpny_name,
    //         'parent'              => optional($company)->parent,
    //         'project'             => optional($company)->project,
    //         // identitas & tanggal
    //         'created_by_username' => $itemReq->created_by,
    //         'created_by_name'     => ucwords(strtolower(optional($itemReq->creator)->name)),
    //         'created_at_fmt'      => optional($itemReq->created_at)->format('d F Y'),
    //         'req_date_fmt'        => optional($itemReq->created_at)->format('d M Y H:i'),
    //         'itemReqdate'            => \Carbon\Carbon::parse($itemReq->itemReqdate)->format('d F Y'),
    //         // konten
    //         'keperluan'           => $itemReq->inventory_descr_req,
    //         'status_doc'          => $status_doc,
    //         'requesttype_name'    => optional($itemReq->requestType)->requesttype_name,
    //     ];

    //     // Kirim ke view
    //     $pdf = \PDF::loadView(
    //         'pages.itemrequests.pdf_itemrequests',
    //         array_merge($data, [
    //             'detail'         => $itemReqdetail,
    //             'approvals'      => $approvals,
    //             'approve_count'  => $approve_count,
    //         ])
    //     );

    //     // Portrait jika <= 5 approver, else landscape
    //     $pdf->setPaper('A4', ($approve_count <= 5) ? 'portrait' : 'landscape');

    //     return $pdf->stream("pdf_itemrequests_{$itemReq->irid}.pdf");
    // }

    public function printVoucherTaxi($hash)
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

        $approvals = TrApproval::query()
            ->where('refnbr', $voucher->docid)
            ->where('status', '<>', 'X')
            ->orderByRaw('CAST(aprv_leveling AS INTEGER)')
            ->get();

        $company = MsCompany::where('cpny_id', $voucher->cpny_id)
            ->first();

        $status_doc = match ($voucher->status) {
            'P' => 'On Progress',
            'C' => 'Completed',
            'R' => 'Rejected',
            'D' => 'Revise',
            'X' => 'Cancelled',
            default => '-',
        };

        $pdf = PDF::loadView(
            'pages.vouchertaxi.pdf_vouchertaxi',
            [
                'voucher'      => $voucher,
                'approvals'    => $approvals,
                'company'      => $company,
                'status_doc'   => $status_doc,
            ]
        );

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream(
            'voucher_taxi_' . $voucher->docid . '.pdf'
        );
    }

}
