<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentFin extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_department_fin";

    protected $fillable = [
        'department_fin_id',
        'cpny_id',
        'department_name',
        'status',
        'created_by',
        'updated_by'
    ];
}
