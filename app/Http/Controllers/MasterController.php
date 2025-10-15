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
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BqDetailTempImport; 

class MasterController extends Controller
{

    public function InventoryList(Request $request)
    {
        $type    = strtoupper($request->get('type', 'STOCK')); // STOCK | NONSTOCK | JASA | ALL
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
        if ($type === 'STOCK') {
            $query->where('item_sub_type', 'STOCK');
        } else {
            // semua selain STOCK
            $query->where('item_sub_type', '<>', 'STOCK');
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

}
