<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsProjectStatus;
use App\Models\MsTeam;
use App\Models\TrProjectStatusTeam;
use App\Models\TrTeamMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class TeamController extends Controller
{
    use HasAutonbr;

    // Only Captains-to-be can create a Team.
    private function hasCaptainAccess(): bool
    {
        return (bool) Auth::user()?->hasRole('CAPTACCESS');
    }

    // Anyone reaching the module (Captain or existing Project users) can browse.
    private function canView(): bool
    {
        return $this->hasCaptainAccess() || (bool) Auth::user()?->hasRole('PROJECTACCESS');
    }

    private function canManage(MsTeam $team): bool
    {
        $user = Auth::user();

        return $team->isCaptain($user->username) || $user->isAdmin();
    }

    public function index()
    {
        abort_unless($this->canView(), 403);

        $canCreate = $this->hasCaptainAccess();

        return view('pages.team.all-team', compact('canCreate'));
    }

    // Same page as index(), but pre-opens a Team's detail modal — mirrors this
    // app's /showticket/{eid} convention: a Hashids-encoded numeric id in the
    // URL (not the raw ms_team.id or the human-readable team_id business key).
    public function show(string $eid)
    {
        abort_unless($this->canView(), 403);

        $id = Hashids::decode($eid)[0] ?? null;
        abort_if(!$id, 404);

        $team = MsTeam::where('id', $id)->where('status', 'A')->firstOrFail();

        $canCreate = $this->hasCaptainAccess();

        return view('pages.team.all-team', ['canCreate' => $canCreate, 'openTeamId' => $team->team_id]);
    }

    // JSON data for the read-only detail modal — full member list enriched
    // with ms_user info, unlike edit() which only returns bare usernames.
    public function detail(string $teamId)
    {
        $team = MsTeam::where('team_id', $teamId)->where('status', 'A')->firstOrFail();
        abort_unless($this->canView(), 403);

        $memberRows = $team->members()->get();
        $usernames = $memberRows->pluck('username')->map(fn ($u) => strtolower(trim($u)));

        $users = User::whereIn(DB::raw('lower(username)'), $usernames->all())
            ->get(['username', 'name', 'origin_cpny_id', 'origin_department_id', 'jabatan'])
            ->keyBy(fn ($u) => strtolower(trim($u->username)));

        $members = $memberRows->map(function ($m) use ($users) {
            $u = $users->get(strtolower(trim($m->username)));

            return [
                'username' => $m->username,
                'member_role' => $m->member_role,
                'name' => $u->name ?? $m->username,
                'cpny_id' => $u->origin_cpny_id ?? null,
                'department_id' => $u->origin_department_id ?? null,
                'jabatan' => $u->jabatan ?? null,
            ];
        })->sortByDesc(fn ($m) => $m['member_role'] === 'CAPTAIN')->values();

        return response()->json([
            'team_id' => $team->team_id,
            'eid' => Hashids::encode($team->id),
            'team_name' => $team->team_name,
            'team_description' => $team->team_description,
            'members' => $members,
            'member_count' => $members->count(),
            'created_by' => $team->created_by,
            'created_at' => optional($team->created_at)->toDateTimeString(),
            'can_manage' => $this->canManage($team),
        ]);
    }

    public function json()
    {
        abort_unless($this->canView(), 403);

        $teams = MsTeam::where('status', 'A')->orderBy('team_name')->get();

        $data = $teams->map(function ($team) {
            $captain = $team->captain();

            return [
                'team_id' => $team->team_id,
                'team_name' => $team->team_name,
                'team_description' => $team->team_description,
                'captain_username' => $captain?->username,
                'member_count' => $team->members()->count(),
                'can_manage' => $this->canManage($team),
                'created_by' => $team->created_by,
                'created_at' => optional($team->created_at)->toDateTimeString(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    // AJAX: live user search across ALL companies/departments — a Team is
    // deliberately not scoped like the old ms_group, so there is no
    // department-based candidate pool here, just a name/username search.
    public function searchUsers(Request $request)
    {
        abort_unless($this->canView(), 403);

        $term = trim((string) $request->query('q', ''));
        $excludeUsername = strtolower(trim((string) Auth::user()->username));

        $query = User::where('status', 'A')
            ->whereRaw('lower(username) <> ?', [$excludeUsername]);

        if ($term !== '') {
            $like = '%' . strtolower($term) . '%';
            $query->where(function ($q) use ($like) {
                $q->whereRaw('lower(name) like ?', [$like])
                    ->orWhereRaw('lower(username) like ?', [$like]);
            });
        }

        $total = $query->count();

        $users = $query->orderBy('name')->limit(30)
            ->get(['username', 'name', 'origin_cpny_id as cpny_id', 'origin_department_id as department_id', 'jabatan']);

        return response()->json(['data' => $users, 'total' => $total]);
    }

    public function store(Request $request)
    {
        abort_unless($this->hasCaptainAccess(), 403);

        $request->validate([
            'team_name' => ['required', 'string', 'max:255'],
            'team_description' => ['nullable', 'string'],
            'members' => ['nullable', 'array'],
            'members.*' => ['string', 'exists:pgsql2.ms_user,username'],
        ]);

        $username = Auth::user()->username;
        $now = now();

        $auto = $this->nextAutonbr('TEAM', (int) $now->year, $now->format('m'), $username, 'Team');
        $teamId = 'TEAM' . substr((string) $now->year, 2) . $now->format('m') . sprintf('%04d', $auto['next']);

        $members = collect($request->input('members', []))
            ->map(fn ($u) => trim($u))
            ->reject(fn ($u) => strtolower($u) === strtolower($username))
            ->unique(fn ($u) => strtolower($u));

        DB::connection('pgsql5')->transaction(function () use ($request, $teamId, $username, $now, $members) {
            MsTeam::create([
                'team_id' => $teamId,
                'team_name' => $request->team_name,
                'team_description' => $request->team_description,
                'status' => 'A',
                'created_by' => $username,
                'created_at' => $now,
            ]);

            TrTeamMember::create([
                'team_id' => $teamId,
                'username' => $username,
                'member_role' => 'CAPTAIN',
                'added_by' => $username,
                'added_at' => $now,
                'status' => 'A',
            ]);

            foreach ($members as $memberUsername) {
                TrTeamMember::create([
                    'team_id' => $teamId,
                    'username' => $memberUsername,
                    'member_role' => 'MEMBER',
                    'added_by' => $username,
                    'added_at' => $now,
                    'status' => 'A',
                ]);
            }

            // Give the new Team the standard Kanban columns to start with —
            // mirrors the default Task-board statuses seeded per Project.
            foreach (MsProjectStatus::where('status', 'A')->whereIn('status_id', ['NOTSTARTED', 'INPROGRESS', 'DONE'])->pluck('status_id') as $statusId) {
                TrProjectStatusTeam::firstOrCreate(
                    ['status_id' => $statusId, 'team_id' => $teamId],
                    ['status' => 'A', 'created_by' => $username, 'created_at' => $now]
                );
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Team created successfully',
            'team_id' => $teamId,
        ]);
    }

    public function edit(string $teamId)
    {
        $team = MsTeam::where('team_id', $teamId)->firstOrFail();
        abort_unless($this->canView(), 403);

        return response()->json([
            'team_id' => $team->team_id,
            'team_name' => $team->team_name,
            'team_description' => $team->team_description,
            'captain_username' => $team->captain()?->username,
            'members' => $team->members()->where('member_role', 'MEMBER')->pluck('username'),
            'can_manage' => $this->canManage($team),
        ]);
    }

    public function update(Request $request, string $teamId)
    {
        $team = MsTeam::where('team_id', $teamId)->firstOrFail();
        abort_unless($this->canManage($team), 403);

        $request->validate([
            'team_name' => ['required', 'string', 'max:255'],
            'team_description' => ['nullable', 'string'],
            'members' => ['nullable', 'array'],
            'members.*' => ['string', 'exists:pgsql2.ms_user,username'],
        ]);

        $username = Auth::user()->username;
        $now = now();
        $captainUsername = $team->captain()?->username;

        $members = collect($request->input('members', []))
            ->map(fn ($u) => trim($u))
            ->reject(fn ($u) => strtolower($u) === strtolower((string) $captainUsername))
            ->unique(fn ($u) => strtolower($u));

        DB::connection('pgsql5')->transaction(function () use ($team, $request, $username, $now, $members) {
            $team->update([
                'team_name' => $request->team_name,
                'team_description' => $request->team_description,
                'updated_by' => $username,
                'updated_at' => $now,
            ]);

            // Keep the Captain row untouched — only resync regular Members.
            TrTeamMember::where('team_id', $team->team_id)
                ->where('member_role', 'MEMBER')
                ->update(['status' => 'X']);

            foreach ($members as $memberUsername) {
                $existing = TrTeamMember::where('team_id', $team->team_id)
                    ->where('username', $memberUsername)
                    ->first();

                if ($existing) {
                    $existing->update(['status' => 'A', 'member_role' => 'MEMBER']);
                } else {
                    TrTeamMember::create([
                        'team_id' => $team->team_id,
                        'username' => $memberUsername,
                        'member_role' => 'MEMBER',
                        'added_by' => $username,
                        'added_at' => $now,
                        'status' => 'A',
                    ]);
                }
            }
        });

        return response()->json(['success' => true, 'message' => 'Team updated successfully']);
    }

    public function destroy(string $teamId)
    {
        $team = MsTeam::where('team_id', $teamId)->firstOrFail();
        abort_unless($this->canManage($team), 403);

        $team->update([
            'status' => 'X',
            'updated_by' => Auth::user()->username,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Team archived successfully']);
    }
}
