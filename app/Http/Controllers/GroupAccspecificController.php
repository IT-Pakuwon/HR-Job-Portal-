<?php

namespace App\Http\Controllers;

use App\Models\GroupAccspecific;
use App\Models\DepartmentHR;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupAccspecificController extends Controller
{
    protected $groupAccessOptions = [
        'STEP',
        'PAYROLL',
        'EDIT',
        'POSTING',
        'CHECKLIST',
        'INTERVIEWUSER',
        'INTERVIEWHC',
        'JOIN',
        'SCHEDULE',
    ];

    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $users = User::select('username', 'name')
            ->orderBy('username')
            ->get();

        $departments = DepartmentHR::select('department_id', 'department_name')
            ->orderBy('department_name')
            ->get();

        return view('pages.groupaccspecific.groupaccspecific', [
            'groupAccessOptions' => $this->groupAccessOptions,
            'users' => $users,
            'departments' => $departments,
        ]);
    }

    public function json()
    {
        $rows = GroupAccspecific::select([
                'id',
                'group_access_id',
                'group_access_name',
                'username',
                'department_id',
                'parameter_access_id',
                'status',
            ])
            ->orderBy('group_access_id')
            ->orderBy('username')
            ->get();

        return response()->json([
            'data' => $rows,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'group_access_id' => 'required|string|max:50',
            'group_access_name' => 'nullable|string|max:100',
            'username' => 'required|string|max:50',
            'department_id' => 'nullable|string|max:20',
            'parameter_access_id' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();
            $now = now();
            $createdUser = $user->username ?? 'system';

            $row = GroupAccspecific::create([
                'group_access_id' => trim($request->group_access_id),
                'group_access_name' => $request->filled('group_access_name') ? trim($request->group_access_name) : null,
                'username' => trim($request->username),
                'department_id' => $request->filled('department_id') ? trim($request->department_id) : null,
                'parameter_access_id' => $request->filled('parameter_access_id') ? trim($request->parameter_access_id) : null,
                'status' => 'A',
                'created_user' => $createdUser,
                'created_at' => $now,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $row,
                'message' => 'Group Access Specific berhasil disimpan',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan Group Access Specific',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $row = GroupAccspecific::findOrFail($id);

        return response()->json([
            'id' => $row->id,
            'group_access_id' => $row->group_access_id,
            'group_access_name' => $row->group_access_name,
            'username' => $row->username,
            'department_id' => $row->department_id,
            'parameter_access_id' => $row->parameter_access_id,
            'status' => $row->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $row = GroupAccspecific::findOrFail($id);

        $request->validate([
            'group_access_id' => 'required|string|max:50',
            'group_access_name' => 'nullable|string|max:100',
            'username' => 'required|string|max:50',
            'department_id' => 'nullable|string|max:20',
            'parameter_access_id' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();
            $now = now();
            $updatedUser = $user->username ?? 'system';

            $row->update([
                'group_access_id' => trim($request->group_access_id),
                'group_access_name' => $request->filled('group_access_name') ? trim($request->group_access_name) : null,
                'username' => trim($request->username),
                'department_id' => $request->filled('department_id') ? trim($request->department_id) : null,
                'parameter_access_id' => $request->filled('parameter_access_id') ? trim($request->parameter_access_id) : null,
                'updated_user' => $updatedUser,
                'updated_at' => $now,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Group Access Specific berhasil diupdate',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update Group Access Specific',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:A,X',
        ]);

        $row = GroupAccspecific::findOrFail($id);
        $user = Auth::user();

        $row->update([
            'status' => $request->status,
            'updated_user' => $user->username ?? 'system',
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'status' => $request->status,
            'message' => 'Status berhasil diupdate',
        ]);
    }
}
