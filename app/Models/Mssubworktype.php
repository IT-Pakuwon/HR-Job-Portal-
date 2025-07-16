<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mssubworktype extends Model
{
    // protected $connection = 'mysql2';
    protected $table = "task_ms_subworktype";
   
    protected $fillable = [     
        'subworktype_id',
        'worktype_id',
        'cpnyid',
        'departementid',
        'subworktype_descr',
        'subworktype_type',
        'status',
        'created_user',
        'updated_user'
    ];
    
}
