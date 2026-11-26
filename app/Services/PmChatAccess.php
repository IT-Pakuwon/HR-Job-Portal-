<?php

namespace App\Services;

use App\Models\TrProjectTask;
use App\Models\TrTeamTask;
use App\Models\TrTeamTaskAssignee;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

// Who takes part in a Task's chat (its messages and files), for Project Tasks
// ('TSK') and Team Tasks ('TTK'):
//   - a Task with assignees (direct, or through an assigned Team) is limited
//     to them and its creator;
//   - a Task with none follows the nearest parent that has some, that
//     parent's creator included;
//   - with none anywhere up the line, everyone on the board takes part;
//   - project admins take part in these chats too.
// A private (locked) Task, and everything under it, is stricter: only the
// assignees take part — not project admins, nor the creator unless assigned
// (see PrivateChatAudience).
// Board membership and locks are separate checks — see
// TrProjectTask::canOpenThread().
class PmChatAccess
{
    public static function isAdmin(?User $user): bool
    {
        return $user && $user->hasRole('PROADMINACCESS');
    }

    // Whether $user is part of an audience from audiences() / audience().
    public static function includes(?Collection $audience, ?User $user): bool
    {
        return $audience === null
            || ($user && $audience->contains(strtolower(trim((string) $user->username))))
            || (! $audience instanceof PrivateChatAudience && self::isAdmin($user));
    }

    // A Task chat's profile bubbles: only the people assigned to it — or, when
    // it has no one, to the nearest parent that has. Others who can get into
    // the chat (creator, project admins, everyone on an unassigned Task) are
    // not shown.
    public static function bubblePeople(string $doctype, TrProjectTask|TrTeamTask $task): Collection
    {
        $names = collect();
        $seen = [];

        for ($node = $task; $node && !isset($seen[$node->task_id]) && $names->isEmpty();) {
            $seen[$node->task_id] = true;
            $names = self::assignees($doctype, collect([$node->task_id]))->get($node->task_id) ?? collect();
            $node = $node->parent_task_id ? $task::where('task_id', $node->parent_task_id)->first() : null;
        }

        if ($names->isEmpty()) {
            return collect();
        }

        return User::whereIn(DB::raw('lower(username)'), $names->unique()->values()->all())
            ->orderBy('name')->get(['username', 'name'])
            ->map(fn ($u) => ['username' => $u->username, 'name' => $u->name])->values();
    }

    // Who may change a Task's assignees. Wider than the chat on a private
    // Task: project admins and the creator still manage it, so one left with
    // nobody assigned can be recovered.
    public static function canAssign(?Collection $audience, TrProjectTask|TrTeamTask $task, ?User $user): bool
    {
        return self::includes($audience, $user)
            || self::isAdmin($user)
            || ($user && strtolower(trim((string) $task->created_by)) === strtolower(trim((string) $user->username)));
    }

    // Being assigned opens a Task's chat, so on a Task that is already limited
    // (its own assignees or a parent's) not everyone on the board may change
    // who is assigned. Otherwise anyone could add themselves.
    public static function abortUnlessCanAssign(string $doctype, TrProjectTask|TrTeamTask $task): void
    {
        abort_unless(
            self::canAssign(self::audience($doctype, $task), $task, auth()->user()),
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

    // task_id => lowercase usernames, or null for "everyone on the board". A
    // private Task gets a PrivateChatAudience (possibly empty).
    // $rows (task_id, parent_task_id, created_by, and is_locked for Project
    // Tasks) must include the parents of every task asked about. $assigned
    // (task_id => lowercase usernames) can be passed by a caller that already
    // loaded it.
    public static function audiences(string $doctype, Collection $rows, ?Collection $assigned = null): Collection
    {
        $byId = $rows->keyBy('task_id');
        $assigned ??= self::assignees($doctype, $byId->keys());

        return $byId->map(function ($task) use ($byId, $assigned) {
            $seen = [];
            $private = false;
            $names = null;
            $owner = null;

            // The nearest assignees decide the audience; a lock anywhere up
            // the line (even above them) makes the Task private.
            for ($node = $task; $node && !isset($seen[$node->task_id]); $node = $byId->get($node->parent_task_id)) {
                $seen[$node->task_id] = true;
                $private = $private || (bool) $node->is_locked;

                if (! $names && $assigned->get($node->task_id)?->isNotEmpty()) {
                    $names = $assigned->get($node->task_id);
                    $owner = $node;
                }
            }

            if ($private) {
                return new PrivateChatAudience(($names ?? collect())->values()->all());
            }

            return $names?->concat([$owner->created_by, $task->created_by])
                ->map(fn ($u) => strtolower(trim((string) $u)))
                ->filter()->unique()->values();
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
