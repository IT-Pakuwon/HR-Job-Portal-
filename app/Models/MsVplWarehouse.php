<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsVplWarehouse extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'ms_vpl_warehouse';

    protected $fillable = [
        'cpnyid',
        'whs_id',
        'vp_type',
        'status',
        'created_user',
        'updated_user',
    ];

    public function departments()
    {
        return $this->hasMany(MsVplWarehouseDept::class, 'whs_id', 'whs_id');
    }

    public function usages()
    {
        return $this->hasMany(MsVplWarehouseUsage::class, 'whs_id', 'whs_id');
    }
}
