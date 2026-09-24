<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrProjectTask extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_project_task';

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
    public static function effectiveAssigneeMap($taskIds)
    {
        $taskIds = collect($taskIds)->values();

        $direct = TrProjectTaskAssignee::whereIn('task_id', $taskIds)->where('status', 'A')
            ->get(['task_id', 'username'])->groupBy('task_id');
        $teams = TrProjectTaskTeam::whereIn('task_id', $taskIds)->where('status', 'A')
            ->get(['task_id', 'team_id'])->groupBy('task_id');
        $members = TrTeamMember::whereIn('team_id', $teams->flatten()->pluck('team_id')->unique())
            ->where('status', 'A')->get(['team_id', 'username'])->groupBy('team_id');

        return $taskIds->mapWithKeys(fn ($id) => [$id => collect()
            ->merge(($direct->get($id) ?? collect())->pluck('username'))
            ->merge(($teams->get($id) ?? collect())->flatMap(fn ($t) => ($members->get($t->team_id) ?? collect())->pluck('username')))
            ->map(fn ($u) => strtolower(trim($u)))
            ->filter()->unique()->values()]);
    }

    // Admins / PROADMINACCESS always get through a lock — the recovery path
    // if a locked task ends up with nobody left assigned to it.
    public static function bypassesLock($user): bool
    {
        return $user && ($user->isPrimaryAdmin() || $user->hasRole('PROADMINACCESS'));
    }

    // A lock covers the task's whole subtree: $user can open this task only
    // if they're assigned to EVERY locked task on the path from the root
    // down to (and including) this one.
    public function isAccessibleBy($user): bool
    {
        if (self::bypassesLock($user)) {
            return true;
        }

        $username = strtolower(trim((string) $user?->username));
        $node = $this;
        $seen = [];

        while ($node && !isset($seen[$node->task_id])) {
            $seen[$node->task_id] = true;

            if ($node->is_locked && !self::effectiveAssigneeMap([$node->task_id])->get($node->task_id)->contains($username)) {
                return false;
            }

            $node = $node->parent_task_id
                ? self::where('task_id', $node->parent_task_id)->first()
                : null;
        }

        return true;
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

    // Used by the shared comment/attachment endpoints, which only know a
    // doctype + refnbr — anything that isn't an existing TSK passes through.
    public static function abortUnlessAccessible(string $doctype, $taskId): void
    {
        if (!in_array(strtoupper($doctype), ['TSK', 'TSKCOVER'], true)) {
            return;
        }

        $task = self::where('task_id', $taskId)->first();

        abort_if($task && !$task->isAccessibleBy(auth()->user()), 403, 'This task is locked.');
    }
}
