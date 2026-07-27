<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsTaskStatus extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'ms_task_status';

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
