<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrSto extends Model
{   
    protected $connection = 'pgsql3';
    protected $table = 'hr_ms_sto';     
    protected $fillable = [     
        'sto_id',
        'sto_date',
        'cpnyid',
        'departementid',
        'user',
        'status',
        'created_user',
        'updated_user',
        'completed_user' 
    ];
   
}
