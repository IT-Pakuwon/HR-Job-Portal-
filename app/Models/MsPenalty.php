<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsPenalty extends Model
{
    // protected $connection = 'mysql2';
    protected $connection = 'pgsql';
    protected $table = "ms_penalty";
   
    protected $fillable = [     
        'penalty_id' , 'min_amount' , 'max_amount' , 'penalty' , 'max_percent_amount' , 'status' , 
        'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at'
    ];    
}
