<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Collection;

trait BuildsTaskTree
{
    /**
     * Nest a flat collection of task arrays (each carrying a
     * 'parent_task_id' key) into a recursive tree, keyed by 'children'.
     * Shared by PmTaskController and TeamTaskController — both own a
     * self-referencing task table of the same shape.
     */
    protected function buildTaskTree(Collection $flatTasks, ?string $parentId = null): array
    {
        return $flatTasks->where('parent_task_id', $parentId)
            ->map(fn ($t) => array_merge($t, ['children' => $this->buildTaskTree($flatTasks, $t['task_id'])]))
            ->values()->all();
    }
}
