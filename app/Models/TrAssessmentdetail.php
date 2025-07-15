<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrAssessmentdetail extends Model
{    
    protected $connection = 'mysql3';
    protected $table = "hr_trx_interview_assessment_detail";   
    protected $fillable = [   
        'docid',
        'jobapply_id',
        'jobid',
        'applicant_id',
        'assessment_id',
        'step_order_group',
        'step_order',
        'assessment_type',
        'assessment_score',
        'assessment_score_value',  
        'status',
        'created_user',
        'updated_user',
        'completed_user'

    ];

   
}
    
