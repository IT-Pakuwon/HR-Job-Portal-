<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsProject;
use App\Models\MsProjectStatus;
use App\Models\MsTaskStatus;
use App\Models\MsTaskTag;
use App\Models\MsTeam;
use App\Models\TrFavorite;
use App\Models\TrProject;
use App\Models\TrProjectPic;
use App\Models\TrProjectStatusTeam;
use App\Models\TrProjectTag;
use App\Models\TrProjectTask;
use App\Models\TrProjectTeam;
use App\Models\TrTeamMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class PmProjectController extends Controller
{
    use HasAutonbr;

    private function hasProjectAccess(): bool
    {
        return (bool) Auth::user()?->hasRole('PROJECTACCESS');
    }

    // PROADMINACCESS holders see every Team/Project module-wide, bypassing
    // the usual "must be an explicit tr_team_member" scoping.
    private function hasAdminAccess(): bool
    {
        return (bool) Auth::user()?->hasRole('PROADMINACCESS');
    }

    // Org admins (ORGPROJECTACCESS, who manage Groups/Teams) can also browse
    // the Projects pages, even though only PROJECTACCESS holders can
    // actually create/use Projects.
    private function canBrowse(): bool
    {
        return $this->hasProjectAccess() || $this->hasAdminAccess() || (bool) Auth::user()?->hasRole('ORGPROJECTACCESS');
    }

    // Teams the current user can see Projects for. PROADMINACCESS holders
    // get every active Team; everyone else must hold PROJECTACCESS (module
    // access) and be an explicit tr_team_member (Captain or Member) of the
    // Team — Teams are not department-scoped like the old ms_group.
    private function myTeams()
    {
        $user = Auth::user();

        if ($this->hasAdminAccess()) {
            return MsTeam::where('status', 'A')->orderBy('team_name')->get();
        }

        if (!$this->hasProjectAccess()) {
            return collect();
        }

        $teamIds = TrTeamMember::where('status', 'A')
            ->whereRaw('lower(username) = ?', [strtolower(trim($user->username))])
            ->pluck('team_id');

        return MsTeam::where('status', 'A')
            ->whereIn('team_id', $teamIds)
            ->orderBy('team_name')
            ->get();
    }

    private function assertTeamAccess(MsProject $project): void
    {
        $teamIds = $this->myTeams()->pluck('team_id');
        $projectTeamIds = $project->teams->pluck('team_id');
        abort_unless($teamIds->intersect($projectTeamIds)->isNotEmpty() || Auth::user()->isAdmin(), 403);
    }

    // Union of member usernames across every Team linked to the Project —
    // a Project handled by more than one Team draws its eligible PIC/
    // assignee/mention pool from all of them, not just one.
    private function eligibleUsernames(MsProject $project)
    {
        $teamIds = $project->teams->pluck('team_id');

        return MsTeam::whereIn('team_id', $teamIds)->get()
            ->flatMap(fn ($t) => $t->memberUsers()->pluck('username'))
            ->map(fn ($u) => strtolower(trim($u)))
            ->unique();
    }

    public function index()
    {
        abort_unless($this->canBrowse(), 403);

        return view('pages.projectmanagement.projects', ['initialTab' => 'kanban', 'canCreateProject' => $this->hasProjectAccess()]);
    }

    public function kanban()
    {
        abort_unless($this->canBrowse(), 403);

        return view('pages.projectmanagement.projects', ['initialTab' => 'kanban', 'canCreateProject' => $this->hasProjectAccess()]);
    }

    public function gantt()
    {
        abort_unless($this->canBrowse(), 403);

        return view('pages.projectmanagement.projects', ['initialTab' => 'gantt', 'canCreateProject' => $this->hasProjectAccess()]);
    }

    // Starred Teams/Projects (username-scoped) — drives the sidebar's "pin
    // favorites to the top" behavior in boardData().
    private function myFavorites()
    {
        $username = strtolower(trim(Auth::user()->username));

        return TrFavorite::whereRaw('lower(username) = ?', [$username])->get();
    }

    // Kanban columns enabled for a scope: the given Team, or — when no
    // Team is selected — the union of every status enabled across all of
    // the user's Teams, so the combined "all my Teams" board shows
    // whatever any of them use.
    private function enabledStatusIds($teamId, $teamIds)
    {
        return TrProjectStatusTeam::where('status', 'A')
            ->whereIn('team_id', $teamId ? [$teamId] : $teamIds)
            ->pluck('status_id')
            ->unique();
    }

    // Tags share the same "master" list as Task tags (ms_task_tag) — typing
    // a new one on a Project both tags it and registers it for Tasks' (and
    // other Projects') pickers to reuse, exact typed text preserved and
    // matched/deduped via a normalized tag_id.
    private function syncProjectTags(string $projectId, array $tagNames, string $username, $now): void
    {
        TrProjectTag::where('project_id', $projectId)->update(['status' => 'X']);

        foreach (collect($tagNames)->map(fn ($t) => trim($t))->filter()->unique(fn ($t) => strtolower($t)) as $tagName) {
            $tagId = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $tagName));
            if ($tagId === '') {
                continue;
            }

            $tag = MsTaskTag::where('tag_id', $tagId)->first();

            if ($tag) {
                if ($tag->tag_name !== $tagName) {
                    $tag->update(['tag_name' => $tagName, 'updated_by' => $username, 'updated_at' => $now]);
                }
            } else {
                MsTaskTag::create([
                    'tag_id' => $tagId,
                    'tag_name' => $tagName,
                    'color' => '#6366F1',
                    'status' => 'A',
                    'created_by' => $username,
                    'created_at' => $now,
                ]);
            }

            $existingLink = TrProjectTag::where('project_id', $projectId)->where('tag_id', $tagId)->first();
            if ($existingLink) {
                $existingLink->update(['status' => 'A']);
            } else {
                TrProjectTag::create(['project_id' => $projectId, 'tag_id' => $tagId, 'status' => 'A']);
            }
        }
    }

    // Team list for a Project — deactivate-then-reactivate-or-create, same
    // dance as syncProjectTags()/syncProjectPics().
    private function syncProjectTeams(string $projectId, array $teamIds, string $addedBy, $now): void
    {
        TrProjectTeam::where('project_id', $projectId)->update(['status' => 'X']);

        foreach (collect($teamIds)->unique() as $teamId) {
            $existing = TrProjectTeam::where('project_id', $projectId)->where('team_id', $teamId)->first();

            if ($existing) {
                $existing->update(['status' => 'A']);
            } else {
                TrProjectTeam::create([
                    'project_id' => $projectId,
                    'team_id' => $teamId,
                    'added_by' => $addedBy,
                    'added_at' => $now,
                    'status' => 'A',
                ]);
            }
        }
    }

    // PIC list for a Project — a PIC entry can be a whole Team or an
    // individual person. Each $entries item is ['pic_type' => 'TEAM'|'USER',
    // 'ref_id' => team_id or username]. Same deactivate-then-reactivate-or-
    // create dance as syncProjectTags()/syncProjectTeams().
    private function syncProjectPics(string $projectId, array $entries, string $addedBy, $now): void
    {
        TrProjectPic::where('project_id', $projectId)->update(['status' => 'X']);

        $seen = collect();

        foreach ($entries as $entry) {
            $picType = strtoupper((string) ($entry['pic_type'] ?? ''));
            $refId = trim((string) ($entry['ref_id'] ?? ''));

            if (!in_array($picType, ['TEAM', 'USER'], true) || $refId === '') {
                continue;
            }

            $dedupeKey = $picType . ':' . strtolower($refId);
            if ($seen->contains($dedupeKey)) {
                continue;
            }
            $seen->push($dedupeKey);

            $existing = TrProjectPic::where('project_id', $projectId)
                ->where('pic_type', $picType)
                ->where('ref_id', $refId)
                ->first();

            if ($existing) {
                $existing->update(['status' => 'A']);
            } else {
                TrProjectPic::create([
                    'project_id' => $projectId,
                    'pic_type' => $picType,
                    'ref_id' => $refId,
                    'added_by' => $addedBy,
                    'added_at' => $now,
                    'status' => 'A',
                ]);
            }
        }
    }

    // Validates a raw pic entries payload against the Project's own
    // (about-to-be-saved) team_ids: a TEAM entry must be one of those
    // teams, a USER entry must be a member of at least one of them.
    private function validatePicEntries($entries, array $teamIds): void
    {
        $memberUsernames = MsTeam::whereIn('team_id', $teamIds)->get()
            ->flatMap(fn ($t) => $t->memberUsers()->pluck('username'))
            ->map(fn ($u) => strtolower(trim($u)));

        foreach ($entries as $entry) {
            $picType = strtoupper((string) ($entry['pic_type'] ?? ''));
            $refId = trim((string) ($entry['ref_id'] ?? ''));

            if ($picType === 'TEAM') {
                abort_unless(in_array($refId, $teamIds, true), 422, 'PIC Team must be one of the Project\'s linked Teams.');
            } else {
                abort_unless($memberUsernames->contains(strtolower($refId)), 422, 'PIC must be a member of one of the Project\'s linked Teams.');
            }
        }
    }

    // Resolve a TrProjectPic row into its display shape — a TEAM entry
    // shows the Team's name, a USER entry shows the person's name/avatar.
    // Shared by boardData() and detail().
    private function resolvePic(TrProjectPic $pic, $picUsers, $teamNames): array
    {
        if ($pic->isTeam()) {
            return [
                'pic_type' => 'TEAM',
                'team_id' => $pic->ref_id,
                'name' => $teamNames->get($pic->ref_id)?->team_name ?? $pic->ref_id,
            ];
        }

        $user = $picUsers->get(strtolower(trim($pic->ref_id)));

        return [
            'pic_type' => 'USER',
            'username' => $pic->ref_id,
            'name' => $user->name ?? $pic->ref_id,
            'photo_url' => $user?->profile_photo_url,
        ];
    }

    // Master tag palette — shared by the New Project / Add Card Tags picker.
    // Not scoped to a Project since it also needs to work while creating one.
    public function tags()
    {
        abort_unless($this->canBrowse(), 403);

        return response()->json(MsTaskTag::where('status', 'A')->orderBy('tag_name')->get(['tag_id', 'tag_name', 'color']));
    }

    // Shared data feed for both portfolio views (Kanban / Gantt).
    public function boardData(Request $request)
    {
        $teams = $this->myTeams();
        $teamIds = $teams->pluck('team_id');

        $teamId = $request->query('team_id');

        // A Project can be linked to more than one Team — scope to Projects
        // linked to (any of) the user's Teams, optionally narrowed to one.
        $linkedProjectIds = TrProjectTeam::where('status', 'A')
            ->whereIn('team_id', $teamId ? [$teamId] : $teamIds)
            ->pluck('project_id');

        $projects = MsProject::where('status', 'A')
            ->whereIn('project_id', $linkedProjectIds)
            ->orderBy('start_date')
            ->get();

        $projectTeams = TrProjectTeam::where('status', 'A')
            ->whereIn('project_id', $projects->pluck('project_id'))
            ->get()
            ->groupBy('project_id');
        $teamNames = MsTeam::whereIn('team_id', $projectTeams->flatten()->pluck('team_id')->unique())
            ->get(['team_id', 'team_name'])
            ->keyBy('team_id');

        $allStatuses = MsProjectStatus::where('status', 'A')->orderBy('sort_order')->get();
        $enabledIds = $this->enabledStatusIds($teamId, $teamIds);
        $statuses = $allStatuses->whereIn('status_id', $enabledIds->all())->values();
        $availableStatuses = $allStatuses->reject(fn ($s) => $enabledIds->contains($s->status_id))->values();

        $favorites = $this->myFavorites();
        $favoriteTeamIds = $favorites->where('fav_type', 'TEAM')->pluck('ref_id');
        $favoriteProjectIds = $favorites->where('fav_type', 'PROJECT')->pluck('ref_id');

        $pics = TrProjectPic::where('status', 'A')
            ->whereIn('project_id', $projects->pluck('project_id'))
            ->get()
            ->groupBy('project_id');

        $picUsernames = $pics->flatten()->where('pic_type', 'USER')->pluck('ref_id')->map(fn ($u) => strtolower(trim($u)))->unique();
        $picUsers = User::whereIn(DB::raw('lower(username)'), $picUsernames->all())
            ->get(['username', 'name'])
            ->keyBy(fn ($u) => strtolower(trim($u->username)));

        $projectTags = TrProjectTag::where('status', 'A')
            ->whereIn('project_id', $projects->pluck('project_id'))
            ->get()
            ->groupBy('project_id');
        $tagMaster = MsTaskTag::whereIn('tag_id', $projectTags->flatten()->pluck('tag_id')->unique())
            ->get(['tag_id', 'tag_name', 'color'])
            ->keyBy('tag_id');

        $teamsOut = $teams->map(fn ($t) => [
            'team_id' => $t->team_id,
            'team_name' => $t->team_name,
            'is_favorite' => $favoriteTeamIds->contains($t->team_id),
        ])->sortByDesc('is_favorite')->values();

        $projectsOut = $projects->map(fn ($p) => [
            'project_id' => $p->project_id,
            'teams' => ($projectTeams->get($p->project_id) ?? collect())->map(fn ($pt) => [
                'team_id' => $pt->team_id,
                'team_name' => $teamNames->get($pt->team_id)?->team_name,
            ])->values(),
            'project_name' => $p->project_name,
            'project_description' => $p->project_description,
            'start_date' => optional($p->start_date)->toDateString(),
            'end_date' => optional($p->end_date)->toDateString(),
            'status_id' => $p->status_id,
            'progress_percent' => (float) $p->progress_percent,
            'is_favorite' => $favoriteProjectIds->contains($p->project_id),
            'pics' => ($pics->get($p->project_id) ?? collect())
                ->map(fn ($pic) => $this->resolvePic($pic, $picUsers, $teamNames))
                ->values(),
            'tags' => ($projectTags->get($p->project_id) ?? collect())
                ->map(fn ($pt) => $tagMaster->get($pt->tag_id))
                ->filter()
                ->values(),
        ])->sortByDesc('is_favorite')->values();

        return response()->json([
            'teams' => $teamsOut,
            'statuses' => $statuses,
            'availableStatuses' => $availableStatuses,
            'projects' => $projectsOut,
        ]);
    }

    // Toggle a "My Favorite" star for a Team or Project in the sidebar.
    public function toggleFavorite(Request $request)
    {
        abort_unless($this->canBrowse(), 403);

        $request->validate([
            'fav_type' => ['required', 'string', 'in:TEAM,PROJECT'],
            'ref_id' => ['required', 'string'],
        ]);

        if ($request->fav_type === 'TEAM') {
            abort_unless($this->myTeams()->pluck('team_id')->contains($request->ref_id), 403);
        } else {
            $project = MsProject::where('project_id', $request->ref_id)->firstOrFail();
            $this->assertTeamAccess($project);
        }

        $username = Auth::user()->username;

        $existing = TrFavorite::whereRaw('lower(username) = ?', [strtolower(trim($username))])
            ->where('fav_type', $request->fav_type)
            ->where('ref_id', $request->ref_id)
            ->first();

        if ($existing) {
            $existing->delete();
            $isFavorite = false;
        } else {
            TrFavorite::create([
                'username' => $username,
                'fav_type' => $request->fav_type,
                'ref_id' => $request->ref_id,
                'created_at' => now(),
            ]);
            $isFavorite = true;
        }

        return response()->json(['success' => true, 'is_favorite' => $isFavorite]);
    }

    public function store(Request $request)
    {
        abort_unless($this->hasProjectAccess(), 403);

        $request->validate([
            'team_ids' => ['required', 'array', 'min:1'],
            'team_ids.*' => ['string', 'exists:pgsql5.ms_team,team_id'],
            'project_name' => ['required', 'string', 'max:255'],
            'project_description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'pics' => ['nullable', 'array'],
            'pics.*.pic_type' => ['required_with:pics', 'string', 'in:TEAM,USER'],
            'pics.*.ref_id' => ['required_with:pics', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
            'status_id' => ['nullable', 'string', 'exists:pgsql5.ms_project_status,status_id'],
        ]);

        $teamIds = collect($request->team_ids)->unique()->values();
        $myTeamIds = $this->myTeams()->pluck('team_id');
        abort_unless($teamIds->every(fn ($t) => $myTeamIds->contains($t)) || Auth::user()->isAdmin(), 403);

        $picEntries = $request->input('pics', []);
        $this->validatePicEntries($picEntries, $teamIds->all());

        $username = Auth::user()->username;
        $now = now();
        $statusId = $request->status_id
            ?? (MsProjectStatus::where('status_id', 'NOTSTARTED')->exists() ? 'NOTSTARTED' : null);

        $auto = $this->nextAutonbr('PRJ', (int) $now->year, $now->format('m'), $username, 'Project');
        $projectId = 'PRJ' . substr((string) $now->year, 2) . $now->format('m') . sprintf('%04d', $auto['next']);

        $project = DB::connection('pgsql5')->transaction(function () use ($request, $projectId, $username, $now, $statusId, $teamIds, $picEntries) {
            $project = MsProject::create([
                'project_id' => $projectId,
                'project_name' => $request->project_name,
                'project_description' => $request->project_description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status_id' => $statusId,
                'progress_percent' => 0,
                'status' => 'A',
                'created_by' => $username,
                'created_at' => $now,
            ]);

            $this->syncProjectTeams($projectId, $teamIds->all(), $username, $now);

            $this->syncProjectPics($projectId, $picEntries, $username, $now);

            $this->syncProjectTags($projectId, $request->input('tags', []), $username, $now);

            // Guarantee the Project's own status has a Kanban column on
            // every linked Team's board — matters the first time a brand
            // new Team (with no columns enabled yet) creates its first Project.
            if ($statusId) {
                foreach ($teamIds as $teamId) {
                    TrProjectStatusTeam::firstOrCreate(
                        ['status_id' => $statusId, 'team_id' => $teamId],
                        ['status' => 'A', 'created_by' => $username, 'created_at' => $now]
                    );
                }
            }

            // Seed default Task-board statuses for this Project (To Do / In
            // Progress / Done), matching the same idea as ms_project_status.
            foreach ([['TODO', 'To Do', '#9CA3AF', 0], ['INPROGRESS', 'In Progress', '#3B82F6', 1], ['DONE', 'Done', '#10B981', 2]] as [$id, $name, $color, $order]) {
                MsTaskStatus::create([
                    'status_id' => $id,
                    'project_id' => $projectId,
                    'status_name' => $name,
                    'color' => $color,
                    'sort_order' => $order,
                    'status' => 'A',
                    'created_by' => $username,
                    'created_at' => $now,
                ]);
            }

            return $project;
        });

        return response()->json(['success' => true, 'message' => 'Project created successfully', 'project_id' => $project->project_id]);
    }

    // Deep-link into a specific Project's detail modal — mirrors this app's
    // /showticket/{eid} convention: a Hashids-encoded numeric id in the URL
    // (not the raw ms_project.project_id business key), pre-opening the
    // modal on the same portfolio board page rather than a separate route.
    public function show(string $eid)
    {
        abort_unless($this->canBrowse(), 403);

        $id = Hashids::decode($eid)[0] ?? null;
        abort_if(!$id, 404);

        $project = MsProject::where('id', $id)->where('status', 'A')->firstOrFail();
        $this->assertTeamAccess($project);

        return view('pages.projectmanagement.projects', [
            'initialTab' => 'kanban',
            'canCreateProject' => $this->hasProjectAccess(),
            'openProjectId' => $project->project_id,
        ]);
    }

    // JSON data for the Project detail modal — full detail unlike boardData()'s
    // flat portfolio-card fields.
    public function detail(string $projectId)
    {
        $project = MsProject::where('project_id', $projectId)->firstOrFail();
        $this->assertTeamAccess($project);

        $myTeamIds = $this->myTeams()->pluck('team_id');
        $linkableProjectIds = TrProjectTeam::where('status', 'A')
            ->whereIn('team_id', $myTeamIds)
            ->pluck('project_id')
            ->unique()
            ->reject(fn ($id) => $id === $project->project_id);

        $linkableProjects = MsProject::where('status', 'A')
            ->whereIn('project_id', $linkableProjectIds)
            ->orderBy('project_name')
            ->get(['project_id', 'project_name']);

        $linkedProjects = $project->linkedProjects();

        $teamIds = $project->teams->pluck('team_id');
        $teams = MsTeam::whereIn('team_id', $teamIds)->get();
        $teamNames = $teams->keyBy('team_id');
        $eligibleUsers = $teams->flatMap(fn ($t) => $t->memberUsers())
            ->unique(fn ($u) => strtolower(trim($u->username)))
            ->values();

        $status = MsProjectStatus::where('status_id', $project->status_id)->first();

        $pics = TrProjectPic::where('project_id', $project->project_id)->where('status', 'A')->get();
        $picUsernames = $pics->where('pic_type', 'USER')->pluck('ref_id')->map(fn ($u) => strtolower(trim($u)));
        $picUsers = User::whereIn(DB::raw('lower(username)'), $picUsernames->all())
            ->get(['username', 'name'])
            ->keyBy(fn ($u) => strtolower(trim($u->username)));

        $tags = TrProjectTag::where('project_id', $project->project_id)->where('status', 'A')->get();
        $tagMaster = MsTaskTag::whereIn('tag_id', $tags->pluck('tag_id'))->get(['tag_id', 'tag_name', 'color'])->keyBy('tag_id');

        // Same "done" definition as the Sub Task tab's flat list
        // (progress_percent >= 100), restricted to top-level Tasks so the
        // header's "X of Y subtasks" agrees with recalcProjectProgress().
        $taskProgress = TrProjectTask::where('project_id', $project->project_id)
            ->where('status', 'A')
            ->whereNull('parent_task_id')
            ->pluck('progress_percent');

        return response()->json([
            'project_id' => $project->project_id,
            'eid' => Hashids::encode($project->id),
            'project_name' => $project->project_name,
            'project_description' => $project->project_description,
            'start_date' => optional($project->start_date)->format('d M Y'),
            'end_date' => optional($project->end_date)->format('d M Y'),
            'start_date_raw' => optional($project->start_date)->toDateString(),
            'end_date_raw' => optional($project->end_date)->toDateString(),
            'progress_percent' => (float) $project->progress_percent,
            'subtask_total' => $taskProgress->count(),
            'subtask_done' => $taskProgress->filter(fn ($p) => (float) $p >= 100)->count(),
            'teams' => $teams->map(fn ($t) => ['team_id' => $t->team_id, 'team_name' => $t->team_name])->values(),
            'status' => $status ? ['status_id' => $status->status_id, 'status_name' => $status->status_name, 'color' => $status->color] : null,
            'pics' => $pics->map(fn ($pic) => $this->resolvePic($pic, $picUsers, $teamNames))->values(),
            'tags' => $tags->map(fn ($t) => $tagMaster->get($t->tag_id))->filter()->values(),
            'linked_projects' => $linkedProjects->map(fn ($lp) => [
                'project_id' => $lp->project_id,
                'project_name' => $lp->project_name,
            ]),
            'linkable_projects' => $linkableProjects->map(fn ($lp) => [
                'project_id' => $lp->project_id,
                'project_name' => $lp->project_name,
            ]),
            'eligible_users' => $eligibleUsers->map(fn ($u) => ['username' => $u->username, 'name' => $u->name]),
        ]);
    }

    public function update(Request $request, string $projectId)
    {
        $project = MsProject::where('project_id', $projectId)->firstOrFail();
        $this->assertTeamAccess($project);

        $request->validate([
            'project_name' => ['required', 'string', 'max:255'],
            'project_description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status_id' => ['nullable', 'string', 'exists:pgsql5.ms_project_status,status_id'],
            'team_ids' => ['required', 'array', 'min:1'],
            'team_ids.*' => ['string', 'exists:pgsql5.ms_team,team_id'],
            'pics' => ['nullable', 'array'],
            'pics.*.pic_type' => ['required_with:pics', 'string', 'in:TEAM,USER'],
            'pics.*.ref_id' => ['required_with:pics', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
        ]);

        $teamIds = collect($request->team_ids)->unique()->values();
        $myTeamIds = $this->myTeams()->pluck('team_id');
        abort_unless($teamIds->every(fn ($t) => $myTeamIds->contains($t)) || Auth::user()->isAdmin(), 403);

        $picEntries = $request->input('pics', []);
        $this->validatePicEntries($picEntries, $teamIds->all());

        $username = Auth::user()->username;
        $now = now();

        DB::connection('pgsql5')->transaction(function () use ($project, $request, $projectId, $username, $now, $teamIds, $picEntries) {
            $project->update([
                'project_name' => $request->project_name,
                'project_description' => $request->project_description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status_id' => $request->status_id ?? $project->status_id,
                'updated_by' => $username,
                'updated_at' => $now,
            ]);

            $this->syncProjectTeams($projectId, $teamIds->all(), $username, $now);
            $this->syncProjectPics($projectId, $picEntries, $username, $now);
            $this->syncProjectTags($projectId, $request->input('tags', []), $username, $now);
        });

        return response()->json(['success' => true, 'message' => 'Project updated successfully']);
    }

    // Drag-and-drop status change on the portfolio Kanban.
    public function updateStatus(Request $request, string $projectId)
    {
        $project = MsProject::where('project_id', $projectId)->firstOrFail();
        $this->assertTeamAccess($project);

        $request->validate(['status_id' => ['required', 'string', 'exists:pgsql5.ms_project_status,status_id']]);

        $project->update([
            'status_id' => $request->status_id,
            'updated_by' => Auth::user()->username,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(string $projectId)
    {
        $project = MsProject::where('project_id', $projectId)->firstOrFail();
        $this->assertTeamAccess($project);

        $project->update(['status' => 'X', 'updated_by' => Auth::user()->username, 'updated_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Project archived successfully']);
    }

    // Global "master" status list ("+ Add status" on the portfolio Kanban).
    // The picker on the front end offers existing master entries, but also
    // accepts free text — matched here by a normalized status_id so "In
    // progress" and "in Progress" resolve to the same row. The exact text
    // last typed is what gets saved/kept as status_name (no case-
    // normalizing it away). Enabling it links it to the current Team scope
    // (or every one of the user's Teams, if no single Team is selected) via
    // tr_project_status_team — it doesn't touch other Teams' boards.
    public function storeStatus(Request $request)
    {
        abort_unless($this->hasProjectAccess(), 403);

        $request->validate([
            'status_name' => ['required', 'string', 'max:100'],
            'team_id' => ['nullable', 'string', 'exists:pgsql5.ms_team,team_id'],
        ]);

        $myTeamIds = $this->myTeams()->pluck('team_id');

        if ($request->team_id) {
            abort_unless($myTeamIds->contains($request->team_id), 403);
            $targetTeamIds = collect([$request->team_id]);
        } else {
            $targetTeamIds = $myTeamIds;
        }

        abort_if($targetTeamIds->isEmpty(), 422, 'No Team to add this status to.');

        $statusName = trim($request->status_name);
        $statusId = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $statusName));
        abort_if($statusId === '', 422, 'Status name must contain at least one letter or number.');

        $status = MsProjectStatus::where('status_id', $statusId)->first();

        if ($status) {
            if ($status->status_name !== $statusName) {
                $status->update([
                    'status_name' => $statusName,
                    'updated_by' => Auth::user()->username,
                    'updated_at' => now(),
                ]);
            }
        } else {
            $nextOrder = (int) MsProjectStatus::max('sort_order') + 1;

            $status = MsProjectStatus::create([
                'status_id' => $statusId,
                'status_name' => $statusName,
                'color' => $request->input('color', '#6366F1'),
                'sort_order' => $nextOrder,
                'status' => 'A',
                'created_by' => Auth::user()->username,
                'created_at' => now(),
            ]);
        }

        foreach ($targetTeamIds as $teamId) {
            TrProjectStatusTeam::firstOrCreate(
                ['status_id' => $statusId, 'team_id' => $teamId],
                ['status' => 'A', 'created_by' => Auth::user()->username, 'created_at' => now()]
            );
        }

        return response()->json(['success' => true, 'status' => $status]);
    }

    // ── Project-to-project linking ──────────────────────────────────────
    public function link(Request $request, string $projectId)
    {
        $project = MsProject::where('project_id', $projectId)->firstOrFail();
        $this->assertTeamAccess($project);

        $request->validate(['linked_project_id' => ['required', 'string', 'exists:pgsql5.ms_project,project_id', 'different:project_id']]);

        $linkedId = $request->linked_project_id;

        $exists = TrProject::where('status', 'A')
            ->where(function ($q) use ($projectId, $linkedId) {
                $q->where(['project_id' => $projectId, 'linked_project_id' => $linkedId])
                  ->orWhere(['project_id' => $linkedId, 'linked_project_id' => $projectId]);
            })->exists();

        if (!$exists) {
            TrProject::create([
                'project_id' => $projectId,
                'linked_project_id' => $linkedId,
                'status' => 'A',
                'created_by' => Auth::user()->username,
                'created_at' => now(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Projects linked successfully']);
    }

    public function unlink(Request $request, string $projectId, string $linkedProjectId)
    {
        $project = MsProject::where('project_id', $projectId)->firstOrFail();
        $this->assertTeamAccess($project);

        TrProject::where('status', 'A')
            ->where(function ($q) use ($projectId, $linkedProjectId) {
                $q->where(['project_id' => $projectId, 'linked_project_id' => $linkedProjectId])
                  ->orWhere(['project_id' => $linkedProjectId, 'linked_project_id' => $projectId]);
            })
            ->update(['status' => 'X', 'updated_by' => Auth::user()->username, 'updated_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Projects unlinked successfully']);
    }

    // @mention autocomplete for a Project's chat — eligible members across
    // every linked Team.
    public function mentionableUsers(string $projectId)
    {
        $project = MsProject::where('project_id', $projectId)->firstOrFail();
        $this->assertTeamAccess($project);

        $usernames = $this->eligibleUsernames($project)
            ->reject(fn ($u) => $u === strtolower(Auth::user()->username));

        $users = User::whereIn(DB::raw('lower(username)'), $usernames->all())->get(['username', 'name']);

        return response()->json($users->map(fn ($u) => ['username' => $u->username, 'name' => $u->name]));
    }
}
