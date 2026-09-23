<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrProjectTaskDetail extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_project_task_detail';

    protected $fillable = [
        'task_detail_id',
        'task_id',
        'subtask_name',
        'subtask_description',
        'start_date',
        'end_date',
        'status_id',
        'progress_percent',
        'status',
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

    public function task()
    {
        return $this->belongsTo(TrProjectTask::class, 'task_id', 'task_id');
    }

    // Same reasoning as TrProjectTask::taskStatus() — status_id is only
    // unique combined with the parent task's project_id.
    public function taskStatus()
    {
        $projectId = $this->task?->project_id;

        return $projectId
            ? MsTaskStatus::where('project_id', $projectId)->where('status_id', $this->status_id)->first()
            : null;
    }

    public function assignees()
    {
        return $this->hasMany(TrProjectTaskAssignee::class, 'task_detail_id', 'task_detail_id')
            ->where('status', 'A');
    }
}
