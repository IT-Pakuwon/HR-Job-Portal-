<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsVplWarehouseDept extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'ms_vpl_warehouse_dept';

    protected $fillable = [
        'activity_type',
        'cpnyid',
        'whs_id',
        'department_id',
        'vp_type',
        'status',
        'created_user',
        'updated_user',
    ];

    public function warehouse()
    {
        return $this->belongsTo(MsVplWarehouse::class, 'whs_id', 'whs_id');
    }
}
