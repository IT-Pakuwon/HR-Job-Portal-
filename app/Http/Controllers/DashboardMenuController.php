<?php

namespace App\Http\Controllers;

use App\Models\DashboardMenu;
use App\Models\SysMenu;
use App\Models\SysRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

/** Admin screen for the dashboard "Menu" tile bar (ms_dashboard_menu / ms_dashboard_menu_map). */
class DashboardMenuController extends Controller
{
    protected const CONN = 'pgsql2';

    public function index()
    {
        $db = DB::connection(self::CONN);

        $this->ensurePanelTiles($db);

        // Retired dashboard-card tiles (panel_key not in components/panel) are left out; OM keeps its Approval panel.
        $validPanels = array_merge(DashboardMenu::panelKeys(), ['approval']);
        $tiles = $db->table('ms_dashboard_menu')->orderBy('menu_name')->get()
            ->filter(fn ($t) => !$t->panel_key || in_array($t->panel_key, $validPanels, true))->values();

        $maps = $db->table('ms_dashboard_menu_map')->orderBy('sort_no')->get()
            ->groupBy('dash_cd')
            ->map(fn ($rows) => $rows->map(fn ($r) => $r->sys_menu_id
                ? ['t' => 's', 'key' => $r->sys_menu_id, 'desc' => $r->menu_desc ?? '', 'default' => false]
                : ['t' => 'c', 'key' => $r->menu_key, 'desc' => $r->menu_desc ?? '', 'default' => (bool) $r->is_default]
            )->values());

        $screenNames = $db->table('sys_screen')->where('application_id', 'DASHBOARD')->pluck('screen_name', 'screen_id');

        $dashboards = collect(array_keys(MultiDashboardController::DASHBOARD_COMPONENTS))
            ->map(fn ($cd) => [
                'code' => $cd,
                'name' => $screenNames->get($cd, $cd),
                'items' => $maps->get($cd, collect())->all(),
            ])
            ->values();

        // Sidebar items (sys_menu) a tile can point at: active entries that open a page.
        $all = SysMenu::where('status', 'A')->get(['menu_id', 'parent_menu_id', 'menu_name', 'menu_route', 'menu_url', 'menu_icon']);
        $byId = $all->keyBy('menu_id');
        $sysMenus = $all->filter(fn ($m) => $m->menu_route || $m->menu_url)
            ->map(function ($m) use ($byId) {
                $parent = $m->parent_menu_id ? ($byId->get($m->parent_menu_id)?->menu_name) : null;
                return [
                    'menu_id' => $m->menu_id,
                    'menu_name' => $m->menu_name,
                    'group' => $parent,
                    'icon_path' => $m->menu_icon,
                ];
            })
            ->sortBy(fn ($m) => ($m['group'] ?? '') . '|' . $m['menu_name'])->values();

        // Icon library for the tile form: every distinct icon already used by a sidebar item or a tile, so a
        // tile's icon can be picked instead of pasted. Labelled by the first item that uses it.
        $iconLibrary = $all->map(fn ($m) => ['path' => $m->menu_icon, 'label' => $m->menu_name])
            ->concat($tiles->map(fn ($t) => ['path' => $t->icon_path, 'label' => $t->menu_name]))
            ->filter(fn ($i) => $i['path'] && !str_starts_with(trim($i['path']), '<'))
            ->unique('path')->sortBy('label')->values();

        // Named GET routes without URL parameters are valid tile targets.
        $routes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($r) => $r->getName() && in_array('GET', $r->methods()) && !str_contains($r->uri(), '{'))
            ->map(fn ($r) => $r->getName())->sort()->values();

