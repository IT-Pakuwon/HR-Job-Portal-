<?php

namespace App\Http\Controllers;

use App\Models\SysRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SysRoleController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        
        return view('pages.roles.roles');
    }

    public function json()
    {
        $roles = SysRole::select([
                'id',
                'role_id',
                'role_name',
                'status',
            ])
            ->orderBy('role_id')
            ->get();

        // DataTables kita pakai format { data: [...] }
        return response()->json(['data' => $roles]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'role_id'   => 'required|string|max:50|unique:pgsql2.sys_role,role_id',
            'role_name' => 'required|string|max:200',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();

            $role = SysRole::create([
                'role_id'    => strtoupper($request->role_id),
                'role_name'  => $request->role_name,
                'status'     => 'A',
                'created_by' => $user->username ?? 'system',
                'created_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $role,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan role',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $role = SysRole::findOrFail($id);

        return response()->json([
            'id'        => $role->id,
            'role_id'   => $role->role_id,
            'role_name' => $role->role_name,
            'status'    => $role->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $role = SysRole::findOrFail($id);

        $request->validate([
            'role_id'   => 'required|string|max:50|unique:pgsql2.sys_role,role_id,' . $role->id,
            'role_name' => 'required|string|max:200',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();

            $role->update([
                'role_id'    => strtoupper($request->role_id),
                'role_name'  => $request->role_name,
                'updated_by' => $user->username ?? 'system',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update role',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $role = SysRole::findOrFail($id);
        $newStatus = request('status'); // 'A' atau 'X'
        $username  = Auth::check() ? Auth::user()->username : 'system';

        $role->update([
            'status'     => $newStatus,
            'updated_by' => $username,
        ]);

        return response()->json([
            'success' => true,
            'status'  => $newStatus,
        ]);
    }
}
