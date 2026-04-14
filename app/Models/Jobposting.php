<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jobposting extends Model
{
    protected $connection = 'mysql3';
    protected $table = "hr_trx_jobposting";
    protected $fillable = [
        'docid',
        'refid',
        'cpnyid',
        'departementid',
        'division_id',
        'locationname',
        'date',
        'user',
        'job_title',
        'subgrade_id',
        'job_level',
        'immediate_superior',
        'state_position',
        'job_type',
        'name_job',
        'reason_vacancy',
        'other_reason',
        'required',
        'actual',
        'total_actual',
        'education',
        'experience_start',
        'experience_end',
        'expected_employment_date',
        'status',
        'created_user',
        'updated_user',
        'completed_user'


    ];
}
