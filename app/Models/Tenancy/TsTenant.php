<?php

namespace App\Models\Tenancy;

use Illuminate\Database\Eloquent\Model;

class TsTenant extends Model
{
    protected $connection = 'mysql5';
    protected $table = 'mstenant';
    public $timestamps = false;

    protected $fillable = [
        'storename', 'tenantcompanyid', 'siteid', 'locationid', 'floorid', 'unit', 'picname', 'picphone', 'status',
        'is_warned', 'warning_reason', 'allow_sik', 'allow_sia', 'allow_simkb_in', 'allow_simkb_out', 'allow_simkb_inout',
        'created_user', 'created_datetime', 'lastupdate_user', 'lastupdate_datetime',
    ];

    public function location()
    {
        return $this->belongsTo(TsLocation::class, 'locationid');
    }

    public function floor()
    {
        return $this->belongsTo(TsFloor::class, 'floorid');
    }

    public function tenantCompany()
    {
        return $this->belongsTo(TsTenantCompany::class, 'tenantcompanyid');
    }

    public function userTenants()
    {
        return $this->hasMany(TsUserTenant::class, 'tenantid');
    }
}
