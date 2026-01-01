<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsActivity extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_activity";
    
    protected $fillable = [       
        'activity_id', 'cpny_id', 'activity_descr', 'activity_type', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at'
    ];

   

}
