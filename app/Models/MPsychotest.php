<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MPsychotest extends Model
{
    protected $connection = 'mysql3';
    protected $table = "hr_ms_psychotest";
   
    protected $fillable = [   
        'aspect',
        'sub_aspect',
        'scale_value',
        'description'       
        
    ];
    
}
