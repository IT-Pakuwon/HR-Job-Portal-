<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\Tenancy\TsUserTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TsUserTenantController extends Controller
{
    public function json()
    {
        $userTenants = TsUserTenant::with('tenant:id,tenant_name')
            ->select(['id', 'tenant_id', 'user_name', 'email', 'phone', 'position', 'status'])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $userTenants]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|exists:mysql5.ms_tenant,id',
            'user_name' => 'required|string|max:150',
            'email'     => 'nullable|email|max:150',
            'phone'     => 'nullable|string|max:50',
            'position'  => 'nullable|string|max:100',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $userTenant = TsUserTenant::create([
                'tenant_id' => $request->tenant_id,
                'user_name' => $request->user_name,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'position'  => $request->position,
                'status'    => 'A',
                'created_by' => $loginUser->username ?? 'system',
                'created_at' => now(),
            ]);

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true, 'user_tenant' => $userTenant]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan user tenant',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $userTenant = TsUserTenant::findOrFail($id);

        return response()->json([
            'id'        => $userTenant->id,
            'tenant_id' => $userTenant->tenant_id,
            'user_name' => $userTenant->user_name,
            'email'     => $userTenant->email,
            'phone'     => $userTenant->phone,
            'position'  => $userTenant->position,
            'status'    => $userTenant->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $userTenant = TsUserTenant::findOrFail($id);

        $request->validate([
            'tenant_id' => 'required|exists:mysql5.ms_tenant,id',
            'user_name' => 'required|string|max:150',
            'email'     => 'nullable|email|max:150',
            'phone'     => 'nullable|string|max:50',
            'position'  => 'nullable|string|max:100',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $userTenant->update([
                'tenant_id' => $request->tenant_id,
                'user_name' => $request->user_name,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'position'  => $request->position,
                'updated_by' => $loginUser->username ?? 'system',
                'updated_at' => now(),
            ]);

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal update user tenant',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $userTenant = TsUserTenant::findOrFail($id);
        $newStatus = request('status'); // A / X

        $userTenant->update([
            'status'     => $newStatus,
            'updated_by' => Auth::user()->username ?? 'system',
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
