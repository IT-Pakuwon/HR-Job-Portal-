<?php

namespace App\Http\Controllers;

use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\User;
use App\Models\ViewUsersTalenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * "Update Data User": side-by-side view of Talenta (view_users_talenta) and ms_user,
 * used to correct ms_user's NPK, Name, origin company and origin department so they
 * match what Talenta says. Talenta has no company/department codes of its own — its
 * organization_name is "<COMPANY> - <Department>", so that string is split for display
 * and used only to *suggest* ms_company / ms_department values.
 */
class UserDataSyncController extends Controller
{
    public function index()
    {
        return view('pages.userdatasync.index');
    }

    private function norm($s): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', (string) $s)));
    }

    private function talentaRows()
    {
        return ViewUsersTalenta::query()
            ->whereNotNull('employee_id')
            ->where('employee_id', '!=', '')
            ->orderBy('employee_id')
            ->get(['employee_id', 'first_name', 'last_name', 'organization_name', 'job_position', 'status_talenta', 'birth_date'])
            ->map(function ($t) {
                $org = trim((string) $t->organization_name);
                $parts = explode(' - ', $org, 2);

                return [
                    'npk' => trim($t->employee_id),
                    'name' => trim($t->first_name.' '.$t->last_name),
                    'organization' => $org,
                    'company' => count($parts) === 2 ? trim($parts[0]) : $org,
                    'department' => count($parts) === 2 ? trim($parts[1]) : '',
                    'job_position' => $t->job_position,
                    'status' => $t->status_talenta,
                    'bod' => $t->birth_date ? substr((string) $t->birth_date, 0, 10) : '',
                ];
            })
            ->values();
    }

    private function activeUsers()
    {
        return User::query()
            ->where('status', 'A')
            ->orderBy('name')
            ->toBase()
            ->get(['id', 'username', 'name', 'npk', 'origin_cpny_id', 'origin_department_id', 'status']);
    }

    private function isResigned($t): bool
    {
        return $t && strtolower(trim((string) $t['status'])) === 'resigned';
    }

    public function talentaJson()
    {
        return response()->json(['data' => $this->talentaRows()]);
    }

    /**
     * Talenta people sharing the same name + birth date under different NPKs. A group with
     * exactly one non-resigned row and >=1 Resigned row is a "rehire": the app account on
     * the Active NPK is kept (else the first old account, which gets re-pointed to the
     * Active NPK) and every other account in the group is switched off.
     */
    private function talentaGroups($talenta, $users)
    {
        $usersByNpk = $users->filter(fn ($u) => $u->npk)->groupBy(fn ($u) => trim($u->npk));

        return $talenta
            ->filter(fn ($t) => $t['bod'] !== '' && $this->norm($t['name']) !== '')
            ->groupBy(fn ($t) => $this->norm($t['name']).'|'.$t['bod'])
            ->filter(fn ($g) => $g->pluck('npk')->unique()->count() > 1)
            ->map(function ($g) use ($usersByNpk) {
                $active = $g->filter(fn ($t) => !$this->isResigned($t))->values();
                $resigned = $g->filter(fn ($t) => $this->isResigned($t))->values();
                $accounts = $g->flatMap(fn ($t) => $usersByNpk->get($t['npk'], collect())
                    ->map(fn ($u) => [
                        'id' => $u->id, 'username' => $u->username, 'npk' => $u->npk, 'name' => $u->name,
                        'is_active_npk' => !$this->isResigned($t),
                    ]))->values();

                $rehire = $active->count() === 1 && $resigned->count() >= 1;
                $keep = $accounts->firstWhere('is_active_npk', true) ?? $accounts->first();

                return [
                    'name' => $g->first()['name'],
                    'bod' => $g->first()['bod'],
                    'type' => $rehire ? 'rehire' : 'duplicate',
                    'rows' => $g->values(),
                    'active' => $rehire ? $active->first() : null,
                    'accounts' => $accounts,
                    'keep_id' => $rehire && $keep ? $keep['id'] : null,
                ];
            })->values();
    }

    /**
     * Classify every active ms_user against Talenta in one pass. The first matching
     * issue wins: rehire > resigned > no_match > link (matched by name only) >
     * mismatch (matched by NPK, fields differ) > ok. `dup` is an independent flag.
     * `suggested` is what "Sync" would write; `diff` marks which fields would change.
     */
    private function buildOverview(): array
    {
        $talenta = $this->talentaRows();
        $users = $this->activeUsers();
        $byNpk = $talenta->keyBy('npk');
        $byName = $talenta->groupBy(fn ($t) => $this->norm($t['name']));
        $groups = $this->talentaGroups($talenta, $users);

        $coMap = MsCompany::query()->get(['cpny_id'])->unique('cpny_id')
            ->mapWithKeys(fn ($c) => [$this->norm($c->cpny_id) => $c->cpny_id]);
        $dpMap = [];
        foreach (MsDepartment::query()->get(['department_id', 'department_name'])->unique('department_id') as $d) {
            $dpMap[$this->norm($d->department_name)] ??= $d->department_id;
            $dpMap[$this->norm($d->department_id)] = $d->department_id;
        }

        $rehire = [];
        foreach ($groups->where('type', 'rehire') as $g) {
            foreach ($g['accounts'] as $a) {
                $rehire[$a['id']] = ['active_npk' => $g['active']['npk'], 'keep' => $a['id'] === $g['keep_id']];
            }
        }

        $npkCount = $users->filter(fn ($u) => $u->npk)->countBy(fn ($u) => trim($u->npk));
        $nameCount = $users->countBy(fn ($u) => $this->norm($u->name));

        $rows = $users->map(function ($u) use ($byNpk, $byName, $coMap, $dpMap, $rehire, $npkCount, $nameCount) {
            $npk = trim((string) $u->npk);
            $t = $npk !== '' ? $byNpk->get($npk) : null;
            $via = $t ? 'npk' : null;
            if (!$t) {
                $c = $byName->get($this->norm($u->name));
                if ($c && $c->count() === 1) {
                    $t = $c->first();
                    $via = 'name';
                }
            }

            $suggested = $diff = null;
            if ($t) {
                $co = $coMap[$this->norm(explode(' ', $t['company'])[0])] ?? null;
                $dp = $dpMap[$this->norm($t['department'])] ?? null;
                $suggested = [
                    'npk' => $t['npk'],
                    'name' => strtoupper($t['name']),
                    'origin_cpny_id' => $co ?? $u->origin_cpny_id,
                    'origin_department_id' => $dp ?? $u->origin_department_id,
                ];
                $diff = [
                    'npk' => $npk !== $t['npk'],
                    'name' => $this->norm($u->name) !== $this->norm($t['name']),
                    'company' => $co !== null && $co !== $u->origin_cpny_id,
                    'dept' => $dp !== null && $dp !== $u->origin_department_id,
                ];
            }

            $issue = isset($rehire[$u->id]) ? 'rehire'
                : ($via === 'npk' && $this->isResigned($t) ? 'resigned'
                : (!$t ? 'no_match'
                : ($via === 'name' ? 'link'
                : (in_array(true, $diff, true) ? 'mismatch' : 'ok'))));

            $dup = null;
            if ($npk !== '' && $npkCount[$npk] > 1) {
                $dup = 'Same NPK';
            } elseif (($nameCount[$this->norm($u->name)] ?? 0) > 1) {
                $dup = 'Same name';
            }

            return [
                'id' => $u->id, 'username' => $u->username, 'name' => $u->name, 'npk' => $u->npk,
                'cpny' => $u->origin_cpny_id, 'dept' => $u->origin_department_id,
                'issue' => $issue, 'dup' => $dup, 'via' => $via,
                'talenta' => $t ? array_intersect_key($t, array_flip(['npk', 'name', 'company', 'department', 'status', 'bod'])) : null,
                'suggested' => $suggested, 'diff' => $diff,
                'rehire' => $rehire[$u->id] ?? null,
            ];
        })->values();

        return [
            'rows' => $rows,
            'talenta_dups' => $groups->where('type', 'duplicate')->values(),
        ];
    }

    public function overview()
    {
        return response()->json($this->buildOverview());
    }

    /**
     * Bulk "Sync": apply Talenta's NPK/Name/Company/Department to accounts already matched
     * by NPK whose fields differ. Name-only matches are never bulk-applied (could be a
     * different person) — those are confirmed one at a time.
     */
    public function syncBulk(Request $request)
    {
        $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer']);

        $me = Auth::user()->username ?? 'system';
        $done = 0;
        foreach (collect($this->buildOverview()['rows'])->whereIn('id', $request->ids)->where('issue', 'mismatch') as $r) {
            User::where('id', $r['id'])->update($r['suggested'] + ['updated_by' => $me]);
            ++$done;
        }

        return response()->json(['message' => "{$done} account(s) synced with Talenta.", 'count' => $done]);
    }

    /** Bulk switch off active accounts Talenta flags Resigned; every id is re-checked server-side. */
    public function deactivateResigned(Request $request)
    {
        $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer']);

        $byNpk = $this->talentaRows()->keyBy('npk');
        $ids = $this->activeUsers()
            ->whereIn('id', $request->ids)
            ->filter(fn ($u) => $u->npk && $this->isResigned($byNpk->get(trim($u->npk))))
            ->pluck('id');

        $count = User::whereIn('id', $ids)->update([
            'status' => 'X',
            'updated_by' => Auth::user()->username ?? 'system',
        ]);

        return response()->json(['message' => "{$count} account(s) set to status X.", 'count' => $count, 'ids' => $ids]);
    }

    /** Switch off a single account (used from the duplicates tab). */
    public function deactivate($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'X', 'updated_by' => Auth::user()->username ?? 'system']);

        return response()->json(['message' => "{$user->username} set to status X."]);
    }

    /** Resolve one rehire pair: keep + update one account to the Active Talenta row, switch the rest off. */
    public function resolveRehire(Request $request)
    {
        $data = $request->validate(['active_npk' => 'required|string']);

        $pair = $this->talentaGroups($this->talentaRows(), $this->activeUsers())
            ->first(fn ($g) => $g['type'] === 'rehire' && $g['active']['npk'] === trim($data['active_npk']));

        if (!$pair || !$pair['keep_id']) {
            return response()->json(['message' => 'No rehire pair with an app account found for that NPK.'], 422);
        }

        $t = $pair['active'];
        $co = MsCompany::query()->get(['cpny_id'])
            ->first(fn ($c) => $this->norm($c->cpny_id) === $this->norm(explode(' ', $t['company'])[0]));
        $dp = MsDepartment::query()->get(['department_id', 'department_name'])
            ->first(fn ($d) => $this->norm($d->department_id) === $this->norm($t['department'])
                || $this->norm($d->department_name) === $this->norm($t['department']));

        $me = Auth::user()->username ?? 'system';
        $offIds = $pair['accounts']->pluck('id')->reject(fn ($i) => $i === $pair['keep_id'])->values();

        DB::connection((new User())->getConnectionName())->transaction(function () use ($pair, $t, $co, $dp, $offIds, $me) {
            // switch the others off first so the NPK is never held by two rows
            User::whereIn('id', $offIds)->update(['status' => 'X', 'updated_by' => $me]);

            $keep = User::findOrFail($pair['keep_id']);
            $keep->update([
                'npk' => $t['npk'],
                'name' => strtoupper($t['name']),
                'origin_cpny_id' => $co->cpny_id ?? $keep->origin_cpny_id,
                'origin_department_id' => $dp->department_id ?? $keep->origin_department_id,
                'updated_by' => $me,
            ]);
        });

        return response()->json([
            'message' => 'Account updated to NPK '.$t['npk'].($offIds->isNotEmpty() ? '; '.$offIds->count().' other account(s) set to X.' : '.'),
        ]);
    }

    public function options()
    {
        return response()->json([
            'companies' => MsCompany::query()->orderBy('cpny_id')->toBase()->get(['cpny_id', 'cpny_name'])
                ->unique('cpny_id')->values(),
            'departments' => MsDepartment::query()->orderBy('department_id')->toBase()->get(['department_id', 'department_name'])
                ->unique('department_id')->values(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'npk' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'origin_cpny_id' => 'nullable|string|max:50',
            'origin_department_id' => 'nullable|string|max:100',
        ]);

        $user = User::findOrFail($id);
        $npk = trim($data['npk']);

        $taken = User::where('npk', $npk)->where('id', '!=', $user->id)->value('username');
        if ($taken) {
            return response()->json([
                'message' => "NPK {$npk} is already used by user '{$taken}'.",
            ], 422);
        }

        $user->update([
            'npk' => $npk,
            'name' => strtoupper(trim($data['name'])),
            'origin_cpny_id' => $data['origin_cpny_id'] ?? null,
            'origin_department_id' => $data['origin_department_id'] ?? null,
            'updated_by' => Auth::user()->username,
        ]);

        return response()->json([
            'message' => 'User updated.',
            'user' => $user->only(['id', 'username', 'name', 'npk', 'origin_cpny_id', 'origin_department_id', 'status']),
        ]);
    }
}
