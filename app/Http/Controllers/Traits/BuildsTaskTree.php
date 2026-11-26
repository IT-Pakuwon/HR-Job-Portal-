<?php

namespace App\Http\Controllers\Traits;

use App\Models\TrAttachment;
use App\Models\TrProjectMessage;
use App\Services\PmChatAccess;
use App\Services\PrivateChatAudience;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

        $comments = TrProjectMessage::where('doctype', $doctype)
            ->whereIn('refnbr', $ids)
            ->where('status', 'A')
            ->where(fn ($q) => $q->whereNull('message_type')->orWhere('message_type', '!=', 'Private'))
            ->groupBy('refnbr')
            ->selectRaw('refnbr, count(*) as total')
            ->pluck('total', 'refnbr');

        return [$files, $comments];
    }

    /**
     * The chat list on a board's Message tab: the board's own thread plus one
     * entry per Task / Subtask whose chat the user takes part in
     * (PmChatAccess), each with its latest message. Tasks with messages come
     * first (newest on top), then the rest by name, each Task followed by its
     * Subtasks. Checklist items have no chat.
     *
     * @param  Collection  $all      every live task row of the board
     * @param  array|null  $canOpen  task_id => bool on a board with locks
     * @return array{general: ?array, tasks: Collection}
     */
    protected function buildChatThreads(string $scopeDoctype, string $scopeId, string $taskDoctype, Collection $all, ?array $canOpen = null): array
    {
        $byId = $all->keyBy('task_id');
        $user = Auth::user();
        $audiences = PmChatAccess::audiences($taskDoctype, $all);
        $opens = fn ($id) => $canOpen === null || ($canOpen[$id] ?? false);

        // A row whose parent also has a parent is a checklist item.
        $tasks = $all->reject(fn ($t) => $byId->get($t->parent_task_id)?->parent_task_id)
            ->filter(fn ($t) => $opens($t->task_id) && PmChatAccess::includes($audiences->get($t->task_id), $user));

        $last = $this->latestMessages($taskDoctype, $tasks->pluck('task_id'));

        return [
            'general' => $this->latestMessages($scopeDoctype, collect([$scopeId]))->get($scopeId),
            'tasks' => $tasks->map(fn ($t) => [
                'task_id' => $t->task_id,
                'task_name' => $t->task_name,
                'parent_name' => $byId->get($t->parent_task_id)?->task_name,
                // Private itself or under a private Task: assignees only.
                'locked' => $audiences->get($t->task_id) instanceof PrivateChatAudience,
                'restricted' => $audiences->get($t->task_id) !== null,
                'can_open' => $opens($t->task_id),
                'last' => $last->get($t->task_id),
            ])->sortBy([
                fn ($a, $b) => strcmp($b['last']['date'] ?? '', $a['last']['date'] ?? ''),
                fn ($a, $b) => strnatcasecmp($a['parent_name'] ?? $a['task_name'], $b['parent_name'] ?? $b['task_name']),
                fn ($a, $b) => isset($a['parent_name']) <=> isset($b['parent_name']),
                fn ($a, $b) => strnatcasecmp($a['task_name'], $b['task_name']),
            ])->values(),
        ];
    }

    // refnbr => the thread's newest message, for those that have one.
    private function latestMessages(string $doctype, Collection $refnbrs): Collection
    {
        if ($refnbrs->isEmpty()) {
            return collect();
        }

        return TrProjectMessage::where('doctype', $doctype)
            ->whereIn('refnbr', $refnbrs->map(fn ($id) => (string) $id)->values()->all())
            ->where('status', 'A')
            ->where(fn ($q) => $q->whereNull('message_type')->orWhere('message_type', '!=', 'Private'))
            ->selectRaw('distinct on (refnbr) refnbr, id, username, name, message, message_date')
            ->orderBy('refnbr')->orderByDesc('message_date')->orderByDesc('id')
            ->get()
            ->mapWithKeys(fn ($m) => [$m->refnbr => [
                'id' => $m->id,
                'username' => $m->username,
                'name' => $m->name ?: $m->username,
                'text' => Str::limit(TrProjectMessage::plainText($m->message), 120),
                'date' => Carbon::parse($m->message_date)->format('Y-m-d H:i:s'),
            ]]);
    }
}
