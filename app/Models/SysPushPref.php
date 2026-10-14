<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// A user's My APP notification settings (see database/sql/push_pref.sql). No row = defaults.
class SysPushPref extends Model
{
    protected $connection = 'pgsql2';
    protected $table = 'sys_push_pref';
    protected $primaryKey = 'username';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['username', 'approvals', 'updates', 'comments', 'others', 'quiet_on', 'quiet_from', 'quiet_to', 'tz'];

    protected $casts = ['approvals' => 'boolean', 'updates' => 'boolean', 'comments' => 'boolean', 'others' => 'boolean', 'quiet_on' => 'boolean'];
}
