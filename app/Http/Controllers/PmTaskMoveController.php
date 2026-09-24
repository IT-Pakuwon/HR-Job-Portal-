<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Http\Controllers\Traits\RequiresTaskAccess;
use App\Models\MsProject;
use App\Models\MsProjectTaskStatus;
use App\Models\MsTeam;
use App\Models\MsTeamTaskStatus;
use App\Models\TrAttachment;
use App\Models\TrMessage;
use App\Models\TrPmActivity;
use App\Models\TrProjectPic;
use App\Models\TrProjectTask;
use App\Models\TrProjectTaskAssignee;
use App\Models\TrProjectTaskTag;
use App\Models\TrProjectTaskTeam;
use App\Models\TrProjectTeam;
use App\Models\TrTeamMember;
use App\Models\TrTeamTask;
use App\Models\TrTeamTaskAssignee;
use App\Models\TrTeamTaskTag;
use App\Services\PmActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Task Detail header → Move: sends a Task (with its whole subtree) to
// another Team or Project board the user can open, into a chosen status
// column, landing there as a top-level card.
//
// Same kind (Project→Project, Team→Team): rows keep their task_id and just
// change board, so chat/files/cover/activity follow automatically.
// Across kinds (Team↔Project) the two live in different tables with their
// own numbering (TTK/TSK), so the subtree is re-created under new ids, its
// chat/files/cover/activity are re-pointed to them, and the old rows are
// archived ('X').
//
// Assignees who aren't eligible on the destination board are dropped, as
// are PIC Teams not linked to a destination Project. Locks only exist on
// Project Tasks, so a move to a Team clears them — which is why a subtree
// containing a lock the mover can't open can't be moved at all.
class PmTaskMoveController extends Controller
{
    use HasAutonbr;
    use RequiresTaskAccess;

    private const KINDS = [
        'PROJECT' => [
            'task' => TrProjectTask::class,
            'assignee' => TrProjectTaskAssignee::class,
            'tag' => TrProjectTaskTag::class,
            'status' => MsProjectTaskStatus::class,
            'scope_col' => 'project_id',
            'doctype' => 'TSK',
            'label' => 'Project',
        ],
        'TEAM' => [
            'task' => TrTeamTask::class,
            'assignee' => TrTeamTaskAssignee::class,
            'tag' => TrTeamTaskTag::class,
            'status' => MsTeamTaskStatus::class,
            'scope_col' => 'team_id',
            'doctype' => 'TTK',
            'label' => 'Team',
        ],
    ];

    private function isPmAdmin(): bool
    {
        return Auth::user()->isPrimaryAdmin() || Auth::user()->hasRole('PROADMINACCESS');
    }

    private function me(): string
    {
        return strtolower(trim(Auth::user()->username));
    }

    // Same rule as TeamTaskController::team(): a member, or an admin.
    private function myTeamIds(): Collection
    {
        if ($this->isPmAdmin()) {
            return MsTeam::where('status', 'A')->pluck('team_id');
        }

        $memberOf = TrTeamMember::where('status', 'A')->whereRaw('lower(trim(username)) = ?', [$this->me()])->pluck('team_id');

        return MsTeam::where('status', 'A')->whereIn('team_id', $memberOf)->pluck('team_id');
    }

    // Same rule as PmTaskController::project(): member of a linked Team,
    // a USER PIC, the creator — or an admin.
    private function myProjectIds(): Collection
    {
        if ($this->isPmAdmin()) {
            return MsProject::where('status', 'A')->pluck('project_id');
        }

        $memberOf = TrTeamMember::where('status', 'A')->whereRaw('lower(trim(username)) = ?', [$this->me()])->pluck('team_id');

        $ids = TrProjectTeam::where('status', 'A')->whereIn('team_id', $memberOf)->pluck('project_id')
            ->merge(TrProjectPic::where('status', 'A')->where('pic_type', 'USER')->whereRaw('lower(trim(ref_id)) = ?', [$this->me()])->pluck('project_id'))
            ->merge(MsProject::whereRaw('lower(trim(created_by)) = ?', [$this->me()])->pluck('project_id'))
            ->unique();

        return MsProject::where('status', 'A')->whereIn('project_id', $ids)->pluck('project_id');
    }

    private function boardIds(string $type): Collection
    {
        return $type === 'PROJECT' ? $this->myProjectIds() : $this->myTeamIds();
    }

