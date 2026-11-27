<?php

namespace App\Http\Controllers;

use App\Models\MsSubLocation;
use App\Support\TenantLocationMatcher as Matcher;
use App\Support\TenantRelationScope;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Setup > Locations > From Tenant: every tenant of the tenancy master (mysql5) against the Sub Locations.
 * Existing ones are listed only; missing ones can be reviewed and added. A store name is never doubled within a Location.
 */
class TenantSubLocationController extends Controller
{
    public function index()
    {
        return view('pages.location.from-tenant');
    }

    public function json()
    {
        $tenants = DB::connection('mysql5')->table('mstenant as t')
            ->leftJoin('msfloor as f', 'f.id', '=', 't.floorid')
            ->where('t.status', 'A')->whereIn('t.siteid', array_keys(TenantRelationScope::SITE_COMPANY))
            ->orderBy('t.storename')->get(['t.siteid', 't.storename', 't.unit', 'f.floor'])
            ->groupBy('siteid');

        $links = Matcher::links();
        $rows = [];
        $sites = [];
        foreach (TenantRelationScope::SITE_COMPANY as $site => $cpnies) {
            $locations = Matcher::locations($cpnies);
            $sites[$site] = $locations->whereIn('cpny_id', $cpnies)
                ->map(fn ($l) => ['id' => $l->location_id, 'name' => $l->location_name])->values();

            // Comparison keys are computed once; every status counts, so an inactive one is not silently duplicated.
            $subs = MsSubLocation::whereIn('cpny_id', [...$cpnies, 'ALL'])->get(['location_id', 'sub_location_id', 'sub_location_name', 'status'])
                ->filter(fn ($s) => $locations->has($s->location_id))
                ->map(fn ($s) => [
                    'id' => $s->sub_location_id, 'label' => $s->sub_location_name, 'active' => $s->status === 'A',
                    'location' => $locations[$s->location_id]->location_name, 'location_id' => $s->location_id,
                    'own' => in_array($locations[$s->location_id]->cpny_id, $cpnies, true), // not a shared "ALL" building
                    'floor' => Matcher::floor($locations[$s->location_id]->location_name),
                    'name' => Matcher::subName($s->sub_location_name), 'unit' => Matcher::subUnit($s->sub_location_name),
                ]);
            $byName = $subs->groupBy('name');
            $byFloor = $subs->groupBy('floor');
            $byId = $subs->keyBy('id');

            // One row per store per floor (a store can be registered for several units).
            $groups = collect($tenants[$site] ?? [])
                ->filter(fn ($t) => Matcher::name($t->storename) !== '')
                ->groupBy(fn ($t) => Matcher::name($t->storename) . '|' . Matcher::floor($t->floor));

            foreach ($groups as $key => $group) {
                $t = $group->first();
                [$name, $floor] = explode('|', $key);

                $units = $group->map(fn ($x) => trim((string) $x->unit))->filter()->unique()->values();
                $unitKeys = $units->map(fn ($u) => Matcher::unit($u))->filter()->all();

                // Already exists = confirmed by a user (link), else the same store name on this floor (active or not).
                $linked = $byId->get($links->get("$site|$key", ''));
                $sameName = $byName->get($name, collect());
                $existing = $linked ? collect([$linked]) : $sameName->filter(fn ($s) => $s['own'] && ($floor === '' || $s['floor'] === $floor))
                    ->sortByDesc('active')->values();

                // Otherwise show possible duplicates to decide on: same unit or alike name on this floor, same name elsewhere.
                $similar = collect();
                if ($existing->isEmpty()) {
                    $alike = $byFloor->get($floor, collect())->filter(fn ($s) => in_array($s['unit'], $unitKeys, true)
                        || (strlen($name) >= 4 && strlen($s['name']) >= 4 && (str_contains($s['name'], $name) || str_contains($name, $s['name']))));
                    $similar = $alike->merge($sameName)->unique('id')->take(3)->values();
                }

                $location = Matcher::floorLocation($locations, $cpnies, $t->floor);

                $rows[] = [
                    'key' => "$site|$key", 'tenant_key' => $name, 'floor_code' => $floor, 'linked' => (bool) $linked, 'site' => $site,'store' => trim($t->storename), 'floor' => $t->floor,
                    'unit' => $units->implode(', '), 'location_id' => $location->location_id ?? '',
                    'status' => $existing->isNotEmpty() ? 'exists' : ($similar->isNotEmpty() ? 'similar' : 'new'),
                    'name' => $existing->isNotEmpty() ? '' : $this->suggestName($cpnies[0], $t->storename, $units->first()),
                    'existing' => $existing->map(fn ($s) => Arr::only($s, ['id', 'label', 'location', 'active'])),
                    'similar' => $similar->map(function ($s) use ($name, $floor, $unitKeys, $groups, $links) {
                        $reason = match (true) {
                            $s['name'] === $name => $s['floor'] === $floor ? 'Same name, other building' : 'Same name, other floor',
                            in_array($s['unit'], $unitKeys, true) => 'Same unit',
                            default => 'Similar name',
                        };

                        // "Change location" is for the company's own record on another floor; "taken" = another tenant relies on it there.
                        $move = $reason === 'Same name, other floor' && $s['own']
                            ? ['taken' => $groups->has("$name|{$s['floor']}") || $links->contains($s['id'])] : null;

                        return Arr::only($s, ['id', 'label', 'location', 'location_id', 'active']) + compact('reason', 'move');
                    }),
                ];
            }
        }

        return response()->json(['rows' => $rows, 'sites' => $sites]);
    }

