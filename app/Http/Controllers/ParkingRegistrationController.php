<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
// use Illuminate\Support\Carbon;
use App\Models\Autonbr;
use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\Usercpny;
use App\Models\Userdept;
use App\Models\User;
use App\Models\Site;
use App\Models\TrParkingRegistration;
use App\Models\TrParkingRegistrationDetail;
use App\Models\TrAttachment;
use App\Models\TrApproval;
use App\Models\SysUserRole;
use App\Models\MsCategory;
use App\Models\MsSite;
use App\Models\MsParkingKendaraan;
use App\Models\MsKendaraan;

use Mail;
use PDF;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Controllers\TrAttachmentController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Google\Cloud\Storage\StorageClient;
use App\Http\Controllers\ApprovalController;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;


use App\Http\Controllers\Traits\HasAutonbr;


class ParkingRegistrationController extends Controller
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
            : array_filter((array) $user->cpny_id);

        $deptIds = is_string($user->department_id)
            ? array_filter(array_map('trim', explode(',', $user->department_id)))
            : array_filter((array) $user->department_id);

        $canParkingAccess = SysUserRole::where('username', $user->username)
            ->where('role_id', 'PARKINGACCESS')
            ->where('status', 'A')
            ->exists();

        $q = TrParkingRegistration::query()
            ->whereIn('cpny_id', $cpnyIds)
            ->whereIn('department_id', $deptIds);

        $all        = (clone $q)->count();
        $onProgress = (clone $q)->where('status', 'P')->count();
        $reject     = (clone $q)->where('status', 'R')->count();
        $revise     = (clone $q)->where('status', 'D')->count();
        $completed  = (clone $q)->where('status', 'C')->count();

        $allParkingCount = 0;
        $masterKendaraanCount = 0;

        if ($canParkingAccess) {
            $allParkingCount = TrParkingRegistration::query()
                ->whereIn('cpny_id', $cpnyIds)
                ->count();

            $masterKendaraanCount = MsParkingKendaraan::query()
                ->whereIn('site_id_parking', $cpnyIds)
                ->whereNull('deleted_at')
                ->count();
        }

        $masterSites = collect();
        $masterDepartments = collect();
        $parkingTypes = collect();
        $workerTypes = collect();

        if ($canParkingAccess) {
            $masterSites = MsSite::whereIn('siteid', $cpnyIds)
                ->where('site_parking', true)
                ->where('status', 'A')
                ->orderBy('siteid')
                ->get(['siteid', 'site_name']);

            $masterDepartments = MsParkingKendaraan::whereIn('site_id_parking', $cpnyIds)
                ->whereNull('deleted_at')
                ->whereNotNull('department_id')
                ->distinct()
                ->orderBy('department_id')
                ->pluck('department_id');

            $parkingTypes = MsCategory::where('doctype', 'PKR')
                ->where('type', 'TYPE')
                ->where('status', 'A')
                ->orderBy('category_name')
                ->get(['categoryid', 'category_name']);

            $workerTypes = MsCategory::where('doctype', 'PKR')
                ->where('type', 'WORKER')
                ->where('status', 'A')
                ->orderBy('category_name')
                ->get(['categoryid', 'category_name']);
        }

        return view('pages.parkingregistration.parkingregistration', compact(
            'all',
            'onProgress',
            'reject',
            'revise',
            'completed',
            'canParkingAccess',
            'allParkingCount',
            'masterKendaraanCount',
            'masterSites',
            'masterDepartments',
            'parkingTypes',
            'workerTypes',
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
            : array_filter((array) $user->cpny_id);

        $deptIds = is_string($user->department_id)
            ? array_filter(array_map('trim', explode(',', $user->department_id)))
            : array_filter((array) $user->department_id);

        $canParkingAccess = SysUserRole::where('username', $user->username)
            ->where('role_id', 'PARKINGACCESS')
            ->where('status', 'A')
            ->exists();

        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));
        $status = (string) $request->query('status', '');
        $scope  = (string) $request->query('scope', 'my');

        if (!in_array($scope, ['my', 'all', 'master'], true)) {
            $scope = 'my';
        }

        if (in_array($scope, ['all', 'master'], true) && !$canParkingAccess) {
            return response()->json([
                'draw'            => $draw,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
            ]);
        }

        if ($scope === 'master') {
            return $this->jsonMasterKendaraan($request, $draw, $start, $length, $search, $status, $cpnyIds);
        }

        return $this->jsonParkingRegistration($request, $draw, $start, $length, $search, $status, $scope, $cpnyIds, $deptIds);
    }

    private function jsonParkingRegistration(
        Request $request,
        int $draw,
        int $start,
        int $length,
        string $search,
        string $status,
        string $scope,
        array $cpnyIds,
        array $deptIds
    ) {
        $columns = [
            0  => 'pr.docid',
            1  => 'pr.docid',
            2  => 'pr.parking_regist_date',
            3  => 'pr.cpny_id',
            4  => 'pr.department_id',
            5  => 'pr.site_id_parking',
            6  => 'pr.parking_type',
            7  => 'pr.worker_type',
            8  => 'pr.perpost',
            9  => 'pr.info',
            10 => 'pr.status',
        ];

        $orderIdx = (int) $request->input('order.0.column', 1);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'pr.parking_regist_date';

        $baseTable = (new TrParkingRegistration)->getTable();

        $base = TrParkingRegistration::from($baseTable . ' as pr')
            ->whereIn('pr.cpny_id', $cpnyIds);

        if ($scope === 'my') {
            $base->whereIn('pr.department_id', $deptIds);
        }

        if ($status !== '') {
            $base->where('pr.status', $status);
        }

        $recordsTotal = (clone $base)->count();

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('pr.docid', 'ilike', "%{$search}%")
                    ->orWhere('pr.parking_regist_date', 'ilike', "%{$search}%")
                    ->orWhere('pr.cpny_id', 'ilike', "%{$search}%")
                    ->orWhere('pr.department_id', 'ilike', "%{$search}%")
                    ->orWhere('pr.site_id_parking', 'ilike', "%{$search}%")
                    ->orWhere('pr.parking_type', 'ilike', "%{$search}%")
                    ->orWhere('pr.worker_type', 'ilike', "%{$search}%")
                    ->orWhere('pr.perpost', 'ilike', "%{$search}%")
                    ->orWhere('pr.info', 'ilike', "%{$search}%")
                    ->orWhere('pr.status', 'ilike', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $base)->count();

        $data = $base->select([
                'pr.id',
                'pr.docid',
                'pr.parking_regist_date',
                'pr.cpny_id',
                'pr.department_id',
                'pr.site_id_parking',
                'pr.parking_type',
                'pr.worker_type',
                'pr.perpost',
                'pr.info',
                'pr.status',
                'pr.created_by',
            ])
            ->orderBy($orderCol, $orderDir)
            ->orderBy('pr.docid', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        $data = $this->mapParkingNames($data, 'parking');

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    private function jsonMasterKendaraan(
        Request $request,
        int $draw,
        int $start,
        int $length,
        string $search,
        string $status,
        array $cpnyIds
    ) {
        $siteParking     = trim((string) $request->query('site_parking', ''));
        $parkingType     = trim((string) $request->query('parking_type', ''));
        $workerType      = trim((string) $request->query('worker_type', ''));
        $jenisKendaraan  = trim((string) $request->query('jenis_kendaraan', ''));
        $departmentId    = trim((string) $request->query('department_id', ''));

        $columns = [
            0  => 'mk.id',
            1  => 'mk.id',
            2  => 'mk.site_id_parking',
            3  => 'mk.nama',
            4  => 'mk.nopol',
            5  => 'mk.jenis_kendaraan',
            6  => 'mk.parking_type',
            7  => 'mk.worker_type',
            8  => 'mk.department_id',
            9  => 'mk.perpost',
            10 => 'mk.startdate',
            11 => 'mk.enddate',
            12 => 'mk.no_kartu',
            13 => 'mk.attach_stnk',
            14 => 'mk.attach_idcard',
            15 => 'mk.attach_bukti_bayar',
            16 => 'mk.status',
        ];

        $orderIdx = (int) $request->input('order.0.column', 1);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'mk.nama';

        $baseTable = (new MsParkingKendaraan)->getTable();

        $base = MsParkingKendaraan::from($baseTable . ' as mk')
            ->whereIn('mk.site_id_parking', $cpnyIds)
            ->whereNull('mk.deleted_at');

        if ($status !== '') {
            $base->where('mk.status', $status);
        }

        if ($siteParking !== '') {
            $base->where('mk.site_id_parking', $siteParking);
        }

        if ($parkingType !== '') {
            $base->where('mk.parking_type', $parkingType);
        }

        if ($workerType !== '') {
            $base->where('mk.worker_type', $workerType);
        }

        if ($jenisKendaraan !== '') {
            $base->where('mk.jenis_kendaraan', $jenisKendaraan);
        }

        if ($departmentId !== '') {
            $base->where('mk.department_id', $departmentId);
        }

        $recordsTotal = (clone $base)->count();

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('mk.site_id_parking', 'ilike', "%{$search}%")
                    ->orWhere('mk.nama', 'ilike', "%{$search}%")
                    ->orWhere('mk.username', 'ilike', "%{$search}%")
                    ->orWhere('mk.nopol', 'ilike', "%{$search}%")
                    ->orWhere('mk.jenis_kendaraan', 'ilike', "%{$search}%")
                    ->orWhere('mk.parking_type', 'ilike', "%{$search}%")
                    ->orWhere('mk.worker_type', 'ilike', "%{$search}%")
                    ->orWhere('mk.department_id', 'ilike', "%{$search}%")
                    ->orWhere('mk.perpost', 'ilike', "%{$search}%")
                    ->orWhere('mk.no_kartu', 'ilike', "%{$search}%")
                    ->orWhere('mk.status', 'ilike', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $base)->count();

        $data = $base->select([
            'mk.id',
            'mk.site_id_parking',
            'mk.parking_type',
            'mk.worker_type',
            'mk.nopol',
            'mk.jenis_kendaraan',
            'mk.username',
            'mk.nama',
            'mk.cpny_id',
            'mk.department_id',
            'mk.perpost',
            'mk.startdate',
            'mk.enddate',
            'mk.no_kartu',
            'mk.attach_stnk',
            'mk.attach_idcard',
            'mk.attach_bukti_bayar',
            'mk.status',
            'mk.created_at',
        ])
            ->orderBy($orderCol, $orderDir)
            ->skip($start)
            ->take($length)
            ->get()
            ->map(function ($row) {
                $row->row_type = 'master';
                return $row;
            });

        $data = $this->mapParkingNames($data, 'master');

        /*
        |--------------------------------------------------------------------------
        | Generate Signed URL GCS
        |--------------------------------------------------------------------------
        */
        $bucket = $this->gcsBucket();

        $data->transform(function ($row) use ($bucket) {
            $row->attach_stnk_url = $this->makeGcsSignedUrl($bucket, $row->attach_stnk);
            $row->attach_idcard_url = $this->makeGcsSignedUrl($bucket, $row->attach_idcard);
            $row->attach_bukti_bayar_url = $this->makeGcsSignedUrl($bucket, $row->attach_bukti_bayar);

            return $row;
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    private function mapParkingNames($data, string $type)
    {
        $siteIds = $data->pluck('site_id_parking')
            ->filter()
            ->unique()
            ->values();

        $siteMap = MsSite::whereIn('siteid', $siteIds)
            ->pluck('site_name', 'siteid');

        $parkingTypeIds = $data->pluck('parking_type')
            ->filter()
            ->unique()
            ->values();

        $workerTypeIds = $data->pluck('worker_type')
            ->filter()
            ->unique()
            ->values();

        $parkingTypeMap = MsCategory::where('doctype', 'PKR')
            ->where('type', 'TYPE')
            ->whereIn('categoryid', $parkingTypeIds)
            ->pluck('category_name', 'categoryid');

        $workerTypeMap = MsCategory::where('doctype', 'PKR')
            ->where('type', 'WORKER')
            ->whereIn('categoryid', $workerTypeIds)
            ->pluck('category_name', 'categoryid');

        return $data->transform(function ($row) use ($siteMap, $parkingTypeMap, $workerTypeMap, $type) {
            if ($type === 'parking' && isset($row->id)) {
                $row->eid = Hashids::encode($row->id);
                unset($row->id);
            }

            /*
            |--------------------------------------------------------------------------
            | PENTING:
            |--------------------------------------------------------------------------
            | Untuk master kendaraan, id jangan di-unset.
            | Karena dipakai tombol:
            | /parking-kendaraan/{id}/toggle-status
            | /parking-kendaraan/{id}/no-kartu
            |--------------------------------------------------------------------------
            */

            $row->row_type = $type;

            $row->site_parking_name = $siteMap[$row->site_id_parking] ?? $row->site_id_parking;
            $row->parking_type_name = $parkingTypeMap[$row->parking_type] ?? $row->parking_type;
            $row->worker_type_name  = $workerTypeMap[$row->worker_type] ?? $row->worker_type;

            return $row;
        });
    }

    private function gcsBucket()
    {
        $config = config('filesystems.disks.gcs');

        $keyFilePath = $config['key_file'];

        if (!Str::startsWith($keyFilePath, ['/', 'C:\\', 'D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }

        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]);

        return $storage->bucket($config['bucket']);
    }

    private function makeGcsSignedUrl($bucket, ?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        try {
            $object = $bucket->object($path);

            return $object->signedUrl(
                new \DateTimeImmutable('+10 minutes'),
                ['version' => 'v4']
            );
        } catch (\Throwable $e) {
            \Log::warning('Signed URL master kendaraan gagal', [
                'path'  => $path,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function uploadParkingFileToGcs(?UploadedFile $file, string $docid, string $folder, string $username): ?string
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        $bucket = $this->gcsBucket();

        $year = now()->year;
        $ext = $file->getClientOriginalExtension();
        $filename = md5(random_int(1, 99999999) . microtime(true)) . '.' . $ext;

        $gcsPath = "parking_registration/{$year}/{$docid}/{$folder}/{$filename}";

        $bucket->upload(
            fopen($file->getPathname(), 'r'),
            [
                'name' => $gcsPath,
                'predefinedAcl' => 'private',
                'metadata' => [
                    'contentType' => $file->getMimeType(),
                    'metadata' => [
                        'original-name' => $file->getClientOriginalName(),
                        'uploaded-by' => $username,
                    ],
                ],
            ]
        );

        return $gcsPath;
    }
            
    public function createParkingRegistration()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $usercpny = Usercpny::where('username', $user->username)->get();
        $usercpny2 = Usercpny::where('username', $user->username)->first();

        $userdept = Userdept::where('username', $user->username)->get();
        $userdept2 = Userdept::where('username', $user->username)->first();

        $workerTypes = MsCategory::where('doctype', 'PKR')
            ->where('type', 'WORKER')
            ->where('status', 'A')
            ->orderBy('category_name', 'asc')
            ->get();

        $parkingTypes = MsCategory::where('doctype', 'PKR')
            ->where('type', 'TYPE')
            ->where('status', 'A')
            ->orderBy('groups', 'asc')
            ->get();

        $sites = MsSite::where('site_parking', true)
            ->where('status', 'A')
            ->orderBy('siteid', 'asc')
            ->get();

        $employees = User::where('status', 'A')
            ->orderBy('name', 'asc')
            ->get([
                'username',
                'name',
                'npk',
                'department_id',
                'cpny_id',
            ]);

        return view('pages.parkingregistration.createparkingregistration', compact(
            'usercpny',
            'usercpny2',
            'userdept',
            'userdept2',
            'workerTypes',
            'parkingTypes',
            'sites',
            'employees'
        ));
    }

    public function employeesByFilter(Request $request)
    {
        $cpnyId       = trim((string) $request->query('cpny_id', ''));
        $departmentId = trim((string) $request->query('department_id', ''));
        $siteParking  = trim((string) $request->query('site_id_parking', ''));
        $perpost      = trim((string) $request->query('perpost', ''));
        $parkingType  = strtoupper(trim((string) $request->query('parking_type', '')));
        $workerType   = strtoupper(trim((string) $request->query('worker_type', '')));
        $search       = trim((string) $request->query('q', ''));

        /*
        |--------------------------------------------------------------------------
        | Worker Type = OPRVEHICLES
        |--------------------------------------------------------------------------
        | Name dropdown diambil dari ms_kendaraan.
        | Yang ditampilkan: namakendaraan
        | Auto isi:
        | - nopol = no_polisi
        | - jenis_kendaraan = typekendaraan
        |--------------------------------------------------------------------------
        */
        if (
            $workerType === 'OPRVEHICLES'
            && in_array($parkingType, ['NEWREQUEST', 'TEMPREQUEST'], true)
        ) {
            $q = MsKendaraan::query()
                ->where('status', 'A');

            if ($cpnyId !== '') {
                $q->where('cpny_id', $cpnyId);
            }

            if ($search !== '') {
                $q->where(function ($qq) use ($search) {
                    $qq->where('namakendaraan', 'ilike', "%{$search}%")
                        ->orWhere('no_polisi', 'ilike', "%{$search}%")
                        ->orWhere('typekendaraan', 'ilike', "%{$search}%")
                        ->orWhere('merk_kendaraan', 'ilike', "%{$search}%")
                        ->orWhere('pemilikkendaraan', 'ilike', "%{$search}%");
                });
            }

            $data = $q->orderBy('namakendaraan', 'asc')
                ->limit(30)
                ->get([
                    'id',
                    'cpny_id',
                    'no_polisi',
                    'namakendaraan',
                    'typekendaraan',
                    'merk_kendaraan',
                    'pemilikkendaraan',
                ])
                ->map(function ($row) {
                    return [
                        'id'              => 'OPRVEHICLES|' . $row->id,
                        'text'            => $row->namakendaraan,
                        'name'            => $row->namakendaraan,
                        'username'        => null,
                        'nopol'           => $row->no_polisi,
                        'jenis_kendaraan' => $row->typekendaraan,
                        'cpny_id'         => $row->cpny_id,
                        'merk_kendaraan'  => $row->merk_kendaraan,
                        'pemilik'         => $row->pemilikkendaraan,
                    ];
                });

            return response()->json([
                'results' => $data,
            ]);
        }     
       

        /*
        |--------------------------------------------------------------------------
        | RENEWAL / CHANGECARD / CHANGENOPOL
        |--------------------------------------------------------------------------
        | Untuk worker_type EMPLOYEE ataupun non EMPLOYEE:
        | baca dari ms_parking_kendaraan status A.
        |--------------------------------------------------------------------------
        */
        if (in_array($parkingType, ['RENEWAL', 'CHANGECARD', 'CHANGENOPOL'], true)) {
            $q = MsParkingKendaraan::query()
                ->where('status', 'A')
                ->whereNull('deleted_at');

            if ($cpnyId !== '') {
                $q->where('cpny_id', $cpnyId);
            }

            if ($departmentId !== '') {
                $q->where('department_id', $departmentId);
            }

            if ($siteParking !== '') {
                $q->where('site_id_parking', $siteParking);
            }

            // if ($perpost !== '') {
            //     $q->where('perpost', $perpost);
            // }

            // Tambahan filter worker type
            if ($workerType !== '') {
                $q->where('worker_type', $workerType);
            }

            if ($search !== '') {
                $q->where(function ($qq) use ($search) {
                    $qq->where('nama', 'ilike', "%{$search}%")
                        ->orWhere('username', 'ilike', "%{$search}%")
                        ->orWhere('nopol', 'ilike', "%{$search}%")
                        ->orWhere('jenis_kendaraan', 'ilike', "%{$search}%")
                        ->orWhere('cpny_id', 'ilike', "%{$search}%")
                        ->orWhere('department_id', 'ilike', "%{$search}%")
                        ->orWhere('site_id_parking', 'ilike', "%{$search}%")
                        ->orWhere('parking_type', 'ilike', "%{$search}%")
                        ->orWhere('worker_type', 'ilike', "%{$search}%")
                        ->orWhere('perpost', 'ilike', "%{$search}%");
                });
            }

            $data = $q->orderBy('nama', 'asc')
                ->limit(30)
                ->get([
                    'id',
                    'username',
                    'nama',
                    'nopol',
                    'jenis_kendaraan',
                    'cpny_id',
                    'department_id',
                    'site_id_parking',
                    'parking_type',
                    'worker_type',
                    'perpost',
                ])
                ->map(function ($row) {
                    $selectId = ($row->username ?: 'PARKING') . '|' . $row->id;

                    return [
                        'id'              => $selectId,
                        'text'            => trim(($row->nama ?: '-')),
                        'name'            => $row->nama,
                        'username'        => $row->username,
                        'nopol'           => $row->nopol,
                        'jenis_kendaraan' => $row->jenis_kendaraan,
                        'cpny_id'         => $row->cpny_id,
                        'department_id'   => $row->department_id,
                        'site_id_parking' => $row->site_id_parking,
                        'parking_type'    => $row->parking_type,
                        'worker_type'     => $row->worker_type,
                        'perpost'         => $row->perpost,
                    ];
                });

            return response()->json([
                'results' => $data,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | TEMPREQUEST
        |--------------------------------------------------------------------------
        | EMPLOYEE     => baca User status A
        | non EMPLOYEE => view pakai input manual, endpoint ini tidak perlu data
        |--------------------------------------------------------------------------
        */
        if ($parkingType === 'TEMPREQUEST') {
            if ($workerType !== 'EMPLOYEE') {
                return response()->json([
                    'results' => [],
                ]);
            }

            $q = User::query()
                ->where('status', 'A');

            if ($cpnyId !== '') {
                $q->whereRaw(
                    "EXISTS (
                        SELECT 1
                        FROM unnest(string_to_array(COALESCE(cpny_id, ''), ',')) AS x(val)
                        WHERE trim(x.val) = ?
                    )",
                    [$cpnyId]
                );
            }

            if ($departmentId !== '') {
                $q->whereRaw(
                    "EXISTS (
                        SELECT 1
                        FROM unnest(string_to_array(COALESCE(department_id, ''), ',')) AS x(val)
                        WHERE trim(x.val) = ?
                    )",
                    [$departmentId]
                );
            }

            if ($search !== '') {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'ilike', "%{$search}%")
                        ->orWhere('username', 'ilike', "%{$search}%")
                        ->orWhere('npk', 'ilike', "%{$search}%")
                        ->orWhere('jabatan', 'ilike', "%{$search}%");
                });
            }

            $data = $q->orderBy('name', 'asc')
                ->limit(30)
                ->get([
                    'username',
                    'name',
                    'npk',
                    'cpny_id',
                    'department_id',
                    'jabatan',
                ])
                ->map(function ($row) {
                    return [
                        'id'            => $row->username,
                        'text'          => $row->name,
                        'name'          => $row->name,
                        'username'      => $row->username,
                        'npk'           => $row->npk,
                        'jabatan'       => $row->jabatan,
                        'cpny_id'       => $row->cpny_id,
                        'department_id' => $row->department_id,
                    ];
                });

            return response()->json([
                'results' => $data,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | NEWREQUEST
        |--------------------------------------------------------------------------
        | EMPLOYEE     => logic lama pakai limit jabatan
        | non EMPLOYEE => view pakai input manual, endpoint ini tidak perlu data
        |--------------------------------------------------------------------------
        */
        if ($workerType !== 'EMPLOYEE') {
            return response()->json([
                'results' => [],
            ]);
        }

        $limitByJabatan = MsCategory::query()
            ->where('doctype', 'PKR')
            ->where('type', 'LIMIT')
            ->where('status', 'A')
            ->get(['groups', 'categoryid'])
            ->mapWithKeys(function ($row) {
                return [
                    strtoupper(trim((string) $row->groups)) => (int) $row->categoryid
                ];
            })
            ->toArray();

        if (empty($limitByJabatan)) {
            return response()->json([
                'results' => [],
            ]);
        }

        $parkingCountQuery = MsParkingKendaraan::query()
            ->select('username', DB::raw('COUNT(*) as jumlah'))
            ->whereNull('deleted_at')
            ->whereIn('status', ['A', 'P']);

        if ($siteParking !== '') {
            $parkingCountQuery->where('site_id_parking', $siteParking);
        }

        if ($perpost !== '') {
            $parkingCountQuery->where('perpost', $perpost);
        }

        $parkingCountByUsername = $parkingCountQuery
            ->groupBy('username')
            ->pluck('jumlah', 'username')
            ->toArray();

        $q = User::query()
            ->where('status', 'A')
            ->whereIn(DB::raw('UPPER(TRIM(jabatan))'), array_keys($limitByJabatan));

        if ($cpnyId !== '') {
            $q->whereRaw(
                "EXISTS (
                    SELECT 1
                    FROM unnest(string_to_array(COALESCE(cpny_id, ''), ',')) AS x(val)
                    WHERE trim(x.val) = ?
                )",
                [$cpnyId]
            );
        }

        if ($departmentId !== '') {
            $q->whereRaw(
                "EXISTS (
                    SELECT 1
                    FROM unnest(string_to_array(COALESCE(department_id, ''), ',')) AS x(val)
                    WHERE trim(x.val) = ?
                )",
                [$departmentId]
            );
        }

        if ($search !== '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'ilike', "%{$search}%")
                    ->orWhere('username', 'ilike', "%{$search}%")
                    ->orWhere('npk', 'ilike', "%{$search}%")
                    ->orWhere('jabatan', 'ilike', "%{$search}%");
            });
        }

        $users = $q->orderBy('name', 'asc')
            ->limit(300)
            ->get([
                'username',
                'name',
                'npk',
                'cpny_id',
                'department_id',
                'jabatan',
            ]);

        $data = $users
            ->filter(function ($row) use ($limitByJabatan, $parkingCountByUsername) {
                $jabatanKey = strtoupper(trim((string) $row->jabatan));

                $qty = $limitByJabatan[$jabatanKey] ?? 0;
                $jumlah = (int) ($parkingCountByUsername[$row->username] ?? 0);

                return $qty > 0 && $jumlah < $qty;
            })
            ->take(30)
            ->values()
            ->map(function ($row) use ($limitByJabatan, $parkingCountByUsername) {
                $jabatanKey = strtoupper(trim((string) $row->jabatan));

                $qty = $limitByJabatan[$jabatanKey] ?? 0;
                $jumlah = (int) ($parkingCountByUsername[$row->username] ?? 0);
                $sisa = max($qty - $jumlah, 0);

                return [
                    'id'            => $row->username,
                    'text'          => $row->name,
                    'name'          => $row->name,
                    'username'      => $row->username,
                    'npk'           => $row->npk,
                    'jabatan'       => $row->jabatan,
                    'cpny_id'       => $row->cpny_id,
                    'department_id' => $row->department_id,
                    'qty'           => $qty,
                    'jumlah'        => $jumlah,
                    'sisa'          => $sisa,
                ];
            });

        return response()->json([
            'results' => $data,
        ]);
    }   

    public function storeParkingRegistration(Request $request)
    {        
        $parkingType = strtoupper(trim((string) $request->parking_type));

        $rules = [
            'cpny_id'          => ['required', 'string'],
            'department_id'    => ['required', 'string'],
            'perpost'          => ['required', 'string'],
            'site_id_parking'  => ['required', 'string'],
            'parking_type'     => ['required', 'string'],
            'worker_type'      => ['required', 'string'],

            'detail_name'              => ['required', 'array', 'min:1'],
            'detail_name.*'            => ['required', 'string'],
            'detail_username'          => ['nullable', 'array'],
            'detail_username.*'        => ['nullable', 'string'],
            'detail_no_polisi'         => ['required', 'array', 'min:1'],
            'detail_no_polisi.*'       => ['required', 'string'],
            'detail_jenis_kendaraan'   => ['required', 'array', 'min:1'],
            'detail_jenis_kendaraan.*' => ['required', 'string'],

            'detail_nopol_lama'        => ['nullable', 'array'],
            'detail_nopol_lama.*'      => ['nullable', 'string'],
            'detail_jenis_lama'        => ['nullable', 'array'],
            'detail_jenis_lama.*'      => ['nullable', 'string'],

            'detail_attach_stnk.*'        => ['nullable', 'file', 'max:10240'],
            'detail_attach_idcard.*'      => ['nullable', 'file', 'max:10240'],
            'detail_attach_bukti_bayar.*' => ['nullable', 'file', 'max:10240'],

            'attachments.*' => ['nullable', 'file', 'max:10240'],
        ];

        if (in_array($parkingType, ['NEWREQUEST', 'TEMPREQUEST'], true)) {
            $rules['detail_attach_stnk'] = ['required', 'array', 'min:1'];
            $rules['detail_attach_stnk.*'] = ['required', 'file', 'max:10240'];

            $rules['detail_attach_idcard'] = ['required', 'array', 'min:1'];
            $rules['detail_attach_idcard.*'] = ['required', 'file', 'max:10240'];
        }

        if (in_array($parkingType, ['CHANGENOPOL', 'CHANGECARD'], true)) {
            $rules['detail_attach_stnk'] = ['required', 'array', 'min:1'];
            $rules['detail_attach_stnk.*'] = ['required', 'file', 'max:10240'];
        }

        $request->validate($rules);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $username      = $user->username ?? 'system';
        $dt            = now();
        $year          = (int) $dt->year;
        $month         = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $doctype       = 'PKR';
        $docName       = 'Parking Registration';

        $cpnyId        = $request->cpny_id;
        $departmentId  = $request->department_id;
        $siteParking   = $request->site_id_parking;
        $parkingType   = $request->parking_type;
        $workerType    = $request->worker_type;
        $perpost       = $request->perpost;

        // $startDate = Carbon::createFromDate((int) $perpost, 1, 1)->toDateString();
        // $endDate   = Carbon::createFromDate((int) $perpost, 12, 31)->toDateString();
        $isEmployee = strtoupper(trim((string) $workerType)) === 'EMPLOYEE';

        if ($isEmployee) {
            /*
            |--------------------------------------------------------------------------
            | EMPLOYEE
            |--------------------------------------------------------------------------
            | Date range tetap otomatis dari perpost:
            | startdate = 1 Jan perpost
            | enddate   = 31 Dec perpost
            |--------------------------------------------------------------------------
            */
            $startDate = Carbon::createFromDate((int) $perpost, 1, 1)->toDateString();
            $endDate   = Carbon::createFromDate((int) $perpost, 12, 31)->toDateString();

            /*
            |--------------------------------------------------------------------------
            | Info EMPLOYEE otomatis:
            | ms_category.category_name + perpost
            |--------------------------------------------------------------------------
            */
            $parkingTypeName = MsCategory::where('doctype', 'PKR')
                ->where('type', 'TYPE')
                ->where('status', 'A')
                ->where('categoryid', $parkingType)
                ->value('category_name');

            $headerInfo = trim(($parkingTypeName ?: $parkingType) . ' - ' . $perpost);
        } else {
            /*
            |--------------------------------------------------------------------------
            | NON EMPLOYEE
            |--------------------------------------------------------------------------
            | Date range wajib dari input user.
            |--------------------------------------------------------------------------
            */
            if (!$request->filled('startdate') || !$request->filled('enddate')) {
                return response()->json([
                    'message' => 'Mohon periksa input.',
                    'errors' => [
                        'startdate' => ['Start Date wajib diisi untuk worker type selain EMPLOYEE.'],
                        'enddate'   => ['End Date wajib diisi untuk worker type selain EMPLOYEE.'],
                    ],
                ], 422);
            }

            $startDate = Carbon::parse($request->startdate)->toDateString();
            $endDate   = Carbon::parse($request->enddate)->toDateString();

            $headerInfo = $request->info;
        }

        $approvalCtl = app(\App\Http\Controllers\ApprovalController::class);

        try {
            /*
            |--------------------------------------------------------------------------
            | Validasi setup approval
            |--------------------------------------------------------------------------
            */
            $approvalCtl->loadLines($doctype, $cpnyId, $departmentId);

            DB::connection('pgsql5')->beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | Generate Doc ID
            |--------------------------------------------------------------------------
            */
            $auto = $this->nextAutonbr(
                $doctype,
                $year,
                $month,
                $username,
                $docName
            );

            $urutan = (int) $auto['next'];
            $tglbln = substr((string) $year, 2) . $month;
            $docid  = $doctype . $tglbln . sprintf('%04d', $urutan);

            /*
            |--------------------------------------------------------------------------
            | Insert Header
            |--------------------------------------------------------------------------
            */
            $header = TrParkingRegistration::create([
                'docid'               => $docid,
                'parking_regist_date' => $dt->toDateString(),
                'cpny_id'             => $cpnyId,
                'department_id'       => $departmentId,
                'location_id'         => $request->location_id ?? null,
                'user_peminta'        => $username,
                'site_id_parking'     => $siteParking,
                'parking_type'        => $parkingType,
                'worker_type'         => $workerType,
                'perpost'             => $perpost,
                'info'                => $headerInfo,
                'status'              => 'P',
                'created_by'          => $username,
                'created_at'          => $dt,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Insert Detail
            |--------------------------------------------------------------------------
            */
            $detailNames = $request->input('detail_name', []);

            foreach ($detailNames as $i => $detailName) {               

                $rowNo = $i + 1;

                $stnkPath = $this->uploadParkingDetailFileGcs(
                    $request->file("detail_attach_stnk.$i"),
                    $docid,
                    'STNK',
                    $rowNo,
                    $username
                );

                $idCardPath = $this->uploadParkingDetailFileGcs(
                    $request->file("detail_attach_idcard.$i"),
                    $docid,
                    'IDCARD',
                    $rowNo,
                    $username
                );

                $buktiBayarPath = $this->uploadParkingDetailFileGcs(
                    $request->file("detail_attach_bukti_bayar.$i"),
                    $docid,
                    'BUKTIBAYAR',
                    $rowNo,
                    $username
                );

                TrParkingRegistrationDetail::create([
                    'docid'              => $docid,
                    'parking_type'       => $parkingType,
                    'worker_type'        => $workerType,
                    'nopol'              => strtoupper((string) $request->input("detail_no_polisi.$i")),
                    'jenis_kendaraan'    => $request->input("detail_jenis_kendaraan.$i"),
                    'username'           => $request->input("detail_username.$i"),
                    'nama'               => $detailName,
                    'cpny_id'            => $cpnyId,
                    'department_id'      => $departmentId,
                    'site_id_parking'    => $siteParking,
                    'perpost'            => $perpost,
                    'startdate'          => $startDate,
                    'enddate'            => $endDate,
                    'nopol_lama'         => $request->input("detail_nopol_lama.$i"),
                    'jenis_lama'         => $request->input("detail_jenis_lama.$i"),
                    'ref_nbr'            => null,
                    'attach_stnk'        => $stnkPath,
                    'attach_idcard'      => $idCardPath,
                    'attach_bukti_bayar' => $buktiBayarPath,
                    'status'             => 'P',
                    'created_by'         => $username,
                    'created_at'         => $dt,
                ]);

                $parkingTypeUpper = strtoupper(trim((string) $parkingType));

                // $detailUsername = $request->input("detail_username.$i");
                // $detailUsername = $detailUsername && str_contains($detailUsername, '|')
                //     ? explode('|', $detailUsername)[0]
                //     : $detailUsername;
                $detailUsername = $request->input("detail_username.$i");

                if ($detailUsername && str_starts_with($detailUsername, 'OPRVEHICLES|')) {
                    $detailUsername = null;
                } elseif ($detailUsername && str_contains($detailUsername, '|')) {
                    $detailUsername = explode('|', $detailUsername)[0];
                }

                $detailNopol = strtoupper(trim((string) $request->input("detail_no_polisi.$i")));
                $detailJenis = $request->input("detail_jenis_kendaraan.$i");

                $detailNopolLama = strtoupper(trim((string) $request->input("detail_nopol_lama.$i")));
                $detailJenisLama = $request->input("detail_jenis_lama.$i");

                /*
                |--------------------------------------------------------------------------
                | NEWREQUEST / TEMPREQUEST
                |--------------------------------------------------------------------------
                | Buat data baru di ms_parking_kendaraan dengan status P.
                |--------------------------------------------------------------------------
                */
                if (in_array($parkingTypeUpper, ['NEWREQUEST', 'TEMPREQUEST'], true)) {
                    MsParkingKendaraan::create([
                        'site_id_parking'    => $siteParking,
                        'parking_type'       => $parkingType,
                        'worker_type'        => $workerType,
                        'nopol'              => $detailNopol,
                        'jenis_kendaraan'    => $detailJenis,
                        'username'           => $detailUsername,
                        'nama'               => $detailName,
                        'cpny_id'            => $cpnyId,
                        'department_id'      => $departmentId,
                        'perpost'            => $perpost,
                        'startdate'          => $startDate,
                        'enddate'            => $endDate,
                        'no_kartu'           => null,
                        'attach_stnk'        => $stnkPath,
                        'attach_idcard'      => $idCardPath,
                        'attach_bukti_bayar' => $buktiBayarPath,
                        'status'             => 'P',
                        'created_by'         => $username,
                        'created_at'         => $dt,
                    ]);
                } else {
                    /*
                    |--------------------------------------------------------------------------
                    | RENEWAL / CHANGECARD / CHANGENOPOL
                    |--------------------------------------------------------------------------
                    | Data sudah ada di ms_parking_kendaraan status A.
                    | Saat submit, ubah status menjadi P.
                    |--------------------------------------------------------------------------
                    */

                    /*
                    |--------------------------------------------------------------------------
                    | Untuk CHANGENOPOL, matching pakai nopol lama.
                    | Untuk RENEWAL / CHANGECARD, matching pakai nopol current.
                    |--------------------------------------------------------------------------
                    */
                    $matchNopol = $parkingTypeUpper === 'CHANGENOPOL'
                        ? $detailNopolLama
                        : $detailNopol;

                    $qKendaraan = MsParkingKendaraan::query()
                        ->where('status', 'A')
                        ->where('site_id_parking', $siteParking)
                        // ->where('parking_type', $parkingType)
                        ->where('worker_type', $workerType)
                        // ->where('perpost', $perpost)
                        ->whereRaw('UPPER(TRIM(nopol)) = ?', [$matchNopol]);
            
                    if (!empty($detailUsername)) {
                        $qKendaraan->where('username', $detailUsername);
                    } else {
                        $qKendaraan->where('nama', $detailName);
                    }

                    $updatedRows = $qKendaraan->update([
                        'status'             => 'P',
                        // 'attach_stnk'        => $stnkPath ?: DB::raw('attach_stnk'),
                        // 'attach_idcard'      => $idCardPath ?: DB::raw('attach_idcard'),
                        // 'attach_bukti_bayar' => $buktiBayarPath ?: DB::raw('attach_bukti_bayar'),
                        'updated_by'         => $username,
                        'updated_at'         => $dt,
                    ]);

                    // \Log::info('Update MsParkingKendaraan to P from PKR store', [
                    //     'docid'           => $docid,
                    //     'parking_type'    => $parkingType,
                    //     'worker_type'     => $workerType,
                    //     'site_id_parking' => $siteParking,
                    //     'perpost'         => $perpost,
                    //     'username'        => $detailUsername,
                    //     'nama'            => $detailName,
                    //     'match_nopol'     => $matchNopol,
                    //     'updated_rows'    => $updatedRows,
                    // ]);

                    if ($updatedRows < 1) {
                        throw new \Exception(
                            "Data kendaraan aktif tidak ditemukan untuk {$detailName} - {$matchNopol}. Pastikan data sudah ada di master kendaraan."
                        );
                    }
                }

            }

            

            /*
            |--------------------------------------------------------------------------
            | Generate Approval
            |--------------------------------------------------------------------------
            */
            $ctx = [
                'site_id_parking' => $siteParking,
            ];

            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $docid,
                $doctype,
                $cpnyId,
                $departmentId,
                $username,
                $ctx,
                $dt
            );

            if ($firstApprovalUsernames) {
                $header->completed_by = $firstApprovalUsernames;
                $header->completed_at = $dt;
                $header->save();
            }

            /*
            |--------------------------------------------------------------------------
            | Header Attachment Optional
            |--------------------------------------------------------------------------
            */
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $docid,
                    'doctype'       => $doctype,
                    'cpnyid'        => $cpnyId,
                    'departementid' => $departmentId,
                    'base_folder'   => 'att-parking-registration',
                    'created_by'    => $username,
                ];

                $uploader = app(\App\Http\Controllers\TrAttachmentController::class);
                $uploader->uploadInternal($meta, (array) $request->file('attachments'));
            }

            $eid = Hashids::encode($header->id);

            /*
            |--------------------------------------------------------------------------
            | Notify First Approver
            |--------------------------------------------------------------------------
            */
            $approvalCtl->notifyFirstApprover(
                $docid,
                $doctype,
                $header->status,
                $docName,
                url('/showparkingregistration/' . $eid),
                [
                    'info'            => $headerInfo,
                    'createdby'       => $header->created_by,
                    'date'            => $dt->toDateTimeString(),
                    'cpny_id'         => $cpnyId,
                    'department_id'   => $departmentId,
                    'site_id_parking' => $siteParking,
                    'parking_type'    => $parkingType,
                    'worker_type'     => $workerType,
                    'perpost'         => $perpost,
                ]
            );

            DB::connection('pgsql5')->commit();

            return response()->json([
                'message' => 'Parking Registration created successfully',
                'docid'   => $docid,
                'eid'     => $eid,
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            report($e);

            return response()->json([
                'message' => 'Failed to create Parking Registration',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
       
  
   
    public function editParkingRegistration($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $parkingRegistration = TrParkingRegistration::where('id', $id)->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Optional security
        |--------------------------------------------------------------------------
        | Biasanya edit hanya boleh saat status D / Revise.
        |--------------------------------------------------------------------------
        */
        if (!in_array($parkingRegistration->status, ['D'], true)) {
            abort(403, 'Document cannot be edited.');
        }

        $parkingRegistrationDetail = TrParkingRegistrationDetail::where('docid', $parkingRegistration->docid)
            ->whereNull('deleted_at')
            ->orderBy('id', 'asc')
            ->get();

        $usercpny = Usercpny::where('username', $user->username)->get();
        $usercpny2 = Usercpny::where('username', $user->username)
            ->where('cpny_id', $parkingRegistration->cpny_id)
            ->first();

        $userdept = Userdept::where('username', $user->username)->get();
        $userdept2 = Userdept::where('username', $user->username)
            ->where('department_id', $parkingRegistration->department_id)
            ->first();

        $sites = MsSite::where('site_parking', true)
            ->where('status', 'A')
            ->orderBy('siteid')
            ->get();

        $parkingTypes = MsCategory::where('doctype', 'PKR')
            ->where('type', 'TYPE')
            ->where('status', 'A')
            ->orderBy('category_name')
            ->get();

        $workerTypes = MsCategory::where('doctype', 'PKR')
            ->where('type', 'WORKER')
            ->where('status', 'A')
            ->orderBy('category_name')
            ->get();

        $employees = User::where('status', 'A')
            ->orderBy('name')
            ->get(['username', 'name']);

        /*
        |--------------------------------------------------------------------------
        | Signed URL untuk attachment detail lama
        |--------------------------------------------------------------------------
        */
        $bucket = null;

        try {
            $bucket = $this->gcsBucket();
        } catch (\Throwable $e) {
            \Log::warning('GCS bucket init failed on edit PKR', [
                'error' => $e->getMessage(),
            ]);
        }

        $parkingRegistrationDetail->transform(function ($row) use ($bucket) {
            $row->attach_stnk_url = $bucket ? $this->makeGcsSignedUrl($bucket, $row->attach_stnk) : null;
            $row->attach_idcard_url = $bucket ? $this->makeGcsSignedUrl($bucket, $row->attach_idcard) : null;
            $row->attach_bukti_bayar_url = $bucket ? $this->makeGcsSignedUrl($bucket, $row->attach_bukti_bayar) : null;

            return $row;
        });

        return view('pages.parkingregistration.editparkingregistration', compact(
            'parkingRegistration',
            'parkingRegistrationDetail',
            'usercpny',
            'usercpny2',
            'userdept',
            'userdept2',
            'sites',
            'parkingTypes',
            'workerTypes',
            'employees',
            'hash'
        ));
    }



    public function updateParkingRegistration(Request $request, $docid)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $parking = TrParkingRegistration::where('docid', $docid)->first();

        if (!$parking) {
            return response()->json([
                'success' => false,
                'message' => 'Parking Registration not found',
            ], 404);
        }

        if (!in_array($parking->status, ['D'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Only revised document can be edited.',
            ], 403);
        }

        $parkingType = strtoupper(trim((string) $request->parking_type));
        $workerType = strtoupper(trim((string) $request->worker_type));

        $rules = [
            'cpny_id'          => ['required', 'string'],
            'department_id'    => ['required', 'string'],
            'perpost'          => ['required', 'string'],
            'site_id_parking'  => ['required', 'string'],
            'parking_type'     => ['required', 'string'],
            'worker_type'      => ['required', 'string'],

            'startdate'        => ['nullable', 'date'],
            'enddate'          => ['nullable', 'date', 'after_or_equal:startdate'],
            'info'             => ['nullable', 'string'],

            'detail_name'              => ['required', 'array', 'min:1'],
            'detail_name.*'            => ['required', 'string'],
            'detail_username'          => ['nullable', 'array'],
            'detail_username.*'        => ['nullable', 'string'],
            'detail_no_polisi'         => ['required', 'array', 'min:1'],
            'detail_no_polisi.*'       => ['required', 'string'],
            'detail_jenis_kendaraan'   => ['required', 'array', 'min:1'],
            'detail_jenis_kendaraan.*' => ['required', 'string'],

            'detail_nopol_lama'        => ['nullable', 'array'],
            'detail_nopol_lama.*'      => ['nullable', 'string'],
            'detail_jenis_lama'        => ['nullable', 'array'],
            'detail_jenis_lama.*'      => ['nullable', 'string'],

            'old_attach_stnk'          => ['nullable', 'array'],
            'old_attach_stnk.*'        => ['nullable', 'string'],
            'old_attach_idcard'        => ['nullable', 'array'],
            'old_attach_idcard.*'      => ['nullable', 'string'],
            'old_attach_bukti_bayar'   => ['nullable', 'array'],
            'old_attach_bukti_bayar.*' => ['nullable', 'string'],

            'detail_attach_stnk.*'        => ['nullable', 'file', 'max:10240'],
            'detail_attach_idcard.*'      => ['nullable', 'file', 'max:10240'],
            'detail_attach_bukti_bayar.*' => ['nullable', 'file', 'max:10240'],
        ];

        $request->validate($rules);

        $dt = Carbon::now();
        $username = $user->username;

        $cpnyId = $request->cpny_id;
        $departmentId = $request->department_id;
        $siteParking = $request->site_id_parking;
        $perpost = $request->perpost;

        $isEmployee = $workerType === 'EMPLOYEE';

        if ($isEmployee) {
            $startDate = Carbon::createFromDate((int) $perpost, 1, 1)->toDateString();
            $endDate = Carbon::createFromDate((int) $perpost, 12, 31)->toDateString();

            $parkingTypeName = MsCategory::where('doctype', 'PKR')
                ->where('type', 'TYPE')
                ->where('status', 'A')
                ->where('categoryid', $parkingType)
                ->value('category_name');

            $headerInfo = trim(($parkingTypeName ?: $parkingType) . ' - ' . $perpost);
        } else {
            if (!$request->filled('startdate') || !$request->filled('enddate')) {
                return response()->json([
                    'message' => 'Mohon periksa input.',
                    'errors' => [
                        'startdate' => ['Start Date wajib diisi untuk worker type selain EMPLOYEE.'],
                        'enddate'   => ['End Date wajib diisi untuk worker type selain EMPLOYEE.'],
                    ],
                ], 422);
            }

            $startDate = Carbon::parse($request->startdate)->toDateString();
            $endDate = Carbon::parse($request->enddate)->toDateString();
            $headerInfo = $request->info;
        }

        DB::connection('pgsql5')->beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | Rollback data kendaraan dari detail lama
            |--------------------------------------------------------------------------
            | Jika sebelumnya submit edit sudah pernah membuat status P, balikin dulu.
            |--------------------------------------------------------------------------
            */
            $oldDetails = TrParkingRegistrationDetail::where('docid', $parking->docid)->get();

            foreach ($oldDetails as $old) {
                $oldParkingType = strtoupper(trim((string) $old->parking_type));
                $matchNopol = $oldParkingType === 'CHANGENOPOL'
                    ? strtoupper(trim((string) $old->nopol_lama))
                    : strtoupper(trim((string) $old->nopol));

                $qOld = MsParkingKendaraan::query()
                    ->where('status', 'P')
                    ->where('site_id_parking', $old->site_id_parking)                  
                    ->where('worker_type', $old->worker_type)                   
                    ->whereRaw('UPPER(TRIM(nopol)) = ?', [$matchNopol]);

                if (!empty($old->username)) {
                    $qOld->where('username', $old->username);
                } else {
                    $qOld->where('nama', $old->nama);
                }

                if (in_array($oldParkingType, ['NEWREQUEST', 'TEMPREQUEST'], true)) {
                    $qOld->delete();
                } else {
                    $qOld->update([
                        'status' => 'A',
                        'updated_by' => $username,
                        'updated_at' => $dt,
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Update header
            |--------------------------------------------------------------------------
            */
            $parking->update([
                'parking_regist_date' => $parking->parking_regist_date ?: $dt->toDateString(),
                'cpny_id'             => $cpnyId,
                'department_id'       => $departmentId,
                'site_id_parking'     => $siteParking,
                'parking_type'        => $parkingType,
                'worker_type'         => $workerType,
                'perpost'             => $perpost,
                'info'                => $headerInfo,
                'status'              => 'P',
                'updated_by'          => $username,
                'updated_at'          => $dt,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Hapus detail lama
            |--------------------------------------------------------------------------
            */
            TrParkingRegistrationDetail::where('docid', $parking->docid)->delete();

            $names = $request->input('detail_name', []);

            foreach ($names as $i => $detailName) {
                $detailUsername = $request->input("detail_username.$i");

                if ($detailUsername && str_contains($detailUsername, '|')) {
                    $detailUsername = explode('|', $detailUsername)[0];
                }

                $detailNopol = strtoupper(trim((string) $request->input("detail_no_polisi.$i")));
                $detailJenis = $request->input("detail_jenis_kendaraan.$i");

                $detailNopolLama = strtoupper(trim((string) $request->input("detail_nopol_lama.$i")));
                $detailJenisLama = $request->input("detail_jenis_lama.$i");

                $oldStnk = $request->input("old_attach_stnk.$i");
                $oldIdcard = $request->input("old_attach_idcard.$i");
                $oldBuktiBayar = $request->input("old_attach_bukti_bayar.$i");

                $stnkPath = $this->uploadParkingFileToGcs(
                    $request->file("detail_attach_stnk.$i"),
                    $parking->docid,
                    'stnk',
                    $username
                ) ?: $oldStnk;

                $idCardPath = $this->uploadParkingFileToGcs(
                    $request->file("detail_attach_idcard.$i"),
                    $parking->docid,
                    'idcard',
                    $username
                ) ?: $oldIdcard;

                $buktiBayarPath = $this->uploadParkingFileToGcs(
                    $request->file("detail_attach_bukti_bayar.$i"),
                    $parking->docid,
                    'bukti_bayar',
                    $username
                ) ?: $oldBuktiBayar;

                TrParkingRegistrationDetail::create([
                    'docid'              => $parking->docid,
                    'parking_type'       => $parkingType,
                    'worker_type'        => $workerType,
                    'nopol'              => $detailNopol,
                    'jenis_kendaraan'    => $detailJenis,
                    'username'           => $detailUsername,
                    'nama'               => $detailName,
                    'cpny_id'            => $cpnyId,
                    'department_id'      => $departmentId,
                    'site_id_parking'    => $siteParking,
                    'perpost'            => $perpost,
                    'startdate'          => $startDate,
                    'enddate'            => $endDate,
                    'nopol_lama'         => $detailNopolLama ?: null,
                    'jenis_lama'         => $detailJenisLama ?: null,
                    'ref_nbr'            => null,
                    'attach_stnk'        => $stnkPath,
                    'attach_idcard'      => $idCardPath,
                    'attach_bukti_bayar' => $buktiBayarPath,
                    'status'             => 'P',
                    'created_by'         => $username,
                    'created_at'         => $dt,
                    'updated_by'         => $username,
                    'updated_at'         => $dt,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Update / create master kendaraan menjadi P
                |--------------------------------------------------------------------------
                */
                if (in_array($parkingType, ['NEWREQUEST', 'TEMPREQUEST'], true)) {
                    MsParkingKendaraan::create([
                        'site_id_parking'    => $siteParking,
                        'parking_type'       => $parkingType,
                        'worker_type'        => $workerType,
                        'nopol'              => $detailNopol,
                        'jenis_kendaraan'    => $detailJenis,
                        'username'           => $detailUsername,
                        'nama'               => $detailName,
                        'cpny_id'            => $cpnyId,
                        'department_id'      => $departmentId,
                        'perpost'            => $perpost,
                        'startdate'          => $startDate,
                        'enddate'            => $endDate,
                        'no_kartu'           => null,
                        'attach_stnk'        => $stnkPath,
                        'attach_idcard'      => $idCardPath,
                        'attach_bukti_bayar' => $buktiBayarPath,
                        'status'             => 'P',
                        'created_by'         => $username,
                        'created_at'         => $dt,
                    ]);
                } else {
                    $matchNopol = $parkingType === 'CHANGENOPOL'
                        ? $detailNopolLama
                        : $detailNopol;

                    $qKendaraan = MsParkingKendaraan::query()
                        ->where('status', 'A')
                        ->where('site_id_parking', $siteParking)                       
                        ->where('worker_type', $workerType)                        
                        ->whereRaw('UPPER(TRIM(nopol)) = ?', [$matchNopol]);

                    if (!empty($detailUsername)) {
                        $qKendaraan->where('username', $detailUsername);
                    } else {
                        $qKendaraan->where('nama', $detailName);
                    }

                    $updatedRows = $qKendaraan->update([
                        'status'     => 'P',
                        'updated_by' => $username,
                        'updated_at' => $dt,
                    ]);

                    if ($updatedRows < 1) {
                        throw new \Exception("Data kendaraan aktif tidak ditemukan untuk {$detailName} - {$matchNopol}.");
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Generate ulang approval
            |--------------------------------------------------------------------------
            | Sesuaikan dengan struktur ApprovalController kamu.
            |--------------------------------------------------------------------------
            */
            // DB::connection('pgsql')->table('tr_approval')
            //     ->where('refnbr', $parking->docid)
            //     ->where('doctype', 'PKR')
            //     ->delete();

            $approvalCtl = app(\App\Http\Controllers\ApprovalController::class);

            $ctx = [
                'site_id_parking' => $siteParking,
            ];

            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $parking->docid,
                'PKR',
                $cpnyId,
                $departmentId,
                $username,
                $ctx,
                $dt
            );

            if ((int) $linesCount < 1) {
                throw new \Exception('Approval line belum di-setup, Please contact IT!');
            }

            $eid = Hashids::encode($parking->id);

            $approvalCtl->notifyFirstApprover(
                $parking->docid,
                'PKR',
                'P',
                'Parking Registration',
                url('/showparkingregistration/' . $eid),
                [
                    'info'            => $parking->info,
                    'createdby'       => $parking->created_by,
                    'date'            => $dt->toDateTimeString(),
                    'cpny_id'         => $cpnyId,
                    'department_id'   => $departmentId,
                    'site_id_parking' => $siteParking,
                    'parking_type'    => $parkingType,
                    'worker_type'     => $workerType,
                    'perpost'         => $perpost,
                ]
            );

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Parking Registration updated and submitted successfully.',
                'docid'   => $parking->docid,
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            \Log::error('Update Parking Registration failed', [
                'docid' => $parking->docid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update Parking Registration',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
          

    public function showParkingRegistration($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $doctype = 'PKR';

        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */
        $parkingRegistration = TrParkingRegistration::with([
            'creator:username,name',
        ])->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | DETAIL
        |--------------------------------------------------------------------------
        */
        $parkingRegistrationDetail = TrParkingRegistrationDetail::where('docid', $parkingRegistration->docid)
            ->orderBy('id', 'asc')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | HEADER ATTACHMENTS FROM tr_attachment
        |--------------------------------------------------------------------------
        */
        $rows = TrAttachment::where('refnbr', $parkingRegistration->docid)
            ->where('doctype', $doctype)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        $config = config('filesystems.disks.gcs');

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
            $object = $bucket->object($objectPath);

            $signedUrl = null;

            try {
                $signedUrl = $object->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                \Log::warning('Signed URL gagal', [
                    'path'  => $objectPath,
                    'error' => $e->getMessage(),
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

        /*
        |--------------------------------------------------------------------------
        | DETAIL ATTACHMENTS SIGNED URL
        |--------------------------------------------------------------------------
        */
        $parkingRegistrationDetail = $parkingRegistrationDetail->map(function ($row) use ($bucket) {
            $row->attach_stnk_url = null;
            $row->attach_idcard_url = null;
            $row->attach_bukti_bayar_url = null;

            foreach ([
                'attach_stnk'        => 'attach_stnk_url',
                'attach_idcard'      => 'attach_idcard_url',
                'attach_bukti_bayar' => 'attach_bukti_bayar_url',
            ] as $field => $urlField) {
                if (!$row->{$field}) {
                    continue;
                }

                try {
                    $object = $bucket->object($row->{$field});

                    $row->{$urlField} = $object->signedUrl(
                        new \DateTimeImmutable('+10 minutes'),
                        ['version' => 'v4']
                    );
                } catch (\Throwable $e) {
                    \Log::warning('Signed URL detail PKR gagal', [
                        'docid' => $row->docid,
                        'field' => $field,
                        'path'  => $row->{$field},
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return $row;
        });

        $loginUsername = $user->username ?? $user->name ?? null;
        $canUpload = ($parkingRegistration->created_by === $loginUsername);

        $siteParkingName = MsSite::where('siteid', $parkingRegistration->site_id_parking)
            ->value('site_name');

        $parkingTypeName = MsCategory::where('doctype', 'PKR')
            ->where('type', 'TYPE')
            ->where('categoryid', $parkingRegistration->parking_type)
            ->value('category_name');

        $workerTypeName = MsCategory::where('doctype', 'PKR')
            ->where('type', 'WORKER')
            ->where('categoryid', $parkingRegistration->worker_type)
            ->value('category_name');

        return view('pages.parkingregistration.showparkingregistration', compact(
            'parkingRegistration',
            'parkingRegistrationDetail',
            'attachments',
            'hash',
            'canUpload',
            'siteParkingName',
            'parkingTypeName',
            'workerTypeName'
        ));
    }

      
    public function approveParkingRegistration(Request $request, $docid)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $doctype = 'PKR';
        $docName = 'Parking Registration';

        $parking = TrParkingRegistration::with('creator')
            ->where('docid', $docid)
            ->first();

        if (!$parking) {
            return response()->json([
                'success' => false,
                'message' => 'Parking Registration not found',
            ], 404);
        }

        $eid = Hashids::encode($parking->id);
        $docUrl = url('/showparkingregistration/' . $eid);

        $fullname = data_get($parking, 'creator.name') ?: $parking->created_by;

        $approvalCtl = app(\App\Http\Controllers\ApprovalController::class);

        $result = $approvalCtl->approveStep(
            $parking->docid,
            $doctype,
            $user->username,
            $user->name ?? $user->username,

            /*
            |--------------------------------------------------------------------------
            | ON COMPLETE APPROVAL
            |--------------------------------------------------------------------------
            | Jika approval sudah selesai semua:
            | - Header PKR status C
            | - Detail PKR status C
            | - MsParkingKendaraan status C
            |--------------------------------------------------------------------------
            */
            function (string $refnbr, Carbon $now) use ($parking, $fullname, $docUrl, $user, $approvalCtl, $docName) {
                $parking->status = 'C';
                $parking->completed_by = $user->username;
                $parking->completed_at = $now;
                $parking->updated_by = $user->username;
                $parking->updated_at = $now;
                $parking->save();

                /*
                |--------------------------------------------------------------------------
                | Ambil semua detail berdasarkan docid
                |--------------------------------------------------------------------------
                | Jangan filter status dulu, karena kalau status detail sudah berubah
                | oleh proses lain, loop jadi kosong.
                |--------------------------------------------------------------------------
                */
                $details = TrParkingRegistrationDetail::where('docid', $refnbr)
                    ->get();

                foreach ($details as $detail) {
                    /*
                    |--------------------------------------------------------------------------
                    | Update Detail PKR per baris
                    |--------------------------------------------------------------------------
                    */
                    TrParkingRegistrationDetail::where('id', $detail->id)
                        ->update([
                            'status'     => 'C',
                            'updated_by' => $user->username,
                            'updated_at' => $now,
                        ]);

                    $parkingType = strtoupper(trim((string) $detail->parking_type));

                    /*
                    |--------------------------------------------------------------------------
                    | Untuk CHANGENOPOL, data master masih pakai nopol lama.
                    | Jadi matching MsParkingKendaraan pakai nopol_lama.
                    |--------------------------------------------------------------------------
                    */
                    $matchNopol = $parkingType === 'CHANGENOPOL'
                        ? strtoupper(trim((string) $detail->nopol_lama))
                        : strtoupper(trim((string) $detail->nopol));

                    /*
                    |--------------------------------------------------------------------------
                    | Query master kendaraan pending
                    |--------------------------------------------------------------------------
                    */
                    $q = MsParkingKendaraan::query()
                        ->where('status', 'P')
                        ->where('site_id_parking', $detail->site_id_parking)
                        ->where('parking_type', $detail->parking_type)
                        ->where('worker_type', $detail->worker_type)
                        ->where('perpost', $detail->perpost)
                        ->whereRaw('UPPER(TRIM(nopol)) = ?', [$matchNopol]);

                    if (!empty($detail->username)) {
                        $q->where('username', $detail->username);
                    } else {
                        $q->where('nama', $detail->nama);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Default update: TEMPREQUEST / NEWREQUEST / RENEWAL
                    |--------------------------------------------------------------------------
                    */
                    $updateData = [
                        'status'     => 'A',
                        'updated_by' => $user->username,
                        'updated_at' => $now,
                    ];

                    /*
                    |--------------------------------------------------------------------------
                    | CHANGECARD
                    |--------------------------------------------------------------------------
                    | Status jadi A, attachment ambil dari detail.
                    |--------------------------------------------------------------------------
                    */
                    if ($parkingType === 'CHANGECARD') {
                        $updateData['attach_stnk'] = $detail->attach_stnk;
                        $updateData['attach_idcard'] = $detail->attach_idcard;
                        $updateData['attach_bukti_bayar'] = $detail->attach_bukti_bayar;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | CHANGENOPOL
                    |--------------------------------------------------------------------------
                    | Status jadi A, attachment ambil dari detail,
                    | nopol dan jenis_kendaraan diganti dengan data baru dari detail.
                    |--------------------------------------------------------------------------
                    */
                    if ($parkingType === 'CHANGENOPOL') {
                        $updateData['nopol'] = strtoupper(trim((string) $detail->nopol));
                        $updateData['jenis_kendaraan'] = $detail->jenis_kendaraan;

                        $updateData['attach_stnk'] = $detail->attach_stnk;
                        $updateData['attach_idcard'] = $detail->attach_idcard;
                        $updateData['attach_bukti_bayar'] = $detail->attach_bukti_bayar;
                    }

                    $updated = $q->update($updateData);

                    \Log::info('Approve PKR update MsParkingKendaraan', [
                        'docid'           => $refnbr,
                        'detail_id'       => $detail->id,
                        'parking_type'    => $detail->parking_type,
                        'worker_type'     => $detail->worker_type,
                        'site_id_parking' => $detail->site_id_parking,
                        'perpost'         => $detail->perpost,
                        'username'        => $detail->username,
                        'nama'            => $detail->nama,
                        'match_nopol'     => $matchNopol,
                        'new_nopol'       => $detail->nopol,
                        'new_jenis'       => $detail->jenis_kendaraan,
                        'updated_rows'    => $updated,
                    ]);
                }

                $approvalCtl->notifyRequesterOnStatus(
                    $parking->docid,
                    $docName,
                    'C',
                    $parking->created_by,
                    $docUrl,
                    [
                        'cpnyid'          => $parking->cpny_id ?? '',
                        'deptname'        => $parking->department_id ?? '',
                        'date'            => $parking->parking_regist_date,
                        'info'            => $parking->info,
                        'fullname'        => $fullname,
                        'name'            => $fullname,
                        'createdby'       => $fullname,
                        'site_id_parking' => $parking->site_id_parking,
                        'parking_type'    => $parking->parking_type,
                        'worker_type'     => $parking->worker_type,
                        'perpost'         => $parking->perpost,
                    ]
                );
            },

            /*
            |--------------------------------------------------------------------------
            | NOTIFY NEXT APPROVER
            |--------------------------------------------------------------------------
            | Jika masih ada next approver.
            |--------------------------------------------------------------------------
            */
            function ($next, Carbon $now) use ($parking, $docUrl, $user, $approvalCtl, $docName, $doctype) {
                $approvalCtl->notifyFirstApprover(
                    $parking->docid,
                    $doctype,
                    'P',
                    $docName,
                    $docUrl,
                    [
                        'info'            => $parking->info,
                        'createdby'       => $parking->created_by,
                        'date'            => $now->toDateTimeString(),
                        'cpny_id'         => $parking->cpny_id,
                        'department_id'   => $parking->department_id,
                        'site_id_parking' => $parking->site_id_parking,
                        'parking_type'    => $parking->parking_type,
                        'worker_type'     => $parking->worker_type,
                        'perpost'         => $parking->perpost,
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | Jejak approver terakhir yang proses
                |--------------------------------------------------------------------------
                */
                $parking->completed_by = $user->username;
                $parking->completed_at = $now;
                $parking->updated_by = $user->username;
                $parking->updated_at = $now;
                $parking->save();
            }
        );

        if (!($result['ok'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Approve failed',
            ], 403);
        }

        return response()->json([
            'success'   => true,
            'completed' => $result['completed'] ?? false,
            'message'   => ($result['completed'] ?? false)
                ? 'Parking Registration approved and completed successfully.'
                : 'Parking Registration approved successfully.',
        ]);
    }

    public function rejectParkingRegistration(Request $request, $docid)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $doctype = 'PKR';
        $docName = 'Parking Registration';

        $parking = TrParkingRegistration::with('creator')
            ->where('docid', $docid)
            ->first();

        if (!$parking) {
            return response()->json([
                'success' => false,
                'message' => 'Parking Registration not found',
            ], 404);
        }

        $eid = Hashids::encode($parking->id);
        $docUrl = url('/showparkingregistration/' . $eid);

        $fullname = data_get($parking, 'creator.name') ?: $parking->created_by;

        $approvalCtl = app(\App\Http\Controllers\ApprovalController::class);

        $result = $approvalCtl->rejectStep(
            $parking->docid,
            $doctype,
            $user->username,
            $user->name ?? $user->username,

            function (string $refnbr, $now) use ($parking, $fullname, $docUrl, $user, $approvalCtl, $docName, $doctype, $request) {
                /*
                |--------------------------------------------------------------------------
                | Header PKR -> Rejected
                |--------------------------------------------------------------------------
                */
                $parking->status = 'R';
                $parking->completed_by = $user->username;
                $parking->completed_at = $now;
                $parking->updated_by = $user->username;
                $parking->updated_at = $now;
                $parking->save();

                /*
                |--------------------------------------------------------------------------
                | Ambil detail sebelum update status detail
                |--------------------------------------------------------------------------
                */
                $details = TrParkingRegistrationDetail::where('docid', $refnbr)
                    ->get();

                /*
                |--------------------------------------------------------------------------
                | Detail PKR -> Rejected
                |--------------------------------------------------------------------------
                */
                TrParkingRegistrationDetail::where('docid', $refnbr)
                    ->whereIn('status', ['P', 'D'])
                    ->update([
                        'status'     => 'R',
                        'updated_by' => $user->username,
                        'updated_at' => $now,
                    ]);

                /*
                |--------------------------------------------------------------------------
                | Update / Delete MsParkingKendaraan berdasarkan detail
                |--------------------------------------------------------------------------
                | Tidak pakai ref_nbr.
                |
                | TEMPREQUEST / NEWREQUEST:
                | - data pending ms_parking_kendaraan dihapus
                |
                | Selain itu:
                | - data pending dikembalikan status A
                |--------------------------------------------------------------------------
                */
                foreach ($details as $detail) {
                    $parkingType = strtoupper(trim((string) $detail->parking_type));

                    /*
                    |--------------------------------------------------------------------------
                    | Untuk CHANGENOPOL
                    |--------------------------------------------------------------------------
                    | Master kendaraan masih menyimpan nopol lama.
                    | Contoh:
                    | - detail nopol      = F1234BB
                    | - detail nopol_lama = B24452AA
                    | - master nopol      = B24452AA
                    |--------------------------------------------------------------------------
                    */
                    $matchNopol = $parkingType === 'CHANGENOPOL'
                        ? strtoupper(trim((string) $detail->nopol_lama))
                        : strtoupper(trim((string) $detail->nopol));

                    $matchNopolClean = strtoupper(preg_replace('/\s+/', '', (string) $matchNopol));

                    /*
                    |--------------------------------------------------------------------------
                    | Query master kendaraan
                    |--------------------------------------------------------------------------
                    | Jangan pakai where parking_type di sini.
                    | Karena master kendaraan bisa masih NEWREQUEST,
                    | sedangkan request sekarang CHANGENOPOL / CHANGECARD / RENEWAL.
                    |--------------------------------------------------------------------------
                    */
                    $q = MsParkingKendaraan::query()
                        ->whereIn('status', ['P', 'D'])
                        ->where('site_id_parking', $detail->site_id_parking)
                        ->where('worker_type', $detail->worker_type)                       
                        ->whereRaw(
                            "UPPER(REGEXP_REPLACE(TRIM(nopol), '\\s+', '', 'g')) = ?",
                            [$matchNopolClean]
                        );

                    if (!empty($detail->username)) {
                        $q->where('username', $detail->username);
                    } else {
                        $q->whereRaw('UPPER(TRIM(nama)) = ?', [
                            strtoupper(trim((string) $detail->nama))
                        ]);
                    }

                    if (in_array($parkingType, ['TEMPREQUEST', 'NEWREQUEST'], true)) {
                        $deleted = $q->delete();

                        \Log::info('Delete MsParkingKendaraan on PKR revise', [
                            'docid'           => $refnbr,
                            'detail_id'       => $detail->id,
                            'username'        => $detail->username,
                            'nama'            => $detail->nama,
                            'parking_type'    => $detail->parking_type,
                            'site_id_parking' => $detail->site_id_parking,
                            'worker_type'     => $detail->worker_type,
                            'perpost'         => $detail->perpost,
                            'match_nopol'     => $matchNopol,
                            'match_clean'     => $matchNopolClean,
                            'deleted_rows'    => $deleted,
                        ]);
                    } else {
                        $updated = $q->update([
                            'status'       => 'A',                           
                            'updated_by'   => $user->username,
                            'updated_at'   => $now,
                        ]);

                        \Log::info('Rollback MsParkingKendaraan to A on PKR revise', [
                            'docid'           => $refnbr,
                            'detail_id'       => $detail->id,
                            'username'        => $detail->username,
                            'nama'            => $detail->nama,
                            'request_type'    => $detail->parking_type,
                            'site_id_parking' => $detail->site_id_parking,
                            'worker_type'     => $detail->worker_type,
                            'perpost'         => $detail->perpost,
                            'match_nopol'     => $matchNopol,
                            'match_clean'     => $matchNopolClean,
                            'updated_rows'    => $updated,
                        ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Notify requester
                |--------------------------------------------------------------------------
                */
                $approvalCtl->notifyRequesterOnStatus(
                    $parking->docid,
                    $docName,
                    'R',
                    $parking->created_by,
                    $docUrl,
                    [
                        'cpnyid'          => $parking->cpny_id ?? '',
                        'deptname'        => $parking->department_id ?? '',
                        'date'            => $now->toDateString(),
                        'info'            => $parking->info,
                        'fullname'        => $fullname,
                        'name'            => $fullname,
                        'createdby'       => $fullname,
                        'site_id_parking' => $parking->site_id_parking,
                        'parking_type'    => $parking->parking_type,
                        'worker_type'     => $parking->worker_type,
                        'perpost'         => $parking->perpost,
                        'reason'          => $request->input('reason'),
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | Simpan komentar reject
                |--------------------------------------------------------------------------
                */
                try {
                    app(\App\Http\Controllers\SendCommentController::class)
                        ->sendmsg($parking->id, $doctype, $request);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        );

        if (!($result['ok'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Reject failed',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Parking Registration rejected successfully',
        ]);
    }

    public function reviseParkingRegistration(Request $request, $docid)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $doctype = 'PKR';
        $docName = 'Parking Registration';

        $parking = TrParkingRegistration::with('creator')
            ->where('docid', $docid)
            ->first();

        if (!$parking) {
            return response()->json([
                'success' => false,
                'message' => 'Parking Registration not found',
            ], 404);
        }

        $eid = Hashids::encode($parking->id);
        $docUrl = url('/showparkingregistration/' . $eid);

        $fullname = data_get($parking, 'creator.name') ?: $parking->created_by;

        $approvalCtl = app(\App\Http\Controllers\ApprovalController::class);

        $result = $approvalCtl->reviseStep(
            $parking->docid,
            $doctype,
            $user->username,
            $user->name ?? $user->username,

            function (string $refnbr, $now) use ($parking, $fullname, $docUrl, $user, $approvalCtl, $docName, $doctype, $request) {
                /*
                |--------------------------------------------------------------------------
                | Header PKR -> Revise
                |--------------------------------------------------------------------------
                */
                $parking->status = 'D';
                $parking->completed_by = $user->username;
                $parking->completed_at = $now;
                $parking->updated_by = $user->username;
                $parking->updated_at = $now;
                $parking->save();

                /*
                |--------------------------------------------------------------------------
                | Ambil detail sebelum update status detail
                |--------------------------------------------------------------------------
                */
                $details = TrParkingRegistrationDetail::where('docid', $refnbr)
                    ->get();

                /*
                |--------------------------------------------------------------------------
                | Detail PKR -> Revise
                |--------------------------------------------------------------------------
                */
                TrParkingRegistrationDetail::where('docid', $refnbr)
                    ->whereIn('status', ['P', 'D'])
                    ->update([
                        'status'     => 'D',
                        'updated_by' => $user->username,
                        'updated_at' => $now,
                    ]);

                /*
                |--------------------------------------------------------------------------
                | Update / Delete MsParkingKendaraan berdasarkan detail
                |--------------------------------------------------------------------------
                | Tidak pakai ref_nbr.
                |
                | TEMPREQUEST / NEWREQUEST:
                | - data pending ms_parking_kendaraan dihapus
                |
                | Selain itu:
                | - data pending dikembalikan status A
                |--------------------------------------------------------------------------
                */
                foreach ($details as $detail) {
                    $parkingType = strtoupper(trim((string) $detail->parking_type));

                    /*
                    |--------------------------------------------------------------------------
                    | Untuk CHANGENOPOL
                    |--------------------------------------------------------------------------
                    | Master kendaraan masih menyimpan nopol lama.
                    | Contoh:
                    | - detail nopol      = F1234BB
                    | - detail nopol_lama = B24452AA
                    | - master nopol      = B24452AA
                    |--------------------------------------------------------------------------
                    */
                    $matchNopol = $parkingType === 'CHANGENOPOL'
                        ? strtoupper(trim((string) $detail->nopol_lama))
                        : strtoupper(trim((string) $detail->nopol));

                    $matchNopolClean = strtoupper(preg_replace('/\s+/', '', (string) $matchNopol));

                    /*
                    |--------------------------------------------------------------------------
                    | Query master kendaraan
                    |--------------------------------------------------------------------------
                    | Jangan pakai where parking_type di sini.
                    | Karena master kendaraan bisa masih NEWREQUEST,
                    | sedangkan request sekarang CHANGENOPOL / CHANGECARD / RENEWAL.
                    |--------------------------------------------------------------------------
                    */
                    $q = MsParkingKendaraan::query()
                        ->whereIn('status', ['P', 'D'])
                        ->where('site_id_parking', $detail->site_id_parking)
                        ->where('worker_type', $detail->worker_type)
                        // ->where('perpost', $detail->perpost)
                        ->whereRaw(
                            "UPPER(REGEXP_REPLACE(TRIM(nopol), '\\s+', '', 'g')) = ?",
                            [$matchNopolClean]
                        );

                    if (!empty($detail->username)) {
                        $q->where('username', $detail->username);
                    } else {
                        $q->whereRaw('UPPER(TRIM(nama)) = ?', [
                            strtoupper(trim((string) $detail->nama))
                        ]);
                    }

                    if (in_array($parkingType, ['TEMPREQUEST', 'NEWREQUEST'], true)) {
                        $deleted = $q->delete();

                        \Log::info('Delete MsParkingKendaraan on PKR revise', [
                            'docid'           => $refnbr,
                            'detail_id'       => $detail->id,
                            'username'        => $detail->username,
                            'nama'            => $detail->nama,
                            'parking_type'    => $detail->parking_type,
                            'site_id_parking' => $detail->site_id_parking,
                            'worker_type'     => $detail->worker_type,
                            'perpost'         => $detail->perpost,
                            'match_nopol'     => $matchNopol,
                            'match_clean'     => $matchNopolClean,
                            'deleted_rows'    => $deleted,
                        ]);
                    } else {
                        $updated = $q->update([
                            'status'       => 'A',                         
                            'updated_by'   => $user->username,
                            'updated_at'   => $now,
                        ]);

                        \Log::info('Rollback MsParkingKendaraan to A on PKR revise', [
                            'docid'           => $refnbr,
                            'detail_id'       => $detail->id,
                            'username'        => $detail->username,
                            'nama'            => $detail->nama,
                            'request_type'    => $detail->parking_type,
                            'site_id_parking' => $detail->site_id_parking,
                            'worker_type'     => $detail->worker_type,
                            'perpost'         => $detail->perpost,
                            'match_nopol'     => $matchNopol,
                            'match_clean'     => $matchNopolClean,
                            'updated_rows'    => $updated,
                        ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Notify requester
                |--------------------------------------------------------------------------
                */
                $approvalCtl->notifyRequesterOnStatus(
                    $parking->docid,
                    $docName,
                    'D',
                    $parking->created_by,
                    $docUrl,
                    [
                        'cpnyid'          => $parking->cpny_id ?? '',
                        'deptname'        => $parking->department_id ?? '',
                        'date'            => $now->toDateString(),
                        'info'            => $parking->info,
                        'fullname'        => $fullname,
                        'name'            => $fullname,
                        'createdby'       => $fullname,
                        'site_id_parking' => $parking->site_id_parking,
                        'parking_type'    => $parking->parking_type,
                        'worker_type'     => $parking->worker_type,
                        'perpost'         => $parking->perpost,
                        'reason'          => $request->input('reason'),
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | Simpan komentar revise
                |--------------------------------------------------------------------------
                */
                try {
                    app(\App\Http\Controllers\SendCommentController::class)
                        ->sendmsg($parking->id, $doctype, $request);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        );

        if (!($result['ok'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Revise failed',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Parking Registration revised successfully',
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
        $itemReq = TrParkingRegistration::with([
                'requestType:requesttypeid,requesttype_name',
                'creator:username,name',
            ])
            ->findOrFail($id);

        // Detail baris Item Request
        $itemReqdetail = TrParkingRegistrationdetail::with([
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

    private function uploadParkingDetailFileGcs(
        ?UploadedFile $file,
        string $docid,
        string $type,
        int $rowNo,
        string $createdBy
    ): ?string {
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return null;
        }
        $doctype = 'PKR';        
        $year = now()->year;

        $baseFolder = 'att-parking-registration';
        $folder = "{$baseFolder}/{$doctype}/{$year}";

        $originalName = str_replace(['%', '\\', '/'], '', $file->getClientOriginalName());
        $ext = $file->getClientOriginalExtension();

        $randomPrefix = md5(random_int(1, 99999999));
        $filename = strtoupper($type) . '_' . $rowNo . '_' . $randomPrefix . '.' . $ext;

        $gcsPath = "{$folder}/{$filename}";

        $config = config('filesystems.disks.gcs');

        $keyFilePath = $config['key_file'];

        if (!Str::startsWith($keyFilePath, ['/', 'C:\\', 'D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }

        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]);

        $bucket = $storage->bucket($config['bucket']);

        try {
            $bucket->upload(
                fopen($file->getPathname(), 'r'),
                [
                    'name'          => $gcsPath,
                    'predefinedAcl' => 'private',
                    'metadata'      => [
                        'contentType' => $file->getMimeType(),
                        'metadata'    => [
                            'original-name' => $originalName,
                            'docid'         => $docid,
                            'type'          => strtoupper($type),
                            'row_no'        => (string) $rowNo,
                            'created_by'    => $createdBy,
                        ],
                    ],
                ]
            );

            Log::info('Upload parking detail attachment sukses', [
                'docid'   => $docid,
                'type'    => $type,
                'gcsPath' => $gcsPath,
            ]);

            return $gcsPath;
        } catch (\Throwable $e) {
            Log::error('Upload parking detail attachment gagal', [
                'docid'   => $docid,
                'type'    => $type,
                'gcsPath' => $gcsPath,
                'error'   => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function cancelParkingRegistration(Request $request, $docid)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $parking = TrParkingRegistration::where('docid', $docid)->first();

        if (!$parking) {
            return response()->json([
                'success' => false,
                'message' => 'Parking Registration not found',
            ], 404);
        }

        // Optional: hanya creator yang boleh cancel
        if ($parking->created_by !== $user->username) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to cancel this document.',
            ], 403);
        }

        // Optional: hanya status D/P yang boleh dicancel
        if (!in_array($parking->status, ['D', 'P'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Only Revise / On Progress document can be cancelled.',
            ], 403);
        }

        $parking->update([
            'status'     => 'X',
            'updated_by' => $user->username,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Parking Registration cancelled successfully.',
        ]);
    }

    public function toggleStatusParkingKendaraan(Request $request, $id)
    {
        // dd($id);
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $hasAccess = SysUserRole::where('username', $user->username)
            ->where('role_id', 'PARKINGACCESS')
            ->where('status', 'A')
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to update parking master.',
            ], 403);
        }

        $row = MsParkingKendaraan::where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Data kendaraan tidak ditemukan.',
            ], 404);
        }

        $newStatus = strtoupper((string) $row->status) === 'A' ? 'I' : 'A';

        $row->update([
            'status'     => $newStatus,
            'updated_by' => $user->username,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $newStatus === 'A'
                ? 'Data kendaraan berhasil diaktifkan.'
                : 'Data kendaraan berhasil dinonaktifkan.',
            'status' => $newStatus,
        ]);
    }

    public function updateNoKartuParkingKendaraan(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $hasAccess = SysUserRole::where('username', $user->username)
            ->where('role_id', 'PARKINGACCESS')
            ->where('status', 'A')
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to update parking master.',
            ], 403);
        }

        $request->validate([
            'no_kartu' => ['required', 'string', 'max:100'],
        ]);

        $row = MsParkingKendaraan::where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Data kendaraan tidak ditemukan.',
            ], 404);
        }

        $row->update([
            'no_kartu'   => $request->no_kartu,
            'updated_by' => $user->username,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'No Kartu berhasil disimpan.',
            'no_kartu' => $row->no_kartu,
        ]);
    }





    






}
