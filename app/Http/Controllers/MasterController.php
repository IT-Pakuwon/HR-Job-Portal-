<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MsInventoryPG;
use App\Models\MsInventoryStockPG;
use App\Models\MsRequestType;
use App\Models\MsLocationPG;
use App\Models\MsSubLocationPG;
use App\Models\DepartmentFin;
use App\Models\BudgetDetail;
use App\Models\Budget;

class MasterController extends Controller
{
    public function InventoryList(Request $request)
    {
        // dd($request->all());
        $type    = strtoupper($request->get('type', 'STOCK')); // STOCK | NONSTOCK | JASA | ALL
        $search  = trim($request->get('search', ''));
        $page    = max((int) $request->get('page', 1), 1);
        $perPage = max((int) $request->get('per_page', 10), 1);

        // Gunakan ILIKE utk Postgres, LIKE utk selain itu
        // $driver = config('database.connections.'.config('database.default').'.driver');
        // $LIKE   = $driver === 'pgsql' ? 'ilike' : 'like';

        // === Pilih sumber data ===
        if ($type === 'STOCK') {
            // Ambil dari tabel stok
            $query = MsInventoryStockPG::query()
                ->select('inventoryid', 'inventory_descr', 'stock_unit', 'account_id')
                ->where('item_sub_type', $type);
        } else {
            // Ambil dari master inventory umum
            $query = MsInventoryPG::query()
                ->select('inventoryid', 'inventory_descr', 'stock_unit','item_type','item_category');

            // Filter tipe jika spesifik
            if (in_array($type, ['NONSTOCK', 'JASA'], true)) {
                $query->where('item_sub_type', $type);
            }
            // 'ALL' atau tipe tidak dikenali → tanpa filter item_sub_type
        }

        // Pencarian
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('inventoryid',     'ilike', "%{$search}%")
                ->orWhere('inventory_descr','ilike', "%{$search}%")
                ->orWhere('stock_unit',    'ilike', "%{$search}%");
            });
        }

        // Hitung total & pagination
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
        $cpnyid = $request->get('cpnyid');

        if (!$cpnyid) {
            return response()->json(['data' => []]);
        }
   
        $rows = MsRequestType::query()
            ->select('requesttypeid', 'requesttype_name')
            ->where('cpny_id', $cpnyid)
            ->where('doctype', 'SPPB')
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

}
