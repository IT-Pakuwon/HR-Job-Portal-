<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantSW extends Model
{
    protected $connection = 'mysql3';
    protected $table = "hr_ms_applicant_sw";
   
    protected $fillable = [     
        'applicant_id',       
        'sw_descr',
        'sw_type',             
        'status',
        'created_user',
        'updated_user',
        'completed_user'  
    ];
    
}
