<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantEducation extends Model
{
    protected $connection = 'mysql3';
    protected $table = "hr_ms_applicant_education";
   
    protected $fillable = [     
        'applicant_id', 
        'education_name',
        'education_type',
        'start_year',
        'end_year',
        'education_score',
        'status',
        'created_user',
        'updated_user',
        'completed_user'  
    ];
    
}
