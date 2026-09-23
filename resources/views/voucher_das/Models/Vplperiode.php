<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vplperiode extends Model
{
    protected $table = "vpl_ms_period";

    protected $fillable = [       
        'cpnyid',
        'perpost_year',
        'perpost_month',       
        'status',
        'created_user',
        'created_at',
        'updated_user',
        'updated_at',
    ];
}
