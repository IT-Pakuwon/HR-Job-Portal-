<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsGroup;
use App\Models\MsProject;
use App\Models\MsTaskStatus;
use App\Models\TrProjectTask;
use App\Models\TrProjectTaskAssignee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PmTaskController extends Controller
{
    use HasAutonbr;

    private function project(string $projectId): MsProject
    {
        $project = MsProject::where('project_id', $projectId)->firstOrFail();

        $group = MsGroup::where('group_id', $project->group_id)->first();
        $eligible = $group?->eligibleUsers()->pluck('username')->map(fn ($u) => strtolower(trim($u))) ?? collect();

        abort_unless(
            $eligible->contains(strtolower(Auth::user()->username)) || Auth::user()->isAdmin(),
            403
        );

        return $project;
    }

    // Recalculate a Project's rollup progress from its (non-archived) Tasks.
    private function recalcProjectProgress(string $projectId): void
    {
        $avg = TrProjectTask::where('project_id', $projectId)->where('status', 'A')->avg('progress_percent');

        MsProject::where('project_id', $projectId)->update(['progress_percent' => $avg ?? 0]);
    }

    public function boardData(string $projectId)
    {
        $project = $this->project($projectId);

        $statuses = MsTaskStatus::where('project_id', $projectId)->where('status', 'A')->orderBy('sort_order')->get();

        $tasks = TrProjectTask::where('project_id', $projectId)->where('status', 'A')
            ->with(['subtasks' => fn ($q) => $q->where('status', 'A')])
            ->get();

        $assignees = TrProjectTaskAssignee::whereIn('task_id', $tasks->pluck('task_id'))
            ->where('status', 'A')
            ->get()
            ->groupBy(fn ($a) => $a->task_detail_id ? 'sub:' . $a->task_detail_id : 'task:' . $a->task_id);

        return response()->json([
            'statuses' => $statuses,
            'tasks' => $tasks->map(function ($t) use ($assignees) {
                return [
                    'task_id' => $t->task_id,
                    'task_name' => $t->task_name,
                    'task_description' => $t->task_description,
                    'start_date' => optional($t->start_date)->toDateString(),
                    'end_date' => optional($t->end_date)->toDateString(),
                    'status_id' => $t->status_id,
                    'progress_percent' => (float) $t->progress_percent,
                    'assignees' => ($assignees->get('task:' . $t->task_id) ?? collect())->pluck('username'),
                    'subtasks' => $t->subtasks->map(fn ($s) => [
                        'task_detail_id' => $s->task_detail_id,
                        'subtask_name' => $s->subtask_name,
                        'start_date' => optional($s->start_date)->toDateString(),
                        'end_date' => optional($s->end_date)->toDateString(),
                        'status_id' => $s->status_id,
                        'progress_percent' => (float) $s->progress_percent,
                        'assignees' => ($assignees->get('sub:' . $s->task_detail_id) ?? collect())->pluck('username'),
                    ]),
                ];
            }),
        ]);
    }

    public function store(Request $request, string $projectId)
    {
        $this->project($projectId);

        $request->validate([
            'task_name' => ['required', 'string', 'max:255'],
            'task_description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'assignees' => ['nullable', 'array'],
            'assignees.*' => ['string'],
        ]);

        $username = Auth::user()->username;
        $now = now();

        $auto = $this->nextAutonbr('TSK', (int) $now->year, $now->format('m'), $username, 'Project Task');
        $taskId = 'TSK' . substr((string) $now->year, 2) . $now->format('m') . sprintf('%04d', $auto['next']);

        $defaultStatus = MsTaskStatus::where('project_id', $projectId)->where('status_id', 'TODO')->exists() ? 'TODO' : null;

        DB::connection('pgsql5')->transaction(function () use ($request, $projectId, $taskId, $username, $now, $defaultStatus) {
            TrProjectTask::create([
                'task_id' => $taskId,
                'project_id' => $projectId,
                'task_name' => $request->task_name,
                'task_description' => $request->task_description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status_id' => $defaultStatus,
                'progress_percent' => 0,
                'status' => 'A',
                'created_by' => $username,
                'created_at' => $now,
            ]);

            foreach ($request->input('assignees', []) as $assigneeUsername) {
                TrProjectTaskAssignee::create([
                    'task_id' => $taskId,
                    'task_detail_id' => null,
                    'username' => $assigneeUsername,
                    'assigned_by' => $username,
                    'assigned_at' => $now,
                    'status' => 'A',
                ]);
            }
        });

        $this->recalcProjectProgress($projectId);

        return response()->json(['success' => true, 'message' => 'Task created successfully', 'task_id' => $taskId]);
    }

    public function update(Request $request, string $projectId, string $taskId)
    {
        $this->project($projectId);
        $task = TrProjectTask::where('project_id', $projectId)->where('task_id', $taskId)->firstOrFail();

        $request->validate([
            'task_name' => ['required', 'string', 'max:255'],
            'task_description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'progress_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status_id' => ['nullable', 'string'],
            'assignees' => ['nullable', 'array'],
            'assignees.*' => ['string'],
        ]);

        $username = Auth::user()->username;
        $now = now();

        DB::connection('pgsql5')->transaction(function () use ($request, $task, $username, $now) {
            $task->update([
                'task_name' => $request->task_name,
                'task_description' => $request->task_description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'progress_percent' => $request->input('progress_percent', $task->progress_percent),
                'status_id' => $request->input('status_id', $task->status_id),
                'updated_by' => $username,
                'updated_at' => $now,
            ]);

            if ($request->has('assignees')) {
                TrProjectTaskAssignee::where('task_id', $task->task_id)->whereNull('task_detail_id')->update(['status' => 'X']);

                foreach ($request->input('assignees', []) as $assigneeUsername) {
                    $existing = TrProjectTaskAssignee::where('task_id', $task->task_id)
                        ->whereNull('task_detail_id')
                        ->where('username', $assigneeUsername)
                        ->first();

                    if ($existing) {
                        $existing->update(['status' => 'A']);
                    } else {
                        TrProjectTaskAssignee::create([
                            'task_id' => $task->task_id,
                            'task_detail_id' => null,
                            'username' => $assigneeUsername,
                            'assigned_by' => $username,
                            'assigned_at' => $now,
                            'status' => 'A',
                        ]);
                    }
                }
            }
        });

        $this->recalcProjectProgress($task->project_id);

        return response()->json(['success' => true, 'message' => 'Task updated successfully']);
    }

    // Drag-and-drop status change on the per-project Task Kanban.
    public function updateStatus(Request $request, string $projectId, string $taskId)
    {
        $this->project($projectId);
        $task = TrProjectTask::where('project_id', $projectId)->where('task_id', $taskId)->firstOrFail();

        $request->validate(['status_id' => ['required', 'string']]);

        $task->update(['status_id' => $request->status_id, 'updated_by' => Auth::user()->username, 'updated_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function destroy(string $projectId, string $taskId)
    {
        $this->project($projectId);
        $task = TrProjectTask::where('project_id', $projectId)->where('task_id', $taskId)->firstOrFail();

        $task->update(['status' => 'X', 'updated_by' => Auth::user()->username, 'updated_at' => now()]);

        $this->recalcProjectProgress($projectId);

        return response()->json(['success' => true, 'message' => 'Task archived successfully']);
    }

    // Per-project custom Task-board status columns ("+ Add status").
    public function storeStatus(Request $request, string $projectId)
    {
        $this->project($projectId);

        $request->validate(['status_name' => ['required', 'string', 'max:100']]);

        $statusId = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $request->status_name));
        $nextOrder = (int) MsTaskStatus::where('project_id', $projectId)->max('sort_order') + 1;

        $status = MsTaskStatus::firstOrCreate(
            ['project_id' => $projectId, 'status_id' => $statusId],
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

    // @mention autocomplete for a Task's chat — same eligible pool as the parent Project.
    public function mentionableUsers(string $projectId, string $taskId)
    {
        $project = $this->project($projectId);
        $group = MsGroup::where('group_id', $project->group_id)->firstOrFail();

        $users = $group->eligibleUsers()
            ->reject(fn ($u) => strtolower(trim($u->username)) === strtolower(Auth::user()->username))
            ->values();

        return response()->json($users->map(fn ($u) => ['username' => $u->username, 'name' => $u->name]));
    }
}
