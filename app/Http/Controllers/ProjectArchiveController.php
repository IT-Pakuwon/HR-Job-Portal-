<?php

namespace App\Http\Controllers;

use App\Models\MsProject;
use App\Models\MsProjectTaskStatus;
use App\Models\MsTeam;
use App\Models\MsTeamTaskStatus;
use App\Models\TrProjectTask;
use App\Models\TrProjectTeam;
use App\Models\TrTeamTask;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Global Settings > Project Setup — admin-only recovery for Projects and
// Tasks soft-archived (status 'X') from the Projects module, plus
// cancelled ('C') Team Tasks so they can be brought back from one place.
class ProjectArchiveController extends Controller
{
    public function projects()
    {
        return view('pages.project_setup.project_archive');
    }

    public function tasks()
    {
        return view('pages.project_setup.task_archive');
    }

    // username => display name, for "Archived by" / "Created by" columns.
    private function userNames($usernames)
    {
        $keys = collect($usernames)->filter()->map(fn ($u) => strtolower(trim($u)))->unique();

        return User::whereIn(DB::raw('lower(username)'), $keys->all())
            ->get(['username', 'name'])
            ->mapWithKeys(fn ($u) => [strtolower(trim($u->username)) => $u->name]);
    }

    public function projectsJson()
    {
        $projects = MsProject::where('status', 'X')->orderByDesc('updated_at')->get();

        $projectTeams = TrProjectTeam::where('status', 'A')
            ->whereIn('project_id', $projects->pluck('project_id'))
            ->get()
            ->groupBy('project_id');
        $teamNames = MsTeam::whereIn('team_id', $projectTeams->flatten()->pluck('team_id')->unique())
            ->pluck('team_name', 'team_id');

        $names = $this->userNames($projects->pluck('updated_by')->merge($projects->pluck('created_by')));
        $name = fn ($u) => $u ? ($names->get(strtolower(trim($u))) ?? $u) : null;

        $archivedTasks = TrProjectTask::where('status', 'X')
            ->whereIn('project_id', $projects->pluck('project_id'))
            ->get(['project_id', 'updated_at'])
            ->groupBy('project_id');

        return response()->json($projects->map(fn ($p) => [
            'project_id' => $p->project_id,
            'project_name' => $p->project_name,
            'teams' => ($projectTeams->get($p->project_id) ?? collect())
                ->map(fn ($pt) => $teamNames->get($pt->team_id) ?? $pt->team_id)
                ->values(),
            'task_count' => ($archivedTasks->get($p->project_id) ?? collect())
                ->filter(fn ($t) => $this->archivedWithProject($t, $p))
                ->count(),
            'created_by' => $name($p->created_by),
            'archived_by' => $name($p->updated_by),
            'archived_at' => optional($p->updated_at)->format('d M Y H:i'),
        ])->values());
    }

    public function restoreProject(string $projectId)
    {
        $project = MsProject::where('project_id', $projectId)->where('status', 'X')->firstOrFail();

        // Tasks archived together with the Project (PmProjectController::
        // destroy()) come back with it; ones archived on their own before
        // that stay archived.
        $tasks = TrProjectTask::where('project_id', $projectId)->where('status', 'X')->get()
            ->filter(fn ($t) => $this->archivedWithProject($t, $project));

        // Same fallback as restoreTask() for a column deleted in the meantime.
        $activeStatusIds = MsProjectTaskStatus::where('project_id', $projectId)->where('status', 'A')
            ->orderBy('sort_order')->pluck('status_id');
        $fallbackStatusId = $activeStatusIds->first();

        $username = Auth::user()->username;
        $now = now();

        DB::connection('pgsql5')->transaction(function () use ($project, $tasks, $activeStatusIds, $fallbackStatusId, $username, $now) {
            $project->update(['status' => 'A', 'updated_by' => $username, 'updated_at' => $now]);

            foreach ($tasks as $task) {
                $task->update([
                    'status' => 'A',
                    'status_id' => $activeStatusIds->contains($task->status_id) ? $task->status_id : $fallbackStatusId,
                    'updated_by' => $username,
                    'updated_at' => $now,
                ]);
            }
        });

        $this->recalcProjectProgress($projectId);

        return response()->json([
            'success' => true,
            'message' => $tasks->count() ? "Project and {$tasks->count()} task(s) restored" : 'Project restored',
        ]);
    }

