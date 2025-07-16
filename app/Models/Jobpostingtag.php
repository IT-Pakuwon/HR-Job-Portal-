<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jobpostingtag extends Model
{
    protected $connection = 'mysql3';
    protected $table = 'hr_trx_jobposting_tags';     
    protected $fillable = ['docid', 'refid','job_tags','status','created_user','updated_user'];
    
}
