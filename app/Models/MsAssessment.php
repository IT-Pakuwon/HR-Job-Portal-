<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsAssessment extends Model
{    
    protected $connection = 'mysql3';
    protected $table = "hr_ms_interview_assessment";   
    protected $fillable = [        
        'assessment_id',
        'assessment_group',
        'assessment_descr',
        'assessment_score',
        'step_order_group',
        'step_order',
        'assessment_type',        
        'status',
        'created_user',
        'updated_user',
        'completed_user'

    ];

   
}
    
