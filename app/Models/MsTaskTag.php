<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsTaskTag extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'ms_task_tag';

    protected $fillable = [
        'tag_id',
        'tag_name',
        'color',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];
}
