<?php

namespace App\Models\Tenancy;

use Illuminate\Database\Eloquent\Model;

class TsUserTenant extends Model
{
    protected $connection = 'mysql5';
    protected $table = 'msusertenant';
    public $timestamps = false;

    protected $fillable = [
        'name', 'companyname', 'phone', 'email', 'username', 'password',
        'userid', 'siteid', 'usertype', 'tenantid', 'status',
        'created_user', 'created_datetime', 'lastupdate_user', 'lastupdate_datetime',
    ];

    protected $hidden = ['password'];

    public function tenant()
    {
        return $this->belongsTo(TsTenant::class, 'tenantid');
    }

    public function user()
    {
        return $this->belongsTo(TsUser::class, 'userid');
    }
}
