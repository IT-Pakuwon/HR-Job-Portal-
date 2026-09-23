<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsVplPeriod extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'ms_vpl_period';

    protected $fillable = [
        'cpnyid',
        'perpost_year',
        'perpost_month',
        'status',
        'created_user',
        'updated_user',
    ];
}
