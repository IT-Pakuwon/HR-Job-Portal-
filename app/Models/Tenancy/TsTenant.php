<?php

namespace App\Models\Tenancy;

use Illuminate\Database\Eloquent\Model;

class TsTenant extends Model
{
    protected $connection = 'mysql5';
    protected $table = 'mstenant';
    public $timestamps = false;

    /** mscategory.type used for tenant categories. */
    public const CATEGORY_TYPE = 'Tenant';

    /** mscategory.type that lists the "Type" choices; mstenant.unittype stores the name. */
    public const UNIT_TYPE_CATEGORY = 'Tenant Type';
    /** The Type name that also sets status V. */
    public const VACANT_TYPE = 'Vacant';
    public const BRAND_TYPES = ['Local', 'International'];
    public const CONTRACT_STATUSES = ['Active', 'Pending', 'Expired'];
    /** Suggestions only: "Type Status" also accepts a typed value. */
    public const TYPE_STATUSES = ['Tenant Upcoming', 'Renovasi', 'Pullout'];

    protected $casts = ['unitsize' => 'float'];

    protected $fillable = [
        'storename', 'tenantcompanyid', 'siteid', 'locationid', 'floorid', 'unit', 'picname', 'picphone', 'status',
        'unittype', 'typestatus', 'periode', 'unitsize', 'brandorigin', 'brandtype', 'contractstatus', 'perusahaan',
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

    public function categories()
    {
        return $this->belongsToMany(TsCategory::class, 'mstenantcategory', 'tenantid', 'categoryid');
    }

    public function userTenants()
    {
        return $this->hasMany(TsUserTenant::class, 'tenantid');
    }
}
