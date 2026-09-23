<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsDepartment;
use App\Models\MsGroup;
use App\Models\MsProject;
use App\Models\MsProjectStatus;
use App\Models\MsTaskStatus;
use App\Models\TrProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PmProjectController extends Controller
{
    use HasAutonbr;

    private function hasProjectAccess(): bool
    {
        return (bool) Auth::user()?->hasRole('PROJECTACCESS');
    }

    // Org admins (ORGPROJECTACCESS, who manage Groups/Teams) can also browse
    // the Projects pages — mainly so they can reach "Manage Teams" — even
    // though only PROJECTACCESS holders can actually create/use Projects.
    private function canBrowse(): bool
    {
        return $this->hasProjectAccess() || (bool) Auth::user()?->hasRole('ORGPROJECTACCESS');
    }

    // Groups the current user can see Projects for: must hold PROJECTACCESS
    // (module access) and their own ms_department chains into one of the
    // Group's assigned ms_department_opr rows.
    private function myGroups()
    {
        $user = Auth::user();

        if (!$this->hasProjectAccess()) {
            return collect();
        }

        $departmentOprId = MsDepartment::where('department_id', $user->department_id)->value('department_opr_id');

        if (!$departmentOprId) {
            return collect();
        }

        return MsGroup::where('status', 'A')
            ->whereHas('details', fn ($q) => $q->where('department_opr_id', $departmentOprId))
            ->orderBy('group_name')
            ->get();
    }

    private function assertGroupAccess(MsProject $project): void
    {
        $groupIds = $this->myGroups()->pluck('group_id');
        abort_unless($groupIds->contains($project->group_id) || Auth::user()->isAdmin(), 403);
    }

    public function index()
    {
        abort_unless($this->canBrowse(), 403);

        return view('pages.projectmanagement.projects', ['initialTab' => 'cards']);
    }

    public function kanban()
    {
        abort_unless($this->canBrowse(), 403);

        return view('pages.projectmanagement.projects', ['initialTab' => 'kanban']);
    }

    public function gantt()
    {
        abort_unless($this->canBrowse(), 403);

        return view('pages.projectmanagement.projects', ['initialTab' => 'gantt']);
    }

    // Shared data feed for all 3 portfolio views (Kanban / Cards / Gantt).
    public function boardData(Request $request)
    {
        $groups = $this->myGroups();
        $groupIds = $groups->pluck('group_id');

        $groupId = $request->query('group_id');
        $projectsQuery = MsProject::where('status', 'A')->whereIn('group_id', $groupIds);

        if ($groupId) {
            $projectsQuery->where('group_id', $groupId);
        }

        $projects = $projectsQuery->orderBy('start_date')->get();
        $statuses = MsProjectStatus::where('status', 'A')->orderBy('sort_order')->get();

        return response()->json([
            'groups' => $groups->map(fn ($g) => ['group_id' => $g->group_id, 'group_name' => $g->group_name]),
            'statuses' => $statuses,
            'projects' => $projects->map(fn ($p) => [
                'project_id' => $p->project_id,
                'group_id' => $p->group_id,
                'project_name' => $p->project_name,
                'project_description' => $p->project_description,
                'start_date' => optional($p->start_date)->toDateString(),
                'end_date' => optional($p->end_date)->toDateString(),
                'status_id' => $p->status_id,
                'progress_percent' => (float) $p->progress_percent,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($this->hasProjectAccess(), 403);

        $request->validate([
            'group_id' => ['required', 'string', 'exists:pgsql5.ms_group,group_id'],
            'project_name' => ['required', 'string', 'max:255'],
            'project_description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $groupIds = $this->myGroups()->pluck('group_id');
        abort_unless($groupIds->contains($request->group_id) || Auth::user()->isAdmin(), 403);

        $username = Auth::user()->username;
        $now = now();
        $defaultStatus = MsProjectStatus::where('status_id', 'NOTSTARTED')->exists() ? 'NOTSTARTED' : null;

        $auto = $this->nextAutonbr('PRJ', (int) $now->year, $now->format('m'), $username, 'Project');
        $projectId = 'PRJ' . substr((string) $now->year, 2) . $now->format('m') . sprintf('%04d', $auto['next']);

        $project = DB::connection('pgsql5')->transaction(function () use ($request, $projectId, $username, $now, $defaultStatus) {
            $project = MsProject::create([
                'project_id' => $projectId,
                'group_id' => $request->group_id,
                'project_name' => $request->project_name,
                'project_description' => $request->project_description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status_id' => $defaultStatus,
                'progress_percent' => 0,
                'status' => 'A',
                'created_by' => $username,
                'created_at' => $now,
            ]);

            // Seed default Task-board statuses for this Project (To Do / In
            // Progress / Done), matching the same idea as ms_project_status.
            foreach ([['TODO', 'To Do', '#9CA3AF', 0], ['INPROGRESS', 'In Progress', '#3B82F6', 1], ['DONE', 'Done', '#10B981', 2]] as [$id, $name, $color, $order]) {
                MsTaskStatus::create([
                    'status_id' => $id,
                    'project_id' => $projectId,
                    'status_name' => $name,
                    'color' => $color,
                    'sort_order' => $order,
                    'status' => 'A',
                    'created_by' => $username,
                    'created_at' => $now,
                ]);
            }

            return $project;
        });

        return response()->json(['success' => true, 'message' => 'Project created successfully', 'project_id' => $project->project_id]);
    }

    public function show(string $projectId)
    {
        $project = MsProject::where('project_id', $projectId)->firstOrFail();
        $this->assertGroupAccess($project);

        $linkableProjects = MsProject::where('status', 'A')
            ->where('project_id', '!=', $project->project_id)
            ->whereIn('group_id', $this->myGroups()->pluck('group_id'))
            ->orderBy('project_name')
            ->get(['project_id', 'project_name', 'group_id']);

        $linkedProjects = $project->linkedProjects();
        $statuses = MsProjectStatus::where('status', 'A')->orderBy('sort_order')->get();
        $group = MsGroup::where('group_id', $project->group_id)->first();
        $eligibleUsers = $group?->eligibleUsers() ?? collect();

        return view('pages.projectmanagement.project_show', compact('project', 'linkableProjects', 'linkedProjects', 'statuses', 'eligibleUsers'));
    }

    public function update(Request $request, string $projectId)
    {
        $project = MsProject::where('project_id', $projectId)->firstOrFail();
        $this->assertGroupAccess($project);

        $request->validate([
            'project_name' => ['required', 'string', 'max:255'],
            'project_description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status_id' => ['nullable', 'string', 'exists:pgsql5.ms_project_status,status_id'],
        ]);

        $project->update([
            'project_name' => $request->project_name,
            'project_description' => $request->project_description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status_id' => $request->status_id ?? $project->status_id,
            'updated_by' => Auth::user()->username,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Project updated successfully']);
    }

    // Drag-and-drop status change on the portfolio Kanban.
    public function updateStatus(Request $request, string $projectId)
    {
        $project = MsProject::where('project_id', $projectId)->firstOrFail();
        $this->assertGroupAccess($project);

        $request->validate(['status_id' => ['required', 'string', 'exists:pgsql5.ms_project_status,status_id']]);

        $project->update([
            'status_id' => $request->status_id,
            'updated_by' => Auth::user()->username,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(string $projectId)
    {
        $project = MsProject::where('project_id', $projectId)->firstOrFail();
        $this->assertGroupAccess($project);

        $project->update(['status' => 'X', 'updated_by' => Auth::user()->username, 'updated_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Project archived successfully']);
    }

    // Global project-status columns ("+ Add status" on the portfolio Kanban).
    public function storeStatus(Request $request)
    {
        abort_unless($this->hasProjectAccess(), 403);

        $request->validate(['status_name' => ['required', 'string', 'max:100']]);

        $statusId = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $request->status_name));
        $nextOrder = (int) MsProjectStatus::max('sort_order') + 1;

        $status = MsProjectStatus::firstOrCreate(
            ['status_id' => $statusId],
            [
                'status_name' => $request->status_name,
                'color' => $request->input('color', '#6366F1'),
                'sort_order' => $nextOrder,
                'status' => 'A',
                'created_by' => Auth::user()->username,
                'created_at' => now(),
            ]
        );

        return response()->json(['success' => true, 'status' => $status]);
    }

    // ── Project-to-project linking ──────────────────────────────────────
    public function link(Request $request, string $projectId)
    {
        $project = MsProject::where('project_id', $projectId)->firstOrFail();
        $this->assertGroupAccess($project);

        $request->validate(['linked_project_id' => ['required', 'string', 'exists:pgsql5.ms_project,project_id', 'different:project_id']]);

        $linkedId = $request->linked_project_id;

        $exists = TrProject::where('status', 'A')
            ->where(function ($q) use ($projectId, $linkedId) {
                $q->where(['project_id' => $projectId, 'linked_project_id' => $linkedId])
                  ->orWhere(['project_id' => $linkedId, 'linked_project_id' => $projectId]);
            })->exists();

        if (!$exists) {
            TrProject::create([
                'project_id' => $projectId,
                'linked_project_id' => $linkedId,
                'status' => 'A',
                'created_by' => Auth::user()->username,
                'created_at' => now(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Projects linked successfully']);
    }

    public function unlink(Request $request, string $projectId, string $linkedProjectId)
    {
        $project = MsProject::where('project_id', $projectId)->firstOrFail();
        $this->assertGroupAccess($project);

        TrProject::where('status', 'A')
            ->where(function ($q) use ($projectId, $linkedProjectId) {
                $q->where(['project_id' => $projectId, 'linked_project_id' => $linkedProjectId])
                  ->orWhere(['project_id' => $linkedProjectId, 'linked_project_id' => $projectId]);
            })
            ->update(['status' => 'X', 'updated_by' => Auth::user()->username, 'updated_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Projects unlinked successfully']);
    }

    // @mention autocomplete for a Project's chat — eligible members of its Group.
    public function mentionableUsers(string $projectId)
    {
        $project = MsProject::where('project_id', $projectId)->firstOrFail();
        $this->assertGroupAccess($project);

        $group = MsGroup::where('group_id', $project->group_id)->firstOrFail();

        $users = $group->eligibleUsers()
            ->reject(fn ($u) => strtolower(trim($u->username)) === strtolower(Auth::user()->username))
            ->values();

        return response()->json($users->map(fn ($u) => ['username' => $u->username, 'name' => $u->name]));
    }
}
