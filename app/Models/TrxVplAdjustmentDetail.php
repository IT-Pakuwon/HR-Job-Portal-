<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrxVplAdjustmentDetail extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'trx_vpl_adjustment_detail';

    protected $fillable = [
        'adjustment_id',
        'linenbr',
        'product_id',
        'expired_date',
        'whs_id',
        'qty_adjustment',
        'status',
        'created_user',
        'updated_user',
    ];

    protected $casts = [
        'expired_date' => 'date',
    ];

    public function adjustment()
    {
        return $this->belongsTo(TrxVplAdjustment::class, 'adjustment_id', 'adjustment_id');
    }

    public function product()
    {
        return $this->belongsTo(MsVplProduct::class, 'product_id', 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(MsVplWarehouse::class, 'whs_id', 'whs_id');
    }
}
