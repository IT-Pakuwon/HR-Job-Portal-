<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrTeamMember extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_team_member';
    public $timestamps = false;

    protected $fillable = [
        'team_id',
        'username',
        'member_role',
        'added_by',
        'added_at',
        'status',
    ];

    public function team()
    {
        return $this->belongsTo(MsTeam::class, 'team_id', 'team_id');
    }
}