    private function boardName(string $type, string $id): string
    {
        return $type === 'PROJECT'
            ? (string) MsProject::where('project_id', $id)->value('project_name')
            : (string) MsTeam::where('team_id', $id)->value('team_name');
    }

    // Lowercase usernames who may be assigned on the destination board —
    // the same pools the Edit form's PIC picker offers.
    private function eligibleUsernames(string $type, string $id): Collection
    {
        if ($type === 'TEAM') {
            return MsTeam::where('team_id', $id)->first()->memberUsers()->pluck('username')->map(fn ($u) => strtolower(trim($u)));
        }

        $project = MsProject::where('project_id', $id)->first();

        return MsTeam::whereIn('team_id', $project->teams->pluck('team_id'))->get()
            ->flatMap(fn ($t) => $t->memberUsers()->pluck('username'))
            ->map(fn ($u) => strtolower(trim($u)))
            ->merge($project->picUsernames())
            ->push(strtolower(trim((string) $project->created_by)))
            ->filter()
            ->unique();
    }

    // GET: every board the user could move a task to, each with its status
    // columns — the Move dialog's two pickers.
    public function targets()
    {
        $teamIds = $this->myTeamIds();
        $projectIds = $this->myProjectIds();

        $cols = ['status_id', 'status_name', 'color', 'sort_order'];
        $teamStatuses = MsTeamTaskStatus::whereIn('team_id', $teamIds)->where('status', 'A')->orderBy('sort_order')
            ->get(array_merge($cols, ['team_id']))->groupBy('team_id');
        $projectStatuses = MsProjectTaskStatus::whereIn('project_id', $projectIds)->where('status', 'A')->orderBy('sort_order')
            ->get(array_merge($cols, ['project_id']))->groupBy('project_id');

        $shape = fn ($rows) => ($rows ?? collect())->map(fn ($s) => [
            'status_id' => $s->status_id,
            'status_name' => $s->status_name,
            'color' => $s->color,
        ])->values();

        return response()->json([
            'teams' => MsTeam::whereIn('team_id', $teamIds)->orderBy('team_name')->get(['team_id', 'team_name'])
                ->map(fn ($t) => ['id' => $t->team_id, 'name' => $t->team_name, 'statuses' => $shape($teamStatuses->get($t->team_id))])->values(),
            'projects' => MsProject::whereIn('project_id', $projectIds)->orderBy('project_name')->get(['project_id', 'project_name'])
                ->map(fn ($p) => ['id' => $p->project_id, 'name' => $p->project_name, 'statuses' => $shape($projectStatuses->get($p->project_id))])->values(),
        ]);
    }

