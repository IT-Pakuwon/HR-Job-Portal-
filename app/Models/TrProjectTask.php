<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrProjectTask extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_project_task';

    protected $fillable = [
        'task_id',
        'project_id',
        'task_name',
        'task_description',
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

    public function project()
    {
        return $this->belongsTo(MsProject::class, 'project_id', 'project_id');
    }

    // status_id is only unique combined with project_id (each Project has
    // its own status vocabulary) — a plain belongsTo on status_id alone
    // would risk matching another project's status row of the same id.
    public function taskStatus()
    {
        return MsTaskStatus::where('project_id', $this->project_id)
            ->where('status_id', $this->status_id)
            ->first();
    }

    public function subtasks()
    {
        return $this->hasMany(TrProjectTaskDetail::class, 'task_id', 'task_id');
    }

    public function assignees()
    {
        return $this->hasMany(TrProjectTaskAssignee::class, 'task_id', 'task_id')
            ->whereNull('task_detail_id')
            ->where('status', 'A');
    }
}
