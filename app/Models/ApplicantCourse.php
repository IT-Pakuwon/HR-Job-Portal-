<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantCourse extends Model
{
    protected $connection = 'mysql3';
    protected $table = "hr_ms_applicant_course";

    protected $fillable = [
        'applicant_id',
        'group_cpny_id',
        'course_name',
        'course_type',
        'course_certificate',
        'course_cost',
        'course_score',
        'start_year',
        'end_year',
        'status',
        'created_user',
        'updated_user',
        'completed_user'

    ];

}
