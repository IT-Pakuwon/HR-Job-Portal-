<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\BuildsTaskTree;
use App\Http\Controllers\Traits\ChecklistItems;
use App\Http\Controllers\Traits\AddsTaskAssignees;
use App\Http\Controllers\Traits\HasAutonbr;
use App\Http\Controllers\Traits\RequiresTaskAccess;
use App\Http\Controllers\Traits\ManagesTaskCover;
use App\Http\Controllers\Traits\TagsCompleteTasks;
use App\Models\MsProject;
use App\Models\MsProjectTaskStatus;
use App\Models\MsTaskTag;
use App\Models\MsTeam;
use App\Models\TrProjectTask;
use App\Models\TrProjectTaskAssignee;
use App\Models\TrProjectTaskTag;
use App\Models\TrProjectTaskTeam;
use App\Models\User;
use App\Services\PmActivityLogger;
use App\Services\PmChatAccess;
use App\Services\PrivateChatAudience;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class PmTaskController extends Controller
{
    use HasAutonbr;
    use RequiresTaskAccess;
    use BuildsTaskTree;
    use ManagesTaskCover;
    use AddsTaskAssignees;
    use TagsCompleteTasks;
    use ChecklistItems;

    // Task → Subtask → Checklist item (see ChecklistItems): this Project's
    // task_id => parent_task_id map, and the Subtask progress roll-up.
    private function parentMap(string $projectId): Collection
    {
        return $this->checklistParentMap(TrProjectTask::class, 'project_id', $projectId);
    }

    private function rollupSubtask(string $projectId, ?string $subtaskId): void
    {
        $this->rollupChecklistProgress(TrProjectTask::class, 'project_id', $projectId, $subtaskId);
    }

    private function project(string $projectId): MsProject
    {
        $project = MsProject::where('project_id', $projectId)->firstOrFail();

        abort_unless($project->isAccessibleBy(Auth::user()), 403);

        return $project;
    }

    // A Task of this Project that the current user is allowed into — a
    // locked task (or anything under one) is assignees-only.
    private function accessibleTask(string $projectId, string $taskId): TrProjectTask
    {
        $task = TrProjectTask::where('project_id', $projectId)->where('task_id', $taskId)->firstOrFail();

        abort_unless($task->isAccessibleBy(Auth::user()), 403, 'This task is private. Only its assignees can open it.');

        return $task;
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

    // Teams assigned as PIC on a Task — only Teams already linked to the
    // Project are accepted (the picker offers nothing else either).
    private function syncTaskTeams(MsProject $project, string $taskId, array $teamIds, string $username, $now): void
    {
        $allowed = $project->teams->pluck('team_id');
        $teamIds = collect($teamIds)->map(fn ($t) => trim($t))->filter()->unique();

        abort_if($teamIds->diff($allowed)->isNotEmpty(), 422, 'Only Teams linked to this project can be assigned.');

        TrProjectTaskTeam::where('task_id', $taskId)->update(['status' => 'X']);

        foreach ($teamIds as $teamId) {
            $existing = TrProjectTaskTeam::where('task_id', $taskId)->where('team_id', $teamId)->first();

            if ($existing) {
                $existing->update(['status' => 'A']);
            } else {
                TrProjectTaskTeam::create([
                    'task_id' => $taskId,
                    'team_id' => $teamId,
                    'assigned_by' => $username,
                    'assigned_at' => $now,
                    'status' => 'A',
                ]);
            }
        }
    }

    // What a Task looks like to a human, for the before/after activity diff.
    private function taskSnapshot(TrProjectTask $task): array
    {
        $statusName = MsProjectTaskStatus::where('project_id', $task->project_id)->where('status_id', $task->status_id)->value('status_name');
        $tagIds = TrProjectTaskTag::where('task_id', $task->task_id)->where('status', 'A')->pluck('tag_id');

        return [
            'task_name' => $task->task_name,
            'task_description' => $task->task_description,
            'start_date' => PmActivityLogger::formatDate($task->start_date),
            'end_date' => PmActivityLogger::formatDate($task->end_date),
            'status' => $statusName ?? $task->status_id,
            'progress' => PmActivityLogger::formatPercent($task->progress_percent),
            'assignees' => PmActivityLogger::userNames(TrProjectTaskAssignee::where('task_id', $task->task_id)->where('status', 'A')->pluck('username')),
            'teams' => PmActivityLogger::teamNames(TrProjectTaskTeam::where('task_id', $task->task_id)->where('status', 'A')->pluck('team_id')),
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
        'teams' => 'PIC Teams',
        'tags' => 'Tags',
    ];

    private function logTask(TrProjectTask $task, string $action, string $description, array $changes = [], ?string $by = null): void
    {
        PmActivityLogger::log('PROJECT', $task->project_id, $task->task_id, $action, $description, $changes, $by);
    }

    // Re-sync the auto-managed "Complete" tag across the whole Project
    // after anything that can change a task's completion (progress set, a
    // subtask added/archived) — see TagsCompleteTasks. Same as
    // TeamTaskController::refreshCompleteTags().
    private function refreshCompleteTags(string $projectId, ?Collection $tasks = null): void
    {
        $this->syncCompleteTags(
            $this->withoutChecklistItems($tasks ?? TrProjectTask::where('project_id', $projectId)->where('status', 'A')->get()),
            TrProjectTaskTag::class,
            fn ($task, $added) => $this->logTask($task, 'tags', $added
                ? "tagged the {$this->noun($task)} Complete automatically (it reached 100%)"
                : "removed the Complete tag automatically (the {$this->noun($task)} is no longer at 100%)", [], 'system')
        );
    }

    // Diff against $before and log it — nothing when nothing changed. A
    // lone progress flip to/from 100% (the subtask checkbox) reads as
    // "marked done" / "reopened" rather than a generic edit.
    private function logTaskUpdate(TrProjectTask $task, array $before): void
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

    private function noun(TrProjectTask $task): string
    {
        if (!$task->parent_task_id) {
            return 'task';
        }

        // A Subtask whose own parent has a parent is a checklist item.
        $grandparent = TrProjectTask::where('task_id', $task->parent_task_id)->value('parent_task_id');

        return $grandparent ? 'checklist item' : 'subtask';
    }

    // Task Detail → Activity tab: this task and everything under it,
    // newest first, minus anything behind a lock the viewer can't open.
    public function activity(string $projectId, string $taskId)
    {
        $this->project($projectId);
        $this->accessibleTask($projectId, $taskId);

        $all = TrProjectTask::where('project_id', $projectId)->get(['task_id', 'parent_task_id', 'task_name', 'is_locked']);
        $canOpen = TrProjectTask::accessMap($all, Auth::user());
        $subtree = PmActivityLogger::subtree($all, $taskId);

        $tasks = $all->whereIn('task_id', $subtree)->filter(fn ($t) => $canOpen[$t->task_id])
            ->mapWithKeys(fn ($t) => [$t->task_id => ['name' => $t->task_name, 'parent' => $t->task_id !== $taskId]])
            ->all();

        return response()->json(['items' => PmActivityLogger::feed('PROJECT', $projectId, $tasks, 'TSK', false)]);
    }

    // Deep link into a Project Task's detail modal — /task/{eid} (reached via
    // TeamTaskController::show()), or /project-task/{eid} from bell
    // notifications: a Hashids-encoded tr_project_task.id, opening the modal
    // on the Project's own Task board.
    public function show(string $eid)
    {
        $id = Hashids::decode($eid)[0] ?? null;
        abort_if(! $id, 404);

        $task = TrProjectTask::where('status', 'A')->findOrFail($id);
        $this->project($task->project_id);

        $user = Auth::user();
        abort_unless($task->isAccessibleBy($user), 403, 'This task is private. Only its assignees can open it.');

        return view('pages.projectmanagement.projects', [
            'initialTab' => 'kanban',
            'canCreateProject' => $user->hasRole('PROADMINACCESS'),
            'openProjectBoardId' => $task->project_id,
            // Re-encoded so it matches board-data even from an older link.
            'openTaskEid' => Hashids::encode($task->id, TrProjectTask::EID_TAG),
        ]);
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

        // Self-heal on every load (e.g. tasks already at 100% before this
        // behavior existed) — before the tag query below, so the board
        // already shows it.
        $this->refreshCompleteTags($projectId, $tasks);

        $assignees = TrProjectTaskAssignee::whereIn('task_id', $tasks->pluck('task_id'))
            ->where('status', 'A')
            ->get()
            ->groupBy('task_id');

        $taskTags = TrProjectTaskTag::where('status', 'A')
            ->whereIn('task_id', $tasks->pluck('task_id'))
            ->get()
            ->groupBy('task_id');
        $tagIds = $taskTags->flatten()->pluck('tag_id')->unique();
        $tagMaster = $tagIds->isEmpty() ? collect() : MsTaskTag::whereIn('tag_id', $tagIds)
            ->get(['tag_id', 'tag_name', 'color'])
            ->keyBy('tag_id');

        // Empty IN () lists still cost a database round trip — skip them.
        $assigneeUsernames = $assignees->flatten()->pluck('username')->map(fn ($u) => strtolower(trim($u)))->unique();
        $assigneeUsers = $assigneeUsernames->isEmpty() ? collect() : User::whereIn(DB::raw('lower(username)'), $assigneeUsernames->all())
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

        // Lock visibility: a locked task (and everything beneath it) is hidden
        // from anyone who isn't assigned. "Assigned" = direct PIC people +
        // members of PIC Teams; PROADMINACCESS always sees everything.
        $me = strtolower(trim(Auth::user()->username));
        // Assignee / Team rows are loaded once here and reused below and by
        // effectiveAssigneeMap() instead of being fetched a second time.
        $allTaskTeams = TrProjectTaskTeam::whereIn('task_id', $tasks->pluck('task_id'))->where('status', 'A')->get()->groupBy('task_id');
        $effective = TrProjectTask::effectiveAssigneeMap($tasks->pluck('task_id'), $assignees, $allTaskTeams);
        $canOpen = TrProjectTask::accessMap($tasks, Auth::user());
        $resolve = fn ($taskId) => $canOpen[$taskId];
        // A private task (or anything under one) the viewer can't open is left
        // out of the board entirely — not even a masked card.
        $tasks = $tasks->filter(fn ($t) => $canOpen[$t->task_id] ?? true);

        // Chat and files are narrower than the card: see PmChatAccess.
        $audiences = PmChatAccess::audiences('TSK', $tasks, $effective);

        // toBase(): these are groups keyed by task_id, and Eloquent's only()
        // expects models — it failed as soon as any task had a PIC Team.
        $taskTeams = $allTaskTeams->toBase()->only($tasks->pluck('task_id')->all());
        $teamIdsOnBoard = $taskTeams->flatten()->pluck('team_id')->unique();
        $teamNames = $teamIdsOnBoard->isEmpty() ? collect() : MsTeam::whereIn('team_id', $teamIdsOnBoard)->pluck('team_name', 'team_id');

        [$fileCounts, $commentCounts] = $this->taskCardCounts('TSK', $tasks->pluck('task_id'));

        $flat = $tasks->map(function ($t) use ($assignees, $taskTags, $tagMaster, $withPeople, $resolve, $effective, $me, $taskTeams, $teamNames, $fileCounts, $commentCounts, $audiences) {
            $canAccess = $resolve($t->task_id);
            $canChat = $canAccess && PmChatAccess::includes($audiences->get($t->task_id), Auth::user());

            return [
                'can_chat' => $canChat,
                // A private Task's admins / creator manage it without its chat.
                'can_assign' => $canAccess && PmChatAccess::canAssign($audiences->get($t->task_id), $t, Auth::user()),
                'chat_private' => $audiences->get($t->task_id) instanceof PrivateChatAudience,
                'is_locked' => (bool) $t->is_locked,
                'locked_by' => $t->locked_by,
                'can_access' => $canAccess,
                'cover_url' => $canAccess ? self::coverUrl($t->cover_attachment_id) : null,
                'is_assignee' => $effective->get($t->task_id)->contains($me),
                'teams' => ($taskTeams->get($t->task_id) ?? collect())
                    ->map(fn ($tt) => ['team_id' => $tt->team_id, 'team_name' => $teamNames->get($tt->team_id, $tt->team_id)])
                    ->values(),
                'eid' => Hashids::encode($t->id, TrProjectTask::EID_TAG),
                'task_id' => $t->task_id,
                'parent_task_id' => $t->parent_task_id,
                'task_name' => $t->task_name,
                'task_description' => $canAccess ? $t->task_description : null,
                'start_date' => optional($t->start_date)->toDateString(),
                'end_date' => optional($t->end_date)->toDateString(),
                'status_id' => $t->status_id,
                'progress_percent' => (float) $t->progress_percent,
                // Masked like description/tags for a locked task the viewer can't open.
                'file_count' => $canChat ? (int) ($fileCounts[$t->task_id] ?? 0) : null,
                'comment_count' => $canChat ? (int) ($commentCounts[$t->task_id] ?? 0) : null,
                'created_by' => $t->created_by,
                'created_at' => optional($t->created_at)->toDateTimeString(),
                'assignees' => ($assignees->get($t->task_id) ?? collect())->pluck('username'),
                'assignee_people' => $withPeople($assignees->get($t->task_id) ?? collect()),
                'tags' => $canAccess ? ($taskTags->get($t->task_id) ?? collect())
                    ->map(fn ($tt) => $tagMaster->get($tt->tag_id))
                    ->filter()
                    ->values() : [],
            ];
        });

        return response()->json([
            'statuses' => $statuses,
            'tasks' => $this->buildTaskTree($flat),
        ]);
    }

    public function store(Request $request, string $projectId)
    {
        $this->assertCanEditTasks();
        $project = $this->project($projectId);

        $request->validate([
            'task_name' => ['required', 'string', 'max:255'],
            'task_description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'parent_task_id' => ['nullable', 'string', 'exists:pgsql5.tr_project_task,task_id'],
            'assignees' => ['nullable', 'array'],
            'assignees.*' => ['string'],
            'team_ids' => ['nullable', 'array'],
            'team_ids.*' => ['string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
            'status_id' => ['nullable', 'string'],
        ]);

        // A parent (if given) must belong to this same Project — a task
        // can't be nested under another Project's tree.
        if ($request->parent_task_id) {
            abort_unless(
                TrProjectTask::where('project_id', $projectId)->where('task_id', $request->parent_task_id)->exists(),
                422,
                'Parent task must belong to the same project.'
            );
            $this->accessibleTask($projectId, $request->parent_task_id);

            // Task → Subtask → Checklist item: nothing nests below level 3.
            $parents = $this->parentMap($projectId);
            $this->assertCanNestUnder($parents, $request->parent_task_id);

            // A new level-3 item is just a name + checkbox — drop anything else.
            if ($this->taskDepthIn($parents, $request->parent_task_id) === self::MAX_TASK_DEPTH - 1) {
                $request->merge(['task_description' => null, 'start_date' => null, 'end_date' => null, 'assignees' => [], 'team_ids' => [], 'tags' => []]);
            }
        }

        $username = Auth::user()->username;
        $now = now();

        $auto = $this->nextAutonbr('TSK', (int) $now->year, $now->format('m'), $username, 'Project Task');
        $taskId = 'TSK' . substr((string) $now->year, 2) . $now->format('m') . sprintf('%04d', $auto['next']);

        // The column the card was added to; else the Project's own first
        // column (by sort order) — whatever columns this Project has, never
        // a hardcoded id, so a task always lands on a visible column.
        $activeStatuses = MsProjectTaskStatus::where('project_id', $projectId)->where('status', 'A')->orderBy('sort_order');
        $defaultStatus = $request->filled('status_id') && (clone $activeStatuses)->where('status_id', $request->status_id)->exists()
            ? $request->status_id
            : (clone $activeStatuses)->value('status_id');

        DB::connection('pgsql5')->transaction(function () use ($request, $project, $projectId, $taskId, $username, $now, $defaultStatus) {
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

            $this->syncTaskTeams($project, $taskId, $request->input('team_ids', []), $username, $now);
            $this->syncTaskTags($taskId, $request->input('tags', []), $username, $now);
        });

        $task = TrProjectTask::where('task_id', $taskId)->first();
        $snapshot = $this->taskSnapshot($task);
        $this->logTask($task, 'created', $task->parent_task_id ? 'added the ' . $this->noun($task) : 'created the task',
            PmActivityLogger::diff([], $snapshot, array_diff_key(self::TASK_LABELS, array_flip(['task_name', 'task_description', 'progress'])), ['task_description']));

        // A new checklist item (0%) changes its Subtask's roll-up.
        $this->rollupSubtask($projectId, $task->parent_task_id);
        $this->recalcProjectProgress($projectId);
        // A new 0% subtask un-completes its parent.
        $this->refreshCompleteTags($projectId);

        return response()->json(['success' => true, 'message' => 'Task created successfully', 'task_id' => $taskId]);
    }

    public function update(Request $request, string $projectId, string $taskId)
    {
        $this->assertCanEditTasks();
        $project = $this->project($projectId);
        $task = $this->accessibleTask($projectId, $taskId);

        // Checklist item (level 3): only its name and checkbox can change.
        if ($this->isChecklistItemIn($this->parentMap($projectId), $taskId)) {
            $request->validate([
                'task_name' => ['required', 'string', 'max:255'],
                'progress_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            ]);

            $before = $this->taskSnapshot($task);
            $task->update([
                'task_name' => $request->task_name,
                // A checkbox: anything below 100 is "not done".
                'progress_percent' => $request->filled('progress_percent')
                    ? ((float) $request->progress_percent >= 100 ? 100 : 0)
                    : $task->progress_percent,
                'updated_by' => Auth::user()->username,
                'updated_at' => now(),
            ]);
            $this->logTaskUpdate($task, $before);

            $this->rollupSubtask($projectId, $task->parent_task_id);
            $this->refreshCompleteTags($projectId);

            return response()->json(['success' => true, 'message' => 'Checklist item updated']);
        }

        $request->validate([
            'task_name' => ['required', 'string', 'max:255'],
            'task_description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'progress_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status_id' => ['nullable', 'string'],
            'assignees' => ['nullable', 'array'],
            'assignees.*' => ['string'],
            'team_ids' => ['nullable', 'array'],
            'team_ids.*' => ['string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
        ]);

        // The PIC picker posts pic_submitted=1 so clearing every person and
        // Team still syncs (an empty array never makes it into the form data).
        $syncPics = $request->has('assignees') || $request->has('team_ids') || $request->boolean('pic_submitted');

        abort_if(
            $task->is_locked && $syncPics
                && empty(array_filter($request->input('assignees', [])))
                && empty(array_filter($request->input('team_ids', []))),
            422,
            'A private task needs at least one PIC — make it public first.'
        );

        // Same conditions as the two syncs below. Saving the form with the
        // PICs untouched stays open to every task editor.
        $picsChanged = (($request->has('assignees') || $request->boolean('pic_submitted'))
                && PmChatAccess::listChanged(
                    TrProjectTaskAssignee::where('task_id', $task->task_id)->where('status', 'A')->pluck('username'),
                    $request->input('assignees', [])))
            || (($request->has('team_ids') || $request->boolean('pic_submitted'))
                && PmChatAccess::listChanged(
                    TrProjectTaskTeam::where('task_id', $task->task_id)->where('status', 'A')->pluck('team_id'),
                    $request->input('team_ids', [])));

        if ($picsChanged) {
            PmChatAccess::abortUnlessCanAssign('TSK', $task);
        }

        $username = Auth::user()->username;
        $now = now();
        $before = $this->taskSnapshot($task);

        DB::connection('pgsql5')->transaction(function () use ($request, $project, $task, $username, $now, $syncPics) {
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

            if ($request->has('team_ids') || $request->boolean('pic_submitted')) {
                $this->syncTaskTeams($project, $task->task_id, $request->input('team_ids', []), $username, $now);
            }

            if ($request->has('assignees') || $request->boolean('pic_submitted')) {
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

            if ($request->has('tags') || $request->boolean('tags_submitted')) {
                $this->syncTaskTags($task->task_id, $request->input('tags', []), $username, $now);
            }
        });

        $this->logTaskUpdate($task, $before);

        $this->recalcProjectProgress($task->project_id);
        // After the tag sync above, so a form that re-posts "Complete" on a
        // task that's no longer at 100% still ends up untagged.
        $this->refreshCompleteTags($task->project_id);

        return response()->json(['success' => true, 'message' => 'Task updated successfully']);
    }

    // Lock/unlock toggle in the Task detail header. Only someone assigned
    // to the task (or an admin) may flip it — so nobody can lock a task
    // they'd immediately be locked out of, and a lock always has at least
    // one assignee who can still get in.
    public function toggleLock(Request $request, string $projectId, string $taskId)
    {
        $this->assertCanEditTasks();
        $this->project($projectId);
        $task = $this->accessibleTask($projectId, $taskId);

        $this->abortIfChecklistItem($this->parentMap($projectId), $taskId, 'privacy setting');
        $request->validate(['locked' => ['required', 'boolean']]);

        $me = strtolower(trim(Auth::user()->username));
        $effective = TrProjectTask::effectiveAssigneeMap([$task->task_id])->get($task->task_id);

        abort_unless($effective->contains($me) || TrProjectTask::bypassesLock(Auth::user()), 403, 'Only people assigned to this task can change its privacy.');

        $locked = $request->boolean('locked');
        abort_if($locked && $effective->isEmpty(), 422, 'Assign at least one person or Team before making this task private.');

        $task->update([
            'is_locked' => $locked,
            'locked_by' => $locked ? Auth::user()->username : null,
            'locked_at' => $locked ? now() : null,
            'updated_by' => Auth::user()->username,
            'updated_at' => now(),
        ]);

        $this->logTask($task, $locked ? 'locked' : 'unlocked', 'made the ' . $this->noun($task) . ($locked ? ' private' : ' public'));

        return response()->json([
            'success' => true,
            'is_locked' => $locked,
            'message' => $locked ? 'Task is now private — only its assignees can open it.' : 'Task is now public.',
        ]);
    }

    // Detail header → "+" next to the PIC avatars: adds people to the task
    // without touching anyone already on it (the Edit form is still where
    // PICs/Teams get removed). Only people eligible for the Project.
    public function addAssignees(Request $request, string $projectId, string $taskId)
    {
        $this->assertCanEditTasks();
        $project = $this->project($projectId);
        $task = $this->accessibleTask($projectId, $taskId);

        $this->abortIfChecklistItem($this->parentMap($projectId), $taskId, 'assignees');
        PmChatAccess::abortUnlessCanAssign('TSK', $task);
        $request->validate(['usernames' => ['required', 'array', 'min:1'], 'usernames.*' => ['string']]);

        $eligible = $this->eligibleUsernames($project);
        $usernames = collect($request->input('usernames'))->map(fn ($u) => trim($u))->filter()->unique();
        abort_unless($usernames->every(fn ($u) => $eligible->contains(strtolower($u))), 422, 'Only people on this project can be added.');

        $before = $this->taskSnapshot($task);
        $this->activateAssignees(TrProjectTaskAssignee::class, $task->task_id, $usernames);
        $this->logTaskUpdate($task, $before);

        return response()->json(['success' => true, 'message' => 'People added to the task.']);
    }

    // Detail header → Cover: add/replace (POST) or remove (DELETE).
    public function uploadCover(Request $request, string $projectId, string $taskId)
    {
        $this->assertCanEditTasks();
        $this->project($projectId);
        $this->abortIfChecklistItem($this->parentMap($projectId), $taskId, 'cover');

        return $this->saveCover($request, $this->accessibleTask($projectId, $taskId), 'TSKCOVER');
    }

    public function destroyCover(string $projectId, string $taskId)
    {
        $this->assertCanEditTasks();
        $this->project($projectId);

        return $this->removeCover($this->accessibleTask($projectId, $taskId));
    }

    // Drag-and-drop status change on the per-project Task Kanban.
    public function updateStatus(Request $request, string $projectId, string $taskId)
    {
        $this->assertCanEditTasks();
        $this->project($projectId);
        $task = $this->accessibleTask($projectId, $taskId);

        $this->abortIfChecklistItem($this->parentMap($projectId), $taskId, 'board status');
        $request->validate(['status_id' => ['required', 'string']]);

        $before = $this->taskSnapshot($task);
        $task->update(['status_id' => $request->status_id, 'updated_by' => Auth::user()->username, 'updated_at' => now()]);
        $this->logTaskUpdate($task, $before);

        return response()->json(['success' => true]);
    }

    // "By Spreadsheet" drag-and-drop — same as TeamTaskController::reparent().
    // Both the moved task and its new parent must be ones the user can
    // open, so nothing gets dragged into (or out of) a lock they're not in.
    public function reparent(Request $request, string $projectId, string $taskId)
    {
        $this->assertCanEditTasks();
        $this->project($projectId);
        $task = $this->accessibleTask($projectId, $taskId);
        abort_unless($task->status === 'A', 404);

        $request->validate([
            'parent_task_id' => ['nullable', 'string'],
            'status_id' => ['nullable', 'string'],
        ]);

        $parentId = $request->input('parent_task_id') ?: null;
        $parent = $parentId ? $this->accessibleTask($projectId, $parentId) : null;
        abort_if($parent && $parent->status !== 'A', 404);
        abort_if($parent && $this->subtreeTaskIds($projectId, $taskId)->contains($parentId), 422, 'A task cannot be moved under itself or one of its own subtasks.');

        // Task → Subtask → Checklist item: the moved branch must still fit in three levels.
        $parents = $this->parentMap($projectId);
        $this->assertCanMoveUnder($parents, $taskId, $parentId);
        $oldParentId = $task->parent_task_id;

        abort_if(
            PmChatAccess::moveWouldOpen('TSK',
                TrProjectTask::where('project_id', $projectId)->where('status', 'A')->get(['task_id', 'parent_task_id', 'created_by', 'is_locked']),
                $taskId, $parentId, Auth::user()),
            403,
            'This move would open a task chat you are not part of. Ask an assignee, the creator or a project admin.'
        );

        $values = ['parent_task_id' => $parentId];
        if (!$parentId && $request->filled('status_id')) {
            abort_unless(MsProjectTaskStatus::where('project_id', $projectId)->where('status_id', $request->status_id)->where('status', 'A')->exists(), 422, 'Unknown status.');
            $values['status_id'] = $request->status_id;
        }

        $oldParentName = $task->parent_task_id ? TrProjectTask::where('task_id', $task->parent_task_id)->value('task_name') : null;
        $before = ['parent' => $oldParentName ?? 'Top level', 'status' => $this->taskSnapshot($task)['status']];
        $noun = $this->noun($task);

        $task->update($values + ['updated_by' => Auth::user()->username, 'updated_at' => now()]);

        $changes = PmActivityLogger::diff($before, ['parent' => $parent?->task_name ?? 'Top level', 'status' => $this->taskSnapshot($task->fresh())['status']], ['parent' => 'Parent', 'status' => 'Status']);
        if (!$changes) {
            return response()->json(['success' => true, 'changed' => false]);
        }

        $this->logTask($task, 'moved', $parent ? "moved the {$noun} under \"{$parent->task_name}\"" : "moved the {$noun} to the top level", $changes);

        // The Project rollup only averages top-level Tasks, and both the
        // old and the new parent's completion% can shift.
        $this->rollupSubtask($projectId, $oldParentId);
        $this->rollupSubtask($projectId, $parentId);
        $this->recalcProjectProgress($projectId);
        $this->refreshCompleteTags($projectId);

        return response()->json(['success' => true, 'changed' => true]);
    }

    public function destroy(string $projectId, string $taskId)
    {
        $this->assertCanEditTasks();
        $this->project($projectId);
        $task = $this->accessibleTask($projectId, $taskId);

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

        $nested = $ids->count() - 1;
        $this->logTask($task, 'archived', "archived the {$this->noun($task)}" . ($nested ? " (and {$nested} subtask" . ($nested > 1 ? 's' : '') . ')' : ''));

        // Archiving a checklist item changes its Subtask's roll-up.
        $this->rollupSubtask($projectId, $task->parent_task_id);
        $this->recalcProjectProgress($projectId);
        // Archiving a Subtask drops it out of its parent's completion math.
        $this->refreshCompleteTags($projectId);

        return response()->json(['success' => true, 'message' => 'Task archived successfully']);
    }

    // "+ Add status" on a Project's Task board — this Project's own,
    // isolated status list (mirrors TeamTaskController::storeStatus()).
    public function storeStatus(Request $request, string $projectId)
    {
        $this->assertCanEditTasks();
        $this->project($projectId);

        $request->validate([
            'status_name' => ['required', 'string', 'max:100'],
            'status_id' => ['nullable', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // A custom status_id / sort_order is only honored for admins (the
        // Status Settings panel); everyone else gets one derived from the name.
        $isAdmin = Auth::user()->hasRole('PROADMINACCESS');
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

        PmActivityLogger::log('PROJECT', $projectId, null, 'status_column', "added the status column \"{$statusName}\"");

        return response()->json(['success' => true, 'status' => $status]);
    }

    // Rename/recolor a status column — status_id itself never changes, so
    // Tasks already sitting in it keep pointing at the same row.
    public function updateStatusColumn(Request $request, string $projectId, string $statusId)
    {
        $this->assertCanEditTasks();
        $this->project($projectId);

        $request->validate([
            'status_name' => ['required', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $status = MsProjectTaskStatus::where('project_id', $projectId)->where('status_id', $statusId)->firstOrFail();
        $before = ['status_name' => $status->status_name, 'color' => $status->color, 'sort_order' => $status->sort_order];

        $status->update([
            'status_name' => trim($request->status_name),
            'color' => $request->input('color', $status->color),
            'sort_order' => (Auth::user()->hasRole('PROADMINACCESS') && $request->filled('sort_order')) ? (int) $request->sort_order : $status->sort_order,
            'updated_by' => Auth::user()->username,
            'updated_at' => now(),
        ]);

        $changes = PmActivityLogger::diff($before, $status->only(['status_name', 'color', 'sort_order']), ['status_name' => 'Name', 'color' => 'Color', 'sort_order' => 'Order']);
        if ($changes) {
            PmActivityLogger::log('PROJECT', $projectId, null, 'status_column', "edited the status column \"{$status->status_name}\"", $changes);
        }

        return response()->json(['success' => true, 'status' => $status]);
    }

    // Every default and custom status is equally deletable — blocked only
    // while a Task is still sitting in it, so a card is never silently
    // orphaned onto a column that no longer exists.
    public function destroyStatusColumn(string $projectId, string $statusId)
    {
        $this->assertCanEditTasks();
        $this->project($projectId);

        $status = MsProjectTaskStatus::where('project_id', $projectId)->where('status_id', $statusId)->firstOrFail();

        abort_if(
            TrProjectTask::where('project_id', $projectId)->where('status_id', $statusId)->where('status', 'A')->exists(),
            422,
            'Move or delete the tasks in this status first.'
        );

        $status->update(['status' => 'X', 'updated_by' => Auth::user()->username, 'updated_at' => now()]);

        PmActivityLogger::log('PROJECT', $projectId, null, 'status_column', "deleted the status column \"{$status->status_name}\"");

        return response()->json(['success' => true]);
    }

    // Chat list of the Project board's Message tab.
    public function chatThreads(string $projectId)
    {
        $this->project($projectId);

        $all = TrProjectTask::where('project_id', $projectId)->where('status', 'A')
            ->get(['task_id', 'parent_task_id', 'task_name', 'is_locked', 'created_by']);

        return response()->json($this->buildChatThreads('PRJ', $projectId, 'TSK', $all,
            TrProjectTask::accessMap($all, Auth::user())));
    }

    // @mention autocomplete for a Task's chat — the people of the parent
    // Project (union of members across every linked Team) who take part in
    // this chat.
    public function mentionableUsers(string $projectId, string $taskId)
    {
        $project = $this->project($projectId);
        $task = TrProjectTask::where('project_id', $projectId)->where('task_id', $taskId)->firstOrFail();
        TrProjectTask::abortUnlessAccessible('TSK', $taskId);

        // ?people=1 asks for the chat's profile bubbles instead.
        if (request()->boolean('people')) {
            return response()->json(PmChatAccess::bubblePeople('TSK', $task));
        }

        $usernames = $this->eligibleUsernames($project)
            ->reject(fn ($u) => $u === strtolower(Auth::user()->username));
        $people = User::whereIn(DB::raw('lower(username)'), $usernames->all())->get(['username', 'name', 'user_role']);

        // Same rule as the thread's own guard, answered in bulk.
        $audience = PmChatAccess::audience('TSK', $task);
        $openers = $task->usersWhoCanOpen($people)->pluck('username')->flip();
        $users = $people->filter(fn ($u) => $openers->has($u->username)
            && PmChatAccess::includes($audience, $u))->values();

        return response()->json($users->map(fn ($u) => ['username' => $u->username, 'name' => $u->name]));
    }
}
