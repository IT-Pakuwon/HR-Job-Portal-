<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrBASTRating extends Model
{
    // protected $connection = 'mysql2';
    protected $connection = 'pgsql';
    protected $table = "tr_bast_rating";
   
    protected $fillable = [     
        'bast_id' , 'rating_id', 'rating_no' , 'rating_name' , 'rating_descr' , 'rating_score' , 'status' , 
        'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at'    
    ];    
}
