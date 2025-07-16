<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrJobtag extends Model
{
    protected $table = "hr_trx_prf_job_tags"; 
    protected $fillable = [
        'docid',
        'job_tags',        
        'status',  
        'created_user',       
        'updated_user'       
    ];
}
