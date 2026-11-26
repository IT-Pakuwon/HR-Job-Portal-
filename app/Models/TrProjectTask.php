<?php

namespace App\Models;

use App\Services\PmChatAccess;
use Illuminate\Database\Eloquent\Model;

class TrProjectTask extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_project_task';

    // Second number inside a Project Task's /task/{eid} hash. Team Task and
    // Project Task ids overlap, and a Team Task's hash carries the id alone.
    public const EID_TAG = 1;

    protected $fillable = [
        'task_id',
        'project_id',
        'parent_task_id',
        'task_name',
        'task_description',
        'start_date',
        'end_date',
        'status_id',
        'progress_percent',
        'status',
        'is_locked',
        'locked_by',
        'locked_at',
        'cover_attachment_id',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(MsProject::class, 'project_id', 'project_id');
    }

    public function taskStatus()
    {
        return MsProjectTaskStatus::where('project_id', $this->project_id)->where('status_id', $this->status_id)->first();
    }

    // Self-referencing — a Task's children can themselves have children,
    // to unlimited depth (what used to be the separate, one-level-only
    // TrProjectTaskDetail "Subtask" is now just a Task with a parent_task_id).
    public function children()
    {
        return $this->hasMany(self::class, 'parent_task_id', 'task_id')
            ->where('status', 'A');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_task_id', 'task_id');
    }

    public function assignees()
    {
        return $this->hasMany(TrProjectTaskAssignee::class, 'task_id', 'task_id')
            ->where('status', 'A');
    }

    public function assignedTeams()
    {
        return $this->hasMany(TrProjectTaskTeam::class, 'task_id', 'task_id')
            ->where('status', 'A');
    }

    // task_id => lowercase usernames who count as assigned: the task's
    // direct people plus every current member of its assigned Teams.
    // Bulk so the board can resolve a whole project in three queries.
    // A caller that already loaded the task's assignee / Team rows (grouped
    // by task_id) can pass them in to skip the duplicate queries.
    public static function effectiveAssigneeMap($taskIds, $direct = null, $teams = null)
    {
        $taskIds = collect($taskIds)->values();

        $direct ??= $taskIds->isEmpty() ? collect() : TrProjectTaskAssignee::whereIn('task_id', $taskIds)->where('status', 'A')
            ->get(['task_id', 'username'])->groupBy('task_id');
        $teams ??= $taskIds->isEmpty() ? collect() : TrProjectTaskTeam::whereIn('task_id', $taskIds)->where('status', 'A')
            ->get(['task_id', 'team_id'])->groupBy('task_id');
        $teamIds = $teams->flatten()->pluck('team_id')->unique();
        $members = $teamIds->isEmpty() ? collect() : TrTeamMember::whereIn('team_id', $teamIds)
            ->where('status', 'A')->get(['team_id', 'username'])->groupBy('team_id');

        return $taskIds->mapWithKeys(fn ($id) => [$id => collect()
            ->merge(($direct->get($id) ?? collect())->pluck('username'))
            ->merge(($teams->get($id) ?? collect())->flatMap(fn ($t) => ($members->get($t->team_id) ?? collect())->pluck('username')))
            ->map(fn ($u) => strtolower(trim($u)))
            ->filter()->unique()->values()]);
    }

    // Only PROADMINACCESS gets through a lock regardless of assignment — the
    // recovery path if a locked task ends up with nobody left assigned to it.
    // (The plain admin role does not.)
    public static function bypassesLock($user): bool
    {
        return $user && $user->hasRole('PROADMINACCESS');
    }

    // A lock covers the task's whole subtree: $user can open this task only
    // if they're assigned to EVERY locked task on the path from the root
    // down to (and including) this one. Says nothing about Project
    // membership — that's MsProject::isAccessibleBy().
    public function isAccessibleBy($user): bool
    {
        return $this->usersWhoCanOpen(collect([$user]))->isNotEmpty();
    }

    // The subset of $users who get past this task's locks (all of them when
    // nothing on the path is locked).
    public function usersWhoCanOpen($users)
    {
        $locked = $this->lockedPathIds();

        if (!$locked) {
            return $users;
        }

        $assigned = self::effectiveAssigneeMap($locked);

        return $users->filter(function ($user) use ($assigned) {
            $username = strtolower(trim((string) $user?->username));

            return $assigned->every(fn ($names) => $names->contains($username))
                || self::bypassesLock($user);
        });
    }

    // task_ids of the locked tasks from this one up to the root.
    private function lockedPathIds(): array
    {
        $node = $this;
        $seen = [];
        $locked = [];

        while ($node && !isset($seen[$node->task_id])) {
            $seen[$node->task_id] = true;

            if ($node->is_locked) {
                $locked[] = $node->task_id;
            }

            $node = $node->parent_task_id
                ? self::where('task_id', $node->parent_task_id)->first()
                : null;
        }

        return $locked;
    }

    // Bulk isAccessibleBy() over one project's tasks: task_id => bool.
    // Parents outside $tasks are treated as open, same as the board.
    public static function accessMap($tasks, $user): array
    {
        $byId = collect($tasks)->keyBy('task_id');
        $bypass = self::bypassesLock($user);
        $me = strtolower(trim((string) $user?->username));
        $effective = $bypass ? collect() : self::effectiveAssigneeMap($byId->where('is_locked', true)->keys());
        $canOpen = [];

        $resolve = function ($taskId) use (&$resolve, &$canOpen, $byId, $effective, $me, $bypass) {
            if (array_key_exists($taskId, $canOpen)) {
                return $canOpen[$taskId];
            }
            $canOpen[$taskId] = true; // cycle guard
            $t = $byId->get($taskId);
            $ok = $bypass || !$t->is_locked || $effective->get($taskId)->contains($me);
            if ($ok && $t->parent_task_id && $byId->has($t->parent_task_id)) {
                $ok = $resolve($t->parent_task_id);
            }

            return $canOpen[$taskId] = $ok;
        };

        return $byId->keys()->mapWithKeys(fn ($id) => [$id => $resolve($id)])->all();
    }

    // Who may read or write a Project module thread (its messages and files),
    // identified the way tr_project_message / tr_attachment do it: doctype + refnbr.
    //   TEAM      — an active Team's members
    //   PRJ       — whoever can open the Project
    //   TSK       — whoever can open the Project, gets past the locks AND is in
    //               the Task's chat audience (see PmChatAccess); project
    //               admins take part regardless of locks and assignees
    //   TTK       — whoever can open the Team and is in the chat audience
    //   TSKCOVER  — the card image: whoever can open the Task
    //   TTKCOVER  — the card image: whoever can open the Team
    // Other doctypes, and refnbrs that aren't Project module records (the
    // legacy "Task" document also uses TSK), are not restricted here.
    public static function canOpenThread(string $doctype, $refnbr, ?User $user): bool
    {
        switch (strtoupper($doctype)) {
            case 'TEAM':
                $team = MsTeam::where('team_id', $refnbr)->where('status', 'A')->first();

                return (bool) $team?->isAccessibleBy($user);

            case 'PRJ':
                $project = MsProject::where('project_id', $refnbr)->first();

                return !$project || $project->isAccessibleBy($user);

            case 'TSK':
            case 'TSKCOVER':
                $task = self::where('task_id', $refnbr)->first();

                if (!$task) {
                    return true;
                }
                if (!$task->project?->isAccessibleBy($user)) {
                    return false;
                }
                if (strtoupper($doctype) === 'TSKCOVER') {
                    return $task->isAccessibleBy($user);
                }

                return PmChatAccess::isAdmin($user)
                    || ($task->isAccessibleBy($user) && PmChatAccess::includes(PmChatAccess::audience('TSK', $task), $user));

            case 'TTK':
            case 'TTKCOVER':
                $task = TrTeamTask::where('task_id', $refnbr)->first();

                if (!$task) {
                    return true;
                }
                if (!MsTeam::where('team_id', $task->team_id)->where('status', 'A')->first()?->isAccessibleBy($user)) {
                    return false;
                }

                return strtoupper($doctype) === 'TTKCOVER'
                    || PmChatAccess::includes(PmChatAccess::audience('TTK', $task), $user);

            default:
                return true;
        }
    }

    // The guard on the shared comment/attachment endpoints.
    public static function abortUnlessAccessible(string $doctype, $refnbr): void
    {
        abort_unless(
            self::canOpenThread($doctype, $refnbr, auth()->user()),
            403,
            'You do not have access to these messages and files.'
        );
    }
}
