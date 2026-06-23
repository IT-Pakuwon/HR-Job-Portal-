<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mswhsusage extends Model
{
    protected $table = "vpl_ms_warehouse_usage";
    // protected $primaryKey = 'id';
    protected $fillable = [       
        'whs_id',
        'cpnyid',
        'department_id',       
        'status',
        'created_user',
        'updated_user'        
    ];
}
