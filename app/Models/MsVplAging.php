<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsVplAging extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'ms_vpl_aging';

    protected $fillable = [
        'age_descr',
        'start_age',
        'end_age',
        'order_age',
        'status',
        'created_user',
        'updated_user',
    ];
}
