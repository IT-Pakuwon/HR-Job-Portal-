<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\Tenancy\TsTenant;
use App\Models\Tenancy\TsUser;
use App\Models\Tenancy\TsUserTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TsUserTenantController extends Controller
{
    public function json()
    {
        $userTenants = TsUserTenant::with('tenant:id,storename')
            ->select(['id', 'tenantid', 'name', 'companyname', 'email', 'phone', 'username', 'status'])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $userTenants]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tenantid'    => 'required|exists:mysql5.mstenant,id',
            'name'        => 'required|string|max:100',
            'companyname' => 'nullable|string|max:100',
            'email'       => 'required|email|max:50',
            'phone'       => 'nullable|string|max:50',
            'username'    => 'required|string|max:50|unique:mysql5.user,username',
            'password'    => 'required|string|min:6',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();
            $tenant = TsTenant::findOrFail($request->tenantid);
            $hashed = Hash::make($request->password);
            $now = now();

            $user = TsUser::create([
                'name'                => $request->name,
                'companyname'         => $request->companyname,
                'phone'               => $request->phone,
                'email'               => $request->email,
                'username'            => $request->username,
                'password'            => $hashed,
                'is_admin'            => 0,
                'usertype'            => 'TENANT',
                'siteid'              => $tenant->siteid,
                'refid'               => $tenant->id,
                'status'              => 'A',
                'created_user'        => $loginUser->username ?? 'system',
                'created_datetime'    => $now,
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => $now,
            ]);

            $userTenant = TsUserTenant::create([
                'name'                => $request->name,
                'companyname'         => $request->companyname,
                'phone'               => $request->phone,
                'email'               => $request->email,
                'username'            => $request->username,
                'password'            => $hashed,
                'userid'              => $user->id,
                'siteid'              => $tenant->siteid,
                'usertype'            => 'TENANT',
                'tenantid'            => $tenant->id,
                'status'              => 'A',
                'created_user'        => $loginUser->username ?? 'system',
                'created_datetime'    => $now,
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => $now,
            ]);

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true, 'user_tenant' => $userTenant]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan user tenant',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $userTenant = TsUserTenant::findOrFail($id);

        return response()->json([
            'id'          => $userTenant->id,
            'tenantid'    => $userTenant->tenantid,
            'name'        => $userTenant->name,
            'companyname' => $userTenant->companyname,
            'email'       => $userTenant->email,
            'phone'       => $userTenant->phone,
            'username'    => $userTenant->username,
            'status'      => $userTenant->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $userTenant = TsUserTenant::findOrFail($id);

        $request->validate([
            'tenantid'    => 'required|exists:mysql5.mstenant,id',
            'name'        => 'required|string|max:100',
            'companyname' => 'nullable|string|max:100',
            'email'       => 'required|email|max:50',
            'phone'       => 'nullable|string|max:50',
            'username'    => ['required', 'string', 'max:50', Rule::unique('mysql5.user', 'username')->ignore($userTenant->userid)],
            'password'    => 'nullable|string|min:6',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();
            $tenant = TsTenant::findOrFail($request->tenantid);
            $now = now();

            $shared = [
                'name'                => $request->name,
                'companyname'         => $request->companyname,
                'phone'               => $request->phone,
                'email'               => $request->email,
                'username'            => $request->username,
                'siteid'              => $tenant->siteid,
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => $now,
            ];

            if ($request->filled('password')) {
                $shared['password'] = Hash::make($request->password);
            }

            TsUser::where('id', $userTenant->userid)->update($shared + ['refid' => $tenant->id]);
            $userTenant->update($shared + ['tenantid' => $tenant->id]);

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal update user tenant',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $userTenant = TsUserTenant::findOrFail($id);
        $newStatus = request('status'); // A / X
        $loginUser = Auth::user();
        $now = now();

        DB::connection('mysql5')->beginTransaction();
        try {
            TsUser::where('id', $userTenant->userid)->update([
                'status'              => $newStatus,
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => $now,
            ]);

            $userTenant->update([
                'status'              => $newStatus,
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => $now,
            ]);

            DB::connection('mysql5')->commit();
            return response()->json(['message' => 'Status updated']);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal update status',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
