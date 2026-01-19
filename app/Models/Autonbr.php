<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Autonbr extends Model
{
    // protected $connection = 'mysql2';
    protected $connection = 'pgsql2';
    protected $table = "ms_autonbr_test";
    protected $primaryKey = 'id';
    protected $fillable = [
        'doctype',
        'doctype_descr',
        'year',
        'month',
        'number',
        'status',
        'created_by', 
        'updated_by', 
    ];
}
