<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SysScreen extends Model
{
    protected $connection = 'pgsql2';
    protected $table = 'sys_screen';

    public $timestamps = false;

    protected $fillable = [
        'screen_id',
        'screen_name',
        'application_id',
        'status',
        'created_by',
        'created_at',
        'updated_by',
    ];
}
