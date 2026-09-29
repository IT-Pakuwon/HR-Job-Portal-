<?php

namespace App\Models\Tenancy;

use Illuminate\Database\Eloquent\Model;

class TsUser extends Model
{
    protected $connection = 'mysql5';
    protected $table = 'user';
    public $timestamps = false;

    protected $fillable = [
        'name', 'companyname', 'phone', 'email', 'username', 'password',
        'is_admin', 'usertype', 'departmentid', 'siteid', 'refid', 'refdefault', 'status',
        'created_user', 'created_datetime', 'lastupdate_user', 'lastupdate_datetime',
    ];

    protected $hidden = ['password', 'remember_token'];
}
