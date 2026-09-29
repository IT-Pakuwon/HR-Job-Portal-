<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// A whole Team assigned as PIC on a Project Task — sits beside
// TrProjectTaskAssignee (individual people). See TrProjectTask::assigneeUsernames().
class TrProjectTaskTeam extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_project_task_team';
    public $timestamps = false;

    protected $fillable = [
        'task_id',
        'team_id',
        'assigned_by',
        'assigned_at',
        'status',
    ];

    public function task()
    {
        return $this->belongsTo(TrProjectTask::class, 'task_id', 'task_id');
    }

    public function team()
    {
        return $this->belongsTo(MsTeam::class, 'team_id', 'team_id');
    }
}
