<?php

namespace App\Services;

use App\Models\MsTeam;
use App\Models\TrAttachment;
use App\Models\TrMessage;
use App\Models\TrPmActivity;
use App\Models\TrProjectTask;
use App\Models\TrTeamTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Writes and reads the Project/Team activity history (tr_pm_activity).
// Writing never throws — a failed log line must not undo the user's save.
class PmActivityLogger
{
    // Newest-first cap for one History/Activity load.
    const FEED_LIMIT = 500;

    public static function log(string $scopeType, string $scopeId, ?string $taskId, string $action, string $description, array $changes = [], ?string $by = null): void
    {
        try {
            TrPmActivity::create([
                'scope_type' => $scopeType,
                'scope_id' => $scopeId,
                'task_id' => $taskId,
                'action' => $action,
                'description' => $description,
                'changes' => $changes ?: null,
                'created_by' => $by ?? Auth::user()?->username,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('PM activity log failed', ['scope' => "$scopeType:$scopeId", 'task' => $taskId, 'error' => $e->getMessage()]);
        }
    }

    // For the shared comment/attachment endpoints, which only know a
    // doctype + refnbr — anything that isn't a PM entity is ignored.
    public static function logForDocument(string $doctype, string $refnbr, string $action, string $description, array $changes = []): void
    {
        $doctype = strtoupper($doctype);

        [$scopeType, $scopeId, $taskId] = match ($doctype) {
            'PRJ' => ['PROJECT', $refnbr, null],
            'TSK' => ['PROJECT', TrProjectTask::where('task_id', $refnbr)->value('project_id'), $refnbr],
            'TTK' => ['TEAM', TrTeamTask::where('task_id', $refnbr)->value('team_id'), $refnbr],
            default => [null, null, null],
        };

        if ($scopeId) {
            self::log($scopeType, $scopeId, $taskId, $action, $description, $changes);
        }
    }

    // Field-by-field before/after diff. $labels maps key => label; a scalar
    // pair becomes {label, from, to}, a list pair becomes {label, added,
    // removed}, and keys in $summaryOnly (long rich text) just say "edited".
    public static function diff(array $before, array $after, array $labels, array $summaryOnly = []): array
    {
        $changes = [];

        foreach ($labels as $key => $label) {
            $old = $before[$key] ?? null;
            $new = $after[$key] ?? null;

            if (is_array($old) || is_array($new)) {
                $old = collect($old ?? [])->filter()->values();
                $new = collect($new ?? [])->filter()->values();
                $added = $new->diff($old)->values()->all();
                $removed = $old->diff($new)->values()->all();
                if ($added || $removed) {
                    $changes[] = ['label' => $label, 'added' => $added, 'removed' => $removed];
                }
                continue;
            }

            $old = self::normalize($old);
            $new = self::normalize($new);
            if ($old === $new) {
                continue;
            }

            $changes[] = in_array($key, $summaryOnly, true)
                ? ['label' => $label, 'note' => 'edited']
                : ['label' => $label, 'from' => $old ?? '—', 'to' => $new ?? '—'];
        }

        return $changes;
    }

    private static function normalize($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim(html_entity_decode(strip_tags((string) $value)));

        return $value === '' ? null : $value;
    }

    public static function formatDate($date): ?string
    {
        return $date ? Carbon::parse($date)->format('d M Y') : null;
    }

    public static function formatPercent($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.') . '%';
    }

    // usernames => display names (falls back to the username itself).
    public static function userNames($usernames): array
    {
        $usernames = collect($usernames)->map(fn ($u) => trim((string) $u))->filter()->unique(fn ($u) => strtolower($u));
        if ($usernames->isEmpty()) {
            return [];
        }

        $names = User::whereIn(DB::raw('lower(username)'), $usernames->map(fn ($u) => strtolower($u))->all())
            ->get(['username', 'name'])
            ->mapWithKeys(fn ($u) => [strtolower(trim($u->username)) => $u->name]);

        return $usernames->map(fn ($u) => $names->get(strtolower($u)) ?: $u)->values()->all();
    }

    public static function teamNames($teamIds): array
    {
        $teamIds = collect($teamIds)->filter()->unique();
        if ($teamIds->isEmpty()) {
            return [];
        }
        $names = MsTeam::whereIn('team_id', $teamIds)->pluck('team_name', 'team_id');

        return $teamIds->map(fn ($id) => $names->get($id, $id))->values()->all();
    }

    // $rootId plus every descendant's task_id, archived ones included —
    // their history still belongs to the parent's Activity list.
    public static function subtree($tasks, string $rootId): array
    {
        $ids = [$rootId];
        $frontier = [$rootId];

        while ($frontier) {
            $frontier = collect($tasks)->whereIn('parent_task_id', $frontier)->pluck('task_id')->diff($ids)->values()->all();
            $ids = array_merge($ids, $frontier);
        }

        return $ids;
    }

    // Merged, newest-first feed: logged events + chat messages + file
    // uploads. $tasks = task_id => ['name' => …, 'parent' => bool] of the
    // tasks the viewer may see; $scopeDoctype ('PRJ' or null) pulls in the
    // Project's own chat/files too when $withScopeEvents is on.
    public static function feed(string $scopeType, string $scopeId, array $tasks, string $taskDoctype, bool $withScopeEvents, ?string $scopeDoctype = null): array
    {
        $taskIds = array_keys($tasks);
        $limit = self::FEED_LIMIT;
        $items = collect();

        $logs = TrPmActivity::where('scope_type', $scopeType)->where('scope_id', $scopeId)
            ->where(function ($q) use ($taskIds, $withScopeEvents) {
                $q->whereIn('task_id', $taskIds ?: ['']);
                if ($withScopeEvents) {
                    $q->orWhereNull('task_id');
                }
            })
            ->orderByDesc('created_at')->orderByDesc('id')->limit($limit)->get();

        foreach ($logs as $row) {
            $items->push([
                'id' => 'log-' . $row->id,
                'kind' => $row->task_id ? 'task' : strtolower($scopeType),
                'action' => $row->action,
                'at' => $row->created_at->format('Y-m-d H:i:s'),
                'username' => $row->created_by,
                'text' => $row->description,
                'task_id' => $row->task_id,
                'changes' => $row->changes ?: [],
            ]);
        }

        // Comments/files live on pgsql2, keyed by doctype + refnbr.
        $refs = [[$taskDoctype, $taskIds]];
        if ($withScopeEvents && $scopeDoctype) {
            $refs[] = [$scopeDoctype, [$scopeId]];
        }

        foreach ($refs as [$doctype, $refnbrs]) {
            if (!$refnbrs) {
                continue;
            }

            $messages = TrMessage::where('doctype', $doctype)->whereIn('refnbr', $refnbrs)->where('status', 'A')
                ->where(fn ($q) => $q->whereNull('message_type')->orWhere('message_type', '!=', 'Private'))
                ->orderByDesc('message_date')->limit($limit)->get();

            foreach ($messages as $m) {
                $items->push([
                    'id' => 'msg-' . $m->id,
                    'kind' => 'chat',
                    'action' => 'commented',
                    'at' => Carbon::parse($m->message_date)->format('Y-m-d H:i:s'),
                    'username' => $m->username,
                    'text' => 'sent a message',
                    'task_id' => $doctype === $taskDoctype ? $m->refnbr : null,
                    'message' => TrMessage::plainText($m->message),
                    'changes' => [],
                ]);
            }

            $files = TrAttachment::where('doctype', $doctype)->whereIn('refnbr', $refnbrs)
                ->orderByDesc('created_at')->limit($limit)->get();

            foreach ($files as $f) {
                if (!$f->created_at) {
                    continue;
                }
                $items->push([
                    'id' => 'att-' . $f->id,
                    'kind' => 'file',
                    'action' => 'uploaded',
                    'at' => Carbon::parse($f->created_at)->format('Y-m-d H:i:s'),
                    'username' => $f->created_by,
                    'text' => 'uploaded a file',
                    'task_id' => $doctype === $taskDoctype ? $f->refnbr : null,
                    'file' => $f->attachment_name . ($f->extention ? '.' . $f->extention : ''),
                    'changes' => [],
                ]);
            }
        }

        $items = $items->sortByDesc(fn ($i) => $i['at'] . '|' . $i['id'])->take($limit)->values();

        $people = User::whereIn(DB::raw('lower(username)'), $items->pluck('username')->filter()->map(fn ($u) => strtolower(trim($u)))->unique()->values()->all())
            ->get(['username', 'name'])
            ->keyBy(fn ($u) => strtolower(trim($u->username)));

        return $items->map(function ($i) use ($people, $tasks) {
            $u = $people->get(strtolower(trim((string) $i['username'])));
            $task = $i['task_id'] ? ($tasks[$i['task_id']] ?? null) : null;

            return $i + [
                'name' => $u->name ?? ($i['username'] === 'system' ? 'System' : ($i['username'] ?: 'Someone')),
                'task_name' => $task['name'] ?? null,
                'is_subtask' => (bool) ($task['parent'] ?? false),
            ];
        })->all();
    }
}
