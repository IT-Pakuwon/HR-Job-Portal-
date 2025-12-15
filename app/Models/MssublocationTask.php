<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MssublocationTask extends Model
{
    // protected $connection = 'mysql2';
    protected $table = "task_ms_sublocation";
   
    protected $fillable = [     
        'sublocation_id',
        'location_id',
        'cpnyid',
        'sublocation_descr',
        'status',
        'created_user',
        'updated_user'
    ];
    
}
