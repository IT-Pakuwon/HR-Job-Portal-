<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SysRole extends Model
{
    protected $connection = 'pgsql2';
    protected $table = 'sys_role';

    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'role_name',
        'status',
        'created_by',
        'created_at',
        'updated_by',
    ];
}
