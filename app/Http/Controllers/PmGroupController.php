<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsDepartmentOpr;
use App\Models\MsGroup;
use App\Models\TrGroupDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PmGroupController extends Controller
{
    use HasAutonbr;

    // Creating/managing Groups & Teams is a dedicated org-admin capability,
    // separate from PROJECTACCESS (which gates Project/Task/Subtask creation
    // and day-to-day module use).
    private function hasOrgAccess(): bool
    {
        return (bool) Auth::user()?->hasRole('ORGPROJECTACCESS');
    }

    // Either role can browse the Teams list (a PROJECTACCESS holder needs
    // this to reach "Manage Teams" from the Projects page) — only
    // ORGPROJECTACCESS can actually create/edit/deactivate a Team.
    private function canView(): bool
    {
        return $this->hasOrgAccess() || (bool) Auth::user()?->hasRole('PROJECTACCESS');
    }

    public function index()
    {
        abort_unless($this->canView(), 403);

        $departmentOprs = MsDepartmentOpr::where('status', 'A')
            ->orderBy('cpny_id')
            ->orderBy('department_name')
            ->get(['department_opr_id', 'cpny_id', 'department_name']);

        // Only companies that actually have operational departments to pick from.
        $companies = \App\Models\MsCompany::where('status', 'A')
            ->whereIn('cpny_id', $departmentOprs->pluck('cpny_id')->unique())
            ->orderBy('cpny_id')
            ->get(['cpny_id', 'cpny_name']);

        $canManageGroups = $this->hasOrgAccess();

        return view('pages.projectmanagement.groups', compact('departmentOprs', 'companies', 'canManageGroups'));
    }

    public function json()
    {
        $groups = MsGroup::where('status', 'A')
            ->orderBy('group_name')
            ->get();

        $data = $groups->map(function ($group) {
            return [
                'group_id' => $group->group_id,
                'group_name' => $group->group_name,
                'group_description' => $group->group_description,
                'departments' => $group->details()->pluck('department_opr_id'),
                'member_count' => $group->members()->count(),
                'project_count' => $group->projects()->where('status', 'A')->count(),
                'created_by' => $group->created_by,
                'created_at' => optional($group->created_at)->toDateTimeString(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        abort_unless($this->hasOrgAccess(), 403);

        $request->validate([
            'group_name' => ['required', 'string', 'max:255'],
            'group_description' => ['nullable', 'string'],
            'department_opr_id' => ['required', 'array', 'min:1'],
            'department_opr_id.*' => ['string', 'exists:pgsql2.ms_department_opr,department_opr_id'],
            'members' => ['nullable', 'array'],
            'members.*' => ['string', 'exists:pgsql2.ms_user,username'],
        ]);

        $username = Auth::user()->username;
        $now = now();

        // Only usernames actually in the department+role candidate pool can
        // be added as members — protects against a tampered request adding
        // someone outside the Group's intended scope.
        $candidateUsernames = MsGroup::candidateUsersForDepartmentOprIds($request->department_opr_id)
            ->pluck('username')->map(fn ($u) => strtolower(trim($u)));
        $members = collect($request->input('members', []))
            ->filter(fn ($u) => $candidateUsernames->contains(strtolower(trim($u))));

        $auto = $this->nextAutonbr('GRP', (int) $now->year, $now->format('m'), $username, 'Project Group');
        $groupId = 'GRP' . substr((string) $now->year, 2) . $now->format('m') . sprintf('%04d', $auto['next']);

        DB::connection('pgsql5')->transaction(function () use ($request, $groupId, $username, $now, $members) {
            MsGroup::create([
                'group_id' => $groupId,
                'group_name' => $request->group_name,
                'group_description' => $request->group_description,
                'status' => 'A',
                'created_by' => $username,
                'created_at' => $now,
            ]);

            $departmentOprs = MsDepartmentOpr::whereIn('department_opr_id', $request->department_opr_id)
                ->get(['department_opr_id', 'cpny_id']);

            foreach ($departmentOprs as $opr) {
                TrGroupDetail::create([
                    'group_id' => $groupId,
                    'department_opr_id' => $opr->department_opr_id,
                    'cpny_id' => $opr->cpny_id,
                    'status' => 'A',
                    'created_by' => $username,
                    'created_at' => $now,
                ]);
            }

            foreach ($members as $memberUsername) {
                \App\Models\TrGroupMember::create([
                    'group_id' => $groupId,
                    'username' => $memberUsername,
                    'added_by' => $username,
                    'added_at' => $now,
                    'status' => 'A',
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Group created successfully',
            'group_id' => $groupId,
        ]);
    }

    public function edit(string $groupId)
    {
        $group = MsGroup::where('group_id', $groupId)->firstOrFail();

        return response()->json([
            'group_id' => $group->group_id,
            'group_name' => $group->group_name,
            'group_description' => $group->group_description,
            'department_opr_id' => $group->details()->pluck('department_opr_id'),
            'members' => $group->members()->pluck('username'),
        ]);
    }

    public function update(Request $request, string $groupId)
    {
        $group = MsGroup::where('group_id', $groupId)->firstOrFail();

        abort_unless($this->hasOrgAccess(), 403);

        $request->validate([
            'group_name' => ['required', 'string', 'max:255'],
            'group_description' => ['nullable', 'string'],
            'department_opr_id' => ['required', 'array', 'min:1'],
            'department_opr_id.*' => ['string', 'exists:pgsql2.ms_department_opr,department_opr_id'],
            'members' => ['nullable', 'array'],
            'members.*' => ['string', 'exists:pgsql2.ms_user,username'],
        ]);

        $username = Auth::user()->username;
        $now = now();

        $candidateUsernames = MsGroup::candidateUsersForDepartmentOprIds($request->department_opr_id)
            ->pluck('username')->map(fn ($u) => strtolower(trim($u)));
        $members = collect($request->input('members', []))
            ->filter(fn ($u) => $candidateUsernames->contains(strtolower(trim($u))));

        DB::connection('pgsql5')->transaction(function () use ($request, $group, $username, $now, $members) {
            $group->update([
                'group_name' => $request->group_name,
                'group_description' => $request->group_description,
                'updated_by' => $username,
                'updated_at' => $now,
            ]);

            TrGroupDetail::where('group_id', $group->group_id)->update([
                'status' => 'X',
                'updated_by' => $username,
                'updated_at' => $now,
            ]);

            $departmentOprs = MsDepartmentOpr::whereIn('department_opr_id', $request->department_opr_id)
                ->get(['department_opr_id', 'cpny_id']);

            foreach ($departmentOprs as $opr) {
                $existing = TrGroupDetail::where('group_id', $group->group_id)
                    ->where('department_opr_id', $opr->department_opr_id)
                    ->first();

                if ($existing) {
                    $existing->update(['status' => 'A', 'updated_by' => $username, 'updated_at' => $now]);
                } else {
                    TrGroupDetail::create([
                        'group_id' => $group->group_id,
                        'department_opr_id' => $opr->department_opr_id,
                        'cpny_id' => $opr->cpny_id,
                        'status' => 'A',
                        'created_by' => $username,
                        'created_at' => $now,
                    ]);
                }
            }

            \App\Models\TrGroupMember::where('group_id', $group->group_id)->update(['status' => 'X']);

            foreach ($members as $memberUsername) {
                $existing = \App\Models\TrGroupMember::where('group_id', $group->group_id)
                    ->where('username', $memberUsername)
                    ->first();

                if ($existing) {
                    $existing->update(['status' => 'A']);
                } else {
                    \App\Models\TrGroupMember::create([
                        'group_id' => $group->group_id,
                        'username' => $memberUsername,
                        'added_by' => $username,
                        'added_at' => $now,
                        'status' => 'A',
                    ]);
                }
            }
        });

        return response()->json(['success' => true, 'message' => 'Group updated successfully']);
    }

    public function toggleStatus(Request $request, string $groupId)
    {
        $group = MsGroup::where('group_id', $groupId)->firstOrFail();

        abort_unless($this->hasOrgAccess(), 403);

        $request->validate(['status' => ['required', 'in:A,X']]);

        $group->update([
            'status' => $request->status,
            'updated_by' => Auth::user()->username,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Status updated successfully']);
    }

    // AJAX: the candidate pool (department + PROJECTACCESS matched) for the
    // selected department_opr_id set — populates the Members picker live
    // while building the form, before the Group is even saved.
    public function previewEligibleUsers(Request $request)
    {
        $request->validate([
            'department_opr_id' => ['required', 'array', 'min:1'],
            'department_opr_id.*' => ['string'],
        ]);

        $users = MsGroup::candidateUsersForDepartmentOprIds($request->department_opr_id)
            ->map(fn ($u) => $u->only(['username', 'name', 'department_id']))
            ->values();

        return response()->json(['data' => $users]);
    }
}
