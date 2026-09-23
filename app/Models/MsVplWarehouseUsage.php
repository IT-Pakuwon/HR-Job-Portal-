<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsVplWarehouseUsage extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'ms_vpl_warehouse_usage';

    protected $fillable = [
        'whs_id',
        'cpnyid',
        'department_id',
        'status',
        'created_user',
        'updated_user',
    ];

    public function warehouse()
    {
        return $this->belongsTo(MsVplWarehouse::class, 'whs_id', 'whs_id');
    }
}
