<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantSkill extends Model
{
    protected $connection = 'mysql3';
    protected $table = "hr_ms_applicant_skill";
   
    protected $fillable = [     
        'applicant_id',       
        'skill_descr',
        'skill_type',             
        'status',
        'created_user',
        'updated_user',
        'completed_user'  
    ];
    
}
