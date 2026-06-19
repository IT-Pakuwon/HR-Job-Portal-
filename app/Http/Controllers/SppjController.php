<?php

namespace App\Http\Controllers;

use App\Exports\SppjDetailExport;
use App\Http\Controllers\Traits\HasAutonbr;
use App\Imports\BqDetailTempImport;
use App\Models\Autonbr;
use App\Models\Bq;
use App\Models\BqDetail;
use App\Models\BqDetailTemp;
use App\Models\Budget;
use App\Models\BudgetDetail;
use App\Models\BusinessUnit;
use App\Models\MsCompany;
use App\Models\MsKontrakBQ;
use App\Models\MsKontrakCategory;
use App\Models\MsKontrakDocument;
use App\Models\SysUserRole;
use App\Models\TrApproval;
use App\Models\TrAttachment;
use App\Models\TrBast;
use App\Models\TrItrecommend;
use App\Models\TrItrecommendDetail;
use App\Models\TrTicket;
use App\Models\TrTicketActivity;
use App\Models\TrCS;
use App\Models\TrCSdetail;
use App\Models\TrPO;
use App\Models\TrPOdetail;
use App\Models\TrSPPJ;
use App\Models\TrSPPJdetail;
use App\Models\TrWO;
use App\Models\User;
use App\Models\Userbusinessunit;
use App\Models\Usercpny;
use App\Models\Userdept;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Vinkla\Hashids\Facades\Hashids;

