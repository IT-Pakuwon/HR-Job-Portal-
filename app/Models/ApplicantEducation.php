<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicantEducation extends Model
{
    protected $connection = 'mysql3';
    protected $table = 'hr_ms_applicant_education';

    protected $fillable = [
        'applicant_id',
        'group_cpny_id',
        'education_name',
        'education_type',
        'start_year',
        'end_year',
        'education_faculty',
        'education_cost',
        'education_score',
        'status',
        'created_user',
        'updated_user',
        'completed_user',
    ];
}
