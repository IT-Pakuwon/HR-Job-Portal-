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
        'subgrade_id',
        'subgrade_name',
        'subgrade_color_code',  
        'grade_id',       
        'group_grade',     
        'status',
        'created_user',
        'updated_user',
        'completed_user' 
    ];


}

