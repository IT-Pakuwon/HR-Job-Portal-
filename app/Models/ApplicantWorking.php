<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantWorking extends Model
{
    protected $connection = 'mysql3';
    protected $table = "hr_ms_applicant_working_exp";
   
    protected $fillable = [     
        'applicant_id',       
        'company_name',
        'job_title',
        'start_date',
        'end_date',
        'superior_name',
        'reason_for_leaving',                    
        'status',
        'created_user',
        'updated_user',
        'completed_user'  
    ];
    
}
