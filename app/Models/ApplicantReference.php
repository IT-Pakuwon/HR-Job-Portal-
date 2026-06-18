<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicantReference extends Model
{
    protected $connection = 'mysql3';
    protected $table = 'hr_ms_applicant_reference';

    protected $fillable = [
        'applicant_id',
        'reference_name',
        'reference_company_name',
        'reference_job_position',
        'reference_phone_number',
        'reference_relation',
        'status',
        'created_user',
        'updated_user',
        'completed_user',
    ];
}
