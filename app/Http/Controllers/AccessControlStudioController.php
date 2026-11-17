<?php

namespace App\Http\Controllers;

use App\Models\SysApplication;
use App\Models\SysScreen;
use App\Models\SysMenu;
use App\Models\SysRole;
use App\Models\SysAccessRight;
use App\Services\AccessAuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            ->get(['screen_id', 'screen_name', 'application_id', 'status']);

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

        // Named GET routes without required params, for the Menu "Route Name" picker.
        $routeNames = collect(RouteFacade::getRoutes()->getRoutesByName())
            ->filter(fn ($r) => in_array('GET', $r->methods()) && !str_contains($r->uri(), '{'))
            ->keys()
            ->reject(fn ($n) => str_starts_with($n, 'ignition.') || str_starts_with($n, 'debugbar.') || str_starts_with($n, 'sanctum.'))
            ->sort()
            ->values();

        return view('pages.access_control_studio.index', compact(
            'applications', 'screens', 'menus', 'roles', 'accessNames', 'routeNames'
        ));
    }

    /** "What can this role see?" — users, effective menus and access rights of one role. */
    public function roleOverview(string $roleId)
    {
        $db = fn ($t) => DB::connection('pgsql2')->table($t);
        $role = $db('sys_role')->where('role_id', $roleId)->first(['role_id', 'role_name', 'status']);
        abort_unless($role, 404);

        // Only display-safe columns from ms_user (never password/token).
        $users = $db('sys_user_role as ur')
            ->leftJoin('ms_user as u', 'u.username', '=', 'ur.username')
            ->where('ur.role_id', $roleId)->where('ur.status', 'A')
            ->orderBy('ur.username')
            ->get(['ur.username', 'u.name', 'u.jabatan', 'u.department_id', 'u.status as user_status', 'u.last_login_at']);

        $menus = $db('sys_role_menu as rm')
            ->join('sys_menu as m', 'm.menu_id', '=', 'rm.menu_id')
            ->where('rm.role_id', $roleId)->where('rm.status', 'A')->where('m.status', 'A')
            ->orderBy('m.menu_sort_order')->orderBy('m.menu_name')
            ->get(['m.menu_id', 'm.parent_menu_id', 'm.menu_name', 'm.menu_route']);
        $menus = $menus->map(function ($m) {
            $m->route_missing = !empty($m->menu_route) && !RouteFacade::has($m->menu_route);
            return $m;
        });

        $rights = $db('sys_access_right as r')
            ->leftJoin('sys_screen as s', 's.screen_id', '=', 'r.screen_id')
            ->where('r.role_id', $roleId)->where('r.status', 'A')
            ->orderBy('r.screen_id')
            ->get(['r.screen_id', 's.screen_name', 'r.application_id', 'r.access_name'])
            ->groupBy('screen_id')
            ->map(fn ($g) => [
                'screen_id'   => $g[0]->screen_id,
                'screen_name' => $g[0]->screen_name,
                'application_id' => $g[0]->application_id,
                'actions'     => $g->pluck('access_name')->unique()->values(),
            ])->values();

        return response()->json(compact('role', 'users', 'menus', 'rights'));
    }

    /** Reverse view: which roles can see this menu. */
    public function menuRoles(string $menuId)
    {
        $roles = DB::connection('pgsql2')->table('sys_role_menu as rm')
            ->join('sys_role as r', 'r.role_id', '=', 'rm.role_id')
            ->where('rm.menu_id', $menuId)->where('rm.status', 'A')->where('r.status', 'A')
            ->orderBy('r.role_id')->get(['r.role_id', 'r.role_name']);

        return response()->json(['menu_id' => $menuId, 'roles' => $roles]);
    }

    /** Blast radius of disabling a role or menu, shown in the confirm dialog. */
    public function impact(string $type, string $id)
    {
        $db = fn ($t) => DB::connection('pgsql2')->table($t);

        if ($type === 'role') {
            return response()->json([
                'users' => $db('sys_user_role')->where('role_id', $id)->where('status', 'A')->count(),
                'menus' => $db('sys_role_menu')->where('role_id', $id)->where('status', 'A')->count(),
            ]);
        }
        if ($type === 'menu') {
            return response()->json([
                'roles'    => $db('sys_role_menu')->where('menu_id', $id)->where('status', 'A')->distinct()->count('role_id'),
                'children' => $db('sys_menu')->where('parent_menu_id', $id)->where('status', 'A')->count(),
            ]);
        }
        abort(404);
    }

    /** Copy a role's menus + access rights into a new role (users are NOT copied). */
    public function cloneRole(Request $request)
    {
        $request->validate([
            'source_role_id' => 'required|string|max:50|exists:pgsql2.sys_role,role_id',
            'role_id'        => 'required|string|max:50|unique:pgsql2.sys_role,role_id',
            'role_name'      => 'required|string|max:200',
        ]);

        $src = $request->source_role_id;
        $new = strtoupper(trim($request->role_id));
        $by  = Auth::user()->username ?? 'system';
        $conn = DB::connection('pgsql2');

        try {
            $counts = $conn->transaction(function () use ($conn, $src, $new, $by, $request) {
                $conn->table('sys_role')->insert([
                    'role_id' => $new, 'role_name' => $request->role_name, 'status' => 'A',
                    'created_by' => $by, 'created_at' => now(),
                ]);

                $menus = $conn->table('sys_role_menu')->where('role_id', $src)->get(['menu_id', 'parent_menu_id', 'status']);
                foreach ($menus as $m) {
                    $conn->table('sys_role_menu')->insert([
                        'role_id' => $new, 'menu_id' => $m->menu_id, 'parent_menu_id' => $m->parent_menu_id,
                        'status' => $m->status, 'created_by' => $by, 'updated_by' => $by,
                    ]);
                }

                $rights = $conn->table('sys_access_right')->where('role_id', $src)->get(['screen_id', 'application_id', 'access_name', 'access_right', 'access_type', 'status']);
                foreach ($rights as $r) {
                    $conn->table('sys_access_right')->insert([
                        'role_id' => $new, 'screen_id' => $r->screen_id, 'application_id' => $r->application_id,
                        'access_name' => $r->access_name, 'access_right' => $r->access_right, 'access_type' => $r->access_type,
                        'status' => $r->status, 'created_by' => $by, 'updated_by' => $by,
                    ]);
                }

                return ['menus' => $menus->count(), 'rights' => $rights->count()];
            });
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Clone failed', 'error' => $e->getMessage()], 500);
        }

        AccessAuditLogger::log('role', $new, 'clone', "Cloned role {$src} → {$new} ({$counts['menus']} menus, {$counts['rights']} rights)", $counts + ['source' => $src]);

        return response()->json(['success' => true, 'data' => ['role_id' => $new], 'counts' => $counts]);
    }

    /** Recent RBAC changes for the History drawer. */
    public function audit(Request $request)
    {
        return response()->json(['data' => AccessAuditLogger::recent(200, $request->query('type'), $request->query('entity'))]);
    }

    /**
     * Read-only gap report — no table, no middleware, just introspection:
     * 1) leaf menus (no children) missing a screen_id
     * 2) screens with zero `access:SCREEN,ACTION` route anywhere in web.php
     * 3) screen_ids used by access: middleware that aren't registered in Screen master data
     * 4) menus whose application_id doesn't match the application_id of their own screen_id
     */
    public function coverage()
    {
        $menus = SysMenu::on('pgsql2')
            ->where('status', 'A')
            ->get(['id', 'menu_id', 'menu_name', 'menu_route', 'parent_menu_id', 'screen_id', 'application_id']);

        $parentIds = $menus->pluck('parent_menu_id')->filter()->unique();

        $menusMissingScreen = $menus
            ->reject(fn ($m) => $parentIds->contains($m->menu_id)) // drop parents/folders
            ->filter(fn ($m) => !$m->screen_id)
            ->map(fn ($m) => ['id' => $m->id, 'menu_id' => $m->menu_id, 'menu_name' => $m->menu_name])
            ->values();

        $screenActions = []; // screen_id => [action_names...]
        foreach (RouteFacade::getRoutes() as $route) {
            foreach ($route->gatherMiddleware() as $mw) {
                if (is_string($mw) && str_starts_with($mw, 'access:')) {
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
            ->get(['screen_id', 'screen_name', 'application_id']);

        $screenAppIds = $screens->pluck('application_id', 'screen_id');

        $menusAppMismatch = $menus
            ->filter(fn ($m) => $m->screen_id && $screenAppIds->has($m->screen_id))
            ->filter(fn ($m) => $m->application_id !== $screenAppIds->get($m->screen_id))
            ->map(fn ($m) => [
                'id'                  => $m->id,
                'menu_id'             => $m->menu_id,
                'menu_name'           => $m->menu_name,
                'menu_application_id' => $m->application_id,
                'screen_id'           => $m->screen_id,
                'screen_application_id' => $screenAppIds->get($m->screen_id),
            ])
            ->values();

        // 5) menus whose menu_route is not a defined named route in this codebase
        $menusRouteMissing = $menus
            ->filter(fn ($m) => !empty($m->menu_route) && !RouteFacade::has($m->menu_route))
            ->map(fn ($m) => ['id' => $m->id, 'menu_id' => $m->menu_id, 'menu_name' => $m->menu_name, 'menu_route' => $m->menu_route])
            ->values();

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
            'menus_missing_screen'    => $menusMissingScreen,
            'screens_with_route'      => $screensWithRoute,
            'screens_without_route'   => $screensWithoutRoute,
            'unregistered_screen_ids' => $unregisteredScreenIds,
            'menus_app_mismatch'      => $menusAppMismatch,
            'menus_route_missing'     => $menusRouteMissing,
        ]);
    }
}
