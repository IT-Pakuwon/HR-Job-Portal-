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

        // Usage counts so admins can see a role's blast radius at a glance.
        $userCounts = DB::connection('pgsql2')->table('sys_user_role')
            ->where('status', 'A')->selectRaw('role_id, count(*) c')->groupBy('role_id')->pluck('c', 'role_id');
        $menuCounts = DB::connection('pgsql2')->table('sys_role_menu')
            ->where('status', 'A')->selectRaw('role_id, count(*) c')->groupBy('role_id')->pluck('c', 'role_id');
        $roles->each(function ($r) use ($userCounts, $menuCounts) {
            $r->setAttribute('user_count', (int) ($userCounts[$r->role_id] ?? 0));
            $r->setAttribute('menu_count', (int) ($menuCounts[$r->role_id] ?? 0));
        });

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

            \App\Services\AccessAuditLogger::log('role', $role->role_id, 'create', "Created role {$role->role_id} ({$role->role_name})");

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
            $before = $role->only(['role_id', 'role_name']);

            $role->update([
                'role_id'    => strtoupper($request->role_id),
                'role_name'  => $request->role_name,
                'updated_by' => $user->username ?? 'system',
            ]);

            DB::commit();

            $diff = [];
            foreach ($before as $k => $v) {
                if ((string) $v !== (string) $role->{$k}) $diff[$k] = ['from' => $v, 'to' => $role->{$k}];
            }
            if ($diff) {
                \App\Services\AccessAuditLogger::log('role', $role->role_id, 'update', 'Updated role ' . $role->role_id . ' (' . implode(', ', array_keys($diff)) . ')', $diff);
            }

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

        \App\Services\AccessAuditLogger::log('role', $role->role_id, $newStatus === 'A' ? 'enable' : 'disable',
            ($newStatus === 'A' ? 'Enabled' : 'Disabled') . " role {$role->role_id} ({$role->role_name})");

        return response()->json([
            'success' => true,
            'status'  => $newStatus,
        ]);
    }
}
