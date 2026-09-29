<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\BuildsTaskTree;
use App\Http\Controllers\Traits\AddsTaskAssignees;
use App\Http\Controllers\Traits\HasAutonbr;
use App\Http\Controllers\Traits\RequiresTaskAccess;
use App\Http\Controllers\Traits\ManagesTaskCover;
use App\Http\Controllers\Traits\TagsCompleteTasks;
use App\Models\MsTeam;
use App\Models\MsTeamTaskStatus;
use App\Models\MsTaskTag;
use App\Models\TrTeamTask;
use App\Models\TrTeamTaskAssignee;
use App\Models\TrTeamTaskTag;
use App\Models\User;
use App\Services\PmActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

// A Team's own recursive Task tree — independent of Project (a Project
// keeps its own separate tree, see PmTaskController). Structural twin of
// PmTaskController against tr_team_task/ms_team_task_status/
// tr_team_task_assignee/tr_team_task_tag.
class TeamTaskController extends Controller
{
    use HasAutonbr;
    use RequiresTaskAccess;
    use BuildsTaskTree;
    use ManagesTaskCover;
    use AddsTaskAssignees;
    use TagsCompleteTasks;

    private function team(string $teamId): MsTeam
    {
        $team = MsTeam::where('team_id', $teamId)->where('status', 'A')->firstOrFail();

        abort_unless($team->isAccessibleBy(Auth::user()), 403);

        return $team;
    }

    // Recalculate nothing at the Team level — MsTeam carries no
    // progress_percent rollup (unlike MsProject); each Task's own
    // progress_percent stays independently user-set, same as today's
    // Project Task/Subtask behavior.

    // Collect a task's id plus every descendant's id, for cascade-archive.
    // Includes cancelled ('C') rows — a cancelled task is still "alive" and
    // visible, only archived ('X') ones are excluded from the tree.
    private function subtreeTaskIds(string $teamId, string $taskId)
    {
        $all = TrTeamTask::where('team_id', $teamId)->whereIn('status', ['A', 'C'])->get(['task_id', 'parent_task_id']);

        $ids = collect([$taskId]);
        $frontier = collect([$taskId]);

        while ($frontier->isNotEmpty()) {
            $children = $all->whereIn('parent_task_id', $frontier)->pluck('task_id');
            $ids = $ids->merge($children);
            $frontier = $children;
        }

        return $ids->unique()->values();
    }

