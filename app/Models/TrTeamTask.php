<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrTeamTask extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_team_task';

    protected $fillable = [
        'task_id',
        'team_id',
        'parent_task_id',
        'task_name',
        'task_description',
        'start_date',
        'end_date',
        'status_id',
        'progress_percent',
        'status',
        'cover_attachment_id',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function team()
    {
        return $this->belongsTo(MsTeam::class, 'team_id', 'team_id');
    }

    // Self-referencing, unlimited depth — same shape as TrProjectTask.
    public function children()
    {
        return $this->hasMany(self::class, 'parent_task_id', 'task_id')
            ->where('status', 'A');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_task_id', 'task_id');
    }

    // status_id is only unique combined with team_id — same reasoning as
    // TrProjectTask::taskStatus().
    public function taskStatus()
    {
        return MsTeamTaskStatus::where('team_id', $this->team_id)
            ->where('status_id', $this->status_id)
            ->first();
    }

    public function assignees()
    {
        return $this->hasMany(TrTeamTaskAssignee::class, 'task_id', 'task_id')
            ->where('status', 'A');
    }
}
