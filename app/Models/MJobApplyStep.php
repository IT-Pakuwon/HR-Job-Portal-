<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MJobApplyStep extends Model
{    
    protected $connection = 'mysql3';
    protected $table = "hr_ms_job_step";   
    protected $fillable = [  
        'step_id',
        'step_descr',               
        'step_order',
        'type',
        'step_pic',
        'step_approve',
        'schedule',       
        'status',
        'created_user',
        'updated_user',
        'completed_user'

    ];

   
}
    
