<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsGroup;
use App\Models\MsProject;
use App\Models\TrProjectTask;
use App\Models\TrProjectTaskAssignee;
use App\Models\TrProjectTaskDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PmTaskDetailController extends Controller
{
    use HasAutonbr;

    private function task(string $projectId, string $taskId): TrProjectTask
    {
        $project = MsProject::where('project_id', $projectId)->firstOrFail();
        $group = MsGroup::where('group_id', $project->group_id)->first();
        $eligible = $group?->eligibleUsers()->pluck('username')->map(fn ($u) => strtolower(trim($u))) ?? collect();

        abort_unless(
            $eligible->contains(strtolower(Auth::user()->username)) || Auth::user()->isAdmin(),
            403
        );

        return TrProjectTask::where('project_id', $projectId)->where('task_id', $taskId)->firstOrFail();
    }

    public function store(Request $request, string $projectId, string $taskId)
    {
        $task = $this->task($projectId, $taskId);

        $request->validate([
            'subtask_name' => ['required', 'string', 'max:255'],
            'subtask_description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'assignees' => ['nullable', 'array'],
            'assignees.*' => ['string'],
        ]);

        $username = Auth::user()->username;
        $now = now();

        $auto = $this->nextAutonbr('SUB', (int) $now->year, $now->format('m'), $username, 'Project Subtask');
        $taskDetailId = 'SUB' . substr((string) $now->year, 2) . $now->format('m') . sprintf('%04d', $auto['next']);

        DB::connection('pgsql5')->transaction(function () use ($request, $task, $taskDetailId, $username, $now) {
            TrProjectTaskDetail::create([
                'task_detail_id' => $taskDetailId,
                'task_id' => $task->task_id,
                'subtask_name' => $request->subtask_name,
                'subtask_description' => $request->subtask_description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status_id' => null,
                'progress_percent' => 0,
                'status' => 'A',
                'created_by' => $username,
                'created_at' => $now,
            ]);

            foreach ($request->input('assignees', []) as $assigneeUsername) {
                TrProjectTaskAssignee::create([
                    'task_id' => $task->task_id,
                    'task_detail_id' => $taskDetailId,
                    'username' => $assigneeUsername,
                    'assigned_by' => $username,
                    'assigned_at' => $now,
                    'status' => 'A',
                ]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Subtask created successfully', 'task_detail_id' => $taskDetailId]);
    }

    public function update(Request $request, string $projectId, string $taskId, string $taskDetailId)
    {
        $this->task($projectId, $taskId);
        $subtask = TrProjectTaskDetail::where('task_id', $taskId)->where('task_detail_id', $taskDetailId)->firstOrFail();

        $request->validate([
            'subtask_name' => ['required', 'string', 'max:255'],
            'subtask_description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'progress_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status_id' => ['nullable', 'string'],
            'assignees' => ['nullable', 'array'],
            'assignees.*' => ['string'],
        ]);

        $username = Auth::user()->username;
        $now = now();

        DB::connection('pgsql5')->transaction(function () use ($request, $subtask, $username, $now) {
            $subtask->update([
                'subtask_name' => $request->subtask_name,
                'subtask_description' => $request->subtask_description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'progress_percent' => $request->input('progress_percent', $subtask->progress_percent),
                'status_id' => $request->input('status_id', $subtask->status_id),
                'updated_by' => $username,
                'updated_at' => $now,
            ]);

            if ($request->has('assignees')) {
                TrProjectTaskAssignee::where('task_detail_id', $subtask->task_detail_id)->update(['status' => 'X']);

                foreach ($request->input('assignees', []) as $assigneeUsername) {
                    $existing = TrProjectTaskAssignee::where('task_detail_id', $subtask->task_detail_id)
                        ->where('username', $assigneeUsername)
                        ->first();

                    if ($existing) {
                        $existing->update(['status' => 'A']);
                    } else {
                        TrProjectTaskAssignee::create([
                            'task_id' => $subtask->task_id,
                            'task_detail_id' => $subtask->task_detail_id,
                            'username' => $assigneeUsername,
                            'assigned_by' => $username,
                            'assigned_at' => $now,
                            'status' => 'A',
                        ]);
                    }
                }
            }
        });

        return response()->json(['success' => true, 'message' => 'Subtask updated successfully']);
    }

    public function updateStatus(Request $request, string $projectId, string $taskId, string $taskDetailId)
    {
        $this->task($projectId, $taskId);
        $subtask = TrProjectTaskDetail::where('task_id', $taskId)->where('task_detail_id', $taskDetailId)->firstOrFail();

        $request->validate(['status_id' => ['required', 'string']]);

        $subtask->update(['status_id' => $request->status_id, 'updated_by' => Auth::user()->username, 'updated_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function destroy(string $projectId, string $taskId, string $taskDetailId)
    {
        $this->task($projectId, $taskId);
        $subtask = TrProjectTaskDetail::where('task_id', $taskId)->where('task_detail_id', $taskDetailId)->firstOrFail();

        $subtask->update(['status' => 'X', 'updated_by' => Auth::user()->username, 'updated_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Subtask archived successfully']);
    }
}
