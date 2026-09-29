<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrProjectTaskTag extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_project_task_tag';
    public $timestamps = false;

    protected $fillable = [
        'task_id',
        'tag_id',
        'status',
    ];
}
