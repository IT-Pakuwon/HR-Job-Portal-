<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsVplProductDetail extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'ms_vpl_product_detail';

    protected $fillable = [
        'product_id',
        'expired_date',
        'cpnyid',
        'whs_id',
        'qty_available',
        'qty_reserved',
        'target_date',
        'status',
        'created_user',
        'updated_user',
    ];

    protected $casts = [
        'expired_date' => 'date',
        'target_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(MsVplProduct::class, 'product_id', 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(MsVplWarehouse::class, 'whs_id', 'whs_id');
    }
}
