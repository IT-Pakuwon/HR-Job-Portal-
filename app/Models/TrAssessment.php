<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrAssessment extends Model
{    
    protected $connection = 'mysql3';
    protected $table = "hr_trx_interview_assessment";   
    protected $fillable = [            
        'docid',
        'jobapply_id',
        'jobid',
        'applicant_id',
        'assessment_date',
        'type',
        'user',
        'total_assessment_score_value',    
        'status',
        'created_user',
        'updated_user',
        'completed_user'

    ];

   
}
    
