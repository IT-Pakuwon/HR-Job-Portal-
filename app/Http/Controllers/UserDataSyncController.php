<?php

namespace App\Http\Controllers;

use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\User;
use App\Models\ViewUsersTalenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function talentaJson()
    {
        $rows = ViewUsersTalenta::query()
            ->whereNotNull('employee_id')
            ->where('employee_id', '!=', '')
            ->orderBy('employee_id')
            ->get(['employee_id', 'first_name', 'last_name', 'organization_name', 'job_position', 'status_talenta'])
            ->map(function ($t) {
                $org = trim((string) $t->organization_name);
                $parts = explode(' - ', $org, 2);

                return [
                    'npk' => trim($t->employee_id),
                    'name' => trim($t->first_name . ' ' . $t->last_name),
                    'organization' => $org,
                    'company' => count($parts) === 2 ? trim($parts[0]) : $org,
                    'department' => count($parts) === 2 ? trim($parts[1]) : '',
                    'job_position' => $t->job_position,
                    'status' => $t->status_talenta,
                ];
            })
            ->values();

        return response()->json(['data' => $rows]);
    }

    public function usersJson()
    {
        $users = User::query()
            ->where('status', 'A')
            ->orderBy('name')
            ->get(['id', 'username', 'name', 'npk', 'origin_cpny_id', 'origin_department_id', 'status']);

        return response()->json(['data' => $users]);
    }

    public function options()
    {
        return response()->json([
            'companies' => MsCompany::query()->orderBy('cpny_id')->get(['cpny_id', 'cpny_name'])
                ->unique('cpny_id')->values(),
            'departments' => MsDepartment::query()->orderBy('department_id')->get(['department_id', 'department_name'])
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
