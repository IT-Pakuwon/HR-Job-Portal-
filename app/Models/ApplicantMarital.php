<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantMarital extends Model
{
    protected $connection = 'mysql3';
    protected $table = "hr_ms_applicant_marital_and_children";
   
    protected $fillable = [     
        'applicant_id',       
        'core_family_name',
        'core_family_type',
        'core_family_gender',
        'core_family_birt_of_date',
        'core_family_education',
        'core_family_profession',          
        'status',
        'created_user',
        'updated_user',
        'completed_user'  
    ];
    
}
