<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobpostingQualification extends Model
{
    protected $connection = 'mysql3';
    protected $table = 'hr_trx_jobposting_qualification';     
    protected $fillable = ['docid', 'refid','no_job_qualification','job_qualification_descr','status','created_user','updated_user'];
    
}
