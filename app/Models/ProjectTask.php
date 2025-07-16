<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTask extends Model
{
    // protected $connection = 'mysql2';
    protected $table = "task_trx_task";
   
    protected $fillable = [     
        'docid',
        'taskdate',
        'tasktype',
        'cpnyid',
        'departementid',
        'taskpriority',
        'summary',
        'description',
        'participant',
        'assign',
        'startdate',
        'enddate',
        'duedate',
        'status',
        'created_user',
        'updated_user'
    ];
    
}
