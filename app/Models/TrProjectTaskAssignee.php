<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrProjectTaskAssignee extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_project_task_assignee';
    public $timestamps = false;

    protected $fillable = [
        'task_id',
        'username',
        'assigned_by',
        'assigned_at',
        'status',
    ];

    public function task()
    {
        return $this->belongsTo(TrProjectTask::class, 'task_id', 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }
}
