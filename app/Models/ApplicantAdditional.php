<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantAdditional extends Model
{
    protected $connection = 'mysql3';
    protected $table = 'hr_ms_applicant_additional';

    protected $fillable = [
        'applicant_id',
        'group_cpny_id',
        'preferred_job',
        'preferred_work_environment',
        'npwp_id',
        'bpjs_id',
        'has_exp_organization',
        'additional_education_and_training',
        'has_severe_illness_history',
        'illness_year',
        'illness_name',
        'illness_duration',
        'treatment_location',
        'has_traffic_accident_history',
        'accident_year',
        'accident_name',
        'accident_impact',
        'has_criminal_history',
        'incident_year',
        'incident_description',
        'incident_consequence',
        'joining_date_availability',
        'willing_for_medical_check',
        'willing_to_resign_if_unfit',
        'willing_to_make_npwp',
        'allow_reference_check',
        'about_me',
        'applied_position_description',
        'achievements_and_accomplishments',
        'challenges_and_solutions',
        'life_lessons_learned',
        'role_model',
        'five_year_career_goals',
        'status',
        'created_user',
        'updated_user',
        'completed_user',
    ];
}