        return view('pages.dashboard-menu.index', [
            'tiles' => $tiles,
            'dashboards' => $dashboards,
            'sysMenus' => $sysMenus,
            'routeNames' => $routes,
            'panelKeys' => DashboardMenu::panelKeys(),
            'iconLibrary' => $iconLibrary,
            'templatePanelKeys' => array_keys(DashboardMenu::templatePanels()),
        ]);
    }

    /** Create or update a tile in the catalog. */
    public function saveTile(Request $request)
    {
        $data = $request->validate([
            'menu_key'   => ['required', 'string', 'max:30', 'regex:/^[A-Z0-9_]+$/'],
            'menu_name'  => ['required', 'string', 'max:100'],
            'menu_desc'  => ['nullable', 'string', 'max:255'],
            'icon_path'  => ['nullable', 'string', 'max:2000'],
            'route_name' => ['nullable', 'string', 'max:100'],
            'external_url' => ['nullable', 'url:https', 'max:1000'],
            'panel_key'  => ['nullable', Rule::in(DashboardMenu::panelKeys())],
            'role_id'    => ['nullable', 'string', 'max:50'],
            'status'     => ['required', Rule::in(['A', 'I'])],
        ]);

        if (count(array_filter([$data['route_name'] ?? null, $data['panel_key'] ?? null, $data['external_url'] ?? null])) !== 1) {
            return response()->json(['message' => 'Choose exactly one of Route, Panel or External URL.'], 422);
        }
        if (!empty($data['route_name']) && !Route::has($data['route_name'])) {
            return response()->json(['message' => 'Route "' . $data['route_name'] . '" does not exist.'], 422);
        }

        $now = now();
        $user = Auth::user()->username;
        $table = DB::connection(self::CONN)->table('ms_dashboard_menu');

        if ($table->where('menu_key', $data['menu_key'])->exists()) {
            $table->where('menu_key', $data['menu_key'])
                ->update($data + ['updated_by' => $user, 'updated_at' => $now]);
        } else {
            $table->insert($data + ['created_by' => $user, 'created_at' => $now]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Replace the ordered tile list of one dashboard.
     * items: [{t: 'c'|'s', key, desc}] — 'c' = catalog tile (menu_key), 's' = sidebar item (sys_menu.menu_id).
     */
    public function saveMap(Request $request, string $dash)
    {
        $dash = strtoupper($dash);
        abort_unless(array_key_exists($dash, MultiDashboardController::DASHBOARD_COMPONENTS), 404);

        $data = $request->validate([
            'items'        => ['present', 'array'],
            'items.*.t'    => ['required', Rule::in(['c', 's'])],
            'items.*.key'  => ['required', 'string', 'max:100'],
            'items.*.desc' => ['nullable', 'string', 'max:255'],
            'items.*.default' => ['nullable', 'boolean'],
        ]);

        $items = collect($data['items'])->unique(fn ($i) => $i['t'] . '|' . $i['key'])->values();
        $db = DB::connection(self::CONN);

        $catalogKeys = $items->where('t', 'c')->pluck('key');
        $sysKeys = $items->where('t', 's')->pluck('key');
        if ($db->table('ms_dashboard_menu')->whereIn('menu_key', $catalogKeys)->count() !== $catalogKeys->count()
            || SysMenu::whereIn('menu_id', $sysKeys)->where('status', 'A')->count() !== $sysKeys->count()) {
            return response()->json(['message' => 'One or more tiles no longer exist. Reload the page.'], 422);
        }

        // Reject panel tiles that aren't a panel template (OM also keeps its embedded Approval panel).
        $badPanel = $catalogKeys->isEmpty() ? collect() : $db->table('ms_dashboard_menu')
            ->whereIn('menu_key', $catalogKeys)->whereNotNull('panel_key')->pluck('panel_key')
            ->reject(fn ($p) => DashboardMenu::panelAllowed($dash, $p));
        if ($badPanel->isNotEmpty()) {
            return response()->json(['message' => 'One or more panel tiles are not a valid panel.'], 422);
        }

        // Only a panel tile can be "open by default"; at most one per dashboard.
        $panelKeys = $catalogKeys->isEmpty() ? collect() : $db->table('ms_dashboard_menu')
            ->whereIn('menu_key', $catalogKeys)->whereNotNull('panel_key')->pluck('menu_key');
        $defaultKey = $items->first(fn ($i) => !empty($i['default']) && $i['t'] === 'c' && $panelKeys->contains($i['key']))['key'] ?? null;

        // Upsert rather than delete + re-insert, so a row keeps its id (and created_by/created_at) across saves.
        $db->transaction(function ($db) use ($dash, $items, $defaultKey) {
            $existing = $db->table('ms_dashboard_menu_map')->where('dash_cd', $dash)->get();
            $rowFor = fn ($it) => $existing->first(fn ($r) => $it['t'] === 'c' ? $r->menu_key === $it['key'] : $r->sys_menu_id === $it['key']);

            $keep = [];
            foreach ($items as $i => $it) {
                $values = [
                    'menu_desc'  => ($it['desc'] ?? '') !== '' ? $it['desc'] : null,
                    'sort_no'    => $i + 1,
                    'is_default' => $it['t'] === 'c' && $it['key'] === $defaultKey,
                    'status'     => 'A',
                ];
                if ($row = $rowFor($it)) {
                    $db->table('ms_dashboard_menu_map')->where('id', $row->id)->update($values);
                    $keep[] = $row->id;
                } else {
                    $keep[] = $db->table('ms_dashboard_menu_map')->insertGetId($values + [
                        'dash_cd'     => $dash,
                        'menu_key'    => $it['t'] === 'c' ? $it['key'] : null,
                        'sys_menu_id' => $it['t'] === 's' ? $it['key'] : null,
                        'created_by'  => Auth::user()->username,
                        'created_at'  => now(),
                    ], 'id');
                }
            }

            $db->table('ms_dashboard_menu_map')->where('dash_cd', $dash)->whereNotIn('id', $keep)->delete();
        });

        return response()->json(['ok' => true]);
    }

    /**
     * Make sure the catalog has a tile for every panel template (components/panel), so a new
     * template shows up here without a hand-written SQL insert.
     */
    protected function ensurePanelTiles($db): void
    {
        $have = $db->table('ms_dashboard_menu')->whereNotNull('panel_key')->pluck('panel_key')->all();
        $missing = array_diff(DashboardMenu::panelKeys(), $have);
        if (!$missing) {
            return;
        }

        $icon = 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z';
        $templates = DashboardMenu::templatePanels();

        foreach ($missing as $key) {
            $db->table('ms_dashboard_menu')->insertOrIgnore([
                'menu_key'   => 'PNL_' . strtoupper(str_replace('-', '_', $key)),
                'menu_name'  => $templates[$key],
                'menu_desc'  => 'Open the ' . $templates[$key] . ' panel',
                'icon_path'  => $icon,
                'panel_key'  => $key,
                'status'     => 'A',
                'created_by' => 'system',
            ]);
        }
    }
    /**
     * Count bubbles for panel tiles: how many rows each panel's list currently shows this user.
     * Each count comes from the panel's own JSON endpoint (same middleware, same company/role scoping),
     * so the badge always equals what opening the panel shows. Cached 30s per user.
     */
    public function badges(Request $request)
    {
        $keys = collect((array) $request->query('keys'))->filter(fn ($k) => is_string($k) && DashboardMenu::isTemplatePanel($k))->unique()->take(60);
        $user = $request->user();
        $counts = [];

        foreach ($keys as $key) {
            $counts['p:' . $key] = Cache::remember('dash-badge:' . $user->username . ':' . $key, 30, function () use ($request, $key) {
                $url = DashboardMenu::panelUrl($key);
                if (!$url) {
                    return 0;
                }
                try {
                    $sub = Request::create($url, 'GET', [], [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest', 'HTTP_ACCEPT' => 'application/json']);
                    $sub->setUserResolver($request->getUserResolver());
                    if ($request->hasSession()) {
                        $sub->setLaravelSession($request->session());
                    }
                    app()->instance('request', $sub); // controllers type-hint Request, so they must receive the sub-request
                    $res = app('router')->dispatch($sub);
                    $body = json_decode($res->getContent(), true);

                    return $res->getStatusCode() === 200 && is_array($body['data'] ?? null) ? count($body['data']) : 0;
                } catch (\Throwable $e) {
                    return 0; // a panel the user may not open (403/404) simply has no badge
                } finally {
                    app()->instance('request', $request);
                }
            });
        }

        return response()->json(['counts' => $counts]);
    }

    /**
     * A user pins (or un-pins) a panel to open by default for themself. Any signed-in user may do this,
     * but only for a panel tile that dashboard actually shows them.
     */
    public function pin(Request $request)
    {
        $data = $request->validate([
            'dash'  => ['required', 'string', 'max:30'],
            'panel' => ['required', 'string', 'max:30'],
            'open'  => ['required', 'boolean'],
        ]);
        $dash = strtoupper($data['dash']);
        $user = Auth::user();

        abort_unless(array_key_exists($dash, MultiDashboardController::DASHBOARD_COMPONENTS), 404);
        abort_unless(DashboardMenu::panelAllowed($dash, $data['panel']), 422, 'That panel does not belong to this dashboard.');
        $panels = DashboardMenu::forDashboard($dash, $user)->pluck('panel')->filter()->unique();
        abort_unless($panels->contains($data['panel']), 422, 'This dashboard has no tile for that panel.');

        // Only one default per dashboard: pinning one panel switches every other panel's default off for this user
        // (an explicit false also overrides the admin default of that other panel).
        $now = now();
        $rows = $panels->map(fn ($p) => [
            'username' => $user->username, 'dash_cd' => $dash, 'panel_key' => $p,
            'open_default' => $p === $data['panel'] ? (bool) $data['open'] : false,
            'updated_at' => $now,
        ]);
        if (!$data['open']) {
            $rows = $rows->filter(fn ($r) => $r['panel_key'] === $data['panel']);
        }

        DB::connection(self::CONN)->table('ms_dashboard_menu_user_pref')->upsert(
            $rows->values()->all(),
            ['username', 'dash_cd', 'panel_key'],
            ['open_default', 'updated_at']
        );

        return response()->json(['ok' => true]);
    }

    /** Update only the subtitle / "open by default" of one tile already stored on a dashboard (t: 'c' catalog | 's' sidebar). */
    public function saveRow(Request $request, string $dash)
    {
        $dash = strtoupper($dash);
        abort_unless(array_key_exists($dash, MultiDashboardController::DASHBOARD_COMPONENTS), 404);

        $data = $request->validate([
            't'    => ['required', Rule::in(['c', 's'])],
            'key'  => ['required', 'string', 'max:100'],
            'desc' => ['nullable', 'string', 'max:255'],
            'default' => ['nullable', 'boolean'],
        ]);

        $db = DB::connection(self::CONN);
        $row = fn () => $db->table('ms_dashboard_menu_map')->where('dash_cd', $dash)
            ->where($data['t'] === 'c' ? 'menu_key' : 'sys_menu_id', $data['key']);

        $isPanel = $data['t'] === 'c' && $db->table('ms_dashboard_menu')
            ->where('menu_key', $data['key'])->whereNotNull('panel_key')->exists();
        $makeDefault = !empty($data['default']) && $isPanel;

        $updated = $row()->update([
            'menu_desc'  => ($data['desc'] ?? '') !== '' ? $data['desc'] : null,
            'is_default' => $makeDefault,
        ]);
        if ($updated && $makeDefault) {
            // one default per dashboard
            $db->table('ms_dashboard_menu_map')->where('dash_cd', $dash)
                ->where('menu_key', '!=', $data['key'])->update(['is_default' => false]);
        }

        if (!$updated) {
            return response()->json(['message' => 'This tile is not saved on the dashboard yet. Use Save order first.'], 422);
        }

        return response()->json(['ok' => true]);
    }
}
