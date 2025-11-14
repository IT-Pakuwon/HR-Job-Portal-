<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\MsInventoryPG;
use App\Models\MsInventoryStockPG;
use App\Models\MsRequestType;
use App\Models\MsLocationPG;
use App\Models\MsSubLocationPG;
use App\Models\DepartmentFin;
use App\Models\Autonbr;
use App\Models\BudgetDetail;
use App\Models\Budget;
use App\Models\MsUom;
use App\Models\TrSPPJ;
use App\Models\TrSPPJdetail;
use App\Models\Bq;
use App\Models\BqDetail;
use App\Models\BqDetailTemp;
use App\Models\Attachment;
use App\Models\MsKendaraan;
use App\Models\User;
use App\Models\MsTenant;
use App\Models\MsVendor;
use App\Models\MsTax;
use App\Models\MsCategory;
use App\Models\MsWorktype;
use App\Models\MsSubworktype;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BqDetailTempImport; 
use App\Models\TrWO;
use App\Models\MsWorktypeItem;
use App\Models\MsWorktypeDept;
use App\Models\ViewInventoryAW;
use App\Models\ViewInventoryEPH;
use App\Models\ViewInventoryO8;
use App\Models\ViewInventoryPSA;
use App\Models\ViewInventoryGPS;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;



class MasterController extends Controller
{

