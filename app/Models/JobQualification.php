<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobQualification extends Model
{
    protected $table = 'hr_trx_prf_job_qualification';     
    protected $fillable = ['docid', 'no_job_qualification','job_qualification_descr','status','created_user','updated_user'];
    
}