    // A Task archived in the same cascade as its (archived) Project — see
    // PmProjectController::destroy(), which stamps both with one updated_at.
    private function archivedWithProject($task, $project): bool
    {
        return $project->status === 'X'
            && $task->updated_at
            && optional($project->updated_at)->equalTo($task->updated_at);
    }

    // Same rollup as PmTaskController::recalcProjectProgress().
    private function recalcProjectProgress(string $projectId): void
    {
        $avg = TrProjectTask::where('project_id', $projectId)
            ->where('status', 'A')
            ->whereNull('parent_task_id')
            ->avg('progress_percent');
        MsProject::where('project_id', $projectId)->update(['progress_percent' => $avg ?? 0]);
    }

    // Only "root" archived Tasks are listed — ones whose parent is still
    // alive (or that have no parent). Descendants archived in the same
    // cascade come back with their root on restore instead of cluttering
    // the list (restoring one alone would orphan it under an archived parent).
    private function archivedRoots($model)
    {
        $archived = $model::where('status', 'X')->orderByDesc('updated_at')->get();
        $archivedIds = $archived->pluck('task_id')->flip();

        return [$archived, $archived->filter(fn ($t) => !$t->parent_task_id || !$archivedIds->has($t->parent_task_id))->values()];
    }

    // The root plus every descendant archived in the same cascade (same
    // updated_at) — a descendant archived separately earlier stays archived.
    private function cascadeIds($all, $root)
    {
        $ids = collect([$root->task_id]);
        $frontier = collect([$root->task_id]);

        while ($frontier->isNotEmpty()) {
            $children = $all->whereIn('parent_task_id', $frontier)
                ->filter(fn ($t) => optional($t->updated_at)->equalTo($root->updated_at))
                ->pluck('task_id');
            $ids = $ids->merge($children);
            $frontier = $children;
        }

        return $ids->unique()->values();
    }

    public function tasksJson()
    {
        [$projectAll, $projectRoots] = $this->archivedRoots(TrProjectTask::class);
        [$teamAll, $teamRoots] = $this->archivedRoots(TrTeamTask::class);

        // Cancel only exists on Team Tasks (TeamTaskController::cancel()) and
        // never cascades, so each cancelled row stands on its own.
        $teamCancelled = TrTeamTask::where('status', 'C')->orderByDesc('updated_at')->get();

        $projectNames = MsProject::whereIn('project_id', $projectRoots->pluck('project_id')->unique())
            ->get(['project_id', 'project_name', 'status', 'updated_at'])->keyBy('project_id');

        // Tasks archived together with their Project are recovered from
        // Project Archive as one unit, so they aren't listed here.
        $projectRoots = $projectRoots->reject(function ($t) use ($projectNames) {
            $project = $projectNames->get($t->project_id);

            return $project && $this->archivedWithProject($t, $project);
        })->values();
        $teamNames = MsTeam::whereIn('team_id', $teamRoots->pluck('team_id')->merge($teamCancelled->pluck('team_id'))->unique())
            ->get(['team_id', 'team_name', 'status'])->keyBy('team_id');

        $names = $this->userNames($projectRoots->pluck('updated_by')
            ->merge($teamRoots->pluck('updated_by'))
            ->merge($teamCancelled->pluck('updated_by')));
        $name = fn ($u) => $u ? ($names->get(strtolower(trim($u))) ?? $u) : null;

        $rows = $projectRoots->map(function ($t) use ($projectAll, $projectNames, $name) {
            $container = $projectNames->get($t->project_id);

            return [
                'kind' => 'ARCHIVED',
                'source' => 'PROJECT',
                'container_id' => $t->project_id,
                'container_name' => $container->project_name ?? $t->project_id,
                'container_archived' => ($container->status ?? null) === 'X',
                'task_id' => $t->task_id,
                'task_name' => $t->task_name,
                'is_subtask' => (bool) $t->parent_task_id,
                'subtask_count' => $this->cascadeIds($projectAll, $t)->count() - 1,
                'archived_by' => $name($t->updated_by),
                'archived_at' => optional($t->updated_at)->format('d M Y H:i'),
                'archived_ts' => optional($t->updated_at)->timestamp,
            ];
        })->merge($teamRoots->map(function ($t) use ($teamAll, $teamNames, $name) {
            $container = $teamNames->get($t->team_id);

            return [
                'kind' => 'ARCHIVED',
                'source' => 'TEAM',
                'container_id' => $t->team_id,
                'container_name' => $container->team_name ?? $t->team_id,
                'container_archived' => ($container->status ?? null) === 'X',
                'task_id' => $t->task_id,
                'task_name' => $t->task_name,
                'is_subtask' => (bool) $t->parent_task_id,
                'subtask_count' => $this->cascadeIds($teamAll, $t)->count() - 1,
                'archived_by' => $name($t->updated_by),
                'archived_at' => optional($t->updated_at)->format('d M Y H:i'),
                'archived_ts' => optional($t->updated_at)->timestamp,
            ];
        }))->merge($teamCancelled->map(function ($t) use ($teamNames, $name) {
            $container = $teamNames->get($t->team_id);

            return [
                'kind' => 'CANCELLED',
                'source' => 'TEAM',
                'container_id' => $t->team_id,
                'container_name' => $container->team_name ?? $t->team_id,
                'container_archived' => ($container->status ?? null) === 'X',
                'task_id' => $t->task_id,
                'task_name' => $t->task_name,
                'is_subtask' => (bool) $t->parent_task_id,
                'subtask_count' => 0,
                'archived_by' => $name($t->updated_by),
                'archived_at' => optional($t->updated_at)->format('d M Y H:i'),
                'archived_ts' => optional($t->updated_at)->timestamp,
            ];
        }))->sortByDesc('archived_ts')->values();

        return response()->json($rows);
    }

