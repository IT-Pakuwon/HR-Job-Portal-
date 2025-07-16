<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accesoriesroom extends Model
{
    protected $connection = 'mysql2';
    protected $table = "ms_accessories";

    protected $fillable = [
        'acc_name',
        'acc_qty',        
        'status',     
    ];
}
