<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Collection;

/**
 * Task → Subtask → Checklist item: the task tree is capped at three levels.
 *
 * A "checklist item" is simply a task at depth 3 (its parent is a Subtask,
 * whose parent is a top-level Task). There is no extra column or table — the
 * level is derived from parent_task_id, so it can never drift out of step
 * with the tree. A checklist item is deliberately lightweight: only a name
 * and a done/not-done checkbox (progress_percent 0 or 100). It has no PIC,
 * dates, description, tags, cover, lock, status column or sub-items.
 *
 * Used by PmTaskController (Project tasks, tr_project_task) and
 * TeamTaskController (Team tasks, tr_team_task) — $model is the task model
 * class, $ownerCol its Project/Team key column ('project_id' / 'team_id').
 * The front end applies the same rule (see projects.blade.php).
 */
trait ChecklistItems
{
    /** Task = 1, Subtask = 2, Checklist item = 3 — nothing can sit below that. */
    protected const MAX_TASK_DEPTH = 3;

    /** task_id => parent_task_id for every non-archived task of one Project/Team. */
    protected function checklistParentMap(string $model, string $ownerCol, string $ownerId): Collection
    {
        return $model::where($ownerCol, $ownerId)->whereIn('status', ['A', 'C'])->pluck('parent_task_id', 'task_id');
    }

    /** 1 for a top-level task; the walk is cycle-safe. */
    protected function taskDepthIn(Collection $parents, ?string $taskId): int
    {
        $depth = 0;
        $seen = [];

        while ($taskId && !isset($seen[$taskId]) && $parents->has($taskId)) {
            $seen[$taskId] = true;
            $depth++;
            $taskId = $parents->get($taskId);
        }

        return $depth;
    }

    /** Levels in the subtree rooted at $taskId, counting $taskId itself (a leaf = 1). */
    protected function subtreeHeightIn(Collection $parents, string $taskId): int
    {
        // parent_task_id => [child task_ids]; preserveKeys so the task_ids survive the grouping.
        $byParent = $parents->filter()->groupBy(fn ($p) => $p, true)->map(fn ($g) => $g->keys());
        $height = 0;
        $frontier = collect([$taskId]);
        $seen = [$taskId => true];

        while ($frontier->isNotEmpty()) {
            $height++;
            $frontier = $frontier->flatMap(fn ($id) => $byParent->get($id, collect()))
                ->reject(function ($id) use (&$seen) {
                    $dup = isset($seen[$id]);
                    $seen[$id] = true;

                    return $dup;
                })->values();
        }

        return $height;
    }

    protected function isChecklistItemIn(Collection $parents, string $taskId): bool
    {
        return $this->taskDepthIn($parents, $taskId) >= self::MAX_TASK_DEPTH;
    }

    /** Creating a child under $parentId: refused once that would be a 4th level. */
    protected function assertCanNestUnder(Collection $parents, ?string $parentId): void
    {
        abort_if(
            $parentId && $this->taskDepthIn($parents, $parentId) >= self::MAX_TASK_DEPTH,
            422,
            'A checklist item cannot have anything under it. Tasks go Task → Subtask → Checklist.'
        );
    }

    /** Moving $taskId (with its subtree) under $parentId must not push anything past level 3. */
    protected function assertCanMoveUnder(Collection $parents, string $taskId, ?string $parentId): void
    {
        if (!$parentId) {
            return;
        }

        abort_if(
            $this->taskDepthIn($parents, $parentId) + $this->subtreeHeightIn($parents, $taskId) > self::MAX_TASK_DEPTH,
            422,
            'That would nest too deep. Tasks go Task → Subtask → Checklist (three levels at most).'
        );
    }

    /** Reject operations that make no sense for a checklist item (PIC, cover, lock, status, cancel…). */
    protected function abortIfChecklistItem(Collection $parents, string $taskId, string $what): void
    {
        abort_if(
            $this->isChecklistItemIn($parents, $taskId),
            422,
            "A checklist item only has a name and a checkbox — it has no {$what}."
        );
    }

    /**
     * A Subtask that has checklist items is as done as its items are: after
     * any change under it, set its progress_percent to checked / total (open
     * items only — archived/cancelled ones don't count). With no items left
     * its own progress is left as it was.
     */
    protected function rollupChecklistProgress(string $model, string $ownerCol, string $ownerId, ?string $subtaskId): void
    {
        if (!$subtaskId) {
            return;
        }

        // Only a Subtask (it has a parent of its own) rolls up. A top-level
        // Task's progress is not derived here — its own rule lives in the UI.
        if (!$model::where($ownerCol, $ownerId)->where('task_id', $subtaskId)->whereNotNull('parent_task_id')->exists()) {
            return;
        }

        $items = $model::where($ownerCol, $ownerId)->where('parent_task_id', $subtaskId)->where('status', 'A')->get(['task_id', 'progress_percent']);
        if ($items->isEmpty()) {
            return;
        }

        $pct = round($items->filter(fn ($i) => (float) $i->progress_percent >= 100)->count() / $items->count() * 100, 2);

        $model::where($ownerCol, $ownerId)->where('task_id', $subtaskId)->where('progress_percent', '!=', $pct)->update(['progress_percent' => $pct]);
    }

    /**
     * Checklist items never carry the auto "Complete" tag (they have no tags
     * at all) — drop them from the set the tagger looks at. A Subtask with
     * checklist items then reads as a leaf at its rolled-up progress_percent.
     */
    protected function withoutChecklistItems(Collection $tasks): Collection
    {
        $parents = $tasks->pluck('parent_task_id', 'task_id');

        return $tasks->reject(fn ($t) => $this->isChecklistItemIn($parents, $t->task_id))->values();
    }
}
