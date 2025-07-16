<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoSubGrading extends Model
{
    protected $table = "hr_ms_sto_subgrading";
    
    protected $fillable = [
        'subgrade_id',
        'subgrade_name',
        'subgrade_color_code',  
        'grade_id',            
        'status',
        'created_user',
        'updated_user',
        'completed_user' 
    ];


}

