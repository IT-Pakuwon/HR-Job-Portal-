<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplyStep extends Model
{    
    protected $connection = 'mysql3';
    protected $table = "hr_trx_job_apply_step";   
    protected $fillable = [       
        'docid',
        'jobid',
        'applicant_id',
        'step_id',
        'step_order',
        'type',
        'step_pic',
        'step_approve',
        'aprvusername',
        'aprvuserdate',
        'status',
        'created_user',
        'updated_user',
        'completed_user'

    ];

   
}
    
