<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SysStagingSetting extends Model
{
    protected $connection = 'pgsql2';
    protected $table = 'sys_staging_setting';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_application',
        'name_application',
        'last_update',
        'next_update',
        'interval',
        'ref_app',
        'status',
        'created_user',
        'created_datetime',
        'lastupdate_user',
        'lastupdate_datetime',
    ];

    protected $casts = [
        'last_update' => 'datetime',
        'next_update' => 'datetime',
        'interval' => 'integer',
        'created_datetime' => 'datetime',
        'lastupdate_datetime' => 'datetime',
    ];
}