    public function move(Request $request)
    {
        $this->assertCanEditTasks();
        $request->validate([
            'from_type' => ['required', 'in:PROJECT,TEAM'],
            'from_id' => ['required', 'string'],
            'task_id' => ['required', 'string'],
            'to_type' => ['required', 'in:PROJECT,TEAM'],
            'to_id' => ['required', 'string'],
            'status_id' => ['required', 'string'],
        ]);

        [$fromType, $fromId, $toType, $toId] = [$request->from_type, $request->from_id, $request->to_type, $request->to_id];
        $from = self::KINDS[$fromType];
        $to = self::KINDS[$toType];

        abort_if($fromType === $toType && $fromId === $toId, 422, 'The task is already on this board.');
        abort_unless($this->boardIds($fromType)->contains($fromId), 403);
        abort_unless($this->boardIds($toType)->contains($toId), 403, 'You don\'t have access to that ' . strtolower($to['label']) . '.');

        $destStatuses = $to['status']::where($to['scope_col'], $toId)->where('status', 'A')->get(['status_id', 'status_name']);
        abort_unless($destStatuses->contains('status_id', $request->status_id), 422, 'Pick a status column on the destination board.');

        // Whole source board once, then walk the subtree parents-first.
        $liveStatuses = $fromType === 'PROJECT' ? ['A'] : ['A', 'C'];
        $boardRows = $from['task']::where($from['scope_col'], $fromId)->whereIn('status', $liveStatuses)->get();
        $root = $boardRows->firstWhere('task_id', $request->task_id);
        abort_unless($root, 404);

        $subtree = collect([$root]);
        for ($frontier = collect([$root->task_id]); $frontier->isNotEmpty();) {
            $children = $boardRows->whereIn('parent_task_id', $frontier->all());
            $subtree = $subtree->merge($children);
            $frontier = $children->pluck('task_id');
        }
        $ids = $subtree->pluck('task_id');

        if ($fromType === 'PROJECT') {
            $canOpen = TrProjectTask::accessMap($boardRows, Auth::user());
            abort_unless($canOpen[$root->task_id] ?? false, 403, 'This task is locked. Only its assignees can open it.');
            abort_if($ids->contains(fn ($id) => !($canOpen[$id] ?? false)), 403,
                'This task has locked subtasks you can\'t open, so it can\'t be moved.');
        }

        // Root lands in the picked column; subtasks keep a same-named
        // column if the destination has one, else follow the root.
        $byName = $destStatuses->keyBy(fn ($s) => strtolower(trim($s->status_name)));
        $srcStatusNames = $from['status']::where($from['scope_col'], $fromId)->pluck('status_name', 'status_id');
        $statusFor = function ($row) use ($root, $request, $byName, $srcStatusNames) {
            if ($row->task_id === $root->task_id) {
                return $request->status_id;
            }
            $name = strtolower(trim((string) $srcStatusNames->get($row->status_id)));

            return $byName->get($name)?->status_id ?? $request->status_id;
        };

        $eligible = $this->eligibleUsernames($toType, $toId);
        $username = Auth::user()->username;
        $now = now();

        $dropped = $fromType === $toType
            ? $this->moveWithinKind($from, $toType, $fromId, $toId, $subtree, $root, $statusFor, $eligible, $username, $now)
            : $this->moveAcrossKinds($from, $to, $fromType, $toType, $fromId, $toId, $subtree, $root, $statusFor, $eligible, $username, $now);

        $newRootId = $dropped['root_id'];
        $noun = $root->parent_task_id ? 'subtask' : 'task';
        PmActivityLogger::log($toType, $toId, $newRootId, 'moved',
            "moved the {$noun} here from {$from['label']} \"" . $this->boardName($fromType, $fromId) . '"');
        PmActivityLogger::log($fromType, $fromId, null, 'moved',
            "moved the {$noun} \"{$root->task_name}\" to {$to['label']} \"" . $this->boardName($toType, $toId) . '"');

        foreach ([[$fromType, $fromId], [$toType, $toId]] as [$type, $id]) {
            if ($type === 'PROJECT') {
                $avg = TrProjectTask::where('project_id', $id)->where('status', 'A')->whereNull('parent_task_id')->avg('progress_percent');
                MsProject::where('project_id', $id)->update(['progress_percent' => $avg ?? 0]);
            }
        }

        $message = "Moved to {$to['label']} \"" . $this->boardName($toType, $toId) . '".';
        if ($dropped['people']) {
            $message .= ' ' . $dropped['people'] . ' assignee(s) not on that board were removed.';
        }

        return response()->json(['success' => true, 'message' => $message, 'task_id' => $newRootId]);
    }

    // Project→Project / Team→Team: same rows, new board.
    private function moveWithinKind(array $k, string $type, string $fromId, string $toId, Collection $subtree, $root, callable $statusFor, Collection $eligible, string $username, $now): array
    {
        $ids = $subtree->pluck('task_id');
        $dropped = 0;

        DB::connection('pgsql5')->transaction(function () use ($k, $type, $fromId, $toId, $subtree, $root, $statusFor, $eligible, $username, $now, $ids, &$dropped) {
            foreach ($subtree as $row) {
                $row->update([
                    $k['scope_col'] => $toId,
                    'parent_task_id' => $row->task_id === $root->task_id ? null : $row->parent_task_id,
                    'status_id' => $statusFor($row),
                    'updated_by' => $username,
                    'updated_at' => $now,
                ]);
            }

            $stale = $k['assignee']::whereIn('task_id', $ids)->where('status', 'A')->get()
                ->reject(fn ($a) => $eligible->contains(strtolower(trim($a->username))));
            $k['assignee']::whereIn('id', $stale->pluck('id'))->update(['status' => 'X']);
            $dropped = $stale->count();

            if ($type === 'PROJECT') {
                $linked = MsProject::where('project_id', $toId)->first()->teams->pluck('team_id');
                TrProjectTaskTeam::whereIn('task_id', $ids)->where('status', 'A')->whereNotIn('team_id', $linked)->update(['status' => 'X']);

                // A lock with nobody left who can get in would strand the task.
                $locked = $subtree->where('is_locked', true)->pluck('task_id');
                $effective = TrProjectTask::effectiveAssigneeMap($locked);
                foreach ($locked as $id) {
                    if ($effective->get($id)->isEmpty()) {
                        TrProjectTask::where('task_id', $id)->update(['is_locked' => false, 'locked_by' => null, 'locked_at' => null]);
                    }
                }
            }

            TrPmActivity::where('scope_type', $type)->where('scope_id', $fromId)->whereIn('task_id', $ids)->update(['scope_id' => $toId]);
        });

        return ['root_id' => $root->task_id, 'people' => $dropped];
    }

