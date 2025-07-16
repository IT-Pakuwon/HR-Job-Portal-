<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mscomplaint extends Model
{
    // protected $connection = 'mysql2';
    protected $table = "task_ms_complaint";
   
    protected $fillable = [     
        'complaintid',
        'cpnyid',
        'departementid',
        'complaint_descr',
        'complaint_type',
        'status',
        'created_user',
        'updated_user'
    ];
    
}
