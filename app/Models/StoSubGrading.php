<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoSubGrading extends Model
{
    protected $connection = 'pgsql3';
    protected $table = "hr_ms_sto_subgrading";
    
    protected $fillable = [
        'group_cpny_id',
        'subgrade_id',
        'subgrade_name',
        'subgrade_color_code',
        'grade_id',
        'group_grade',
        'status',
        'created_user',
        'created_at',
        'updated_user',
        'updated_at',
        'completed_user'
    ];


}

