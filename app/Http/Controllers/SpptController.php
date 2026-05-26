<?php

namespace App\Http\Controllers;

use App\Exports\SpptDetailExport;
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
use App\Models\MsSite;
use App\Models\MsTenant;
use App\Models\SysUserRole;
use App\Models\TrApproval;
use App\Models\TrAttachment;
use App\Models\TrBast;
use App\Models\TrCS;
use App\Models\TrCSdetail;
use App\Models\TrPO;
use App\Models\TrPOdetail;
use App\Models\TrSPPT;
use App\Models\TrSPPTdetail;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Mail;
use PDF;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Vinkla\Hashids\Facades\Hashids;

class SpptController extends Controller
{
    use HasAutonbr;

    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Paksa ke array supaya aman jika nanti multi company/dept
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

        $all = TrSPPT::whereIn('cpny_id', $cpnyIds)
                    ->whereIn('department_id', $deptIds)
                    ->count();

        $onProgress = TrSPPT::where('status', 'P')
                    ->whereIn('cpny_id', $cpnyIds)
                    ->whereIn('department_id', $deptIds)
                    ->count();

        $reject = TrSPPT::where('status', 'R')
                    ->whereIn('cpny_id', $cpnyIds)
                    ->whereIn('department_id', $deptIds)
                    ->count();

        $revise = TrSPPT::where('status', 'D')
                    ->whereIn('cpny_id', $cpnyIds)
                    ->whereIn('department_id', $deptIds)
                    ->count();

        $completed = TrSPPT::where('status', 'C')
                    ->whereIn('cpny_id', $cpnyIds)
                    ->whereIn('department_id', $deptIds)
                    ->count();
        $allListCount = TrSPPT::whereIn('cpny_id', $cpnyIds)
            ->whereIn('status', ['P', 'C'])
            ->count();

        return view('pages.sppts.sppts', compact('all', 'onProgress', 'reject', 'revise', 'completed', 'allListCount'));
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

        $baseTable = (new TrSPPT())->getTable();

        $columns = [
            0 => 'sppt.spptid',
            1 => 'sppt.spptdate',
            2 => 'sppt.cpny_id',
            3 => 'sppt.department_id',
            4 => 'rt.requesttype_name',
            5 => 'sppt.keperluan',
            6 => 'sppt.status',
        ];

        $orderIdx = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'sppt.spptid';

        // ==============================
        // BASE QUERY
        // ==============================
        $base = TrSPPT::from($baseTable.' as sppt')
            ->leftJoin('ms_request_type as rt', function ($join) {
                $join->on('rt.requesttypeid', '=', 'sppt.requesttypeid');
            })
            ->whereIn('sppt.cpny_id', $cpnyIds)
            ->where('rt.doctype', 'SPPT');

        // ==============================
        // MODE LOGIC
        // ==============================
        if ($mode === 'normal') {
            $base->whereIn('sppt.department_id', $deptIds);

            if ($status !== '') {
                $base->where('sppt.status', $status);
            }
        }

        if ($mode === 'all') {
            // only P & C
            $base->whereIn('sppt.status', ['P', 'C']);

            if (!empty($deptExtra)) {
                $base->where('sppt.department_id', $deptExtra);
            }

            if ($status !== '') {
                $base->where('sppt.status', $status);
            }
        }

        // ==============================
        // TOTAL BEFORE SEARCH
        // ==============================
        $recordsTotal = (clone $base)
            ->distinct('sppt.spptid')
            ->count('sppt.spptid');

