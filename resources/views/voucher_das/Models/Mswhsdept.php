<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mswhsdept extends Model
{
    protected $table = "vpl_ms_warehouse_dept";
    // protected $primaryKey = 'id';
    protected $fillable = [       
        'whs_id',
        'cpnyid',
        'department_id',
        'whs_type',
        'status',
        'created_user',
        'updated_user'        
    ];
}
