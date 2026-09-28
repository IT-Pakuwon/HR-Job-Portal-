<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\Tenancy\TsTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TsTenantController extends Controller
{
    public function json()
    {
        $tenants = TsTenant::with('floor:id,location_id,floor_name', 'floor.location:id,location_name')
            ->select(['id', 'floor_id', 'tenant_code', 'tenant_name', 'unit_no', 'status'])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $tenants]);
    }

    public function options(Request $request)
    {
        $q = TsTenant::where('status', 'A')->orderBy('tenant_name');

        if ($request->filled('floor_id')) {
            $q->where('floor_id', $request->floor_id);
        }

        return response()->json(['data' => $q->get(['id', 'floor_id', 'tenant_code', 'tenant_name'])]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'floor_id'    => 'required|exists:mysql5.ms_floor,id',
            'tenant_code' => 'required|string|max:50|unique:mysql5.ms_tenant,tenant_code',
            'tenant_name' => 'required|string|max:200',
            'unit_no'     => 'nullable|string|max:50',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $tenant = TsTenant::create([
                'floor_id'    => $request->floor_id,
                'tenant_code' => strtoupper($request->tenant_code),
                'tenant_name' => $request->tenant_name,
                'unit_no'     => $request->unit_no,
                'status'      => 'A',
                'created_by'  => $loginUser->username ?? 'system',
                'created_at'  => now(),
            ]);

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true, 'tenant' => $tenant]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan tenant',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $tenant = TsTenant::with('floor:id,location_id')->findOrFail($id);

        return response()->json([
            'id'          => $tenant->id,
            'floor_id'    => $tenant->floor_id,
            'location_id' => $tenant->floor->location_id ?? null,
            'tenant_code' => $tenant->tenant_code,
            'tenant_name' => $tenant->tenant_name,
            'unit_no'     => $tenant->unit_no,
            'status'      => $tenant->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $tenant = TsTenant::findOrFail($id);

        $request->validate([
            'floor_id'    => 'required|exists:mysql5.ms_floor,id',
            'tenant_code' => ['required', 'string', 'max:50', Rule::unique('mysql5.ms_tenant', 'tenant_code')->ignore($tenant->id)],
            'tenant_name' => 'required|string|max:200',
            'unit_no'     => 'nullable|string|max:50',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $tenant->update([
                'floor_id'    => $request->floor_id,
                'tenant_code' => strtoupper($request->tenant_code),
                'tenant_name' => $request->tenant_name,
                'unit_no'     => $request->unit_no,
                'updated_by'  => $loginUser->username ?? 'system',
                'updated_at'  => now(),
            ]);

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal update tenant',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $tenant = TsTenant::findOrFail($id);
        $newStatus = request('status'); // A / X

        $tenant->update([
            'status'     => $newStatus,
            'updated_by' => Auth::user()->username ?? 'system',
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
