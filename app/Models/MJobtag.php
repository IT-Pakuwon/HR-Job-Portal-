<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MJobtag extends Model
{
    protected $table = "hr_ms_job_tags"; 
    protected $fillable = [
        'job_tags',        
        'status',  
        'created_user',       
        'updated_user'       
    ];
}
