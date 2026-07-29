<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\Usercpny;
use App\Models\Userdept;
use App\Models\Userbusinessunit;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\SysRole;
use App\Models\SysUserRole;
use App\Models\BusinessUnit;
use App\Models\MsDivision;
use App\Models\Userdivision;
use App\Models\UserDas;
use App\Models\SysScreen;

class UsersController extends Controller
{
    /**
     * adminsby may only assign these RBAC roles to a user.
     */
    private const SBY_ALLOWED_ROLE_IDS = ['RECACCALLDEPT', 'RECACCESS', 'RECDIRACCESS'];

    private function isSbyContext(): bool
    {
        return request()->routeIs('users-sby*');
    }

    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $company = MsCompany::select(['cpny_id', 'cpny_name', 'group_cpny_id'])->where('status', 'A')->get();
        $department = MsDepartment::select(['department_id', 'department_name'])->where('status', 'A')->get();
        // $businessUnits = BusinessUnit::select('business_unit_id')->where('status', 'A')->get();
        $businessUnits = BusinessUnit::select(['business_unit_id', 'business_unit_name'])
            ->where('status', 'A')
            ->distinct()
            ->get();

        $divisions = MsDivision::select(['division_id', 'division_name'])
            ->where('status', 'A')
            ->orderBy('division_name')
            ->get();

        $roles = SysRole::where('status', 'A')
            ->when($this->isSbyContext(), fn ($q) => $q->whereIn('role_id', self::SBY_ALLOWED_ROLE_IDS))
            ->orderBy('role_id')
            ->get();

        $screens = SysScreen::query()
            ->where('status', 'A')
            ->where('application_id', 'DASHBOARD')
            ->orderBy('screen_name')

            ->get([
                'screen_id',
                'screen_name'
            ]);

