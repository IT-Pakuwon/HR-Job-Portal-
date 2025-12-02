<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SysAccessRight extends Model
{
    protected $connection = 'pgsql2';
    protected $table = 'sys_access_right';

    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'screen_id',
        'application_id',
        'access_name',    // contoh: view/create/edit/delete/approve
        'access_right',   // boolean
        'access_type',    // opsional
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'access_right' => 'boolean', // supaya 't' / 'f' dari postgres → true/false
    ];
}
