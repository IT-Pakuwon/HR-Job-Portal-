<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentHR extends Model
{
    protected $connection = 'mysql3';
    protected $table = "hr_ms_department";

    protected $fillable = [
        'department_id',
        'division_id',
        'department_name',
        'status',
        'created_user',
        'updated_user',
        'completed_user',
        'completed_at',
    ];
}
