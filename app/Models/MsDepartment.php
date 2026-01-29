<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsDepartment extends Model
{
    
    protected $connection = 'pgsql2';
    protected $table = "ms_department";
 
    protected $fillable = [       
        'department_id', 'department_name', 'department_fin_id', 'department_hr_id','status', 'created_by',
        'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at'
    ];
}
