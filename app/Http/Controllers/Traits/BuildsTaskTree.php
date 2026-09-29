<?php

namespace App\Http\Controllers\Traits;

use App\Models\TrAttachment;
use App\Models\TrMessage;
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

    /**
     * Per-task File / Comment counts for the board cards, one grouped query
     * each. Same filters as the detail modal's File tab
     * (TrAttachmentController::listAttachments) and Chat tab
     * (SendCommentController::fetchComments) so the numbers match. Covers
     * live under their own doctype (TSKCOVER/TTKCOVER) so aren't counted.
     *
     * @return array{0: Collection, 1: Collection} [files, comments] keyed by task_id
     */
    protected function taskCardCounts(string $doctype, Collection $taskIds): array
    {
        $ids = $taskIds->map(fn ($id) => (string) $id)->values()->all();
        if (! $ids) {
            return [collect(), collect()];
        }

        $files = TrAttachment::where('doctype', $doctype)
            ->whereIn('refnbr', $ids)
            ->where('status', 'A')
            ->groupBy('refnbr')
            ->selectRaw('refnbr, count(*) as total')
            ->pluck('total', 'refnbr');

        $comments = TrMessage::where('doctype', $doctype)
            ->whereIn('refnbr', $ids)
            ->where('status', 'A')
            ->where(fn ($q) => $q->whereNull('message_type')->orWhere('message_type', '!=', 'Private'))
            ->groupBy('refnbr')
            ->selectRaw('refnbr, count(*) as total')
            ->pluck('total', 'refnbr');

        return [$files, $comments];
    }
}
