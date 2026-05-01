<?php

namespace App\Http\Controllers;

use App\Exports\IMBudgetNonPurchDetailExport;
use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\Autonbr;
use App\Models\Budget;
use App\Models\BudgetDetail;
use App\Models\BusinessUnit;
use App\Models\MsCompany;
use App\Models\MsSite;
use App\Models\Site;
use App\Models\SysUserRole;
use App\Models\TrApproval;
use App\Models\TrAttachment;
use App\Models\TrCS;
use App\Models\TrCSdetail;
use App\Models\TrPO;
use App\Models\TrPOdetail;
use App\Models\TrReceipt;
use App\Models\TrReceiptdetail;
use App\Models\TrSPB;
use App\Models\TrSPBdetail;
use App\Models\TrImbudgetNonPurch;
use App\Models\TrImbudgetNonPurchdetail;
use App\Models\TrWO;
use App\Models\User;
use App\Models\Userbusinessunit;
use App\Models\Usercpny;
use App\Models\Userdept;
use App\Models\VTrackingIMBudgetNonPurchFlow;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Mail;
use Vinkla\Hashids\Facades\Hashids;

class IMBudgetNonPurchController extends Controller
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

        $base = TrImbudgetNonPurch::query()
            ->whereIn('cpny_id', $cpnyIds)
            ->whereIn('department_id', $deptIds);

        $all = (clone $base)->count();
        $onProgress = (clone $base)->where('status', 'P')->count();
        $reject = (clone $base)->where('status', 'R')->count();
        $revise = (clone $base)->where('status', 'D')->count();
        $completed = (clone $base)->where('status', 'C')->count();

        $allListCount = TrImbudgetNonPurch::query()
            ->whereIn('cpny_id', $cpnyIds)
            ->count();

        return view('pages.imbudgetnonpurch.imbudgetnonpurch', compact(
            'all',
            'onProgress',
            'reject',
            'revise',
            'completed',
            'allListCount'
        ));
    }

    public function json(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([], 401);
        }

        $cpnyIds = is_string($user->cpny_id)
            ? array_filter(array_map('trim', explode(',', $user->cpny_id)))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_filter(array_map('trim', explode(',', $user->department_id)))
            : (array) $user->department_id;

        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));

        $status = (string) $request->query('status', '');
        $mode = (string) $request->query('mode', 'normal');
        $deptExtra = (string) $request->query('department_extra', '');

        $baseTable = (new TrImbudgetNonPurch())->getTable();

        $columns = [
            1 => 'im.imnonpurchaseid',
            2 => 'im.imnonpurchasedate',
            3 => 'im.cpny_id',
            4 => 'im.department_id',
            5 => 'im.user_peminta',
            6 => 'im.imnonpurchasetype',
            7 => 'im.imbudgetkeperluan',
            8 => 'im.budget_from',
            9 => 'im.budget_to',
            10 => 'im.expenditure_type',
            11 => 'im.request_budget',
            12 => 'im.status',
        ];

        $orderIdx = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'im.imnonpurchaseid';

        $base = TrImbudgetNonPurch::from($baseTable . ' as im')
            ->whereIn('im.cpny_id', $cpnyIds);

        if ($mode === 'normal') {
            $base->whereIn('im.department_id', $deptIds);

            if ($status !== '') {
                $base->where('im.status', $status);
            }
        }

        if ($mode === 'all') {
            if ($deptExtra !== '') {
                $base->where('im.department_id', $deptExtra);
            }

            if ($status !== '') {
                $base->where('im.status', $status);
            }
        }

        $recordsTotal = (clone $base)
            ->distinct('im.imnonpurchaseid')
            ->count('im.imnonpurchaseid');

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('im.imnonpurchaseid', 'ilike', "%{$search}%")
                    ->orWhere('im.imnonpurchasedate', 'ilike', "%{$search}%")
                    ->orWhere('im.cpny_id', 'ilike', "%{$search}%")
                    ->orWhere('im.department_id', 'ilike', "%{$search}%")
                    ->orWhere('im.user_peminta', 'ilike', "%{$search}%")
                    ->orWhere('im.imnonpurchasetype', 'ilike', "%{$search}%")
                    ->orWhere('im.imbudgetkeperluan', 'ilike', "%{$search}%")
                    ->orWhere('im.budget_from', 'ilike', "%{$search}%")
                    ->orWhere('im.budget_to', 'ilike', "%{$search}%")
                    ->orWhere('im.expenditure_type', 'ilike', "%{$search}%")
                    ->orWhere('im.status', 'ilike', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $base)
            ->distinct('im.imnonpurchaseid')
            ->count('im.imnonpurchaseid');

        $data = $base->select(
                'im.id',
                'im.imnonpurchaseid',
                'im.imnonpurchasedate',
                'im.cpny_id',
                'im.department_id',
                'im.user_peminta',
                'im.imnonpurchasetype',
                'im.imbudgetkeperluan',
                'im.budget_from',
                'im.budget_to',
                'im.expenditure_type',
                'im.existing_budget',
                'im.request_budget',
                'im.over_budget',
                'im.status',
                'im.created_by'
            )
            ->orderBy($orderCol, $orderDir)
            ->orderBy('im.imnonpurchaseid', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        $data->transform(function ($row) {
            $row->eid = Hashids::encode($row->id);
            unset($row->id);
            return $row;
        });

        $departments = [];

        if ($mode === 'all') {
            $departments = TrImbudgetNonPurch::query()
                ->whereIn('cpny_id', $cpnyIds)
                ->select('department_id')
                ->distinct()
                ->orderBy('department_id')
                ->pluck('department_id');
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
            'departments' => $departments,
        ]);
    }

    public function createIMBudgetNonPurch()
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
            ->where('role_id', 'WHSACCESS')
            ->first();

        return view('pages.imbudgetnonpurch.createimbudgetnonpurch', compact('usercpny', 'usercpny2', 'userdept', 'userdept2', 'akses_stock'));
    }

    public function storeIMBudgetNonPurch(Request $request)
    {
        $user = $request->user();
        $username = $user->username ?? 'system';

        $dt = now();
        $year = $dt->year;
        $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $doctype = 'IMR';

        // helper number
        $toFloat = function ($v): float {
            if ($v === null || $v === '') {
                return 0;
            }

            $s = trim((string) $v);
            $s = preg_replace('/\s+/', '', $s);

            $hasComma = str_contains($s, ',');
            $hasDot = str_contains($s, '.');

            if ($hasComma && $hasDot) {
                $lastComma = strrpos($s, ',');
                $lastDot = strrpos($s, '.');

                if ($lastComma > $lastDot) {
                    // 19.000.000,00
                    $s = str_replace('.', '', $s);
                    $s = str_replace(',', '.', $s);
                } else {
                    // 19,000,000.00
                    $s = str_replace(',', '', $s);
                }
            } elseif ($hasComma) {
                // 19000000,00
                $s = str_replace(',', '.', $s);
            } elseif ($hasDot) {
                // 19000000.00 atau 19.000.000
                if (substr_count($s, '.') > 1) {
                    $s = str_replace('.', '', $s);
                }
            }

            return is_numeric($s) ? (float) $s : 0;
        };

        // $totalRequestBudget = 0;
        $approvalCtl = app(ApprovalController::class);

        // Pastikan line approval ada (kalau mau validasi awal sebelum simpan detail, panggil loadLines)
        $approvalCtl->loadLines($doctype, $request->cpnyid, $request->departementid);

        DB::beginTransaction();
        try {

            // ===== GENERATE DOC ID =====
            $auto = $this->nextAutonbr('IMR', $year, $month, $username, 'IMBudget Non Purchase');
            $docid = 'IMR' . substr($year, 2) . $month . sprintf('%03d', $auto['next']);

            // ============================
            // HEADER (MODEL BARU)
            // ============================
            $header = new TrImbudgetNonPurch();
            $header->imnonpurchaseid = $docid;
            $header->imnonpurchasedate = $dt->toDateString();
            $header->cpny_id = $request->cpnyid;
            $header->department_id = $request->departementid;
            $header->user_peminta = $username;

            // ===== Budget Info =====
            $header->imnonpurchasetype = $request->imnonpurchasetype;
            $header->imbudgetkeperluan = $request->keperluan;

            $header->budget_from = $toFloat($request->budget_from);
            $header->budget_to = $toFloat($request->budget_to);
            $header->expenditure_type = $request->expenditure_type;
            $header->existing_budget = $toFloat($request->existing_budget);
            $header->request_budget = $toFloat($request->request_budget);
            $header->over_budget = $toFloat($request->over_budget);

            $header->status = 'P';
            $header->created_by = $username;
            $header->save();

            // ============================
            // DETAIL
            // ============================
            $descs = $request->imnonpurchase_descr ?? [];
            $qtys = $request->qty ?? [];
            $uoms = $request->uom ?? [];
            $notes = $request->note ?? [];
            $prices = $request->price ?? [];
            $totals = $request->total_price ?? [];

            $coaIds = $request->coa_id ?? [];
            $activityIds = $request->activity_id ?? [];
            $busUnitIds = $request->business_unit_id_detail ?? [];
            $deptFinIds = $request->department_fin_id ?? [];
            $actDescrs = $request->activity_descr ?? [];

            $rowCount = count($descs);

            for ($i = 0; $i < $rowCount; $i++) {

                $qty = $toFloat($qtys[$i] ?? 0);
                $price = $toFloat($prices[$i] ?? 0);
                $total = $qty * $price;

                // skip empty row
                if (!$descs[$i] || $qty <= 0) continue;

                TrImbudgetNonPurchDetail::create([
                    'imnonpurchaseid' => $docid,
                    'imnonpurchase_descr' => $descs[$i],
                    'imnonpurchase_note' => $notes[$i] ?? null,
                    'qty' => $qty,
                    'uom' => $uoms[$i] ?? null,
                    'price' => $price,
                    'total_price' => $total,

                    'budget_perpost' => $year,
                    'budget_cpny_id' => $request->cpnyid,
                    'budget_business_unit_id' => $busUnitIds[$i] ?? null,
                    'budget_department_fin_id' => $deptFinIds[$i] ?? null,
                    'budget_account_id' => $coaIds[$i] ?? null,
                    'budget_activity_id' => $activityIds[$i] ?? null,
                    'budget_activity_descr' => $actDescrs[$i] ?? null,

                    'status' => 'P',
                    'created_by' => $username,
                ]);
            }


            // ============================
            // APPROVAL (TETAP)
            // ============================
            $approvalCtl = app(ApprovalController::class);

            $approvalCtl->loadLines(
                'IMR',
                $request->cpnyid,
                $request->departementid
            );

            [$firstApprovalUsernames] = $approvalCtl->generateForDocument(
                $docid,
                'IMR',
                $request->cpnyid,
                $request->departementid,
                $username,
                [
                    'ignore_nominal' => true
                ],
                $dt
            );

            if ($firstApprovalUsernames) {
                $header->completed_by = $firstApprovalUsernames;
                $header->completed_at = $dt;
                $header->save();
            }

            if ($request->hasFile('attachments')) {                
                $meta = [
                    'refnbr' => $docid,
                    'doctype' => $doctype,
                    'cpnyid' => $request->input('cpnyid'),
                    'departementid' => $request->input('departementid'),
                    'base_folder' => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by' => $username,
                ];

                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternal($meta, $files);
                    // tidak return di sini!
                } catch (\Throwable $e) {
                    \DB::rollBack();

                    return response()->json([
                        'message' => 'Failed to create IMR',
                        'error' => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            } else {
                $uploadResult = null; // tidak ada attachment
            }

            $eid = Hashids::encode($header->id);

            $approvalCtl->notifyFirstApprover(
                $docid,
                $doctype,
                $header->status,                 // 'P' | 'R' | 'D' | 'A' | 'C'
                'IM Budget Non Purchase',   // nama dokumen untuk ditampilkan di email
                url('/showimbudgetnonpurch/'.$eid),
                [
                    'info' => $request->imbudgetkeperluan,
                    'createdby' => $header->created_by,
                    'date' => $dt->toDateTimeString(),
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'IM Budget Non Purchase created successfully',
                'docid' => $docid
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'message' => 'Failed to create IM Budget Non Purchase',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function editIMBudgetNonPurch($hash)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $imnonpurchase = TrImbudgetNonPurch::findOrFail($id);

        // =========================
        // DETAIL
        // =========================
        $imnonpurchasedetail = TrImbudgetNonPurchdetail::where('imnonpurchaseid', $imnonpurchase->imnonpurchaseid)
            ->get(); // ❗ FIX: kurang ;

        // =========================
        // BUSINESS UNIT (ambil dari detail)
        // =========================
        $detailBuIds = $imnonpurchasedetail
            ->pluck('budget_business_unit_id')
            ->filter(fn ($v) => !blank($v))
            ->unique()
            ->values();

        $selectedBuId = $detailBuIds->first();

        $selectedBuName = null;
        if ($selectedBuId) {
            $bu = BusinessUnit::where('business_unit_id', $selectedBuId)->first();
            $selectedBuName = $bu->business_unit_name ?? null;
        }

        // inject ke header
        $imnonpurchase->business_unit_id = $selectedBuId;
        $imnonpurchase->business_unit_name = $selectedBuName;

        // =========================
        // USER DATA
        // =========================
        $usercpny = Usercpny::where('username', $user->username)->get();
        $usercpny2 = Usercpny::where('username', $user->username)->first();
        $userdept = Userdept::where('username', $user->username)->get();
        $userdept2 = Userdept::where('username', $user->username)->first();

        // =========================
        // ATTACHMENT (GCS SIGNED URL)
        // =========================
        $rows = TrAttachment::where('refnbr', $imnonpurchase->imnonpurchaseid)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        $config = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];

        if (!Str::startsWith($keyFilePath, ['/', 'C:\\', 'D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }

        $storage = new StorageClient([
            'projectId' => $config['project_id'],
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
                    'path' => $objectPath,
                    'error' => $e->getMessage()
                ]);
            }

            return (object) [
                'id' => $r->id,
                'display_name' => $r->attachment_name,
                'created_by' => $r->created_by,
                'created_at' => $r->created_at,
                'url' => $signedUrl,
                'folder' => $r->folder,
                'filename' => $r->filename,
                'extention' => $r->extention,
                'size' => $r->filesize,
            ];
        });

        $akses_stock = SysUserRole::where('username', $user->username)
            ->where('role_id', 'WHSACCESS')
            ->first();

        return view('pages.imbudgetnonpurch.editimbudgetnonpurch', compact(
            'imnonpurchase',
            'imnonpurchasedetail',
            'usercpny',
            'usercpny2',
            'userdept',
            'userdept2',
            'attachments',
            'hash',
            'akses_stock'
        ));
    }

    public function updateIMBudgetNonPurch(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404, 'IMR tidak ditemukan.');

        $user = $request->user();
        $dt = Carbon::now();

        $doctype = 'IMR';
        $username = $user->username ?? 'system';

        $toFloat = function ($v): float {
            if ($v === null || $v === '') {
                return 0;
            }

            $s = preg_replace('/\s+/', '', trim((string) $v));

            $hasComma = str_contains($s, ',');
            $hasDot = str_contains($s, '.');

            if ($hasComma && $hasDot) {
                $lastComma = strrpos($s, ',');
                $lastDot = strrpos($s, '.');

                if ($lastComma > $lastDot) {
                    $s = str_replace('.', '', $s);
                    $s = str_replace(',', '.', $s);
                } else {
                    $s = str_replace(',', '', $s);
                }
            } elseif ($hasComma) {
                $s = str_replace(',', '.', $s);
            } elseif ($hasDot && substr_count($s, '.') > 1) {
                $s = str_replace('.', '', $s);
            }

            return is_numeric($s) ? (float) $s : 0;
        };

        $approvalCtl = app(ApprovalController::class);
        $approvalCtl->loadLines($doctype, $request->cpnyid, $request->departementid);

        DB::beginTransaction();

        try {
            $header = TrImbudgetNonPurch::findOrFail($id);

            // =========================
            // HEADER
            // =========================
            $header->cpny_id = $request->input('cpnyid');
            $header->department_id = $request->input('departementid');
            $header->user_peminta = $header->user_peminta ?: $username;
            $header->imnonpurchasetype = $request->input('imnonpurchasetype');
            $header->imbudgetkeperluan = $request->input('keperluan');

            $header->budget_from = $toFloat($request->input('budget_from'));
            $header->budget_to = $toFloat($request->input('budget_to'));
            $header->expenditure_type = $request->input('expenditure_type');
            $header->existing_budget = $toFloat($request->input('existing_budget'));
            $header->request_budget = $toFloat($request->input('request_budget'));
            $header->over_budget = $toFloat($request->input('over_budget'));

            $header->status = 'P';
            $header->updated_by = $username;
            $header->save();

            // =========================
            // DETAIL ARRAYS
            // =========================
            $detailIds = array_values($request->input('detail_id', []));
            $descriptions = array_values($request->input('imnonpurchase_descr', []));
            $qtys = array_values($request->input('qty', []));
            $uoms = array_values($request->input('uom', []));
            $notes = array_values($request->input('note', []));
            $prices = array_values($request->input('price', []));
            $totalPrices = array_values($request->input('total_price', []));

            $activityIds = array_values($request->input('activity_id', []));
            $businessUnitIds = array_values($request->input('business_unit_id_detail', []));
            $deptFinIds = array_values($request->input('department_fin_id', []));
            $activityDescrs = array_values($request->input('activity_descr', []));
            $coaIds = array_values($request->input('coa_id', []));

            // =========================
            // DELETE DETAIL REMOVED FROM FORM
            // =========================
            $existingDetailIds = TrImbudgetNonPurchDetail::where('imnonpurchaseid', $header->imnonpurchaseid)
                ->pluck('id')
                ->map(fn ($v) => (string) $v)
                ->toArray();

            $submittedDetailIds = collect($detailIds)
                ->filter(fn ($v) => !blank($v))
                ->map(fn ($v) => (string) $v)
                ->toArray();

            $deleteIds = array_diff($existingDetailIds, $submittedDetailIds);

            if (!empty($deleteIds)) {
                TrImbudgetNonPurchDetail::where('imnonpurchaseid', $header->imnonpurchaseid)
                    ->whereIn('id', $deleteIds)
                    ->delete();
            }

            // =========================
            // UPSERT DETAIL
            // =========================
            $rowCount = max(
                count($descriptions),
                count($qtys),
                count($prices),
                count($coaIds)
            );

            for ($i = 0; $i < $rowCount; ++$i) {
                $descr = trim((string) ($descriptions[$i] ?? ''));
                $qty = $toFloat($qtys[$i] ?? 0);
                $price = $toFloat($prices[$i] ?? 0);
                $totalPrice = $toFloat($totalPrices[$i] ?? 0);
                $coaId = $coaIds[$i] ?? null;

                if ($descr === '' && $qty <= 0 && $price <= 0 && blank($coaId)) {
                    continue;
                }

                if ($totalPrice <= 0) {
                    $totalPrice = $qty * $price;
                }

                $data = [
                    'imnonpurchaseid' => $header->imnonpurchaseid,
                    'imnonpurchase_descr' => $descr,
                    'imnonpurchase_note' => $notes[$i] ?? null,
                    'qty' => $qty,
                    'uom' => $uoms[$i] ?? null,
                    'price' => $price,
                    'total_price' => $totalPrice,

                    'budget_perpost' => $dt->year,
                    'budget_cpny_id' => $request->input('cpnyid'),
                    'budget_business_unit_id' => $businessUnitIds[$i] ?? null,
                    'budget_department_fin_id' => $deptFinIds[$i] ?? null,
                    'budget_account_id' => $coaId,
                    'budget_activity_id' => $activityIds[$i] ?? null,
                    'budget_activity_descr' => $activityDescrs[$i] ?? null,

                    'status' => 'P',
                    'updated_by' => $username,
                ];

                $detailId = $detailIds[$i] ?? null;

                if ($detailId) {
                    $detail = TrImbudgetNonPurchDetail::where('id', $detailId)
                        ->where('imnonpurchaseid', $header->imnonpurchaseid)
                        ->first();

                    if ($detail) {
                        $detail->fill($data);
                        $detail->save();
                    } else {
                        $data['created_by'] = $username;
                        TrImbudgetNonPurchDetail::create($data);
                    }
                } else {
                    $data['created_by'] = $username;
                    TrImbudgetNonPurchDetail::create($data);
                }
            }

            // =========================
            // SYNC REQUEST BUDGET FROM DETAIL
            // =========================
            $grandTotal = TrImbudgetNonPurchDetail::where('imnonpurchaseid', $header->imnonpurchaseid)
                ->sum('total_price');

            $header->request_budget = $grandTotal;

            if ($header->imnonpurchasetype === 'Over Budget') {
                $header->over_budget = $grandTotal - (float) ($header->existing_budget ?? 0);
            } else {
                $header->over_budget = 0;
            }

            $header->save();

            // =========================
            // RE-GENERATE APPROVAL
            // =========================
            $ctx = [
                'is_urgent' => false,
                'first_inventory_category' => null,
                'has_fixed_asset_subtype' => false,
                'ignore_nominal' => true,
            ];

            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $header->imnonpurchaseid,
                $doctype,
                $request->input('cpnyid'),
                $request->input('departementid'),
                $username,
                $ctx,
                $dt
            );

            if ($firstApprovalUsernames) {
                $header->completed_by = $firstApprovalUsernames;
                $header->completed_at = $dt;
                $header->save();
            }

            // =========================
            // UPLOAD NEW ATTACHMENTS
            // =========================
            $uploadResult = null;

            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr' => $header->imnonpurchaseid,
                    'doctype' => $doctype,
                    'cpnyid' => $request->input('cpnyid'),
                    'departementid' => $request->input('departementid'),
                    'base_folder' => 'att-purchasing-app/' . strtolower($doctype),
                    'created_by' => $username,
                ];

                $files = (array) $request->file('attachments');

                $uploader = app(TrAttachmentController::class);
                $uploadResult = $uploader->uploadInternal($meta, $files);
            }

            $eid = Hashids::encode($header->id);

            $approvalCtl->notifyFirstApprover(
                $header->imnonpurchaseid,
                $doctype,
                $header->status,
                'IMBudgetNonPurch',
                url('/showimbudgetnonpurch/' . $eid),
                [
                    'info' => $header->imbudgetkeperluan,
                    'createdby' => $header->created_by,
                    'date' => $dt->toDateTimeString(),
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'IMBudgetNonPurch updated successfully',
                'imnonpurchaseid' => $header->imnonpurchaseid,
                'grand_total' => $grandTotal,
                'attachments' => $uploadResult,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Update failed',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function removeAttachment($id)
    {
        try {
            $attachment = TrAttachment::findOrFail($id);
            $attachment->update(['status' => 'X']); // Update status ke "D" (Deleted)

            return response()->json(['success' => true, 'message' => 'Attachment status updated']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update attachment status', 'error' => $e->getMessage()], 500);
        }
    }

    public function showIMBudgetNonPurch($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        
        $header = TrImbudgetNonPurch::with([          
            'creator:username,name',
        ])
        ->findOrFail($id);

     
        $details = TrImbudgetNonPurchDetail::where('imnonpurchaseid', $header->imnonpurchaseid)
            ->orderBy('id', 'desc')
            ->get();

        $budgets = BudgetDetail::leftJoin('ms_coa', function ($join) {
            $join->on('ms_budget.account_id', '=', 'ms_coa.account_id')
                ->on('ms_budget.cpny_id', '=', 'ms_coa.cpny_id');
        })
            ->where('ms_budget.status', 'C')
            ->select(
                'ms_budget.cpny_id',
                'ms_budget.business_unit_id',
                'ms_budget.department_fin_id',
                'ms_budget.account_id',
                'ms_budget.activity_id',
                'ms_budget.activity_descr',
                'ms_budget.perpost',
                'ms_budget.totalbudget',
                'ms_budget.totalbudget_add',
                'ms_budget.total_reserve',
                'ms_budget.total_used',
                'ms_coa.account_descr as account_descr'
            )
                ->get();

        $budgetMap = [];

        foreach ($budgets as $b) {
            $key = implode('|', [
                $b->cpny_id,
                $b->business_unit_id,
                $b->department_fin_id,
                $b->account_id,
                $b->activity_descr,
                $b->perpost,
            ]);

            $budgetMap[$key] = $b;
        }

        foreach ($details as $item) {
            $key = implode('|', [
                $item->budget_cpny_id,
                $item->budget_business_unit_id,
                $item->budget_department_fin_id,
                $item->budget_account_id,
                $item->budget_activity_descr,
                $item->budget_perpost,
            ]);

            if (isset($budgetMap[$key])) {
                $budget = $budgetMap[$key];

                $item->budget_data = $budget;
                $item->account_descr = $budget->account_descr;

                $budgetValue = (float) ($budget->totalbudget ?? 0);
                $additional = (float) ($budget->totalbudget_add ?? 0);
                $reserved = (float) ($budget->total_reserve ?? 0);
                $used = (float) ($budget->total_used ?? 0);

                $item->budget_remaining =
                    $budgetValue + $additional - $reserved - $used;
            } else {
                $item->budget_data = null;
                $item->account_descr = null;
                $item->budget_remaining = 0;
            }
        }

        $attachment = $this->mapAttachmentsToSignedUrl($header->imnonpurchaseid);        

        $loginUsername = $user->username ?? $user->name ?? null;
        $canUpload = $header->created_by === $loginUsername;
        $akses_cc = SysUserRole::where('username', $user->username)
            ->where('role_id', 'COSTCTRLACCESS')
            ->first();

        $userCpny = Usercpny::query()
        ->where('username', $user->username)->where('status', 'A')
        ->pluck('cpny_id')->values();

        $userBu = Userbusinessunit::query()
        ->where('username', $user->username)->where('status', 'A')
        ->get(['cpny_id', 'business_unit_id']);

        $userCpnyIds = Usercpny::query()
            ->where('username', $user->username)
            ->where('status', 'A')
            ->pluck('cpny_id');

        $userDeptFin = Budget::query()
            ->whereIn('cpny_id', $userCpnyIds)
            ->where('status', 'C')
            ->whereNotNull('department_fin_id')
            ->select('department_fin_id')
            ->distinct()
            ->orderBy('department_fin_id')
            ->get();


        return view('pages.imbudgetnonpurch.showimbudgetnonpurch', compact('header', 'details', 'hash', 'canUpload', 'akses_cc', 'userCpny', 'userBu', 'userDeptFin', 'attachment'  ));
    }

    public function exportDetail($id)
    {
        $imnonpurchase = TrImbudgetNonPurch::findOrFail($id);

        $imnonpurchasedetail = TrImbudgetNonPurchdetail::with([
            'location',
            'subLocation',
        ])
        ->where('imnonpurchaseid', $imnonpurchase->imnonpurchaseid)
        ->orderBy('imnonpurchase_no', 'ASC')
        ->get();

        $budgets = BudgetDetail::select(
            'cpny_id',
            'business_unit_id',
            'department_fin_id',
            'account_id',
            'activity_id',
            'perpost',
            'totalbudget',
            'total_reserve',
            'total_used'
        )->get();

        foreach ($imnonpurchasedetail as $item) {
            $budget = $budgets->first(function ($b) use ($item) {
                return $b->cpny_id == $item->budget_cpny_id
                    && $b->business_unit_id == $item->budget_business_unit_id
                    && $b->department_fin_id == $item->budget_department_fin_id
                    && $b->account_id == $item->budget_account_id
                    && $b->activity_id == $item->budget_activity_id
                    && $b->perpost == $item->budget_perpost;
            });

            $item->budget_data = $budget;
        }

        return Excel::download(
            new IMBudgetNonPurchDetailExport($imnonpurchasedetail),
            'IMBudgetNonPurch_Detail_'.$imnonpurchase->imnonpurchaseid.'.xlsx'
        );
    }

    private function mapAttachmentsToSignedUrl($refnbr)
    {
        $rows = TrAttachment::where('refnbr', $refnbr)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        $config = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];
        if (!Str::startsWith($keyFilePath, ['/', 'C:\\', 'D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }

        $storage = new StorageClient([
            'projectId' => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]);
        $bucket = $storage->bucket($config['bucket']);

        return $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/').'/'.$r->filename;
            $object = $bucket->object($objectPath);

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
                'display_name' => $r->attachment_name,
                'created_by' => $r->created_by,
                'created_at' => $r->created_at,
                'url' => $signedUrl,
                'folder' => $r->folder,
                'filename' => $r->filename,
                'extention' => $r->extention,
                'size' => $r->filesize,
            ];
        });
    }

    public function approveIMBudgetNonPurch(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'IMR';

        $imnonpurchase = TrImbudgetNonPurch::with('creator')->where('imnonpurchaseid', $docid)->first();
        if (!$imnonpurchase) {
            return response()->json(['success' => false, 'message' => 'IMBudgetNonPurch not found'], 404);
        }

        $eid = Hashids::encode($imnonpurchase->id);
        $docUrl = url('/showimbudgetnonpurch/'.$eid);
        $fullname = data_get($imnonpurchase, 'creator.name') ?: $imnonpurchase->created_by;

        $result = app(ApprovalController::class)->approveStep(
            $imnonpurchase->imnonpurchaseid,
            $doctype,
            $user->username,
            $user->name,

            // complete: update header/detail + email creator complete
            function (string $refnbr, \Carbon\Carbon $now) use ($imnonpurchase, $fullname, $docUrl) {
                $imnonpurchase->status = 'C';
                $imnonpurchase->completed_by = $imnonpurchase->completed_by ?: auth()->user()->username;
                $imnonpurchase->completed_at = $now;
                $imnonpurchase->save();

                TrImbudgetNonPurchdetail::where('imnonpurchaseid', $imnonpurchase->imnonpurchaseid)->update(['status' => 'C']);

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $imnonpurchase->imnonpurchaseid,
                    'IMBudgetNonPurch',
                    'C',
                    $imnonpurchase->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $imnonpurchase->cpny_id ?? $imnonpurchase->cpnyid ?? '',
                        'deptname' => $imnonpurchase->department_id ?? $imnonpurchase->departementid ?? '',
                        'date' => $imnonpurchase->imnonpurchasedate,
                        'info' => $imnonpurchase->keperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                    ]
                );
            },

            // notify next approver
            function ($next, \Carbon\Carbon $now) use ($imnonpurchase, $docUrl) {
                app(ApprovalController::class)->notifyFirstApprover(
                    $imnonpurchase->imnonpurchaseid,
                    'IMR',
                    'P',
                    'IMBudgetNonPurch',
                    $docUrl,
                    [
                        'info' => $imnonpurchase->keperluan,
                        'createdby' => $imnonpurchase->created_by,
                        'date' => $now->toDateTimeString(),
                    ]
                );

                // jejak terakhir diproses (optional)
                $imnonpurchase->completed_by = auth()->user()->username;
                $imnonpurchase->completed_at = $now;
                $imnonpurchase->save();
            }
        );

        if (!$result['ok']) {
            return response()->json(['success' => false, 'message' => $result['message'] ?? 'Approve failed'], 403);
        }

        return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    }

    public function rejectIMBudgetNonPurch(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'IMR';

        $imnonpurchase = TrImbudgetNonPurch::with('creator')->where('imnonpurchaseid', $docid)->first();
        if (!$imnonpurchase) {
            return response()->json(['success' => false, 'message' => 'IMBudgetNonPurch not found'], 404);
        }

        $eid = Hashids::encode($imnonpurchase->id);
        $docUrl = url('/showimbudgetnonpurch/'.$eid);
        $fullname = data_get($imnonpurchase, 'creator.name') ?: $imnonpurchase->created_by;

        $result = app(ApprovalController::class)->rejectStep(
            $imnonpurchase->imnonpurchaseid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($imnonpurchase, $fullname, $docUrl) {
                $imnonpurchase->status = 'R';
                $imnonpurchase->completed_by = auth()->user()->username;
                $imnonpurchase->completed_at = $now;
                $imnonpurchase->save();

                // =========================
                // 🔥 PANGGIL UPDATE SPB
                // =========================
                try {
                    $spbId = $imnonpurchase->spbid;

                    if ($spbId) {
                        $this->updateSPBQtyIMBudgetNonPurch(
                            $spbId,
                            $imnonpurchase->imnonpurchaseid,
                            auth()->user()->username
                        );
                    }
                } catch (\Throwable $e) {
                    \Log::error('Update SPB after reject IMBudgetNonPurch failed', [
                        'imnonpurchaseid' => $imnonpurchase->imnonpurchaseid,
                        'spbid' => $imnonpurchase->spbid,
                        'error' => $e->getMessage(),
                    ]);
                }

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $imnonpurchase->imnonpurchaseid,
                    'IMBudgetNonPurch',
                    'R',
                    $imnonpurchase->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $imnonpurchase->cpny_id ?? $imnonpurchase->cpnyid ?? '',
                        'deptname' => $imnonpurchase->department_id ?? $imnonpurchase->departementid ?? '',
                        'date' => $now->toDateString(),
                        'info' => $imnonpurchase->keperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                    ]
                );

                // simpan komentar (jika ada)
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($imnonpurchase->id, 'IMR', request());
                } catch (\Throwable $e) {
                }
            }
        );

        if (!$result['ok']) {
            return response()->json(['success' => false, 'message' => $result['message'] ?? 'Reject failed'], 403);
        }

        return response()->json(['success' => true, 'message' => 'IMBudgetNonPurch rejected successfully']);
    }

    public function reviseIMBudgetNonPurch(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'IMR';

        $imnonpurchase = TrImbudgetNonPurch::with('creator')->where('imnonpurchaseid', $docid)->first();
        if (!$imnonpurchase) {
            return response()->json(['success' => false, 'message' => 'IMBudgetNonPurch not found'], 404);
        }

        $eid = Hashids::encode($imnonpurchase->id);
        $docUrl = url('/showimbudgetnonpurch/'.$eid);
        $fullname = data_get($imnonpurchase, 'creator.name') ?: $imnonpurchase->created_by;

        $result = app(ApprovalController::class)->reviseStep(
            $imnonpurchase->imnonpurchaseid,            // refnbr
            $doctype,                 // PT
            $user->username,          // actor
            $user->name,              // actor
            function (string $refnbr, \Carbon\Carbon $now) use ($imnonpurchase, $fullname, $docUrl) {
                // === HEADER IMBudgetNonPurch -> D ===
                $imnonpurchase->status = 'D';
                $imnonpurchase->completed_by = auth()->user()->username;
                $imnonpurchase->completed_at = $now;
                $imnonpurchase->save();

                // (opsional) DETAIL -> D
                // \App\Models\TrImbudgetNonPurchdetail::where('imnonpurchaseid', $imnonpurchase->imnonpurchaseid)->update(['status' => 'D']);

                // === Email ke requester ===
                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $imnonpurchase->imnonpurchaseid,
                    'IMBudgetNonPurch',
                    'D',
                    $imnonpurchase->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $imnonpurchase->cpny_id ?? $imnonpurchase->cpnyid ?? '',
                        'deptname' => $imnonpurchase->department_id ?? $imnonpurchase->departementid ?? '',
                        'date' => $now->toDateString(),
                        'info' => $imnonpurchase->imbudgetkeperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,   // <<< tambahkan ini
                    ]
                );

                // === Simpan komentar (jika ada) ===
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($imnonpurchase->id, 'IMR', request());
                } catch (\Throwable $e) {
                }
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Revise failed',
            ], 403);
        }

        return response()->json(['success' => true, 'message' => 'IMBudgetNonPurch revised successfully']);
    }

    // public function approveIMBudgetNonPurch(Request $request, $docid)
    // {
    //     $now  = Carbon::now();
    //     $user = $request->user();
    //     $doctype = 'IMR';

    //     // Ambil header + creator
    //     $imnonpurchase = TrImbudgetNonPurch::with('creator')->where('imnonpurchaseid', $docid)->first();
    //     if (!$imnonpurchase) {
    //         return response()->json(['success' => false, 'message' => 'IMBudgetNonPurch not found'], 404);
    //     }
    //     $fullname = data_get($imnonpurchase, 'creator.name') ?: $imnonpurchase->created_by;

    //     // Cari row approval PENDING level terendah yang sudah "aktif" (aprv_datebefore != null)
    //     // Lalu pastikan user saat ini termasuk dalam daftar aprv_username (support ; atau ,)
    //     $currentPending = TrApproval::query()
    //         ->where('refnbr', $imnonpurchase->imnonpurchaseid)
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
    //         $imnonpurchase->completed_by = $user->username;
    //         $imnonpurchase->completed_at = $now;
    //         $imnonpurchase->save();

    //         // 2) Masih ada pending lain?
    //         $pendingCount = TrApproval::query()
    //             ->where('refnbr', $imnonpurchase->imnonpurchaseid)
    //             ->where('aprv_doctype', $doctype)
    //             ->where('status', 'P')
    //             ->count();

    //         $eid = Hashids::encode($imnonpurchase->id);
    //         $subjectMap = [
    //             'P' => 'Waiting Approval',
    //             'R' => 'Rejected Approval',
    //             'D' => 'Revise Approval',
    //             'A' => 'Approved',
    //             'C' => 'Completed',
    //         ];

    //         if ($pendingCount === 0) {
    //             // 3) Tidak ada approver lagi -> dokumen complete
    //             $imnonpurchase->status       = 'C';
    //             $imnonpurchase->completed_by = $user->username;
    //             $imnonpurchase->completed_at = $now;
    //             $imnonpurchase->save();

    //             // Close semua detail
    //             TrImbudgetNonPurchdetail::where('imnonpurchaseid', $imnonpurchase->imnonpurchaseid)->update(['status' => 'C']);

    //             // Kirim email ke requester (creator)
    //             $status        = 'C';
    //             $subjectSuffix = $subjectMap[$status] ?? 'Notification';

    //             $data = [
    //                 'docid'     => $imnonpurchase->imnonpurchaseid,
    //                 'cpnyid'    => $imnonpurchase->cpny_id ?? $imnonpurchase->cpnyid ?? '',
    //                 'deptname'  => $imnonpurchase->department_id ?? $imnonpurchase->departementid ?? '',
    //                 'date'      => $imnonpurchase->imnonpurchasedate,
    //                 'fullname'  => $fullname,
    //                 'name'      => $fullname,
    //                 'createdby' => $fullname,
    //                 'docname'   => 'IMBudgetNonPurch',
    //                 'info'      => $imnonpurchase->keperluan,
    //                 'status'    => $status,
    //                 'url'       => url('/showimbudgetnonpurch/' . $eid),
    //             ];

    //             $recipients = User::where('username', $imnonpurchase->created_by)
    //                 ->where('status', 'A')
    //                 ->get();

    //             foreach ($recipients as $rcp) {
    //                 try {
    //                     $to = $rcp->notification_email ?? $rcp->email;
    //                     Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    //                         $message->to($to)
    //                             ->subject($data['docid'] . ' - ' . $subjectSuffix . ' IMBudgetNonPurch')
    //                             ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //                     });
    //                 } catch (\Throwable $e) {
    //                     Log::error('Failed sending IMBudgetNonPurch completion email', ['error' => $e->getMessage()]);
    //                 }
    //             }

    //         } else {
    //             // 4) Masih ada approver berikutnya -> aktifkan step berikutnya (level terendah)
    //             $next = TrApproval::query()
    //                 ->where('refnbr', $imnonpurchase->imnonpurchaseid)
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
    //                     $imnonpurchase->imnonpurchaseid,
    //                     $doctype,
    //                     'P',
    //                     'IMBudgetNonPurch',
    //                     url('/showimbudgetnonpurch/' . $eid),
    //                     [
    //                         'info'      => $imnonpurchase->keperluan,
    //                         'createdby' => $imnonpurchase->created_by,
    //                         'date'      => $now->toDateTimeString(),
    //                     ]
    //                 );
    //             }
    //         }

    //         DB::commit();
    //         return response()->json(['success' => true, 'message' => 'Task approved successfully']);

    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Approve IMBudgetNonPurch failed', ['error' => $e->getMessage()]);
    //         return response()->json(['success' => false, 'message' => 'Approve failed'], 500);
    //     }
    // }

    // public function rejectIMBudgetNonPurch(Request $request, $docid)
    // {
    //     $now     = Carbon::now();
    //     $user    = $request->user();
    //     $doctype = 'IMR';

    //     // Header + creator
    //     $imnonpurchase = TrImbudgetNonPurch::with('creator')->where('imnonpurchaseid', $docid)->first();
    //     if (!$imnonpurchase) {
    //         return response()->json(['success' => false, 'message' => 'Task not found'], 404);
    //     }
    //     $fullname = data_get($imnonpurchase, 'creator.name') ?: $imnonpurchase->created_by;

    //     // Row approval aktif (pending + sudah "dibuka" datebefore)
    //     $currentPending = TrApproval::query()
    //         ->where('refnbr', $imnonpurchase->imnonpurchaseid)
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

    //         // 2) Update header IMBudgetNonPurch -> Rejected
    //         $imnonpurchase->status       = 'R';
    //         $imnonpurchase->completed_by = $user->username;
    //         $imnonpurchase->completed_at = $now;
    //         $imnonpurchase->save();

    //         // 3) Batalkan semua approval yang masih pending (status 'X')
    //         TrApproval::query()
    //             ->where('refnbr', $imnonpurchase->imnonpurchaseid)
    //             ->where('aprv_doctype', $doctype)
    //             ->where('status', 'P')
    //             ->update(['status' => 'X']);

    //         DB::commit();
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Reject IMBudgetNonPurch failed', ['docid' => $docid, 'error' => $e->getMessage()]);
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
    //         $eid           = Hashids::encode($imnonpurchase->id);

    //         $data = [
    //             'docid'     => $imnonpurchase->imnonpurchaseid,
    //             'cpnyid'    => $imnonpurchase->cpny_id ?? $imnonpurchase->cpnyid ?? '',
    //             'deptname'  => $imnonpurchase->department_id ?? $imnonpurchase->departementid ?? '',
    //             'date'      => $now->toDateString(),
    //             'fullname'  => $fullname,
    //             'name'      => $fullname,
    //             'createdby' => $fullname,
    //             'docname'   => 'IMBudgetNonPurch',
    //             'info'      => $imnonpurchase->keperluan,
    //             'status'    => $status,
    //             'url'       => url('/showimbudgetnonpurch/' . $eid),
    //         ];

    //         $recipients = User::where('username', $imnonpurchase->created_by)
    //             ->where('status', 'A')
    //             ->get();

    //         foreach ($recipients as $rcp) {
    //             $to = $rcp->notification_email ?? $rcp->email;
    //             if (!$to) continue;

    //             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    //                 $message->to($to)
    //                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' IMBudgetNonPurch')
    //                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //             });
    //         }
    //     } catch (\Throwable $e) {
    //         Log::error('Failed sending IMBudgetNonPurch rejected email', [
    //             'docid' => $imnonpurchase->imnonpurchaseid,
    //             'error' => $e->getMessage()
    //         ]);
    //     }

    //     // 5) Simpan komentar penolakan (jika ada)
    //     try {
    //         app('App\Http\Controllers\SendCommentController')->sendmsg($imnonpurchase->id, $doctype, $request);
    //     } catch (\Throwable $e) {
    //         Log::warning('SendComment after reject failed', [
    //             'docid' => $imnonpurchase->imnonpurchaseid,
    //             'error' => $e->getMessage()
    //         ]);
    //     }

    //     return response()->json(['success' => true, 'message' => 'IMBudgetNonPurch rejected successfully']);
    // }

    // public function reviseIMBudgetNonPurch(Request $request, $docid)
    // {
    //     $now     = Carbon::now();
    //     $user    = $request->user();
    //     $doctype = 'IMR';

    //     // 1) Ambil header + creator
    //     $imnonpurchase = TrImbudgetNonPurch::with('creator')->where('imnonpurchaseid', $docid)->first();
    //     if (!$imnonpurchase) {
    //         return response()->json(['success' => false, 'message' => 'IMBudgetNonPurch not found'], 404);
    //     }
    //     $fullname = data_get($imnonpurchase, 'creator.name') ?: $imnonpurchase->created_by;

    //     // 2) Validasi: user harus approver aktif (status P) pada step terendah yang sudah "dibuka" (aprv_datebefore != null)
    //     $currentPending = TrApproval::query()
    //         ->where('refnbr', $imnonpurchase->imnonpurchaseid)
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

    //         // 5) Update header IMBudgetNonPurch -> D (Revise)
    //         $imnonpurchase->status       = 'D';
    //         $imnonpurchase->completed_by = $user->username;
    //         $imnonpurchase->completed_at = $now;
    //         $imnonpurchase->save();

    //         // (opsional) tandai detail sebagai D juga kalau mau:
    //         // TrImbudgetNonPurchdetail::where('imnonpurchaseid', $imnonpurchase->imnonpurchaseid)->update(['status' => 'D']);

    //         // 6) Batalkan semua approval lain yang masih pending (status 'X')
    //         TrApproval::query()
    //             ->where('refnbr', $imnonpurchase->imnonpurchaseid)
    //             ->where('aprv_doctype', $doctype)
    //             ->where('status', 'P')
    //             ->update(['status' => 'X']);

    //         DB::commit();
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Revise IMBudgetNonPurch failed', ['docid' => $docid, 'error' => $e->getMessage()]);
    //         return response()->json(['success' => false, 'message' => 'Revise failed'], 500);
    //     }

    //     // 7) Kirim email ke requester (creator) -> Revise
    //     try {
    //         $status        = 'D';
    //         $subjectMap    = ['P'=>'Waiting Approval','R'=>'Rejected Approval','D'=>'Revise Approval','A'=>'Approved','C'=>'Completed'];
    //         $subjectSuffix = $subjectMap[$status] ?? 'Notification';
    //         $eid           = Hashids::encode($imnonpurchase->id);

    //         $data = [
    //             'docid'     => $imnonpurchase->imnonpurchaseid,
    //             'cpnyid'    => $imnonpurchase->cpny_id ?? $imnonpurchase->cpnyid ?? '',
    //             'deptname'  => $imnonpurchase->department_id ?? $imnonpurchase->departementid ?? '',
    //             'date'      => $now->toDateString(), // atau pakai $currentPending->aprv_dateafter
    //             'fullname'  => $fullname,
    //             'name'      => $fullname,
    //             'createdby' => $fullname,
    //             'docname'   => 'IMBudgetNonPurch',
    //             'info'      => $imnonpurchase->keperluan,
    //             'status'    => $status,
    //             'url'       => url('/showimbudgetnonpurch/' . $eid),
    //         ];

    //         $recipients = User::where('username', $imnonpurchase->created_by)
    //             ->where('status', 'A')
    //             ->get();

    //         foreach ($recipients as $rcp) {
    //             $to = $rcp->notification_email ?? $rcp->email;
    //             if (!$to) continue;

    //             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    //                 $message->to($to)
    //                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' IMBudgetNonPurch')
    //                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //             });
    //         }
    //     } catch (\Throwable $e) {
    //         Log::error('Failed sending IMBudgetNonPurch revise email', [
    //             'docid' => $imnonpurchase->imnonpurchaseid,
    //             'error' => $e->getMessage()
    //         ]);
    //     }

    //     // 8) Simpan komentar revisi (jika ada)
    //     try {
    //         app('App\Http\Controllers\SendCommentController')->sendmsg($imnonpurchase->id, $doctype, $request);
    //     } catch (\Throwable $e) {
    //         Log::warning('SendComment after revise failed', [
    //             'docid' => $imnonpurchase->imnonpurchaseid,
    //             'error' => $e->getMessage()
    //         ]);
    //     }

    //     return response()->json(['success' => true, 'message' => 'IMBudgetNonPurch revised successfully']);
    // }

    public function trackingDetail($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $imnonpurchase = TrImbudgetNonPurch::findOrFail($id);
        $imnonpurchaseNo = $imnonpurchase->imnonpurchaseid;

        $fmt = fn ($dt) => $dt ? Carbon::parse($dt)->format('Y-m-d H:i') : null;
        $approved = fn ($h) => $h ? (!empty($h->completed_by) || !empty($h->completed_at)) : false;

        // ===== IMBudgetNonPurch =====
        $imnonpurchaseDetails = TrImbudgetNonPurchdetail::query()
            ->where('imnonpurchaseid', $imnonpurchaseNo)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        // ===== LIST CS (ALL) =====
        $csList = TrCS::query()
            ->where('imnonpurchasejktid', $imnonpurchaseNo)
            ->whereNull('deleted_at')
            ->orderBy('csdate', 'desc')
            ->get(['csid', 'csdate', 'status', 'completed_by', 'completed_at']);

        // default selected CS: yang terbaru
        $selCsNo = optional($csList->first())->csid;

        // ===== LIST PO (ALL) =====
        // Jika kamu mau PO mengikuti CS terpilih, nanti kita filter dengan csid
        $poList = TrPO::query()
            ->where('imnonpurchasejktid', $imnonpurchaseNo)
            ->where('cpny_id', $imnonpurchase->cpny_id)
            ->whereNull('deleted_at')
            ->orderBy('podate', 'desc')
            ->get(['ponbr', 'podate', 'status', 'csid', 'completed_by', 'completed_at']);

        $selPoNo = optional($poList->first())->ponbr;

        // ===== LIST RECEIPT (ALL) =====
        // Default: receipt paling baru untuk IMBudgetNonPurch ini
        $receiptList = TrReceipt::query()
            ->where('imnonpurchasejktid', $imnonpurchaseNo)
            ->whereNull('deleted_at')
            ->orderBy('receiptdate', 'desc')
            ->get(['receiptnbr', 'receiptdate', 'status', 'ponbr', 'csid', 'completed_by', 'completed_at']);

        // default selected receipt: kalau ada PO terpilih, ambil receipt terbaru by ponbr itu
        $selReceiptNo = optional(
            $receiptList->firstWhere('ponbr', $selPoNo) ?? $receiptList->first()
        )->receiptnbr;

        // ===== DETAIL DEFAULT SELECTED (CS/PO/RECEIPT) =====
        $csHeader = $selCsNo ? TrCS::where('csid', $selCsNo)->whereNull('deleted_at')->first() : null;
        // $csDetails = $selCsNo ? TrCSdetail::where('csid',$selCsNo)->whereNull('deleted_at')->orderBy('id')->get() : collect();
        $csDetails = collect();
        if ($selCsNo) {
            $csHeader = TrCS::where('csid', $selCsNo)->whereNull('deleted_at')->first();

            $isTrue = function ($v) {
                // handle boolean / int / string 't'/'f'
                if (is_bool($v)) {
                    return $v;
                }
                $v = strtolower((string) $v);

                return in_array($v, ['1', 'true', 't', 'yes', 'y'], true);
            };

            $csDetails = TrCSdetail::query()
                ->where('csid', $selCsNo)
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->get()
                // ✅ hanya tampilkan yang vendor selected = true
                ->filter(function ($d) use ($isTrue) {
                    return $isTrue($d->vendor1selected)
                        || $isTrue($d->vendor2selected)
                        || $isTrue($d->vendor3selected)
                        || $isTrue($d->vendor4selected)
                        || $isTrue($d->vendor5selected)
                        || $isTrue($d->vendor6selected);
                })
                // ✅ map jadi array supaya field tambahan pasti ikut ke JSON
                ->map(function ($d) use ($csHeader, $isTrue) {
                    $vendorName = null;
                    $vendorPrice = null;

                    if ($csHeader) {
                        if ($isTrue($d->vendor1selected)) {
                            $vendorName = $csHeader->vendorname1;
                            $vendorPrice = $d->vendorprice1;
                        } elseif ($isTrue($d->vendor2selected)) {
                            $vendorName = $csHeader->vendorname2;
                            $vendorPrice = $d->vendorprice2;
                        } elseif ($isTrue($d->vendor3selected)) {
                            $vendorName = $csHeader->vendorname3;
                            $vendorPrice = $d->vendorprice3;
                        } elseif ($isTrue($d->vendor4selected)) {
                            $vendorName = $csHeader->vendorname4;
                            $vendorPrice = $d->vendorprice4;
                        } elseif ($isTrue($d->vendor5selected)) {
                            $vendorName = $csHeader->vendorname5;
                            $vendorPrice = $d->vendorprice5;
                        } elseif ($isTrue($d->vendor6selected)) {
                            $vendorName = $csHeader->vendorname6;
                            $vendorPrice = $d->vendorprice6;
                        }
                    }

                    return [
                        'id' => $d->id,
                        'inventoryid' => $d->inventoryid,
                        'inventory_descr' => $d->inventory_descr,
                        'qty' => $d->qty,
                        'uom' => $d->uom,

                        // ✅ ini yang kamu butuhin
                        'vendorname_selected' => $vendorName,
                        'vendorprice_selected' => $vendorPrice,

                        'status' => $d->status,
                    ];
                })
                ->values();
        }

        $poHeader = $selPoNo ? TrPO::where('ponbr', $selPoNo)
            ->where('cpny_id', $imnonpurchase->cpny_id)
            ->whereNull('deleted_at')
            ->first() : null;
        $poDetails = $selPoNo ? TrPOdetail::where('ponbr', $selPoNo)
            ->where('budget_cpny_id', $imnonpurchase->cpny_id)
            ->whereNull('deleted_at')->orderBy('id')->get() : collect();

        $receiptHeader = $selReceiptNo ? TrReceipt::where('receiptnbr', $selReceiptNo)->whereNull('deleted_at')->first() : null;
        $receiptDetails = $selReceiptNo ? TrReceiptdetail::where('receiptnbr', $selReceiptNo)->whereNull('deleted_at')->orderBy('id')->get() : collect();

        $lastApprIMBudgetNonPurch = $this->getLastApprovalInfo($imnonpurchaseNo);
        $lastApprCs = $selCsNo ? $this->getLastApprovalInfo($selCsNo) : null;
        $lastApprReceipt = $selReceiptNo ? $this->getLastApprovalInfo($selReceiptNo) : null;

        return response()->json([
            'doc' => $imnonpurchaseNo,

            'lists' => [
                'cs' => $csList->map(fn ($x) => [
                    'doc' => $x->csid,
                    'date' => $fmt($x->csdate),
                    'status' => $x->status,
                    'is_approved' => (!empty($x->completed_by) || !empty($x->completed_at)),
                ])->values(),
                'po' => $poList->map(fn ($x) => [
                    'doc' => $x->ponbr,
                    'date' => $fmt($x->podate),
                    'status' => $x->status,
                    'csid' => $x->csid,
                    'is_approved' => (!empty($x->completed_by) || !empty($x->completed_at)),
                ])->values(),
                'receipt' => $receiptList->map(fn ($x) => [
                    'doc' => $x->receiptnbr,
                    'date' => $fmt($x->receiptdate),
                    'status' => $x->status,
                    'ponbr' => $x->ponbr,
                    'csid' => $x->csid,
                    'is_approved' => (!empty($x->completed_by) || !empty($x->completed_at)),
                ])->values(),
            ],

            'selected' => [
                'cs_no' => $selCsNo,
                'po_no' => $selPoNo,
                'receipt_no' => $selReceiptNo,
            ],

            'imnonpurchase' => [
                'header' => [
                    'doc' => $imnonpurchase->imnonpurchaseid,
                    'date' => $fmt($imnonpurchase->imnonpurchasedate),
                    'cpny_id' => $imnonpurchase->cpny_id,
                    'department_id' => $imnonpurchase->department_id,
                    'keperluan' => $imnonpurchase->keperluan,
                    'status' => $imnonpurchase->status,
                    'created_by' => $imnonpurchase->created_by,
                    'created_at' => $fmt($imnonpurchase->created_at),
                    'completed_by' => $imnonpurchase->completed_by,
                    'completed_at' => $fmt($imnonpurchase->completed_at),
                    'is_approved' => $approved($imnonpurchase),
                    'last_approval' => $lastApprIMBudgetNonPurch,
                    'approval_list' => $this->getApprovalList($imnonpurchaseNo),
                ],
                'details' => $imnonpurchaseDetails,
            ],

            'cs' => [
                'header' => $csHeader ? [
                    'doc' => $csHeader->csid,
                    'date' => $fmt($csHeader->csdate),
                    'cpny_id' => $csHeader->cpny_id,
                    'department_id' => $csHeader->department_id,
                    'keperluan' => $csHeader->keperluan,
                    'created_by' => $csHeader->created_by,
                    'status' => $csHeader->status,
                    'completed_by' => $csHeader->completed_by,
                    'completed_at' => $fmt($csHeader->completed_at),
                    'is_approved' => $approved($csHeader),
                    'last_approval' => $lastApprCs,
                    'approval_list' => $this->getApprovalList($csHeader->csid),
                ] : null,
                'details' => $csDetails,
            ],

            'po' => [
                'header' => $poHeader ? [
                    'doc' => $poHeader->ponbr,
                    'date' => $fmt($poHeader->podate),
                    'cpny_id' => $poHeader->cpny_id,
                    'department_id' => $poHeader->department_id,
                    'vendorname' => $poHeader->vendorname,
                    'status' => $poHeader->status,
                    'created_by' => $poHeader->created_by,
                    'completed_by' => $poHeader->completed_by,
                    'completed_at' => $fmt($poHeader->completed_at),
                    'is_approved' => $approved($poHeader),
                ] : null,
                'details' => $poDetails,
            ],

            'receipt' => [
                'header' => $receiptHeader ? [
                    'doc' => $receiptHeader->receiptnbr,
                    'date' => $fmt($receiptHeader->receiptdate),
                    'cpny_id' => $receiptHeader->cpny_id,
                    'department_id' => $receiptHeader->department_id,
                    'vendorname' => $receiptHeader->vendorname,
                    'status' => $receiptHeader->status,
                    'created_by' => $receiptHeader->created_by,
                    'completed_by' => $receiptHeader->completed_by,
                    'completed_at' => $fmt($receiptHeader->completed_at),
                    'is_approved' => $approved($receiptHeader),
                    'last_approval' => $lastApprReceipt,
                    'approval_list' => $this->getApprovalList($receiptHeader->receiptnbr),
                ] : null,
                'details' => $receiptDetails,
            ],
        ]);
    }

    public function trackingDetailItem($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $imnonpurchase = TrImbudgetNonPurch::findOrFail($id);
        $imnonpurchaseNo = $imnonpurchase->imnonpurchaseid;

        $type = request('type');  // cs|po|receipt
        $doc = request('doc');   // csid / ponbr / receiptnbr

        abort_if(!in_array($type, ['cs', 'po', 'receipt'], true), 400);
        abort_if(!$doc, 400);

        $fmt = fn ($dt) => $dt ? \Carbon\Carbon::parse($dt)->format('Y-m-d H:i') : null;
        $approved = fn ($h) => $h ? (!empty($h->completed_by) || !empty($h->completed_at)) : false;

        // ===== helper: ambil last approval per doc =====
        $getLastApproval = function (string $refnbr) use ($fmt) {
            $q = TrApproval::query()->where('refnbr', $refnbr);

            // 1) cari yang masih pending tapi sudah mulai proses (datebefore not null)
            $pending = (clone $q)
                ->where('status', 'P')
                ->whereNotNull('aprv_datebefore')
                ->orderByDesc('aprv_leveling')
                ->orderByDesc('created_at')
                ->first();

            $row = $pending;

            // 2) kalau tidak ada, ambil yang sudah approve
            if (!$row) {
                $row = (clone $q)
                    ->where('status', 'A')
                    ->orderByDesc('aprv_leveling')
                    ->orderByDesc('created_at')
                    ->first();
            }

            if (!$row) {
                return null;
            }

            return [
                'refnbr' => $row->refnbr,
                'aprv_leveling' => $row->aprv_leveling,
                'status' => $row->status,
                'username' => $row->aprv_username,
                'name' => $row->aprv_name,
                'date_before' => $fmt($row->aprv_datebefore),
                'date_after' => $fmt($row->aprv_dateafter),
                'type' => $row->aprv_type,
                'condition' => $row->aprv_condition,
            ];
        };

        $getApprovalList = function (string $refnbr) use ($fmt) {
            return TrApproval::query()
                ->where('refnbr', $refnbr)
                ->where('status', '<>', 'X')
                ->orderByRaw('CAST(aprv_leveling AS numeric) ASC')
                ->orderBy('id', 'asc')
                ->get()
                ->map(function ($row) use ($fmt) {
                    return [
                        'level' => $row->aprv_leveling,
                        'status' => $row->status, // A / P / R / D
                        'name' => $row->aprv_name,
                        'username' => $row->aprv_username,
                        'date_before' => $fmt($row->aprv_datebefore),
                        'date_after' => $fmt($row->aprv_dateafter),
                    ];
                })
                ->values();
        };
        if ($type === 'cs') {
            $h = TrCS::where('csid', $doc)
                ->where('imnonpurchasejktid', $imnonpurchaseNo)
                ->whereNull('deleted_at')
                ->first();

            $d = $h ? TrCSdetail::where('csid', $doc)
                ->whereNull('deleted_at')
                ->orderBy('id')->get() : collect();

            return response()->json([
                'header' => $h ? [
                    'doc' => $h->csid,
                    'date' => $fmt($h->csdate),
                    'cpny_id' => $h->cpny_id,
                    'department_id' => $h->department_id,
                    'keperluan' => $h->keperluan,
                    'status' => $h->status,
                    'created_by' => $h->created_by,
                    'completed_by' => $h->completed_by,
                    'completed_at' => $fmt($h->completed_at),
                    'is_approved' => $approved($h),

                    // ✅ tambah ini
                    'last_approval' => $getLastApproval($h->csid),
                    'approval_list' => $getApprovalList($h->csid),
                ] : null,
                'details' => $d,
            ]);
        }

        if ($type === 'po') {
            $h = TrPO::where('ponbr', $doc)
                ->where('imnonpurchasejktid', $imnonpurchaseNo)
                ->whereNull('deleted_at')
                ->first();

            $d = $h ? TrPOdetail::where('ponbr', $doc)
                ->where('budget_cpny_id', $h->cpny_id)
                ->whereNull('deleted_at')
                ->orderBy('id')->get() : collect();

            return response()->json([
                'header' => $h ? [
                    'doc' => $h->ponbr,
                    'date' => $fmt($h->podate),
                    'cpny_id' => $h->cpny_id,
                    'department_id' => $h->department_id,
                    'vendorname' => $h->vendorname,
                    'status' => $h->status,
                    'created_by' => $h->created_by,
                    'completed_by' => $h->completed_by,
                    'completed_at' => $fmt($h->completed_at),
                    'is_approved' => $approved($h),

                    // ✅ tambah ini
                    'last_approval' => $getLastApproval($h->ponbr),
                    'approval_list' => $getApprovalList($h->ponbr),
                ] : null,
                'details' => $d,
            ]);
        }

        // receipt
        $h = TrReceipt::where('receiptnbr', $doc)
            ->where('imnonpurchasejktid', $imnonpurchaseNo)
            ->whereNull('deleted_at')
            ->first();

        $d = $h ? TrReceiptdetail::where('receiptnbr', $doc)
            ->whereNull('deleted_at')
            ->orderBy('id')->get() : collect();

        return response()->json([
            'header' => $h ? [
                'doc' => $h->receiptnbr,
                'date' => $fmt($h->receiptdate),
                'cpny_id' => $h->cpny_id,
                'department_id' => $h->department_id,
                'vendorname' => $h->vendorname,
                'status' => $h->status,
                'created_by' => $h->created_by,
                'completed_by' => $h->completed_by,
                'completed_at' => $fmt($h->completed_at),
                'is_approved' => $approved($h),

                // ✅ tambah ini
                'last_approval' => $getLastApproval($h->receiptnbr),
                'approval_list' => $getApprovalList($h->receiptnbr),
            ] : null,
            'details' => $d,
        ]);
    }

    private function getLastApprovalInfo(string $refnbr): ?array
    {
        $refnbr = trim((string) $refnbr);
        if ($refnbr === '') {
            return null;
        }

        // 1) PRIORITY: status P & aprv_datebefore not null
        $row = TrApproval::query()
            ->where('refnbr', $refnbr)
            ->where('status', 'P')
            ->whereNotNull('aprv_datebefore')
            ->orderByDesc('aprv_leveling')
            ->orderByDesc('id')
            ->first();

        // 2) FALLBACK: status A (approved)
        if (!$row) {
            $row = TrApproval::query()
                ->where('refnbr', $refnbr)
                ->where('status', 'A')
                ->orderByDesc('aprv_leveling')
                ->orderByDesc('id')
                ->first();
        }

        if (!$row) {
            return null;
        }

        // Note: field "created_by" kamu ada di fillable, tapi juga ada aprv_username & aprv_name
        return [
            'status' => $row->status,                 // P / A
            'aprv_leveling' => $row->aprv_leveling,
            'username' => $row->aprv_username ?? $row->created_by,
            'name' => $row->aprv_name,
            'date_before' => $row->aprv_datebefore,
            'date_after' => $row->aprv_dateafter,
            'doctype' => $row->aprv_doctype,
            'condition' => $row->aprv_condition,
        ];
    }

    private function getApprovalList(string $refnbr)
    {
        return TrApproval::query()
            ->where('refnbr', $refnbr)
            ->where('status', '<>', 'X')
            ->orderByRaw('CAST(aprv_leveling AS numeric) ASC')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($row) {
                return [
                    'level' => $row->aprv_leveling,
                    'name' => $row->aprv_name,
                    'username' => $row->aprv_username,
                    'status' => $row->status, // P / A / R / D
                    'date_before' => $row->aprv_datebefore,
                    'date_after' => $row->aprv_dateafter,
                ];
            })
            ->values();
    }

    public function tracking_xxx($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $imnonpurchase = TrImbudgetNonPurch::findOrFail($id);

        // Ambil 1 row terbaru dari view untuk imnonpurchase ini
        // Kalau view kamu menghasilkan banyak baris, ambil yang paling "akhir"
        $row = VTrackingIMBudgetNonPurchFlow::query()
            ->where('imnonpurchase_no', $imnonpurchase->imnonpurchaseid)
            ->orderByRaw('cs_date DESC NULLS LAST')
            ->orderByRaw('po_date DESC NULLS LAST')
            ->orderByRaw('receipt_date DESC NULLS LAST')
            ->first();

        // Kalau tidak ketemu di view (harusnya minimal ada IMBudgetNonPurch)
        if (!$row) {
            return response()->json([
                'doc' => $imnonpurchase->imnonpurchaseid,
                'steps' => [[
                    'key' => 'imnonpurchase',
                    'title' => 'IMBudgetNonPurch',
                    'doc' => $imnonpurchase->imnonpurchaseid,
                    'status' => 'C',
                    'status_label' => 'Submitted',
                    'by' => $imnonpurchase->created_by,
                    'at' => optional($imnonpurchase->created_at)->format('Y-m-d H:i'),
                ]],
            ]);
        }

        $fmt = fn ($dt) => $dt ? Carbon::parse($dt)->format('Y-m-d H:i') : null;

        $stepStatus = function (?string $docNo, $isApproved) {
            // belum dibuat
            if (!$docNo) {
                return ['_', 'Not created yet'];
            }
            // sudah dibuat tapi belum complete
            if (!$isApproved) {
                return ['P', 'In progress / waiting approval'];
            }

            // approved/complete
            return ['C', 'Approved / completed'];
        };

        // ====== BUILD STEPS ======
        // IMBudgetNonPurch selalu ada
        [$imnonpurchaseSt, $imnonpurchaseLbl] = $stepStatus($row->imnonpurchase_no, $row->imnonpurchase_is_approved ?? false);
        // Tapi IMBudgetNonPurch "Submitted" lebih jelas sebagai baseline
        $steps = [[
            'key' => 'imnonpurchase',
            'title' => 'IMBudgetNonPurch',
            'doc' => $row->imnonpurchase_no,
            'status' => 'C',
            'status_label' => 'Submitted',
            'by' => $row->imnonpurchase_created_by ?? null,
            'at' => $fmt($row->imnonpurchase_created_at ?? null),
        ]];

        // CS
        [$csSt, $csLbl] = $stepStatus($row->cs_no ?? null, $row->cs_is_approved ?? false);
        $steps[] = [
            'key' => 'cs',
            'title' => 'CS',
            'doc' => $row->cs_no ?? null,
            'status' => $csSt,
            'status_label' => $csLbl,
            'by' => ($row->cs_is_approved ?? false) ? ($row->cs_completed_by ?? null) : null,
            'at' => ($row->cs_is_approved ?? false) ? $fmt($row->cs_completed_at ?? null) : null,
        ];

        // PO
        [$poSt, $poLbl] = $stepStatus($row->po_no ?? null, $row->po_is_approved ?? false);
        $steps[] = [
            'key' => 'po',
            'title' => 'PO',
            'doc' => $row->po_no ?? null,
            'status' => $poSt,
            'status_label' => $poLbl,
            'by' => ($row->po_is_approved ?? false) ? ($row->po_completed_by ?? null) : null,
            'at' => ($row->po_is_approved ?? false) ? $fmt($row->po_completed_at ?? null) : null,
        ];

        // Receipt
        [$rcSt, $rcLbl] = $stepStatus($row->receipt_no ?? null, $row->receipt_is_approved ?? false);
        $steps[] = [
            'key' => 'receipt',
            'title' => 'Receipt',
            'doc' => $row->receipt_no ?? null,
            'status' => $rcSt,
            'status_label' => $rcLbl,
            'by' => ($row->receipt_is_approved ?? false) ? ($row->receipt_completed_by ?? null) : null,
            'at' => ($row->receipt_is_approved ?? false) ? $fmt($row->receipt_completed_at ?? null) : null,
        ];

        return response()->json([
            'doc' => $row->imnonpurchase_no,
            'steps' => $steps,
        ]);
    }

    public function printIMBudgetNonPurch($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Ambil IMBudgetNonPurch + relasi yang dibutuhkan
        $imnonpurchase = TrImbudgetNonPurch::with([
            'requestType:requesttypeid,requesttype_name',
            'creator:username,name',
        ])
            ->findOrFail($id);

        // Detail baris IMBudgetNonPurch
        $imnonpurchasedetail = TrImbudgetNonPurchdetail::with([
            'location:location_id,location_name',
            'subLocation:sub_location_id,sub_location_name',
        ])
            ->where('imnonpurchaseid', $imnonpurchase->imnonpurchaseid)
            ->orderBy('imnonpurchase_no', 'ASC')
            ->get();

        $refnbr = $imnonpurchase->imnonpurchaseid;
        $apprTable = (new TrApproval())->getTable(); // "tr_approval"

        $approval = TrApproval::query()
            ->where('refnbr', $refnbr)
            ->where('status', '<>', 'X')
            ->reorder()
            ->orderBy('created_at', 'asc')
            ->orderBy('aprv_leveling', 'asc')
            ->orderBy('id', 'asc')
            ->get([
                'aprv_leveling',
                'aprv_name',
                'aprv_datebefore',
                'aprv_dateafter',
                'status',
                'aprv_type',
                'aprv_condition',
            ]);
        $approval = TrApproval::query()
            ->where('refnbr', $refnbr)
            ->where('status', '<>', 'X')
            ->reorder()
            ->orderBy('created_at', 'asc')
            ->orderBy('aprv_leveling', 'asc')
            ->orderBy('id', 'asc')
            ->get([
                'aprv_leveling',
                'aprv_name',
                'aprv_datebefore',
                'aprv_dateafter',
                'status',
                'aprv_type',
                'aprv_condition',
            ]);
        // dd($approval);
        $approve_count = $approval->count();

        // Company (handle null)
        $company = MsCompany::where('cpny_id', $imnonpurchase->cpny_id)->first();

        // Mapping status dokumen
        switch ($imnonpurchase->status) {
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
            'title' => 'Surat Permintaan Pembelian Barang',
            'doc_type' => 'IMBudgetNonPurch',
            'docid' => $imnonpurchase->imnonpurchaseid,
            'department_id' => $imnonpurchase->department_id,
            'cpnyname' => optional($company)->cpny_name,
            'parent' => optional($company)->parent,
            'project' => optional($company)->project,
            // identitas & tanggal
            'created_by_username' => $imnonpurchase->created_by,
            'created_by_name' => ucwords(strtolower(optional($imnonpurchase->creator)->name)),
            'created_at_fmt' => optional($imnonpurchase->created_at)->format('d F Y'),
            'req_date_fmt' => optional($imnonpurchase->created_at)->format('d M Y H:i'),
            'imnonpurchasedate' => \Carbon\Carbon::parse($imnonpurchase->imnonpurchasedate)->format('d F Y'),
            // konten
            'keperluan' => $imnonpurchase->keperluan,
            'status_doc' => $status_doc,
            'requesttype_name' => optional($imnonpurchase->requestType)->requesttype_name,
        ];

        // Kirim ke view
        $pdf = \PDF::loadView(
            'pages.imbudgetnonpurch.pdf_imbudgetnonpurch',
            array_merge($data, [
                'detail' => $imnonpurchasedetail,
                'approval' => $approval,
                'approve_count' => $approve_count,
            ])
        );

        // Portrait jika <= 5 approver, else landscape
        $pdf->setPaper('A4', ($approve_count <= 5) ? 'portrait' : 'landscape');

        return $pdf->stream("pdf_imbudgetnonpurch_{$imnonpurchase->imnonpurchaseid}.pdf");
    }

    public function cancelIMBudgetNonPurch(Request $request, string $hash)
    {
        // decode hash -> id (sesuaikan kalau tidak pakai Hashids)
        $decoded = Hashids::decode($hash);
        abort_if(empty($decoded), 404, 'Invalid document');

        $id = $decoded[0];

        // ambil doc
        $imnonpurchase = TrImbudgetNonPurch::query()->where('id', $id)->firstOrFail();

        DB::beginTransaction();
        try {
            // update status header jadi X (Canceled)
            $imnonpurchase->status = 'X';
            $imnonpurchase->updated_by = Auth::user()->username ?? Auth::id(); // kalau kolom ada
            $imnonpurchase->updated_at = now(); // kalau kolom ada
            $imnonpurchase->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Document canceled (status X).',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel document.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function updateSPBQtyIMBudgetNonPurch(string $spbId, ?string $imnonpurchaseId = null, ?string $username = null): void
    {
        $username = $username ?? auth()->user()->username ?? 'system';

        DB::connection('pgsql')->transaction(function () use ($spbId, $imnonpurchaseId, $username) {
            $spb = TrSPB::where('spbid', $spbId)->lockForUpdate()->first();

            if (!$spb) {
                return;
            }

            $detailQuery = TrSPBdetail::where('spbid', $spbId)->lockForUpdate();

            if (!empty($imnonpurchaseId)) {
                $detailQuery->where('imnonpurchaseid', $imnonpurchaseId);
            }

            $details = $detailQuery->get();

            foreach ($details as $detail) {
                $detail->imnonpurchaseid = null;
                $detail->imnonpurchase_qty = 0;
                $detail->base_imnonpurchase_qty = 0;
                $detail->updated_by = $username;
                $detail->updated_at = now();
                $detail->save();
            }

            $totalIMBudgetNonPurchQty = TrSPBdetail::where('spbid', $spbId)->sum('imnonpurchase_qty');

            $spb->imnonpurchaseid = null;
            $spb->status_imnonpurchase = 'Open';
            $spb->totalimnonpurchaseqty = $totalIMBudgetNonPurchQty ?? 0;
            $spb->updated_by = $username;
            $spb->updated_at = now();
            $spb->save();
        });
    }
}
