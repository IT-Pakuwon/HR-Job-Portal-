<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manpower extends Model
{
    protected $connection = 'pgsql3';
    protected $table = "hr_ms_mpp";   
    protected $fillable = [
        'docid',        
        'cpnyid',
        'departementid',
        'date',
        'periodyear',
        'required',
        'actual',
        'total_actual',
        'status',
        'created_user',
        'created_at',
        'updated_user',
        'updated_at',     
        'completed_user',
        'completed_at'           
    ];
}
