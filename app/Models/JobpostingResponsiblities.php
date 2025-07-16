<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobpostingResponsiblities extends Model
{
    protected $connection = 'mysql3';
    protected $table = 'hr_trx_jobposting_responsiblities';     
    protected $fillable = ['docid', 'refid','no_job_responsiblities','job_responsibilities_descr','status','created_user','updated_user'];
    
}
