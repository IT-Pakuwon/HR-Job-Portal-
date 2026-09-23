<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsTeamTaskStatus extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'ms_team_task_status';

    protected $fillable = [
        'status_id',
        'team_id',
        'status_name',
        'color',
        'sort_order',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];

    public function team()
    {
        return $this->belongsTo(MsTeam::class, 'team_id', 'team_id');
    }
}
