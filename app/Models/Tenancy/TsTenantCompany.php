<?php

namespace App\Models\Tenancy;

use Illuminate\Database\Eloquent\Model;

class TsTenantCompany extends Model
{
    protected $connection = 'mysql5';
    protected $table = 'mstenantcompany';
    public $timestamps = false;

    protected $fillable = [
        'tenantcompanyname', 'badanusaha', 'status',
        'created_user', 'created_datetime', 'lastupdate_user', 'lastupdate_datetime',
    ];
}
