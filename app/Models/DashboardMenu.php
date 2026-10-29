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
     * Panel templates: one Blade component per dashboard tab (resources/views/components/panel/<key>.blade.php,
     * rendered by <x-panel.shell>). Not tied to a dashboard — any dashboard can show any of them as a Menu tile.
     *
     * @return array<string,string> panel key => label
     */
    public static function templatePanels(): array
    {
        return [
            'it-ticket'         => 'IT · Ticket',
            'it-access'         => 'IT · Access Request',
            'it-recommendation' => 'IT · IT Recommendation',
            'ga-voucher-taxi'   => 'GA · Voucher Taxi',
            'ga-booking-car'    => 'GA · Booking Car',
            'ga-parking'        => 'GA · Parking',
            'hr-prf'            => 'HR · PRF',
            'hr-applicant'      => 'HR · Applicant',
            'hr-self-register'  => 'HR · Self Register',
            'opr-workorder'     => 'Operational · Work Order',
            'recruit-applicant'      => 'Recruitment · Applicant',
            'recruit-self-register'  => 'Recruitment · Self Register',
            'corptek-ticket'         => 'Corporate Teknik · Ticket',
            'corptek-berita-acara'   => 'Corporate Teknik · Berita Acara',
            'cost-po'                => 'Cost Control · Pending PO',
            'cost-issue'             => 'Cost Control · Pending Issue',
            'cost-budget'            => 'Cost Control · Budget Monitoring',
            'cost-imbudget'          => 'Cost Control · IM Budget',
            'cost-expired'           => 'Cost Control · Expired List',
            'cost-waiting-settlement' => 'Cost Control · Waiting For Settlement',
            'vpcoll-expired'         => 'VP Collection · Expired Voucher List',
            'vpcoll-settlement'      => 'VP Collection · Waiting For Settlement',
            'vpprmtn-expired'        => 'VP Promotion · Expired Product List',
            'vpprmtn-settlement'     => 'VP Promotion · Waiting For Settlement',
            'vployalty-expired'      => 'VP Loyalty · Expired Voucher & Product List',
            'fin-rfca-purchase'      => 'Finance · RFCA Purchase Waiting',
            'fin-calr-purchase'      => 'Finance · CALR Purchase Waiting',
            'fin-rfp-nonpurch'       => 'Finance · RFP & RFCA Non-Purchase Waiting',
            'fin-calr-nonpurch'      => 'Finance · CALR Non-Purchase Waiting',
            'treas-rfca-purchase'    => 'Treasury · RFCA Purchase Waiting',
            'treas-calr-purchase'    => 'Treasury · CALR Purchase Waiting',
            'treas-rfp-nonpurch'     => 'Treasury · RFP & RFCA Non-Purchase Waiting',
            'treas-calr-nonpurch'    => 'Treasury · CALR Non-Purchase Waiting',
            'wh-sppb'                => 'Warehouse · SPPB On Progress',
            'wh-po-solomon'          => 'Warehouse · PO Solomon',
            'wh-grn-solomon'         => 'Warehouse · GRN Solomon',
            'wh-issue-solomon'       => 'Warehouse · Issue Solomon',
            'purch-cs'               => 'Purchasing · CS',
            'purch-po-unsend'        => 'Purchasing · PO Unsend',
            'om-summary'             => 'OM · Summary',
            'gm-summary'             => 'GM · Summary',
            'my-activity'            => 'My Activity',
            'zoom'                   => 'Zoom',
        ];
    }

    public static function isTemplatePanel(?string $panelKey): bool
    {
        return $panelKey !== null && array_key_exists($panelKey, static::templatePanels());
    }

    /** The JSON endpoint a template panel lists from (read from its Blade template, the single source of truth). */
    public static function panelUrl(string $panelKey): ?string
    {
        if (!static::isTemplatePanel($panelKey)) {
            return null;
        }
        $file = resource_path('views/components/panel/' . $panelKey . '.blade.php');

        return is_file($file) && preg_match('/\burl="([^"]+)"/', file_get_contents($file), $m) ? $m[1] : null;
    }

    /** Every panel_key a custom tile may use. */
    public static function panelKeys(): array
    {
        return array_keys(static::templatePanels());
    }

    /** Can this dashboard open that panel? Any template panel; OM also keeps its embedded Approval panel. */
    public static function panelAllowed(string $dashCd, ?string $panelKey): bool
    {
        return !$panelKey
            || array_key_exists($panelKey, static::templatePanels())
            || (in_array(strtoupper($dashCd), ['DASHOM', 'DASHGM'], true) && $panelKey === 'approval');
    }

    /**
     * Tiles that show a live count bubble. Key = 'sys:<sys_menu.menu_id>' or 'panel:<panel_key>',
     * value = the source the dashboard-menu component polls/listens to.
     */
    public const BADGES = [
        'sys:MAILBOX'    => 'mailbox',   // unread emails (mailbox.unread-count)
        'panel:approval' => 'approval',  // waiting approvals (same number as the Approval Dashboard KPI)
        // every other template panel gets 'p:<panel_key>' = the rows its list shows (see DashboardMenuController::badges)
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
                    'kind'  => 'menu',
                    'external' => false,
                    'badge' => static::BADGES['sys:' . $m->menu_id] ?? null,
                    'progress' => (bool) ($row->in_progress ?? true),
                    'icon'  => $m->menu_icon,
                ];
            }

            $t = $catalog->get($row->menu_key);
            if (!$t) {
                return null;
            }

            return [
                'name'  => $t->menu_name,
                'desc'  => $row->menu_desc ?: $t->menu_desc,
                'href'  => static::resolveHref($t->route_name, $t->external_url),
                'external' => (bool) $t->external_url,
                'badge' => $t->panel_key ? (static::BADGES['panel:' . $t->panel_key] ?? (static::panelUrl($t->panel_key) ? 'p:' . $t->panel_key : null)) : null, // a panel without a list (the Summary panels) has nothing to count, so no bubble and no "All clear" row
                'panel' => $t->panel_key,
                'progress' => (bool) ($row->in_progress ?? true), // admin choice per dashboard: does its count appear in "Your Progress"
                'kind'  => $t->panel_key ? 'panel' : ($t->external_url ? 'link' : 'menu'),
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
