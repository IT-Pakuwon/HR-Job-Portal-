<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplySch extends Model
{
    use HasFactory;

    protected $connection = 'mysql3';
    protected $table = 'hr_trx_job_apply_sch';    
    protected $fillable = [     
        'docid',      
        'docid',
        'jobapply_id',
        'jobid',
        'applicant_id',
        'step_id',
        'title',
        'description',
        'participant',
        'location',
        'location_address',
        'refid',
        'reftype',
        'agenda_note',
        'startdate',
        'enddate',     
        'status',
        'created_user',
        'updated_user'
    ];
   
}
