<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MsVplWarehouse;
use App\Models\MsVplWarehouseDept;
use App\Models\MsDepartment;
use App\Models\Usercpny;
use DataTables;

class VplWarehouseSetupController extends Controller
{
    // -------------------------------------------------------
    // PAGE
    // -------------------------------------------------------

    public function index(Request $request)
    {
        $user        = Auth::user();
        $usercpny    = Usercpny::where('username', $user->username)->where('status', 'A')->get();
        $usercpny2   = Usercpny::where('username', $user->username)->where('status', 'A')->first();
        $departments = MsDepartment::where('status', 'A')->orderBy('department_name')->get();

        return view('pages.voucher_product.setup', compact('usercpny', 'usercpny2', 'departments'));
    }

    // -------------------------------------------------------
    // WAREHOUSE
    // -------------------------------------------------------

    public function warehouseJson(Request $request)
    {
        abort_unless($request->ajax(), 404);
        return DataTables::of(MsVplWarehouse::orderBy('cpnyid')->orderBy('whs_id')->get())
            ->addIndexColumn()
            ->addColumn('status_badge', fn ($row) => $row->status === 'A'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Inactive</span>')
            ->rawColumns(['status_badge'])
            ->make(true);
    }

    public function warehouseList(Request $request)
    {
        abort_unless($request->ajax(), 404);
        $query = MsVplWarehouse::where('status', 'A');
        if ($request->filled('cpnyid')) {
            $query->where('cpnyid', $request->cpnyid);
        }
        return response()->json($query->orderBy('whs_id')->get(['id', 'cpnyid', 'whs_id']));
    }

    public function editWarehouse(Request $request, int $id)
    {
        abort_unless($request->ajax(), 404);
        return response()->json(['warehouse' => MsVplWarehouse::findOrFail($id)]);
    }

    public function saveWarehouse(Request $request)
    {
        $username = Auth::user()->username;
        $id       = $request->input('id');

        try {
            if ($id) {
                $whs               = MsVplWarehouse::findOrFail($id);
                $whs->cpnyid       = $request->cpnyid;
                $whs->vp_type      = $request->vp_type ?? null;
                $whs->updated_user = $username;
                $whs->save();
            } else {
                MsVplWarehouse::create([
                    'cpnyid'       => $request->cpnyid,
                    'whs_id'       => strtoupper(trim($request->whs_id)),
                    'vp_type'      => $request->vp_type ?? null,
                    'status'       => 'A',
                    'created_user' => $username,
                ]);
            }
            return response()->json(['message' => 'Warehouse saved successfully.']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to save warehouse',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function toggleWarehouse(Request $request, int $id)
    {
        $whs               = MsVplWarehouse::findOrFail($id);
        $whs->status       = $request->boolean('activate') ? 'A' : 'X';
        $whs->updated_user = Auth::user()->username;
        $whs->save();

        $label = $whs->status === 'A' ? 'activated' : 'deactivated';
        return response()->json(['message' => "Warehouse {$label} successfully."]);
    }

    // -------------------------------------------------------
    // WAREHOUSE DEPT
    // -------------------------------------------------------

    public function warehouseDeptJson(Request $request)
    {
        abort_unless($request->ajax(), 404);
        return DataTables::of(MsVplWarehouseDept::orderBy('cpnyid')->orderBy('whs_id')->get())
            ->addIndexColumn()
            ->addColumn('status_badge', fn ($row) => $row->status === 'A'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Inactive</span>')
            ->rawColumns(['status_badge'])
            ->make(true);
    }

    public function editWarehouseDept(Request $request, int $id)
    {
        abort_unless($request->ajax(), 404);
        return response()->json(['dept' => MsVplWarehouseDept::findOrFail($id)]);
    }

    public function saveWarehouseDept(Request $request)
    {
        $username = Auth::user()->username;
        $id       = $request->input('id');

        try {
            if ($id) {
                $dept                = MsVplWarehouseDept::findOrFail($id);
                $dept->cpnyid        = $request->cpnyid;
                $dept->whs_id        = $request->whs_id;
                $dept->activity_type = $request->activity_type ?? null;
                $dept->department_id = $request->department_id ?? null;
                $dept->vp_type       = $request->vp_type       ?? null;
                $dept->updated_user  = $username;
                $dept->save();
            } else {
                MsVplWarehouseDept::create([
                    'cpnyid'        => $request->cpnyid,
                    'whs_id'        => $request->whs_id,
                    'activity_type' => $request->activity_type ?? null,
                    'department_id' => $request->department_id ?? null,
                    'vp_type'       => $request->vp_type       ?? null,
                    'status'        => 'A',
                    'created_user'  => $username,
                ]);
            }
            return response()->json(['message' => 'Warehouse dept saved successfully.']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to save warehouse dept',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function toggleWarehouseDept(Request $request, int $id)
    {
        $dept               = MsVplWarehouseDept::findOrFail($id);
        $dept->status       = $request->boolean('activate') ? 'A' : 'X';
        $dept->updated_user = Auth::user()->username;
        $dept->save();

        $label = $dept->status === 'A' ? 'activated' : 'deactivated';
        return response()->json(['message' => "Warehouse dept {$label} successfully."]);
    }
}
