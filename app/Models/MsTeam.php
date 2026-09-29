<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MsTeam extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'ms_team';

    protected $fillable = [
        'team_id',
        'team_name',
        'team_description',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];

    public function taskStatuses()
    {
        return $this->hasMany(MsTeamTaskStatus::class, 'team_id', 'team_id')
            ->where('status', 'A')
            ->orderBy('sort_order');
    }

    public function tasks()
    {
        return $this->hasMany(TrTeamTask::class, 'team_id', 'team_id');
    }

    public function members()
    {
        return $this->hasMany(TrTeamMember::class, 'team_id', 'team_id')
            ->where('status', 'A');
    }

    public function captain()
    {
        return $this->members()->where('member_role', 'CAPTAIN')->first();
    }

    public function isCaptain(string $username): bool
    {
        return $this->members()
            ->where('member_role', 'CAPTAIN')
            ->whereRaw('lower(username) = ?', [strtolower(trim($username))])
            ->exists();
    }

    // Members joined against ms_user for display (name, cpny_id, department_id, ...).
    public function memberUsers()
    {
        $usernames = $this->members()->pluck('username')
            ->map(fn ($u) => strtolower(trim($u)));

        return User::whereIn(DB::raw('lower(username)'), $usernames->all())->get();
    }

    // Who may open this Team's board and its Message thread: its members,
    // plus admin / PROADMINACCESS.
    public function isAccessibleBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->isPrimaryAdmin()
            || $user->hasRole('PROADMINACCESS')
            || $this->members()->whereRaw('lower(trim(username)) = ?', [strtolower(trim($user->username))])->exists();
    }

    // Team-wide Message thread (tr_message / tr_attachment doctype 'TEAM',
    // refnbr = team_id) — members only. Called via
    // TrProjectTask::abortUnlessAccessible(), the shared comments/attachments guard.
    public static function abortUnlessChatAccessible(string $teamId): void
    {
        $team = self::where('team_id', $teamId)->where('status', 'A')->first();

        abort_unless($team && $team->isAccessibleBy(auth()->user()), 403, 'Only members of this Team can use its messages.');
    }
}
