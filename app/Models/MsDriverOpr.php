<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsDriverOpr extends Model
{
    use HasFactory;

    protected $connection = 'pgsql5';

    protected $table = 'ms_driver_opr';

    protected $fillable = [
        'drivername',
        'hp',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];
}
