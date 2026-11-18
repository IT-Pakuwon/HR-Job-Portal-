<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsProjectTaskStatus extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'ms_project_task_status';

    // The columns every new Team AND every new Project starts its task board
    // with — one list so the two can never drift apart again. [id, name,
    // color, sort_order]. "Closed", not "Archive": the card's Archive button
    // hides a task entirely (status 'X') and never moves it into a column.
    public const DEFAULTS = [
        ['TODO', 'To Do', '#9CA3AF', 0],
        ['INPROGRESS', 'In Progress', '#3B82F6', 1],
        ['DONE', 'Done', '#10B981', 2],
        ['CLOSED', 'Closed', '#6B7280', 3],
    ];

    protected $fillable = [
        'status_id',
        'project_id',
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

    public function project()
    {
        return $this->belongsTo(MsProject::class, 'project_id', 'project_id');
    }
}
