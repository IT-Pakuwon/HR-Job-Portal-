<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrAssessmentResult extends Model
{
    protected $connection = 'mysql3';
    protected $table = "hr_trx_interview_assessment_result";
    protected $fillable = [
        'docid',
        'cpnyid',
        'group_cpny_id',
        'jobapply_id',
        'jobid',
        'applicant_id',
        'assessment_type',
        'assessment_strengths',
        'assessment_weaknesses',
        'assessment_comment',
        'assessment_result',
        'status',
        'created_user',
        'updated_user',
        'completed_user'

    ];


}

