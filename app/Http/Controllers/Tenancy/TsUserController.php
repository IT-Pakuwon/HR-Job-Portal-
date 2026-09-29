<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\Tenancy\TsUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TsUserController extends Controller
{
    public function json()
    {
        $users = TsUser::select(['id', 'name', 'companyname', 'email', 'username', 'usertype', 'siteid', 'departmentid', 'status'])
            ->orderByDesc('id')
            ->get();

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
            'departmentid' => 'nullable|string|max:50',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $user = TsUser::create([
                'name'                => $request->name,
                'companyname'         => $request->companyname,
                'phone'               => $request->phone,
                'email'               => $request->email,
                'username'            => $request->username,
                'password'            => Hash::make($request->password),
                'is_admin'            => false,
                'usertype'            => $request->usertype,
                'siteid'              => $request->siteid,
                'departmentid'        => $request->departmentid,
                'status'              => 'A',
                'created_user'        => $loginUser->username ?? 'system',
                'created_datetime'    => now(),
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => now(),
            ]);

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
            'departmentid' => 'nullable|string|max:50',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $data = [
                'name'                => $request->name,
                'companyname'         => $request->companyname,
                'phone'               => $request->phone,
                'email'               => $request->email,
                'username'            => $request->username,
                'usertype'            => $request->usertype,
                'siteid'              => $request->siteid,
                'departmentid'        => $request->departmentid,
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => now(),
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

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
