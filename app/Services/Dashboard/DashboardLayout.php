<?php

namespace App\Services\Dashboard;

use App\Http\Controllers\MultiDashboardController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * How a dashboard is arranged: ROWS of COLUMNS (widths out of 12) holding BLOCKS in order.
 * The ONLY place that decides it, so the renderer (dashboard-top) and the editor (Dashboard Setup) never disagree.
 *
 *   1. the standard arrangement (defaultLayout) — what a dashboard gets until an admin saves one
 *   2. ms_dashboard_layout (pgsql2)             — the admin's arrangement, same for everyone assigned to the dashboard
 *
 * A block that does not apply to a dashboard (no menu tiles, no counts, no KPI cards of its own) is skipped at render time
 * and an emptied column / row folds away, so a saved arrangement can never produce a broken page.
 *
 * Stored JSON: {"rows":[{"columns":[{"span":9,"blocks":[{"id":"menu","v":true}, ...]}, ...]}, ...]}
 * (an older {"columns":[...]} is one row). A block that is not in it is OFF.
 */
class DashboardLayout
{
    public const CONN = 'pgsql2';
    public const TABLE = 'ms_dashboard_layout';
    public const SPANS = [3, 4, 5, 6, 7, 8, 9, 12];
    public const MAX_ROWS = 6;
    public const MAX_COLUMNS = 3;
    /** Auto-refresh choices (seconds). */
    public const REFRESH_CHOICES = [10, 20, 30, 60, 120];
    /** What every dashboard, tab and panel refreshes at until an admin picks another value in Dashboard Setup. */
    public const DEFAULT_REFRESH = 20;
    public const TITLE_MAX = 80;
    public const SUBTITLE_MAX = 160;

    /** Heading of each dashboard until an admin sets one in Dashboard Setup: code => [title, subtitle]. */
    public const DEFAULT_HEADERS = [
        'DASHOM'        => ['OM Dashboard', 'Favourites • Main Menu'],
        'DASHGM'        => ['GM Dashboard', 'Favourites • Main Menu'],
        'DASHIT'        => ['IT Dashboard', 'Ticketing • Access Request • IT Recommendation • Approval'],
        'DASHGA'        => ['General Affairs Dashboard', 'Approval • Transportation • Parking'],
        'DASHHR'        => ['HR Dashboard', 'PRF • Recruitment • Approval'],
        'DASHFIN'       => ['Finance Dashboard', 'Approval • RFCA Purchase • CALR Purchase • Non-Purchase'],
        'DASHTREAS'     => ['Treasury Dashboard', 'Approval • RFCA Purchase • CALR Purchase • Non-Purchase'],
        'DASHWH'        => ['Warehouse Dashboard', 'Approval • SPPB • Solomon Integration'],
        'DASHOPR'       => ['Operational Dashboard', 'Approval • Work Order'],
        'DASHCC'        => ['Dashboard Cost Control', 'Approval • PO Monitoring • Budget Monitoring • IM Budget'],
        'DASHPURCH'     => ['Purchasing Dashboard', 'Approval • Canvass Sheet • Purchase Order'],
        'DASHCORPTEK'   => ['Corporate Teknik Dashboard', 'Waiting Approval • Approval History • Ticket • Berita Acara'],
        'RECRUITDASH'   => ['Recruitment Dashboard', 'Approval • Applicants • Self Register'],
        'DASHVPCOLL'    => ['Dashboard Collection', 'Approval • Expired Voucher • Settlement'],
        'DASHVPPRMTN'   => ['Dashboard Promotion', 'Approval • Expired Product • Settlement'],
        'DASHVPLOYALTY' => ['Dashboard Loyalty', 'Approval • Expired Voucher & Product'],
    ];

    /**
     * needs: null = always; 'tiles' = the dashboard has Main Menu tiles; 'counts' = a tile carries a count badge;
     * 'kpis' / 'tabs' = the dashboard has its own KPI cards / work tabs (resources/views/components/multidashboard/blocks/<component>/).
     * locked = cannot be taken off. hideable = keep rendering it (hidden) when off, because the dashboard's script reads its elements.
     */
    public const BLOCKS = [
        'menu'       => ['label' => 'Main Menu',     'desc' => 'The tiles of this dashboard',                                'needs' => 'tiles',  'locked' => false],
        'favourites' => ['label' => 'My Favourites', 'desc' => 'Each user\'s own shortcuts',                                 'needs' => null,     'locked' => false],
        'panels'     => ['label' => 'Panel area',    'desc' => 'The panel opened from a tile appears here',                  'needs' => 'tiles',  'locked' => true],
        'progress'   => ['label' => 'Your Progress', 'desc' => 'What is still waiting (counts from the menu tiles)',        'needs' => 'counts', 'locked' => false],
        'todo'       => ['label' => 'To Do List',    'desc' => 'Personal tasks, calendar and meetings',                      'needs' => null,     'locked' => false],
        'kpis'       => ['label' => 'KPI cards',     'desc' => 'The counters at the top; each one opens its tab',            'needs' => 'kpis',   'locked' => false],
        'tabs'       => ['label' => 'Work tabs',     'desc' => 'Waiting Approval, history and this dashboard\'s own lists',  'needs' => 'tabs',   'locked' => false],
    ];

