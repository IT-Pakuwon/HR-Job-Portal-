<?php

namespace App\Http\Controllers\Traits;

use App\Models\MsTaskTag;
use Illuminate\Support\Collection;

trait TagsCompleteTasks
{
    /**
     * Keep the system "Complete" tag in step with each task's completion,
     * using the same rule the frontend's progress bar/badge already uses
     * (teamTaskCard()/openTaskEntityDetail(): all non-cancelled children at
     * 100%, or its own progress_percent for a leaf). Tagging instead of
     * moving the task into a "Done" column means it works on every board,
     * whatever columns the Team/Project has (or has deleted/renamed).
     *
     * $tasks is the whole Team/Project's 'A'+'C' task set — the rule only
     * looks at direct children, so evaluating everything in memory covers
     * a parent completed by its last subtask without walking up the tree.
     * $tagModel is the per-kind link table (TrTeamTaskTag/TrProjectTaskTag).
     * $log($task, $added) records each change in the task's Activity List.
     */
    protected function syncCompleteTags(Collection $tasks, string $tagModel, callable $log): void
    {
        $children = $tasks->where('status', 'A')->whereNotNull('parent_task_id')->groupBy('parent_task_id');

        $complete = $tasks->where('status', 'A')->mapWithKeys(function ($t) use ($children) {
            $kids = $children->get($t->task_id, collect());

            return [$t->task_id => $kids->isNotEmpty()
                ? $kids->every(fn ($c) => (float) $c->progress_percent >= 100)
                : (float) $t->progress_percent >= 100];
        });

        if ($complete->isEmpty()) {
            return;
        }

        $tagged = $tagModel::where('tag_id', 'COMPLETE')->where('status', 'A')
            ->whereIn('task_id', $complete->keys())
            ->pluck('task_id')
            ->flip();

        $toAdd = $complete->filter(fn ($done, $id) => $done && ! $tagged->has($id))->keys();
        $toRemove = $complete->filter(fn ($done, $id) => ! $done && $tagged->has($id))->keys();

        if ($toAdd->isNotEmpty()) {
            MsTaskTag::firstOrCreate(
                ['tag_id' => 'COMPLETE'],
                ['tag_name' => 'Complete', 'color' => '#10B981', 'status' => 'A', 'created_by' => 'system', 'created_at' => now()]
            );
        }

        foreach ($toAdd as $taskId) {
            $tagModel::updateOrCreate(['task_id' => $taskId, 'tag_id' => 'COMPLETE'], ['status' => 'A']);
            $log($tasks->firstWhere('task_id', $taskId), true);
        }

        if ($toRemove->isNotEmpty()) {
            $tagModel::where('tag_id', 'COMPLETE')->whereIn('task_id', $toRemove)->update(['status' => 'X']);
            $toRemove->each(fn ($taskId) => $log($tasks->firstWhere('task_id', $taskId), false));
        }
    }
}
