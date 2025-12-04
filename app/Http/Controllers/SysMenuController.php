<?php

namespace App\Http\Controllers;

use App\Models\SysMenu;
use App\Models\SysRoleMenu;        // ✨ NEW
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SysScreen;
use App\Models\SysApplication;

class SysMenuController extends Controller
{
    public function index()
    {
        // Untuk dropdown parent menu
        $parentMenus = SysMenu::on('pgsql2')
            ->where('status', 'A')
            ->orderBy('menu_id')
            ->get(['menu_id', 'menu_name']);

        // Dropdown Screen ID
        $screens = SysScreen::on('pgsql2')
            ->where('status', 'A')
            ->orderBy('screen_id')
            ->get(['screen_id', 'screen_name', 'application_id']);

        // Dropdown Application ID
        $applications = SysApplication::on('pgsql2')
            ->where('status', 'A')
            ->orderBy('application_id')
            ->get(['application_id', 'application_name']);

        return view('pages.menus.menus', compact('parentMenus', 'screens', 'applications'));
    }

    public function json()
    {
        $menus = SysMenu::select([
                'id',
                'menu_id',
                'parent_menu_id',
                'menu_name',
                'menu_route',
                'menu_url',
                'menu_icon',
                'menu_sort_order',
                'screen_id',
                'application_id',
                'status',
            ])
            ->orderBy('parent_menu_id')
            ->orderBy('menu_sort_order')
            ->orderBy('menu_id')
            ->get();

        return response()->json(['data' => $menus]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'menu_id'        => 'required|string|max:50|unique:pgsql2.sys_menu,menu_id',
            'menu_name'      => 'required|string|max:200',
            'parent_menu_id' => 'nullable|string|max:50',
            'menu_route'     => 'nullable|string|max:200',
            'menu_url'       => 'nullable|string|max:255',
            'menu_icon'      => 'nullable|string',
            'menu_sort_order'=> 'nullable|integer',
            'screen_id'      => 'nullable|string|max:100',
            'application_id' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();
            $username = $user->username ?? 'system';

            // 1) SIMPAN MENU
            $menu = SysMenu::create([
                'menu_id'        => strtoupper($request->menu_id),
                'parent_menu_id' => $request->parent_menu_id ?: null,
                'menu_name'      => $request->menu_name,
                'menu_route'     => $request->menu_route,
                'menu_url'       => $request->menu_url,
                'menu_icon'      => $request->menu_icon,
                'menu_sort_order'=> $request->menu_sort_order ?? 0,
                'screen_id'      => $request->screen_id,
                'application_id' => $request->application_id,
                'status'         => 'A',
                'created_by'     => $username,
            ]);

            // 2) AUTO-GENERATE BARIS DI sys_role_menu UNTUK SEMUA ROLE AKTIF ✨ NEW
            //    (status awal dibuat X = non-aktif, nanti di-maintain dari layar Role Menu)
            $roleIds = DB::connection('pgsql2')
                ->table('sys_role')
                ->where('status', 'A')
                ->pluck('role_id');

            foreach ($roleIds as $roleId) {
                SysRoleMenu::create([
                    'role_id'        => $roleId,
                    'menu_id'        => $menu->menu_id,
                    'parent_menu_id' => $menu->parent_menu_id,
                    'status'         => 'A',        // default non-aktif
                    'created_by'     => $username,
                ]);
            }

            DB::commit();

            return response()->json(['success' => true, 'data' => $menu]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan menu',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $menu = SysMenu::findOrFail($id);

        return response()->json([
            'id'             => $menu->id,
            'menu_id'        => $menu->menu_id,
            'parent_menu_id' => $menu->parent_menu_id,
            'menu_name'      => $menu->menu_name,
            'menu_route'     => $menu->menu_route,
            'menu_url'       => $menu->menu_url,
            'menu_icon'      => $menu->menu_icon,
            'menu_sort_order'=> $menu->menu_sort_order,
            'screen_id'      => $menu->screen_id,
            'application_id' => $menu->application_id,
            'status'         => $menu->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $menu = SysMenu::findOrFail($id);

        $request->validate([
            'menu_id'        => 'required|string|max:50|unique:pgsql2.sys_menu,menu_id,' . $menu->id,
            'menu_name'      => 'required|string|max:200',
            'parent_menu_id' => 'nullable|string|max:50',
            'menu_route'     => 'nullable|string|max:200',
            'menu_url'       => 'nullable|string|max:255',
            'menu_icon'      => 'nullable|string',
            'menu_sort_order'=> 'nullable|integer',
            'screen_id'      => 'nullable|string|max:100',
            'application_id' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();

        try {
            $user     = Auth::user();
            $username = $user->username ?? 'system';

            // SIMPAN MENU ID LAMA (kalau di-edit)
            $oldMenuId        = $menu->menu_id;

            // 1) UPDATE TABEL sys_menu
            $newMenuId        = strtoupper($request->menu_id);
            $newParentMenuId  = $request->parent_menu_id ?: null;

            $menu->update([
                'menu_id'        => $newMenuId,
                'parent_menu_id' => $newParentMenuId,
                'menu_name'      => $request->menu_name,
                'menu_route'     => $request->menu_route,
                'menu_url'       => $request->menu_url,
                'menu_icon'      => $request->menu_icon,
                'menu_sort_order'=> $request->menu_sort_order ?? 0,
                'screen_id'      => $request->screen_id,
                'application_id' => $request->application_id,
                'updated_by'     => $username,
            ]);

            // 2) SYNC KE sys_role_menu ✨ NEW
            //    - Kalau menu_id diubah → ikut update
            //    - parent_menu_id juga di-sync
            SysRoleMenu::where('menu_id', $oldMenuId)->update([
                'menu_id'        => $newMenuId,
                'parent_menu_id' => $newParentMenuId,
                'updated_by'     => $username,
            ]);

            DB::commit();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update menu',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $menu = SysMenu::findOrFail($id);
        $newStatus = request('status'); // 'A' atau 'X'
        $username  = Auth::check() ? Auth::user()->username : 'system';

        DB::beginTransaction();
        try {
            // 1) Update status di sys_menu
            $menu->update([
                'status'     => $newStatus,
                'updated_by' => $username,
            ]);

            // 2) Sync status di sys_role_menu untuk menu_id tersebut
            SysRoleMenu::where('menu_id', $menu->menu_id)
                ->update([
                    'status'     => $newStatus,
                    'updated_by' => $username,
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'status'  => $newStatus,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update status menu & role menu',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

}