class SppjController extends Controller
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

        // department_id juga bisa multi, tapi di debug sudah "IT"
        if (is_string($user->department_id)) {
            $deptIds = array_map('trim', explode(',', $user->department_id));
        } else {
            $deptIds = (array) $user->department_id;
        }

        $all = TrSPPJ::whereIn('cpny_id', $cpnyIds)
                    ->whereIn('department_id', $deptIds)
                    ->count();

        $onProgress = TrSPPJ::where('status', 'P')
                    ->whereIn('cpny_id', $cpnyIds)
                    ->whereIn('department_id', $deptIds)
                    ->count();

        $reject = TrSPPJ::where('status', 'R')
                    ->whereIn('cpny_id', $cpnyIds)
                    ->whereIn('department_id', $deptIds)
                    ->count();

        $revise = TrSPPJ::where('status', 'D')
                    ->whereIn('cpny_id', $cpnyIds)
                    ->whereIn('department_id', $deptIds)
                    ->count();

        $completed = TrSPPJ::where('status', 'C')
                    ->whereIn('cpny_id', $cpnyIds)
                    ->whereIn('department_id', $deptIds)
                    ->count();
        // SPPj All List Count (P & C, all departments)
        $allListCount = TrSPPJ::whereIn('cpny_id', $cpnyIds)
            ->whereIn('status', ['P', 'C'])
            ->count();

        return view('pages.sppjs.sppjs', compact('all', 'onProgress', 'reject', 'revise', 'completed', 'allListCount'));
    }

    public function json(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([], 401);
        }

        // ==============================
        // USER COMPANY
        // ==============================
        if (is_string($user->cpny_id)) {
            $cpnyIds = array_map('trim', explode(',', $user->cpny_id));
        } else {
            $cpnyIds = (array) $user->cpny_id;
        }

        // ==============================
        // USER DEPARTMENT (NORMAL MODE ONLY)
        // ==============================
        if (is_string($user->department_id)) {
            $deptIds = array_map('trim', explode(',', $user->department_id));
        } else {
            $deptIds = (array) $user->department_id;
        }

        // ==============================
        // DATATABLE PARAMETERS
        // ==============================
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));

        $status = (string) $request->query('status', '');
        $mode = (string) $request->query('mode', 'normal');
        $deptExtra = (string) $request->query('department_extra', '');

        $baseTable = (new TrSPPJ())->getTable();

        $columns = [
            0 => 'sppj.sppjid',
            1 => 'sppj.sppjdate',
            2 => 'sppj.cpny_id',
            3 => 'sppj.department_id',
            4 => 'sppj.bqtype',
            5 => 'rt.requesttype_name',
            6 => 'sppj.keperluan',
            7 => 'sppj.status',
        ];

        $orderIdx = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'sppj.sppjid';

        // ==============================
        // BASE QUERY
        // ==============================
        $base = TrSPPJ::from($baseTable.' as sppj')
            ->leftJoin('ms_request_type as rt', function ($join) {
                $join->on('rt.requesttypeid', '=', 'sppj.requesttypeid');
            })
            ->where('rt.doctype', 'SPPJ')
            ->whereIn('sppj.cpny_id', $cpnyIds);

        // ==============================
        // MODE LOGIC
        // ==============================
        if ($mode === 'normal') {
            // restrict by user department
            $base->whereIn('sppj.department_id', $deptIds);

            if ($status !== '') {
                $base->where('sppj.status', $status);
            }
        }

        if ($mode === 'all') {
            // only P & C
            $base->whereIn('sppj.status', ['P', 'C']);

            // department filter from dropdown
            if (!empty($deptExtra)) {
                $base->where('sppj.department_id', $deptExtra);
            }

            // status dropdown override
            if ($status !== '') {
                $base->where('sppj.status', $status);
            }
        }

        // ==============================
        // TOTAL BEFORE SEARCH
        // ==============================
        $recordsTotal = (clone $base)
            ->distinct('sppj.sppjid')
            ->count('sppj.sppjid');

        // ==============================
        // SEARCH FILTER
        // ==============================
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('sppj.sppjid', 'ilike', "%{$search}%")
                ->orWhere('sppj.cpny_id', 'ilike', "%{$search}%")
                ->orWhere('sppj.department_id', 'ilike', "%{$search}%")
                ->orWhere('sppj.bqtype', 'ilike', "%{$search}%")
                ->orWhere('rt.requesttype_name', 'ilike', "%{$search}%")
                ->orWhere('sppj.keperluan', 'ilike', "%{$search}%")
                ->orWhere('sppj.status', 'ilike', "%{$search}%");
            });
        }

        // ==============================
        // TOTAL AFTER SEARCH
        // ==============================
        $recordsFiltered = (clone $base)
            ->distinct('sppj.sppjid')
            ->count('sppj.sppjid');

        // ==============================
        // DATA FETCH
        // ==============================
        $data = $base->select(
            'sppj.id',
            'sppj.sppjid',
            'sppj.sppjdate',
            'sppj.cpny_id',
            'sppj.department_id',
            'sppj.requesttypeid',
            'rt.requesttype_name',
            'sppj.keperluan',
            'sppj.bqtype',
            'sppj.status',
            'sppj.created_by'
        )
                ->orderBy($orderCol, $orderDir)
                ->orderBy('sppj.sppjid', 'desc')
                ->skip($start)
                ->take($length)
                ->get();

        // Encrypt ID
        $data->transform(function ($row) {
            $row->eid = Hashids::encode($row->id);
            unset($row->id);

            return $row;
        });

        // ==============================
        // DEPARTMENT LIST (ONLY FOR ALL MODE)
        // ==============================
        $departments = [];

        if ($mode === 'all') {
            $deptQuery = TrSPPJ::from($baseTable.' as sppj')
                ->whereIn('sppj.cpny_id', $cpnyIds)
                ->whereIn('sppj.status', ['P', 'C']);

            if (!empty($deptExtra)) {
                $deptQuery->where('sppj.department_id', $deptExtra);
            }

            $departments = $deptQuery
                ->select('sppj.department_id')
                ->distinct()
                ->orderBy('sppj.department_id')
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

    public function createSppj()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();
        $userdept = Userdept::where('username', '=', $user->username)
            ->get();
        $userdept2 = Userdept::where('username', '=', $user->username)
            ->first();

        $kontrakDocs = MsKontrakDocument::query()
        ->where('status', 'A')
        ->where('kontrakcategory', 'Kontrak')
        ->orderBy('kontrakdocument_order')
        ->get();

        return view('pages.sppjs.createsppjs', compact('usercpny', 'usercpny2', 'userdept', 'userdept2', 'kontrakDocs'));
    }

    public function storeSppj(Request $request)
    {
        // dd($request->all()); // Debugging: check request data
        // kumpulkan array dari form
        $inventoryIds = $request->input('inventoryid', $request->input('inventory_id', []));
        $productNames = $request->input('product_name', []);
        $qtys = $request->input('qty', []);
        $uoms = $request->input('stock_unit', $request->input('uom', [])); // <- penting
        $notes = $request->input('note', []);
        $locations = $request->input('location', []);
        $locationIds = $request->input('location_id', $request->input('locationid', [])); // <- kalau perlu simpan
        $subLocIds = $request->input('sub_location_id', $request->input('sublocationid', []));
        $subLocations = $request->input('sub_location', []);
        $activityIds = $request->input('activity_id', []);
        $busUnitIds = $request->input('business_unit_id', []);
        $deptFinIds = $request->input('department_fin_id', []);
        $actDescrs = $request->input('activity_descr', []);
        $coaIds = $request->input('coa_id', []); // account_id
        $item_types = $request->input('item_type', []);
        $item_categories = $request->input('item_category', []);

        $purchaseUnits = $request->input('purchase_unit', []);     // dari hidden purchase_unit[]
        $uomMultDivs = $request->input('uom_unitmultdiv', []);   // 'M' atau 'D'
        $uomRates = $request->input('uom_unitrate', []);      // bisa "12", "12,5", "12.000",

        $inventoryCategories = $request->input('item_category', []);      // baris pertama untuk Komputer
        $inventorySubTypes = $request->input('item_sub_type', []); // untuk Fixed Asset subtype

        $doctype = 'PJ';
        $user = $request->user();
        $username = $user->username ?? 'system';
        $fullname = $user->name ?? 'system';

        $dt = Carbon::now();
        $year = (int) $dt->year;
        $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();

        // helper untuk normalisasi angka lokal (ID format)
        $toFloat = function ($v): ?float {
            if ($v === null || $v === '') {
                return null;
            }
            $s = preg_replace('/\s+/', '', (string) $v);

            $hasComma = strpos($s, ',') !== false;
            $hasDot = strpos($s, '.') !== false;

            if ($hasComma && $hasDot) {
                // Decimal = separator yang muncul paling akhir
                $lastComma = strrpos($s, ',');
                $lastDot = strrpos($s, '.');
                if ($lastComma > $lastDot) {
                    // koma = decimal, titik = ribuan
                    $s = str_replace('.', '', $s);
                    $s = str_replace(',', '.', $s);
                } else {
                    // titik = decimal, koma = ribuan
                    $s = str_replace(',', '', $s);
                }
            } elseif ($hasComma) {
                // hanya koma → koma = decimal
                $s = str_replace(',', '.', $s);
            } elseif ($hasDot) {
                // hanya titik → asumsikan titik = decimal
                // kalau ada >1 titik, anggap titik = ribuan → hapus semua titik
                if (substr_count($s, '.') > 1) {
                    $s = str_replace('.', '', $s);
                }
                // kalau 1 titik, biarkan sebagai decimal
            }

            return is_numeric($s) ? (float) $s : null;
        };

        // ===== generate TrApproval dari MsApproval sesuai context =====
        $approvalCtl = app(ApprovalController::class);

        // Pastikan line approval ada (kalau mau validasi awal sebelum simpan detail, panggil loadLines)
        $approvalCtl->loadLines($doctype, $request->cpnyid, $request->departementid);

        DB::beginTransaction();
        try {
            // === generate autonbr & docid (lock) ===
            $auto = $this->nextAutonbr(
                $doctype,
                $year,
                $month,
                $username,
                'SPPJ'
            );
            $urutan = (int) $auto['next'];

            $tglbln = substr((string) $year, 2).$month;   // YYMM
            $docid = $doctype.$tglbln.sprintf('%04d', $urutan);
            $sppjNo = $docid;

            // === 1) header dulu (totalqty sementara 0) ===
            $header = new TrSPPJ();
            $header->sppjid = $docid;                // PK string
            $header->sppjdate = $dt->toDateString();
            $header->cpny_id = $request->input('cpnyid');
            $header->department_id = $request->input('departementid');
            $header->requesttypeid = $request->input('requesttypeid');
            $header->keperluan = $request->input('keperluan');
            $header->budget_perpost = $request->input('perpost');
            $header->woid = $request->input('woid');
            $header->itrecommendid = $request->input('itrecommendid');
            $header->ticketid = $request->input('ticketid');
            $header->is_urgent = $request->input('is_urgent');
            $header->bqtype = $request->input('bqtype');
            $header->bqid = '';
            $header->totalopenordered = 0;
            $header->totalqty = 0;
            $header->totalordered = 0;
            $header->totalrejectordered = 0;
            $header->totalcompleteordered = 0;
            $header->assignby = null;
            $header->assigndate = null;
            $header->assignpurchasing = null;
            $header->csjobs = null;
            $header->cs = null;
            $header->status = 'P';
            $header->created_by = $username;
            $header->save();

            // === 2) detail ===
            $totalQty = 0;
            $totalOpenOrdered = 0;
            $rowCount = max(count($inventoryIds), count($qtys));

            // ===== default site fallback (ambil sekali per header cpny) =====
            $defaultSiteId = null;

            $buSiteCache = [];

            for ($i = 0; $i < $rowCount; ++$i) {
                $invId = $inventoryIds[$i] ?? null;
                $productName = $productNames[$i] ?? null;
                // qty: sudah kamu konversi koma->titik di JS; tetap jaga-jaga:
                $qty = (float) str_replace(',', '.', (string) ($qtys[$i] ?? 0));
                $uom = $uoms[$i] ?? null;

                if (empty($invId) || $qty <= 0) {
                    continue;
                }

                // ==== perhitungan base_* ====
                $baseUom = $purchaseUnits[$i] ?? null;                   // WAJIB: purchase_unit
                $typeMultiplier = strtoupper(trim((string) ($uomMultDivs[$i] ?? ''))); // 'M' / 'D' / ''
                $rateRaw = $uomRates[$i] ?? null;
                $rate = $toFloat($rateRaw) ?? 1.0;                     // default 1 kalau kosong/tidak valid
                if ($rate <= 0) {                                                // guard divide-by-zero & negatif
                    $rate = 1.0;
                    $typeMultiplier = '';                                        // anggap tidak ada konversi
                }

                // base_qty logic
                $baseQty = $qty;
                if ($typeMultiplier === 'M') {
                    $baseQty = $qty * $rate;
                } elseif ($typeMultiplier === 'D') {
                    $baseQty = $qty / $rate;
                }

                // ============================
                // SiteID dari Business Unit
                // ============================
                $buIdRow = trim((string) ($busUnitIds[$i] ?? ''));

                $siteFromBu = null;
                if ($buIdRow !== '') {
                    if (array_key_exists($buIdRow, $buSiteCache)) {
                        $siteFromBu = $buSiteCache[$buIdRow];
                    } else {
                        // query BU
                        $bu = BusinessUnit::query()
                            ->select('ifca_entity_cd', 'solomon_cpny_id')
                            ->where('cpny_id', $request->cpnyid)
                            ->where('business_unit_id', $buIdRow)
                            ->where('status', 'A')
                            ->first();

                        $siteFromBu = null;
                        if ($bu) {
                            $ifca = trim((string) ($bu->ifca_entity_cd ?? ''));
                            $solo = trim((string) ($bu->solomon_cpny_id ?? ''));
                            $siteFromBu = $ifca !== '' ? $ifca : ($solo !== '' ? $solo : null);
                        }

                        // simpan ke cache (boleh null)
                        $buSiteCache[$buIdRow] = $siteFromBu;
                    }
                }

                // final siteid: dari BU kalau ada, kalau tidak fallback ke default site company
                $finalSiteId = $siteFromBu ?: $defaultSiteId;

                // kalau kamu wajib punya siteid:
                if (empty($finalSiteId)) {
                    throw new \Exception("SiteID kosong. BU={$buIdRow} tidak punya ifca_entity_cd/solomon_cpny_id dan default site company tidak ditemukan (cpny={$request->cpnyid}).");
                }

                $detail = new TrSPPJdetail();
                $detail->sppjid = $docid;
                $detail->sppj_no = $i + 1;   // nomor urut detail
                $detail->inventoryid = $invId;
                $detail->inventory_descr = $productName;
                $detail->siteid = $finalSiteId;
                $detail->qty = $qty;
                $detail->uom = $uom;
                $detail->note = $notes[$i] ?? null;
                $detail->inventory_type = $item_types[$i] ?? null;
                $detail->inventory_sub_type = $inventorySubTypes[$i] ?? null;
                $detail->inventory_category = $inventoryCategories[$i] ?? null;
                $detail->base_uom = $baseUom;            // = purchase_unit
                $detail->base_multiplier = $rate;               // = uom_unitrate (float)
                $detail->type_multiplier = $typeMultiplier ?: null; // = 'M' / 'D' / null
                $detail->base_qty = $baseQty;            // hitungan M/D
                $detail->budget_cpny_id = $request->cpnyid;
                $detail->budget_business_unit_id = $busUnitIds[$i] ?? null;
                $detail->budget_department_fin_id = $deptFinIds[$i] ?? null;
                $detail->budget_activity_descr = $actDescrs[$i] ?? null;
                $detail->budget_account_id = $coaIds[$i] ?? null;
                $detail->budget_activity_id = $activityIds[$i] ?? null;
                $detail->location_id = $locationIds[$i] ?? null;
                $detail->sub_location_id = $subLocIds[$i] ?? null;
                $detail->budget_perpost = $request->perpost;
                $detail->assignby = null;
                $detail->assigndate = null;
                $detail->assignpurchasing = null;
                $detail->openordered = $qty;
                $detail->ordered = 0;
                $detail->rejectordered = 0;
                $detail->completeordered = 0;
                $detail->status = 'P';
                $detail->created_by = $username;
                $detail->save();

                $totalQty += $qty;
            }

            // update totalqty di header
            $header->totalqty = $totalQty;
            $header->totalopenordered = $totalQty;
            $header->save();

            // // === 4) copy line approval (M_approval -> T_approval) ===

            // 1) Urgent → dari header field is_urgent (boolean atau "1"/"true")
            $isUrgent = (bool) $request->input('is_urgent', false);

            // 2) Komputer → hanya kategori pada BARIS PERTAMA yang non-empty
            $firstCategory = null;
            if (!empty($inventoryCategories)) {
                foreach ($inventoryCategories as $c) {
                    if (!empty($c)) {
                        $firstCategory = $c;
                        break;
                    }
                }
            }

            // 3) Fixed Asset → minimal ada SATU detail dengan inventory_sub_type = Fixed Asset / FA
            $hasFixedAssetSubtype = false;
            foreach ((array) $inventorySubTypes as $sub) {
                $s = mb_strtolower((string) $sub);
                if ($s === 'fixed asset' || $s === 'fa') {
                    $hasFixedAssetSubtype = true;
                    break;
                }
            }

            // 4) Build context untuk ApprovalController
            $ctx = [
                'is_urgent' => $isUrgent,
                'first_inventory_category' => $firstCategory,
                'has_fixed_asset_subtype' => $hasFixedAssetSubtype,
                'ignore_nominal' => true,   // SPPJ diminta tidak cek nominal
                // 'grand_total'           => ...     // tidak dipakai di SPPJ
            ];

            // Generate TrApproval
            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $docid,
                $doctype,
                $request->cpnyid,
                $request->departementid,
                $username,
                $ctx,
                $dt
            );

            // (opsional) simpan hint approver pertama di header seperti sebelumnya
            if ($firstApprovalUsernames) {
                $header->completed_by = $firstApprovalUsernames;
                $header->completed_at = $dt;
                $header->save();
            }

            // === 5) attachments (opsional) ===

            //    if ($request->hasFile('attachments')) {
            //         $meta = [
            //             'refnbr'        => $docid,
            //             'doctype'       => $doctype,
            //             'cpnyid'        => $request->input('cpnyid'),
            //             'departementid' => $request->input('departementid'),
            //             'base_folder'   => 'att-purchasing-app/'.strtolower($doctype),
            //             'created_by'    => $user->username,
            //         ];

            //         $files = (array) $request->file('attachments');

            //         try {
            //             $uploader = app(TrAttachmentController::class);
            //             $uploadResult = $uploader->uploadInternal($meta, $files);
            //             // tidak return di sini!
            //         } catch (\Throwable $e) {
            //             \DB::rollBack();
            //             return response()->json([
            //                 'message' => 'Failed to create PJ',
            //                 'error'   => 'Gagal upload attachment: '.$e->getMessage(),
            //             ], 500);
            //         }
            //     } else {
            //         $uploadResult = null; // tidak ada attachment
            //     }

            // === 5) attachments ===
            $uploadResult = null;

            $bqtype = trim((string) $request->input('bqtype'));

            if ($bqtype === 'Kontrak') {
                $kontrakFiles = (array) $request->file('kontrak_attachments', []);

                $docs = MsKontrakDocument::query()
                    ->where('status', 'A')
                    ->where('kontrakcategory', 'Kontrak')
                    ->orderBy('kontrakdocument_order')
                    ->get(['kontrakdocument_id', 'kontrakdocument_descr', 'kontrakdocument_required']);

                $docMap = $docs->pluck('kontrakdocument_descr', 'kontrakdocument_id')->toArray();

                $filesWithDoc = [];
                foreach ($kontrakFiles as $docId => $file) {
                    if ($file instanceof \Illuminate\Http\UploadedFile) {
                        $filesWithDoc[] = [
                            'kontrakdocument_id' => (string) $docId,  // ✅ FIX KEY
                            'file' => $file,
                        ];
                    }
                }

                if (!empty($filesWithDoc)) {
                    $meta = [
                        'refnbr' => $docid,
                        'doctype' => $doctype,
                        'cpny_id' => $request->input('cpnyid'),         // ✅ FIX META KEY
                        'department_id' => $request->input('departementid'),  // ✅ FIX META KEY
                        'base_folder' => 'att-purchasing-app/'.strtolower($doctype),
                        'created_by' => $user->username,

                        'kontrak_doc_map' => $docMap,
                    ];

                    $uploader = app(TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternalKontrak($meta, $filesWithDoc);
                }
            } else {
                // Jasa (default)
                if ($request->hasFile('attachments')) {
                    $meta = [
                        'refnbr' => $docid,
                        'doctype' => $doctype,
                        'cpnyid' => $request->input('cpnyid'),
                        'departementid' => $request->input('departementid'),
                        'base_folder' => 'att-purchasing-app/'.strtolower($doctype),
                        'created_by' => $user->username,
                    ];

                    $files = (array) $request->file('attachments');

                    try {
                        $uploader = app(TrAttachmentController::class);
                        $uploadResult = $uploader->uploadInternal($meta, $files);
                    } catch (\Throwable $e) {
                        \DB::rollBack();

                        return response()->json([
                            'message' => 'Failed to create PJ',
                            'error' => 'Gagal upload attachment jasa: '.$e->getMessage(),
                        ], 500);
                    }
                }
            }

            // Auto-attach PDF ITR jika ada
            if (!empty($header->itrecommendid)) {
                try {
                    $this->attachItrPdf(
                        $header->itrecommendid,
                        $docid,
                        $request->input('cpnyid'),
                        $request->input('departementid'),
                        $username
                    );
                } catch (\Throwable $e) {
                    \Log::warning('Auto-attach ITR PDF gagal (storeSppj)', [
                        'itrId' => $header->itrecommendid,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Auto-attach PDF Ticket jika ada
            if (!empty($header->ticketid)) {
                try {
                    $this->attachTicketPdf(
                        $header->ticketid,
                        $docid,
                        $request->input('cpnyid'),
                        $request->input('departementid'),
                        $username
                    );
                } catch (\Throwable $e) {
                    \Log::warning('Auto-attach Ticket PDF gagal (storeSppj)', [
                        'ticketId' => $header->ticketid,
                        'error'    => $e->getMessage(),
                    ]);
                }
            }

            $eid = Hashids::encode($header->id);

            $approvalCtl->notifyFirstApprover(
                $docid,
                $doctype,
                $header->status,                 // 'P' | 'R' | 'D' | 'A' | 'C'
                'SPPJ',
                url('/showsppjs/'.$eid),
                [
                    'info' => $request->keperluan,
                    'createdby' => $header->created_by,
                    'date' => $dt->toDateTimeString(),
                ]
            );

            // // === 6) kirim email ke approver pertama ===
            // $firstApproval = T_approval::where('docid', $docid)
            //     ->where('status', 'P')
            //     ->orderBy('aprvid')
            //     ->first();

            // if ($firstApproval) {

            //     $status = $header->status; // 'P' | 'R' | 'D' | 'A' | 'C'

            //     $subjectMap = [
            //         'P' => 'Waiting Approval',
            //         'R' => 'Rejected Approval',
            //         'D' => 'Revise Approval',
            //         'A' => 'Approved',
            //         'C' => 'Completed',
            //     ];
            //     $subjectSuffix = $subjectMap[$status] ?? 'Notification';

            //     $eid = Hashids::encode($header->id);

            //     $data = [
            //         'docid'    => $firstApproval->docid,
            //         'cpnyid'   => $firstApproval->aprvcpnyid,
            //         'deptname' => $firstApproval->aprvdeptid,
            //         'date'     => $firstApproval->aprvdatebefore,
            //         'name'     => $firstApproval->name,
            //         'createdby'=> $header->created_by,
            //         'info'     => $request->keperluan,
            //         'status'   => $status,
            //         'docname'  => 'SPPJ',
            //         'url'      => url('/showsppjs/' . $eid),
            //     ];

            //     $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
            //     $emails = User::whereIn('username', $approvers)
            //         ->where('status', 'A')
            //         ->pluck('notification_email');

            //     foreach ($emails as $email) {
            //         \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data) {
            //             $message->to($email)
            //                 ->subject($data['docid'].' - Waiting Approval SPPJ')
            //                 ->from('digitalserver@pakuwon.com', 'Pakuwon System');
            //         });
            //     }
            // }

            DB::commit();

            return response()->json([
                'message' => 'SPPJ created successfully',
                'sppjid' => $docid,
                'sppj_no' => $sppjNo,
                'totalqty' => $totalQty,
                'attachments' => $uploadResult,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'message' => 'Failed to create SPPJ',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function editSppj($hash)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $sppj = TrSPPJ::findOrFail($id);

        // Ambil detail + eager load relasi lokasi & sublokasi
        $sppjdetail = TrSPPJdetail::with([
            'location:location_id,location_name',
            'subLocation:sub_location_id,sub_location_name',
        ])
            ->where('sppjid', $sppj->sppjid)
            ->get()
            ->map(function ($d) {
                // Sematkan nama ke attribute agar Blade lama tetap jalan
                $d->location_name = optional($d->location)->location_name;
                $d->sub_location_name = optional($d->subLocation)->sub_location_name;

                return $d;
            });

        $detailBuIds = $sppjdetail
            ->pluck('budget_business_unit_id')
            ->filter(fn ($v) => !blank($v))
            ->unique()
            ->values();

        $selectedBuId = $detailBuIds->first();

        $selectedBuName = null;
        if ($selectedBuId) {
            $bu = BusinessUnit::query()
                ->where('business_unit_id', $selectedBuId)
                ->first();

            $selectedBuName = $bu->business_unit_name ?? null;
        }

        // Inject ke object $sppj supaya Blade existing tetap jalan
        $sppj->business_unit_id = $selectedBuId;
        $sppj->business_unit_name = $selectedBuName;

        // Optional: log kalau ternyata 1 SPPJ punya lebih dari 1 BU di detail
        if ($detailBuIds->count() > 1) {
            \Log::warning('SPPJ memiliki lebih dari satu budget_business_unit_id pada detail', [
                'sppjid' => $sppj->sppjid,
                'budget_business_unit_ids' => $detailBuIds->toArray(),
            ]);
        }

        $user = request()->user();
        $usercpny = Usercpny::where('username', $user->username)->get();
        $usercpny2 = Usercpny::where('username', $user->username)->first();
        $userdept = Userdept::where('username', $user->username)->get();
        $userdept2 = Userdept::where('username', $user->username)->first();

        // $attachment = Attachment::where('docid', $sppj->sppjid)
        //     ->where('status', 'A')
        //     ->get();

        $rows = TrAttachment::where('refnbr', $sppj->sppjid)
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

        return view('pages.sppjs.editsppjs', compact(
            'sppj', 'sppjdetail', 'usercpny', 'usercpny2', 'userdept', 'userdept2', 'attachments', 'hash'
        ));
    }

    public function updateSppj(Request $request, $hash)
    {
        // dd($request->all()); // matikan agar eksekusi lanjut

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404, 'PJ tidak ditemukan.');

        $user = $request->user();
        $dt = Carbon::now();
        $year = (int) $dt->year;
        $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();
        $doctype = 'PJ';
        $username = $user->username ?? 'system';
        $fullname = $user->name ?? 'system';

        // ===== generate TrApproval dari MsApproval sesuai context =====
        $approvalCtl = app(ApprovalController::class);

        // Pastikan line approval ada (kalau mau validasi awal sebelum simpan detail, panggil loadLines)
        $approvalCtl->loadLines($doctype, $request->cpnyid, $request->departementid);

        // helper: normalisasi angka (tahan "12.000", "1.234,56", "12,5")
        $toFloat = function ($v): ?float {
            if ($v === null || $v === '') {
                return null;
            }
            $s = preg_replace('/\s+/', '', (string) $v);
            $hasComma = strpos($s, ',') !== false;
            $hasDot = strpos($s, '.') !== false;

            if ($hasComma && $hasDot) {
                $lastComma = strrpos($s, ',');
                $lastDot = strrpos($s, '.');
                if ($lastComma > $lastDot) {
                    // koma = decimal, titik = ribuan
                    $s = str_replace('.', '', $s);
                    $s = str_replace(',', '.', $s);
                } else {
                    // titik = decimal, koma = ribuan
                    $s = str_replace(',', '', $s);
                }
            } elseif ($hasComma) {
                $s = str_replace(',', '.', $s);
            } elseif ($hasDot) {
                if (substr_count($s, '.') > 1) {
                    $s = str_replace('.', '', $s);
                }
            }

            return is_numeric($s) ? (float) $s : null;
        };

        $header = TrSPPJ::findOrFail($id);

        $oldItrId    = $header->itrecommendid;
        $oldTicketId = $header->ticketid;

        // update header
        $header->cpny_id = $request->cpnyid;
        $header->department_id = $request->departementid;
        $header->requesttypeid = $request->requesttypeid;
        $header->keperluan = $request->keperluan;
        $header->budget_perpost = $request->perpost;
        $header->bqtype = $request->bqtype;
        $header->woid = $request->woid;
        $header->itrecommendid = $request->itrecommendid ?: null;
        $header->ticketid = $request->ticketid ?: null;
        $header->is_urgent = $request->is_urgent;
        $header->status = 'P';
        $header->updated_by = $username;
        $header->save();

        // arrays utama
        $detailIds = array_values($request->input('detail_id', []));
        $inventoryIds = array_values($request->input('inventoryid', []));
        $productNames = array_values($request->input('product_name', []));
        $qtys = array_values($request->input('qty', []));
        $uoms = array_values($request->input('stock_unit', []));
        $notes = array_values($request->input('note', []));
        $locIds = array_values($request->input('location_id', []));
        $subLocIds = array_values($request->input('sub_location_id', []));
        $actIds = array_values($request->input('activity_id', []));
        $buIds = array_values($request->input('business_unit_id', []));
        $deptFinIds = array_values($request->input('department_fin_id', []));
        $actDescrs = array_values($request->input('activity_descr', []));
        $coaIds = array_values($request->input('coa_id', []));
        $itemTypes = array_values($request->input('item_type', []));
        $itemCats = array_values($request->input('item_category', []));

        // arrays UoM tambahan
        $purchaseUnits = array_values($request->input('purchase_unit', []));      // hidden dari UI
        $uomMultDivs = array_values($request->input('uom_unitmultdiv', []));    // 'M'/'D'
        $uomRates = array_values($request->input('uom_unitrate', []));       // bisa "12.000"

        $inventorySubTypes = $request->input('item_sub_type', []);

        DB::beginTransaction();

        try {
            // hapus baris yang di-mark delete
            if ($request->filled('deleted_detail_ids')) {
                $idsToDelete = array_filter(array_map('trim', explode(',', $request->deleted_detail_ids)));
                if ($idsToDelete) {
                    TrSPPJdetail::whereIn('id', $idsToDelete)->delete();
                }
            }

            $rowCount = max(count($inventoryIds), count($qtys));
            $savedDetails = [];
            $buSiteCache = [];

            for ($i = 0; $i < $rowCount; ++$i) {
                $invId = $inventoryIds[$i] ?? null;
                $qty = (float) str_replace(',', '.', (string) ($qtys[$i] ?? 0));
                if (empty($invId) || $qty <= 0) {
                    continue;
                }

                // === konversi base_* seperti di store ===
                $displayUom = $uoms[$i] ?? null;
                $baseUom = $purchaseUnits[$i] ?? null;                        // purchase_unit
                $typeMultiplier = strtoupper(trim((string) ($uomMultDivs[$i] ?? ''))); // 'M'/'D'
                $rate = $toFloat($uomRates[$i] ?? null) ?? 1.0;             // 12.000 -> 12.0
                if ($rate <= 0) {
                    $rate = 1.0;
                    $typeMultiplier = '';
                }

                $baseQty = $qty;
                if ($typeMultiplier === 'M') {
                    $baseQty = $qty * $rate;
                } elseif ($typeMultiplier === 'D') {
                    $baseQty = $qty / $rate;
                }

                // ============================
                // SiteID dari Business Unit
                // ============================
                $buIdRow = trim((string) ($buIds[$i] ?? ''));

                $siteFromBu = null;
                if ($buIdRow !== '') {
                    if (array_key_exists($buIdRow, $buSiteCache)) {
                        $siteFromBu = $buSiteCache[$buIdRow];
                    } else {
                        // query BU
                        $bu = BusinessUnit::query()
                            ->select('ifca_entity_cd', 'solomon_cpny_id')
                            ->where('cpny_id', $request->cpnyid)
                            ->where('business_unit_id', $buIdRow)
                            ->where('status', 'A')
                            ->first();

                        $siteFromBu = null;
                        if ($bu) {
                            $ifca = trim((string) ($bu->ifca_entity_cd ?? ''));
                            $solo = trim((string) ($bu->solomon_cpny_id ?? ''));
                            $siteFromBu = $ifca !== '' ? $ifca : ($solo !== '' ? $solo : null);
                        }

                        // simpan ke cache (boleh null)
                        $buSiteCache[$buIdRow] = $siteFromBu;
                    }
                }

                // final siteid: dari BU kalau ada, kalau tidak fallback ke default site company
                $finalSiteId = $siteFromBu ?: $defaultSiteId;

                // kalau kamu wajib punya siteid:
                if (empty($finalSiteId)) {
                    throw new \Exception("SiteID kosong. BU={$buIdRow} tidak punya ifca_entity_cd/solomon_cpny_id dan default site company tidak ditemukan (cpny={$request->cpnyid}).");
                }

                $data = [
                    'inventoryid' => $invId,
                    'inventory_descr' => $productNames[$i] ?? null,
                    'qty' => $qty,
                    'uom' => $displayUom,
                    'siteid' => $finalSiteId,
                    'note' => $notes[$i] ?? null,
                    'inventory_type' => $itemTypes[$i] ?? null,
                    'inventory_sub_type' => $inventorySubTypes[$i] ?? null,
                    'inventory_category' => $itemCats[$i] ?? null,

                    // >>> ini yang ditambahkan <<<
                    'base_uom' => $baseUom,                       // purchase_unit
                    'base_multiplier' => $rate,                          // uom_unitrate (float)
                    'type_multiplier' => $typeMultiplier ?: null,        // 'M'/'D'/null
                    'base_qty' => $baseQty,                        // hasil M/D

                    'budget_cpny_id' => $request->cpnyid,
                    'budget_business_unit_id' => $buIds[$i] ?? null,
                    'budget_department_fin_id' => $deptFinIds[$i] ?? null,
                    'budget_activity_descr' => $actDescrs[$i] ?? null,
                    'budget_account_id' => $coaIds[$i] ?? null,
                    'budget_activity_id' => $actIds[$i] ?? null,
                    'openordered' => $qty,
                    'ordered' => 0,
                    'location_id' => $locIds[$i] ?? null,
                    'sub_location_id' => $subLocIds[$i] ?? null,
                    'budget_perpost' => $request->perpost,
                    'status' => 'P',
                    'updated_by' => $username,
                ];

                $idDetail = $detailIds[$i] ?? null;

                if ($idDetail) {
                    $detail = TrSPPJdetail::where('id', $idDetail)
                        ->where('sppjid', $header->sppjid)
                        ->first();
                    if ($detail) {
                        $detail->fill($data)->save();
                    } else {
                        $detail = new TrSPPJdetail($data);
                        $detail->sppjid = $header->sppjid;
                        $detail->save();
                    }
                } else {
                    $detail = new TrSPPJdetail($data);
                    $detail->sppjid = $header->sppjid;
                    $detail->save();
                }

                $savedDetails[] = $detail->id;
            }

            // Renumber sppj_no 1..N
            $n = 1;
            foreach ($savedDetails as $did) {
                TrSPPJdetail::where('id', $did)->update(['sppj_no' => $n++]);
            }

            // Hitung total qty (kalau mau pakai base_qty, ganti ke sum('base_qty'))
            $totalQty = TrSPPJdetail::where('sppjid', $header->sppjid)->sum('qty');
            $header->totalqty = $totalQty;
            $header->totalopenordered = $totalQty;
            $header->save();

            // // === regenerasi T_approval (opsional, ikuti logikamu) ===
            // $approvals = M_approval::where([
            //     ['status', '=', 'A'],
            //     ['aprvcpnyid', '=', $request->cpnyid],
            //     ['aprvdeptid', '=', $request->departementid],
            //     ['aprvdoctype', '=', $doctype],
            // ])->get();

            // foreach ($approvals as $a) {
            //     T_approval::create([
            //         'docid'          => $header->sppjid,
            //         'aprvid'         => $a->aprvid,
            //         'aprvdoctype'    => $a->aprvdoctype,
            //         'aprvcpnyid'     => $a->aprvcpnyid,
            //         'aprvdeptid'     => $a->aprvdeptid,
            //         'aprvusername'   => $a->aprvusername,
            //         'name'           => $a->name,
            //         'aprvdatebefore' => $a->aprvid == 1 ? $datestamp : null,
            //         'aprvtotalday'   => 1,
            //         'status'         => 'P',
            //         'created_user'   => $username,
            //     ]);
            // }

            // $firstApprovalUsernames = optional($approvals->first())->aprvusername;
            // if ($firstApprovalUsernames) {
            //     $header->completed_by = $firstApprovalUsernames;
            //     $header->completed_at = $dt;
            //     $header->save();
            // }

            // 1) Urgent → dari header field is_urgent (boolean atau "1"/"true")
            $isUrgent = (bool) $request->input('is_urgent', false);

            // 2) Komputer → hanya kategori pada BARIS PERTAMA yang non-empty
            $firstCategory = null;
            if (!empty($inventoryCategories)) {
                foreach ($inventoryCategories as $c) {
                    if (!empty($c)) {
                        $firstCategory = $c;
                        break;
                    }
                }
            }

            // 3) Fixed Asset → minimal ada SATU detail dengan inventory_sub_type = Fixed Asset / FA
            $hasFixedAssetSubtype = false;
            foreach ((array) $inventorySubTypes as $sub) {
                $s = mb_strtolower((string) $sub);
                if ($s === 'fixed asset' || $s === 'fa') {
                    $hasFixedAssetSubtype = true;
                    break;
                }
            }

            // 4) Build context untuk ApprovalController
            $ctx = [
                'is_urgent' => $isUrgent,
                'first_inventory_category' => $firstCategory,
                'has_fixed_asset_subtype' => $hasFixedAssetSubtype,
                'ignore_nominal' => true,   // SPPJ diminta tidak cek nominal
                // 'grand_total'           => ...     // tidak dipakai di SPPJ
            ];

            // Generate TrApproval
            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $header->sppjid,
                $doctype,
                $request->cpnyid,
                $request->departementid,
                $username,
                $ctx,
                $dt
            );

            // (opsional) simpan hint approver pertama di header seperti sebelumnya
            if ($firstApprovalUsernames) {
                $header->completed_by = $firstApprovalUsernames;
                $header->completed_at = $dt;
                $header->save();
            }

            // attachments (tetap)
            // if ($request->hasfile('attachments')) {
            //     foreach ($request->file('attachments') as $file) {
            //         $randomNumber = random_int(10000000, 99999999);
            //         $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            //         $originalName = str_replace('%', '', $file->getClientOriginalName());
            //         $ext        = $file->getClientOriginalExtension();
            //         $attachfile = md5($randomNumber) . '.' . $ext;

            //         //attach to folder
            //         $folder_attach = public_path() . '/attachments/'.$year;
            //         $config['upload_path'] = $folder_attach;
            //         if(!is_dir($folder_attach))
            //         {
            //             mkdir($folder_attach, 0777);
            //         }

            //         $folder_upload = $folder_attach;
            //         // $folder_upload = public_path() . '/attachments';
            //         $file->move($folder_upload, $attachfile);

            //         //insert to table attachments
            //         $attach = new Attachment();
            //         $attach->docid = $header->sppjid;
            //         $attach->name = $filename;
            //         $attach->attachfile = $attachfile;
            //         $attach->status = 'A';
            //         $attach->extention = $file->getClientOriginalExtension();
            //         $attach->created_user = $user->username;
            //         $attach->save();
            //     }
            // }

            $uploadResult = null;
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr' => $header->sppjid,
                    'doctype' => $doctype,
                    'cpnyid' => $request->cpnyid,
                    'departementid' => $request->departementid,
                    'base_folder' => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by' => $user->username,
                ];
                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    DB::rollBack();

                    return response()->json([
                        'message' => 'Failed to update PJ',
                        'error' => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            }

            // Auto-attach PDF ITR: hapus lama, pasang baru jika ID berubah
            $newItrId    = $header->itrecommendid;
            $newTicketId = $header->ticketid;

            if ($newItrId !== $oldItrId) {
                if (!empty($oldItrId)) {
                    TrAttachment::where('refnbr', $header->sppjid)
                        ->where('attachment_name', 'IT-RECOMMENDATION-'.$oldItrId)
                        ->where('status', 'A')
                        ->update(['status' => 'X', 'updated_by' => $username]);
                }
                if (!empty($newItrId)) {
                    try {
                        $this->attachItrPdf(
                            $newItrId,
                            $header->sppjid,
                            $request->cpnyid,
                            $request->departementid,
                            $username
                        );
                    } catch (\Throwable $e) {
                        \Log::warning('Auto-attach ITR PDF gagal (updateSppj)', [
                            'itrId' => $newItrId,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Auto-attach PDF Ticket: hapus lama, pasang baru jika ID berubah
            if ($newTicketId !== $oldTicketId) {
                if (!empty($oldTicketId)) {
                    TrAttachment::where('refnbr', $header->sppjid)
                        ->where('attachment_name', 'TICKET-'.$oldTicketId)
                        ->where('status', 'A')
                        ->update(['status' => 'X', 'updated_by' => $username]);
                }
                if (!empty($newTicketId)) {
                    try {
                        $this->attachTicketPdf(
                            $newTicketId,
                            $header->sppjid,
                            $request->cpnyid,
                            $request->departementid,
                            $username
                        );
                    } catch (\Throwable $e) {
                        \Log::warning('Auto-attach Ticket PDF gagal (updateSppj)', [
                            'ticketId' => $newTicketId,
                            'error'    => $e->getMessage(),
                        ]);
                    }
                }
            }

            // // email approver pertama (tetap)
            // $firstApproval = T_approval::where('docid', $header->sppjid)
            //     ->where('status', 'P')
            //     ->orderBy('aprvid')
            //     ->first();

            // if ($firstApproval) {
            //     $status = $header->status; // 'P' | 'R' | 'D' | 'A' | 'C'

            //     $subjectMap = [
            //         'P' => 'Waiting Approval',
            //         'R' => 'Rejected Approval',
            //         'D' => 'Revise Approval',
            //         'A' => 'Approved',
            //         'C' => 'Completed',
            //     ];
            //     $subjectSuffix = $subjectMap[$status] ?? 'Notification';

            //     $eid = Hashids::encode($header->id);

            //     $data = [
            //         'docid'    => $firstApproval->docid,
            //         'cpnyid'   => $firstApproval->aprvcpnyid,
            //         'deptname' => $firstApproval->aprvdeptid,
            //         'date'     => $firstApproval->aprvdatebefore,
            //         'name'     => $firstApproval->name,
            //         'createdby'=> $header->created_by,
            //         'info'     => $request->keperluan,
            //         'status'   => $status,
            //         'docname'  => 'SPPJ',
            //         'url'      => url('/showsppjs/' . $eid),
            //     ];

            //     $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
            //     $emails = User::whereIn('username', $approvers)
            //         ->where('status', 'A')
            //         ->pluck('notification_email');

            //     foreach ($emails as $email) {
            //         \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data) {
            //             $message->to($email)
            //                 ->subject($data['docid'].' - Waiting Approval SPPJ')
            //                 ->from('digitalserver@pakuwon.com', 'Pakuwon System');
            //         });
            //     }
            // }

            $eid = Hashids::encode($header->id);

            $approvalCtl->notifyFirstApprover(
                $header->sppjid,
                $doctype,
                $header->status,                 // 'P' | 'R' | 'D' | 'A' | 'C'
                'SPPJ',
                url('/showsppjs/'.$eid),
                [
                    'info' => $request->keperluan,
                    'createdby' => $header->created_by,
                    'date' => $dt->toDateTimeString(),
                ]
            );

            DB::commit();

            return response()->json(['message' => 'SPPJ updated successfully']);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json(['message' => 'Update failed', 'error' => $e->getMessage()], 500);
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

    public function showSppj($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // $sppj = TrSPPJ::findOrFail($id);
        $sppj = TrSPPJ::with([
            'requestType:requesttypeid,requesttype_name',
            'creator:username,name',
        ])
        ->findOrFail($id);

        $sppjdetail = TrSPPJdetail::with([
            'location:location_id,location_name',
            'subLocation:sub_location_id,sub_location_name',
        ])
        ->where('sppjid', $sppj->sppjid)
        ->orderby('sppj_no', 'ASC')
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

        foreach ($sppjdetail as $item) {
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

        $attachmentPJ = $this->mapAttachmentsToSignedUrl($sppj->sppjid);

        $attachmentWO = collect();
        if (!empty($sppj->woid)) {
            $attachmentWO = $this->mapAttachmentsToSignedUrl($sppj->woid);
        }

        $bq = Bq::where('bqid', $sppj->bqid)
            ->first();

        if ($bq) {
            $bq->eid = Hashids::encode($bq->id);
        }

        $loginUsername = $user->username ?? $user->name ?? null;
        $canUpload = $sppj->created_by === $loginUsername;
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

        $woData = null;
        $woHash = null;

        if (!empty($sppj->woid)) {
            $woData = TrWO::select('id', 'woid', 'keperluan')
                ->where('woid', $sppj->woid)
                ->first();

            if ($woData) {
                $woHash = Hashids::encode($woData->id);
            }
        }

        $itrData = null;
        $itrHash = null;

        if (!empty($sppj->itrecommendid)) {
            $itrData = TrItrecommend::query()
                ->select('id', 'docid')
                ->where('docid', $sppj->itrecommendid)
                ->first();

            if ($itrData) {
                $itrHash = Hashids::encode($itrData->id);
            }
        }

        $ticketData = null;
        $ticketHash = null;

        if (!empty($sppj->ticketid)) {
            $ticketData = TrTicket::query()
                ->select('id', 'ticketid')
                ->where('ticketid', $sppj->ticketid)
                ->first();

            if ($ticketData) {
                $ticketHash = Hashids::encode($ticketData->id);
            }
        }

        return view('pages.sppjs.showsppjs', compact(
            'sppj',
            'attachmentPJ',
            'attachmentWO',
            'sppjdetail',
            'bq',
            'hash',
            'canUpload',
            'akses_cc',
            'userCpny',
            'userBu',
            'userDeptFin',
            'woData',
            'woHash',
            'itrData',
            'itrHash',
            'ticketData',
            'ticketHash'
        ));
    }

    public function exportDetail($id)
    {
        $sppj = TrSPPJ::findOrFail($id);

        $sppjdetail = TrSPPJdetail::where('sppjid', $sppj->sppjid)
            ->with(['location', 'subLocation'])
            ->orderBy('sppj_no', 'ASC')
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

        foreach ($sppjdetail as $item) {
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
            new SppjDetailExport($sppjdetail),
            'SPPJ_Detail_'.$sppj->sppjid.'.xlsx'
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

    public function approveSppj(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'PJ';

        $sppj = TrSPPJ::with('creator')->where('sppjid', $docid)->first();
        if (!$sppj) {
            return response()->json(['success' => false, 'message' => 'SPPJ not found'], 404);
        }

        $eid = Hashids::encode($sppj->id);
        $docUrl = url('/showsppjs/'.$eid);
        $fullname = data_get($sppj, 'creator.name') ?: $sppj->created_by;

        $result = app(ApprovalController::class)->approveStep(
            $sppj->sppjid,
            $doctype,
            $user->username,
            $user->name,

            // complete: update header/detail + email creator complete
            function (string $refnbr, \Carbon\Carbon $now) use ($sppj, $fullname, $docUrl) {
                $sppj->status = 'C';
                $sppj->completed_by = $sppj->completed_by ?: auth()->user()->username;
                $sppj->completed_at = $now;
                $sppj->save();

                TrSPPJdetail::where('sppjid', $sppj->sppjid)->update(['status' => 'C']);

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $sppj->sppjid,
                    'SPPJ',
                    'C',
                    $sppj->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $sppj->cpny_id ?? $sppj->cpnyid ?? '',
                        'deptname' => $sppj->department_id ?? $sppj->departementid ?? '',
                        'date' => $sppj->sppjdate,
                        'info' => $sppj->keperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                    ]
                );
            },

            // notify next approver
            function ($next, \Carbon\Carbon $now) use ($sppj, $docUrl) {
                app(ApprovalController::class)->notifyFirstApprover(
                    $sppj->sppjid,
                    'PJ',
                    'P',
                    'SPPJ',
                    $docUrl,
                    [
                        'info' => $sppj->keperluan,
                        'createdby' => $sppj->created_by,
                        'date' => $now->toDateTimeString(),
                    ]
                );

                // jejak terakhir diproses (optional)
                $sppj->completed_by = auth()->user()->username;
                $sppj->completed_at = $now;
                $sppj->save();
            }
        );

        if (!$result['ok']) {
            return response()->json(['success' => false, 'message' => $result['message'] ?? 'Approve failed'], 403);
        }

        return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    }

    public function rejectSppj(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'PJ';

        $sppj = TrSPPJ::with('creator')->where('sppjid', $docid)->first();
        if (!$sppj) {
            return response()->json(['success' => false, 'message' => 'SPPJ not found'], 404);
        }

        $eid = Hashids::encode($sppj->id);
        $docUrl = url('/showsppjs/'.$eid);
        $fullname = data_get($sppj, 'creator.name') ?: $sppj->created_by;

        $result = app(ApprovalController::class)->rejectStep(
            $sppj->sppjid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($sppj, $fullname, $docUrl) {
                $sppj->status = 'R';
                $sppj->completed_by = auth()->user()->username;
                $sppj->completed_at = $now;
                $sppj->save();

                // optional: tandai detail R
                // \App\Models\TrSPPJdetail::where('sppjid', $sppj->sppjid)->update(['status' => 'R']);

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $sppj->sppjid,
                    'SPPJ',
                    'R',
                    $sppj->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $sppj->cpny_id ?? $sppj->cpnyid ?? '',
                        'deptname' => $sppj->department_id ?? $sppj->departementid ?? '',
                        'date' => $now->toDateString(),
                        'info' => $sppj->keperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                    ]
                );

                // simpan komentar (jika ada)
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($sppj->id, 'PJ', request());
                } catch (\Throwable $e) {
                }
            }
        );

        if (!$result['ok']) {
            return response()->json(['success' => false, 'message' => $result['message'] ?? 'Reject failed'], 403);
        }

        return response()->json(['success' => true, 'message' => 'SPPJ rejected successfully']);
    }

    public function reviseSppj(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'PJ';

        $sppj = TrSPPJ::with('creator')->where('sppjid', $docid)->first();
        if (!$sppj) {
            return response()->json(['success' => false, 'message' => 'SPPJ not found'], 404);
        }

        $eid = Hashids::encode($sppj->id);
        $docUrl = url('/showsppjs/'.$eid);
        $fullname = data_get($sppj, 'creator.name') ?: $sppj->created_by;

        $result = app(ApprovalController::class)->reviseStep(
            $sppj->sppjid,            // refnbr
            $doctype,                 // PT
            $user->username,          // actor
            $user->name,              // actor
            function (string $refnbr, \Carbon\Carbon $now) use ($sppj, $fullname, $docUrl) {
                // === HEADER SPPJ -> D ===
                $sppj->status = 'D';
                $sppj->completed_by = auth()->user()->username;
                $sppj->completed_at = $now;
                $sppj->save();

                // (opsional) DETAIL -> D
                // \App\Models\TrSPPJdetail::where('sppjid', $sppj->sppjid)->update(['status' => 'D']);

                // === Email ke requester ===
                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $sppj->sppjid,
                    'SPPJ',
                    'D',
                    $sppj->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $sppj->cpny_id ?? $sppj->cpnyid ?? '',
                        'deptname' => $sppj->department_id ?? $sppj->departementid ?? '',
                        'date' => $now->toDateString(),
                        'info' => $sppj->keperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,   // <<< tambahkan ini
                    ]
                );

                // === Simpan komentar (jika ada) ===
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($sppj->id, 'PJ', request());
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

        return response()->json(['success' => true, 'message' => 'SPPJ revised successfully']);
    }

    public function trackingDetail($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $sppj = TrSPPJ::findOrFail($id);
        $sppjNo = $sppj->sppjid;

        $fmt = fn ($dt) => $dt ? Carbon::parse($dt)->format('Y-m-d H:i') : null;

        // ✅ sesuai request: approved jika status = 'C'
        $approved = fn ($h) => $h ? (strtoupper((string) $h->status) === 'C') : false;

        // ===== SPPJ DETAIL =====
        $sppjDetails = TrSPPJdetail::query()
            ->where('sppjid', $sppjNo)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        // ===== LIST CS (ALL) =====
        $csList = TrCS::query()
            ->where('sppbjktid', $sppjNo) // <-- relasi ke SPPJ
            ->whereNull('deleted_at')
            ->orderBy('csdate', 'desc')
            ->get(['csid', 'csdate', 'status', 'completed_by', 'completed_at']);

        $selCsNo = optional($csList->first())->csid;

        // ===== LIST PO (ALL) =====
        $poList = TrPO::query()
            ->where('sppbjktid', $sppjNo) // <-- relasi ke SPPJ
            ->whereNull('deleted_at')
            ->orderBy('podate', 'desc')
            ->get(['ponbr', 'podate', 'status', 'csid', 'completed_by', 'completed_at']);

        $selPoNo = optional($poList->first())->ponbr;

        // ===== LIST BAST (ALL) =====
        // ⚠️ asumsi: tr_bast.sppbjktid = sppjid
        $bastList = TrBast::query()
            ->where('sppbjktid', $sppjNo)
            ->whereNull('deleted_at')
            ->orderBy('bastdate', 'desc')
            ->get(['bastid', 'bastdate', 'status', 'ponbr', 'csid', 'completed_by', 'completed_at']);

        // default selected bast: kalau ada PO terpilih, pilih bast terbaru by ponbr tsb
        $selBastNo = optional(
            $bastList->firstWhere('ponbr', $selPoNo) ?? $bastList->first()
        )->bastid;

        // ===== DETAIL DEFAULT SELECTED (CS/PO/BAST) =====

        // ---- CS header & selected-vendor-only detail ----
        $csHeader = $selCsNo
            ? TrCS::where('csid', $selCsNo)->whereNull('deleted_at')->first()
            : null;

        $csDetails = collect();
        if ($selCsNo) {
            $isTrue = function ($v) {
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
                // ✅ hanya vendor selected = true
                ->filter(function ($d) use ($isTrue) {
                    return $isTrue($d->vendor1selected)
                        || $isTrue($d->vendor2selected)
                        || $isTrue($d->vendor3selected)
                        || $isTrue($d->vendor4selected)
                        || $isTrue($d->vendor5selected)
                        || $isTrue($d->vendor6selected);
                })
                // ✅ map ke array supaya field tambahan pasti ikut ke JSON
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

                        // ✅ tampilkan vendor selected (nama)
                        'vendorname_selected' => $vendorName,
                        'vendorprice_selected' => $vendorPrice,

                        'status' => $d->status,
                    ];
                })
                ->values();
        }

        // ---- PO ----
        $poHeader = $selPoNo
            ? TrPO::where('ponbr', $selPoNo)
            ->where('cpny_id', $sppj->cpny_id)
            ->whereNull('deleted_at')->first()
            : null;

        $poDetails = $selPoNo
            ? TrPOdetail::where('ponbr', $selPoNo)
            ->where('budget_cpny_id', $sppj->cpny_id)
            ->whereNull('deleted_at')->orderBy('id')->get()
            : collect();

        // ---- BAST (header only, no detail) ----
        $bastHeader = $selBastNo
            ? TrBast::where('bastid', $selBastNo)->whereNull('deleted_at')->first()
            : null;

        $lastApprSppj = $this->getLastApprovalInfo($sppjNo);
        $lastApprCs = $selCsNo ? $this->getLastApprovalInfo($selCsNo) : null;
        $lastApprBast = $selBastNo ? $this->getLastApprovalInfo($selBastNo) : null;

        return response()->json([
            'doc' => $sppjNo,

            'lists' => [
                'cs' => $csList->map(fn ($x) => [
                    'doc' => $x->csid,
                    'date' => $fmt($x->csdate),
                    'status' => $x->status,
                    'is_approved' => (strtoupper((string) $x->status) === 'C'),
                ])->values(),

                'po' => $poList->map(fn ($x) => [
                    'doc' => $x->ponbr,
                    'date' => $fmt($x->podate),
                    'status' => $x->status,
                    'csid' => $x->csid,
                    'is_approved' => (strtoupper((string) $x->status) === 'C'),
                ])->values(),

                'bast' => $bastList->map(fn ($x) => [
                    'doc' => $x->bastid,
                    'date' => $fmt($x->bastdate),
                    'status' => $x->status,
                    'ponbr' => $x->ponbr,
                    'csid' => $x->csid,
                    'is_approved' => (strtoupper((string) $x->status) === 'C'),
                ])->values(),
            ],

            'selected' => [
                'cs_no' => $selCsNo,
                'po_no' => $selPoNo,
                'bast_no' => $selBastNo,
            ],

            'sppj' => [
                'header' => [
                    'doc' => $sppj->sppjid,
                    'date' => $fmt($sppj->sppjdate),
                    'cpny_id' => $sppj->cpny_id,
                    'department_id' => $sppj->department_id,
                    'keperluan' => $sppj->keperluan,
                    'status' => $sppj->status,
                    'created_by' => $sppj->created_by,
                    'created_at' => $fmt($sppj->created_at),
                    'completed_by' => $sppj->completed_by,
                    'completed_at' => $fmt($sppj->completed_at),
                    'is_approved' => $approved($sppj),
                    'last_approval' => $lastApprSppj,
                    'approval_list' => $this->getApprovalList($sppjNo),
                ],
                'details' => $sppjDetails,
            ],

            'cs' => [
                'header' => $csHeader ? [
                    'doc' => $csHeader->csid,
                    'date' => $fmt($csHeader->csdate),
                    'cpny_id' => $csHeader->cpny_id,
                    'department_id' => $csHeader->department_id,
                    'keperluan' => $csHeader->keperluan,
                    'status' => $csHeader->status,
                    'completed_by' => $csHeader->completed_by,
                    'completed_at' => $fmt($csHeader->completed_at),
                    'is_approved' => $approved($csHeader),
                    'last_approval' => $lastApprCs,
                    'approval_list' => $this->getApprovalList($csHeader->csid),
                    'flag_imbudget'   => (bool) $csHeader->flag_imbudget,
                    'imbudgetid'      => $csHeader->imbudgetid,
                    'status_imbudget' => $csHeader->status_imbudget,
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
                    'completed_by' => $poHeader->completed_by,
                    'completed_at' => $fmt($poHeader->completed_at),
                    'is_approved' => $approved($poHeader),

                ] : null,
                'details' => $poDetails,
            ],

            'bast' => [
                'header' => $bastHeader ? [
                    'doc' => $bastHeader->bastid,
                    'date' => $fmt($bastHeader->bastdate),
                    'cpny_id' => $bastHeader->cpny_id,
                    'department_id' => $bastHeader->department_id,
                    'vendorname' => $bastHeader->vendorname,
                    'status' => $bastHeader->status,
                    'completed_by' => $bastHeader->completed_by,
                    'completed_at' => $fmt($bastHeader->completed_at),
                    'is_approved' => $approved($bastHeader),
                    'last_approval' => $lastApprBast,
                    'approval_list' => $this->getApprovalList($bastHeader->bastid),
                ] : null,

                // ✅ tambahan info header buat isi "detail"
                'extra' => $bastHeader ? [
                    'ponbr' => $bastHeader->ponbr,
                    'csid' => $bastHeader->csid,
                    'keperluan' => $bastHeader->keperluan,
                    'user_peminta' => $bastHeader->user_peminta,
                    'handoverdate' => $fmt($bastHeader->handoverdate),
                    'startdate' => $fmt($bastHeader->startdate),
                    'enddate' => $fmt($bastHeader->enddate),

                    'order_term' => $bastHeader->order_term,
                    'terms_id' => $bastHeader->terms_id,
                    'topid' => $bastHeader->topid,
                    'payment_pct' => $bastHeader->payment_pct,
                    'progress_pct' => $bastHeader->progress_pct,

                    'bast_amount' => $bastHeader->bast_amount,
                    'penalty' => $bastHeader->penalty,
                    'total_penalty' => $bastHeader->total_penalty,
                    'realize_amount' => $bastHeader->realize_amount,

                    'location_id' => $bastHeader->location_id,
                    'sub_location_id' => $bastHeader->sub_location_id,
                    'spkpic' => $bastHeader->spkpic,
                    'spkwarranty' => $bastHeader->spkwarranty,
                    'days_penalty' => $bastHeader->days_penalty,
                    'rating_vendor' => $bastHeader->rating_vendor,
                ] : null,

                'details' => [],
            ],
        ]);
    }

    /* =========================================
       ITEM FETCH FOR DROPDOWN
       GET /sppjs/{hash}/tracking-detail/item?type=cs|po|bast&doc=...
       ========================================= */
    public function trackingDetailItem($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $sppj = TrSPPJ::findOrFail($id);
        $sppjNo = $sppj->sppjid;

        $type = request('type'); // cs|po|bast
        $doc = request('doc');  // csid / ponbr / bastid
        abort_if(!in_array($type, ['cs', 'po', 'bast'], true), 400);
        abort_if(!$doc, 400);

        $fmt = fn ($dt) => $dt ? Carbon::parse($dt)->format('Y-m-d H:i') : null;
        $approved = fn ($h) => $h ? (strtoupper((string) $h->status) === 'C') : false;

        if ($type === 'cs') {
            $h = TrCS::where('csid', $doc)
                ->where('sppbjktid', $sppjNo)
                ->whereNull('deleted_at')
                ->first();

            $details = collect();
            if ($h) {
                $isTrue = function ($v) {
                    if (is_bool($v)) {
                        return $v;
                    }
                    $v = strtolower((string) $v);

                    return in_array($v, ['1', 'true', 't', 'yes', 'y'], true);
                };

                $details = TrCSdetail::query()
                    ->where('csid', $doc)
                    ->whereNull('deleted_at')
                    ->orderBy('id')
                    ->get()
                    ->filter(function ($d) use ($isTrue) {
                        return $isTrue($d->vendor1selected)
                            || $isTrue($d->vendor2selected)
                            || $isTrue($d->vendor3selected)
                            || $isTrue($d->vendor4selected)
                            || $isTrue($d->vendor5selected)
                            || $isTrue($d->vendor6selected);
                    })
                    ->map(function ($d) use ($h, $isTrue) {
                        $vendorName = null;
                        $vendorPrice = null;

                        if ($isTrue($d->vendor1selected)) {
                            $vendorName = $h->vendorname1;
                            $vendorPrice = $d->vendorprice1;
                        } elseif ($isTrue($d->vendor2selected)) {
                            $vendorName = $h->vendorname2;
                            $vendorPrice = $d->vendorprice2;
                        } elseif ($isTrue($d->vendor3selected)) {
                            $vendorName = $h->vendorname3;
                            $vendorPrice = $d->vendorprice3;
                        } elseif ($isTrue($d->vendor4selected)) {
                            $vendorName = $h->vendorname4;
                            $vendorPrice = $d->vendorprice4;
                        } elseif ($isTrue($d->vendor5selected)) {
                            $vendorName = $h->vendorname5;
                            $vendorPrice = $d->vendorprice5;
                        } elseif ($isTrue($d->vendor6selected)) {
                            $vendorName = $h->vendorname6;
                            $vendorPrice = $d->vendorprice6;
                        }

                        return [
                            'id' => $d->id,
                            'inventoryid' => $d->inventoryid,
                            'inventory_descr' => $d->inventory_descr,
                            'qty' => $d->qty,
                            'uom' => $d->uom,
                            'vendorname_selected' => $vendorName,
                            'vendorprice_selected' => $vendorPrice,
                            'status' => $d->status,
                        ];
                    })
                    ->values();
            }

            return response()->json([
                'header' => $h ? [
                    'doc' => $h->csid,
                    'date' => $fmt($h->csdate),
                    'cpny_id' => $h->cpny_id,
                    'department_id' => $h->department_id,
                    'keperluan' => $h->keperluan,
                    'status' => $h->status,
                    'completed_by' => $h->completed_by,
                    'completed_at' => $fmt($h->completed_at),
                    'is_approved'     => $approved($h),
                    'last_approval'   => $this->getLastApprovalInfo($h->csid),
                    'flag_imbudget'   => (bool) $h->flag_imbudget,
                    'imbudgetid'      => $h->imbudgetid,
                    'status_imbudget' => $h->status_imbudget,
                ] : null,
                'details' => $details,
            ]);
        }

        if ($type === 'po') {
            $h = TrPO::where('ponbr', $doc)
                ->where('sppbjktid', $sppjNo)
                ->whereNull('deleted_at')
                ->first();

            $d = $h
                ? TrPOdetail::where('ponbr', $doc)
                    ->where('budget_cpny_id', $h->cpny_id)
                    ->whereNull('deleted_at')->orderBy('id')->get()
                : collect();

            return response()->json([
                'header' => $h ? [
                    'doc' => $h->ponbr,
                    'date' => $fmt($h->podate),
                    'cpny_id' => $h->cpny_id,
                    'department_id' => $h->department_id,
                    'vendorname' => $h->vendorname,
                    'status' => $h->status,
                    'completed_by' => $h->completed_by,
                    'completed_at' => $fmt($h->completed_at),
                    'is_approved' => $approved($h),
                ] : null,
                'details' => $d,
            ]);
        }

        // bast
        $h = TrBast::where('bastid', $doc)
            ->where('sppbjktid', $sppjNo)
            ->whereNull('deleted_at')
            ->first();

        return response()->json([
            'header' => $h ? [
                'doc' => $h->bastid,
                'date' => $fmt($h->bastdate),
                'cpny_id' => $h->cpny_id,
                'department_id' => $h->department_id,
                'vendorname' => $h->vendorname,
                'status' => $h->status,
                'completed_by' => $h->completed_by,
                'completed_at' => $fmt($h->completed_at),
                'is_approved' => $approved($h),
                'last_approval' => $this->getLastApprovalInfo($h->bastid),
            ] : null,
            'extra' => $h ? [
                'ponbr' => $h->ponbr,
                'csid' => $h->csid,
                'keperluan' => $h->keperluan,
                'user_peminta' => $h->user_peminta,
                'handoverdate' => $fmt($h->handoverdate),
                'startdate' => $fmt($h->startdate),
                'enddate' => $fmt($h->enddate),
                'bast_amount' => $h->bast_amount,
                'penalty' => $h->penalty,
                'total_penalty' => $h->total_penalty,
                'realize_amount' => $h->realize_amount,
                'topid' => $h->topid,
                'payment_pct' => $h->payment_pct,
                'progress_pct' => $h->progress_pct,
                'location_id' => $h->location_id,
                'sub_location_id' => $h->sub_location_id,
                'spkpic' => $h->spkpic,
                'spkwarranty' => $h->spkwarranty,
                'days_penalty' => $h->days_penalty,
                'rating_vendor' => $h->rating_vendor,
            ] : null,
            'details' => [], // BAST tidak ada detail
        ]);
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

    public function tracking_xxx($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $sppj = TrSPPJ::findOrFail($id);

        $getName = function (?string $username) {
            if (!$username) {
                return null;
            }
            $u = User::where('username', $username)->first();

            return $u->name ?? $username;
        };

        $createdByName = $getName($sppj->created_by ?? null);
        $createdAt = $sppj->created_at ? \Carbon\Carbon::parse($sppj->created_at)->format('Y-m-d H:i') : null;

        $completedByName = $getName($sppj->completed_by ?? null);
        $completedAt = $sppj->completed_at ? \Carbon\Carbon::parse($sppj->completed_at)->format('Y-m-d H:i') : null;

        // kolom opsional, kalau tidak ada biarkan null
        $rejectedByName = $getName($sppj->rejected_by ?? null);
        $rejectedAt = isset($sppj->rejected_at) ? \Carbon\Carbon::parse($sppj->rejected_at)->format('Y-m-d H:i') : null;

        $revisedByName = $getName($sppj->revised_by ?? null);
        $revisedAt = isset($sppj->revised_at) ? \Carbon\Carbon::parse($sppj->revised_at)->format('Y-m-d H:i') : null;

        $status = (string) ($sppj->status ?? '');
        $labelMap = [
            'P' => 'Waiting approval',
            'R' => 'Rejected',
            'D' => 'Revise',
            'C' => 'Completed',
        ];
        $statusLabel = $labelMap[$status] ?? $status;

        // selalu mulai dari Submitted
        $steps = [[
            'key' => 'submitted',
            'title' => 'SPPJ',
            'status' => 'C',              // dibuat = completed
            'status_label' => 'Submitted',
            'by' => $createdByName,
            'at' => $createdAt,
        ]];

        switch ($status) {
            case 'P':
                // masih menunggu/berjalan → tampilkan Approval saja
                $steps[] = [
                    'key' => 'approval',
                    'title' => 'Approval',
                    'status' => 'P',
                    'status_label' => 'Waiting approval',
                    'by' => $completedByName,
                    'at' => $completedAt,
                ];
                break;

            case 'R':
                // DITOLAK → langsung Submitted → Rejected (tanpa Approval)
                $steps[] = [
                    'key' => 'rejected',
                    'title' => 'Rejected',
                    'status' => 'R',
                    'status_label' => 'Rejected',
                    'by' => $completedByName,
                    'at' => $completedAt,
                ];
                break;

            case 'D':
                // REVISE → Submitted → Revise
                $steps[] = [
                    'key' => 'revise',
                    'title' => 'Revise',
                    'status' => 'D',
                    'status_label' => 'Revise',
                    'by' => $completedByName,
                    'at' => $completedAt,
                ];
                break;

            case 'C':
                // SELESAI → bisa langsung Submitted → Completed
                // (kalau kamu ingin menampilkan Approval yang sudah dilalui,
                // tambahkan step 'approval' sebelum 'completed')
                $steps[] = [
                    'key' => 'completed',
                    'title' => 'Completed',
                    'status' => 'C',
                    'status_label' => 'Completed',
                    'by' => $completedByName,
                    'at' => $completedAt,
                ];
                break;

            default:
                // status tidak dikenal → biarkan hanya Submitted
                break;
        }

        return response()->json([
            'doc' => $sppj->sppjid ?? (string) $sppj->id,
            'steps' => $steps,
            'status' => $status,
            'status_label' => $statusLabel,
        ]);
    }

    public function showBQ($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $bq = Bq::with(['creator:username,name'])->findOrFail($id);

        $loginUsername = $user->username ?? $user->name ?? null;

        // 1) Cek approval level 1 masih exist & pending
        $approvalLevel1Exists = TrApproval::where('refnbr', $bq->sppjtid)
            ->where('aprv_leveling', '1')
            ->where('status', 'P')
            ->whereNotNull('aprv_datebefore')
            ->exists();

        // 2) Approver level 1 boleh edit
        $canApproveEdit = TrApproval::where('refnbr', $bq->sppjtid)
            ->where('aprv_leveling', '1')
            ->where('status', 'P')
            ->whereNotNull('aprv_datebefore')
            ->where(function ($q) use ($loginUsername) {
                $u = $loginUsername;

                $q->where('aprv_username', $u)
                ->orWhere('aprv_username', 'ilike', $u.',%')
                ->orWhere('aprv_username', 'ilike', '%,'.$u.',%')
                ->orWhere('aprv_username', 'ilike', '%,'.$u);
            })
            ->exists();

        // 3) Creator boleh edit hanya jika approval level 1 MASIH EXIST
        $isCreator = ($bq->created_by === $loginUsername);
        $canCreatorEdit = $isCreator && $approvalLevel1Exists;

        // 4) Final permission
        $canEdit = $canApproveEdit || $canCreatorEdit;

        // $bqdetail = BqDetail::where('bqid', $bq->bqid)->get();
        $bqdetail = BqDetail::where('bqid', $bq->bqid)
    ->orderByRaw("
        CASE 
            WHEN bq_line_no ~ '^[0-9]+$' THEN bq_line_no::int
            ELSE 999999999
        END ASC
    ")
    ->orderBy('bq_line_no', 'ASC')
    ->get();

        return view('pages.sppjs.showbqsppjs', compact('bq', 'bqdetail', 'canEdit', 'hash'));
    }

    public function editBQ($id)
    {
        // kalau $id adalah PRIMARY KEY tabel tr_bq:
        $bq = Bq::with(['creator:username,name'])->findOrFail($id);

        // kalau $id itu bqid (string) ganti ke:
        // $bq = Bq::with(['creator:username,name'])->where('bqid', $id)->firstOrFail();

        $bq_detail = BqDetail::where('bqid', $bq->bqid)
            ->orderBy('bq_no') // biar urut
            ->get();

        $temp_id = session('import_temp_id');
        $tempData = $temp_id ? BqDetailTemp::where('temp_id', $temp_id)->get() : [];

        return view('pages.sppjs.editbqsppjs', compact(
            'bq',
            'bq_detail',
            'temp_id',
            'tempData'
        ));
    }

    public function printSppj($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Ambil SPPJ + relasi yang dibutuhkan
        $sppj = TrSPPJ::with([
            'requestType:requesttypeid,requesttype_name',
            'creator:username,name',
        ])
            ->findOrFail($id);

        // Detail baris SPPJ
        $sppjdetail = TrSPPJdetail::with([
            'location:location_id,location_name',
            'subLocation:sub_location_id,sub_location_name',
        ])
            ->where('sppjid', $sppj->sppjid)
            ->orderBy('sppj_no', 'ASC')
            ->get();

        $refnbr = $sppj->sppjid;
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

        $approve_count = $approval->count();

        // Company (handle null)
        $company = MsCompany::where('cpny_id', $sppj->cpny_id)->first();

        // Mapping status dokumen
        switch ($sppj->status) {
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
            'title' => ' Surat Permintaan Pekerjaan Jasa',
            'doc_type' => 'SPPJ',
            'docid' => $sppj->sppjid,
            'department_id' => $sppj->department_id,
            'cpnyname' => optional($company)->cpny_name,
            // identitas & tanggal
            'created_by_username' => $sppj->created_by,
            'created_by_name' => ucwords(strtolower(optional($sppj->creator)->name)),
            'created_at_fmt' => optional($sppj->created_at)->format('d F Y'),
            'req_date_fmt' => optional($sppj->created_at)->format('d M Y H:i'),
            'sppjdate' => \Carbon\Carbon::parse($sppj->sppjdate)->format('d F Y'),
            // konten
            'bqid' => $sppj->bqid,
            'keperluan' => $sppj->keperluan,
            'status_doc' => $status_doc,
            'requesttype_name' => optional($sppj->requestType)->requesttype_name,
        ];

        // Kirim ke view
        $pdf = \PDF::loadView(
            'pages.sppjs.pdf_sppjs',
            array_merge($data, [
                'detail' => $sppjdetail,
                'approval' => $approval,
                'approve_count' => $approve_count,
            ])
        );

        // Portrait jika <= 5 approver, else landscape
        $pdf->setPaper('A4', ($approve_count <= 5) ? 'portrait' : 'landscape');

        return $pdf->stream("pdf_sppjs_{$sppj->sppjid}.pdf");
    }

    public function createBQ($id)
    {
        $user = request()->user();
        $sppj = TrSPPJ::findOrFail($id);

        $temp_id = session('import_temp_id'); // ambil dari session

        $tempData = [];
        if ($temp_id) {
            $tempData = BqDetailTemp::where('temp_id', $temp_id)->get();
        }

        return view('pages.sppjs.createbqsppj', compact('sppj', 'tempData', 'temp_id'));
    }

    public function importCreate(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'sppjtid' => 'required',
        ]);

        try {
            $username = Auth::user()->username ?? 'system';
            $temp_id = (string) Str::uuid();

            // Bersihkan temp milik user agar batch tidak tercampur
            BqDetailTemp::where('created_by', $username)->delete();

            $idx = $request->input('idx');
            $sppjtid = $request->input('sppjtid');

            // =========================
            // ✅ VALIDASI: TOLAK FORMULA
            // =========================
            $file = $request->file('file');

            // CSV tidak punya formula, jadi hanya cek Excel
            $ext = strtolower($file->getClientOriginalExtension());
            if (in_array($ext, ['xlsx', 'xls'], true)) {
                $spreadsheet = IOFactory::load($file->getPathname());

                // Cek semua sheet biar aman (kalau template ada multi sheet)
                foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
                    $highestRow = $sheet->getHighestDataRow();
                    $highestCol = $sheet->getHighestDataColumn(); // mis: 'H'

                    for ($row = 1; $row <= $highestRow; ++$row) {
                        // loop kolom A..highestCol
                        for ($col = 'A'; $col <= $highestCol; ++$col) {
                            $cell = $sheet->getCell("{$col}{$row}");
                            $raw = $cell->getValue();

                            // skip kosong
                            if ($raw === null || $raw === '') {
                                continue;
                            }

                            // Deteksi formula (paling akurat)
                            if ($cell->isFormula()) {
                                throw new \RuntimeException("File Excel mengandung rumus (formula) pada sheet '{$sheet->getTitle()}' cell {$col}{$row}. ".'Silakan Copy → Paste Special → Values, lalu import ulang.');
                            }

                            // Guard tambahan (kadang value diawali '=')
                            if (is_string($raw) && str_starts_with(ltrim($raw), '=')) {
                                throw new \RuntimeException("File Excel mengandung rumus (formula) pada sheet '{$sheet->getTitle()}' cell {$col}{$row}. ".'Silakan ubah menjadi nilai (Paste Values), lalu import ulang.');
                            }
                        }
                    }
                }
            }

            // =========================
            // ✅ IMPORT BARU JALAN
            // =========================
            Excel::import(
                new BqDetailTempImport($temp_id, $sppjtid),
                $file
            );

            // Simpan temp_id ke session untuk dipakai di halaman create
            session(['import_temp_id' => $temp_id]);

            // ⬇️ Selalu redirect ke create
            return redirect()
                ->route('bqsppj.create', $idx)
                ->with('success', 'Data berhasil di-import.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal import: '.$e->getMessage());
        }
    }

    public function importCreate_xxx(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'sppjtid' => 'required',
        ]);

        try {
            $username = Auth::user()->username ?? 'system';
            $temp_id = (string) Str::uuid();

            // Bersihkan temp milik user agar batch tidak tercampur
            BqDetailTemp::where('created_by', $username)->delete();

            $idx = $request->input('idx');
            $sppjtid = $request->input('sppjtid');

            // Import Excel ke tr_bq_detail_temp
            Excel::import(
                new BqDetailTempImport($temp_id, $sppjtid),
                $request->file('file')
            );

            // Simpan temp_id ke session untuk dipakai di halaman create
            session(['import_temp_id' => $temp_id]);

            // ⬇️ Selalu redirect ke create
            return redirect()
                ->route('bqsppj.create', $idx)
                ->with('success', 'Data berhasil di-import.');
        } catch (\Throwable $e) {
            // opsional: report($e);
            return back()
                ->withInput()
                ->with('error', 'Gagal import: '.$e->getMessage());
        }
    }

    public function importEdit(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'sppjtid' => 'required', // hidden input
        ]);

        try {
            $username = Auth::user()->username ?? 'system';
            $temp_id = (string) Str::uuid();

            // Bersihkan temp milik user agar batch tidak tercampur
            BqDetailTemp::where('created_by', $username)->delete();

            $idx = $request->input('idx');
            $sppjtid = $request->input('sppjtid');

            // =========================
            // ✅ VALIDASI: TOLAK FORMULA
            // =========================
            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension());

            // CSV tidak punya formula → skip
            if (in_array($ext, ['xlsx', 'xls'], true)) {
                $spreadsheet = IOFactory::load($file->getPathname());

                foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
                    $highestRow = $sheet->getHighestDataRow();
                    $highestCol = $sheet->getHighestDataColumn();

                    for ($row = 1; $row <= $highestRow; ++$row) {
                        for ($col = 'A'; $col <= $highestCol; ++$col) {
                            $cell = $sheet->getCell("{$col}{$row}");
                            $raw = $cell->getValue();

                            if ($raw === null || $raw === '') {
                                continue;
                            }

                            // Deteksi formula resmi
                            if ($cell->isFormula()) {
                                throw new \RuntimeException("Import gagal (edit mode): file Excel mengandung rumus pada sheet '{$sheet->getTitle()}' cell {$col}{$row}. ".'Silakan ubah menjadi nilai (Copy → Paste Values).');
                            }

                            // Guard tambahan
                            if (is_string($raw) && str_starts_with(ltrim($raw), '=')) {
                                throw new \RuntimeException("Import gagal (edit mode): file Excel mengandung rumus pada sheet '{$sheet->getTitle()}' cell {$col}{$row}. ".'Silakan Paste Values lalu import ulang.');
                            }
                        }
                    }
                }
            }

            // =========================
            // ✅ IMPORT BARU JALAN
            // =========================
            Excel::import(
                new BqDetailTempImport($temp_id, $sppjtid),
                $file
            );

            // Simpan temp_id ke session untuk dipakai di edit
            session(['import_temp_id' => $temp_id]);

            return redirect()
                ->route('bqsppj.edit', $idx)
                ->with('success', 'Data berhasil di-import (edit mode).');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal import: '.$e->getMessage());
        }
    }

    public function importEdit_xxx(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'sppjtid' => 'required', // dari hidden input di form
            // 'bqid'   => 'nullable'  // kalau suatu saat kamu kirim bqid juga
        ]);

        try {
            $username = Auth::user()->username ?? 'system';
            $temp_id = (string) Str::uuid();

            // Bersihkan temp milik user agar tidak tercampur batch sebelumnya
            BqDetailTemp::where('created_by', $username)->delete();

            $idx = $request->input('idx');
            $sppjtid = $request->input('sppjtid');
            // $bqid    = $request->input('bqid'); // opsional

            // Import Excel ke tr_bq_detail_temp
            Excel::import(
                new BqDetailTempImport($temp_id, $sppjtid),
                $request->file('file')
            );

            // Simpan temp_id ke session untuk dipakai di createBQ()
            session(['import_temp_id' => $temp_id]);

            return redirect()
                 ->route('bqsppj.edit', $idx)
                 ->with('success', 'Data berhasil di‑import (edit mode).');
            //  return $idx
            //     ? redirect()->route('bqsppj.edit', $idx)
            //                 ->with('success', 'Data berhasil di‑import (edit mode).')
            //     : redirect()->route('bqs.create')
            //                 ->with('success', 'Data berhasil di‑import.');

            return back()->with('success', 'Data BQ berhasil di-import.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal import: '.$e->getMessage());
        }
    }

    public function storeBQ(Request $request)
    {
        $request->validate([
            'temp_id' => 'required',
            // 'bq_type' => 'nullable|string|max:20',
        ]);

        $temp_id = $request->input('temp_id');

        // Ambil batch temp
        $tempData = BqDetailTemp::where('temp_id', $temp_id)
            ->orderBy('bq_line_no', 'asc')
            ->get();
        if ($tempData->isEmpty()) {
            return response()->json(['message' => 'Tidak ada data BQ import ditemukan!'], 422);
        }
        $tempHead = $tempData->first();

        // $dt       = Carbon::now();
        // $datenow  = $dt->format('Y-m-d');
        // $year     = (int) $dt->year;
        // $month    = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        // $username = Auth::user()->username ?? 'system';

        // // Kebutuhan header
        // $doctype  = 'BQ';
        $doctype = 'BQ';
        $user = $request->user();
        $username = $user->username ?? 'system';
        $fullname = $user->name ?? 'system';

        $dt = Carbon::now();
        $year = (int) $dt->year;
        $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();

        $sppjtid = $tempHead->sppjtid ?? $request->input('sppjtid'); // string SPPJID (mis. SPPJ-xxxxx)
        $bq_type = $request->input('bq_type', 'SPPJ'); // default

        // Ambil cpny_id dari SPPJ (kalau kolom BQ wajib)
        $cpny_id = null;
        if ($sppjtid) {
            $sppj = TrSPPJ::where('sppjid', $sppjtid)
                        ->orWhere('id', $request->input('idx')) // kalau kamu kirim idx juga
                        ->first();
            $cpny_id = $sppj->cpny_id ?? $sppj->cpnyid ?? null;
            $deptid = $sppj->department_id ?? $sppj->departmentid ?? null;
        }

        // Grand total header
        $grandMat = $tempData->sum(fn ($r) => (float) ($r->total_est_material_price ?? 0));
        $grandJasa = $tempData->sum(fn ($r) => (float) ($r->total_est_jasa_price ?? 0));

        DB::beginTransaction(); // kalau semua di PG, bisa pakai DB::connection('pgsql')->beginTransaction();
        try {
            // ===== Autonumber untuk BQID =====
            // $autonbr = Autonbr::lockForUpdate()
            //     ->where('doctype', $doctype)
            //     ->where('year', $year)
            //     ->where('month', $month)
            //     ->where('status', 'A')
            //     ->first();

            // if (!$autonbr) {
            //     $autonbr = Autonbr::create([
            //         'doctype' => $doctype,
            //         'year'    => $year,
            //         'month'   => $month,
            //         'status'  => 'A',
            //         'number'  => 1,
            //     ]);
            //     $urutan = 1;
            // } else {
            //     $urutan = $autonbr->number + 1;
            //     $autonbr->number = $urutan;
            //     $autonbr->save();
            // }

            // $tglbln = substr($year, 2) . $month;
            // $bqid   = $doctype . $tglbln . sprintf('%04d', $urutan);

            $auto = $this->nextAutonbr(
                $doctype,
                $year,
                $month,
                $username,
                'BQ'
            );
            $urutan = (int) $auto['next'];

            $tglbln = substr((string) $year, 2).$month;   // YYMM
            $bqid = $doctype.$tglbln.sprintf('%04d', $urutan);

            $sppj->bqid = $bqid;
            $sppj->save();

            // ===== Insert HEADER: tr_bq =====
            $bq = Bq::create([
                'bqid' => $bqid,
                'sppjtid' => $sppjtid,
                'cpny_id' => $cpny_id,
                'bq_type' => $bq_type,
                'grand_total_est_material_price' => $grandMat,
                'grand_total_est_jasa_price' => $grandJasa,
                'status' => 'P',
                'created_by' => $username,
                'updated_by' => $username,
            ]);

            // ===== Insert DETAIL: tr_bq_detail =====
            $seq = 1; // nomor urut dimulai dari 1
            foreach ($tempData as $row) {
                BqDetail::create([
                    'bqid' => $bqid,
                    'sppjtid' => $row->sppjtid,
                    'bq_no' => $seq++,            // <<=== no urut
                    'bq_line_no' => $row->bq_line_no,  // tetap simpan line no asli jika diperlukan
                    'bq_descr' => $row->bq_descr,
                    'qty' => $row->qty,
                    'uom' => $row->uom,
                    'est_material_price' => $row->est_material_price,
                    'total_est_material_price' => $row->total_est_material_price,
                    'est_jasa_price' => $row->est_jasa_price,
                    'total_est_jasa_price' => $row->total_est_jasa_price,
                    'status' => 'P',
                    'created_by' => $username,
                    'updated_by' => $username,
                ]);
            }

            // ===== Hapus temp batch =====
            BqDetailTemp::where('temp_id', $temp_id)->delete();

            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr' => $bqid,
                    'doctype' => $doctype,
                    'cpnyid' => $cpny_id,
                    'departementid' => $deptid,
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
                        'message' => 'Failed to create PB',
                        'error' => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            } else {
                $uploadResult = null; // tidak ada attachment
            }

            DB::commit();

            return response()->json(['success' => true, 'bq' => $bq]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['error' => 'Gagal menyimpan BQ', 'message' => $e->getMessage()], 500);
        }
    }

    public function updateBQ(Request $request, int $id)
    {
        // temp_id boleh kosong → artinya tidak ada import baru (hanya simpan &/atau tambah lampiran)
        $request->validate([
            'temp_id' => 'nullable|string',
            'bq_type' => 'nullable|string|max:20',
            // 'attachments.*' => 'file|mimes:jpg,jpeg,png,webp,gif,bmp,svg|max:5120', // opsional validasi file
        ]);

        $doctype = 'BQ';
        $user = $request->user();
        $username = Auth::user()->username ?? 'system';
        $now = Carbon::now();

        $bq = Bq::findOrFail($id);
        $bqid = $bq->bqid;                 // <-- dipertahankan (tidak generate baru)
        $sppjtid = $bq->sppjtid ?? $request->input('sppjtid');

        $sppj = TrSPPJ::where('sppjid', $sppjtid)
                    ->first();
        $cpny_id = $sppj->cpny_id ?? $sppj->cpnyid ?? null;
        $deptid = $sppj->department_id ?? $sppj->departmentid ?? null;

        // Ambil temp data jika ada
        $tempId = $request->input('temp_id');
        $tempData = collect();
        if ($tempId) {
            $tempData = BqDetailTemp::where('temp_id', $tempId)
                        ->orderBy('bq_line_no', 'asc')
                        ->get();
        }

        DB::beginTransaction();
        try {
            // ===================== HEADER =====================
            // Hitung grand total:
            //  - jika ada tempData → pakai tempData
            //  - jika tidak ada → hitung dari detail existing agar tetap konsisten
            if ($tempData->isNotEmpty()) {
                $grandMat = $tempData->sum(fn ($r) => (float) ($r->total_est_material_price ?? 0));
                $grandJasa = $tempData->sum(fn ($r) => (float) ($r->total_est_jasa_price ?? 0));
            } else {
                $grandMat = (float) BqDetail::where('bqid', $bqid)->sum('total_est_material_price');
                $grandJasa = (float) BqDetail::where('bqid', $bqid)->sum('total_est_jasa_price');
            }

            // Optional: update cpny_id dari SPPJ jika ingin sinkron lagi (bisa di-skip)
            // $cpny_id = $bq->cpny_id;
            // if ($sppjtid) {
            //     $sppj    = TrSPPJ::where('sppjid', $sppjtid)->first();
            //     $cpny_id = $sppj->cpny_id ?? $cpny_id;
            // }

            $bq->grand_total_est_material_price = $grandMat;
            $bq->grand_total_est_jasa_price = $grandJasa;
            if ($request->filled('bq_type')) {
                $bq->bq_type = $request->input('bq_type');
            }
            $bq->updated_by = $username;
            $bq->updated_at = $now;
            $bq->save();

            // ===================== DETAIL (replace jika ada temp) =====================
            if ($tempData->isNotEmpty()) {
                // hapus semua detail lama bqid ini
                BqDetail::where('bqid', $bqid)->delete();

                // insert ulang dari temp (nomor urut bq_no dimulai 1)
                $seq = 1;
                foreach ($tempData as $row) {
                    BqDetail::create([
                        'bqid' => $bqid,
                        'sppjtid' => $row->sppjtid,
                        'bq_no' => $seq++,
                        'bq_line_no' => $row->bq_line_no,
                        'bq_descr' => $row->bq_descr,
                        'qty' => $row->qty,
                        'uom' => $row->uom,
                        'est_material_price' => $row->est_material_price,
                        'total_est_material_price' => $row->total_est_material_price,
                        'est_jasa_price' => $row->est_jasa_price,
                        'total_est_jasa_price' => $row->total_est_jasa_price,
                        'status' => 'P',
                        'created_by' => $username,
                        'updated_by' => $username,
                    ]);
                }

                // bersihkan temp batch setelah dipakai
                BqDetailTemp::where('temp_id', $tempId)->delete();
            }

            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr' => $bqid,
                    'doctype' => $doctype,
                    'cpnyid' => $cpny_id,
                    'departementid' => $deptid,
                    'base_folder' => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by' => $user->username,
                ];

                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternal($meta, $files);
                    // tidak return di sini!
                } catch (\Throwable $e) {
                    \DB::rollBack();

                    return response()->json([
                        'message' => 'Failed to create PB',
                        'error' => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            } else {
                $uploadResult = null; // tidak ada attachment
            }

            DB::commit();

            return response()->json(['success' => true, 'bq' => $bq]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Gagal mengupdate BQ',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function cancelSppj(Request $request, string $hash)
    {
        // decode hash -> id (sesuaikan kalau tidak pakai Hashids)
        $decoded = Hashids::decode($hash);
        abort_if(empty($decoded), 404, 'Invalid document');

        $id = $decoded[0];

        // ambil doc
        $sppb = TrSPPJ::query()->where('id', $id)->firstOrFail();

        DB::beginTransaction();
        try {
            // update status header jadi X (Canceled)
            $sppb->status = 'X';
            $sppb->updated_by = Auth::user()->username ?? Auth::id(); // kalau kolom ada
            $sppb->updated_at = now(); // kalau kolom ada
            $sppb->save();

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

    public function printBQ($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Ambil SPPJ + relasi yang dibutuhkan
        $bq = Bq::findOrFail($id);

        // Detail baris SPPJ
        // $bqdetail = BqDetail::where('bqid', $bq->bqid)
        //     ->get();
        $bqdetail = BqDetail::where('bqid', $bq->bqid)
    ->orderByRaw("
        CASE 
            WHEN bq_line_no ~ '^[0-9]+$' THEN bq_line_no::int
            ELSE 999999999
        END ASC
    ")
    ->orderBy('bq_line_no', 'ASC')
    ->get();

        $sppj = TrSPPJ::where('sppjid', $bq->sppjtid)
            ->first();

        $company = MsCompany::where('cpny_id', $bq->cpny_id)->first();

        $data = [
            'title' => 'Bills of Quantities (BQ)',
            'doc_type' => 'BQ',
            'cpny_id' => $company->cpny_id,
            'cpny_name' => $company->cpny_name,
            'keperluan' => $sppj->keperluan,
        ];

        // Kirim ke view
        $pdf = \PDF::loadView(
            'pages.sppjs.pdfbq_sppj',
            array_merge($data, [
                'bq' => $bq,
                'bqdetail' => $bqdetail,
            ])
        );

        // Portrait jika <= 5 approver, else landscape
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream("pdfbq_sppj_{$bq->bqid}.pdf");
    }

    public function createBqKontrak(string $sppjId)
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $sppj = TrSPPJ::query()
            ->select(['id', 'sppjid', 'cpny_id', 'department_id', 'bqid', 'bqtype'])
            ->where('id', $sppjId)
            ->firstOrFail();

        // Temp rows untuk sppj ini (scoped by user)
        $tempRows = BqDetailTemp::query()
            ->where('sppjtid', $sppj->id)
            ->where('created_by', $user->username)
            ->orderBy('bq_line_no')
            ->get();

        return view('pages.sppjs.createbqkontrak', compact('sppj', 'tempRows'));
    }

    public function categoriesBqKontrak(Request $request, string $sppjId)
    {
        TrSPPJ::query()->where('id', $sppjId)->firstOrFail();

        $search = trim((string) $request->query('search', ''));
        $page = max((int) $request->query('page', 1), 1);
        $perPage = 10;

        $q = MsKontrakCategory::query()
            ->select(['kontrakcategory', 'kontrakcategory_descr'])
            ->where('status', 'A');

        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('kontrakcategory', 'ilike', "%{$search}%")
                ->orWhere('kontrakcategory_descr', 'ilike', "%{$search}%");
            });
        }

        $total = (clone $q)->count();

        $rows = $q->orderBy('kontrakcategory')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return response()->json([
            'ok' => true,
            'data' => $rows,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    public function pickCategoryBqKontrak(Request $request, string $sppjId)
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $request->validate([
            'kontrakcategory' => ['required', 'string', 'max:50'],
        ]);

        $sppj = TrSPPJ::query()
            ->select(['id', 'sppjid', 'cpny_id', 'department_id'])
            ->where('id', $sppjId)
            ->firstOrFail();

        $kontrakcategory = $request->kontrakcategory;

        // Ambil master BQ kontrak berdasar category
        $items = MsKontrakBQ::query()
            ->where('status', 'A')
            ->where('kontrakcategory', $kontrakcategory)
            ->orderBy('kontrak_bq_line_no')
            ->get();

        if ($items->isEmpty()) {
            return response()->json([
                'ok' => false,
                'message' => 'Master MsKontrakBQ kosong untuk category ini',
            ], 422);
        }

        $tempId = (string) Str::uuid();

        DB::transaction(function () use ($user, $sppj, $kontrakcategory, $items, $tempId) {
            // optional: bersihkan temp untuk category yg sama biar tidak dobel
            BqDetailTemp::query()
                ->where('sppjtid', $sppj->id)
                ->where('created_by', $user->username)
                ->where('kontrakcategory', $kontrakcategory)
                ->delete();

            foreach ($items as $it) {
                BqDetailTemp::create([
                    'temp_id' => $tempId,
                    'bqid' => null,
                    'sppjtid' => $sppj->id,
                    'bq_no' => null,

                    'bq_line_no' => $it->kontrak_bq_line_no,
                    'bq_descr' => $it->kontrak_bq_descr,

                    'qty' => 0, // default 0, user isi
                    'uom' => $it->kontrak_bq_uom,

                    'bqtype' => 'Kontrak',

                    'kontrakcategory' => $kontrakcategory,
                    'kontrak_bq_id' => $it->kontrak_bq_id,
                    'kontrak_bq_type' => $it->kontrak_bq_type,
                    'kontrak_duration_qty' => $it->kontrak_duration_qty,

                    'status' => 'A',
                    'created_by' => $user->username,
                    'created_at' => now(),
                    'updated_by' => $user->username,
                    'updated_at' => now(),
                ]);
            }
        });

        // return refreshed temp rows
        $tempRows = BqDetailTemp::query()
            ->where('sppjtid', $sppj->id)
            ->where('created_by', $user->username)
            ->orderBy('bq_line_no')
            ->get();

        // return response()->json(['ok' => true, 'data' => $tempRows]);
        return response()->json([
            'ok' => true,
            'temp_id' => $tempId,
            'data' => $tempRows,
        ]);
    }

    public function saveBqKontrak(Request $request, string $sppjId)
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $sppj = TrSPPJ::query()
            ->select(['id', 'sppjid', 'cpny_id', 'department_id'])
            ->where('id', $sppjId)
            ->firstOrFail();

        $tempId = $request->input('temp_id');
        $qtyMap = $request->input('qty', []);
        if (!is_array($qtyMap)) {
            $qtyMap = [];
        }

        // ✅ Update qty ke temp (boleh 0)
        foreach ($qtyMap as $rowId => $qty) {
            BqDetailTemp::query()
                ->where('temp_id', $tempId)
                ->where('id', (int) $rowId)
                ->where('sppjtid', $sppj->id)
                ->where('created_by', $user->username)
                ->update([
                    'qty' => (float) $qty, // boleh 0
                    'updated_by' => $user->username,
                    'updated_at' => now(),
                ]);
        }

        // ✅ Ambil tempRows berdasarkan temp_id (lebih aman)
        $tempRows = BqDetailTemp::query()
            ->where('temp_id', $tempId)
            ->where('sppjtid', $sppj->id)
            ->where('created_by', $user->username)
            ->orderBy('bq_line_no')
            ->get();

        if ($tempRows->isEmpty()) {
            return back()->withInput()->with('error', 'Detail Kontrak masih kosong. Pilih Category dulu.');
        }

        // ❌ VALIDASI QTY DIHAPUS (sekarang qty boleh 0)

        if ($tempRows->whereNotNull('kontrakcategory')->count() === 0) {
            return back()->withInput()->with('error', 'Category belum terpilih atau data temp invalid.');
        }

        $bq = null;

        DB::transaction(function () use ($user, $sppj, $tempRows, &$bq) {
            // 1️⃣ Insert header BQ
            $bq = Bq::create([
                'bqid' => null,
                'sppjtid' => $sppj->sppjid,
                'cpny_id' => $sppj->cpny_id,
                'bq_type' => 'Kontrak',
                'grand_total_est_material_price' => 0,
                'grand_total_est_jasa_price' => 0,
                'status' => 'P',
                'created_by' => $user->username,
                'created_at' => now(),
                'updated_by' => $user->username,
                'updated_at' => now(),
            ]);

            // 2️⃣ Generate BQID
            $doctype = 'BQ';
            $dt = Carbon::now();
            $year = (int) $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);

            $auto = $this->nextAutonbr($doctype, $year, $month, $user->username, 'BQ');
            $urutan = (int) ($auto['next'] ?? 1);

            $tglbln = substr((string) $year, 2).$month; // YYMM
            $bqid = $doctype.$tglbln.sprintf('%04d', $urutan);

            $bq->update([
                'bqid' => $bqid,
                'updated_by' => $user->username,
                'updated_at' => now(),
            ]);

            // 3️⃣ Insert detail (qty boleh 0)
            foreach ($tempRows as $t) {
                BqDetail::create([
                    'bqid' => $bqid,
                    'sppjtid' => $sppj->sppjid,
                    'bq_no' => $t->bq_line_no,

                    'bq_line_no' => $t->bq_line_no,
                    'bq_descr' => $t->bq_descr,
                    'qty' => (float) $t->qty, // boleh 0
                    'uom' => $t->uom,

                    'est_material_price' => 0,
                    'total_est_material_price' => 0,
                    'est_jasa_price' => 0,
                    'total_est_jasa_price' => 0,

                    'bqtype' => 'Kontrak',
                    'kontrakcategory' => $t->kontrakcategory,
                    'kontrak_bq_id' => $t->kontrak_bq_id,
                    'kontrak_bq_type' => $t->kontrak_bq_type,
                    'kontrak_duration_qty' => $t->kontrak_duration_qty,

                    'status' => 'P',
                    'created_by' => $user->username,
                    'created_at' => now(),
                    'updated_by' => $user->username,
                    'updated_at' => now(),
                ]);
            }

            // 4️⃣ Update SPPJ
            TrSPPJ::query()
                ->where('id', $sppj->id)
                ->update([
                    'bqid' => $bqid,
                    'bqtype' => 'Kontrak',
                    'updated_by' => $user->username,
                    'updated_at' => now(),
                ]);

            // 5️⃣ Clear temp
            BqDetailTemp::query()
                ->where('temp_id', $tempRows->first()->temp_id)
                ->where('created_by', $user->username)
                ->delete();
        });

        $eid = Hashids::encode($bq->id);

        return redirect('/showbqkontrak/'.($eid ?? $bq->id))
            ->with('success', 'BQ Kontrak berhasil disimpan');
    }

    public function showBqKontrak($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $bq = Bq::with(['creator:username,name'])->findOrFail($id);

        $loginUsername = $user->username ?? $user->name ?? null;

        // 1️⃣ Cek approval level 1 masih exist & pending
        $approvalLevel1Exists = TrApproval::where('refnbr', $bq->sppjtid)
            ->where('aprv_leveling', '1')
            ->where('status', 'P')
            ->whereNotNull('aprv_datebefore')
            ->exists();

        // 2️⃣ Approver level 1 boleh edit
        $canApproveEdit = TrApproval::where('refnbr', $bq->sppjtid)
            ->where('aprv_leveling', '1')
            ->where('status', 'P')
            ->whereNotNull('aprv_datebefore')
            ->where(function ($q) use ($loginUsername) {
                $u = trim((string) $loginUsername);

                $q->where('aprv_username', $u)
                    ->orWhere('aprv_username', 'ilike', $u.',%')
                    ->orWhere('aprv_username', 'ilike', '%,'.$u.',%')
                    ->orWhere('aprv_username', 'ilike', '%,'.$u);
            })
            ->exists();

        // 3️⃣ Creator boleh edit hanya jika approval level 1 MASIH EXIST
        $isCreator = ($bq->created_by === $loginUsername);
        $canCreatorEdit = $isCreator && $approvalLevel1Exists;

        // 4️⃣ Final permission
        $canEdit = $canApproveEdit || $canCreatorEdit;

        $bqdetail = BqDetail::where('bqid', $bq->bqid)->get();

        // ambil hash untuk SPPJ
        $sppj = TrSPPJ::select('id', 'sppjid')
            ->where('sppjid', $bq->sppjtid)
            ->first();

        $sppjHash = $sppj ? Hashids::encode($sppj->id) : null;

        return view('pages.sppjs.showbqkontrak', compact(
            'bq',
            'bqdetail',
            'canEdit',
            'hash',
            'sppjHash'
        ));
    }

    public function editBqKontrak_zzz($id)
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $bq = Bq::query()
            ->where('id', $id)
            ->firstOrFail();

        // detail untuk ditampilkan & diedit qty
        $bqdetail = BqDetail::query()
            ->where('bqid', $bq->bqid)
            ->orderBy('bq_line_no')
            ->get();

        // optional: cek hak edit
        $canEdit = true; // sesuaikan logic kamu
        $eid = Hashids::encode($bq->id);

        return view('pages.sppjs.editbqkontrak', compact('bq', 'bqdetail', 'canEdit', 'eid'));
    }

    public function editBqKontrak(string $eid)
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $dec = Hashids::decode($eid);
        abort_if(empty($dec), 404);
        $bqPk = (int) $dec[0];

        $bq = Bq::query()->where('id', $bqPk)->firstOrFail();

        // temp_id utk sesi edit ini (dipakai kalau user ganti category)
        $tempId = (string) Str::uuid();

        // ✅ DEFAULT: tampilkan detail asli (untuk update qty langsung ke BqDetail)
        $details = BqDetail::query()
            ->where('bqid', $bq->bqid)
            ->where('bqtype', 'Kontrak') // optional, kalau memang semua detail kontrak
            ->orderBy('bq_line_no')
            ->get();

        // category existing (buat isi textbox category di view)
        $currentCategory = optional($details->first())->kontrakcategory ?? '';

        // (optional) bersihkan temp lama user utk bqid ini
        BqDetailTemp::query()
            ->where('bqid', $bq->bqid)
            ->where('created_by', $user->username)
            ->delete();

        // ✅ view kamu butuh ini semua
        return view('pages.sppjs.editbqkontrak', compact(
            'bq',
            'details',
            'currentCategory',
            'eid',
            'tempId'
        ));
    }

    public function editBqKontrak_xxx(string $eid)
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $dec = Hashids::decode($eid);
        abort_if(empty($dec), 404);
        $bqPk = (int) $dec[0];

        $bq = Bq::query()->where('id', $bqPk)->firstOrFail();

        // bikin temp_id baru utk sesi edit ini
        $tempId = (string) Str::uuid();

        DB::transaction(function () use ($user, $bq, $tempId) {
            // bersihin temp lama user utk bqid ini (optional)
            BqDetailTemp::query()
                ->where('bqid', $bq->bqid)
                ->where('created_by', $user->username)
                ->delete();

            // copy detail existing -> temp
            $details = BqDetail::query()
                ->where('bqid', $bq->bqid)
                ->orderBy('bq_line_no')
                ->get();

            foreach ($details as $d) {
                BqDetailTemp::create([
                    'temp_id' => $tempId,
                    'bqid' => $bq->bqid,
                    'sppjtid' => $bq->sppjtid, // sesuai struktur kamu
                    'bq_no' => $d->bq_no,
                    'bq_line_no' => $d->bq_line_no,
                    'bq_descr' => $d->bq_descr,
                    'qty' => $d->qty ?? 0,
                    'uom' => $d->uom,

                    'bqtype' => $d->bqtype,

                    'kontrakcategory' => $d->kontrakcategory,
                    'kontrak_bq_id' => $d->kontrak_bq_id,
                    'kontrak_bq_type' => $d->kontrak_bq_type,
                    'kontrak_duration_qty' => $d->kontrak_duration_qty,

                    'status' => 'A',
                    'created_by' => $user->username,
                    'created_at' => now(),
                    'updated_by' => $user->username,
                    'updated_at' => now(),
                ]);
            }
        });

        $tempRows = BqDetailTemp::query()
            ->where('temp_id', $tempId)
            ->where('created_by', $user->username)
            ->orderBy('bq_line_no')
            ->get();

        return view('pages.sppjs.editbqkontrak', compact('bq', 'tempRows', 'eid', 'tempId'));
    }

    public function listKontrakCategories(Request $request, string $eid)
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $search = trim($request->query('search', ''));
        $page = max((int) $request->query('page', 1), 1);
        $perPage = 10;

        $q = MsKontrakCategory::query(); // <-- ganti sesuai table master category kamu
        if ($search !== '') {
            $q->where('kontrakcategory', 'ilike', "%{$search}%")
            ->orWhere('kontrakcategory_descr', 'ilike', "%{$search}%");
        }

        $total = $q->count();
        $rows = $q->orderBy('kontrakcategory')
            ->skip(($page - 1) * $perPage)->take($perPage)
            ->get(['kontrakcategory', 'kontrakcategory_descr']);

        return response()->json([
            'ok' => true,
            'data' => $rows,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    public function pickKontrakCategory(Request $request, string $eid)
    {
        try {
            $user = Auth::user();
            abort_unless($user, 401);

            $category = trim((string) $request->input('kontrakcategory', ''));
            $tempId = trim((string) $request->input('temp_id', ''));

            if ($category === '' || $tempId === '') {
                return response()->json(['ok' => false, 'message' => 'Category/temp_id wajib'], 422);
            }

            $dec = Hashids::decode($eid);
            if (empty($dec)) {
                return response()->json(['ok' => false, 'message' => 'EID tidak valid'], 404);
            }
            $bqPk = (int) $dec[0];

            $bq = Bq::query()->where('id', $bqPk)->first();
            if (!$bq) {
                return response()->json(['ok' => false, 'message' => 'BQ tidak ditemukan'], 404);
            }

            // ✅ MASTER KONTRAK (field benar)
            $masterRows = MsKontrakBQ::query()
                ->where('status', 'A')
                ->where('kontrakcategory', $category)
                ->orderBy('kontrak_bq_line_no')
                ->get();

            if ($masterRows->isEmpty()) {
                return response()->json(['ok' => false, 'message' => 'Master detail kosong'], 422);
            }

            DB::transaction(function () use ($user, $bq, $tempId, $category, $masterRows) {
                // ✅ clear temp untuk session edit ini saja
                BqDetailTemp::query()
                    ->where('temp_id', $tempId)
                    ->where('bqid', $bq->bqid)
                    ->where('created_by', $user->username)
                    ->delete();

                foreach ($masterRows as $m) {
                    BqDetailTemp::create([
                        'temp_id' => $tempId,
                        'bqid' => $bq->bqid,
                        'sppjtid' => $bq->sppjtid,
                        'bq_no' => $m->kontrak_bq_line_no, // optional

                        // ✅ INI KUNCINYA: mapping dari MsKontrakBQ yang benar
                        'bq_line_no' => $m->kontrak_bq_line_no,
                        'bq_descr' => $m->kontrak_bq_descr,
                        'uom' => $m->kontrak_bq_uom,
                        'qty' => 0,

                        'bqtype' => 'Kontrak',
                        'kontrakcategory' => $category,
                        'kontrak_bq_id' => $m->kontrak_bq_id,
                        'kontrak_bq_type' => $m->kontrak_bq_type,
                        'kontrak_duration_qty' => $m->kontrak_duration_qty,

                        'status' => 'A',
                        'created_by' => $user->username,
                        'created_at' => now(),
                        'updated_by' => $user->username,
                        'updated_at' => now(),
                    ]);
                }
            });

            $tempRows = BqDetailTemp::query()
                ->where('temp_id', $tempId)
                ->where('bqid', $bq->bqid)
                ->where('created_by', $user->username)
                ->orderBy('bq_line_no')
                ->get([
                    'id',
                    'bq_line_no',
                    'bq_descr',
                    'qty',
                    'uom',
                    'kontrak_bq_id',
                    'kontrak_bq_type',
                    'kontrak_duration_qty',
                    'kontrakcategory',
                ]);

            // return response()->json(['ok' => true, 'data' => $tempRows]);
            return response()->json([
                'ok' => true,
                'temp_id' => $tempId,
                'data' => $tempRows,
            ]);
        } catch (\Throwable $e) {
            \Log::error('pickKontrakCategory error', [
                'eid' => $eid,
                'payload' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Server error: '.$e->getMessage(),
            ], 500);
        }
    }

    public function pickKontrakCategory_xxx(Request $request, string $eid)
    {
        try {
            $user = Auth::user();
            abort_unless($user, 401);

            $category = trim((string) $request->input('kontrakcategory', ''));
            $tempId = trim((string) $request->input('temp_id', ''));

            if ($category === '' || $tempId === '') {
                return response()->json(['ok' => false, 'message' => 'Category/temp_id wajib'], 422);
            }

            $dec = Hashids::decode($eid);
            if (empty($dec)) {
                return response()->json(['ok' => false, 'message' => 'EID tidak valid'], 404);
            }
            $bqPk = (int) $dec[0];

            $bq = Bq::query()->where('id', $bqPk)->first();
            if (!$bq) {
                return response()->json(['ok' => false, 'message' => 'BQ tidak ditemukan'], 404);
            }

            // ✅ ganti sesuai master detail kamu
            $masterRows = MsKontrakBQ::query()
                ->where('kontrakcategory', $category)
                ->orderBy('kontrak_bq_line_no')
                ->get();

            if ($masterRows->isEmpty()) {
                return response()->json(['ok' => false, 'message' => 'Master detail kosong'], 422);
            }

            DB::transaction(function () use ($user, $bq, $tempId, $category, $masterRows) {
                BqDetailTemp::query()
                    ->where('temp_id', $tempId)
                    ->where('created_by', $user->username)
                    ->delete();

                foreach ($masterRows as $m) {
                    BqDetailTemp::create([
                        'temp_id' => $tempId,
                        'bqid' => $bq->bqid,
                        'sppjtid' => $bq->sppjtid,

                        'bq_line_no' => $m->bq_line_no,
                        'bq_descr' => $m->bq_descr,
                        'qty' => 0,
                        'uom' => $m->uom,

                        'bqtype' => 'Kontrak',
                        'kontrakcategory' => $category,
                        'kontrak_bq_id' => $m->kontrak_bq_id,
                        'kontrak_bq_type' => $m->kontrak_bq_type,
                        'kontrak_duration_qty' => $m->kontrak_duration_qty,

                        'status' => 'A',
                        'created_by' => $user->username,
                        'created_at' => now(),
                        'updated_by' => $user->username,
                        'updated_at' => now(),
                    ]);
                }
            });

            $tempRows = BqDetailTemp::query()
                ->where('temp_id', $tempId)
                ->where('created_by', $user->username)
                ->orderBy('bq_line_no')
                ->get();

            return response()->json(['ok' => true, 'data' => $tempRows]);
        } catch (\Throwable $e) {
            \Log::error('pickKontrakCategory error', [
                'eid' => $eid,
                'payload' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Server error: '.$e->getMessage(),
            ], 500);
        }
    }

    public function updateBqKontrak(Request $request, string $eid)
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $tempId = trim((string) $request->input('temp_id', ''));
        $newCategory = trim((string) $request->input('kontrakcategory', ''));

        $qtyMap = $request->input('qty', []);
        if (!is_array($qtyMap)) {
            $qtyMap = [];
        }

        // decode eid -> pk
        $dec = Hashids::decode($eid);
        abort_if(empty($dec), 404);
        $bqPk = (int) $dec[0];

        $bq = Bq::query()->where('id', $bqPk)->firstOrFail();

        // Ambil category existing dari BqDetail (kontrak) sebagai pembanding
        $currentCategory = (string) (BqDetail::query()
            ->where('bqid', $bq->bqid)
            ->where('bqtype', 'Kontrak')
            ->value('kontrakcategory') ?? '');

        // ==============
        // Tentukan MODE
        // ==============
        // Kalau kontrakcategory berubah -> MODE B (pakai temp replace)
        // Kalau sama / kosong -> MODE A (langsung update qty bq_detail)
        $categoryChanged = ($newCategory !== '' && $currentCategory !== '' && $newCategory !== $currentCategory);

        // kalau currentCategory kosong (misal data lama belum ada) tapi user pilih category,
        // ini kita anggap butuh replace dari temp
        if ($currentCategory === '' && $newCategory !== '') {
            $categoryChanged = true;
        }

        // =========================
        // MODE A: hanya update qty
        // =========================
        if (!$categoryChanged) {
            // Validasi minimal ada qty > 0
            $hasQty = false;
            foreach ($qtyMap as $v) {
                $n = (float) $v;
                if ($n > 0) {
                    $hasQty = true;
                    break;
                }
            }
            if (!$hasQty) {
                return back()->withInput()->with('error', 'Qty masih 0 semua. Isi minimal 1 item.');
            }

            DB::transaction(function () use ($qtyMap, $user, $bq) {
                foreach ($qtyMap as $detailId => $qty) {
                    BqDetail::query()
                        ->where('bqid', $bq->bqid)
                        ->where('bqtype', 'Kontrak')
                        ->where('id', (int) $detailId)   // ← DI MODE A key harus id bq_detail
                        ->update([
                            'qty' => (float) $qty,
                            'updated_by' => $user->username,
                            'updated_at' => now(),
                        ]);
                }

                Bq::query()->where('id', $bq->id)->update([
                    'updated_by' => $user->username,
                    'updated_at' => now(),
                ]);
            });

            return redirect()
                ->route('bqkontrak.show', $eid)
                ->with('success', 'Qty Kontrak berhasil diupdate');
        }

        // =========================================
        // MODE B: category berubah -> pakai TEMP
        // =========================================
        if ($tempId === '') {
            return back()->withInput()->with('error', 'Temp ID tidak ditemukan. Silakan pilih category kembali.');
        }

        // 1) update qty ke temp sesuai input (key qty = id temp)
        foreach ($qtyMap as $rowId => $qty) {
            BqDetailTemp::query()
                ->where('temp_id', $tempId)
                ->where('id', (int) $rowId)
                ->where('bqid', $bq->bqid)
                ->where('created_by', $user->username)
                ->update([
                    'qty' => (float) $qty,
                    'updated_by' => $user->username,
                    'updated_at' => now(),
                ]);
        }

        // 2) ambil temp rows
        $tempRows = BqDetailTemp::query()
            ->where('temp_id', $tempId)
            ->where('bqid', $bq->bqid)
            ->where('created_by', $user->username)
            ->orderBy('bq_line_no')
            ->get();

        if ($tempRows->isEmpty()) {
            return back()->withInput()->with('error', 'Detail Kontrak masih kosong. Pilih Category dulu.');
        }

        if ($tempRows->where('qty', '>', 0)->count() === 0) {
            return back()->withInput()->with('error', 'Qty masih 0 semua. Isi minimal 1 item.');
        }

        // (opsional tapi aman) pastikan kontrakcategory temp sesuai pilihan terbaru
        if ($newCategory !== '') {
            BqDetailTemp::query()
                ->where('temp_id', $tempId)
                ->where('bqid', $bq->bqid)
                ->where('created_by', $user->username)
                ->update([
                    'kontrakcategory' => $newCategory,
                    'updated_by' => $user->username,
                    'updated_at' => now(),
                ]);

            // refresh rows
            $tempRows = BqDetailTemp::query()
                ->where('temp_id', $tempId)
                ->where('bqid', $bq->bqid)
                ->where('created_by', $user->username)
                ->orderBy('bq_line_no')
                ->get();
        }

        DB::transaction(function () use ($user, $bq, $tempRows, $tempId) {
            // delete detail lama (HANYA KONTRAK, jangan delete semua bq detail)
            BqDetail::query()
                ->where('bqid', $bq->bqid)
                ->where('bqtype', 'Kontrak')
                ->delete();

            // insert detail baru dari temp
            foreach ($tempRows as $t) {
                BqDetail::create([
                    'bqid' => $bq->bqid,
                    'sppjtid' => $bq->sppjtid,
                    'bq_no' => $t->bq_line_no,
                    'bq_line_no' => $t->bq_line_no,
                    'bq_descr' => $t->bq_descr,
                    'qty' => (float) $t->qty,
                    'uom' => $t->uom,

                    'est_material_price' => 0,
                    'total_est_material_price' => 0,
                    'est_jasa_price' => 0,
                    'total_est_jasa_price' => 0,

                    'bqtype' => 'Kontrak',
                    'kontrakcategory' => $t->kontrakcategory,
                    'kontrak_bq_id' => $t->kontrak_bq_id,
                    'kontrak_bq_type' => $t->kontrak_bq_type,
                    'kontrak_duration_qty' => $t->kontrak_duration_qty,

                    'status' => 'P',
                    'created_by' => $user->username,
                    'created_at' => now(),
                    'updated_by' => $user->username,
                    'updated_at' => now(),
                ]);
            }

            // update header
            Bq::query()->where('id', $bq->id)->update([
                'updated_by' => $user->username,
                'updated_at' => now(),
            ]);

            // clear temp sesi ini
            BqDetailTemp::query()
                ->where('temp_id', $tempId)
                ->where('bqid', $bq->bqid)
                ->where('created_by', $user->username)
                ->delete();
        });

        return redirect()
            ->route('bqkontrak.show', $eid)
            ->with('success', 'BQ Kontrak berhasil diupdate');
    }

    public function updateBqKontrak_xxx(Request $request, string $eid)
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $tempId = trim((string) $request->input('temp_id', ''));
        $qtyMap = $request->input('qty', []);
        if (!is_array($qtyMap)) {
            $qtyMap = [];
        }

        $dec = Hashids::decode($eid);
        abort_if(empty($dec), 404);
        $bqPk = (int) $dec[0];

        $bq = Bq::query()->where('id', $bqPk)->firstOrFail();

        // 1) update qty temp dari request (key harus row id temp table)
        foreach ($qtyMap as $rowId => $qty) {
            BqDetailTemp::query()
                ->where('temp_id', $tempId)
                ->where('id', (int) $rowId)
                ->where('bqid', $bq->bqid)
                ->where('created_by', $user->username)
                ->update([
                    'qty' => (float) $qty,
                    'updated_by' => $user->username,
                    'updated_at' => now(),
                ]);
        }

        // 2) ambil temp rows
        $tempRows = BqDetailTemp::query()
            ->where('temp_id', $tempId)
            ->where('bqid', $bq->bqid)
            ->where('created_by', $user->username)
            ->orderBy('bq_line_no')
            ->get();

        if ($tempRows->isEmpty()) {
            return back()->withInput()->with('error', 'Detail Kontrak masih kosong. Pilih Category dulu.');
        }

        if ($tempRows->where('qty', '>', 0)->count() === 0) {
            return back()->withInput()->with('error', 'Qty masih 0 semua. Isi minimal 1 item.');
        }

        DB::transaction(function () use ($user, $bq, $tempRows, $tempId) {
            // delete detail lama
            BqDetail::query()
                ->where('bqid', $bq->bqid)
                ->delete();

            // insert detail baru dari temp
            foreach ($tempRows as $t) {
                BqDetail::create([
                    'bqid' => $bq->bqid,
                    'sppjtid' => $bq->sppjtid,
                    'bq_no' => $t->bq_line_no,
                    'bq_line_no' => $t->bq_line_no,
                    'bq_descr' => $t->bq_descr,
                    'qty' => $t->qty,
                    'uom' => $t->uom,

                    'est_material_price' => 0,
                    'total_est_material_price' => 0,
                    'est_jasa_price' => 0,
                    'total_est_jasa_price' => 0,

                    'bqtype' => 'Kontrak',
                    'kontrakcategory' => $t->kontrakcategory,
                    'kontrak_bq_id' => $t->kontrak_bq_id,
                    'kontrak_bq_type' => $t->kontrak_bq_type,
                    'kontrak_duration_qty' => $t->kontrak_duration_qty,

                    'status' => 'P',
                    'created_by' => $user->username,
                    'created_at' => now(),
                    'updated_by' => $user->username,
                    'updated_at' => now(),
                ]);
            }

            // update header (tanpa buat bqid baru)
            Bq::query()->where('id', $bq->id)->update([
                'updated_by' => $user->username,
                'updated_at' => now(),
            ]);

            // clear temp sesi ini
            BqDetailTemp::query()
                ->where('temp_id', $tempId)
                ->where('created_by', $user->username)
                ->delete();
        });

        return redirect()->route('bqkontrak.show', $eid)->with('success', 'BQ Kontrak berhasil diupdate');
    }

    private function attachItrPdf(string $itrId, string $sppjId, string $cpnyId, string $deptId, string $createdBy): void
    {
        $header = TrItrecommend::where('docid', $itrId)->first();
        if (!$header) {
            \Log::warning('attachItrPdf (SPPJ): ITR not found', ['itrId' => $itrId]);
            return;
        }

        $details   = TrItrecommendDetail::where('docid', $itrId)->get();
        $approvals = TrApproval::where('refnbr', $itrId)
            ->where('status', '<>', 'X')
            ->orderByRaw('CAST(aprv_leveling AS NUMERIC)')
            ->orderBy('id')
            ->get();
        $attachments = [];

        $pdfContent = \PDF::loadView(
            'pages.it_recommendation.pdf_it_recommendation',
            compact('header', 'details', 'approvals', 'attachments')
        )->setPaper('a4', 'portrait')->output();

        app(TrAttachmentController::class)->uploadFromContent([
            'refnbr'        => $sppjId,
            'doctype'       => 'PJ',
            'base_folder'   => 'att-purchasing-app/pj',
            'created_by'    => $createdBy,
            'cpny_id'       => $cpnyId,
            'department_id' => $deptId,
        ], $pdfContent, "IT-RECOMMENDATION-{$itrId}", 'pdf');
    }

    private function attachTicketPdf(string $ticketId, string $sppjId, string $cpnyId, string $deptId, string $createdBy): void
    {
        $ticket = TrTicket::with([
            'type', 'category', 'subcategory', 'priority', 'location', 'subLocation',
        ])->where('ticketid', $ticketId)->first();

        if (!$ticket) {
            \Log::warning('attachTicketPdf (SPPJ): Ticket not found', ['ticketId' => $ticketId]);
            return;
        }

        $responseActivity = TrTicketActivity::where('ticketid', $ticketId)
            ->where('status_pekerjaan', 'RESPONSE')
            ->orderBy('id')
            ->first();

        $respondedBy = $responseActivity?->created_by ?? $ticket->pic_ticket;
        $attachments = [];

        $pdfContent = \PDF::loadView(
            'pages.ticket.print',
            compact('ticket', 'attachments', 'respondedBy')
        )->setPaper('a4', 'portrait')->output();

        app(TrAttachmentController::class)->uploadFromContent([
            'refnbr'        => $sppjId,
            'doctype'       => 'PJ',
            'base_folder'   => 'att-purchasing-app/pj',
            'created_by'    => $createdBy,
            'cpny_id'       => $cpnyId,
            'department_id' => $deptId,
        ], $pdfContent, "TICKET-{$ticketId}", 'pdf');
    }
}
