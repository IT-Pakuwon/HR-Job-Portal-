<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrProjectTaskStatus extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_project_task_status';
    public $timestamps = false;

    protected $fillable = [
        'status_id',
        'project_id',
        'status',
        'created_by',
        'created_at',
    ];
}
