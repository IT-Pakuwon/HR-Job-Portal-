<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Autonbr extends Model
{
    protected $connection = 'mysql2';
    protected $table = "ms_autonbr";
    protected $primaryKey = 'id';
    protected $fillable = [
        'doctype',
        'year',
        'month',
        'number',
        'status'
    ];
}
