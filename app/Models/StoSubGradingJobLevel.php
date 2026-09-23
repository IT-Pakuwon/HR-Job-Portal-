<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoSubGradingJobLevel extends Model
{
    protected $connection = 'pgsql3';
    protected $table = "hr_ms_sto_subgrading_joblevel";

    protected $fillable = [
        'group_cpny_id',
        'subgrade_id',
        'grade_id',
        'job_level_id',
        'subgrade_name',
        'group_job_level',
        'status',
        'created_user',
        'created_at',
        'updated_user',
        'updated_at'
    ];

}