    // Team↔Project: re-create under the other table's ids, re-point
    // everything that hangs off the old ids, archive the originals.
    private function moveAcrossKinds(array $from, array $to, string $fromType, string $toType, string $fromId, string $toId, Collection $subtree, $root, callable $statusFor, Collection $eligible, string $username, $now): array
    {
        $idMap = [];
        foreach ($subtree as $row) {
            $auto = $this->nextAutonbr($to['doctype'], (int) $now->year, $now->format('m'), $username,
                $toType === 'PROJECT' ? 'Project Task' : 'Team Task');
            $idMap[$row->task_id] = $to['doctype'] . substr((string) $now->year, 2) . $now->format('m') . sprintf('%04d', $auto['next']);
        }
        $oldIds = array_keys($idMap);
        $dropped = 0;

        DB::connection('pgsql5')->transaction(function () use ($from, $to, $fromType, $toType, $fromId, $toId, $subtree, $root, $statusFor, $eligible, $username, $now, $idMap, $oldIds, &$dropped) {
            foreach ($subtree as $row) {
                $to['task']::create([
                    'task_id' => $idMap[$row->task_id],
                    $to['scope_col'] => $toId,
                    'parent_task_id' => $row->task_id === $root->task_id ? null : ($idMap[$row->parent_task_id] ?? null),
                    'task_name' => $row->task_name,
                    'task_description' => $row->task_description,
                    'start_date' => $row->start_date,
                    'end_date' => $row->end_date,
                    'status_id' => $statusFor($row),
                    'progress_percent' => $row->progress_percent,
                    // Project boards only show 'A' — a cancelled Team Task
                    // arrives active rather than vanishing.
                    'status' => $toType === 'TEAM' ? $row->status : 'A',
                    'cover_attachment_id' => $row->cover_attachment_id,
                    'created_by' => $row->created_by,
                    'created_at' => $row->created_at,
                    'updated_by' => $username,
                    'updated_at' => $now,
                ]);
            }

            foreach ($from['assignee']::whereIn('task_id', $oldIds)->where('status', 'A')->get() as $a) {
                if (!$eligible->contains(strtolower(trim($a->username)))) {
                    $dropped++;
                    continue;
                }
                $to['assignee']::create([
                    'task_id' => $idMap[$a->task_id],
                    'username' => $a->username,
                    'assigned_by' => $a->assigned_by,
                    'assigned_at' => $a->assigned_at,
                    'status' => 'A',
                ]);
            }

            foreach ($from['tag']::whereIn('task_id', $oldIds)->where('status', 'A')->get() as $t) {
                $to['tag']::create(['task_id' => $idMap[$t->task_id], 'tag_id' => $t->tag_id, 'status' => 'A']);
            }

            $from['task']::whereIn('task_id', $oldIds)->update([
                'status' => 'X',
                'deleted_by' => $username,
                'deleted_at' => $now,
            ]);

            foreach ($idMap as $old => $new) {
                TrPmActivity::where('scope_type', $fromType)->where('scope_id', $fromId)->where('task_id', $old)
                    ->update(['scope_type' => $toType, 'scope_id' => $toId, 'task_id' => $new]);
            }
        });

        // Chat, files and the cover image live on pgsql2, keyed by doctype + refnbr.
        DB::connection('pgsql2')->transaction(function () use ($from, $to, $idMap) {
            foreach ($idMap as $old => $new) {
                TrMessage::where('doctype', $from['doctype'])->where('refnbr', $old)
                    ->update(['doctype' => $to['doctype'], 'refnbr' => $new]);
                TrAttachment::where('doctype', $from['doctype'])->where('refnbr', $old)
                    ->update(['doctype' => $to['doctype'], 'refnbr' => $new]);
                TrAttachment::where('doctype', $from['doctype'] . 'COVER')->where('refnbr', $old)
                    ->update(['doctype' => $to['doctype'] . 'COVER', 'refnbr' => $new]);
            }
        });

        return ['root_id' => $idMap[$root->task_id], 'people' => $dropped];
    }
}
