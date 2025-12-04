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
use Illuminate\Support\Facades\Hash;
use App\Models\SysRole;
use App\Models\SysUserRole;


class UsersController extends Controller
{
   
    public function index()
    {
        $company = MsCompany::select('cpny_id')->where('status', 'A')->get();
        $department = MsDepartment::select('department_id')->where('status', 'A')->get();
        $roles = SysRole::where('status', 'A')->orderBy('role_id')->get();

        return view('pages.users.users', compact('company', 'department', 'roles'));
    }


    public function json()
    {
        $users = User::select(['id','name','username','email','cpny_id','department_id','status'])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $users]);
    }
   

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required',
            'email'         => 'required',
            'cpny_id'       => 'required|array',
            'department_id' => 'required|array',
            'role'          => 'required',          // user/admin (yang lama)
            'role_ids'      => 'nullable|array',    // ⬅️ daftar role RBAC (sys_user_role)
        ]);

        DB::beginTransaction();
        try {

            $loginUser = Auth::user();

            $companyIdsString = implode(',', $request->cpny_id);
            $deptIdsString    = implode(',', $request->department_id);

            $email    = $request->email;
            $username = explode('@', $email)[0];

            $password = Hash::make("pakuwon1234#");

            $user = User::create([
                'name'               => strtoupper($request->name),
                'email'              => $email,
                'username'           => $username,
                'cpny_id'            => $companyIdsString,
                'department_id'      => $deptIdsString,
                'password'           => $password,
                'user_role'          => $request->role, // user/admin (level UI)
                'notification_email' => $email,
                'npk'                => $request->npk,
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

            // ✅ SYS_USER_ROLE – simpan roles RBAC
            if ($request->filled('role_ids')) {
                foreach ($request->role_ids as $roleId) {
                    SysUserRole::create([
                        'username'   => $username,
                        'role_id'    => $roleId,
                        'status'     => 'A',
                        'created_by' => $loginUser->username,
                    ]);
                }
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

        // ambil semua role user ini dari sys_user_role
        $userRoles = SysUserRole::where('username', $user->username)
            ->where('status', 'A')
            ->pluck('role_id')
            ->toArray();

        return response()->json([
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'npk'           => $user->npk,
            'role'          => $user->user_role, // user/admin
            'cpny_id'       => explode(',', $user->cpny_id),
            'department_id' => explode(',', $user->department_id),
            'role_ids'      => $userRoles,       // ⬅️ untuk di-set di select2
        ]);
    }
   

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'          => 'required',
            'email'         => 'required',
            'cpny_id'       => 'required|array',
            'department_id' => 'required|array',
            'role'          => 'required',
            'role_ids'      => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {

            $loginUser = Auth::user();

            $user = User::findOrFail($id);

            $companyIdsString = implode(',', $request->cpny_id);
            $deptIdsString    = implode(',', $request->department_id);

            $user->update([
                'name'          => strtoupper($request->name),
                'email'         => $request->email,
                'cpny_id'       => $companyIdsString,
                'department_id' => $deptIdsString,
                'user_role'     => $request->role,
                'npk'           => $request->npk,
                'updated_by'    => $loginUser->username,
            ]);

            // DELETE OLD ACCESS (company / dept)
            Usercpny::where('username', $user->username)->delete();
            Userdept::where('username', $user->username)->delete();

            foreach ($request->cpny_id as $cpny) {
                Usercpny::create([
                    'username'   => $user->username,
                    'cpny_id'    => $cpny,
                    'status'     => 'A',
                    'created_by' => $loginUser->username,
                ]);
            }

            foreach ($request->department_id as $dept) {
                Userdept::create([
                    'username'      => $user->username,
                    'department_id' => $dept,
                    'status'        => 'A',
                    'created_by'    => $loginUser->username,
                ]);
            }

            // ✅ RESET + INSERT ULANG SYS_USER_ROLE
            SysUserRole::where('username', $user->username)->delete();

            if ($request->filled('role_ids')) {
                foreach ($request->role_ids as $roleId) {
                    SysUserRole::create([
                        'username'   => $user->username,
                        'role_id'    => $roleId,
                        'status'     => 'A',
                        'created_by' => $loginUser->username,
                    ]);
                }
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

        if (!$currentUser || $currentUser->user_role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $targetUser = User::findOrFail($id);

        session(['impersonate_original_id' => $currentUser->id]);

        Auth::login($targetUser);

        return response()->json([
            'success'  => true,
            'message'  => 'Now logged in as ' . $targetUser->username,           
            'redirect' => route('users'),           
        ]);
    }


    /**
     * 🔁 Reset password user ke default: pakuwon1234#
     */
    public function resetPassword($id)
    {
        $currentUser = Auth::user();

        // Hanya admin yang boleh reset password
        if (!$currentUser || $currentUser->user_role !== 'admin') {
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
}
