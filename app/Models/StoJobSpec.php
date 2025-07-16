<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class StoJobSpec extends Model
{
    protected $table = "hr_ms_sto_job_spec";    
    protected $fillable = [     
        'departement_id',
        'job_level',
        'education_min',
        'education_jurusan',
        'experience_min',
        'experience_position',
        'refid',
        'status',
        'created_user',
        'updated_user',
        'completed_user' 
    ]; 

        
}
