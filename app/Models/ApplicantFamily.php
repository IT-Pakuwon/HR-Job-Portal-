<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantFamily extends Model
{
    protected $connection = 'mysql3';
    protected $table = "hr_ms_applicant_family_background";
   
    protected $fillable = [     
        'applicant_id',       
        'family_name',
        'family_type',
        'family_gender',
        'family_birt_of_date',
        'family_education',
        'family_profession',        
        'status',
        'created_user',
        'updated_user',
        'completed_user'  
    ];
    
}
