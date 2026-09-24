<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\BuildsTaskTree;
use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsProject;
use App\Models\MsProjectTaskStatus;
use App\Models\MsTaskTag;
use App\Models\MsTeam;
use App\Models\TrProjectTask;
use App\Models\TrProjectTaskAssignee;
use App\Models\TrProjectTaskTag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PmTaskController extends Controller
{
    use HasAutonbr;
    use BuildsTaskTree;

    private function project(string $projectId): MsProject
    {
        $project = MsProject::where('project_id', $projectId)->firstOrFail();

        $eligible = $this->eligibleUsernames($project);

        abort_unless(
            $eligible->contains(strtolower(Auth::user()->username))
                || Auth::user()->isAdmin()
                || Auth::user()->hasRole('PROADMINACCESS'),
            403
        );

        return $project;
    }

    // Union of member usernames across every Team linked to the Project,
    // plus individually-picked PIC people and the creator — a Project can
    // be linked to Teams, people, or both (or only people, no Team at all).
    private function eligibleUsernames(MsProject $project)
    {
        $teamIds = $project->teams->pluck('team_id');

        return MsTeam::whereIn('team_id', $teamIds)->get()
            ->flatMap(fn ($t) => $t->memberUsers()->pluck('username'))
            ->map(fn ($u) => strtolower(trim($u)))
            ->merge($project->picUsernames())
            ->push(strtolower(trim((string) $project->created_by)))
            ->filter()
            ->unique();
    }

    // Recalculate a Project's rollup progress from its top-level
    // (non-archived) Tasks only — former Subtasks now live in the same
    // table but must stay excluded, same as before the recursive merge.
    private function recalcProjectProgress(string $projectId): void
    {
        $avg = TrProjectTask::where('project_id', $projectId)
            ->where('status', 'A')
            ->whereNull('parent_task_id')
            ->avg('progress_percent');

        MsProject::where('project_id', $projectId)->update(['progress_percent' => $avg ?? 0]);
    }

    // Collect a task's id plus every descendant's id, for cascade-archive.
    private function subtreeTaskIds(string $projectId, string $taskId)
    {
        $all = TrProjectTask::where('project_id', $projectId)->where('status', 'A')->get(['task_id', 'parent_task_id']);

        $ids = collect([$taskId]);
        $frontier = collect([$taskId]);

        while ($frontier->isNotEmpty()) {
            $children = $all->whereIn('parent_task_id', $frontier)->pluck('task_id');
            $ids = $ids->merge($children);
            $frontier = $children;
        }

        return $ids->unique()->values();
    }

    // Tags are a shared "master" list (ms_task_tag) — typing a new one on a
    // Task registers it for every other Task's picker too, exact typed text
    // preserved and matched/deduped via a normalized tag_id.
    private function syncTaskTags(string $taskId, array $tagNames, string $username, $now): void
    {
        TrProjectTaskTag::where('task_id', $taskId)->update(['status' => 'X']);

        foreach (collect($tagNames)->map(fn ($t) => trim($t))->filter()->unique(fn ($t) => strtolower($t)) as $tagName) {
            $tagId = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $tagName));
            if ($tagId === '') {
                continue;
            }

            $tag = MsTaskTag::where('tag_id', $tagId)->first();

            if ($tag) {
                if ($tag->tag_name !== $tagName) {
                    $tag->update(['tag_name' => $tagName, 'updated_by' => $username, 'updated_at' => $now]);
                }
            } else {
                MsTaskTag::create([
                    'tag_id' => $tagId,
                    'tag_name' => $tagName,
                    'color' => '#6366F1',
                    'status' => 'A',
                    'created_by' => $username,
                    'created_at' => $now,
                ]);
            }

            $existingLink = TrProjectTaskTag::where('task_id', $taskId)->where('tag_id', $tagId)->first();
            if ($existingLink) {
                $existingLink->update(['status' => 'A']);
            } else {
                TrProjectTaskTag::create(['task_id' => $taskId, 'tag_id' => $tagId, 'status' => 'A']);
            }
        }
    }

    // Master tag palette for the Task modal's Tags picker.
    public function tags(string $projectId)
    {
        $this->project($projectId);

        return response()->json(MsTaskTag::where('status', 'A')->orderBy('tag_name')->get(['tag_id', 'tag_name', 'color']));
    }

    public function boardData(string $projectId)
    {
        $project = $this->project($projectId);

        $statuses = MsProjectTaskStatus::where('project_id', $projectId)->where('status', 'A')->orderBy('sort_order')->get();

        $tasks = TrProjectTask::where('project_id', $projectId)->where('status', 'A')->get();

        $assignees = TrProjectTaskAssignee::whereIn('task_id', $tasks->pluck('task_id'))
            ->where('status', 'A')
            ->get()
            ->groupBy('task_id');

        $taskTags = TrProjectTaskTag::where('status', 'A')
            ->whereIn('task_id', $tasks->pluck('task_id'))
            ->get()
            ->groupBy('task_id');
        $tagMaster = MsTaskTag::whereIn('tag_id', $taskTags->flatten()->pluck('tag_id')->unique())
            ->get(['tag_id', 'tag_name', 'color'])
            ->keyBy('tag_id');

        $assigneeUsernames = $assignees->flatten()->pluck('username')->map(fn ($u) => strtolower(trim($u)))->unique();
        $assigneeUsers = User::whereIn(DB::raw('lower(username)'), $assigneeUsernames->all())
            ->get(['username', 'name'])
            ->keyBy(fn ($u) => strtolower(trim($u->username)));

        $withPeople = function ($rows) use ($assigneeUsers) {
            return $rows->pluck('username')->map(function ($username) use ($assigneeUsers) {
                $u = $assigneeUsers->get(strtolower(trim($username)));

                return [
                    'username' => $username,
                    'name' => $u->name ?? $username,
                    'photo_url' => $u?->profile_photo_url,
                ];
            })->values();
        };

        $flat = $tasks->map(function ($t) use ($assignees, $taskTags, $tagMaster, $withPeople) {
            return [
                'task_id' => $t->task_id,
                'parent_task_id' => $t->parent_task_id,
                'task_name' => $t->task_name,
                'task_description' => $t->task_description,
                'start_date' => optional($t->start_date)->toDateString(),
                'end_date' => optional($t->end_date)->toDateString(),
                'status_id' => $t->status_id,
                'progress_percent' => (float) $t->progress_percent,
                'created_by' => $t->created_by,
                'created_at' => optional($t->created_at)->toDateTimeString(),
                'assignees' => ($assignees->get($t->task_id) ?? collect())->pluck('username'),
                'assignee_people' => $withPeople($assignees->get($t->task_id) ?? collect()),
                'tags' => ($taskTags->get($t->task_id) ?? collect())
                    ->map(fn ($tt) => $tagMaster->get($tt->tag_id))
                    ->filter()
                    ->values(),
            ];
        });

        return response()->json([
            'statuses' => $statuses,
            'tasks' => $this->buildTaskTree($flat),
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
            'parent_task_id' => ['nullable', 'string', 'exists:pgsql5.tr_project_task,task_id'],
            'assignees' => ['nullable', 'array'],
            'assignees.*' => ['string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
        ]);

        // A parent (if given) must belong to this same Project — a task
        // can't be nested under another Project's tree.
        if ($request->parent_task_id) {
            abort_unless(
                TrProjectTask::where('project_id', $projectId)->where('task_id', $request->parent_task_id)->exists(),
                422,
                'Parent task must belong to the same project.'
            );
        }

        $username = Auth::user()->username;
        $now = now();

        $auto = $this->nextAutonbr('TSK', (int) $now->year, $now->format('m'), $username, 'Project Task');
        $taskId = 'TSK' . substr((string) $now->year, 2) . $now->format('m') . sprintf('%04d', $auto['next']);

        $defaultStatus = MsProjectTaskStatus::where('project_id', $projectId)->where('status_id', 'TODO')->where('status', 'A')->exists() ? 'TODO' : null;

        DB::connection('pgsql5')->transaction(function () use ($request, $projectId, $taskId, $username, $now, $defaultStatus) {
            TrProjectTask::create([
                'task_id' => $taskId,
                'project_id' => $projectId,
                'parent_task_id' => $request->parent_task_id,
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
                    'username' => $assigneeUsername,
                    'assigned_by' => $username,
                    'assigned_at' => $now,
                    'status' => 'A',
                ]);
            }

            $this->syncTaskTags($taskId, $request->input('tags', []), $username, $now);
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
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
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
                TrProjectTaskAssignee::where('task_id', $task->task_id)->update(['status' => 'X']);

                foreach ($request->input('assignees', []) as $assigneeUsername) {
                    $existing = TrProjectTaskAssignee::where('task_id', $task->task_id)
                        ->where('username', $assigneeUsername)
                        ->first();

                    if ($existing) {
                        $existing->update(['status' => 'A']);
                    } else {
                        TrProjectTaskAssignee::create([
                            'task_id' => $task->task_id,
                            'username' => $assigneeUsername,
                            'assigned_by' => $username,
                            'assigned_at' => $now,
                            'status' => 'A',
                        ]);
                    }
                }
            }

            if ($request->has('tags')) {
                $this->syncTaskTags($task->task_id, $request->input('tags', []), $username, $now);
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

        $now = now();
        $username = Auth::user()->username;

        // "Archive" hides the Task and its whole subtree (if any) from
        // every view — a status column is never a required destination, so
        // this works the same regardless of depth or which/whether a status
        // column exists. Same behavior as TeamTaskController::destroy().
        $ids = $this->subtreeTaskIds($projectId, $taskId);

        TrProjectTask::where('project_id', $projectId)->whereIn('task_id', $ids)
            ->update(['status' => 'X', 'updated_by' => $username, 'updated_at' => $now]);
        TrProjectTaskAssignee::whereIn('task_id', $ids)->update(['status' => 'X']);

        $this->recalcProjectProgress($projectId);

        return response()->json(['success' => true, 'message' => 'Task archived successfully']);
    }

    // "+ Add status" on a Project's Task board — this Project's own,
    // isolated status list (mirrors TeamTaskController::storeStatus()).
    public function storeStatus(Request $request, string $projectId)
    {
        $this->project($projectId);

        $request->validate([
            'status_name' => ['required', 'string', 'max:100'],
            'status_id' => ['nullable', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // A custom status_id / sort_order is only honored for admins (the
        // Status Settings panel); everyone else gets one derived from the name.
        $isAdmin = Auth::user()->isPrimaryAdmin();
        $statusName = trim($request->status_name);
        $statusId = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', ($isAdmin && $request->filled('status_id')) ? $request->status_id : $statusName));
        abort_if($statusId === '', 422, 'Status ID must contain at least one letter or number.');

        $sortOrder = ($isAdmin && $request->filled('sort_order'))
            ? (int) $request->sort_order
            : (int) MsProjectTaskStatus::where('project_id', $projectId)->where('status', 'A')->max('sort_order') + 1;

        // A previously deleted (status 'X') row with the same id is revived
        // instead of silently returned as-is (it would never reappear).
        $status = MsProjectTaskStatus::where('project_id', $projectId)->where('status_id', $statusId)->first();
        abort_if($status && $status->status === 'A', 422, "Status ID \"{$statusId}\" already exists.");

        $values = [
            'status_name' => $statusName,
            'color' => $request->input('color', '#6366F1'),
            'sort_order' => $sortOrder,
            'status' => 'A',
        ];

        if ($status) {
            $status->update($values + ['updated_by' => Auth::user()->username, 'updated_at' => now()]);
        } else {
            $status = MsProjectTaskStatus::create($values + [
                'project_id' => $projectId,
                'status_id' => $statusId,
                'created_by' => Auth::user()->username,
                'created_at' => now(),
            ]);
        }

        return response()->json(['success' => true, 'status' => $status]);
    }

    // Rename/recolor a status column — status_id itself never changes, so
    // Tasks already sitting in it keep pointing at the same row.
    public function updateStatusColumn(Request $request, string $projectId, string $statusId)
    {
        $this->project($projectId);

        $request->validate([
            'status_name' => ['required', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $status = MsProjectTaskStatus::where('project_id', $projectId)->where('status_id', $statusId)->firstOrFail();

        $status->update([
            'status_name' => trim($request->status_name),
            'color' => $request->input('color', $status->color),
            'sort_order' => (Auth::user()->isPrimaryAdmin() && $request->filled('sort_order')) ? (int) $request->sort_order : $status->sort_order,
            'updated_by' => Auth::user()->username,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'status' => $status]);
    }

    // Every default and custom status is equally deletable — blocked only
    // while a Task is still sitting in it, so a card is never silently
    // orphaned onto a column that no longer exists.
    public function destroyStatusColumn(string $projectId, string $statusId)
    {
        $this->project($projectId);

        $status = MsProjectTaskStatus::where('project_id', $projectId)->where('status_id', $statusId)->firstOrFail();

        abort_if(
            TrProjectTask::where('project_id', $projectId)->where('status_id', $statusId)->where('status', 'A')->exists(),
            422,
            'Move or delete the tasks in this status first.'
        );

        $status->update(['status' => 'X', 'updated_by' => Auth::user()->username, 'updated_at' => now()]);

        return response()->json(['success' => true]);
    }

    // @mention autocomplete for a Task's chat — same eligible pool as the
    // parent Project (union of members across every linked Team).
    public function mentionableUsers(string $projectId, string $taskId)
    {
        $project = $this->project($projectId);
        $usernames = $this->eligibleUsernames($project)
            ->reject(fn ($u) => $u === strtolower(Auth::user()->username));

        $users = User::whereIn(DB::raw('lower(username)'), $usernames->all())->get(['username', 'name']);

        return response()->json($users->map(fn ($u) => ['username' => $u->username, 'name' => $u->name]));
    }
}