    /** Follows how each company already names its sub locations: "MALL : STORE" (KK), "5.06 : STORE" (Blok M), else "STORE". */
    private function suggestName(string $cpny, string $store, ?string $unit): string
    {
        $store = preg_replace('/\s+/', ' ', trim($store));

        if ($cpny === 'EP') {
            $store = "MALL : $store";
        } elseif ($cpny === 'PSA' && preg_match('/\d/', (string) $unit)) {
            // "LG  K02" -> "LG.K02", "UG 16 - 17" -> "UG.16-17"
            $code = preg_replace(['/\s*-\s*/', '/\s+/'], ['-', ' '], trim($unit));
            $store = preg_replace('/ /', '.', $code, 1) . " : $store";
        }

        return mb_strtoupper($store);
    }

    public function store(Request $request)
    {
        $in = $request->validate([
            'items'               => 'required|array|min:1|max:1000',
            'items.*.site'        => ['required', Rule::in(array_keys(TenantRelationScope::SITE_COMPANY))],
            'items.*.location_id' => 'required|string|max:20',
            'items.*.name'        => 'required|string|max:255',
        ]);

        $db = DB::connection('pgsql');
        $username = Auth::user()->username;

        [$created, $skipped] = $db->transaction(function () use ($db, $in, $username) {
            // sub_location_id has no unique index: one batch at a time so two users cannot get the same number.
            $db->select("select pg_advisory_xact_lock(hashtext('ms_sub_location_id'))");

            $locations = Matcher::locations(array_merge(...array_values(TenantRelationScope::SITE_COMPANY)));
            $created = [];
            $skipped = [];

            foreach ($in['items'] as $item) {
                $name = mb_strtoupper(trim($item['name']));
                $location = $locations->get($item['location_id']);
                if (! $location || ! in_array($location->cpny_id, TenantRelationScope::SITE_COMPANY[$item['site']], true)) {
                    $skipped[] = "$name (location not valid for site {$item['site']})";
                    continue;
                }

                $key = Matcher::subName($name);
                if ($key === '') {
                    $skipped[] = "$name (no store name)";
                    continue;
                }

                // "PUYO" = "Puyo" = "MALL : PUYO"; rows added earlier in this batch count too.
                $exists = MsSubLocation::where('location_id', $location->location_id)->pluck('sub_location_name')
                    ->contains(fn ($n) => Matcher::subName($n) === $key);
                if ($exists) {
                    $skipped[] = "$name (already in {$location->location_name})";
                    continue;
                }

                $id = $this->nextId($location->location_id);
                MsSubLocation::create([
                    'cpny_id' => $location->cpny_id, 'location_id' => $location->location_id,
                    'sub_location_id' => $id, 'sub_location_name' => $name,
                    'status' => 'A', 'created_by' => $username, 'created_at' => now(),
                ]);
                $created[] = "$id · $name ({$location->location_name})";
            }

            return [$created, $skipped];
        });

        return response()->json(['added' => count($created), 'created' => $created, 'skipped' => $skipped]);
    }