    /** Blocks whose markup is also read by the dashboard's JavaScript: when off they are still rendered, but hidden. */
    public const KEEP_HIDDEN = ['kpis', 'tabs'];

    private static function cacheKey(string $dash): string
    {
        return 'dash-layout:' . strtoupper($dash);
    }

    private static function component(string $dash): ?string
    {
        return MultiDashboardController::DASHBOARD_COMPONENTS[strtoupper($dash)] ?? null;
    }

    /** View of a dashboard's own block (kpis / tabs), or null when it has none. */
    public static function blockView(string $dash, string $block): ?string
    {
        $comp = self::component($dash);
        $view = $comp ? "components.multidashboard.blocks.$comp.$block" : null;

        return $view && view()->exists($view) ? $view : null;
    }

    /** Every block this dashboard can have: all of them, except KPI cards / Work tabs where it has none of its own. @return list<string> */
    public static function offered(string $dash): array
    {
        return array_values(array_filter(array_keys(self::BLOCKS), function ($id) use ($dash) {
            $need = self::BLOCKS[$id]['needs'];

            return !in_array($need, ['kpis', 'tabs'], true) || self::blockView($dash, $need) !== null;
        }));
    }

    /**
     * The saved rows, plus any block that appeared after the admin saved (it is not in the saved "known" list): those go at the end of
     * the last column, visible. Without this a block added to a dashboard later (e.g. its Work tabs) would be OFF in every saved
     * arrangement and the dashboard's main content would vanish. null when no arrangement is saved.
     */
    public static function storedRows(string $dash): ?array
    {
        $stored = self::stored($dash);
        $rows = $stored['rows'] ?? null;
        if (!$rows) {
            return null;
        }

        if (isset($stored['known']) && is_array($stored['known'])) {
            $placed = collect($rows)->flatMap(fn ($r) => collect($r['columns'])->flatMap(fn ($c) => array_column($c['blocks'], 'id')))->all();
            $new = array_values(array_diff(self::offered($dash), $stored['known'], $placed));
            if ($new) {
                $last = count($rows) - 1;
                $lastCol = count($rows[$last]['columns']) - 1;
                foreach ($new as $id) {
                    $rows[$last]['columns'][$lastCol]['blocks'][] = ['id' => $id, 'v' => true];
                }
            }
        }

        return $rows;
    }

    /** What applies to this dashboard for this user. @param \Illuminate\Support\Collection $items the dashboard's menu tiles */
    public static function flags(string $dash, $items): array
    {
        return [
            'tiles'  => $items->isNotEmpty(),
            'counts' => $items->contains(fn ($i) => !empty($i['badge'])),
            'kpis'   => self::blockView($dash, 'kpis') !== null,
            'tabs'   => self::blockView($dash, 'tabs') !== null,
        ];
    }

    /** What a dashboard looks like until an admin saves an arrangement. */
    public static function defaultLayout(array $has): array
    {
        $b = fn (string ...$ids) => array_map(fn ($id) => ['id' => $id, 'v' => true], $ids);
        $row = fn (array ...$cols) => ['columns' => $cols];
        $col = fn (int $span, array $blocks) => ['span' => $span, 'blocks' => $blocks];

        if (!$has['tiles']) {
            $rows = [$row($col(12, $b('favourites', 'todo')))];
        } elseif ($has['counts']) {
            $rows = [$row($col(9, $b('menu', 'favourites', 'panels')), $col(3, $b('progress', 'todo')))];
        } else {
            $rows = [$row($col(12, $b('menu', 'favourites', 'todo', 'panels')))];
        }
        if ($has['kpis'] ?? false) {
            $rows[] = $row($col(12, $b('kpis')));
        }
        if ($has['tabs'] ?? false) {
            $rows[] = $row($col(12, $b('tabs')));
        }

        return ['rows' => $rows];
    }

