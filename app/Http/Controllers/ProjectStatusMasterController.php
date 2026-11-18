<?php

namespace App\Http\Controllers;

use App\Models\MsProject;
use App\Models\MsProjectStatus;
use App\Models\MsProjectTaskStatus;
use App\Models\MsTeam;
use App\Models\MsTeamTaskStatus;
use App\Models\TrProjectStatusTeam;
use App\Models\TrProjectTask;
use App\Models\TrProjectTeam;
use App\Models\TrTeamTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Global Settings > Project Setup > Status Master — admin-only screen to
// view / add / edit / delete the status masters behind the Task Management
// boards:
//   ms_project_status        global Project statuses (Kanban columns)
//   tr_project_status_team   which Project statuses each Team uses
//   ms_project_task_status   task statuses owned by one Project
//   ms_team_task_status      task statuses owned by one Team
// "Delete" is the app-wide soft delete (status 'X') and is refused while
// anything active still sits in the status, so no card/project is orphaned.
class ProjectStatusMasterController extends Controller
{
    public function index()
    {
        return view('pages.project_setup.status_master');
    }

    // ── helpers ─────────────────────────────────────────────────────────
    private function makeId(string $name, ?string $custom = null): string
    {
        $id = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $custom !== null && $custom !== '' ? $custom : $name));
        abort_if($id === '', 422, 'Status ID must contain at least one letter or number.');

        return substr($id, 0, 20);
    }

    private function validated(Request $request, bool $creating): array
    {
        return $request->validate([
            'status_name' => ['required', 'string', 'max:100'],
            'status_id' => [$creating ? 'nullable' : 'prohibited', 'string', 'max:20'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);
    }

    // Counts of active things sitting in each status, keyed by status_id.
    private function projectUsage()
    {
        return MsProject::where('status', 'A')->whereNotNull('status_id')
            ->selectRaw('status_id, count(*) as c')->groupBy('status_id')->pluck('c', 'status_id');
    }

    // ── Tab 1: global Project statuses ──────────────────────────────────
    public function projectStatuses()
    {
        $usage = $this->projectUsage();
        $teamCounts = TrProjectStatusTeam::where('status', 'A')
            ->selectRaw('status_id, count(*) as c')->groupBy('status_id')->pluck('c', 'status_id');

        return response()->json(
            MsProjectStatus::orderBy('sort_order')->orderBy('status_name')->get()->map(fn ($s) => [
                'status_id' => $s->status_id,
                'status_name' => $s->status_name,
                'color' => $s->color,
                'sort_order' => $s->sort_order,
                'active' => $s->status === 'A',
                'projects' => (int) ($usage[$s->status_id] ?? 0),
                'teams' => (int) ($teamCounts[$s->status_id] ?? 0),
            ])->values()
        );
    }

    public function storeProjectStatus(Request $request)
    {
        $data = $this->validated($request, true);
        $name = trim($data['status_name']);
        $id = $this->makeId($name, $data['status_id'] ?? null);

        abort_if(MsProjectStatus::where('status_id', $id)->exists(), 422, "Status ID \"{$id}\" already exists.");

        MsProjectStatus::create([
            'status_id' => $id,
            'status_name' => $name,
            'color' => $data['color'] ?? '#6366F1',
            'sort_order' => $data['sort_order'] ?? ((int) MsProjectStatus::max('sort_order') + 1),
            'status' => 'A',
            'created_by' => Auth::user()->username,
            'created_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Status added.']);
    }

    public function updateProjectStatus(Request $request, string $statusId)
    {
        $data = $this->validated($request, false);
        $status = MsProjectStatus::where('status_id', $statusId)->firstOrFail();

        $status->update([
            'status_name' => trim($data['status_name']),
            'color' => $data['color'] ?? $status->color,
            'sort_order' => $data['sort_order'] ?? $status->sort_order,
            'updated_by' => Auth::user()->username,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Status updated.']);
    }

    public function toggleProjectStatus(string $statusId)
    {
        $status = MsProjectStatus::where('status_id', $statusId)->firstOrFail();

        if ($status->status === 'A') {
            $used = (int) ($this->projectUsage()[$statusId] ?? 0);
            abort_if($used > 0, 422, "{$used} active project(s) are still in this status. Move them first.");
            $status->update(['status' => 'X', 'deleted_by' => Auth::user()->username, 'deleted_at' => now()]);
            $msg = 'Status deleted.';
        } else {
            $status->update(['status' => 'A', 'deleted_by' => null, 'deleted_at' => null,
                'updated_by' => Auth::user()->username, 'updated_at' => now()]);
            $msg = 'Status restored.';
        }

        return response()->json(['success' => true, 'message' => $msg]);
    }

    // ── Tab 2: which Project statuses each Team uses ────────────────────
    public function teams()
    {
        return response()->json(
            MsTeam::where('status', 'A')->orderBy('team_name')->get(['team_id', 'team_name'])
        );
    }

    public function teamStatuses(string $teamId)
    {
        MsTeam::where('team_id', $teamId)->firstOrFail();

        return response()->json(
            TrProjectStatusTeam::where('team_id', $teamId)->where('status', 'A')->pluck('status_id')->values()
        );
    }

    public function syncTeamStatuses(Request $request, string $teamId)
    {
        MsTeam::where('team_id', $teamId)->firstOrFail();

        $request->validate(['status_ids' => ['present', 'array'], 'status_ids.*' => ['string']]);
        $wanted = collect($request->status_ids)->unique()->values();

        $valid = MsProjectStatus::where('status', 'A')->whereIn('status_id', $wanted)->pluck('status_id');
        abort_if($valid->count() !== $wanted->count(), 422, 'Unknown or inactive status selected.');

        // A status can't be switched off for a Team while that Team's active
        // Projects still sit in it (its Kanban column would vanish).
        $teamProjectIds = TrProjectTeam::where('team_id', $teamId)->where('status', 'A')->pluck('project_id');
        $current = TrProjectStatusTeam::where('team_id', $teamId)->where('status', 'A')->pluck('status_id');
        $removing = $current->diff($wanted);
        if ($removing->isNotEmpty()) {
            $blocked = MsProject::where('status', 'A')->whereIn('project_id', $teamProjectIds)
                ->whereIn('status_id', $removing)->distinct()->pluck('status_id');
            $names = MsProjectStatus::whereIn('status_id', $blocked)->pluck('status_name');
            abort_if($blocked->isNotEmpty(), 422, 'Projects of this Team are still in: ' . $names->implode(', ') . '.');
        }

        $user = Auth::user()->username;
        foreach ($wanted as $statusId) {
            $row = TrProjectStatusTeam::where('team_id', $teamId)->where('status_id', $statusId)->first();
            if ($row) {
                if ($row->status !== 'A') {
                    TrProjectStatusTeam::where('team_id', $teamId)->where('status_id', $statusId)->update(['status' => 'A']);
                }
            } else {
                TrProjectStatusTeam::create(['status_id' => $statusId, 'team_id' => $teamId,
                    'status' => 'A', 'created_by' => $user, 'created_at' => now()]);
            }
        }
        if ($removing->isNotEmpty()) {
            TrProjectStatusTeam::where('team_id', $teamId)->whereIn('status_id', $removing)->update(['status' => 'X']);
        }

        return response()->json(['success' => true, 'message' => 'Team statuses saved.']);
    }

    // ── Tab 3: task statuses owned by a Project or a Team ───────────────
    public function projectsList()
    {
        return response()->json(
            MsProject::where('status', 'A')->orderBy('project_name')->get(['project_id', 'project_name'])
        );
    }

    // kind = 'team' | 'project'  → [model, owner column, task model]
    private function scope(string $kind): array
    {
        return match ($kind) {
            'team' => [MsTeamTaskStatus::class, 'team_id', TrTeamTask::class, MsTeam::class],
            'project' => [MsProjectTaskStatus::class, 'project_id', TrProjectTask::class, MsProject::class],
            default => abort(404),
        };
    }

    public function taskStatuses(string $kind, string $ownerId)
    {
        [$model, $col, $taskModel, $ownerModel] = $this->scope($kind);
        $ownerModel::where($col, $ownerId)->firstOrFail();

        $usage = $taskModel::where($col, $ownerId)->where('status', 'A')
            ->selectRaw('status_id, count(*) as c')->groupBy('status_id')->pluck('c', 'status_id');

        return response()->json(
            $model::where($col, $ownerId)->orderBy('sort_order')->orderBy('status_name')->get()->map(fn ($s) => [
                'status_id' => $s->status_id,
                'status_name' => $s->status_name,
                'color' => $s->color,
                'sort_order' => $s->sort_order,
                'active' => $s->status === 'A',
                'tasks' => (int) ($usage[$s->status_id] ?? 0),
            ])->values()
        );
    }

    public function storeTaskStatus(Request $request, string $kind, string $ownerId)
    {
        [$model, $col, , $ownerModel] = $this->scope($kind);
        $ownerModel::where($col, $ownerId)->firstOrFail();

        $data = $this->validated($request, true);
        $name = trim($data['status_name']);
        $id = $this->makeId($name, $data['status_id'] ?? null);

        $existing = $model::where($col, $ownerId)->where('status_id', $id)->first();
        abort_if($existing && $existing->status === 'A', 422, "Status ID \"{$id}\" already exists.");

        $values = [
            'status_name' => $name,
            'color' => $data['color'] ?? '#6366F1',
            'sort_order' => $data['sort_order'] ?? ((int) $model::where($col, $ownerId)->where('status', 'A')->max('sort_order') + 1),
            'status' => 'A',
        ];

        // A previously deleted row with the same id is revived, as the board does.
        if ($existing) {
            $model::where($col, $ownerId)->where('status_id', $id)
                ->update($values + ['updated_by' => Auth::user()->username, 'updated_at' => now()]);
        } else {
            $model::create($values + [$col => $ownerId, 'status_id' => $id,
                'created_by' => Auth::user()->username, 'created_at' => now()]);
        }

        return response()->json(['success' => true, 'message' => 'Status added.']);
    }

    public function updateTaskStatus(Request $request, string $kind, string $ownerId, string $statusId)
    {
        [$model, $col] = $this->scope($kind);
        $data = $this->validated($request, false);

        $row = $model::where($col, $ownerId)->where('status_id', $statusId)->firstOrFail();
        $model::where($col, $ownerId)->where('status_id', $statusId)->update([
            'status_name' => trim($data['status_name']),
            'color' => $data['color'] ?? $row->color,
            'sort_order' => $data['sort_order'] ?? $row->sort_order,
            'updated_by' => Auth::user()->username,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Status updated.']);
    }

    public function toggleTaskStatus(string $kind, string $ownerId, string $statusId)
    {
        [$model, $col, $taskModel] = $this->scope($kind);
        $row = $model::where($col, $ownerId)->where('status_id', $statusId)->firstOrFail();
        $query = $model::where($col, $ownerId)->where('status_id', $statusId);

        if ($row->status === 'A') {
            $used = $taskModel::where($col, $ownerId)->where('status_id', $statusId)->where('status', 'A')->count();
            abort_if($used > 0, 422, "{$used} active task(s) are still in this status. Move them first.");
            $query->update(['status' => 'X', 'deleted_by' => Auth::user()->username, 'deleted_at' => now()]);
            $msg = 'Status deleted.';
        } else {
            $query->update(['status' => 'A', 'deleted_by' => null, 'deleted_at' => null,
                'updated_by' => Auth::user()->username, 'updated_at' => now()]);
            $msg = 'Status restored.';
        }

        return response()->json(['success' => true, 'message' => $msg]);
    }
}