    // Tags share the same "master" list as Project Tasks (ms_task_tag).
    private function syncTaskTags(string $taskId, array $tagNames, string $username, $now): void
    {
        TrTeamTaskTag::where('task_id', $taskId)->update(['status' => 'X']);

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

            $existingLink = TrTeamTaskTag::where('task_id', $taskId)->where('tag_id', $tagId)->first();
            if ($existingLink) {
                $existingLink->update(['status' => 'A']);
            } else {
                TrTeamTaskTag::create(['task_id' => $taskId, 'tag_id' => $tagId, 'status' => 'A']);
            }
        }
    }

    // What a Team Task looks like to a human, for the before/after activity diff.
    private function taskSnapshot(TrTeamTask $task): array
    {
        $statusName = MsTeamTaskStatus::where('team_id', $task->team_id)->where('status_id', $task->status_id)->value('status_name');
        $tagIds = TrTeamTaskTag::where('task_id', $task->task_id)->where('status', 'A')->pluck('tag_id');

        return [
            'task_name' => $task->task_name,
            'task_description' => $task->task_description,
            'start_date' => PmActivityLogger::formatDate($task->start_date),
            'end_date' => PmActivityLogger::formatDate($task->end_date),
            'status' => $statusName ?? $task->status_id,
            'progress' => PmActivityLogger::formatPercent($task->progress_percent),
            'assignees' => PmActivityLogger::userNames(TrTeamTaskAssignee::where('task_id', $task->task_id)->where('status', 'A')->pluck('username')),
            'tags' => MsTaskTag::whereIn('tag_id', $tagIds)->pluck('tag_name')->all(),
        ];
    }

    private const TASK_LABELS = [
        'task_name' => 'Name',
        'task_description' => 'Description',
        'start_date' => 'Start date',
        'end_date' => 'End date',
        'status' => 'Status',
        'progress' => 'Progress',
        'assignees' => 'PIC',
        'tags' => 'Tags',
    ];

    private function logTask(TrTeamTask $task, string $action, string $description, array $changes = [], ?string $by = null): void
    {
        PmActivityLogger::log('TEAM', $task->team_id, $task->task_id, $action, $description, $changes, $by);
    }

    private function noun(TrTeamTask $task): string
    {
        return $task->parent_task_id ? 'subtask' : 'task';
    }

    // Same wording rules as PmTaskController::logTaskUpdate().
    private function logTaskUpdate(TrTeamTask $task, array $before): void
    {
        $changes = PmActivityLogger::diff($before, $this->taskSnapshot($task->fresh()), self::TASK_LABELS, ['task_description']);
        if (!$changes) {
            return;
        }

        $noun = $this->noun($task);
        [$action, $text] = ['updated', "updated the {$noun}"];
        if (count($changes) === 1 && $changes[0]['label'] === 'Progress') {
            if ($changes[0]['to'] === '100%') {
                [$action, $text] = ['completed', "marked the {$noun} as done"];
            } elseif ($changes[0]['from'] === '100%') {
                [$action, $text] = ['reopened', "reopened the {$noun}"];
            }
        } elseif (count($changes) === 1 && $changes[0]['label'] === 'Status') {
            [$action, $text] = ['status', "moved the {$noun}"];
        }

        $this->logTask($task, $action, $text, $changes);
    }

    // Board header → History: the Team itself (members, edits) plus every
    // one of its Tasks, chat and files included, newest first.
    public function history(string $teamId)
    {
        $this->team($teamId);

        $tasks = TrTeamTask::where('team_id', $teamId)->get(['task_id', 'parent_task_id', 'task_name'])
            ->mapWithKeys(fn ($t) => [$t->task_id => ['name' => $t->task_name, 'parent' => (bool) $t->parent_task_id]])
            ->all();

        // 'TEAM' pulls the Team's own Message thread (and its files) in too.
        return response()->json(['items' => PmActivityLogger::feed('TEAM', $teamId, $tasks, 'TTK', true, 'TEAM')]);
    }

    // Task Detail → Activity tab: this task and everything under it.
    public function activity(string $teamId, string $taskId)
    {
        $this->team($teamId);
        TrTeamTask::where('team_id', $teamId)->where('task_id', $taskId)->firstOrFail();

        $all = TrTeamTask::where('team_id', $teamId)->get(['task_id', 'parent_task_id', 'task_name']);
        $subtree = PmActivityLogger::subtree($all, $taskId);

        $tasks = $all->whereIn('task_id', $subtree)
            ->mapWithKeys(fn ($t) => [$t->task_id => ['name' => $t->task_name, 'parent' => $t->task_id !== $taskId]])
            ->all();

        return response()->json(['items' => PmActivityLogger::feed('TEAM', $teamId, $tasks, 'TTK', false)]);
    }

    public function tags(string $teamId)
    {
        $this->team($teamId);

        return response()->json(MsTaskTag::where('status', 'A')->orderBy('tag_name')->get(['tag_id', 'tag_name', 'color']));
    }

    // Re-sync the auto-managed "Complete" tag across the whole Team after
    // anything that can change a task's completion (progress set, a
    // subtask added/cancelled/archived) — see TagsCompleteTasks. The
    // task's Kanban column is left alone, so it works whether or not the
    // Team has a "Done" column.
    private function refreshCompleteTags(string $teamId, ?Collection $tasks = null): void
    {
        $this->syncCompleteTags(
            $tasks ?? TrTeamTask::where('team_id', $teamId)->whereIn('status', ['A', 'C'])->get(),
            TrTeamTaskTag::class,
            fn ($task, $added) => $this->logTask($task, 'tags', $added
                ? "tagged the {$this->noun($task)} Complete automatically (it reached 100%)"
                : "removed the Complete tag automatically (the {$this->noun($task)} is no longer at 100%)", [], 'system')
        );
    }

    public function boardData(string $teamId)
    {
        $team = $this->team($teamId);

        $statuses = MsTeamTaskStatus::where('team_id', $teamId)->where('status', 'A')->orderBy('sort_order')->get();

        // 'C' (cancelled) tasks stay in the board/tree — only 'X' (archived)
        // is actually excluded. The frontend reads `status` to grey a
        // cancelled row out and leave it out of completion-percent math.
        $tasks = TrTeamTask::where('team_id', $teamId)->whereIn('status', ['A', 'C'])->get();

        // Self-heal on every load, not just reactively on the mutations that
        // call it directly (store()/update()/cancel()/destroy()) — a task
        // can read as 100% without ever going through one of those (e.g.
        // it was already fully done before this behavior existed). Runs
        // before the tag query below, so the board already shows it.
        $this->refreshCompleteTags($teamId, $tasks);

        $assignees = TrTeamTaskAssignee::whereIn('task_id', $tasks->pluck('task_id'))
            ->where('status', 'A')
            ->get()
            ->groupBy('task_id');

        $taskTags = TrTeamTaskTag::where('status', 'A')
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

        [$fileCounts, $commentCounts] = $this->taskCardCounts('TTK', $tasks->pluck('task_id'));

        $flat = $tasks->map(function ($t) use ($assignees, $taskTags, $tagMaster, $withPeople, $fileCounts, $commentCounts) {
            return [
                'task_id' => $t->task_id,
                // Hashids-encoded ms id (not the task_id business key) — the
                // /task/{eid} deep-link URL, same convention as Projects.
                'eid' => Hashids::encode($t->id),
                'cover_url' => self::coverUrl($t->cover_attachment_id),
                'parent_task_id' => $t->parent_task_id,
                'task_name' => $t->task_name,
                'task_description' => $t->task_description,
                'start_date' => optional($t->start_date)->toDateString(),
                'end_date' => optional($t->end_date)->toDateString(),
                'status_id' => $t->status_id,
                'status' => $t->status,
                'progress_percent' => (float) $t->progress_percent,
                'file_count' => (int) ($fileCounts[$t->task_id] ?? 0),
                'comment_count' => (int) ($commentCounts[$t->task_id] ?? 0),
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

    // Deep-link into a specific Task/Subtask's detail modal — mirrors
    // PmProjectController::show()'s /projects/{eid} convention: a
    // Hashids-encoded numeric id (tr_team_task.id, not the task_id business
    // key) in the URL, pre-opening the modal on the Team's own Task board
    // (projects.blade.php's teamId-scoped view) rather than a separate page.
    public function show(string $eid)
    {
        $id = Hashids::decode($eid)[0] ?? null;
        abort_if(! $id, 404);

        $task = TrTeamTask::whereIn('status', ['A', 'C'])->findOrFail($id);
        $this->team($task->team_id);

        return view('pages.projectmanagement.projects', [
            'initialTab' => 'kanban',
            'canCreateProject' => false,
            'openTeamId' => $task->team_id,
            'openTaskEid' => $eid,
        ]);
    }

    // /team-chat/{eid} — deep link (bell notifications) straight into a
    // Team board's Message tab. {eid} = Hashids of ms_team.id.
    public function chat(string $eid)
    {
        $id = Hashids::decode($eid)[0] ?? null;
        abort_if(! $id, 404);

        $team = MsTeam::where('status', 'A')->findOrFail($id);
        $this->team($team->team_id);

        return view('pages.projectmanagement.projects', [
            'initialTab' => 'message',
            'canCreateProject' => false,
            'openTeamId' => $team->team_id,
        ]);
    }

    public function store(Request $request, string $teamId)
    {
        $this->assertCanEditTasks();
        $team = $this->team($teamId);

        $request->validate([
            'task_name' => ['required', 'string', 'max:255'],
            'task_description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'parent_task_id' => ['nullable', 'string', 'exists:pgsql5.tr_team_task,task_id'],
            'status_id' => ['nullable', 'string'],
            'assignees' => ['nullable', 'array'],
            'assignees.*' => ['string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
        ]);

        if ($request->parent_task_id) {
            abort_unless(
                TrTeamTask::where('team_id', $teamId)->where('task_id', $request->parent_task_id)->exists(),
                422,
                'Parent task must belong to the same team.'
            );
        }

        // A Team Task's assignees must already be signed members of that Team.
        $memberUsernames = $team->memberUsers()->pluck('username')->map(fn ($u) => strtolower(trim($u)));
        abort_unless(
            collect($request->input('assignees', []))->every(fn ($u) => $memberUsernames->contains(strtolower(trim($u)))),
            422,
            'Assignee must be a member of the Team.'
        );

        $username = Auth::user()->username;
        $now = now();

        $auto = $this->nextAutonbr('TTK', (int) $now->year, $now->format('m'), $username, 'Team Task');
        $taskId = 'TTK' . substr((string) $now->year, 2) . $now->format('m') . sprintf('%04d', $auto['next']);

        $defaultStatus = $request->status_id
            ?? (MsTeamTaskStatus::where('team_id', $teamId)->where('status_id', 'TODO')->where('status', 'A')->exists() ? 'TODO' : null);

        DB::connection('pgsql5')->transaction(function () use ($request, $teamId, $taskId, $username, $now, $defaultStatus) {
            TrTeamTask::create([
                'task_id' => $taskId,
                'team_id' => $teamId,
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
                TrTeamTaskAssignee::create([
                    'task_id' => $taskId,
                    'username' => $assigneeUsername,
                    'assigned_by' => $username,
                    'assigned_at' => $now,
                    'status' => 'A',
                ]);
            }

            $this->syncTaskTags($taskId, $request->input('tags', []), $username, $now);
        });

        $task = TrTeamTask::where('task_id', $taskId)->first();
        $this->logTask($task, 'created', $task->parent_task_id ? 'added the subtask' : 'created the task',
            PmActivityLogger::diff([], $this->taskSnapshot($task), array_diff_key(self::TASK_LABELS, array_flip(['task_name', 'task_description', 'progress'])), ['task_description']));

        // A new 0% subtask un-completes its parent.
        $this->refreshCompleteTags($teamId);

        return response()->json(['success' => true, 'message' => 'Task created successfully', 'task_id' => $taskId]);
    }

    public function update(Request $request, string $teamId, string $taskId)
    {
        $this->assertCanEditTasks();
        $team = $this->team($teamId);
        $task = TrTeamTask::where('team_id', $teamId)->where('task_id', $taskId)->firstOrFail();

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

        if ($request->has('assignees')) {
            $memberUsernames = $team->memberUsers()->pluck('username')->map(fn ($u) => strtolower(trim($u)));
            abort_unless(
                collect($request->input('assignees', []))->every(fn ($u) => $memberUsernames->contains(strtolower(trim($u)))),
                422,
                'Assignee must be a member of the Team.'
            );
        }

        $username = Auth::user()->username;
        $now = now();
        $before = $this->taskSnapshot($task);

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
                TrTeamTaskAssignee::where('task_id', $task->task_id)->update(['status' => 'X']);

                foreach ($request->input('assignees', []) as $assigneeUsername) {
                    $existing = TrTeamTaskAssignee::where('task_id', $task->task_id)
                        ->where('username', $assigneeUsername)
                        ->first();

                    if ($existing) {
                        $existing->update(['status' => 'A']);
                    } else {
                        TrTeamTaskAssignee::create([
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

        $this->logTaskUpdate($task, $before);

        // Covers both: this task's own progress_percent just changed (a
        // leaf, via the subtask checkbox toggle), and this task is a
        // Subtask whose parent's completion% just shifted because of it.
        // Runs after the tag sync above, so a form that re-posts "Complete"
        // on a task that's no longer at 100% still ends up untagged.
        $this->refreshCompleteTags($teamId);

        return response()->json(['success' => true, 'message' => 'Task updated successfully']);
    }

    // Drag-and-drop status change on the Team's own Task Kanban.
    public function updateStatus(Request $request, string $teamId, string $taskId)
    {
        $this->assertCanEditTasks();
        $this->team($teamId);
        $task = TrTeamTask::where('team_id', $teamId)->where('task_id', $taskId)->firstOrFail();

        $request->validate(['status_id' => ['required', 'string']]);

        $before = $this->taskSnapshot($task);
        $task->update(['status_id' => $request->status_id, 'updated_by' => Auth::user()->username, 'updated_at' => now()]);
        $this->logTaskUpdate($task, $before);

        return response()->json(['success' => true]);
    }

    // "By Spreadsheet" drag-and-drop: re-nest a Task (and its whole
    // subtree) under another Task of this Team, or lift it back to the top
    // level — parent_task_id empty, landing in status_id's group.
    public function reparent(Request $request, string $teamId, string $taskId)
    {
        $this->assertCanEditTasks();
        $this->team($teamId);
        $task = $this->liveTask($teamId, $taskId);

        $request->validate([
            'parent_task_id' => ['nullable', 'string'],
            'status_id' => ['nullable', 'string'],
        ]);

        $parentId = $request->input('parent_task_id') ?: null;
        $parent = $parentId ? $this->liveTask($teamId, $parentId) : null;
        abort_if($parent && $this->subtreeTaskIds($teamId, $taskId)->contains($parentId), 422, 'A task cannot be moved under itself or one of its own subtasks.');

        $values = ['parent_task_id' => $parentId];
        if (!$parentId && $request->filled('status_id')) {
            abort_unless(MsTeamTaskStatus::where('team_id', $teamId)->where('status_id', $request->status_id)->where('status', 'A')->exists(), 422, 'Unknown status.');
            $values['status_id'] = $request->status_id;
        }

        $oldParentName = $task->parent_task_id ? TrTeamTask::where('task_id', $task->parent_task_id)->value('task_name') : null;
        $before = ['parent' => $oldParentName ?? 'Top level', 'status' => $this->taskSnapshot($task)['status']];
        $noun = $this->noun($task);

        $task->update($values + ['updated_by' => Auth::user()->username, 'updated_at' => now()]);

        $changes = PmActivityLogger::diff($before, ['parent' => $parent?->task_name ?? 'Top level', 'status' => $this->taskSnapshot($task->fresh())['status']], ['parent' => 'Parent', 'status' => 'Status']);
        if (!$changes) {
            return response()->json(['success' => true, 'changed' => false]);
        }

        $this->logTask($task, 'moved', $parent ? "moved the {$noun} under \"{$parent->task_name}\"" : "moved the {$noun} to the top level", $changes);

        // Both the old and the new parent's completion% can shift.
        $this->refreshCompleteTags($teamId);

        return response()->json(['success' => true, 'changed' => true]);
    }

    // Detail header → "+" next to the PIC avatars — same as
    // PmTaskController::addAssignees(), limited to this Team's members.
    public function addAssignees(Request $request, string $teamId, string $taskId)
    {
        $this->assertCanEditTasks();
        $team = $this->team($teamId);
        $task = $this->liveTask($teamId, $taskId);

        $request->validate(['usernames' => ['required', 'array', 'min:1'], 'usernames.*' => ['string']]);

        $members = $team->memberUsers()->pluck('username')->map(fn ($u) => strtolower(trim($u)));
        $usernames = collect($request->input('usernames'))->map(fn ($u) => trim($u))->filter()->unique();
        abort_unless($usernames->every(fn ($u) => $members->contains(strtolower($u))), 422, 'Assignee must be a member of the Team.');

        $before = $this->taskSnapshot($task);
        $this->activateAssignees(TrTeamTaskAssignee::class, $task->task_id, $usernames);
        $this->logTaskUpdate($task, $before);

        return response()->json(['success' => true, 'message' => 'People added to the task.']);
    }

    // Detail header → Cover: add/replace (POST) or remove (DELETE).
    public function uploadCover(Request $request, string $teamId, string $taskId)
    {
        $this->assertCanEditTasks();
        $this->team($teamId);

        return $this->saveCover($request, $this->liveTask($teamId, $taskId), 'TTKCOVER');
    }

    public function destroyCover(string $teamId, string $taskId)
    {
        $this->assertCanEditTasks();
        $this->team($teamId);

        return $this->removeCover($this->liveTask($teamId, $taskId));
    }

    private function liveTask(string $teamId, string $taskId): TrTeamTask
    {
        return TrTeamTask::where('team_id', $teamId)->where('task_id', $taskId)->whereIn('status', ['A', 'C'])->firstOrFail();
    }

    // Toggle a single task's 'C' (cancelled) flag — distinct from status_id
    // (its Kanban column) and from destroy()'s 'X' (archived, which hides
    // the whole subtree from the board entirely). A cancelled task stays
    // visible everywhere, just read as void by the frontend and left out of
    // its parent's completion-percent math. Does not cascade to children —
    // each task/subtask is cancelled independently.
    public function cancel(string $teamId, string $taskId)
    {
        $this->assertCanEditTasks();
        $this->team($teamId);
        $task = TrTeamTask::where('team_id', $teamId)->where('task_id', $taskId)->firstOrFail();

        abort_if($task->status === 'X', 404);

        $task->update([
            'status' => $task->status === 'C' ? 'A' : 'C',
            'updated_by' => Auth::user()->username,
            'updated_at' => now(),
        ]);

        $this->logTask($task, $task->status === 'C' ? 'cancelled' : 'restored',
            ($task->status === 'C' ? 'cancelled the ' : 'restored the ') . $this->noun($task));

        // Cancelling a Subtask drops it out of its parent's completion math
        // (see class comment above) — that alone can push the parent to
        // 100% (or, restoring one, back below), so recheck.
        $this->refreshCompleteTags($teamId);

        return response()->json(['success' => true, 'cancelled' => $task->status === 'C']);
    }

    public function destroy(string $teamId, string $taskId)
    {
        $this->assertCanEditTasks();
        $this->team($teamId);
        $task = TrTeamTask::where('team_id', $teamId)->where('task_id', $taskId)->firstOrFail();

        $now = now();
        $username = Auth::user()->username;

        // "Archive" hides the Task and its whole subtree (if any) from
        // every view — a status column is never a required destination, so
        // this works the same regardless of depth or which/whether a status
        // column exists.
        $ids = $this->subtreeTaskIds($teamId, $taskId);

        TrTeamTask::where('team_id', $teamId)->whereIn('task_id', $ids)
            ->update(['status' => 'X', 'updated_by' => $username, 'updated_at' => $now]);
        TrTeamTaskAssignee::whereIn('task_id', $ids)->update(['status' => 'X']);

        $nested = $ids->count() - 1;
        $this->logTask($task, 'archived', "archived the {$this->noun($task)}" . ($nested ? " (and {$nested} subtask" . ($nested > 1 ? 's' : '') . ')' : ''));

        // Archiving a Subtask drops it out of its parent's completion math
        // (boardData() only ever sees 'A'/'C' rows) — same as cancelling
        // one, that alone can complete the parent.
        $this->refreshCompleteTags($teamId);

        return response()->json(['success' => true, 'message' => 'Task archived successfully']);
    }

    // Per-team custom Task-board status columns ("+ Add status").
    public function storeStatus(Request $request, string $teamId)
    {
        $this->assertCanEditTasks();
        $this->team($teamId);

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
            : (int) MsTeamTaskStatus::where('team_id', $teamId)->where('status', 'A')->max('sort_order') + 1;

        // A previously deleted (status 'X') row with the same id is revived
        // instead of silently returned as-is (it would never reappear).
        $status = MsTeamTaskStatus::where('team_id', $teamId)->where('status_id', $statusId)->first();
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
            $status = MsTeamTaskStatus::create($values + [
                'team_id' => $teamId,
                'status_id' => $statusId,
                'created_by' => Auth::user()->username,
                'created_at' => now(),
            ]);
        }

        PmActivityLogger::log('TEAM', $teamId, null, 'status_column', "added the status column \"{$statusName}\"");

        return response()->json(['success' => true, 'status' => $status]);
    }

    // Rename/recolor a status column — status_id itself never changes, so
    // Tasks already sitting in it keep pointing at the same row.
    public function updateStatusColumn(Request $request, string $teamId, string $statusId)
    {
        $this->assertCanEditTasks();
        $this->team($teamId);

        $request->validate([
            'status_name' => ['required', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $status = MsTeamTaskStatus::where('team_id', $teamId)->where('status_id', $statusId)->firstOrFail();
        $before = ['status_name' => $status->status_name, 'color' => $status->color, 'sort_order' => $status->sort_order];

        $status->update([
            'status_name' => trim($request->status_name),
            'color' => $request->input('color', $status->color),
            'sort_order' => (Auth::user()->isPrimaryAdmin() && $request->filled('sort_order')) ? (int) $request->sort_order : $status->sort_order,
            'updated_by' => Auth::user()->username,
            'updated_at' => now(),
        ]);

        $changes = PmActivityLogger::diff($before, $status->only(['status_name', 'color', 'sort_order']), ['status_name' => 'Name', 'color' => 'Color', 'sort_order' => 'Order']);
        if ($changes) {
            PmActivityLogger::log('TEAM', $teamId, null, 'status_column', "edited the status column \"{$status->status_name}\"", $changes);
        }

        return response()->json(['success' => true, 'status' => $status]);
    }

    // Every default and custom status is equally deletable — blocked only
    // while a Task is still sitting in it, so a card is never silently
    // orphaned onto a column that no longer exists.
    public function destroyStatusColumn(string $teamId, string $statusId)
    {
        $this->assertCanEditTasks();
        $this->team($teamId);

        $status = MsTeamTaskStatus::where('team_id', $teamId)->where('status_id', $statusId)->firstOrFail();

        abort_if(
            TrTeamTask::where('team_id', $teamId)->where('status_id', $statusId)->where('status', 'A')->exists(),
            422,
            'Move or delete the tasks in this status first.'
        );

        $status->update(['status' => 'X', 'updated_by' => Auth::user()->username, 'updated_at' => now()]);

        PmActivityLogger::log('TEAM', $teamId, null, 'status_column', "deleted the status column \"{$status->status_name}\"");

        return response()->json(['success' => true]);
    }

    // @mention autocomplete for a Team Task's chat — eligible members of the Team.
    // Same people for a Task's chat and the Team-wide Message tab (which
    // has no task — routed without {taskId}).
    public function mentionableUsers(string $teamId, ?string $taskId = null)
    {
        $team = $this->team($teamId);

        $users = $team->memberUsers()
            ->reject(fn ($u) => strtolower(trim($u->username)) === strtolower(Auth::user()->username))
            ->values();

        return response()->json($users->map(fn ($u) => ['username' => $u->username, 'name' => $u->name]));
    }
}