    /** The admin's arrangement (always in rows form), or null when none is saved. Cached briefly; saving clears it. */
    public static function stored(string $dash): ?array
    {
        $raw = Cache::remember(self::cacheKey($dash), 300, function () use ($dash) {
            try {
                $row = DB::connection(self::CONN)->table(self::TABLE)->where('dash_cd', strtoupper($dash))->first();
            } catch (Throwable $e) {
                return false; // table not created yet: behave as "nothing saved"
            }

            return $row ? (json_decode($row->layout, true) ?: false) : false;
        });

        if (!$raw) {
            return null;
        }
        if (isset($raw['columns']) && !isset($raw['rows'])) {
            $raw = ['rows' => [['columns' => $raw['columns']]]]; // the first version of the editor saved a single row
        }

        return $raw;
    }

    /** The page settings an admin saved (null = not set). @return array{title: ?string, subtitle: ?string, refresh: ?int} */
    public static function settings(string $dash): array
    {
        $s = (self::stored($dash) ?? [])['settings'] ?? [];

        return [
            'title'    => ($s['title'] ?? '') !== '' ? (string) $s['title'] : null,
            'subtitle' => ($s['subtitle'] ?? '') !== '' ? (string) $s['subtitle'] : null,
            'refresh'  => in_array((int) ($s['refresh'] ?? 0), self::REFRESH_CHOICES, true) ? (int) $s['refresh'] : null,
        ];
    }

    /** Seconds between auto-refreshes of this dashboard: the admin's choice, else DEFAULT_REFRESH. */
    public static function refreshSeconds(string $dash): int
    {
        return self::settings($dash)['refresh'] ?? self::DEFAULT_REFRESH;
    }

    /** The heading to print: the admin's, else the dashboard's default. @return array{title: string, subtitle: string, customSubtitle: bool} */
    public static function header(string $dash): array
    {
        $set = self::settings($dash);
        $def = self::DEFAULT_HEADERS[strtoupper($dash)] ?? [strtoupper($dash), ''];

        return ['title' => $set['title'] ?? $def[0], 'subtitle' => $set['subtitle'] ?? $def[1], 'customSubtitle' => $set['subtitle'] !== null];
    }

    /** Clean what the editor sends for the page settings; throws InvalidArgumentException on a bad value. */
    public static function sanitizeSettings(array $in): array
    {
        $clean = fn ($v, int $max, string $what) => ($t = trim(strip_tags((string) ($v ?? '')))) === '' ? null
            : (mb_strlen($t) > $max ? throw new \InvalidArgumentException("The $what is longer than $max characters.") : $t);

        $refresh = $in['refresh'] ?? null;
        if ($refresh !== null && $refresh !== '' && !in_array((int) $refresh, self::REFRESH_CHOICES, true)) {
            throw new \InvalidArgumentException('Refresh every ' . (int) $refresh . ' seconds is not one of the choices.');
        }

        return [
            'title'    => $clean($in['title'] ?? null, self::TITLE_MAX, 'title'),
            'subtitle' => $clean($in['subtitle'] ?? null, self::SUBTITLE_MAX, 'subtitle'),
            'refresh'  => ($refresh === null || $refresh === '') ? null : (int) $refresh,
        ];
    }

    /** Validate what the editor sends; returns the clean layout or throws InvalidArgumentException. */
    public static function sanitize(array $rows): array
    {
        if (count($rows) < 1 || count($rows) > self::MAX_ROWS) {
            throw new \InvalidArgumentException('A dashboard has 1 to ' . self::MAX_ROWS . ' rows.');
        }

        $seen = [];
        $outRows = [];
        foreach ($rows as $r) {
            $columns = $r['columns'] ?? [];
            if (count($columns) < 1 || count($columns) > self::MAX_COLUMNS) {
                throw new \InvalidArgumentException('A row has 1 to ' . self::MAX_COLUMNS . ' columns.');
            }

            $outCols = [];
            foreach ($columns as $c) {
                $span = (int) ($c['span'] ?? 0);
                if (!in_array($span, self::SPANS, true)) {
                    throw new \InvalidArgumentException('Column width ' . $span . ' is not allowed.');
                }
                $blocks = [];
                foreach (($c['blocks'] ?? []) as $b) {
                    $id = (string) ($b['id'] ?? '');
                    if (!isset(self::BLOCKS[$id]) || isset($seen[$id])) {
                        throw new \InvalidArgumentException('Unknown or repeated block: ' . $id);
                    }
                    $seen[$id] = true;
                    $blocks[] = ['id' => $id, 'v' => true];
                }
                $outCols[] = ['span' => $span, 'blocks' => $blocks];
            }

            if (array_sum(array_column($outCols, 'span')) !== 12) {
                throw new \InvalidArgumentException('Column widths in a row must add up to 12.');
            }
            $outRows[] = ['columns' => $outCols];
        }

        return ['rows' => $outRows];
    }

