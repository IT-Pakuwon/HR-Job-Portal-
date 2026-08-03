<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrJobtag extends Model
{
    protected $connection = 'pgsql3';
    protected $table = "hr_trx_prf_job_tags"; 
    protected $fillable = [
        'docid',
        'cpnyid',
        'group_cpny_id',
        'job_tags',
        'status',
        'created_user',
        'created_at',
        'updated_user',
        'updated_at'
    ];
}
