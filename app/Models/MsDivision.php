<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsDivision extends Model
{
    protected $connection = 'mysql3';
    protected $table = "hr_ms_division";

    protected $fillable = [       
        'division_id',
        'division_name',
        'status',
        'created_user',
        'updated_user',
        'completed_user',
        'completed_at',
    ];
}
