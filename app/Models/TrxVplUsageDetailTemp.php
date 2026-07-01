<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrxVplUsageDetailTemp extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'tr_vpl_usage_detail_temp';

    protected $fillable = [
        'refid',
        'usage_id',
        'product_id',
        'expired_date',
        'whs_id',
        'usagetype',
        'qty_usage',
        'qty_return_usage',
        'purpose_id',
        'purpose_remark',
        'created_user',
    ];

    protected $casts = [
        'expired_date' => 'date',
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
