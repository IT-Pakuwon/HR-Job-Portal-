<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SysApplication extends Model
{
    protected $connection = 'pgsql2';
    protected $table = 'sys_application';

    public $timestamps = false;

    protected $fillable = [
        'application_id',
        'application_name',
        'status',
        'created_by',
        'created_at',
        'updated_by',
    ];
}
