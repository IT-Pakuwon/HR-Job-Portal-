<?php

namespace App\Http\Controllers;

use App\Models\SysRoleMenu;
use App\Models\SysMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// kalau kamu punya model SysRole, pakai ini:
// use App\Models\SysRole;

class SysRoleMenuController extends Controller
{
    public function index()
    {
        // Ambil data role & menu untuk dropdown
        // Kalau sudah punya tabel sys_role dengan model SysRole, bisa pakai:
        // $roles = SysRole::where('status', 'A')->orderBy('role_id')->get(['role_id', 'role_name']);

        // sementara asumsikan role list kamu fix / atau ambil manual
        $roles = DB::connection('pgsql2')
            ->table('sys_role')
            ->where('status', 'A')
            ->orderBy('role_id')
            ->get(['role_id', 'role_name']);

        $menus = SysMenu::on('pgsql2')
            ->where('status', 'A')
            ->orderBy('menu_id')
            ->get(['menu_id', 'menu_name', 'parent_menu_id']);

        return view('pages.role_menus.role_menus', compact('roles', 'menus'));
    }

    public function json()
    {
        $data = SysRoleMenu::select([
                'id',
                'role_id',
                'menu_id',
                'parent_menu_id',
                'status',
            ])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'role_id'        => 'required|string|max:50',
            'menu_id'        => 'required|string|max:50',
            'parent_menu_id' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();

            $row = SysRoleMenu::create([
                'role_id'        => $request->role_id,
                'menu_id'        => $request->menu_id,
                'parent_menu_id' => $request->parent_menu_id ?: null,
                'status'         => 'A',
                'created_by'     => $user->username ?? 'system',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $row,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan role menu',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $row = SysRoleMenu::findOrFail($id);

        return response()->json([
            'id'             => $row->id,
            'role_id'        => $row->role_id,
            'menu_id'        => $row->menu_id,
            'parent_menu_id' => $row->parent_menu_id,
            'status'         => $row->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'role_id'        => 'required|string|max:50',
            'menu_id'        => 'required|string|max:50',
            'parent_menu_id' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $row  = SysRoleMenu::findOrFail($id);
            $user = Auth::user();

            $row->update([
                'role_id'        => $request->role_id,
                'menu_id'        => $request->menu_id,
                'parent_menu_id' => $request->parent_menu_id ?: null,
                'updated_by'     => $user->username ?? 'system',
            ]);

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal update role menu',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $row = SysRoleMenu::findOrFail($id);
        $newStatus = request('status'); // 'A' atau 'X'

        $row->update([
            'status'     => $newStatus,
            'updated_by' => Auth::check() ? Auth::user()->username : 'system',
        ]);

        return response()->json([
            'success' => true,
            'status'  => $newStatus,
        ]);
    }
}