    public function InventoryList(Request $request)
    {
        $type    = strtoupper($request->get('type', 'GI')); // STOCK | NONSTOCK | JASA | ALL
        $search  = trim($request->get('search', ''));
        $page    = max((int) $request->get('page', 1), 1);
        $perPage = max((int) $request->get('per_page', 10), 1);

        // Selalu pakai MsInventoryPG
        $query = MsInventoryPG::query()
            ->select(
                'inventoryid',
                'inventory_descr',
                'stock_unit',
                'item_type',               
                'item_category',
                // 'account_id',     // pastikan kolom ada di MsInventoryPG
                'purchase_unit',  // untuk dikirim ke view
                'item_sub_type',                
            );

        // If hanya untuk STOCK, else untuk tipe lain
        if ($type === 'GI') {
            $query->where('item_type', 'GI');
        } else if ($type === 'SE') {
            $query->where('item_type', 'SE');
        } else if ($type === 'NS') {
            $query->where('item_type', 'NS');
        } else {
            // semua selain STOCK dan JASA
            $query->whereNotIn('item_type', ['GI', 'SE']);
        }

        
        // Pencarian (Postgres ILIKE)
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('inventoryid',      'ilike', "%{$search}%")
                ->orWhere('inventory_descr','ilike', "%{$search}%")
                ->orWhere('stock_unit',     'ilike', "%{$search}%")
                ->orWhere('purchase_unit',  'ilike', "%{$search}%");
            });
        }

        $total = (clone $query)->count();

        $rows = $query->orderBy('inventory_descr')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return response()->json([
            'data'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ]);
    }

    public function InventoryByWorktype_zzz(Request $request)
{
    $worktypeid = trim($request->get('worktypeid', ''));
    $cpnyid     = strtoupper(trim($request->get('cpnyid', '')));
    $search     = trim($request->get('search', ''));
    $page       = max((int) $request->get('page', 1), 1);
    $perPage    = min(max((int) $request->get('per_page', 10), 1), 100);

    // 1) Ambil item_class utk worktype + ATK
    $classesQ = \App\Models\MsWorktypeItem::query();
    if ($worktypeid !== '') {
        $classesQ->where(function($q) use ($worktypeid){
            $q->where('worktypeid', $worktypeid)
              ->orWhere('worktypeid', 'ATK');
        });
    } else {
        $classesQ->where('worktypeid', 'ATK');
    }
    $classes = $classesQ->pluck('item_class')
        ->filter(fn($v) => $v !== null && $v !== '')
        ->values();

    if ($classes->isEmpty()) {
        return response()->json([
            'data' => [], 'total' => 0,
            'page' => $page, 'per_page' => $perPage,
            'meta' => ['worktypeid' => $worktypeid, 'cpnyid' => $cpnyid],
        ]);
    }

    // 2) Query PG (tanpa distinct) + filter + paging
    $pg = \App\Models\MsInventoryPG::query()
        ->select([
            'inventoryid','inventory_descr','stock_unit',
            'item_type','item_category','purchase_unit',
            'item_sub_type','item_class'
        ])
        ->whereIn('item_class', $classes);

    if ($search !== '') {
        $pg->where(function ($q) use ($search) {
            $q->where('inventoryid', 'ilike', "%{$search}%")
              ->orWhere('inventory_descr', 'ilike', "%{$search}%")
              ->orWhere('stock_unit', 'ilike', "%{$search}%")
              ->orWhere('purchase_unit', 'ilike', "%{$search}%")
              ->orWhere('item_class', 'ilike', "%{$search}%");
        });
    }

    $totalBase = (clone $pg)->count();

    $pgRows = $pg
        ->orderBy('inventoryid', 'asc')
        ->offset(($page - 1) * $perPage)
        ->limit($perPage)
        ->get();

    if ($pgRows->isEmpty()) {
        return response()->json([
            'data' => [], 'total' => 0,
            'page' => $page, 'per_page' => $perPage,
            'meta' => ['worktypeid' => $worktypeid, 'cpnyid' => $cpnyid],
        ]);
    }

    // 3) Tentukan model View berdasarkan cpnyid
    switch ($cpnyid) {
        case 'AW':  $model = \App\Models\ViewInventoryAW::class;  break;
        case 'EP':  $model = \App\Models\ViewInventoryEPH::class; break;
        case 'O8':  $model = \App\Models\ViewInventoryO8::class;  break;
        case 'PSA': $model = \App\Models\ViewInventoryPSA::class; break;
        case 'GPS': $model = \App\Models\ViewInventoryGPS::class; break;
        default:
            return response()->json([
                'message' => "Unknown cpnyid: {$cpnyid}",
                'data' => [], 'total' => 0
            ], 422);
    }

    // 4) Ambil stok/cost PER SITE dari SQL Server (per invtid)
    $invIds = $pgRows->pluck('inventoryid')->map(fn($v)=>(string)$v)->unique()->values();

    $awRows = $model::query()
        ->selectRaw("
            invtid,
            cpnyid,
            siteid,
            CAST(stock AS float) AS stock,
            CAST(cost  AS float) AS cost
        ")
        ->whereIn('invtid', $invIds)
        ->when($cpnyid !== '', fn($q) => $q->where('cpnyid', $cpnyid))
        ->get();

    // Group: invtid => [baris per site]
    $awGroup = $awRows->groupBy(fn($r) => strtoupper(trim((string)$r->invtid)));

    // 5) Merge: hasil akhir 1 baris per (inventoryid, siteid)
    $final = collect();
    foreach ($pgRows as $r) {
        $key = strtoupper(trim((string)$r->inventoryid));
        $sites = $awGroup->get($key);

        if ($sites && $sites->count()) {
            foreach ($sites as $aw) {
                $final->push((object)[
                    'inventoryid'      => $r->inventoryid,
                    'inventory_descr'  => $r->inventory_descr,
                    'stock_unit'       => $r->stock_unit,
                    'item_type'        => $r->item_type,
                    'item_category'    => $r->item_category,
                    'purchase_unit'    => $r->purchase_unit,
                    'item_sub_type'    => $r->item_sub_type,
                    'item_class'       => $r->item_class,
                    // dari view:
                    'siteid'           => $aw->siteid,
                    'stock'            => $aw->stock,
                    'cost'             => $aw->cost,
                ]);
            }
        } else {
            // tidak ada baris site → tetap kirim 1 baris dengan site null
            $final->push((object)[
                'inventoryid'      => $r->inventoryid,
                'inventory_descr'  => $r->inventory_descr,
                'stock_unit'       => $r->stock_unit,
                'item_type'        => $r->item_type,
                'item_category'    => $r->item_category,
                'purchase_unit'    => $r->purchase_unit,
                'item_sub_type'    => $r->item_sub_type,
                'item_class'       => $r->item_class,
                'siteid'           => null,
                'stock'            => null,
                'cost'             => null,
            ]);
        }
    }

    // Catatan: total sekarang = baris merged (per site) di halaman ini
    // Jika ingin total global akurat per site, perlu hitung di level totalBase dengan query terpisah ke view (lebih mahal).
    $totalMerged = $final->count();

    return response()->json([
        'data'     => $final->values(),
        'total'    => $totalMerged,
        'page'     => $page,
        'per_page' => $perPage,
        'meta'     => ['worktypeid' => $worktypeid, 'cpnyid' => $cpnyid],
    ]);
}


    public function InventoryByWorktype(Request $request)
    {
        $worktypeid = trim($request->get('worktypeid', ''));
        $cpnyid     = strtoupper(trim($request->get('cpnyid', '')));
        $search     = trim($request->get('search', ''));
        $page       = max((int) $request->get('page', 1), 1);
        $perPage    = min(max((int) $request->get('per_page', 10), 1), 100);

        // 1) Ambil item_class utk worktype + ATK
        $classesQ = \App\Models\MsWorktypeItem::query();
        if ($worktypeid !== '') {
            $classesQ->where(function($q) use ($worktypeid){
                $q->where('worktypeid', $worktypeid)
                ->orWhere('worktypeid', 'ATK');
            });
        } else {
            $classesQ->where('worktypeid', 'ATK');
        }
        $classes = $classesQ->pluck('item_class')
            ->filter(fn($v) => $v !== null && $v !== '')
            ->values();

        if ($classes->isEmpty()) {
            return response()->json([
                'data' => [], 'total' => 0,
                'page' => $page, 'per_page' => $perPage,
                'meta' => ['worktypeid' => $worktypeid, 'cpnyid' => $cpnyid],
            ]);
        }

        // 2) Query PG: ambil inventory + paging
        $pg = \App\Models\MsInventoryPG::query()
            ->select([
                'inventoryid','inventory_descr','stock_unit',
                'item_type','item_category','purchase_unit',
                'item_sub_type','item_class'
            ])
            ->whereIn('item_class', $classes);

        if ($search !== '') {
            $pg->where(function ($q) use ($search) {
                $q->where('inventoryid', 'ilike', "%{$search}%")
                ->orWhere('inventory_descr', 'ilike', "%{$search}%")
                ->orWhere('stock_unit', 'ilike', "%{$search}%")
                ->orWhere('purchase_unit', 'ilike', "%{$search}%")
                ->orWhere('item_class', 'ilike', "%{$search}%");
            });
        }

        $total = (clone $pg)->count();

        $rows = $pg->distinct()
            ->groupBy([
                'inventoryid', 'inventory_descr', 'stock_unit',
                'item_type', 'item_category', 'purchase_unit',
                'item_sub_type', 'item_class'
            ])
            ->orderBy('inventoryid', 'asc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();


        // Kalau tidak ada data PG → balikin cepat
        if ($rows->isEmpty()) {
            return response()->json([
                'data' => [], 'total' => 0,
                'page' => $page, 'per_page' => $perPage,
                'meta' => ['worktypeid' => $worktypeid, 'cpnyid' => $cpnyid],
            ]);
        }

        // 3) Ambil stok/cost dari SQL Server berdasarkan invtid & cpnyid
        //    Ganti nama kolom di selectRaw di bawah sesuai HASIL TINKER kamu!
        $invIds = $rows->pluck('inventoryid')->map(fn($v) => (string)$v)->unique()->values();

        // --- Ambil data AW ---
        // $awRows = \App\Models\ViewInventoryAW::on('sqlsrv5')
        //     ->selectRaw("
        //         invtid,
        //         cpnyid,
        //         CAST(stock AS float) AS stock,   
        //         CAST(cost   AS float) AS cost     
        //     ")
        //     ->whereIn('invtid', $invIds)
        //     ->when($cpnyid !== '', fn($q) => $q->where('cpnyid', $cpnyid))
        //     ->get();

        // Tentukan model berdasarkan cpnyid
        switch ($cpnyid) {
            case 'AW':
                $model = \App\Models\ViewInventoryAW::class;
                break;
            case 'EP':
                $model = \App\Models\ViewInventoryEPH::class;
                break;
            case 'O8':
                $model = \App\Models\ViewInventoryO8::class;
                break;
            case 'PSA':
                $model = \App\Models\ViewInventoryPSA::class;
                break;
            case 'GPS':
                $model = \App\Models\ViewInventoryGPS::class;
                break;
            default:
                return response()->json([
                    'message' => "Unknown cpnyid: {$cpnyid}",
                    'data' => [],
                    'total' => 0
                ], 422);
        }

        // 🟢 QUERY STOCK & COST dari SQL Server (dinamis modelnya)
        $awRows = $model::query()
            ->selectRaw("
                invtid,
                cpnyid,
                siteid,                       
                CAST(stock AS float) AS stock,
                CAST(cost  AS float) AS cost
            ")
            ->whereIn('invtid', $invIds)
            ->when($cpnyid !== '', fn($q) => $q->where('cpnyid', $cpnyid))
            ->get();


                // --- GroupBy: key = UPPER(TRIM(invtid)) → bisa >1 row per invtid ---
        $awGroups = $awRows->groupBy(function ($r) {
            return strtoupper(trim((string)$r->invtid));
        });

        // --- Fan-out: 1 inventoryid PG bisa jadi beberapa baris (per siteid) ---
        $expanded = collect();

        foreach ($rows as $r) {
            $key   = strtoupper(trim((string)$r->inventoryid));
            $group = $awGroups->get($key);

            // kalau tidak ada data stok → tetap keluarkan 1 baris (siteid/stock/cost null)
            if (!$group || $group->isEmpty()) {
                $clone = clone $r;
                $clone->stock  = null;
                $clone->cost   = null;
                $clone->siteid = null;
                $expanded->push($clone);
                continue;
            }

            // ada beberapa baris di SQL Server (beda siteid) → keluarkan semua
            foreach ($group as $aw) {
                $clone = clone $r;
                $clone->stock  = $aw->stock;
                $clone->cost   = $aw->cost;
                $clone->siteid = $aw->siteid;
                $expanded->push($clone);
            }
        }

        $rows = $expanded;

                // (opsional) log yang benar-benar membandingkan key hasil normalisasi
        if (config('app.debug')) {
            $pgKeys = $rows->pluck('inventoryid')->map(fn($v) => strtoupper(trim((string)$v)))->unique()->values();
            $awKeys = $awGroups->keys()->values();
            $missing = $pgKeys->reject(fn($k) => $awKeys->contains($k))->values();

            \Log::info('[INV-BY-WORKTYPE-NORM] pgKeys='.json_encode($pgKeys->take(10)).
                ' awKeys='.json_encode($awKeys->take(10)).
                ' missing='.json_encode($missing->take(10)));
        }

        return response()->json([
            'data'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
            'meta'     => ['worktypeid' => $worktypeid, 'cpnyid' => $cpnyid],
        ]);
    }

    public function InventoryByWorktype_xxx(Request $request)
    {        
        
        $worktypeid  = trim($request->get('worktypeid', ''));      // <-- baru
        $search      = trim($request->get('search', ''));
        $page        = max((int) $request->get('page', 1), 1);
        $perPage     = min(max((int) $request->get('per_page', 10), 1), 100);

        $query = MsInventoryPG::query()->select(
            'inventoryid',
            'inventory_descr',
            'stock_unit',
            'item_type',
            'item_category',
            'purchase_unit',
            'item_sub_type',
            'item_class'
        );

        // === Filter by worktype → item_class in (select item_class from ms_worktype_item where worktypeid=?)
        if ($worktypeid !== '') {
            $classes = MsWorktypeItem::query()
                ->where(function ($q) use ($worktypeid) {
                    $q->where('worktypeid', $worktypeid)
                    ->orWhere('worktypeid', 'ATK');    
                })
                ->pluck('item_class')
                ->filter(fn ($c) => $c !== null && $c !== '')
                ->values();

            // kalau worktype tidak memiliki mapping class → kembalikan kosong
            if ($classes->isEmpty()) {
                return response()->json([
                    'data' => [],
                    'total' => 0,
                    'page' => $page,
                    'per_page' => $perPage,
                    'meta' => ['worktypeid' => $worktypeid],
                ]);
            }

            $query->whereIn('item_class', $classes);
        }
       
        // === Search (Postgres ILIKE)
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('inventoryid',       'ilike', "%{$search}%")
                ->orWhere('inventory_descr', 'ilike', "%{$search}%")
                ->orWhere('stock_unit',      'ilike', "%{$search}%")
                ->orWhere('purchase_unit',   'ilike', "%{$search}%")
                ->orWhere('item_class',      'ilike', "%{$search}%");
            });
        }

        $total = (clone $query)->count();

        $rows = $query->orderBy('inventory_descr')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return response()->json([
            'data'      => $rows,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'meta'      => ['worktypeid' => $worktypeid],
        ]);
    }

 
    public function RequestType(Request $request)
    {
        $doctype = $request->query('doctype');
        
        if (!$doctype) {
            return response()->json([
                'message' => 'Parameter "doctype" wajib diisi.'
            ], 400);
        }

        // Opsional: normalisasi huruf besar
        $doctype = strtoupper(trim($doctype));

        $rows = MsRequestType::query()
            ->select('requesttypeid', 'requesttype_name')
            ->where('doctype', $doctype)
            ->where('status', 'A')
            ->orderBy('requesttype_name')
            ->get();
            
        return response()->json(['data' => $rows]);
    }

    public function Location(Request $request)
    {
        $cpnyid  = $request->get('cpnyid');
        $search  = trim($request->get('search', ''));
        $page    = max((int)$request->get('page', 1), 1);
        $perPage = max((int)$request->get('per_page', 10), 1);

        if (!$cpnyid) {
            return response()->json(['data' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage]);
        }

        // Sesuaikan nama kolom Mslocation kamu:
        // asumsi: cpny_id, location_id, location_name, status
        $q = MslocationPG::query()
            ->where('cpny_id', $cpnyid)
            ->where('status', 'A');

        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('location_id', 'ilike', "%{$search}%")
                  ->orWhere('location_name', 'ilike', "%{$search}%");
            });
        }

        $total = (clone $q)->count();

        $rows = $q->orderBy('location_name')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get(['location_id', 'location_name']);

        return response()->json([
            'data'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ]);
    }

    public function SubLocation(Request $request)
    {
        $cpnyid     = $request->get('cpnyid');
        $location_id = $request->get('location_id');
        $search     = trim($request->get('search', ''));
        $page       = max((int)$request->get('page', 1), 1);
        $perPage    = max((int)$request->get('per_page', 10), 1);

        if (!$cpnyid || !$location_id) {
            return response()->json([
                'data' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage
            ]);
        }

        // SESUAIKAN nama kolom:
        // asumsi: cpny_id, location_id, sub_location_id, sub_location_name, status
        $q = MsSubLocationPG::query()
            ->where('cpny_id', $cpnyid)
            ->where('location_id', $location_id)
            ->where('status', 'A');

        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('sub_location_id', 'ilike', "%{$search}%")
                  ->orWhere('sub_location_name', 'ilike', "%{$search}%");
            });
        }

        $total = (clone $q)->count();

        $rows = $q->orderBy('sub_location_name')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get([
                'sub_location_id', 'sub_location_name'
            ]);

        return response()->json([
            'data'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ]);
    }

    public function DepartmentFin(string $cpny_id)
    {
        // dd("cpny_id: {$cpny_id}");
        // Sesuaikan kolom di model/DB: cpny_id, department_fin_id, department_name
        $departments = DepartmentFin::query()
            ->where('cpny_id', $cpny_id)
            ->orderBy('department_name')
            ->get(['department_fin_id', 'department_name']);

        return response()->json($departments);
    }

    public function CoaBudget(Request $request)
    {
        $cpnyid   = $request->get('cpnyid');
        $deptid   = $request->get('deptid');
        $perpost  = $request->get('perpost'); // ⬅️ ambil perpost (tahun)
        $search   = trim($request->get('search', ''));
        $page     = max((int)$request->get('page', 1), 1);
        $perPage  = max((int)$request->get('per_page', 10), 1);

        if (!$cpnyid || !$deptid) {
            return response()->json([
                'data' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage
            ]);
        }

        // Ambil budget aktif untuk company+dept (dan perpost jika ada)
        $budget = Budget::where('status', 'C')
            ->where('cpny_id', $cpnyid)
            ->where('department_fin_id', $deptid)
            ->when($perpost, function ($q) use ($perpost) {
                $q->where('perpost', $perpost);
            })
            ->first();


        $budgetDetail = BudgetDetail::query()
            ->when($budget, function ($q) use ($budget) {
                $q->where('budget_id', $budget->budget_id);
            })
            ->where('cpny_id', $cpnyid)
            ->where('department_fin_id', $deptid)
            // ->where('status', 'A') // aktifkan jika ada kolom status
            ->when($perpost, function ($q) use ($perpost) {
                $q->where('perpost', $perpost);
            });

        if ($search !== '') {
            $budgetDetail->where(function ($w) use ($search) {
                $w->where('account_id',     'ilike', "%{$search}%")
                ->orWhere('activity_detail','ilike', "%{$search}%")
                ->orWhere('totalbudget',  'ilike', "%{$search}%");
            });
        }

        $total = (clone $budgetDetail)->count();

        $rows = $budgetDetail->orderBy('activity_detail')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get(['account_id', 'activity_id', 'activity_detail', 'totalbudget','business_unit_id','department_fin_id']);

        return response()->json([
            'data'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ]);
    }

    public function CoaBudgetWo(Request $request)
    {
        $woid    = trim($request->get('woid', ''));
        $search  = trim($request->get('search', ''));
        $page    = max((int)$request->get('page', 1), 1);
        $perPage = min(max((int)$request->get('per_page', 10), 1), 100);

        if ($woid === '') {
            return response()->json(['message' => 'WOID is required.'], 422);
        }

        // Ambil 1 row WO (header)
        $wo = TrWO::query()
            ->select([
                'woid',
                'cpny_id',
                'department_id',
                'budget_perpost',
                'budget_account_id',
                'budget_activity_id',
                'budget_activity_descr',
                'budget_business_unit_id',
                'budget_department_fin_id',
                'budget_use', // kalau kamu nanti mau pakai sbg totalbudget
            ])
            ->where('woid', $woid)
            ->first();

        if (!$wo) {
            return response()->json(['message' => 'WO not found.'], 404);
        }

        // Bentukkan 1 baris sesuai struktur frontend
        $row = (object) [
            'account_id'        => $wo->budget_account_id,
            'activity_id'       => $wo->budget_activity_id,
            'activity_detail'   => $wo->budget_activity_descr,
            'business_unit_id'  => $wo->budget_business_unit_id,
            'department_fin_id' => $wo->budget_department_fin_id,
            // pakai null agar UI tetap konsisten; kalau mau dari WO:
            // 'totalbudget'    => (float) $wo->budget_use,
            'totalbudget'       => null,
        ];

        // Karena sumbernya 1 header row, lakukan filter search manual (case-insensitive)
        if ($search !== '') {
            $haystack = implode(' ', [
                (string) $row->account_id,
                (string) $row->activity_id,
                (string) $row->activity_detail,
                (string) $row->business_unit_id,
                (string) $row->department_fin_id,
            ]);
            if (stripos($haystack, $search) === false) {
                return response()->json([
                    'meta'      => [
                        'woid'    => $wo->woid,
                        'cpnyid'  => $wo->cpny_id,
                        'deptid'  => $wo->department_id,
                        'perpost' => $wo->budget_perpost,
                    ],
                    'data'      => [],
                    'total'     => 0,
                    'page'      => $page,
                    'per_page'  => $perPage,
                ]);
            }
        }

        return response()->json([
            'meta'      => [
                'woid'    => $wo->woid,
                'cpnyid'  => $wo->cpny_id,
                'deptid'  => $wo->department_id,
                'perpost' => $wo->budget_perpost,
            ],
            'data'      => [$row],
            'total'     => 1,
            'page'      => 1,
            'per_page'  => $perPage,
        ]);
    }



    public function UomInventory(Request $req)
    {
        $inventoryid = $req->get('inventoryid');
        $search      = trim($req->get('search', ''));
        $page        = max(1, (int)$req->get('page', 1));
        $perPage     = max(1, (int)$req->get('per_page', 10));

        if (!$inventoryid) {
            return response()->json([
                'data' => [],
                'total' => 0,
                'page' => $page,
                'per_page' => $perPage,
            ]);
        }

        $q = MsUom::query()->where('inventoryid', $inventoryid);

        if ($search !== '') {
            $q->where(function($w) use ($search) {
                $w->where('from_unit', 'ilike', "%{$search}%")
                ->orWhere('to_unit', 'ilike', "%{$search}%");
            });
        }

        $total  = $q->count();
        $items  = $q->orderBy('from_unit')->orderBy('to_unit')
                    ->skip(($page-1)*$perPage)->take($perPage)->get([
                        'inventoryid','from_unit','to_unit','unitmultdiv','unitrate'
                    ]);

        return response()->json([
            'data'     => $items,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ]);
    }
    
    public function listKendaraan(Request $request)
    {
        $request->validate([
            'search'   => 'nullable|string',
            'page'     => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:500',
        ]);

        $search  = $request->search ?? '';
        $page    = (int)($request->page ?? 1);
        $perPage = (int)($request->per_page ?? 100);

        $q = MsKendaraan::query();

        if ($search !== '') {
            $q->where(function($w) use ($search) {
                $w->where('no_polisi', 'ILIKE', "%{$search}%")
                  ->orWhere('namakendaraan', 'ILIKE', "%{$search}%")
                  ->orWhere('pemilikkendaraan', 'ILIKE', "%{$search}%");
            });
        }

        $total = (clone $q)->count();
        $rows  = $q->orderBy('no_polisi')
                   ->forPage($page, $perPage)
                   ->get(['no_polisi','namakendaraan','pemilikkendaraan']);

        return response()->json([
            'data'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ]);
    }

    public function tenants_zzz(Request $req)
    {
        $q        = trim($req->get('q', ''));
        $page     = max(1, (int) $req->get('page', 1));
        $perPage  = max(1, min(50, (int) $req->get('per_page', 10)));

        $query = MsTenant::query();

        // Asumsi kolom: tenant (nama), lantai (atau floor), unit (atau unit_no)
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('tenant', 'ILIKE', "%{$q}%")
                  ->orWhere('lantai', 'ILIKE', "%{$q}%")
                  ->orWhere('unit', 'ILIKE', "%{$q}%");
            });
        }

        $total = (clone $query)->count();
        
        $rows = $query
            ->orderBy('tenant')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $data = $rows->map(function ($r) {
            $floor = $r->lantai ?? $r->floor ?? '';
            $unit  = $r->unit ?? $r->unit_no ?? '';
            return [
                'id'         => $r->id,                   // sesuaikan PK
                'text'       => $r->tenant ?? '-',        // nama tenant
                'unit_label' => trim(($floor ? $floor : '') . ($unit ? (' - ' . $unit) : '')),
                'floor'      => $floor,
                'unit'       => $unit,
            ];
        });

        return response()->json([
            'data'  => $data,
            'total' => $total,
            'page'  => $page,
            'per_page' => $perPage,
        ]);
    }

    public function tenants(Request $req)
    {
        $q        = trim($req->get('q', ''));
        $page     = max(1, (int) $req->get('page', 1));
        $perPage  = max(1, min(50, (int) $req->get('per_page', 10)));
        $cpnyid   = $req->get('cpnyid'); // tambahan filter by company
        
        $query = MsTenant::query();

        // if ($cpnyid) {
        //     $query->where('cpny_id', $cpnyid);
        // }
        if ($cpnyid) {
            $query->whereRaw('TRIM(UPPER(cpny_id)) = ?', [strtoupper(trim($cpnyid))]);
        }


        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('store_name', 'ILIKE', "%{$q}%")
                ->orWhere('store_no', 'ILIKE', "%{$q}%")
                ->orWhere('floor_id', 'ILIKE', "%{$q}%")
                ->orWhere('unit_id', 'ILIKE', "%{$q}%");
            });
        }

        $total = (clone $query)->count();
       
        $rows = $query
            ->orderBy('store_name')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $data = $rows->map(function ($r) {
            $floor = $r->floor_id ?? '';
            $unit  = $r->store_no ?? $r->unit_id ?? '';
            return [
                'id'         => $r->id,                  // PK
                'text'       => $r->store_name ?? '-',   // nama tenant
                'unit_label' => trim(($floor ? $floor : '') . ($unit ? (' - ' . $unit) : '')),
                'floor'      => $floor,
                'unit'       => $unit,
                'cpnyid'     => $r->cpny_id,
            ];
        });
       

        return response()->json([
            'data'     => $data,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ]);
    }


    public function users(Request $req)
    {
        $q        = trim($req->get('q', ''));
        $page     = max(1, (int) $req->get('page', 1));
        $perPage  = max(1, min(50, (int) $req->get('per_page', 10)));

        $query = User::query();

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('username', 'like', "%{$q}%");
            });
        }

        $total = (clone $query)->count();

        $rows = $query
            ->orderBy('name')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get(['id', 'name', 'email', 'username']);

        $data = $rows->map(function ($u) {
            return [
                // ⬇️ Select2 value harus sama dengan yang kamu simpan (username)
                'id'        => $u->username,
                // ⬇️ Teks yang ditampilkan (nama lengkap). fallback ke email/username
                'text'      => $u->name ?: ($u->email ?: $u->username),
                // info tambahan kalau perlu
                'user_id'   => $u->id,       // numeric id (opsional)
                'email'     => $u->email,
            ];
        });

        return response()->json([
            'data'      => $data,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
        ]);
    }


    public function users_ZZZ(Request $req)
    {
        $q        = trim($req->get('q', ''));
        $page     = max(1, (int) $req->get('page', 1));
        $perPage  = max(1, min(50, (int) $req->get('per_page', 10)));

        $query = User::query();

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                //   ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('username', 'like', "%{$q}%");
            });
        }

        $total = (clone $query)->count();

        $rows = $query
            ->orderBy('name')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get(['id', 'name', 'email','username']);

        $data = $rows->map(function ($u) {
            return [
                'id'    => $u->id,
                'text'  => $u->name ?? $u->email ?? ('User#'.$u->id),
                'email' => $u->email,
                'username' => $u->username,
            ];
        });

        return response()->json([
            'data'  => $data,
            'total' => $total,
            'page'  => $page,
            'per_page' => $perPage,
        ]);
    }

    public function vendors()
    {
        // Ambil semua vendor (atau tambahkan where status = 'A' dll.)
        return response()->json(
            MsVendor::select('id','vendor_id', 'vendor_name', 'contact_person', 'phone_number', 'vendor_addr1')
                  ->orderBy('vendor_name')
                  ->get()
        );
    }

    public function taxes()
    {
        // Ambil pajak aktif (silakan sesuaikan filter status bila perlu)
        $data = MsTax::select('taxid','taxrate','descr','taxtype','status')
            ->where('status', 'A')   // hilangkan baris ini kalau tak perlu filter
            ->orderBy('taxid')
            ->get();

        return response()->json($data);
    }

    public function sitesWarehouse(Request $request)
    {
        $cpnyId = $request->query('cpny_id');
       
        if (!$cpnyId) {
            return response()->json([
                'ok' => false,
                'message' => 'cpny_id is required'
            ], 422);
        }

        // Query ke ms_site (pgsql)
        $rows = DB::connection('pgsql')
            ->table('ms_site')
            ->select('cpny_id', 'siteid')
            ->where('cpny_id', $cpnyId)
            ->orderBy('siteid')
            ->get();

        return response()->json([
            'ok' => true,
            'data' => $rows
        ]);
    }

     // dropdown untuk wotype & worequest dari MsCategory (doctype='WO')
    public function getCategories(string $categoryid)
    {
       
        $items = MsCategory::query()
            ->where('doctype', 'WO')
            ->where('categoryid', $categoryid) // 'wotype' atau 'worequest'
            ->where('status', 'A')
            ->orderBy('category_name')
            ->get(['category_name as text']); // kita kirim text saja, value akan sama

        // hasil: [{text: 'Maintenance'}, ...]
        return response()->json($items);
    }

    // daftar worktype (opsional: filter by departementid)
    public function getWorktypes(Request $request)
    {
        $dept = $request->query('departementid'); // kalau kamu punya id, kirim via query
        $q = MsWorktype::query()->where('status', 'A');       

        $items = $q->orderBy('worktype_name')
            ->get(['worktypeid as value', 'worktype_name as text']);

        return response()->json($items);
    }

    public function getSubWorktypes(Request $request, string $worktypeid)
    {
        // Ambil doctype dari query (?doctype=WO); default 'WO' untuk safety
        $doctype = $request->query('doctype', 'SPBB');

        $items = MsSubworktype::query()
            ->where('worktypeid', $worktypeid)
            ->where('doctype', $doctype)       // <-- filter doctype
            ->where('status', 'A')
            ->orderBy('subworktype_name')
            ->get(['subworktypeid as value', 'subworktype_name as text']);

        return response()->json($items);
    }


    public function getLocations(string $cpny_id)
    {
        $items = MsLocationPG::query()
            ->where('cpny_id', $cpny_id)
            ->where('status', 'A')
            ->orderBy('location_name')
            ->get(['location_id as value', 'location_name as text']);

        return response()->json($items);
    }

    public function getSubLocations(string $cpny_id, string $location_id)
    {
        $items = MsSubLocationPG::query()
            ->where('cpny_id', $cpny_id)
            ->where('location_id', $location_id)
            ->where('status', 'A')
            ->orderBy('sub_location_name')
            ->get(['sub_location_id as value', 'sub_location_name as text']);

        return response()->json($items);
    }

    
    public function showTenant(Request $req)
    {
        $id = $req->get('id');
        if (!$id) return response()->json(['data' => null]);

        $t = MsTenant::select('unit_id','store_name','floor_id','store_no')
            ->where('unit_id', $id)
            ->first();

        if (!$t) return response()->json(['data' => null]);

        return response()->json([
            'data' => [
                'id'         => $t->unit_id,
                'name'       => $t->store_name,
                'floor'      => $t->floor_id,               // jika perlu label lantai, lihat catatan 3)
                'unit'       => $t->store_no,
                'unit_label' => ($t->floor_id && $t->store_no) ? "{$t->floor_id} - {$t->store_no}" : null,
            ]
        ]);
    }

    public function getWoComplated(Request $request)
    {
        $status        = $request->input('status', 'C');
        $worktypeid    = trim($request->input('worktypeid', ''));
        $subworktypeid = trim($request->input('subworktypeid', ''));
        $search        = trim($request->input('search', ''));
        $page          = max((int) $request->input('page', 1), 1);
        $perPage       = min(max((int) $request->input('per_page', 10), 1), 100);

        $query = TrWO::query()
            ->select('woid', 'wodate', 'created_by', 'department_id')
            ->where('status', $status);

        if ($worktypeid !== '') {
            $query->where('worktypeid', $worktypeid);
        }
        if ($subworktypeid !== '') {
            $query->where('subworktypeid', $subworktypeid);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('woid', 'ILIKE', "%{$search}%")
                ->orWhere('created_by', 'ILIKE', "%{$search}%")
                ->orWhere('department_id', 'ILIKE', "%{$search}%")
                ->orWhere('wodate', 'ILIKE', "%{$search}%");
            });
        }

        $total = $query->count();

        $rows = $query->orderByDesc('wodate')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return response()->json([
            'data'      => $rows,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
        ]);
    }


    public function getWoComplated_xxx(Request $request)
    {
        $status   = $request->input('status', 'C');
        $dept     = trim($request->input('departementid', ''));
        $search   = trim($request->input('search', ''));
        $page     = max((int) $request->input('page', 1), 1);
        $perPage  = min(max((int) $request->input('per_page', 10), 1), 100);

        // base query pakai model
        $query = TrWO::query()
            ->select('woid', 'wodate', 'created_by', 'department_id')
            ->where('status', $status);

        if ($dept !== '') {
            $query->where('department_id', $dept);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('woid', 'ILIKE', "%{$search}%")
                    ->orWhere('created_by', 'ILIKE', "%{$search}%")
                    ->orWhere('department_id', 'ILIKE', "%{$search}%")
                    ->orWhere('wodate', 'ILIKE', "%{$search}%");
            });
        }

        $total = $query->count();

        $rows = $query->orderByDesc('wodate')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return response()->json([
            'data'      => $rows,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
        ]);
    }

    
    


}
