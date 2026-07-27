<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsDepartmentOpr extends Model
{
    protected $connection = 'pgsql2';
    protected $table = 'ms_department_opr';
    protected $fillable = [
        'department_opr_id',
        'cpny_id',
        'department_name',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];
}
