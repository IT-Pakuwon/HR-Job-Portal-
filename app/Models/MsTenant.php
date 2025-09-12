<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsTenant extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tenant";

    protected $fillable = [
        'tenant',
        'lantai',
        'unit',
        'status',      
    ];
}
