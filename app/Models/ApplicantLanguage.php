<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantLanguage extends Model
{
    protected $connection = 'mysql3';
    protected $table = "hr_ms_applicant_language";
   
    protected $fillable = [     
        'applicant_id',       
        'language_descr',
        'language_score',           
        'status',
        'created_user',
        'updated_user',
        'completed_user'  
    ];
    
}
