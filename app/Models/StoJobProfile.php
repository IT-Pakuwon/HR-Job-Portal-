<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoJobProfile extends Model
{
    protected $table = "hr_ms_sto_job_profile";    
    protected $fillable = [     
        'departement_id',
        'no_job_purpose',
        'job_purpose',
        'refid',
        'status',
        'created_user',
        'updated_user',
        'completed_user' 
    ]; 

        
}