    /**
     * @param array|null $rows     the sanitized {"rows": [...]} to store, or null to leave the stored arrangement as it is
     * @param array      $settings the sanitized page settings (replace the stored ones)
     */
    public static function save(string $dash, ?array $rows, array $settings, ?string $by): void
    {
        $db = DB::connection(self::CONN)->table(self::TABLE);
        $row = (clone $db)->where('dash_cd', strtoupper($dash))->first();
        $cur = $row ? (json_decode($row->layout, true) ?: []) : [];
        if (isset($cur['columns']) && !isset($cur['rows'])) {
            $cur = ['rows' => [['columns' => $cur['columns']]], 'settings' => $cur['settings'] ?? []]; // first-version single row
        }
        unset($cur['columns']);

        if ($rows !== null) {
            $cur['rows'] = $rows['rows'];
            $cur['known'] = self::offered($dash); // the blocks the admin saw: anything outside this list is new to the arrangement
        }
        $cur['settings'] = $settings;

        DB::connection(self::CONN)->table(self::TABLE)->updateOrInsert(
            ['dash_cd' => strtoupper($dash)],
            ['layout' => json_encode($cur), 'updated_by' => $by, 'updated_at' => now()]
        );
        Cache::forget(self::cacheKey($dash));
    }

    public static function reset(string $dash): void
    {
        DB::connection(self::CONN)->table(self::TABLE)->where('dash_cd', strtoupper($dash))->delete();
        Cache::forget(self::cacheKey($dash));
    }

    /**
     * What the page actually draws for this user: the saved (or standard) arrangement with every block that does not
     * apply removed, empty columns and rows folded away and the widths of each row put back to 12.
     *
     * @return array{rows: list<list<array{span:int, blocks:list<string>}>>, hidden: list<string>, shown: list<string>}
     *         hidden = blocks that are off but must still be in the page (their script reads them), rendered out of sight;
     *         shown = every block id that is drawn
     */
    public static function resolve(string $dash, array $has): array
    {
        $storedRows = self::storedRows($dash);
        $rows = $storedRows ?: self::defaultLayout($has)['rows'];

        // The Panel area is the one block an admin cannot drop: a dashboard with tiles cannot open a tile's panel without it.
        if ($storedRows && ($has['tiles'] ?? false)) {
            $known = collect($rows)->flatMap(fn ($r) => collect($r['columns'])->flatMap(fn ($c) => array_column($c['blocks'], 'id')))->all();
            if (!in_array('panels', $known, true)) {
                $last = count($rows) - 1;
                $lastCol = count($rows[$last]['columns']) - 1;
                $rows[$last]['columns'][$lastCol]['blocks'][] = ['id' => 'panels', 'v' => true];
            }
        }

        $applies = fn (string $id) => ($need = self::BLOCKS[$id]['needs'] ?? null) === null ? true : (bool) ($has[$need] ?? false);

        $shown = [];
        $resolvedRows = [];
        foreach ($rows as $r) {
            $cols = [];
            foreach ($r['columns'] as $c) {
                $ids = [];
                foreach ($c['blocks'] as $b) {
                    $id = $b['id'] ?? '';
                    if (isset(self::BLOCKS[$id]) && $applies($id) && (($b['v'] ?? true) || self::BLOCKS[$id]['locked'])) {
                        $ids[] = $id;
                        $shown[$id] = true;
                    }
                }
                if ($ids) {
                    $cols[] = ['span' => (int) $c['span'], 'blocks' => $ids];
                }
            }
            if ($cols) {
                $resolvedRows[] = self::normaliseSpans($cols);
            }
        }

        $hidden = [];
        foreach (self::KEEP_HIDDEN as $id) {
            if (($has[self::BLOCKS[$id]['needs']] ?? false) && !isset($shown[$id])) {
                $hidden[] = $id;
            }
        }

        return ['rows' => $resolvedRows, 'hidden' => $hidden, 'shown' => array_keys($shown)];
    }

    /** Widths of the remaining columns scaled back to 12 (a single column is always 12). */
    private static function normaliseSpans(array $cols): array
    {
        if (count($cols) === 1) {
            $cols[0]['span'] = 12;

            return $cols;
        }
        $sum = array_sum(array_column($cols, 'span'));
        if ($sum === 12) {
            return $cols;
        }

        foreach ($cols as &$c) {
            $c['span'] = max(3, (int) round($c['span'] / $sum * 12));
        }
        unset($c);

        // put any rounding difference on the widest column
        $diff = 12 - array_sum(array_column($cols, 'span'));
        if ($diff !== 0) {
            $widest = array_keys(array_column($cols, 'span'), max(array_column($cols, 'span')))[0];
            $cols[$widest]['span'] += $diff;
        }

        return $cols;
    }
}
