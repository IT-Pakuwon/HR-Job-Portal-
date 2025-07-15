<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobLevel extends Model
{
    protected $table = "ms_joblevel"; 
    protected $fillable = [
        'talentaid',
        'title_level',           
        'level',     
        'status',           
    ];
}
