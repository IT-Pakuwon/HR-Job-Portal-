<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoGrading extends Model
{
    protected $table = "hr_ms_sto_grading";
    
    protected $fillable = [
        'grade_id',
        'grade_name',
        'grade_color_code',       
        'status',
        'created_user',
        'updated_user',
        'completed_user' 
    ];


}

