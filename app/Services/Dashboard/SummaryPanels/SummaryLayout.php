<?php

namespace App\Services\Dashboard\SummaryPanels;

use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * The card list a Summary panel shows. The ONLY place that decides it, so layers can be added here without
 * touching the Blade renderer, the JSON endpoint or the JS:
 *
 *   1. config/summary_panels.php          — the catalog (All Reports cards) + each panel's default cards
 *   2. ms_summary_layout (pgsql2)         — admin layout: which cards, order / width / title / colour / height
 *   3. per-user layout (planned, DB)      — hide / reorder for one user
 *
 * A layer only stores card ids + settings; every card it can pick is a catalog entry.
 */
class SummaryLayout
{
    public const CONN = 'pgsql2';
    public const TABLE = 'ms_summary_layout';
    public const SPANS = [3, 4, 5, 6, 7, 8, 12];
    public const COLORS = ['violet', 'blue', 'green', 'orange', 'red', 'pink', 'cyan'];

    /** The panel's config (title, subtitle, default ids), or null for an unknown panel. */
    public static function panel(string $panel): ?array
    {
        return config("summary_panels.panels.$panel");
    }

    /** Every card a panel may use (shared catalog). @return list<array> */
    public static function catalog(string $panel = ''): array
    {
        return array_values(config('summary_panels.catalog', []));
    }

    /** What the panel shows: the resolved layout, visible cards only. @return list<array> */
    public static function widgets(string $panel, $user = null): array
    {
        return array_values(array_filter(static::resolved($panel), fn ($w) => $w['is_visible']));
    }

    /**
     * Every catalog card with its effective settings and `is_visible`: the saved layout when there is one,
     * otherwise the panel's default cards first (visible) and the rest after (available, hidden).
     * @return list<array>
     */
    public static function resolved(string $panel): array
    {
        $catalog = collect(static::catalog())->keyBy('id');
        $default = static::panel($panel)['default'] ?? [];

        $out = [];
        foreach (static::overrides($panel) as $r) {
            if (!$catalog->has($r->widget_id)) {
                continue; // card removed from the catalog since it was saved
            }
            $w = $catalog[$r->widget_id];
            $w['is_visible'] = (bool) $r->is_visible;
            foreach (['span', 'title', 'color', 'height'] as $k) {
                if ($r->$k !== null && $r->$k !== '') {
                    $w[$k] = $r->$k;
                }
            }
            $out[$w['id']] = $w;
        }

        // not saved yet: the panel's default cards (visible, in order), then every other catalog card (available)
        foreach ($default as $id) {
            if ($catalog->has($id)) {
                $out[$id] ??= $catalog[$id] + ['is_visible' => true];
            }
        }
        foreach ($catalog as $id => $w) {
            $out[$id] ??= $w + ['is_visible' => false];
        }

        return array_values($out);
    }

    private static function overrides(string $panel)
    {
        try {
            return DB::connection(self::CONN)->table(self::TABLE)->where('panel_key', $panel)->orderBy('sort_no')->get();
        } catch (Throwable $e) {
            report($e); // table/DB unavailable: fall back to the config default rather than break the panel

            return collect();
        }
    }
}
