<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mslocation extends Model
{
    // protected $connection = 'mysql2';
    protected $table = "task_ms_location";
   
    protected $fillable = [     
        'location_id',
        'cpnyid',
        'location_descr',
        'status',
        'created_user',
        'updated_user'
    ];
    
}
