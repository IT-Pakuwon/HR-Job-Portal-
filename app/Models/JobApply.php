<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApply extends Model
{    
    protected $connection = 'mysql3';
    protected $table = "hr_trx_job_apply";   
    protected $fillable = [  
        'docid',
        'jobid',
        'applicant_id',
        'apply_date',
        'apply_step',
        'status',
        'created_user',
        'updated_user',
        'completed_user'

    ];

   
}
    
