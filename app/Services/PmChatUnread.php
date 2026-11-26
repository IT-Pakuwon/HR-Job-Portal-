<?php

namespace App\Services;

use App\Models\TrProjectMessage;
use App\Models\TrProjectTask;
use App\Models\TrTeamTask;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Unread messages of the Project module chats, tracked per user and thread
// in tr_project_message_read (database/sql/tr_project_message_read.sql).
class PmChatUnread
{
    private const TABLE = 'tr_project_message_read';

    // Messages older than this never count as unread.
    private const DAYS = 30;

    /**
     * Unread counts for the given boards, limited to the Task chats the user
     * takes part in (same rule as the Message tab's chat list).
     *
     * @return array{boards: array<string,int>, threads: array<string,int>}
     *         boards:  'TEAM/{team_id}' | 'PRJ/{project_id}' => total, Task chats included
     *         threads: '{doctype}/{refnbr}' => count
     */
    public static function forUser(User $user, Collection $teamIds, Collection $projectIds): array
    {
        $boards = [];
        $threads = [];

        try {
            $counts = self::unreadCounts(self::username($user));
        } catch (QueryException $e) {
            // Most likely the table hasn't been created yet. Logged hourly,
            // not on every poll.
            if (Cache::add('pm_chat_unread_error', true, now()->addHour())) {
                Log::warning('PmChatUnread: could not read unread counts', ['err' => $e->getMessage()]);
            }

            return compact('boards', 'threads');
        }

        $add = function (string $board, string $thread, int $total) use (&$boards, &$threads) {
            $boards[$board] = ($boards[$board] ?? 0) + $total;
            $threads[$thread] = $total;
        };

        foreach (['TEAM' => $teamIds, 'PRJ' => $projectIds] as $doctype => $ids) {
            foreach ($counts->get($doctype, collect()) as $row) {
                if ($ids->contains($row->refnbr)) {
                    $add("$doctype/$row->refnbr", "$doctype/$row->refnbr", (int) $row->total);
                }
            }
        }

        $taskChats = [
            'TSK' => [TrProjectTask::class, 'project_id', 'PRJ', $projectIds, ['A']],
            'TTK' => [TrTeamTask::class, 'team_id', 'TEAM', $teamIds, ['A', 'C']],
        ];

        foreach ($taskChats as $doctype => [$model, $boardColumn, $boardType, $boardIds, $statuses]) {
            $rows = $counts->get($doctype, collect())->keyBy('refnbr');
            if ($rows->isEmpty()) {
                continue;
            }

            $unreadBoards = $model::whereIn('task_id', $rows->keys()->all())
                ->whereIn($boardColumn, $boardIds->values()->all())
                ->pluck($boardColumn)->unique();
            if ($unreadBoards->isEmpty()) {
                continue;
            }

            // Whole boards: locks and chat audiences follow parent Tasks.
            foreach ($model::whereIn($boardColumn, $unreadBoards->all())->whereIn('status', $statuses)->get()->groupBy($boardColumn) as $boardId => $tasks) {
                $canOpen = $doctype === 'TSK' ? TrProjectTask::accessMap($tasks, $user) : null;
                $audiences = PmChatAccess::audiences($doctype, $tasks);
                $byId = $tasks->keyBy('task_id');

                foreach ($tasks as $task) {
                    $row = $rows->get($task->task_id);
                    // A row whose parent also has a parent is a checklist item: no chat.
                    if (! $row || $byId->get($task->parent_task_id)?->parent_task_id) {
                        continue;
                    }
                    if (($canOpen === null || ($canOpen[$task->task_id] ?? false))
                        && PmChatAccess::includes($audiences->get($task->task_id), $user)) {
                        $add("$boardType/$boardId", "$doctype/$task->task_id", (int) $row->total);
                    }
                }
            }
        }

        return compact('boards', 'threads');
    }

    // Moves the user's read position in a thread forward, never back.
    public static function markRead(User $user, string $doctype, string $refnbr, int $lastId): void
    {
        DB::connection('pgsql5')->statement(
            'INSERT INTO ' . self::TABLE . ' (username, doctype, refnbr, last_read_id, updated_at)
             VALUES (?, ?, ?, ?, ?)
             ON CONFLICT (username, doctype, refnbr) DO UPDATE
             SET last_read_id = GREATEST(' . self::TABLE . '.last_read_id, EXCLUDED.last_read_id),
                 updated_at = EXCLUDED.updated_at',
            [self::username($user), $doctype, $refnbr, $lastId, now()]
        );
    }

    // doctype => rows of (doctype, refnbr, total) newer than what was read.
    private static function unreadCounts(string $username): Collection
    {
        $db = DB::connection('pgsql5');

        $start = $db->table(self::TABLE)
            ->where('username', $username)->where('doctype', '*')->value('last_read_id');

        if ($start === null) {
            $start = (int) $db->table('tr_project_message')->max('id');
            $db->table(self::TABLE)->insertOrIgnore([
                'username' => $username, 'doctype' => '*', 'refnbr' => '*',
                'last_read_id' => $start, 'updated_at' => now(),
            ]);
        }

        return $db->table('tr_project_message as m')
            ->leftJoin(self::TABLE . ' as r', fn ($join) => $join
                ->on('r.doctype', '=', 'm.doctype')
                ->on('r.refnbr', '=', 'm.refnbr')
                ->where('r.username', '=', $username))
            ->whereIn('m.doctype', TrProjectMessage::DOCTYPES)
            ->where('m.status', 'A')
            ->where('m.id', '>', $start)
            ->whereRaw('m.id > coalesce(r.last_read_id, 0)')
            ->where('m.message_date', '>=', now()->subDays(self::DAYS))
            ->whereRaw("lower(trim(coalesce(m.username, ''))) <> ?", [$username])
            ->where(fn ($q) => $q->whereNull('m.message_type')->orWhere('m.message_type', '!=', 'Private'))
            ->groupBy('m.doctype', 'm.refnbr')
            ->selectRaw('m.doctype, m.refnbr, count(*) as total')
            ->get()
            ->groupBy('doctype');
    }

    private static function username(User $user): string
    {
        return strtolower(trim((string) $user->username));
    }
}