        // ==============================
        // SEARCH FILTER
        // ==============================
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('sppt.spptid', 'ilike', "%{$search}%")
                ->orWhere('sppt.cpny_id', 'ilike', "%{$search}%")
                ->orWhere('sppt.department_id', 'ilike', "%{$search}%")
                ->orWhere('rt.requesttype_name', 'ilike', "%{$search}%")
                ->orWhere('sppt.keperluan', 'ilike', "%{$search}%")
                ->orWhere('sppt.status', 'ilike', "%{$search}%");
            });
        }

        // ==============================
        // TOTAL AFTER SEARCH
        // ==============================
        $recordsFiltered = (clone $base)
            ->distinct('sppt.spptid')
            ->count('sppt.spptid');

        // ==============================
        // FETCH DATA
        // ==============================
        $data = $base->select(
            'sppt.id',
            'sppt.spptid',
            'sppt.spptdate',
            'sppt.cpny_id',
            'sppt.department_id',
            'sppt.requesttypeid',
            'rt.requesttype_name',
            'sppt.keperluan',
            'sppt.status',
            'sppt.created_by'
        )
                ->orderBy($orderCol, $orderDir)
                ->orderBy('sppt.spptid', 'desc')
                ->skip($start)
                ->take($length)
                ->get();

        $data->transform(function ($row) {
            $row->eid = Hashids::encode($row->id);
            unset($row->id);

            return $row;
        });

        // ==============================
        // DEPARTMENT LIST (ALL MODE ONLY)
        // ==============================
        $departments = [];

        if ($mode === 'all') {
            $deptQuery = TrSPPT::from($baseTable.' as sppt')
                ->whereIn('sppt.cpny_id', $cpnyIds)
                ->whereIn('sppt.status', ['P', 'C']);

            if (!empty($deptExtra)) {
                $deptQuery->where('sppt.department_id', $deptExtra);
            }

            $departments = $deptQuery
                ->select('sppt.department_id')
                ->distinct()
                ->orderBy('sppt.department_id')
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

    public function createSppt()
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

        return view('pages.sppts.createsppts', compact('usercpny', 'usercpny2', 'userdept', 'userdept2'));
    }

    public function storeSppt(Request $request)
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
        $inventorySubTypes = $request->input('item_sub_type', []); // untuk Fixed Asset subtype

        $purchaseUnits = $request->input('purchase_unit', []);     // dari hidden purchase_unit[]
        $uomMultDivs = $request->input('uom_unitmultdiv', []);   // 'M' atau 'D'
        $uomRates = $request->input('uom_unitrate', []);      // bisa "12", "12,5", "12.000",

        $doctype = 'PT';
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

        // // pastikan line approval ada
        // $approvalCount = M_approval::where([
        //     ['status', '=', 'A'],
        //     ['aprvcpnyid', '=', $request->cpnyid],
        //     ['aprvdeptid', '=', $request->departementid],
        //     ['aprvdoctype', '=', $doctype],
        // ])->count();

        // if ($approvalCount === 0) {
        //     return response()->json([
        //         'message' => 'Approval line belum di-setup, Please contact IT!',
        //     ], 422);
        // }

        // ===== generate TrApproval dari MsApproval sesuai context =====
        $approvalCtl = app(ApprovalController::class);

        // Pastikan line approval ada (kalau mau validasi awal sebelum simpan detail, panggil loadLines)
        $approvalCtl->loadLines($doctype, $request->cpnyid, $request->departementid);

        DB::beginTransaction();
        try {
            // === generate autonbr & docid (lock) ===
            // $autonbr = Autonbr::lockForUpdate()
            //     ->where('doctype', $doctype)
            //     ->where('year', $year)
            //     ->where('month', $month)
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
            //     $autonbr->update(['number' => $urutan]);
            // }

            // $tglbln = substr($year, 2) . $month;               // YYMM
            // $docid  = $doctype . $tglbln . sprintf("%04d", $urutan);
            // $spptNo = $docid;                                   // atau 'SPPT-'.$docid

            $auto = $this->nextAutonbr(
                $doctype,
                $year,
                $month,
                $username,
                'SPPT'
            );
            $urutan = (int) $auto['next'];

            $tglbln = substr((string) $year, 2).$month;   // YYMM
            $docid = $doctype.$tglbln.sprintf('%04d', $urutan);
            $spptNo = $docid;

            // === 1) header dulu (totalqty sementara 0) ===
            $header = new TrSPPT();
            $header->spptid = $docid;                // PT string
            $header->spptdate = $dt->toDateString();
            $header->cpny_id = $request->input('cpnyid');
            $header->department_id = $request->input('departementid');
            $header->requesttypeid = $request->input('requesttypeid');
            $header->nama_tenant = $request->input('tenant_id');
            $header->no_unit_tenant = $request->input('unit_id');
            $header->pic_pengawas = $request->input('pic_pengawas');
            $header->condition_unit = $request->input('condition_unit');
            $header->beban = $request->input('beban');
            $header->keperluan = $request->input('keperluan');
            $header->budget_perpost = $request->input('perpost');
            $header->woid = $request->input('woid');
            $header->is_urgent = $request->input('is_urgent');
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
            // try {
            //     $defaultSiteId = MsSite::query()
            //         ->where('cpny_id', $request->cpnyid)
            //         ->where(function($q){
            //             $q->where('site_default', true)
            //             ->orWhere('site_default', 'true')
            //             ->orWhere('site_default', 1)
            //             ->orWhere('site_default', '1');
            //         })
            //         ->value('siteid'); // langsung ambil siteid saja
            // } catch (\Throwable $e) {
            //     // optional: log saja, jangan hentikan proses
            //     \Log::warning('Failed to get default site', [
            //         'cpnyid' => $request->cpnyid,
            //         'err' => $e->getMessage(),
            //     ]);
            // }

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

                // $siteFromForm = trim((string)($siteids[$i] ?? ''));
                // $finalSiteId  = $siteFromForm !== '' ? $siteFromForm : $defaultSiteId;

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

                $detail = new TrSPPTdetail();
                $detail->spptid = $docid;
                $detail->sppt_no = $i + 1;   // nomor urut detail
                $detail->inventoryid = $invId;
                $detail->inventory_descr = $productName;
                $detail->siteid = $finalSiteId;
                $detail->qty = $qty;
                $detail->uom = $uom;
                $detail->note = $notes[$i] ?? null;
                $detail->inventory_type = $item_types[$i] ?? null;
                $detail->inventory_sub_type = $inventorySubTypes[$i] ?? null;
                $detail->inventory_category = $item_categories[$i] ?? null;
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
            // $approvals = M_approval::where([
            //     ['status', '=', 'A'],
            //     ['aprvcpnyid', '=', $request->cpnyid],
            //     ['aprvdeptid', '=', $request->departementid],
            //     ['aprvdoctype', '=', $doctype],
            // ])->get();

            // foreach ($approvals as $a) {
            //     T_approval::create([
            //         'docid'          => $docid,
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

            // $firstApprovalUsernames = optional($approvals->first())->aprvusername; // bisa comma-separated
            // if ($firstApprovalUsernames) {
            //     $header->completed_by = $firstApprovalUsernames;
            //     $header->completed_at = $dt; // atau Carbon::now()
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
                'ignore_nominal' => true,   // SPPT diminta tidak cek nominal
                // 'grand_total'           => ...     // tidak dipakai di SPPT
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
            //         $attach->docid = $docid;
            //         $attach->name = $filename;
            //         $attach->attachfile = $attachfile;
            //         $attach->status = 'A';
            //         $attach->extention = $file->getClientOriginalExtension();
            //         $attach->created_user = $user->username;
            //         $attach->save();
            //     }
            // }

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
                    // tidak return di sini!
                } catch (\Throwable $e) {
                    \DB::rollBack();

                    return response()->json([
                        'message' => 'Failed to create PT',
                        'error' => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            } else {
                $uploadResult = null; // tidak ada attachment
            }

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
            //         'docname'  => 'SPPT',
            //         'url'      => url('/showsppts/' . $eid),
            //     ];

            //     $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
            //     $emails = User::whereIn('username', $approvers)
            //         ->where('status', 'A')
            //         ->pluck('notification_email');

            //     foreach ($emails as $email) {
            //         \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data) {
            //             $message->to($email)
            //                 ->subject($data['docid'].' - Waiting Approval SPPT')
            //                 ->from('digitalserver@pakuwon.com', 'Pakuwon System');
            //         });
            //     }
            // }

            $eid = Hashids::encode($header->id);

            $approvalCtl->notifyFirstApprover(
                $docid,
                $doctype,
                $header->status,                 // 'P' | 'R' | 'D' | 'A' | 'C'
                'SPPT',
                url('/showsppts/'.$eid),
                [
                    'info' => $request->keperluan,
                    'createdby' => $header->created_by,
                    'date' => $dt->toDateTimeString(),
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'SPPT created successfully',
                'spptid' => $docid,
                'sppt_no' => $spptNo,
                'totalqty' => $totalQty,
                'attachments' => $uploadResult,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'message' => 'Failed to create SPPT',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function editSppt($hash)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $sppt = TrSPPT::findOrFail($id);

        // ===== Prefill TENANT: pakai MsTenant (unit_id, store_name, floor_id, store_no)
        if (!empty($sppt->nama_tenant)) {
            $tenant = MsTenant::select('unit_id', 'store_name', 'floor_id', 'store_no')
                ->where('unit_id', $sppt->nama_tenant)
                ->first();

            if ($tenant) {
                $sppt->tenant_name = $tenant->store_name; // <-- label yg ditampilkan Select2
                $sppt->no_unit_tenant = trim(
                    ($tenant->floor_id ? $tenant->floor_id : '').
                    ($tenant->store_no ? (' - '.$tenant->store_no) : '')
                );
            }
        }

        // ===== (Opsional) Prefill PIC: ambil nama lengkap utk label Select2 PIC
        if (!empty($sppt->pic_pengawas)) {
            $pic = User::where('username', $sppt->pic_pengawas)
                ->first(['username', 'name as full_name']);
            if ($pic) {
                $sppt->pic_name = $pic->full_name;
            }
        }

        // ===== Detail + eager load lokasi (sudah OK)
        $spptdetail = TrSPPTdetail::with([
            'location:location_id,location_name',
            'subLocation:sub_location_id,sub_location_name',
        ])
            ->where('spptid', $sppt->spptid)
            ->get()
            ->map(function ($d) {
                $d->location_name = optional($d->location)->location_name;
                $d->sub_location_name = optional($d->subLocation)->sub_location_name;

                return $d;
            });

        $detailBuIds = $spptdetail
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

        // Inject ke object $sppt supaya Blade existing tetap jalan
        $sppt->business_unit_id = $selectedBuId;
        $sppt->business_unit_name = $selectedBuName;

        // Optional: log kalau ternyata 1 SPPT punya lebih dari 1 BU di detail
        if ($detailBuIds->count() > 1) {
            \Log::warning('SPPT memiliki lebih dari satu budget_business_unit_id pada detail', [
                'spptid' => $sppt->spptid,
                'budget_business_unit_ids' => $detailBuIds->toArray(),
            ]);
        }

        $user = request()->user();
        $usercpny = Usercpny::where('username', $user->username)->get();
        $usercpny2 = Usercpny::where('username', $user->username)->first();
        $userdept = Userdept::where('username', $user->username)->get();
        $userdept2 = Userdept::where('username', $user->username)->first();

        $rows = TrAttachment::where('refnbr', $sppt->spptid)
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

        return view('pages.sppts.editsppts', compact(
            'sppt', 'spptdetail', 'usercpny', 'usercpny2', 'userdept', 'userdept2', 'attachments', 'hash'
        ));
    }

    public function updateSppt(Request $request, $hash)
    {
        // dd($request->all()); // matikan agar eksekusi lanjut

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404, 'PT tidak ditemukan.');

        $user = $request->user();
        $dt = Carbon::now();
        $year = (int) $dt->year;
        $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();
        $doctype = 'PT';
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

        $header = TrSPPT::findOrFail($id);
        // update header
        $header->cpny_id = $request->cpnyid;
        $header->department_id = $request->departementid;
        $header->requesttypeid = $request->requesttypeid;
        $header->nama_tenant = $request->nama_tenant;
        $header->no_unit_tenant = $request->no_unit_tenant;
        $header->pic_pengawas = $request->pic_pengawas;
        $header->condition_unit = $request->condition_unit;
        $header->beban = $request->beban;
        $header->keperluan = $request->keperluan;
        $header->budget_perpost = $request->perpost;
        $header->woid = $request->woid;
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

        $inventorySubTypes = array_values($request->input('item_sub_type', []));

        // arrays UoM tambahan
        $purchaseUnits = array_values($request->input('purchase_unit', []));      // hidden dari UI
        $uomMultDivs = array_values($request->input('uom_unitmultdiv', []));    // 'M'/'D'
        $uomRates = array_values($request->input('uom_unitrate', []));       // bisa "12.000"

        DB::beginTransaction();

        try {
            // hapus baris yang di-mark delete
            if ($request->filled('deleted_detail_ids')) {
                $idsToDelete = array_filter(array_map('trim', explode(',', $request->deleted_detail_ids)));
                if ($idsToDelete) {
                    TrSPPTdetail::whereIn('id', $idsToDelete)->delete();
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

                // === konversi base_* seperti di  ===
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
                    $detail = TrSPPTdetail::where('id', $idDetail)
                        ->where('spptid', $header->spptid)
                        ->first();
                    if ($detail) {
                        $detail->fill($data)->save();
                    } else {
                        $detail = new TrSPPTdetail($data);
                        $detail->spptid = $header->spptid;
                        $detail->save();
                    }
                } else {
                    $detail = new TrSPPTdetail($data);
                    $detail->spptid = $header->spptid;
                    $detail->save();
                }

                $savedDetails[] = $detail->id;
            }

            // Renumber sppt_no 1..N
            $n = 1;
            foreach ($savedDetails as $did) {
                TrSPPTdetail::where('id', $did)->update(['sppt_no' => $n++]);
            }

            // Hitung total qty (kalau mau pakai base_qty, ganti ke sum('base_qty'))
            $totalQty = TrSPPTdetail::where('spptid', $header->spptid)->sum('qty');
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
            //         'docid'          => $header->spptid,
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
                'ignore_nominal' => true,   // SPPT diminta tidak cek nominal
                // 'grand_total'           => ...     // tidak dipakai di SPPT
            ];

            // Generate TrApproval
            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $header->spptid,
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
            //         $attach->docid = $header->spptid;
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
                    'refnbr' => $header->spptid,
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
                        'message' => 'Failed to update PT',
                        'error' => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            }

            // // email approver pertama (tetap)
            // $firstApproval = T_approval::where('docid', $header->spptid)
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
            //         'docname'  => 'SPPT',
            //         'url'      => url('/showsppts/' . $eid),
            //     ];

            //     $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
            //     $emails = User::whereIn('username', $approvers)
            //         ->where('status', 'A')
            //         ->pluck('notification_email');

            //     foreach ($emails as $email) {
            //         \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data) {
            //             $message->to($email)
            //                 ->subject($data['docid'].' - Waiting Approval SPPT')
            //                 ->from('digitalserver@pakuwon.com', 'Pakuwon System');
            //         });
            //     }
            // }

            $eid = Hashids::encode($header->id);

            $approvalCtl->notifyFirstApprover(
                $header->spptid,
                $doctype,
                $header->status,                 // 'P' | 'R' | 'D' | 'A' | 'C'
                'SPPT',
                url('/showsppts/'.$eid),
                [
                    'info' => $request->keperluan,
                    'createdby' => $header->created_by,
                    'date' => $dt->toDateTimeString(),
                ]
            );

            DB::commit();

            return response()->json(['message' => 'SPPT updated successfully']);
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

    public function showSppt($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // $sppt = TrSPPT::findOrFail($id);
        $sppt = TrSPPT::with([
            'requestType:requesttypeid,requesttype_name',
            'creator:username,name',
            'tenantname:id,store_name',
            'pic:username,name',
        ])
        ->findOrFail($id);

        $spptdetail = TrSPPTdetail::with([
            'location:location_id,location_name',
            'subLocation:sub_location_id,sub_location_name',
        ])
        ->where('spptid', $sppt->spptid)
        ->orderby('sppt_no', 'ASC')
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

        foreach ($spptdetail as $item) {
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

        // $rows = TrAttachment::where('refnbr', $sppt->spptid)
        //     ->where('status', 'A')
        //     ->orderBy('created_at', 'desc')
        //     ->get();

        // // siapkan Signed URL dari GCS
        // $config = config('filesystems.disks.gcs');
        // $keyFilePath = $config['key_file'];
        // if (!Str::startsWith($keyFilePath, ['/','C:\\','D:\\'])) {
        //     $keyFilePath = base_path($keyFilePath);
        // }

        // $storage = new StorageClient([
        //     'projectId'   => $config['project_id'],
        //     'keyFilePath' => $keyFilePath,
        // ]);
        // $bucket = $storage->bucket($config['bucket']);

        // // map jadi data siap pakai di view
        // $attachments = $rows->map(function ($r) use ($bucket) {
        //     $objectPath = rtrim($r->folder, '/').'/'.$r->filename;   // ex: att-purchasing-app/wo/2025/xxxx-file.pdf
        //     $object     = $bucket->object($objectPath);

        //     // Signed URL 10 menit
        //     $signedUrl = null;
        //     try {
        //         $signedUrl = $object->signedUrl(
        //             new \DateTimeImmutable('+10 minutes'),
        //             ['version' => 'v4']
        //         );
        //     } catch (\Throwable $e) {
        //         // kalau gagal signed URL, biarkan null; di UI tampilkan nama saja
        //         \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
        //     }

        //     return (object) [
        //         'display_name' => $r->attachment_name,         // nama yang enak dibaca
        //         'created_by'   => $r->created_by,
        //         'created_at'   => $r->created_at,
        //         'url'          => $signedUrl,                  // bisa null jika gagal
        //         'folder'       => $r->folder,
        //         'filename'     => $r->filename,
        //         'extention'    => $r->extention,
        //         'size'         => $r->filesize,
        //     ];
        // });

        $attachmentPT = $this->mapAttachmentsToSignedUrl($sppt->spptid);

        $attachmentWO = collect();
        if (!empty($sppt->woid)) {
            $attachmentWO = $this->mapAttachmentsToSignedUrl($sppt->woid);
        }

        $bq = Bq::where('bqid', $sppt->bqid)
            ->first();

        if ($bq) {
            $bq->eid = Hashids::encode($bq->id);
        }

        $loginUsername = $user->username ?? $user->name ?? null;
        $canUpload = $sppt->created_by === $loginUsername;
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

        if (!empty($sppt->woid)) {
            $woData = TrWO::select('id', 'woid', 'keperluan')
                ->where('woid', $sppt->woid)
                ->first();

            if ($woData) {
                $woHash = Hashids::encode($woData->id);
            }
        }

        return view('pages.sppts.showsppts', compact('sppt', 'attachmentPT', 'attachmentWO', 'spptdetail', 'bq', 'hash', 'canUpload', 'akses_cc', 'userCpny', 'userBu', 'userDeptFin', 'woData', 'woHash'));
    }

    public function exportDetail($id)
    {
        $sppt = TrSPPT::findOrFail($id);

        $spptdetail = TrSPPTdetail::with([
            'location',
            'subLocation',
        ])
        ->where('spptid', $sppt->spptid)
        ->orderBy('sppt_no', 'ASC')
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

        foreach ($spptdetail as $item) {
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
            new SpptDetailExport($spptdetail),
            'SPPT_Detail_'.$sppt->spptid.'.xlsx'
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

    public function approveSppt(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'PT';

        $sppt = TrSPPT::with('creator')->where('spptid', $docid)->first();
        if (!$sppt) {
            return response()->json(['success' => false, 'message' => 'SPPT not found'], 404);
        }

        $eid = Hashids::encode($sppt->id);
        $docUrl = url('/showsppts/'.$eid);
        $fullname = data_get($sppt, 'creator.name') ?: $sppt->created_by;

        $result = app(ApprovalController::class)->approveStep(
            $sppt->spptid,
            $doctype,
            $user->username,
            $user->name,

            // complete: update header/detail + email creator complete
            function (string $refnbr, \Carbon\Carbon $now) use ($sppt, $fullname, $docUrl) {
                $sppt->status = 'C';
                $sppt->completed_by = $sppt->completed_by ?: auth()->user()->username;
                $sppt->completed_at = $now;
                $sppt->save();

                TrSPPTdetail::where('spptid', $sppt->spptid)->update(['status' => 'C']);

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $sppt->spptid,
                    'SPPT',
                    'C',
                    $sppt->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $sppt->cpny_id ?? $sppt->cpnyid ?? '',
                        'deptname' => $sppt->department_id ?? $sppt->departementid ?? '',
                        'date' => $sppt->spptdate,
                        'info' => $sppt->keperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                    ]
                );
            },

            // notify next approver
            function ($next, \Carbon\Carbon $now) use ($sppt, $docUrl) {
                app(ApprovalController::class)->notifyFirstApprover(
                    $sppt->spptid,
                    'PT',
                    'P',
                    'SPPT',
                    $docUrl,
                    [
                        'info' => $sppt->keperluan,
                        'createdby' => $sppt->created_by,
                        'date' => $now->toDateTimeString(),
                    ]
                );

                // jejak terakhir diproses (optional)
                $sppt->completed_by = auth()->user()->username;
                $sppt->completed_at = $now;
                $sppt->save();
            }
        );

        if (!$result['ok']) {
            return response()->json(['success' => false, 'message' => $result['message'] ?? 'Approve failed'], 403);
        }

        return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    }

    public function rejectSppt(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'PT';

        $sppt = TrSPPT::with('creator')->where('spptid', $docid)->first();
        if (!$sppt) {
            return response()->json(['success' => false, 'message' => 'SPPT not found'], 404);
        }

        $eid = Hashids::encode($sppt->id);
        $docUrl = url('/showsppts/'.$eid);
        $fullname = data_get($sppt, 'creator.name') ?: $sppt->created_by;

        $result = app(ApprovalController::class)->rejectStep(
            $sppt->spptid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($sppt, $fullname, $docUrl) {
                $sppt->status = 'R';
                $sppt->completed_by = auth()->user()->username;
                $sppt->completed_at = $now;
                $sppt->save();

                // optional: tandai detail R
                // \App\Models\TrSPPTdetail::where('spptid', $sppt->spptid)->update(['status' => 'R']);

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $sppt->spptid,
                    'SPPT',
                    'R',
                    $sppt->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $sppt->cpny_id ?? $sppt->cpnyid ?? '',
                        'deptname' => $sppt->department_id ?? $sppt->departementid ?? '',
                        'date' => $now->toDateString(),
                        'info' => $sppt->keperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                    ]
                );

                // simpan komentar (jika ada)
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($sppt->id, 'PT', request());
                } catch (\Throwable $e) {
                }
            }
        );

        if (!$result['ok']) {
            return response()->json(['success' => false, 'message' => $result['message'] ?? 'Reject failed'], 403);
        }

        return response()->json(['success' => true, 'message' => 'SPPT rejected successfully']);
    }

    public function reviseSppt(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'PT';

        $sppt = TrSPPT::with('creator')->where('spptid', $docid)->first();
        if (!$sppt) {
            return response()->json(['success' => false, 'message' => 'SPPT not found'], 404);
        }

        $eid = Hashids::encode($sppt->id);
        $docUrl = url('/showsppts/'.$eid);
        $fullname = data_get($sppt, 'creator.name') ?: $sppt->created_by;

        $result = app(ApprovalController::class)->reviseStep(
            $sppt->spptid,            // refnbr
            $doctype,                 // PT
            $user->username,          // actor
            $user->name,              // actor
            function (string $refnbr, \Carbon\Carbon $now) use ($sppt, $fullname, $docUrl) {
                // === HEADER SPPT -> D ===
                $sppt->status = 'D';
                $sppt->completed_by = auth()->user()->username;
                $sppt->completed_at = $now;
                $sppt->save();

                // (opsional) DETAIL -> D
                // \App\Models\TrSPPTdetail::where('spptid', $sppt->spptid)->update(['status' => 'D']);

                // === Email ke requester ===
                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $sppt->spptid,
                    'SPPT',
                    'D',
                    $sppt->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $sppt->cpny_id ?? $sppt->cpnyid ?? '',
                        'deptname' => $sppt->department_id ?? $sppt->departementid ?? '',
                        'date' => $now->toDateString(),
                        'info' => $sppt->keperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,   // <<< tambahkan ini
                    ]
                );

                // === Simpan komentar (jika ada) ===
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($sppt->id, 'PT', request());
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

        return response()->json(['success' => true, 'message' => 'SPPT revised successfully']);
    }

    // public function approveSppt(Request $request, $docid)
    // {
    //     $now  = Carbon::now();
    //     $user = $request->user();

    //     // $sppt = TrSPPT::where('spptid', $docid)->first();
    //     $sppt = TrSPPT::with('creator')
    //         ->where('spptid', $docid)
    //         ->first();
    //     $fullname = data_get($sppt, 'creator.name') ?: $sppt->created_by;

    //     if (!$sppt) {
    //         return response()->json(['success' => false, 'message' => 'SPPT not found'], 404);
    //     }

    //     // pastikan user memang approver aktif (status P) di doc ini
    //     $tApproval = T_approval::where('docid', $sppt->spptid)
    //         ->where('status', 'P')
    //         ->where('aprvusername', 'ilike', "%{$user->username}%")
    //         ->whereNotNull('aprvdatebefore')
    //         ->orderBy('aprvid', 'ASC')
    //         ->first();

    //     if (!$tApproval) {
    //         return response()->json(['success' => false, 'message' => "You can't approve!"], 403);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // Set current approver -> Approved
    //         $tApproval->status         = 'A';
    //         $tApproval->aprvdateafter  = $now;
    //         $tApproval->aprvusername   = $user->username;
    //         $tApproval->name           = $user->name;
    //         $tApproval->save();

    //         // Update header informasi "terakhir diproses"
    //         $sppt->completed_by = $user->username;
    //         $sppt->completed_at = $now;
    //         $sppt->save();

    //         // Hitung sisa pending setelah approve ini
    //         $pendingCount = T_approval::where('docid', $sppt->spptid)
    //             ->where('status', 'P')
    //             ->count();

    //         // Pemetaan judul sesuai status
    //         $subjectMap = [
    //             'P' => 'Waiting Approval',
    //             'R' => 'Rejected Approval',
    //             'D' => 'Revise Approval',
    //             'A' => 'Approved',
    //             'C' => 'Completed',
    //         ];

    //         $eid = Hashids::encode($sppt->id);

    //         if ($pendingCount === 0) {
    //             // Tidak ada approver lagi -> dokumen complete
    //             $sppt->status       = 'C';
    //             $sppt->completed_by = $user->username;
    //             $sppt->completed_at = $now;
    //             $sppt->save();

    //             $spptdetail = TrSPPTdetail::where('spptid', $sppt->spptid)
    //                 ->get();

    //             foreach ($spptdetail as $d) {
    //                 $d->status = 'C';
    //                 $d->save();
    //             }

    //             // Kirim email ke requester (creator)
    //             $status        = 'C';
    //             $subjectSuffix = $subjectMap[$status] ?? 'Notification';

    //             $data = [
    //                 'docid'     => $sppt->spptid,
    //                 'cpnyid'    => $sppt->cpny_id ?? $sppt->cpnyid ?? '',
    //                 'deptname'  => $sppt->department_id ?? $sppt->departementid ?? '',
    //                 'date'      => $sppt->spptdate,
    //                 'fullname'  => $fullname,  // nama penerima di email
    //                 'name'      => $fullname,  // fallback
    //                 'createdby' => $fullname,
    //                 'docname'   => 'SPPT',
    //                 'info'      => $sppt->keperluan,
    //                 'status'    => $status,
    //                 'url'       => url('/showsppts/' . $eid),
    //             ];

    //             $recipients = User::where('username', $sppt->created_by)
    //                 ->where('status', 'A')
    //                 ->get();

    //             foreach ($recipients as $rcp) {
    //                 try {
    //                     Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $rcp, $subjectSuffix) {
    //                         $to = $rcp->notification_email ?? $rcp->email; // pakai field yang memang ada
    //                         $message->to($to)
    //                             ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPPT')
    //                             ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //                     });
    //                 } catch (\Throwable $e) {
    //                     Log::error('Failed sending SPPT completion email', ['error' => $e->getMessage()]);
    //                 }
    //             }
    //         } else {
    //             // Masih ada approver berikutnya -> cari level berikutnya (P terrendah aprvid)
    //             $next = T_approval::where('docid', $sppt->spptid)
    //                 ->where('status', 'P')
    //                 ->orderBy('aprvid', 'ASC')
    //                 ->first();

    //             if ($next) {
    //                 // Stempel "datebefore" untuk approver berikutnya
    //                 $next->aprvdatebefore = $now;
    //                 $next->save();

    //                 // Kirim email ke semua username yang ada di kolom aprvusername (dipisah koma)
    //                 $status        = 'P';
    //                 $subjectSuffix = $subjectMap[$status] ?? 'Notification';

    //                 $data = [
    //                     'docid'     => $next->docid,
    //                     'cpnyid'    => $next->aprvcpnyid,
    //                     'deptname'  => $next->aprvdeptid,
    //                     'date'      => $next->aprvdatebefore,
    //                     'fullname'  => $next->name,
    //                     'name'      => $next->name,
    //                     'createdby' => $sppt->created_by,
    //                     'docname'   => 'SPPT',
    //                     'info'      => $sppt->keperluan,
    //                     'status'    => $status,
    //                     'url'       => url('/showsppts/' . $eid),
    //                 ];

    //                 $usernames = array_filter(array_map('trim', explode(',', (string) $next->aprvusername)));
    //                 if (!empty($usernames)) {
    //                     $recipients = User::whereIn('username', $usernames)
    //                         ->where('status', 'A')
    //                         ->get();

    //                     foreach ($recipients as $rcp) {
    //                         try {
    //                             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $rcp, $subjectSuffix) {
    //                                 $to = $rcp->notification_email ?? $rcp->email;
    //                                 $message->to($to)
    //                                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPPT')
    //                                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //                             });
    //                         } catch (\Throwable $e) {
    //                             Log::error('Failed sending SPPT waiting-approval email', ['error' => $e->getMessage()]);
    //                         }
    //                     }
    //                 } else {
    //                     Log::warning('Next approver has empty aprvusername list', ['docid' => $sppt->spptid]);
    //                 }
    //             }
    //         }

    //         DB::commit();
    //         return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Approve SPPT failed', ['error' => $e->getMessage()]);
    //         return response()->json(['success' => false, 'message' => 'Approve failed'], 500);
    //     }
    // }

    // public function rejectSppt(Request $request, $docid)
    // {
    //     $now  = Carbon::now();
    //     $user = $request->user();

    //     // $sppt = TrSPPT::where('spptid', $docid)->first();
    //     $sppt = TrSPPT::with('creator')
    //         ->where('spptid', $docid)
    //         ->first();
    //     $fullname = data_get($sppt, 'creator.name') ?: $sppt->created_by;

    //     if (!$sppt) {
    //         return response()->json(['success' => false, 'message' => 'Task not found'], 404);
    //     }

    //     // Validasi: user harus approver aktif (status P) pada dokumen ini
    //     $tApproval = T_approval::where('docid', $sppt->spptid)
    //         ->where('status', 'P')
    //         ->where('aprvusername', 'ilike', "%{$user->username}%")
    //         ->whereNotNull('aprvdatebefore')
    //         ->orderBy('aprvid', 'ASC')
    //         ->first();

    //     if (!$tApproval) {
    //         return response()->json(['success' => false, 'message' => "You can't reject!"], 403);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // Tandai approval saat ini sebagai Rejected
    //         $tApproval->status        = 'R';
    //         $tApproval->aprvdateafter = $now;
    //         $tApproval->aprvusername  = $user->username; // catat siapa yang reject
    //         $tApproval->name          = $user->name;
    //         $tApproval->save();

    //         // Update header SPPT
    //         $sppt->status       = 'R';
    //         $sppt->completed_by = $user->username;
    //         $sppt->completed_at = $now;
    //         $sppt->save();

    //         // Batalkan semua approval yang masih pending
    //         T_approval::where('docid', $sppt->spptid)
    //             ->where('status', 'P')
    //             ->update(['status' => 'X']);

    //         DB::commit();
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Reject SPPT failed', ['docid' => $docid, 'error' => $e->getMessage()]);
    //         return response()->json(['success' => false, 'message' => 'Reject failed'], 500);
    //     }

    //     // === Kirim Email ke requester (creator) ===
    //     $status = 'R'; // Rejected
    //     $subjectMap = [
    //         'P' => 'Waiting Approval',
    //         'R' => 'Rejected Approval',
    //         'D' => 'Revise Approval',
    //         'A' => 'Approved',
    //         'C' => 'Completed',
    //     ];
    //     $subjectSuffix = $subjectMap[$status] ?? 'Notification';

    //     $eid = Hashids::encode($sppt->id);

    //     $data = [
    //         'docid'     => $sppt->spptid,
    //         'cpnyid'    => $sppt->cpny_id ?? $sppt->cpnyid ?? '',
    //         'deptname'  => $sppt->department_id ?? $sppt->departementid ?? '',
    //         'date'      => $now->toDateString(),            // bisa juga pakai $tApproval->aprvdateafter
    //         'fullname'  => $fullname,               // view email kita pakai $fullname
    //         'name'      => $fullname,               // fallback jika view pakai $name
    //         'createdby' => $fullname,
    //         'docname'   => 'SPPT',
    //         'info'      => $sppt->keperluan,
    //         'status'    => $status,
    //         'url'       => url('/showsppts/' . $eid),
    //     ];

    //     $recipients = User::where('username', $sppt->created_by)
    //         ->where('status', 'A')
    //         ->get();

    //     foreach ($recipients as $rcp) {
    //         try {
    //             $to = $rcp->notification_email ?? $rcp->email; // sesuaikan field yang tersedia
    //             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    //                 $message->to($to)
    //                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPPT')
    //                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //             });
    //         } catch (\Throwable $e) {
    //             Log::error('Failed sending SPPT rejected email', [
    //                 'docid' => $data['docid'],
    //                 'to'    => $rcp->username,
    //                 'error' => $e->getMessage()
    //             ]);
    //         }
    //     }

    //     // Simpan komentar penolakan (jika ada)
    //     try {
    //         app('App\Http\Controllers\SendCommentController')
    //             ->sendmsg($sppt->id, 'PT', $request);
    //     } catch (\Throwable $e) {
    //         Log::warning('SendComment after reject failed', [
    //             'docid' => $sppt->spptid,
    //             'error' => $e->getMessage()
    //         ]);
    //     }

    //     return response()->json(['success' => true, 'message' => 'SPPT rejected successfully']);
    // }

    // public function reviseSppt(Request $request, $docid)
    // {
    //     $now  = Carbon::now();
    //     $user = $request->user();

    //     // $sppt = TrSPPT::where('spptid', $docid)->first();
    //     $sppt = TrSPPT::with('creator')
    //         ->where('spptid', $docid)
    //         ->first();
    //     $fullname = data_get($sppt, 'creator.name') ?: $sppt->created_by;

    //     if (!$sppt) {
    //         return response()->json(['success' => false, 'message' => 'SPPT not found'], 404);
    //     }

    //     // Pastikan user adalah approver aktif (status P) dokumen ini
    //     $tApproval = T_approval::where('docid', $sppt->spptid)
    //         ->where('status', 'P')
    //         ->where('aprvusername', 'ilike', "%{$user->username}%")
    //         ->whereNotNull('aprvdatebefore')
    //         ->orderBy('aprvid', 'ASC')
    //         ->first();

    //     if (!$tApproval) {
    //         return response()->json(['success' => false, 'message' => "You can't revise!"], 403);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // Tandai approval saat ini sebagai Revise (D)
    //         $tApproval->status        = 'D';
    //         $tApproval->aprvdateafter = $now;
    //         $tApproval->aprvusername  = $user->username;  // catat siapa yang revise
    //         $tApproval->name          = $user->name;
    //         $tApproval->save();

    //         // Update header SPPT
    //         $sppt->status       = 'D';
    //         $sppt->completed_by = $user->username;        // mengikuti pola existing
    //         $sppt->completed_at = $now;
    //         $sppt->save();

    //         // Batalkan approval lain yang masih pending
    //         T_approval::where('docid', $sppt->spptid)
    //             ->where('status', 'P')
    //             ->update(['status' => 'X']);

    //         DB::commit();
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Revise SPPT failed', ['docid' => $docid, 'error' => $e->getMessage()]);
    //         return response()->json(['success' => false, 'message' => 'Revise failed'], 500);
    //     }

    //     // === Kirim email ke requester (creator) ===
    //     $status = 'D'; // Revise
    //     $subjectMap = [
    //         'P' => 'Waiting Approval',
    //         'R' => 'Rejected Approval',
    //         'D' => 'Revise Approval',
    //         'A' => 'Approved',
    //         'C' => 'Completed',
    //     ];
    //     $subjectSuffix = $subjectMap[$status] ?? 'Notification';

    //     $eid = Hashids::encode($sppt->id);

    //     $data = [
    //         'docid'     => $sppt->spptid,
    //         'cpnyid'    => $sppt->cpny_id ?? $sppt->cpnyid ?? '',
    //         'deptname'  => $sppt->department_id ?? $sppt->departementid ?? '',
    //         'date'      => $now->toDateString(),          // atau $tApproval->aprvdateafter
    //         'fullname'  => $fullname,             // template email pakai $fullname
    //         'name'      => $fullname,             // fallback jika view pakai $name
    //         'createdby' => $fullname,
    //         'docname'   => 'SPPT',
    //         'info'      => $sppt->keperluan,
    //         'status'    => $status,
    //         'url'       => url('/showsppts/' . $eid),
    //     ];

    //     $recipients = User::where('username', $sppt->created_by)
    //         ->where('status', 'A')
    //         ->get();

    //     foreach ($recipients as $rcp) {
    //         try {
    //             $to = $rcp->notification_email ?? $rcp->email; // sesuaikan dengan kolom yang ada
    //             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    //                 $message->to($to)
    //                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPPT')
    //                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //             });
    //         } catch (\Throwable $e) {
    //             Log::error('Failed sending SPPT revise email', [
    //                 'docid' => $data['docid'],
    //                 'to'    => $rcp->username,
    //                 'error' => $e->getMessage()
    //             ]);
    //         }
    //     }

    //     // Simpan komentar revisi (jika ada)
    //     try {
    //         app('App\Http\Controllers\SendCommentController')
    //             ->sendmsg($sppt->id, 'PT', $request);
    //     } catch (\Throwable $e) {
    //         Log::warning('SendComment after revise failed', [
    //             'docid' => $sppt->spptid,
    //             'error' => $e->getMessage()
    //         ]);
    //     }

    //     return response()->json(['success' => true, 'message' => 'SPPT revised successfully']);
    // }

    // public function checkApproval($id, $action)
    // {
    //     $user = Auth::user(); // Ambil user yang login
    //     // dd($action);
    //     // Query dasar untuk pengecekan
    //     $query = T_approval::where('docid', $id)
    //                 ->where('aprvusername', 'ilike', '%' . $user->username . '%')
    //                 ->where('status', 'P');

    //     // Jika aksi adalah reject atau revise, pastikan aprvdatebefore tidak null
    //     if (in_array($action, ['reject', 'revise','approve'])) {
    //         $query->whereNotNull('aprvdatebefore');
    //     }

    //     // Cek apakah user bisa melakukan aksi
    //     $canPerformAction = $query->exists();

    //     return response()->json(['canPerformAction' => $canPerformAction]);
    // }

    public function trackingDetail($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $sppt = TrSPPT::findOrFail($id);
        $spptNo = $sppt->spptid;

        $fmt = fn ($dt) => $dt ? Carbon::parse($dt)->format('Y-m-d H:i') : null;

        // ✅ sesuai request: approved jika status = 'C'
        $approved = fn ($h) => $h ? (strtoupper((string) $h->status) === 'C') : false;

        // ===== SPPT DETAIL =====
        $spptDetails = TrSPPTdetail::query()
            ->where('spptid', $spptNo)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        // ===== LIST CS (ALL) =====
        $csList = TrCS::query()
            ->where('sppbjktid', $spptNo) // <-- relasi ke SPPT
            ->whereNull('deleted_at')
            ->orderBy('csdate', 'desc')
            ->get(['csid', 'csdate', 'status', 'completed_by', 'completed_at']);

        $selCsNo = optional($csList->first())->csid;

        // ===== LIST PO (ALL) =====
        $poList = TrPO::query()
            ->where('sppbjktid', $spptNo) // <-- relasi ke SPPT
            ->whereNull('deleted_at')
            ->orderBy('podate', 'desc')
            ->get(['ponbr', 'podate', 'status', 'csid', 'completed_by', 'completed_at']);

        $selPoNo = optional($poList->first())->ponbr;

        // ===== LIST BAST (ALL) =====
        // ⚠️ asumsi: tr_bast.sppbjktid = spptid
        $bastList = TrBast::query()
            ->where('sppbjktid', $spptNo)
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
            ->where('cpny_id', $sppt->cpny_id)
            ->whereNull('deleted_at')->first()
            : null;

        $poDetails = $selPoNo
            ? TrPOdetail::where('ponbr', $selPoNo)
            ->where('budget_cpny_id', $sppt->cpny_id)
            ->whereNull('deleted_at')->orderBy('id')->get()
            : collect();

        // ---- BAST (header only, no detail) ----
        $bastHeader = $selBastNo
            ? TrBast::where('bastid', $selBastNo)->whereNull('deleted_at')->first()
            : null;

        $lastApprSppj = $this->getLastApprovalInfo($spptNo);
        $lastApprCs = $selCsNo ? $this->getLastApprovalInfo($selCsNo) : null;
        $lastApprBast = $selBastNo ? $this->getLastApprovalInfo($selBastNo) : null;

        return response()->json([
            'doc' => $spptNo,

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

            'sppt' => [
                'header' => [
                    'doc' => $sppt->spptid,
                    'date' => $fmt($sppt->spptdate),
                    'cpny_id' => $sppt->cpny_id,
                    'department_id' => $sppt->department_id,
                    'keperluan' => $sppt->keperluan,
                    'status' => $sppt->status,
                    'created_by' => $sppt->created_by,
                    'created_at' => $fmt($sppt->created_at),
                    'completed_by' => $sppt->completed_by,
                    'completed_at' => $fmt($sppt->completed_at),
                    'is_approved' => $approved($sppt),
                    'last_approval' => $lastApprSppj,
                    'approval_list' => $this->getApprovalList($spptNo),

                ],
                'details' => $spptDetails,
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

    public function trackingDetailItem($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $sppt = TrSPPT::findOrFail($id);
        $spptNo = $sppt->spptid;

        $type = request('type'); // cs|po|bast
        $doc = request('doc');  // csid / ponbr / bastid
        abort_if(!in_array($type, ['cs', 'po', 'bast'], true), 400);
        abort_if(!$doc, 400);

        $fmt = fn ($dt) => $dt ? Carbon::parse($dt)->format('Y-m-d H:i') : null;
        $approved = fn ($h) => $h ? (strtoupper((string) $h->status) === 'C') : false;

        if ($type === 'cs') {
            $h = TrCS::where('csid', $doc)
                ->where('sppbjktid', $spptNo)
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
                    'is_approved' => $approved($h),
                    'last_approval' => $this->getLastApprovalInfo($h->csid),
                ] : null,
                'details' => $details,
            ]);
        }

        if ($type === 'po') {
            $h = TrPO::where('ponbr', $doc)
                ->where('sppbjktid', $spptNo)
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
            ->where('sppbjktid', $spptNo)
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

        $sppt = TrSPPT::findOrFail($id);

        $getName = function (?string $username) {
            if (!$username) {
                return null;
            }
            $u = User::where('username', $username)->first();

            return $u->name ?? $username;
        };

        $createdByName = $getName($sppt->created_by ?? null);
        $createdAt = $sppt->created_at ? \Carbon\Carbon::parse($sppt->created_at)->format('Y-m-d H:i') : null;

        $completedByName = $getName($sppt->completed_by ?? null);
        $completedAt = $sppt->completed_at ? \Carbon\Carbon::parse($sppt->completed_at)->format('Y-m-d H:i') : null;

        // kolom opsional, kalau tidak ada biarkan null
        $rejectedByName = $getName($sppt->rejected_by ?? null);
        $rejectedAt = isset($sppt->rejected_at) ? \Carbon\Carbon::parse($sppt->rejected_at)->format('Y-m-d H:i') : null;

        $revisedByName = $getName($sppt->revised_by ?? null);
        $revisedAt = isset($sppt->revised_at) ? \Carbon\Carbon::parse($sppt->revised_at)->format('Y-m-d H:i') : null;

        $status = (string) ($sppt->status ?? '');
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
            'title' => 'SPPT',
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
            'doc' => $sppt->spptid ?? (string) $sppt->id,
            'steps' => $steps,
            'status' => $status,
            'status_label' => $statusLabel,
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
        ->whereIn('aprv_leveling', ['1', '1.00'])
        ->where('status', 'P')
        ->whereNotNull('aprv_datebefore')
        ->exists();

    // 2) Approver level 1 boleh edit jika user termasuk approver
    $canApproveEdit = TrApproval::where('refnbr', $bq->sppjtid)
        ->whereIn('aprv_leveling', ['1', '1.00'])
        ->where('status', 'P')
        ->whereNotNull('aprv_datebefore')
        ->where(function ($q) use ($loginUsername) {
            $u = $loginUsername;

            $q->where('aprv_username', $u)
                ->orWhere('aprv_username', 'ilike', $u . ',%')
                ->orWhere('aprv_username', 'ilike', '%,' . $u . ',%')
                ->orWhere('aprv_username', 'ilike', '%,' . $u);
        })
        ->exists();

    // 3) Creator boleh edit hanya jika approval level 1 MASIH EXIST
    $isCreator = $bq->created_by === $loginUsername;
    $canCreatorEdit = $isCreator && $approvalLevel1Exists;

    // 4) Final
    $canEdit = $canApproveEdit || $canCreatorEdit;

    $bqdetail = BqDetail::where('bqid', $bq->bqid)
        ->orderByRaw("
            CASE 
                WHEN bq_line_no ~ '^[0-9]+$' THEN 0
                ELSE 1
            END ASC
        ")
        ->orderByRaw("
            CASE 
                WHEN bq_line_no ~ '^[0-9]+$' THEN bq_line_no::int
                ELSE NULL
            END ASC
        ")
        ->orderBy('bq_line_no', 'ASC')
        ->get();

    return view('pages.sppts.showbqsppts', compact('bq', 'bqdetail', 'canEdit', 'hash'));
}

    public function showBQ_xxx($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // $sppt = TrSPPT::findOrFail($id);
        $bq = Bq::with([
            'creator:username,name',
        ])
        ->findOrFail($id);

        $canEdit = TrApproval::where('refnbr', $bq->sppjtid)
             ->where('status', 'P')
             ->whereNotNull('aprv_datebefore')
             ->where(function ($q) use ($user) {
                 $u = $user->username;

                 $q->where('aprv_username', $u)
                 ->orWhere('aprv_username', 'ilike', $u.',%')
                 ->orWhere('aprv_username', 'ilike', '%,'.$u.',%')
                 ->orWhere('aprv_username', 'ilike', '%,'.$u);
             })
             ->exists();
        // dd( $canEdit);
        $bqdetail = BqDetail::where('bqid', $bq->bqid)
            ->get();

        return view('pages.sppts.showbqsppts', compact('bq', 'bqdetail', 'canEdit', 'hash'));
    }

    public function showBQ_zzz($hash)
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

        // 2) Approver level 1 boleh edit jika user termasuk approver
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

        // 4) Final
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

        return view('pages.sppts.showbqsppts', compact('bq', 'bqdetail', 'canEdit', 'hash'));
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

        return view('pages.sppts.editbqsppts', compact(
            'bq',
            'bq_detail',
            'temp_id',
            'tempData'
        ));
    }

    public function printSppt($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Ambil SPPT + relasi yang dibutuhkan
        $sppt = TrSPPT::with([
            'requestType:requesttypeid,requesttype_name',
            'creator:username,name',
        ])
            ->findOrFail($id);

        // Detail baris SPPT
        $spptdetail = TrSPPTdetail::with([
            'location:location_id,location_name',
            'subLocation:sub_location_id,sub_location_name',
        ])
            ->where('spptid', $sppt->spptid)
            ->orderBy('sppt_no', 'ASC')
            ->get();

        $refnbr = $sppt->spptid;
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
        $company = MsCompany::where('cpny_id', $sppt->cpny_id)->first();

        // Mapping status dokumen
        switch ($sppt->status) {
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
            'title' => ' Surat Permintaan Pekerjaan Tenant',
            'doc_type' => 'SPPT',
            'docid' => $sppt->spptid,
            'department_id' => $sppt->department_id,
            'cpnyname' => optional($company)->cpny_name,
            'parent' => optional($company)->parent,
            'project' => optional($company)->project,
            // identitas & tanggal
            'created_by_username' => $sppt->created_by,
            'created_by_name' => ucwords(strtolower(optional($sppt->creator)->name)),
            'created_at_fmt' => optional($sppt->created_at)->format('d F Y'),
            'req_date_fmt' => optional($sppt->created_at)->format('d M Y H:i'),
            'spptdate' => \Carbon\Carbon::parse($sppt->spptdate)->format('d F Y'),
            // konten
            'bqid' => $sppt->bqid,
            'nama_tenant' => optional($sppt->tenantname)->tenant,
            'no_unit_tenant' => $sppt->no_unit_tenant,
            'pic_pengawas' => ucwords(strtolower(optional($sppt->pic)->name)),
            'condition_unit' => $sppt->condition_unit,
            'beban' => $sppt->beban,
            'keperluan' => $sppt->keperluan,
            'status_doc' => $status_doc,
            'requesttype_name' => optional($sppt->requestType)->requesttype_name,
        ];

        // Kirim ke view
        $pdf = \PDF::loadView(
            'pages.sppts.pdf_sppts',
            array_merge($data, [
                'detail' => $spptdetail,
                'approval' => $approval,
                'approve_count' => $approve_count,
            ])
        );

        // Portrait jika <= 5 approver, else landscape
        $pdf->setPaper('A4', ($approve_count <= 5) ? 'portrait' : 'landscape');

        return $pdf->stream("pdf_sppts_{$sppt->spptid}.pdf");
    }

    public function createBQ($id)
    {
        $user = request()->user();
        $sppt = TrSPPT::findOrFail($id);

        $temp_id = session('import_temp_id'); // ambil dari session

        $tempData = [];
        if ($temp_id) {
            $tempData = BqDetailTemp::where('temp_id', $temp_id)->get();
        }

        return view('pages.sppts.createbqsppt', compact('sppt', 'tempData', 'temp_id'));
    }

    public function importCreate(Request $request)
    {
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
            $ext = strtolower($file->getClientOriginalExtension());

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

                            if ($cell->isFormula()) {
                                throw new \RuntimeException("Import gagal: file Excel mengandung rumus pada sheet '{$sheet->getTitle()}' cell {$col}{$row}. ".'Silakan ubah menjadi nilai (Copy → Paste Values).');
                            }

                            if (is_string($raw) && str_starts_with(ltrim($raw), '=')) {
                                throw new \RuntimeException("Import gagal: file Excel mengandung rumus pada sheet '{$sheet->getTitle()}' cell {$col}{$row}. ".'Silakan Paste Values lalu import ulang.');
                            }
                        }
                    }
                }
            }

            // Import Excel ke tr_bq_detail_temp
            Excel::import(
                new BqDetailTempImport($temp_id, $sppjtid),
                $file
            );

            // Simpan temp_id ke session untuk dipakai di halaman create
            session(['import_temp_id' => $temp_id]);

            return redirect()
                ->route('bqsppt.create', $idx)
                ->with('success', 'Data berhasil di-import.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal import: '.$e->getMessage());
        }
    }

    public function importEdit(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'sppjtid' => 'required',
        ]);

        try {
            $username = Auth::user()->username ?? 'system';
            $temp_id = (string) Str::uuid();

            // Bersihkan temp milik user agar tidak tercampur batch sebelumnya
            BqDetailTemp::where('created_by', $username)->delete();

            $idx = $request->input('idx');
            $sppjtid = $request->input('sppjtid');

            // =========================
            // ✅ VALIDASI: TOLAK FORMULA
            // =========================
            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension());

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

                            if ($cell->isFormula()) {
                                throw new \RuntimeException("Import gagal (edit mode): file Excel mengandung rumus pada sheet '{$sheet->getTitle()}' cell {$col}{$row}. ".'Silakan ubah menjadi nilai (Copy → Paste Values).');
                            }

                            if (is_string($raw) && str_starts_with(ltrim($raw), '=')) {
                                throw new \RuntimeException("Import gagal (edit mode): file Excel mengandung rumus pada sheet '{$sheet->getTitle()}' cell {$col}{$row}. ".'Silakan Paste Values lalu import ulang.');
                            }
                        }
                    }
                }
            }

            // Import Excel ke tr_bq_detail_temp
            Excel::import(
                new BqDetailTempImport($temp_id, $sppjtid),
                $file
            );

            // Simpan temp_id ke session untuk dipakai di halaman edit
            session(['import_temp_id' => $temp_id]);

            return redirect()
                ->route('bqsppt.edit', $idx)
                ->with('success', 'Data berhasil di-import (edit mode).');
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
                ->route('bqsppt.create', $idx)
                ->with('success', 'Data berhasil di-import.');
        } catch (\Throwable $e) {
            // opsional: report($e);
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
                 ->route('bqsppt.edit', $idx)
                 ->with('success', 'Data berhasil di‑import (edit mode).');
            //  return $idx
            //     ? redirect()->route('bqsppt.edit', $idx)
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

        $sppjtid = $tempHead->sppjtid ?? $request->input('sppjtid'); // string SPPTID (mis. SPPT-xxxxx)
        $bq_type = $request->input('bq_type', 'SPPT'); // default

        // Ambil cpny_id dari SPPT (kalau kolom BQ wajib)
        $cpny_id = null;
        if ($sppjtid) {
            $sppt = TrSPPT::where('spptid', $sppjtid)
                        ->orWhere('id', $request->input('idx')) // kalau kamu kirim idx juga
                        ->first();
            $cpny_id = $sppt->cpny_id ?? $sppt->cpnyid ?? null;
            $deptid = $sppt->department_id ?? $sppt->departmentid ?? null;
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

            $sppt->bqid = $bqid;
            $sppt->save();

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

            // ===== Attachments (optional): simpan ke /public/attachments/{year} =====

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

        $sppt = TrSPPT::where('spptid', $sppjtid)
                    ->first();
        $cpny_id = $sppt->cpny_id ?? $sppt->cpnyid ?? null;
        $deptid = $sppt->department_id ?? $sppt->departmentid ?? null;

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

            // Optional: update cpny_id dari SPPT jika ingin sinkron lagi (bisa di-skip)
            // $cpny_id = $bq->cpny_id;
            // if ($sppjtid) {
            //     $sppj    = TrSPPT::where('sppjid', $sppjtid)->first();
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

            // ===================== ATTACHMENTS (tambahan) =====================
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

    public function cancelSppt(Request $request, string $hash)
    {
        // decode hash -> id (sesuaikan kalau tidak pakai Hashids)
        $decoded = Hashids::decode($hash);
        abort_if(empty($decoded), 404, 'Invalid document');

        $id = $decoded[0];

        // ambil doc
        $sppb = TrSPPT::query()->where('id', $id)->firstOrFail();

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
        // $bqdetail = BqDetail::where('bqid', $bq->bqid)
        //     ->orderByRaw('bq_line_no::int ASC')
        //     ->get();
        $bqdetail = BqDetail::where('bqid', $bq->bqid)
        ->orderByRaw("
            CASE 
                WHEN bq_line_no ~ '^[0-9]+$' THEN 0
                ELSE 1
            END ASC
        ")
        ->orderByRaw("
            CASE 
                WHEN bq_line_no ~ '^[0-9]+$' THEN bq_line_no::int
                ELSE NULL
            END ASC
        ")
        ->orderBy('bq_line_no', 'ASC')
        ->get();


        $sppt = TrSPPT::where('spptid', $bq->sppjtid)
            ->first();

        $company = MsCompany::where('cpny_id', $bq->cpny_id)->first();

        $data = [
            'title' => 'Bills of Quantities (BQ)',
            'doc_type' => 'BQ',
            'cpny_id' => $company->cpny_id,
            'cpny_name' => $company->cpny_name,
            'keperluan' => $sppt->keperluan,
        ];

        // Kirim ke view
        $pdf = \PDF::loadView(
            'pages.sppts.pdfbq_sppt',
            array_merge($data, [
                'bq' => $bq,
                'bqdetail' => $bqdetail,
            ])
        );

        // Portrait jika <= 5 approver, else landscape
        $pdf->setPaper('A4');

        return $pdf->stream("pdfbq_sppt_{$bq->bqid}.pdf");
    }
}
