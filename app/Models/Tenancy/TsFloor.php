<?php

namespace App\Models\Tenancy;

use Illuminate\Database\Eloquent\Model;

class TsFloor extends Model
{
    protected $connection = 'mysql5';
    protected $table = 'msfloor';
    public $timestamps = false;

    protected $fillable = [
        'sitetype', 'floor', 'order', 'status',
        'created_user', 'created_datetime', 'lastupdate_user', 'lastupdate_datetime',
    ];
}
