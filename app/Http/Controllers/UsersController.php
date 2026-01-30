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
use App\Models\SysRole;
use App\Models\SysUserRole;
use App\Models\BusinessUnit;


class UsersController extends Controller
{
   
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        
        $company = MsCompany::select(['cpny_id', 'cpny_name'])->where('status', 'A')->get();
        $department = MsDepartment::select(['department_id', 'department_name'])->where('status', 'A')->get();
        // $businessUnits = BusinessUnit::select('business_unit_id')->where('status', 'A')->get();
        $businessUnits = BusinessUnit::select(['business_unit_id', 'business_unit_name'])
            ->where('status', 'A')
            ->distinct()
            ->get();

        $roles = SysRole::where('status', 'A')->orderBy('role_id')->get();

        return view('pages.users.users', compact('company', 'department', 'businessUnits', 'roles'));
    }


    public function json()
    {
        $users = User::select(['id','name','username','email','cpny_id','department_id','business_unit_id','jabatan','npk','status'])
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
            'business_unit_id' => 'required|array',
            'jabatan'       => 'required',            
            'role'          => 'required',          // user/admin (yang lama)
            'role_ids'      => 'nullable|array',    // ⬅️ daftar role RBAC (sys_user_role)
        ]);

        DB::beginTransaction();
        try {

            $loginUser = Auth::user();

            $companyIdsString = implode(',', $request->cpny_id);
            $deptIdsString    = implode(',', $request->department_id);
            $businessUnitIdsString = implode(',', $request->business_unit_id);

            $email    = $request->email;
            $username = explode('@', $email)[0];

            $password = Hash::make("pakuwon1234#");

            $user = User::create([
                'name'               => strtoupper($request->name),
                'email'              => $email,
                'username'           => $username,
                'cpny_id'            => $companyIdsString,
                'department_id'      => $deptIdsString,
                'business_unit_id'   => $businessUnitIdsString,
                'jabatan'            => $request->jabatan,
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

            // USERBUSINESSUNIT
            foreach ($request->business_unit_id as $bu) {
                Userbusinessunit::create([
                    'username'      => $username,
                    'business_unit_id' => $bu,
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
            'jabatan'       => $user->jabatan,
            'role'          => $user->user_role, // user/admin
            'cpny_id'       => explode(',', $user->cpny_id),
            'department_id' => explode(',', $user->department_id),
            'business_unit_id' => explode(',', $user->business_unit_id),
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
            'business_unit_id' => 'required|array',
            'jabatan'       => 'required',
            'role'          => 'required',
            'role_ids'      => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {

            $loginUser = Auth::user();

            $user = User::findOrFail($id);

            $companyIdsString = implode(',', $request->cpny_id);
            $deptIdsString    = implode(',', $request->department_id);
            $businessUnitIdsString = implode(',', $request->business_unit_id);

            $user->update([
                'name'          => strtoupper($request->name),
                'email'         => $request->email,
                'cpny_id'       => $companyIdsString,
                'department_id' => $deptIdsString,
                'business_unit_id' => $businessUnitIdsString,
                'user_role'     => $request->role,
                'npk'           => $request->npk,
                'jabatan'       => $request->jabatan,
                'updated_by'    => $loginUser->username,
            ]);

            // DELETE OLD ACCESS (company / dept)
            Usercpny::where('username', $user->username)->delete();
            Userdept::where('username', $user->username)->delete();
            Userbusinessunit::where('username', $user->username)->delete();

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

            foreach ($request->business_unit_id as $bu) {
                Userbusinessunit::create([
                    'username'      => $user->username,
                    'business_unit_id' => $bu,
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
