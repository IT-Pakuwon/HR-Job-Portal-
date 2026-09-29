<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrProjectTeam extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_project_team';
    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'team_id',
        'added_by',
        'added_at',
        'status',
    ];

    public function project()
    {
        return $this->belongsTo(MsProject::class, 'project_id', 'project_id');
    }

    public function team()
    {
        return $this->belongsTo(MsTeam::class, 'team_id', 'team_id');
    }
}
