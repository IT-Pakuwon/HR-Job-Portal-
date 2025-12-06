<?php

namespace App\Http\Controllers;

use App\Models\SysRoleMenu;
use App\Models\SysMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SysRoleMenuController extends Controller
{
    public function index()
    {
        $roles = DB::connection('pgsql2')
            ->table('sys_role')
            ->where('status', 'A')
            ->orderBy('role_id')
            ->get(['role_id', 'role_name']);

        $menus = SysMenu::on('pgsql2')
            ->where('status', 'A')
            ->orderBy('menu_id')
            ->get(['menu_id', 'menu_name', 'parent_menu_id']);

        $parentMenus = SysMenu::on('pgsql2')
            ->whereNotNull('parent_menu_id')
            ->distinct()
            ->orderBy('parent_menu_id')
            ->pluck('parent_menu_id');

        return view('pages.role_menus.role_menus', compact('roles', 'menus', 'parentMenus'));
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
            'menu_id'        => 'required|array',
            'parent_menu_id' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();
            $createdRows = [];

            foreach ($request->menu_id as $menuId) {
                $createdRows[] = SysRoleMenu::create([
                    'role_id'        => $request->role_id,
                    'menu_id'        => $menuId,
                    'parent_menu_id' => $request->parent_menu_id ?: null,
                    'status'         => 'A',
                    'created_by'     => $user->username ?? 'system',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $createdRows,
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
        // $id di sini = id row (primary key)
        $firstRow = SysRoleMenu::findOrFail($id);

        // Ambil semua menu untuk role yang sama
        $rows = SysRoleMenu::where('role_id', $firstRow->role_id)->get();

        return response()->json([
            'id'             => $firstRow->id,                 // id baris pertama (untuk URL PUT)
            'role_id'        => $firstRow->role_id,            // role
            'menu_ids'       => $rows->pluck('menu_id'),       // semua menu untuk role ini
            'parent_menu_id' => $firstRow->parent_menu_id,
            'status'         => $firstRow->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'role_id'        => 'required|string|max:50',
            'menu_id'        => 'required|array',
            'parent_menu_id' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();

            // Hapus semua mapping lama untuk role ini
            SysRoleMenu::where('role_id', $request->role_id)->delete();

            // Insert ulang semua menu baru
            foreach ($request->menu_id as $menuId) {
                SysRoleMenu::create([
                    'role_id'        => $request->role_id,
                    'menu_id'        => $menuId,
                    'parent_menu_id' => $request->parent_menu_id ?: null,
                    'status'         => 'A',
                    'updated_by'     => $user->username ?? 'system',
                ]);
            }

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