    public function restoreTask(string $source, string $taskId)
    {
        abort_unless(in_array($source, ['PROJECT', 'TEAM'], true), 404);

        $isProject = $source === 'PROJECT';
        $model = $isProject ? TrProjectTask::class : TrTeamTask::class;
        $scopeCol = $isProject ? 'project_id' : 'team_id';

        // A cancelled Team Task was never hidden — just un-cancel that one
        // row, same as the board's own Restore toggle.
        $cancelled = $isProject ? null : TrTeamTask::where('task_id', $taskId)->where('status', 'C')->first();
        if ($cancelled) {
            $cancelled->update(['status' => 'A', 'updated_by' => Auth::user()->username, 'updated_at' => now()]);

            return response()->json(['success' => true, 'message' => 'Task un-cancelled']);
        }

        $root = $model::where('task_id', $taskId)->where('status', 'X')->firstOrFail();
        $all = $model::where($scopeCol, $root->{$scopeCol})->where('status', 'X')->get();
        $ids = $this->cascadeIds($all, $root);

        // Its status column may have been deleted since — fall back to the
        // board's first column so the restored Task actually shows up.
        $statusModel = $isProject ? MsProjectTaskStatus::class : MsTeamTaskStatus::class;
        $activeStatusIds = $statusModel::where($scopeCol, $root->{$scopeCol})->where('status', 'A')
            ->orderBy('sort_order')->pluck('status_id');
        $fallbackStatusId = $activeStatusIds->first();

        $username = Auth::user()->username;
        $now = now();

        DB::connection('pgsql5')->transaction(function () use ($model, $all, $ids, $activeStatusIds, $fallbackStatusId, $username, $now) {
            foreach ($all->whereIn('task_id', $ids) as $task) {
                $task->update([
                    'status' => 'A',
                    'status_id' => $activeStatusIds->contains($task->status_id) ? $task->status_id : $fallbackStatusId,
                    'updated_by' => $username,
                    'updated_at' => $now,
                ]);
            }
        });

        if ($isProject) {
            $this->recalcProjectProgress($root->project_id);
        }

        return response()->json([
            'success' => true,
            'message' => $ids->count() > 1 ? 'Task and ' . ($ids->count() - 1) . ' subtask(s) restored' : 'Task restored',
        ]);
    }
}
