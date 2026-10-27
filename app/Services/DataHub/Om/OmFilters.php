<?php

namespace App\Services\DataHub\Om;

use Illuminate\Http\Request;

/**
 * The mall + filters every OM Dashboard page and AJAX section works from.
 *
 * The page URL never carries them: what a user picked (company, dates, toggles) lives in their
 * session (see remember()/fromSession()). The tab is never saved: every visit opens on Overview.
 * Dates default to today, and a saved range only holds for the day it was picked. The AJAX
 * endpoints are called by the page itself and receive the values explicitly (fromRequest()).
 */
class OmFilters
{
    public const GRANULARITIES = ['monthly', 'weekly', 'daily'];

    public const MOVEMENT_TYPES = ['all', 'new', 'pullout'];

    public const TABS = ['overview', 'department', 'kaizen'];

    private const SESSION_KEY = 'om_dashboard';

    public function __construct(
        public readonly string $mall,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly string $granularity = 'monthly',
        public readonly string $movementType = 'all',
        public readonly string $tab = 'overview',
    ) {}

    /** AJAX sections: values come from the query string; a mall the user may not open is a 403/404. */
    public static function fromRequest(Request $request, OmMalls $malls): self
    {
        return self::build($request->input(), $malls->resolve($request->query('mall')));
    }

    /** The page: values come from the user's session; a stale/forbidden mall falls back to their default. */
    public static function fromSession(Request $request, OmMalls $malls): self
    {
        $state = $request->session()->get(self::SESSION_KEY, []);

        // A date range picked on an earlier day is dropped, so a new day starts on today again.
        if (($state['dates_on'] ?? null) !== now()->toDateString()) {
            unset($state['start_date'], $state['end_date']);
        }
        // The tab is never remembered: Overview is always the starting point.
        unset($state['tab']);

        return self::build($state, $malls->resolveOrDefault($state['mall'] ?? null));
    }

    /** Keeps only the known, valid, permitted values from arbitrary input. */
    public static function sanitize(array $input, OmMalls $malls): array
    {
        $clean = [];

        if (is_string($input['mall'] ?? null) && array_key_exists(strtoupper(trim($input['mall'])), $malls->allowed())) {
            $clean['mall'] = strtoupper(trim($input['mall']));
        }
        foreach (['start_date', 'end_date'] as $key) {
            if (self::isDate($input[$key] ?? null)) {
                $clean[$key] = $input[$key];
            }
        }
        $lists = ['traffic_granularity' => self::GRANULARITIES, 'tenant_movement_type' => self::MOVEMENT_TYPES];
        foreach ($lists as $key => $allowed) {
            if (in_array($input[$key] ?? null, $allowed, true)) {
                $clean[$key] = $input[$key];
            }
        }

        return $clean;
    }

    /** Merges already-sanitized values into the user's saved selection. */
    public static function remember(Request $request, array $clean): void
    {
        if (isset($clean['start_date']) || isset($clean['end_date'])) {
            $clean['dates_on'] = now()->toDateString();
        }

        $request->session()->put(self::SESSION_KEY, array_merge($request->session()->get(self::SESSION_KEY, []), $clean));
    }

    private static function build(array $in, string $mall): self
    {
        $default = now()->toDateString();

        return new self(
            mall: $mall,
            startDate: self::isDate($in['start_date'] ?? null) ? $in['start_date'] : $default,
            endDate: self::isDate($in['end_date'] ?? null) ? $in['end_date'] : $default,
            granularity: self::oneOf($in['traffic_granularity'] ?? null, self::GRANULARITIES),
            movementType: self::oneOf($in['tenant_movement_type'] ?? null, self::MOVEMENT_TYPES),
            tab: self::oneOf($in['tab'] ?? null, self::TABS),
        );
    }

    private static function oneOf(mixed $value, array $allowed): string
    {
        return in_array($value, $allowed, true) ? $value : $allowed[0];
    }

    private static function isDate(mixed $value): bool
    {
        return is_string($value) && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
    }
}
