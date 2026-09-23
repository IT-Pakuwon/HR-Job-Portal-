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
        'parent_task_id',
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

    public function taskStatus()
    {
        return MsTaskStatus::where('status_id', $this->status_id)->first();
    }

    // Self-referencing — a Task's children can themselves have children,
    // to unlimited depth (what used to be the separate, one-level-only
    // TrProjectTaskDetail "Subtask" is now just a Task with a parent_task_id).
    public function children()
    {
        return $this->hasMany(self::class, 'parent_task_id', 'task_id')
            ->where('status', 'A');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_task_id', 'task_id');
    }

    public function assignees()
    {
        return $this->hasMany(TrProjectTaskAssignee::class, 'task_id', 'task_id')
            ->where('status', 'A');
    }
}
