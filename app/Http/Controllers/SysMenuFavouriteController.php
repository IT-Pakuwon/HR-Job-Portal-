<?php

namespace App\Http\Controllers;

use App\Models\SysMenu;
use App\Models\SysMenuFavourite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class SysMenuFavouriteController extends Controller
{
    public function index()
    {
        $username = Auth::user()->username;
        $allowedKeys = SysMenu::allowedScreenKeysForUsername($username);

        $favourites = DB::connection('pgsql2')
            ->table('sys_menu_favourite as f')
            ->join('sys_menu as m', function ($join) {
                $join->on('m.screen_id', '=', 'f.screen_id')
                    ->on('m.application_id', '=', 'f.application_id');
            })
            ->leftJoin('sys_menu as p', 'p.menu_id', '=', 'm.parent_menu_id')
            ->where('f.username', $username)
            ->orderBy('f.menu_order')
            ->select([
                'f.id',
                'f.screen_id',
                'f.application_id',
                'f.menu_order',
                'm.menu_name',
                'm.menu_route',
                'm.menu_url',
                'm.menu_icon',
                'p.menu_name as parent_name',
            ])
            ->get()
            ->filter(fn ($row) => $allowedKeys->contains($row->screen_id . '|' . $row->application_id))
            ->values()
            ->map(function ($row) {
                $row->url = ($row->menu_route && Route::has($row->menu_route))
                    ? route($row->menu_route)
                    : ($row->menu_url ?: '#');

                return $row;
            });

        return response()->json(['data' => $favourites]);
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'screen_id'      => 'required|string',
            'application_id' => 'required|string',
        ]);

        $username = Auth::user()->username;

        $existing = SysMenuFavourite::where('username', $username)
            ->where('screen_id', $request->screen_id)
            ->where('application_id', $request->application_id)
            ->first();

        if ($existing) {
            $existing->delete();

            return response()->json(['success' => true, 'favourited' => false]);
        }

        $allowedKeys = SysMenu::allowedScreenKeysForUsername($username);

        if (!$allowedKeys->contains($request->screen_id . '|' . $request->application_id)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this menu.',
            ], 403);
        }

        $nextOrder = (int) SysMenuFavourite::where('username', $username)->max('menu_order') + 1;

        SysMenuFavourite::create([
            'username'       => $username,
            'screen_id'      => $request->screen_id,
            'application_id' => $request->application_id,
            'menu_order'     => $nextOrder,
            'status'         => 'A',
            'created_by'     => $username,
            'created_at'     => now(),
        ]);

        return response()->json(['success' => true, 'favourited' => true]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer',
        ]);

        $username = Auth::user()->username;

        foreach ($request->ids as $index => $id) {
            SysMenuFavourite::where('id', $id)
                ->where('username', $username)
                ->update([
                    'menu_order' => $index + 1,
                    'updated_by' => $username,
                    'updated_at' => now(),
                ]);
        }

        return response()->json(['success' => true]);
    }
}
