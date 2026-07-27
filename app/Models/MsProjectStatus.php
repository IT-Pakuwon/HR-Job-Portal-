<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsProjectStatus extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'ms_project_status';

    protected $fillable = [
        'status_id',
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
}
