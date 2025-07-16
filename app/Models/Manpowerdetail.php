<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manpowerdetail extends Model
{
    protected $table = "hr_ms_mpp_detail";   
    protected $fillable = [
        'docid',        
        'periodyear',
        'periodmonth',
        'job_title',
        'job_level',
        'qty',
        'reason_vacancy',
        'expected_employment_date',
        'status',
        'created_user',
        'created_at',
        'updated_user',
        'updated_at',     
        'completed_user',
        'completed_at'           
    ];
}
