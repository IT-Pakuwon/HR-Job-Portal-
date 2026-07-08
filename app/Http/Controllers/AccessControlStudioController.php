<?php

namespace App\Http\Controllers;

use App\Models\SysApplication;
use App\Models\SysScreen;
use App\Models\SysMenu;
use App\Models\SysRole;
use App\Models\SysAccessRight;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route as RouteFacade;

/**
 * Single-page, step-by-step console for the RBAC chain: Application ->
 * Screen -> Menu -> Role -> Role Menu -> Access Rights.
 * It has no store/update endpoints of its own — every panel posts to the
 * same controllers/routes as the existing standalone admin pages
 * (applications.*, screens.*, menus.*, roles.*, role_menus.*, access_rights.*),
 * it just renders them all in one guided flow.
 */
class AccessControlStudioController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $applications = SysApplication::on('pgsql2')
            ->where('status', 'A')
            ->orderBy('application_id')
            ->get(['application_id', 'application_name']);

        $screens = SysScreen::on('pgsql2')
            ->where('status', 'A')
            ->orderBy('screen_id')
            ->get(['screen_id', 'screen_name', 'application_id']);

        $menus = SysMenu::on('pgsql2')
            ->where('status', 'A')
            ->orderBy('menu_id')
            ->get(['menu_id', 'menu_name', 'parent_menu_id', 'screen_id', 'application_id']);

        $roles = SysRole::on('pgsql2')
            ->where('status', 'A')
            ->orderBy('role_id')
            ->get(['role_id', 'role_name']);

        $accessNames = collect(['VIEW', 'CREATE', 'EDIT', 'DELETE'])
            ->merge(
                SysAccessRight::whereNotNull('access_name')
                    ->distinct()
                    ->pluck('access_name')
            )
            ->map(fn ($n) => strtoupper(trim($n)))
            ->unique()
            ->values();

        return view('pages.access_control_studio.index', compact(
            'applications', 'screens', 'menus', 'roles', 'accessNames'
        ));
    }

    /**
     * Read-only gap report — no table, no middleware, just introspection:
     * 1) leaf menus (no children) missing a screen_id
     * 2) screens with zero `access:SCREEN,ACTION` route anywhere in web.php
     * 3) screen_ids used by access: middleware that aren't registered in Screen master data
     */
    public function coverage()
    {
        $menus = SysMenu::on('pgsql2')
            ->where('status', 'A')
            ->get(['id', 'menu_id', 'menu_name', 'parent_menu_id', 'screen_id']);

        $parentIds = $menus->pluck('parent_menu_id')->filter()->unique();

        $menusMissingScreen = $menus
            ->reject(fn ($m) => $parentIds->contains($m->menu_id)) // drop parents/folders
            ->filter(fn ($m) => !$m->screen_id)
            ->map(fn ($m) => ['id' => $m->id, 'menu_id' => $m->menu_id, 'menu_name' => $m->menu_name])
            ->values();

        $screenActions = []; // screen_id => [action_names...]
        foreach (RouteFacade::getRoutes() as $route) {
            foreach ($route->gatherMiddleware() as $mw) {
                if (str_starts_with($mw, 'access:')) {
                    [$screenId, $action] = array_pad(explode(',', substr($mw, 7), 2), 2, null);
                    $screenId = trim($screenId ?? '');
                    $action = trim($action ?? '');
                    if ($screenId === '') continue;
                    $screenActions[$screenId] = $screenActions[$screenId] ?? [];
                    if ($action !== '' && !in_array($action, $screenActions[$screenId])) {
                        $screenActions[$screenId][] = $action;
                    }
                }
            }
        }

        $screens = SysScreen::on('pgsql2')
            ->where('status', 'A')
            ->orderBy('screen_id')
            ->get(['screen_id', 'screen_name']);

        $screensWithRoute = [];
        $screensWithoutRoute = [];
        foreach ($screens as $s) {
            if (!empty($screenActions[$s->screen_id])) {
                $screensWithRoute[] = [
                    'screen_id'   => $s->screen_id,
                    'screen_name' => $s->screen_name,
                    'actions'     => $screenActions[$s->screen_id],
                ];
            } else {
                $screensWithoutRoute[] = ['screen_id' => $s->screen_id, 'screen_name' => $s->screen_name];
            }
        }

        $knownScreenIds = $screens->pluck('screen_id')->all();
        $unregisteredScreenIds = collect(array_keys($screenActions))
            ->reject(fn ($id) => in_array($id, $knownScreenIds))
            ->sort()
            ->values();

        return response()->json([
            'menus_missing_screen'   => $menusMissingScreen,
            'screens_with_route'     => $screensWithRoute,
            'screens_without_route'  => $screensWithoutRoute,
            'unregistered_screen_ids' => $unregisteredScreenIds,
        ]);
    }
}
