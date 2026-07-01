<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrxVplUsageDetail extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'tr_vpl_usage_detail';

    protected $fillable = [
        'usage_id',
        'linenbr',
        'product_id',
        'expired_date',
        'whs_id',
        'qty_usage',
        'qty_return_usage',
        'qty_settlement',
        'purpose_id',
        'purpose_remark',
        'ref_usage_id',
        'status',
        'created_user',
        'updated_user',
    ];

    protected $casts = [
        'expired_date' => 'date',
    ];

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
