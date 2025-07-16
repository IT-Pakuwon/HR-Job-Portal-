<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;

    protected $table = 'hr_ms_division';   
    protected $fillable = [     
        'division_id',
        'division_name',
        'status',
        'status',
        'created_user',
        'updated_user'
    ];
   
}
