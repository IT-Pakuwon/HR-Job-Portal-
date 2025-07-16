<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trworkorder extends Model
{
    // protected $connection = 'mysql2';
    protected $table = "task_trx_workorder";
   
    protected $fillable = [     
        'docid',
        'task_id',
        'wo_date',
        'cpnyid',
        'departementid',
        'wo_priority',
        'complaint_type',
        'work_type',
        'sub_work_type',
        'location_id',
        'sub_location_id',
        'work_start_date',
        'work_end_date',
        'work_description',
        'work_response',
        'status',
        'created_user',
        'updated_user',
        'completed_user'
    ];
    
}
