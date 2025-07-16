<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trwoworker extends Model
{
    // protected $connection = 'mysql2';
    protected $table = "task_trx_workorder_worker";
   
    protected $fillable = [     
        'docid',
        'worker',
        'worker_start_date',
        'worker_end_date',
        'worker_rating',
        'worker_response',
        'worker_comment',
        'status',
        'created_user',
        'updated_user',
        'completed_user'
    ];
    
}
