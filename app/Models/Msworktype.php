<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Msworktype extends Model
{
    // protected $connection = 'mysql2';
    protected $table = "task_ms_worktype";
   
    protected $fillable = [     
        'worktype_id',
        'cpnyid',
        'departementid',
        'worktype_descr',
        'worktype_type',
        'status',
        'created_user',
        'updated_user'
    ];
    
}
