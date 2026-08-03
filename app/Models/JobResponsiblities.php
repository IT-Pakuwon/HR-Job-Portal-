<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobResponsiblities extends Model
{
    protected $connection = 'pgsql3';
    protected $table = 'hr_trx_prf_job_responsiblities';     
    protected $fillable = ['docid', 'cpnyid', 'group_cpny_id', 'no_job_responsiblities','job_responsibilities_descr','status','created_user','created_at','updated_user','updated_at'];
    
}
