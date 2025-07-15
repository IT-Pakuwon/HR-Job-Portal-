<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobResponsiblities extends Model
{
    protected $table = 'hr_trx_prf_job_responsiblities';     
    protected $fillable = ['docid', 'no_job_responsiblities','job_responsibilities_descr','status','created_user','updated_user'];
    
}
