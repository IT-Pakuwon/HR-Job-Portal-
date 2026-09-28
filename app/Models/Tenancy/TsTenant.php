<?php

namespace App\Models\Tenancy;

use Illuminate\Database\Eloquent\Model;

class TsTenant extends Model
{
    protected $connection = 'mysql5';
    protected $table = 'ms_tenant';

    protected $fillable = [
        'floor_id', 'tenant_code', 'tenant_name', 'unit_no', 'status',
        'created_by', 'created_at', 'updated_by', 'updated_at',
    ];

    public function floor()
    {
        return $this->belongsTo(TsFloor::class, 'floor_id');
    }

    public function userTenants()
    {
        return $this->hasMany(TsUserTenant::class, 'tenant_id');
    }
}
