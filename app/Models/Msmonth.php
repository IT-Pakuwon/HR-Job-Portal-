<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Msmonth extends Model
{
    protected $connection = 'mysql2';
    protected $table = "ms_month";
    protected $primaryKey = 'id';
    protected $fillable = [     
        'month',     
    ];
}
