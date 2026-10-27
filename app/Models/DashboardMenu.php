<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class DashboardMenu extends Model
{
    protected $connection = 'pgsql2';
    protected $table = 'ms_dashboard_menu';
    protected $primaryKey = 'menu_key';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    /**
     * Each dashboard owns one in-page panel (its main tabbed card), keyed after its view:
     * 'dashboard-it' => 'it', 'purchasing-dashboard' => 'purchasing', 'dashboard-corporate-teknik' => 'corporateteknik'.
     * Derived from MultiDashboardController::DASHBOARD_COMPONENTS, so a new dashboard needs no entry here.
     * A panel tile for that key, assigned to that dashboard, keeps the card hidden until the tile (or a KPI card)
     * is clicked. DASHOM has no card of its own but also hosts the Approval Dashboard embedded as a panel.
     *
     * @return array<string,string> dash code => panel key
     */
    public static function dashboardPanels(): array
    {
        $map = [];
        foreach (\App\Http\Controllers\MultiDashboardController::DASHBOARD_COMPONENTS as $code => $component) {
            if ($code === 'DASHOM') {
                continue;
            }
            $map[$code] = str_replace('-', '', preg_replace('/^dashboard-|-dashboard$/', '', $component));
        }

        return $map;
    }

    /** The panel key a dashboard owns (null for a dashboard without a card, e.g. OM). */
    public static function panelKey(string $dashCd): ?string
    {
        return static::dashboardPanels()[strtoupper($dashCd)] ?? null;
    }

    /** Every panel_key a custom tile may use. */
    public static function panelKeys(): array
    {
        return array_values(array_unique(static::dashboardPanels()));
    }

    /** Can this dashboard open that panel? Each dashboard owns one; OM also hosts the Approval panel. */
    public static function panelAllowed(string $dashCd, ?string $panelKey): bool
    {
        if (!$panelKey) {
            return true;
        }
        $dashCd = strtoupper($dashCd);

        return $panelKey === static::panelKey($dashCd)
            || ($dashCd === 'DASHOM' && $panelKey === 'approval');
    }
    /** True when the dashboard shows a tile for its own panel, i.e. the panel starts hidden. */
    public static function holdsPanel(string $dashCd, string $panelKey, $user = null): bool
    {
        $user = $user ?: auth()->user();

        return static::forDashboard($dashCd, $user)->contains(fn ($i) => ($i['panel'] ?? null) === $panelKey);
    }

    /**
     * Tiles that show a live count bubble. Key = 'sys:<sys_menu.menu_id>' or 'panel:<panel_key>',
     * value = the source the dashboard-menu component polls/listens to.
     */
    public const BADGES = [
        'sys:MAILBOX'    => 'mailbox',   // unread emails (mailbox.unread-count)
        'panel:approval' => 'approval',  // waiting approvals (same number as the Approval Dashboard KPI)
    ];

    /**
     * Tiles for a dashboard (sys_screen.screen_id), in order, as
     * [name, desc, href, panel, icon] arrays. A map row is either a catalog tile
     * (ms_dashboard_menu, optional role gate) or a sidebar item (sys_menu) that is
     * shown only if the user's roles grant it via sys_role_menu — the sidebar's own rule.
     */
    public static function forDashboard(string $dashCd, $user): Collection
    {
        // The Menu card and the panel wrapper both ask on every dashboard load; query once per request.
        static $memo = [];
        $key = strtoupper($dashCd) . '|' . ($user->username ?? '');

        return $memo[$key] ??= static::loadForDashboard($dashCd, $user);
    }

    protected static function loadForDashboard(string $dashCd, $user): Collection
    {
        $db = DB::connection('pgsql2');

        $rows = $db->table('ms_dashboard_menu_map')
            ->where('dash_cd', strtoupper($dashCd))->where('status', 'A')
            ->orderBy('sort_no')->get();
        if ($rows->isEmpty()) {
            return collect();
        }

        $catalog = static::whereIn('menu_key', $rows->pluck('menu_key')->filter())
            ->where('status', 'A')->get()->keyBy('menu_key');
        $sysMenus = SysMenu::whereIn('menu_id', $rows->pluck('sys_menu_id')->filter())
            ->where('status', 'A')->get()->keyBy('menu_id');

        $roles = $user ? $user->roleIds() : collect();
        $prefs = static::userPrefs(strtoupper($dashCd), $user);
        $allowedMenuIds = $user && $sysMenus->isNotEmpty()
            ? SysRoleMenu::whereIn('role_id', $roles)->where('status', 'A')
                ->whereIn('menu_id', $sysMenus->keys())->pluck('menu_id')->flip()
            : collect();

        return $rows->map(function ($row) use ($catalog, $sysMenus, $roles, $allowedMenuIds, $user, $prefs) {
            if ($row->sys_menu_id) {
                $m = $sysMenus->get($row->sys_menu_id);
                if (!$m || !$allowedMenuIds->has($m->menu_id)) {
                    return null;
                }
                // Same extra rule the sidebar applies: Mailbox needs a connected mailbox account.
                if ($m->menu_id === 'MAILBOX' && !MailboxAccount::isConnectedFor($user)) {
                    return null;
                }

                return [
                    'name'  => $m->menu_name,
                    'desc'  => $row->menu_desc,
                    'href'  => static::resolveHref($m->menu_route, $m->menu_url),
                    'panel' => null,
                    'external' => false,
                    'badge' => static::BADGES['sys:' . $m->menu_id] ?? null,
                    'icon'  => $m->menu_icon,
                ];
            }

            $t = $catalog->get($row->menu_key);
            if (!$t || ($t->role_id && !$roles->contains($t->role_id))) {
                return null;
            }

            return [
                'name'  => $t->menu_name,
                'desc'  => $row->menu_desc ?: $t->menu_desc,
                'href'  => static::resolveHref($t->route_name, $t->external_url),
                'external' => (bool) $t->external_url,
                'badge' => $t->panel_key ? (static::BADGES['panel:' . $t->panel_key] ?? null) : null,
                'panel' => $t->panel_key,
                // the user's own pin wins over the admin default
                'default' => $t->panel_key ? (bool) ($prefs[$t->panel_key] ?? ($row->is_default ?? false)) : false,
                'icon'  => $t->icon_path,
            ];
        })->filter(fn ($i) => $i && static::panelAllowed($dashCd, $i['panel'] ?? null))->values();
    }

    /** @return array<string,bool> panel_key => the user's own "open by default" choice on this dashboard */
    protected static function userPrefs(string $dashCd, $user): array
    {
        if (!$user || empty($user->username)) {
            return [];
        }
        try {
            return DB::connection('pgsql2')->table('ms_dashboard_menu_user_pref')
                ->where('username', $user->username)->where('dash_cd', $dashCd)
                ->pluck('open_default', 'panel_key')->map(fn ($v) => (bool) $v)->all();
        } catch (\Throwable $e) {
            return []; // table not created yet: fall back to the admin default
        }
    }

    protected static function resolveHref(?string $routeName, ?string $url): string
    {
        if ($routeName && Route::has($routeName)) {
            try {
                return route($routeName);
            } catch (\Throwable $e) {
                return '#';
            }
        }

        return $url ?: '#';
    }
}
