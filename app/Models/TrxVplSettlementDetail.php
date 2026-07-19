<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrxVplSettlementDetail extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'tr_vpl_settlement_detail';

    protected $fillable = [
        'settlement_id',
        'usage_id',
        'linenbr',
        'product_id',
        'expired_date',
        'whs_id',
        'qty_usage',
        'qty_settlement',
        'qty_remain',
        'settlement_remark',
        'status',
        'created_user',
        'updated_user',
    ];

    protected $casts = [
        'expired_date' => 'date',
    ];

    public function settlement()
    {
        return $this->belongsTo(TrxVplSettlement::class, 'settlement_id', 'settlement_id');
    }

    public function usage()
    {
        return $this->belongsTo(TrxVplUsage::class, 'usage_id', 'usage_id');
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
