<?php

namespace App\Services;

use App\Models\TrProjectTask;
use App\Models\TrTeamTask;
use App\Models\TrTeamTaskAssignee;
use App\Models\User;
use Illuminate\Support\Collection;

// Who takes part in a Task's chat (its messages and files), for Project Tasks
// ('TSK') and Team Tasks ('TTK'):
//   - a Task with assignees (direct, or through an assigned Team) is limited
//     to them and its creator;
//   - a Task with none follows the nearest parent that has some, that
//     parent's creator included;
//   - with none anywhere up the line, everyone on the board takes part;
//   - project admins take part in every Task chat.
// Board membership and locks are separate checks — see
// TrProjectTask::canOpenThread().
class PmChatAccess
{
    public static function isAdmin(?User $user): bool
    {
        return $user && ($user->isPrimaryAdmin() || $user->hasRole('PROADMINACCESS'));
    }

    // Whether $user is part of an audience from audiences() / audience().
    public static function includes(?Collection $audience, ?User $user): bool
    {
        return $audience === null
            || ($user && $audience->contains(strtolower(trim((string) $user->username))))
            || self::isAdmin($user);
    }

    // Being assigned opens a Task's chat, so on a Task that is already limited
    // (its own assignees or a parent's) only its audience may change who is
    // assigned. Otherwise anyone on the board could add themselves.
    public static function abortUnlessCanAssign(string $doctype, TrProjectTask|TrTeamTask $task): void
    {
        abort_unless(
            self::includes(self::audience($doctype, $task), auth()->user()),
            403,
            'Only the people assigned to this task, its creator and project admins can change who is assigned.'
        );
    }

    // Whether an edit form's assignee list differs from the saved one.
    public static function listChanged($current, $requested): bool
    {
        $normalize = fn ($list) => collect($list)->map(fn ($v) => strtolower(trim((string) $v)))
            ->filter()->unique()->sort()->values()->all();

        return $normalize($current) !== $normalize($requested);
    }

    // Whether putting $taskId under $parentId would let $user into a chat that
    // is closed to them now (a Task without assignees follows its parent).
    // $rows = every live task of the board.
    public static function moveWouldOpen(string $doctype, Collection $rows, string $taskId, ?string $parentId, ?User $user): bool
    {
        $assigned = self::assignees($doctype, $rows->pluck('task_id'));
        $before = self::audiences($doctype, $rows, $assigned);
        $after = self::audiences($doctype, $rows->map(function ($row) use ($taskId, $parentId) {
            if ($row->task_id !== $taskId) {
                return $row;
            }

            $moved = clone $row;
            $moved->parent_task_id = $parentId;

            return $moved;
        }), $assigned);

        return $before->keys()->contains(fn ($id) => !self::includes($before->get($id), $user)
            && self::includes($after->get($id), $user));
    }

    // task_id => lowercase usernames, or null for "everyone on the board".
    // $rows (task_id, parent_task_id, created_by) must include the parents of
    // every task asked about. $assigned (task_id => lowercase usernames) can
    // be passed by a caller that already loaded it.
    public static function audiences(string $doctype, Collection $rows, ?Collection $assigned = null): Collection
    {
        $byId = $rows->keyBy('task_id');
        $assigned ??= self::assignees($doctype, $byId->keys());

        return $byId->map(function ($task) use ($byId, $assigned) {
            $seen = [];

            for ($node = $task; $node && !isset($seen[$node->task_id]); $node = $byId->get($node->parent_task_id)) {
                $seen[$node->task_id] = true;
                $names = $assigned->get($node->task_id);

                if ($names && $names->isNotEmpty()) {
                    return $names->concat([$node->created_by, $task->created_by])
                        ->map(fn ($u) => strtolower(trim((string) $u)))
                        ->filter()->unique()->values();
                }
            }

            return null;
        });
    }

    // The audience of a single task, loading its parents.
    public static function audience(string $doctype, TrProjectTask|TrTeamTask $task): ?Collection
    {
        $chain = collect();

        for ($node = $task; $node && !$chain->has($node->task_id);) {
            $chain->put($node->task_id, $node);
            $node = $node->parent_task_id ? $task::where('task_id', $node->parent_task_id)->first() : null;
        }

        return self::audiences($doctype, $chain->values())->get($task->task_id);
    }

    private static function assignees(string $doctype, Collection $taskIds): Collection
    {
        if ($taskIds->isEmpty()) {
            return collect();
        }

        if ($doctype === 'TSK') {
            return TrProjectTask::effectiveAssigneeMap($taskIds);
        }

        return TrTeamTaskAssignee::whereIn('task_id', $taskIds->values()->all())->where('status', 'A')
            ->get(['task_id', 'username'])->groupBy('task_id')
            ->map(fn ($rows) => $rows->map(fn ($a) => strtolower(trim($a->username))));
    }
}