        return view(
            'pages.users.users',
            compact(
                'company',
                'department',
                'businessUnits',
                'divisions',
                'roles',
                'screens'
            )
        );
    }


    public function json()
    {
        $users = User::select([
            'id',
            'name',
            'username',
            'email',
            'cpny_id',
            'department_id',
            'business_unit_id',
            'division_id',
            'jabatan',
            'npk',
            'homepage',
            'status'
        ])
            ->where('status', 'A')
            ->when($this->isSbyContext(), fn ($q) => $q->where('group_cpny_id', 'SBY'))
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $users]);
    }


    public function inactiveJson()
    {
        $users = User::select([
            'id',
            'name',
            'username',
            'email',
            'cpny_id',
            'department_id',
            'business_unit_id',
            'division_id',
            'jabatan',
            'npk',
            'homepage',
            'status'
        ])
            ->where('status', '!=', 'A')
            ->when($this->isSbyContext(), fn ($q) => $q->where('group_cpny_id', 'SBY'))
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $users]);
    }


    public function duplicatesJson()
    {
        $emailKeys = User::query()
            ->select(DB::raw('LOWER(TRIM(email)) as key_val'))
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->groupBy(DB::raw('LOWER(TRIM(email))'))
            ->havingRaw('COUNT(*) > 1')
            ->pluck('key_val');

        $usernameKeys = User::query()
            ->select(DB::raw('LOWER(TRIM(username)) as key_val'))
            ->whereNotNull('username')
            ->where('username', '!=', '')
            ->groupBy(DB::raw('LOWER(TRIM(username))'))
            ->havingRaw('COUNT(*) > 1')
            ->pluck('key_val');

        $npkKeys = User::query()
            ->select('npk')
            ->whereNotNull('npk')
            ->where('npk', '!=', '')
            ->groupBy('npk')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('npk');

        if ($emailKeys->isEmpty() && $usernameKeys->isEmpty() && $npkKeys->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $users = User::query()
            ->select([
                'id',
                'name',
                'username',
                'email',
                'npk',
                'cpny_id',
                'department_id',
                'business_unit_id',
                'jabatan',
                'status',
                'created_at',
            ])
            ->where(function ($q) use ($emailKeys, $usernameKeys, $npkKeys) {
                if ($emailKeys->isNotEmpty()) {
                    $q->orWhereIn(DB::raw('LOWER(TRIM(email))'), $emailKeys);
                }
                if ($usernameKeys->isNotEmpty()) {
                    $q->orWhereIn(DB::raw('LOWER(TRIM(username))'), $usernameKeys);
                }
                if ($npkKeys->isNotEmpty()) {
                    $q->orWhereIn('npk', $npkKeys);
                }
            })
            ->when($this->isSbyContext(), fn ($q) => $q->where('group_cpny_id', 'SBY'))
            ->orderByRaw('LOWER(TRIM(email)) ASC NULLS LAST, LOWER(TRIM(username)) ASC NULLS LAST')
            ->get();

        $users = $users->map(function ($u) use ($emailKeys, $usernameKeys, $npkKeys) {
            $emailKey = $u->email ? mb_strtolower(trim($u->email)) : null;
            $usernameKey = $u->username ? mb_strtolower(trim($u->username)) : null;

            $reasons = [];
            if ($emailKey && $emailKeys->contains($emailKey)) {
                $reasons[] = 'Email';
            }
            if ($usernameKey && $usernameKeys->contains($usernameKey)) {
                $reasons[] = 'Username';
            }
            if ($u->npk && $npkKeys->contains($u->npk)) {
                $reasons[] = 'NPK';
            }

            $u->duplicate_reason = implode(', ', $reasons);
            $u->group_key = $emailKey ?: ($usernameKey ?: $u->npk);

            return $u;
        });

        return response()->json(['data' => $users->values()]);
    }


    public function store(Request $request)
    {
        $isSby = $this->isSbyContext();

        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'group_cpny_id' => 'nullable|string',
            'cpny_id' => 'required|array',
            'department_id' => 'required|array',
            'division_id' => 'nullable|array',
            'business_unit_id' => 'nullable|array',
            'homepage' => 'nullable|string',
            'jabatan' => 'required',
            'role' => 'required|array',
            'role_ids' => 'nullable|array',
            'origin_cpny_id' => 'nullable|string',
            'origin_department_id' => 'nullable|string',
        ]);
        DB::beginTransaction();
        try {

            $loginUser = Auth::user();

            $groupCpnyId = $isSby ? 'SBY' : $request->group_cpny_id;

            $companyIdsString = implode(',', $request->cpny_id);
            $deptIdsString    = implode(',', $request->department_id);
            $businessUnitIdsString = implode(',', $request->business_unit_id ?? []);
            $divisionIdsString = implode(',', $request->division_id ?? []);
            $roles = $isSby ? array_values(array_intersect($request->role, ['user', 'adminsby'])) : $request->role;
            $roleString = implode(',', $roles);

            $email    = $request->email;
            $username = $request->filled('username')
                ? trim($request->username)
                : explode('@', $email)[0];

            $password = Hash::make("pakuwon1234#");

            $user = User::create([
                'name'               => strtoupper($request->name),
                'email'              => $email,
                'username'           => $username,
                'cpny_id'            => $companyIdsString,
                'group_cpny_id'      => $groupCpnyId,
                'department_id'      => $deptIdsString,
                'division_id'        => $divisionIdsString,
                'business_unit_id'   => $businessUnitIdsString,
                'homepage' => $request->homepage,
                'jabatan'            => $request->jabatan,
                'password'           => $password,
                'user_role'          => $roleString, // user/admin (level UI)
                'notification_email' => $email,
                'npk'                => $request->npk,
                'origin_cpny_id'     => $request->origin_cpny_id,
                'origin_department_id' => $request->origin_department_id,
                'created_by'         => $loginUser->username,
                'status'             => 'A',
            ]);

            // USERCPNY
            foreach ($request->cpny_id as $cpny) {
                Usercpny::create([
                    'username'   => $username,
                    'cpny_id'    => $cpny,
                    'status'     => 'A',
                    'created_by' => $loginUser->username,
                ]);
            }

            // USERDEPT
            foreach ($request->department_id as $dept) {
                Userdept::create([
                    'username'      => $username,
                    'department_id' => $dept,
                    'status'        => 'A',
                    'created_by'    => $loginUser->username,
                ]);
            }

            // USERDIVISION
            foreach ($request->division_id ?? [] as $div) {
                Userdivision::create([
                    'username'   => $username,
                    'division_id'=> $div,
                    'status'     => 'A',
                    'created_by' => $loginUser->username,
                ]);
            }


            // USERBUSINESSUNIT (dengan cpny_id)
            $buIds = $request->business_unit_id ?? [];

            // ambil mapping cpny_id per business_unit_id (1 query)
            $buCpnyMap = BusinessUnit::query()
                ->whereIn('business_unit_id', $buIds)
                ->pluck('cpny_id', 'business_unit_id'); // [business_unit_id => cpny_id]

            // insert rows
            foreach ($buIds as $bu) {
                $cpnyIdForBu = $buCpnyMap[$bu] ?? null; // kalau tidak ketemu, null

                Userbusinessunit::create([
                    'username'         => $username,
                    'cpny_id'          => $cpnyIdForBu,
                    'business_unit_id' => $bu,
                    'status'           => 'A',
                    'created_by'       => $loginUser->username,
                ]);
            }


            // ✅ SYS_USER_ROLE – simpan roles RBAC
            $roleIds = $isSby
                ? array_values(array_intersect($request->role_ids ?? [], self::SBY_ALLOWED_ROLE_IDS))
                : ($request->role_ids ?? []);

            foreach ($roleIds as $roleId) {
                SysUserRole::create([
                    'username'   => $username,
                    'role_id'    => $roleId,
                    'status'     => 'A',
                    'created_by' => $loginUser->username,
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'user' => $user]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan user', 'message' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        if ($this->isSbyContext() && $user->group_cpny_id !== 'SBY') {
            abort(403);
        }

        // ambil semua role user ini dari sys_user_role
        $userRoles = SysUserRole::where('username', $user->username)
            ->where('status', 'A')
            ->pluck('role_id')
            ->toArray();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'npk' => $user->npk,
            'jabatan' => $user->jabatan,
            'role' => array_values(array_filter(explode(',', $user->user_role ?? ''), fn($v) => $v !== '')),
            'homepage' => $user->homepage,
            'cpny_id' => array_values(array_filter(explode(',', $user->cpny_id ?? ''), fn($v) => $v !== '')),
            'group_cpny_id' => $user->group_cpny_id,
            'origin_cpny_id' => $user->origin_cpny_id,
            'origin_department_id' => $user->origin_department_id,
            'department_id' => array_values(array_filter(explode(',', $user->department_id ?? ''), fn($v) => $v !== '')),
            'division_id' => array_values(array_filter(explode(',', (string) ($user->division_id ?? '')), fn($v) => $v !== '')),
            'business_unit_id' => array_values(array_filter(explode(',', $user->business_unit_id ?? ''), fn($v) => $v !== '')),
            'role_ids' => $userRoles,
        ]);
    }


    public function update(Request $request, $id)
    {
        $isSby = $this->isSbyContext();

        $request->validate([
            'name'          => 'required',
            'email'         => 'required',
            'group_cpny_id' => 'nullable|string',
            'cpny_id'       => 'required|array',
            'department_id' => 'required|array',
            'division_id'   => 'nullable|array',
            'business_unit_id' => 'nullable|array',
            'homepage'      => 'nullable|string',
            'jabatan'       => 'required',
            'role'          => 'required|array',
            'role_ids'      => 'nullable|array',
            'origin_cpny_id' => 'nullable|string',
            'origin_department_id' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {

            $loginUser = Auth::user();

            $user = User::findOrFail($id);

            if ($isSby && $user->group_cpny_id !== 'SBY') {
                abort(403);
            }

            $groupCpnyId = $isSby ? 'SBY' : $request->group_cpny_id;

            $oldUsername = $user->username;
            $newUsername = $request->filled('username')
                ? trim($request->username)
                : $oldUsername;

            $companyIdsString = implode(',', $request->cpny_id);
            $deptIdsString    = implode(',', $request->department_id);
            $divisionIdsString = implode(',', $request->division_id ?? []);
            $businessUnitIdsString = implode(',', $request->business_unit_id ?? []);
            $roles = $isSby ? array_values(array_intersect($request->role, ['user', 'adminsby'])) : $request->role;
            $roleString = implode(',', $roles);

            $updateData = [
                'name' => strtoupper($request->name),
                'email' => $request->email,
                'cpny_id' => $companyIdsString,
                'group_cpny_id' => $groupCpnyId,
                'department_id' => $deptIdsString,
                'division_id' => $divisionIdsString,
                'business_unit_id' => $businessUnitIdsString,
                'homepage' => $request->homepage,
                'user_role' => $roleString,
                'npk' => $request->npk,
                'jabatan' => $request->jabatan,
                'origin_cpny_id' => $request->origin_cpny_id,
                'origin_department_id' => $request->origin_department_id,
                'updated_by' => $loginUser->username,
            ];

            if (config('app.name') === 'Pakuwon System') {
                $updateData['notification_email'] = $request->email;
            }

            if ($newUsername !== $oldUsername) {
                $updateData['username'] = $newUsername;
                // Cascade username ke tabel terkait sebelum delete/insert
                SysUserRole::where('username', $oldUsername)->update(['username' => $newUsername]);
            }

            $user->update($updateData);

            // DELETE OLD ACCESS (company / dept)
            Usercpny::where('username', $oldUsername)->delete();
            Userdept::where('username', $oldUsername)->delete();
            Userdivision::where('username', $oldUsername)->delete();
            Userbusinessunit::where('username', $oldUsername)->delete();

            foreach ($request->cpny_id as $cpny) {
                Usercpny::create([
                    'username'   => $newUsername,
                    'cpny_id'    => $cpny,
                    'status'     => 'A',
                    'created_by' => $loginUser->username,
                ]);
            }

            foreach ($request->department_id as $dept) {
                Userdept::create([
                    'username'      => $newUsername,
                    'department_id' => $dept,
                    'status'        => 'A',
                    'created_by'    => $loginUser->username,
                ]);
            }

            foreach ($request->division_id ?? [] as $div) {
                Userdivision::create([
                    'username'   => $newUsername,
                    'division_id'=> $div,
                    'status'     => 'A',
                    'created_by' => $loginUser->username,
                ]);
            }

            $buIds = $request->business_unit_id ?? [];
            $buCpnyMap = BusinessUnit::query()
                ->whereIn('business_unit_id', $buIds)
                ->pluck('cpny_id', 'business_unit_id');

            foreach ($buIds as $bu) {
                $cpnyIdForBu = $buCpnyMap[$bu] ?? null;

                Userbusinessunit::create([
                    'username'         => $newUsername,
                    'cpny_id'          => $cpnyIdForBu,
                    'business_unit_id' => $bu,
                    'status'           => 'A',
                    'created_by'       => $loginUser->username,
                ]);
            }

            // ✅ RESET + INSERT ULANG SYS_USER_ROLE
            SysUserRole::where('username', $newUsername)->delete();

            $roleIds = $isSby
                ? array_values(array_intersect($request->role_ids ?? [], self::SBY_ALLOWED_ROLE_IDS))
                : ($request->role_ids ?? []);

            foreach ($roleIds as $roleId) {
                SysUserRole::create([
                    'username'   => $newUsername,
                    'role_id'    => $roleId,
                    'status'     => 'A',
                    'created_by' => $loginUser->username,
                ]);
            }

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal update user', 'message' => $e->getMessage()], 500);
        }
    }


    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => request('status')]);

        return response()->json(['message' => 'Status updated']);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $authUser = Auth::user();
        if (!$authUser) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (!Hash::check($request->current_password, $authUser->password)) {
            return response()->json(['message' => 'Current password does not match'], 422);
        }

        DB::beginTransaction();
        try {
            $newHash = Hash::make($request->password);

            // 1) update password di sistem utama (pgsql2 ms_user)
            $authUser->password = $newHash;
            $authUser->updated_by = $authUser->username ?? $authUser->name ?? 'system';
            $authUser->updated_at = now();
            $authUser->save();

            // 2) update password di DAS (mysql2 users)
            //    prioritas match by email, fallback by username
            $das = UserDas::query()
                ->where('username', $authUser->username)
                ->first();

            if (!$das && !empty($authUser->username)) {
                $das = UserDas::query()
                    ->where('username', $authUser->username)
                    ->first();
            }

            // kalau user DAS wajib ada, gunakan throw supaya rollback
            if (!$das) {
                throw new \Exception('UserDas tidak ditemukan (email/username tidak match).');
            }

            $das->password = $newHash;
            $das->save();

            DB::commit();
            return response()->json(['message' => 'Password updated successfully']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal update password (sinkronisasi ke DAS gagal).',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePassword_xxx(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return response()->json(['message' => 'Current password does not match'], 422);
        }

        $user = auth()->user();
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully']);
    }

    /**
     * 🔑 Login as user yang dipilih
     */
    public function impersonate($id)
    {
        $currentUser = Auth::user();

        if (!$currentUser || !in_array('admin', array_map('trim', explode(',', $currentUser->user_role ?? '')), true)) {
            abort(403, 'Unauthorized');
        }

        if (session()->has('impersonate_original_username')) {
            abort(403, 'Already impersonating a user. Return to your account first.');
        }

        $targetUser = User::findOrFail($id);

        session(['impersonate_original_username' => $currentUser->username]);

        Auth::login($targetUser);

        Log::info('User impersonation started', [
            'admin_id'  => $currentUser->id,
            'admin'     => $currentUser->username,
            'target_id' => $targetUser->id,
            'target'    => $targetUser->username,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Now logged in as ' . $targetUser->username,
            'redirect' => route('dashboard'),
        ]);
    }

    /**
     * 🔙 Kembali ke akun admin setelah Login As
     */
    public function stopImpersonate()
    {
        $originalUsername = session('impersonate_original_username');

        if (!$originalUsername) {
            abort(403, 'Not currently impersonating a user.');
        }

        $impersonatedUser = Auth::user();
        $originalUser = User::where('username', $originalUsername)->firstOrFail();

        session()->forget('impersonate_original_username');

        Auth::login($originalUser);

        Log::info('User impersonation ended', [
            'admin_id'  => $originalUser->id,
            'admin'     => $originalUser->username,
            'target_id' => $impersonatedUser?->id,
            'target'    => $impersonatedUser?->username,
        ]);

        return redirect()->route('dashboard')->with('success', 'Returned to your account.');
    }

    /**
     * 🔁 Reset password user ke default: pakuwon1234#
     */
    public function resetPassword($id)
    {
        $currentUser = Auth::user();

        // Hanya admin yang boleh reset password
        if (!$currentUser || !in_array('admin', array_map('trim', explode(',', $currentUser->user_role ?? '')), true)) {
            abort(403, 'Unauthorized');
        }

        $user = User::findOrFail($id);
        $user->password   = Hash::make('pakuwon1234#');
        $user->updated_by = $currentUser->username;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil di-reset ke default.',
        ]);
    }

    /**
     * 🗑️ Hard delete: permanently remove a user and its related access rows.
     */
    public function destroy($id)
    {
        $currentUser = Auth::user();

        if (!$currentUser || !in_array('admin', array_map('trim', explode(',', $currentUser->user_role ?? '')), true)) {
            abort(403, 'Unauthorized');
        }

        $user = User::findOrFail($id);

        if ($user->id === $currentUser->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        DB::beginTransaction();
        try {
            $username = $user->username;

            Usercpny::where('username', $username)->delete();
            Userdept::where('username', $username)->delete();
            Userdivision::where('username', $username)->delete();
            Userbusinessunit::where('username', $username)->delete();
            SysUserRole::where('username', $username)->delete();

            $user->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'User permanently deleted.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus user.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
