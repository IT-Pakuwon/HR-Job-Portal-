<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\BuildsTaskTree;
use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsTeam;
use App\Models\MsTeamTaskStatus;
use App\Models\MsTaskTag;
use App\Models\TrTeamTask;
use App\Models\TrTeamTaskAssignee;
use App\Models\TrTeamTaskTag;
use App\Models\User;
use Illuminate\Http\Request;
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
    use BuildsTaskTree;

    private function team(string $teamId): MsTeam
    {
        $team = MsTeam::where('team_id', $teamId)->where('status', 'A')->firstOrFail();

        $eligible = $team->memberUsers()->pluck('username')->map(fn ($u) => strtolower(trim($u)));

        abort_unless(
            $eligible->contains(strtolower(Auth::user()->username))
                || Auth::user()->isAdmin()
                || Auth::user()->hasRole('PROADMINACCESS'),
            403
        );

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

    public function tags(string $teamId)
    {
        $this->team($teamId);

        return response()->json(MsTaskTag::where('status', 'A')->orderBy('tag_name')->get(['tag_id', 'tag_name', 'color']));
    }

    // A built-in, non-deletable "Archive" column every Team's Task board
    // gets — archiving a top-level Task (see destroy()) moves it here
    // instead of hiding it outright, so it stays visible/reversible (drag
    // it back out to un-archive). Idempotent: safe to call on every load.
    private function ensureArchiveStatus(string $teamId): void
    {
        MsTeamTaskStatus::firstOrCreate(
            ['team_id' => $teamId, 'status_id' => 'ARCHIVE'],
            [
                'status_name' => 'Archive',
                'color' => '#6B7280',
                'sort_order' => (int) MsTeamTaskStatus::where('team_id', $teamId)->max('sort_order') + 1,
                'status' => 'A',
                'created_by' => 'system',
                'created_at' => now(),
            ]
        );
    }

    // Whenever a Task/Subtask's own completion could have changed (its
    // progress_percent was set, one of its children was cancelled/archived,
    // etc.) — recompute whether IT now reads as "fully done" using the same
    // rule the frontend's own progress bar/badge already uses (see
    // teamTaskCard()/openTaskEntityDetail(): all non-cancelled children at
    // 100%, or its own progress_percent for a leaf), and if so auto-move it
    // into the team's "Done" column (unless it's there already, or the team
    // has no such column). Then walks up to the parent, since completing
    // this task may have just completed ITS parent too.
    private function maybeAutoCompleteToDone(?TrTeamTask $task, string $teamId, string $username, $now): void
    {
        if (! $task) {
            return;
        }

        $children = TrTeamTask::where('team_id', $teamId)
            ->where('parent_task_id', $task->task_id)
            ->whereIn('status', ['A', 'C'])
            ->get()
            ->reject(fn ($c) => $c->status === 'C');

        $isComplete = $children->isNotEmpty()
            ? $children->every(fn ($c) => (float) $c->progress_percent >= 100)
            : (float) $task->progress_percent >= 100;

        if ($isComplete && $task->status_id !== 'DONE' && $task->status === 'A') {
            $hasDoneColumn = MsTeamTaskStatus::where('team_id', $teamId)->where('status_id', 'DONE')->where('status', 'A')->exists();

            if ($hasDoneColumn) {
                $task->update(['status_id' => 'DONE', 'updated_by' => $username, 'updated_at' => $now]);
            }
        }

        if ($task->parent_task_id) {
            $this->maybeAutoCompleteToDone(
                TrTeamTask::where('team_id', $teamId)->where('task_id', $task->parent_task_id)->first(),
                $teamId,
                $username,
                $now
            );
        }
    }

    public function boardData(string $teamId)
    {
        $team = $this->team($teamId);

        $this->ensureArchiveStatus($teamId);

        // Archive always renders last regardless of sort_order — otherwise
        // a later "+ Add status" column (sort_order = current max + 1)
        // would land after it.
        $statuses = MsTeamTaskStatus::where('team_id', $teamId)->where('status', 'A')->orderBy('sort_order')->get()
            ->sortBy(fn ($s) => $s->status_id === 'ARCHIVE' ? 1 : 0)->values();

        // 'C' (cancelled) tasks stay in the board/tree — only 'X' (archived)
        // is actually excluded. The frontend reads `status` to grey a
        // cancelled row out and leave it out of completion-percent math.
        $tasks = TrTeamTask::where('team_id', $teamId)->whereIn('status', ['A', 'C'])->get();

        // Self-heal on every load, not just reactively on the mutations that
        // call this directly (update()/cancel()/destroy()) — a top-level
        // Task can read as 100% without ever going through one of those
        // (e.g. it was already fully done before this behavior existed).
        // Mutates $tasks' own model instances in place, so $flat below
        // already reflects it without a re-query.
        $tasks->whereNull('parent_task_id')->where('status', 'A')->each(
            fn ($t) => $this->maybeAutoCompleteToDone($t, $teamId, 'system', now())
        );

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

        $flat = $tasks->map(function ($t) use ($assignees, $taskTags, $tagMaster, $withPeople) {
            return [
                'task_id' => $t->task_id,
                // Hashids-encoded ms id (not the task_id business key) — the
                // /task/{eid} deep-link URL, same convention as Projects.
                'eid' => Hashids::encode($t->id),
                'parent_task_id' => $t->parent_task_id,
                'task_name' => $t->task_name,
                'task_description' => $t->task_description,
                'start_date' => optional($t->start_date)->toDateString(),
                'end_date' => optional($t->end_date)->toDateString(),
                'status_id' => $t->status_id,
                'status' => $t->status,
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

    public function store(Request $request, string $teamId)
    {
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
            ?? (MsTeamTaskStatus::where('team_id', $teamId)->where('status_id', 'TODO')->exists() ? 'TODO' : null);

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

        return response()->json(['success' => true, 'message' => 'Task created successfully', 'task_id' => $taskId]);
    }

    public function update(Request $request, string $teamId, string $taskId)
    {
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

        // Covers both: this task's own progress_percent just changed (a
        // leaf, via the subtask checkbox toggle), and this task is a
        // Subtask whose parent's completion% just shifted because of it.
        $this->maybeAutoCompleteToDone($task, $teamId, $username, $now);

        return response()->json(['success' => true, 'message' => 'Task updated successfully']);
    }

    // Drag-and-drop status change on the Team's own Task Kanban.
    public function updateStatus(Request $request, string $teamId, string $taskId)
    {
        $this->team($teamId);
        $task = TrTeamTask::where('team_id', $teamId)->where('task_id', $taskId)->firstOrFail();

        $request->validate(['status_id' => ['required', 'string']]);

        $task->update(['status_id' => $request->status_id, 'updated_by' => Auth::user()->username, 'updated_at' => now()]);

        return response()->json(['success' => true]);
    }

    // Toggle a single task's 'C' (cancelled) flag — distinct from status_id
    // (its Kanban column) and from destroy()'s 'X' (archived, which hides
    // the whole subtree from the board entirely). A cancelled task stays
    // visible everywhere, just read as void by the frontend and left out of
    // its parent's completion-percent math. Does not cascade to children —
    // each task/subtask is cancelled independently.
    public function cancel(string $teamId, string $taskId)
    {
        $this->team($teamId);
        $task = TrTeamTask::where('team_id', $teamId)->where('task_id', $taskId)->firstOrFail();

        abort_if($task->status === 'X', 404);

        $task->update([
            'status' => $task->status === 'C' ? 'A' : 'C',
            'updated_by' => Auth::user()->username,
            'updated_at' => now(),
        ]);

        // Cancelling a Subtask drops it out of its parent's completion math
        // (see class comment above) — that alone can push the parent to
        // 100%, so recheck it same as a progress toggle would.
        if ($task->parent_task_id) {
            $this->maybeAutoCompleteToDone(
                TrTeamTask::where('team_id', $teamId)->where('task_id', $task->parent_task_id)->first(),
                $teamId,
                Auth::user()->username,
                now()
            );
        }

        return response()->json(['success' => true, 'cancelled' => $task->status === 'C']);
    }

    public function destroy(string $teamId, string $taskId)
    {
        $this->team($teamId);
        $task = TrTeamTask::where('team_id', $teamId)->where('task_id', $taskId)->firstOrFail();

        $now = now();
        $username = Auth::user()->username;

        // A top-level Task lives on the Kanban board — "Archive" just
        // relocates its card to the board's own Archive column, same as any
        // other status_id change (reversible by dragging it back out). Its
        // own subtree is left untouched, unlike archiving a Subtask below.
        if ($task->parent_task_id === null) {
            $this->ensureArchiveStatus($teamId);
            $task->update(['status_id' => 'ARCHIVE', 'updated_by' => $username, 'updated_at' => $now]);

            return response()->json(['success' => true, 'message' => 'Task archived successfully']);
        }

        // A Subtask has no Kanban column of its own to move to — "Archive"
        // keeps its original meaning: hide it and its own descendants (if
        // any) from every view, same as before.
        $ids = $this->subtreeTaskIds($teamId, $taskId);

        TrTeamTask::where('team_id', $teamId)->whereIn('task_id', $ids)
            ->update(['status' => 'X', 'updated_by' => $username, 'updated_at' => $now]);
        TrTeamTaskAssignee::whereIn('task_id', $ids)->update(['status' => 'X']);

        // Archiving a Subtask drops it out of its parent's completion math
        // (boardData()/subtaskRowHtml only ever see 'A'/'C' rows) — same as
        // cancelling one, that alone can complete the parent.
        if ($task->parent_task_id) {
            $this->maybeAutoCompleteToDone(
                TrTeamTask::where('team_id', $teamId)->where('task_id', $task->parent_task_id)->first(),
                $teamId,
                $username,
                $now
            );
        }

        return response()->json(['success' => true, 'message' => 'Task archived successfully']);
    }

    // Per-team custom Task-board status columns ("+ Add status").
    public function storeStatus(Request $request, string $teamId)
    {
        $this->team($teamId);

        $request->validate(['status_name' => ['required', 'string', 'max:100']]);

        $statusId = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $request->status_name));
        $nextOrder = (int) MsTeamTaskStatus::where('team_id', $teamId)->max('sort_order') + 1;

        $status = MsTeamTaskStatus::firstOrCreate(
            ['team_id' => $teamId, 'status_id' => $statusId],
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

    // @mention autocomplete for a Team Task's chat — eligible members of the Team.
    public function mentionableUsers(string $teamId, string $taskId)
    {
        $team = $this->team($teamId);

        $users = $team->memberUsers()
            ->reject(fn ($u) => strtolower(trim($u->username)) === strtolower(Auth::user()->username))
            ->values();

        return response()->json($users->map(fn ($u) => ['username' => $u->username, 'name' => $u->name]));
    }
}
