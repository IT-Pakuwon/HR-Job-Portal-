<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\Tenancy\TsUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TsUserController extends Controller
{
    /** ms_user.origin_cpny_id => tenancy site (mssite.siteid) and company (mscompany.id). */
    private const ORIGIN_MAP = [
        'AW'  => ['site' => 'GC',  'company_id' => 2], // PT. ARTISAN WAHYU
        'EP'  => ['site' => 'KK',  'company_id' => 1], // PT. ELITE PRIMA HUTAMA
        'GPS' => ['site' => 'PMB', 'company_id' => 4], // PT. GRAMA PRAMESI SIDDHI
        'PSA' => ['site' => 'PBM', 'company_id' => 3], // PT. PAKUWON SENTOSA ABADI
    ];

    public function json()
    {
        $users = TsUser::select(['id', 'name', 'companyname', 'email', 'username', 'usertype', 'siteid', 'departmentid', 'status'])
            ->orderByDesc('id')
            ->get();

        $sitesByUser = DB::connection('mysql5')->table('msusersite')
            ->where('status', 'A')
            ->orderBy('id')
            ->get(['username', 'siteid'])
            ->groupBy('username')
            ->map(fn ($rows) => $rows->pluck('siteid')->values());

        $users->each(function ($u) use ($sitesByUser) {
            $list = $u->usertype === 'SUPERADMIN' ? ($sitesByUser[$u->username] ?? collect()) : collect();
            if ($u->siteid) $list = collect([$u->siteid])->merge($list->reject(fn ($s) => $s === $u->siteid));
            $u->setAttribute('sites', $list->values());
        });

        return response()->json(['data' => $users]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:100',
            'companyname'  => 'nullable|string|max:100',
            'phone'        => 'nullable|string|max:50',
            'email'        => 'required|email|max:50|unique:mysql5.user,email',
            'username'     => 'required|string|max:50|unique:mysql5.user,username',
            'password'     => 'required|string|min:6',
            'usertype'     => 'required|string|max:50',
            'siteid'       => 'nullable|string|max:5|exists:mysql5.mssite,siteid',
            'siteids'      => 'nullable|array',
            'siteids.*'    => 'string|max:5|exists:mysql5.mssite,siteid',
            'departmentid' => 'nullable|string|max:50',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();
            $siteIds = $this->resolveSiteIds($request);

            $user = TsUser::create([
                'name'                => $request->name,
                'companyname'         => $request->companyname,
                'phone'               => $request->phone,
                'email'               => $request->email,
                'username'            => $request->username,
                'password'            => Hash::make($request->password),
                'is_admin'            => false,
                'usertype'            => $request->usertype,
                'siteid'              => $siteIds[0] ?? null,
                'departmentid'        => $request->departmentid,
                'status'              => 'A',
                'created_user'        => $loginUser->username ?? 'system',
                'created_datetime'    => now(),
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => now(),
            ]);

            $this->syncUserSites($user->username, $siteIds, $loginUser->username ?? 'system', $request->usertype === 'SUPERADMIN');

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true, 'user' => $user]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan user',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $user = TsUser::findOrFail($id);

        return response()->json([
            'id'           => $user->id,
            'name'         => $user->name,
            'companyname'  => $user->companyname,
            'phone'        => $user->phone,
            'email'        => $user->email,
            'username'     => $user->username,
            'usertype'     => $user->usertype,
            'siteid'       => $user->siteid,
            'siteids'      => $user->usertype === 'SUPERADMIN'
                ? collect([$user->siteid])->filter()->merge(DB::connection('mysql5')->table('msusersite')->where('username', $user->username)->where('status', 'A')->orderBy('id')->pluck('siteid'))->unique()->values()
                : [],
            'departmentid' => $user->departmentid,
            'status'       => $user->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = TsUser::findOrFail($id);

        $request->validate([
            'name'         => 'required|string|max:100',
            'companyname'  => 'nullable|string|max:100',
            'phone'        => 'nullable|string|max:50',
            'email'        => ['required', 'email', 'max:50', Rule::unique('mysql5.user', 'email')->ignore($user->id)],
            'username'     => ['required', 'string', 'max:50', Rule::unique('mysql5.user', 'username')->ignore($user->id)],
            'password'     => 'nullable|string|min:6',
            'usertype'     => 'required|string|max:50',
            'siteid'       => 'nullable|string|max:5|exists:mysql5.mssite,siteid',
            'siteids'      => 'nullable|array',
            'siteids.*'    => 'string|max:5|exists:mysql5.mssite,siteid',
            'departmentid' => 'nullable|string|max:50',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();
            $siteIds = $this->resolveSiteIds($request);
            $oldUsername = $user->username;

            $data = [
                'name'                => $request->name,
                'companyname'         => $request->companyname,
                'phone'               => $request->phone,
                'email'               => $request->email,
                'username'            => $request->username,
                'usertype'            => $request->usertype,
                'siteid'              => $siteIds[0] ?? null,
                'departmentid'        => $request->departmentid,
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => now(),
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            if ($oldUsername !== $request->username) {
                DB::connection('mysql5')->table('msusersite')->where('username', $oldUsername)->update(['username' => $request->username]);
            }
            $this->syncUserSites($request->username, $siteIds, $loginUser->username ?? 'system', $request->usertype === 'SUPERADMIN');

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal update user',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $user = TsUser::findOrFail($id);
        $newStatus = request('status'); // A / X

        $user->update([
            'status'              => $newStatus,
            'lastupdate_user'     => Auth::user()->username ?? 'system',
            'lastupdate_datetime' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }

    /**
     * SUPERADMIN may hold several sites (siteids[]); every other type keeps exactly one (siteid).
     * The first id becomes the user's primary/default site (user.siteid).
     */
    private function resolveSiteIds(Request $request): array
    {
        if ($request->usertype === 'SUPERADMIN') {
            $ids = array_values(array_unique(array_filter((array) $request->input('siteids', []))));
            if (! $ids && $request->filled('siteid')) $ids = [$request->siteid];
            return $ids;
        }

        return $request->filled('siteid') ? [$request->siteid] : [];
    }

    /**
     * Keeps legacy msusersite (username x site) in step with the user's sites.
     * Non-superadmins only get their rows rewritten when their single site actually changed,
     * so legacy multi-site rows of other types are never collapsed by an unrelated edit.
     */
    private function syncUserSites(string $username, array $siteIds, string $by, bool $multi = true): void
    {
        $db = DB::connection('mysql5');
        $existing = $db->table('msusersite')->where('username', $username)->where('status', 'A')->pluck('siteid')->all();

        if (! $siteIds) return;

        if (! $multi) {
            // single-site types: leave untouched unless the site really changed
            if (in_array($siteIds[0], $existing, true)) return;
            $db->table('msusersite')->where('username', $username)->delete();
            $existing = [];
        } else {
            $toRemove = array_diff($existing, $siteIds);
            if ($toRemove) {
                $db->table('msusersite')->where('username', $username)->whereIn('siteid', $toRemove)->delete();
            }
        }

        $names = $db->table('mssite')->whereIn('siteid', $siteIds)->pluck('sitename', 'siteid');
        foreach (array_diff($siteIds, $existing) as $sid) {
            $db->table('msusersite')->insert([
                'username'            => $username,
                'siteid'              => $sid,
                'sitename'            => $names[$sid] ?? null,
                'status'              => 'A',
                'created_user'        => $by,
                'created_datetime'    => now(),
                'lastupdate_user'     => $by,
                'lastupdate_datetime' => now(),
            ]);
        }
    }

    /** Only holders of the ACCESSTS role can open Tenancy Support, so only they may be copied. */
    private function accessTsUsernames()
    {
        return DB::connection('pgsql2')->table('sys_user_role')
            ->where('role_id', 'ACCESSTS')->where('status', 'A')
            ->select('username');
    }

    /**
     * Active ms_user (pgsql2, the people who log in to this app) that have no tenancy user yet.
     * Matched by username, case-insensitive. Optional ?q= searches name/username/email.
     */
    public function msUsers(Request $request)
    {
        $taken = TsUser::pluck('username')->map(fn ($u) => mb_strtolower(trim($u)))->flip();
        $takenEmails = TsUser::pluck('email')->map(fn ($e) => mb_strtolower(trim($e)))->flip();

        $q = trim((string) $request->query('q', ''));
        $rows = DB::connection('pgsql2')->table('ms_user')
            ->where('status', 'A')
            ->whereNotNull('username')->where('username', '<>', '')
            ->when($request->query('usertype') !== 'USER', fn ($query) => $query->whereIn('username', $this->accessTsUsernames()))
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . mb_strtolower($q) . '%';
                $query->where(fn ($w) => $w->whereRaw('lower(name) like ?', [$like])
                    ->orWhereRaw('lower(username) like ?', [$like])
                    ->orWhereRaw('lower(email) like ?', [$like]));
            })
            ->orderBy('name')
            ->get(['username', 'name', 'email', 'phonenumber']);

        $data = $rows
            ->reject(fn ($r) => $taken->has(mb_strtolower(trim($r->username))))
            ->map(function ($r) use ($takenEmails) {
                $email = trim((string) $r->email);
                $problem = null;
                if ($email === '') $problem = 'No email';
                elseif (mb_strlen($email) > 50 || mb_strlen($r->username) > 50) $problem = 'Email/username too long';
                elseif ($takenEmails->has(mb_strtolower($email))) $problem = 'Email already used by another tenancy user';
                return [
                    'username' => $r->username,
                    'name'     => $r->name,
                    'email'    => $email,
                    'problem'  => $problem,
                ];
            })
            ->values();

        return response()->json(['data' => $data]);
    }

    /**
     * One-way copy ms_user -> tenancy user, by username. Never deletes, never writes back.
     * Existing tenancy rows are skipped (admin owns them). New rows get:
     *  - chosen usertype, random unusable password
     *  - site + company from ms_user.origin_cpny_id (ORIGIN_MAP); SUPERADMIN gets every site
     *  - NO department: always left for an admin, and reported back in `needs`.
     */
    public function copyFromMsUser(Request $request)
    {
        $request->validate([
            'usernames'   => 'required|array|min:1|max:500',
            'usernames.*' => 'string|max:50',
            'usertype'    => 'required|in:SUPERADMIN,ADMIN,CHECKER,USER', // TENANT is managed in tenancy, not copied. Non-USER types need ACCESSTS.
        ]);

        $by = Auth::user()->username ?? 'system';
        $created = [];
        $skipped = [];
        $needs   = [];

        $allSites     = DB::connection('mysql5')->table('mssite')->where('status', 'A')->orderBy('siteid')->pluck('siteid')->all();
        $companyNames = DB::connection('mysql5')->table('mscompany')->pluck('companyname', 'id')->all();

        $source = DB::connection('pgsql2')->table('ms_user')
            ->whereIn('username', $request->usernames)
            ->when($request->usertype !== 'USER', fn ($query) => $query->whereIn('username', $this->accessTsUsernames()))
            ->where('status', 'A')
            ->get(['username', 'name', 'email', 'phonenumber', 'origin_cpny_id'])
            ->keyBy('username');

        foreach (array_unique($request->usernames) as $username) {
            $src = $source[$username] ?? null;
            if (! $src) { $skipped[] = ['username' => $username, 'reason' => ($request->usertype === 'USER' ? 'Not an active ms_user' : 'Not an active ms_user with role ACCESSTS')]; continue; }

            $email = trim((string) $src->email);
            if ($email === '' || mb_strlen($email) > 50) { $skipped[] = ['username' => $username, 'reason' => 'Missing or too-long email']; continue; }

            if (TsUser::whereRaw('lower(username) = ?', [mb_strtolower($username)])->exists()) {
                $skipped[] = ['username' => $username, 'reason' => 'Already in tenancy']; continue;
            }
            if (TsUser::whereRaw('lower(email) = ?', [mb_strtolower($email)])->exists()) {
                $skipped[] = ['username' => $username, 'reason' => 'Email already used by another tenancy user']; continue;
            }

            // Site / company follow the user's origin company; SUPERADMIN gets every site.
            $isSuper = $request->usertype === 'SUPERADMIN';
            $origin  = strtoupper(trim((string) $src->origin_cpny_id));
            $map     = self::ORIGIN_MAP[$origin] ?? null;
            $siteIds = $isSuper ? $allSites : ($map && in_array($map['site'], $allSites, true) ? [$map['site']] : []);
            $primary = $map['site'] ?? ($siteIds[0] ?? null);
            if ($isSuper && $primary && in_array($primary, $siteIds, true)) {
                $siteIds = array_values(array_unique(array_merge([$primary], $siteIds))); // primary site first
            }
            $companyName = $map ? ($companyNames[$map['company_id']] ?? null) : null;

            try {
                DB::connection('mysql5')->transaction(function () use ($src, $email, $username, $request, $by, $siteIds, $companyName, $isSuper) {
                    TsUser::create([
                        'name'                => mb_substr((string) $src->name, 0, 100) ?: $username,
                        'companyname'         => $companyName,
                        'phone'               => $src->phonenumber ? mb_substr((string) $src->phonenumber, 0, 50) : null,
                        'email'               => $email,
                        'username'            => $username,
                        'password'            => Hash::make(Str::random(32)), // unusable; admin uses Reset Password if needed
                        'is_admin'            => false,
                        'usertype'            => $request->usertype,
                        'siteid'              => $siteIds[0] ?? null,
                        'departmentid'        => null, // always filled by an admin afterwards
                        'status'              => 'A',
                        'created_user'        => $by,
                        'created_datetime'    => now(),
                        'lastupdate_user'     => $by,
                        'lastupdate_datetime' => now(),
                    ]);
                    $this->syncUserSites($username, $siteIds, $by, $isSuper);
                });
                $created[] = $username;
                // Reminders for the admin: department is always empty; site/company only when origin company is unmapped.
                $todo = ['Department'];
                if (! $siteIds) $todo[] = 'Site';
                if (! $companyName) $todo[] = 'Company name';
                $needs[] = ['username' => $username, 'todo' => $todo, 'origin' => $origin ?: null];
            } catch (\Throwable $e) {
                $skipped[] = ['username' => $username, 'reason' => 'Error: ' . $e->getMessage()];
            }
        }

        return response()->json(['success' => true, 'created' => $created, 'skipped' => $skipped, 'needs' => $needs]);
    }

    public function resetPassword($id)
    {
        $user = TsUser::findOrFail($id);

        $user->update([
            'password'            => Hash::make('pakuwon1234#'),
            'lastupdate_user'     => Auth::user()->username ?? 'system',
            'lastupdate_datetime' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Password reset successfully']);
    }
}
