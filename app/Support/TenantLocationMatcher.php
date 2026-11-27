<?php

namespace App\Support;

use App\Models\MsLocation;
use App\Models\MsSubLocation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Links legacy tenancy tenants (mysql5: store name, unit, floor) to the purchasing app's Location / Sub Location (pgsql).
 * The two databases share no key, so everything here compares text.
 */
class TenantLocationMatcher
{
    /** Name key: "A & W" = "a and w" = "AandW". */
    public static function name(?string $s): string
    {
        return preg_replace('/[^a-z0-9]/', '', str_replace('&', 'and', mb_strtolower((string) $s)));
    }

    /** Name key of a sub location, ignoring a "MALL : " / "5.06 : " prefix. */
    public static function subName(?string $s): string
    {
        return self::name(preg_replace('/^[^:]*:/', '', (string) $s));
    }

    /** Unit key: "LG K02" = "LG.K2". Empty when there is no number (e.g. unit "LG"). */
    public static function unit(?string $s): string
    {
        preg_match_all('/[a-z]+|\d+/', strtolower((string) $s), $m);
        $key = implode('.', array_map(fn ($p) => ctype_digit($p) ? (string) (int) $p : $p, $m[0]));

        return preg_match('/\d/', $key) ? $key : '';
    }

    /** Unit key of a sub location's prefix ("LG.K2 : NAME"). */
    public static function subUnit(?string $s): string
    {
        return str_contains((string) $s, ':') ? self::unit(strstr($s, ':', true)) : '';
    }

    /** "Lantai LG", "... LANTAI LG", "... LOWER GROUND" -> LG */
    public static function floor(?string $s): string
    {
        $s = preg_replace(['/UPPER GROUND/', '/LOWER GROUND/', '/GROUND FLOOR/', '/BASEMENT\s*/', '/\s+FLOOR$/'], ['UG', 'LG', 'GF', 'B', ''], strtoupper(trim((string) $s)));

        return Str::afterLast($s, ' ');
    }

    /** Active locations of the companies (plus the shared "ALL" ones), keyed by location_id. */
    public static function locations(array $cpnies): Collection
    {
        return MsLocation::whereIn('cpny_id', [...$cpnies, 'ALL'])->where('status', 'A')->orderBy('location_name')
            ->get(['cpny_id', 'location_id', 'location_name'])->keyBy('location_id');
    }

    /**
     * The one location of a floor among the companies' own ones ("ALL" rows are other buildings);
     * the mall wins over an office tower. Null when not clear.
     */
    public static function floorLocation(Collection $locations, array $cpnies, ?string $floor): ?MsLocation
    {
        $floor = self::floor($floor);
        if ($floor === '') return null;

        $hits = $locations->filter(fn ($l) => in_array($l->cpny_id, $cpnies, true) && self::floor($l->location_name) === $floor);
        if ($hits->count() > 1) {
            $hits = $hits->reject(fn ($l) => preg_match('/OFFICE|APARTEMENT/i', $l->location_name));
        }

        return $hits->count() === 1 ? $hits->first() : null;
    }

    /** Table of user-confirmed "tenant = Sub Location" links (database/sql/ms_tenant_sub_location.sql). */
    public const LINK_TABLE = 'ms_tenant_sub_location';

    public static function canLink(): bool
    {
        return Schema::connection('pgsql')->hasTable(self::LINK_TABLE);
    }

    /** "site|store key|floor code" => sub_location_id. Empty while the table is not installed. */
    public static function links(?string $siteid = null): Collection
    {
        if (! self::canLink()) return collect();

        return DB::connection('pgsql')->table(self::LINK_TABLE)
            ->when($siteid, fn ($q) => $q->where('siteid', $siteid))
            ->get()
            ->mapWithKeys(fn ($l) => ["{$l->siteid}|{$l->tenant_key}|{$l->floor_code}" => $l->sub_location_id]);
    }

    /**
     * Best-guess WO location for a tenant. A confirmed link wins; otherwise
     * Sub Location = 1) named like the store, 2) else prefixed with the tenant's unit number; the floor breaks ties.
     * When that does not point to exactly one location, only the Location of the tenant's floor is returned
     * (sub_location_id = null). Null when neither is clear.
     */
    public static function match(string $siteid, string $cpny, ?string $store, ?string $unit, ?string $floor): ?array
    {
        $locations = self::locations([$cpny]);
        $subs = MsSubLocation::whereIn('cpny_id', [$cpny, 'ALL'])->where('status', 'A')->orderBy('sub_location_id')
            ->get(['location_id', 'sub_location_id', 'sub_location_name']);

        $store = self::name($store);

        $linked = self::links($siteid)->get("$siteid|$store|" . self::floor($floor));
        $s = $linked ? $subs->firstWhere('sub_location_id', $linked) : null;
        if ($s && $locations->has($s->location_id)) {
            return self::result($locations[$s->location_id], $s);
        }

        $hits = $store === '' ? collect() : $subs->filter(fn ($s) => self::subName($s->sub_location_name) === $store);

        $unit = self::unit($unit);
        if ($hits->isEmpty() && $unit !== '') {
            $hits = $subs->filter(fn ($s) => self::subUnit($s->sub_location_name) === $unit);
        }

        $hits = $hits->filter(fn ($s) => $locations->has($s->location_id));
        $onFloor = $hits->filter(fn ($s) => self::floor($locations[$s->location_id]->location_name) === self::floor($floor));
        if ($onFloor->isNotEmpty()) $hits = $onFloor;

        if ($hits->pluck('location_id')->unique()->count() === 1) {
            $s = $hits->first();

            return self::result($locations[$s->location_id], $s);
        }

        $l = self::floorLocation($locations, [$cpny], $floor);

        return $l ? ['location_id' => $l->location_id, 'sub_location_id' => null, 'label' => $l->location_name] : null;
    }

    private static function result(MsLocation $location, MsSubLocation $sub): array
    {
        return ['location_id' => $location->location_id, 'sub_location_id' => $sub->sub_location_id,
            'label' => $location->location_name . ' — ' . $sub->sub_location_name];
    }
}