    /**
     * "This tenant is that existing Sub Location". With a name it is renamed too; with a location_id it is moved there
     * (its Sub Location ID never changes, so documents that use it stay connected).
     */
    public function link(Request $request)
    {
        $in = $request->validate($this->tenantRules() + [
            'sub_location_id' => 'required|string|max:20',
            'name'            => 'nullable|string|max:255',
            'location_id'     => 'nullable|string|max:20',
        ]);
        if (! Matcher::canLink()) return $this->noLinkTable();

        $cpnies = TenantRelationScope::SITE_COMPANY[$in['site']];
        $locations = Matcher::locations($cpnies);
        $sub = MsSubLocation::where('sub_location_id', $in['sub_location_id'])->first();
        if (! $sub || ! $locations->has($sub->location_id)) {
            return response()->json(['message' => 'This Sub Location is not available for this site.'], 422);
        }

        $changes = [];
        $name = mb_strtoupper(trim($in['name'] ?? ''));
        if ($name !== '') {
            if (Matcher::subName($name) === '') return response()->json(['message' => 'Please fill the store name.'], 422);
            $changes['sub_location_name'] = $name;
        }

        $newId = $in['location_id'] ?? null;
        if ($newId && $newId !== $sub->location_id) {
            // only between the company's own Locations: a shared "ALL" record stays where it is
            $target = $locations->get($newId);
            if (! $target || ! in_array($target->cpny_id, $cpnies, true) || ! in_array($locations[$sub->location_id]->cpny_id, $cpnies, true)) {
                return response()->json(['message' => 'This Sub Location cannot be moved to that Location.'], 422);
            }
            $changes += ['location_id' => $target->location_id, 'cpny_id' => $target->cpny_id];
        }

        if ($changes) {
            $key = Matcher::subName($changes['sub_location_name'] ?? $sub->sub_location_name);
            $same = MsSubLocation::where('location_id', $changes['location_id'] ?? $sub->location_id)->where('id', '!=', $sub->id)
                ->get(['sub_location_id', 'sub_location_name'])->first(fn ($s) => Matcher::subName($s->sub_location_name) === $key);
            if ($same) {
                return response()->json(['message' => "That name already exists in this Location: {$same->sub_location_id} - {$same->sub_location_name}"], 422);
            }
        }

        $username = Auth::user()->username;
        DB::connection('pgsql')->transaction(function () use ($in, $sub, $changes, $username) {
            if ($changes) {
                $sub->update($changes + ['updated_by' => $username, 'updated_at' => now()]);
            }
            $this->linkQuery($in)->delete();
            DB::connection('pgsql')->table(Matcher::LINK_TABLE)->insert([
                'siteid' => $in['site'], 'tenant_key' => $in['tenant_key'], 'floor_code' => $in['floor_code'] ?? '',
                'sub_location_id' => $sub->sub_location_id, 'created_by' => $username, 'created_at' => now(),
            ]);
        });

        return response()->json(['success' => true]);
    }

    /** Removes the link only; the Sub Location (and any rename or move) stays. */
    public function unlink(Request $request)
    {
        $in = $request->validate($this->tenantRules());
        if (! Matcher::canLink()) return $this->noLinkTable();

        $this->linkQuery($in)->delete();

        return response()->json(['success' => true]);
    }

    private function tenantRules(): array
    {
        return [
            'site'       => ['required', Rule::in(array_keys(TenantRelationScope::SITE_COMPANY))],
            'tenant_key' => 'required|string|max:255',
            'floor_code' => 'nullable|string|max:50',
        ];
    }

    private function linkQuery(array $in)
    {
        return DB::connection('pgsql')->table(Matcher::LINK_TABLE)
            ->where('siteid', $in['site'])->where('tenant_key', $in['tenant_key'])->where('floor_code', $in['floor_code'] ?? '');
    }

    private function noLinkTable()
    {
        return response()->json(['message' => 'Table ms_tenant_sub_location is not installed yet (database/sql/ms_tenant_sub_location.sql).'], 422);
    }

    /** Existing numbering: location AW0000012 -> AW + 012 + 4-digit running number (AW0120001). */
    private function nextId(string $locationId): string
    {
        $prefix = substr($locationId, 0, 2) . substr($locationId, -3);
        $last = MsSubLocation::where('sub_location_id', 'like', $prefix . '%')->pluck('sub_location_id')
            ->map(fn ($id) => (int) substr($id, strlen($prefix)))->max();

        return $prefix . str_pad((string) ((int) $last + 1), 4, '0', STR_PAD_LEFT);
    }
}
