<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepartmentHR extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql3';
    protected $table = "hr_ms_department";

    protected $fillable = [
        'department_id',
        'division_id',
        'department_name',
        'group_cpny_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
