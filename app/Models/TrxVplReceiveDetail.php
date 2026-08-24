<?php

namespace App\Models;

use App\Models\Concerns\NoTimezoneShift;
use Illuminate\Database\Eloquent\Model;

class TrxVplReceiveDetail extends Model
{
    use NoTimezoneShift;

    protected $connection = 'pgsql5';

    protected $table = 'tr_vpl_receive_detail';

    protected $fillable = [
        'receive_id',
        'linenbr',
        'product_id',
        'expired_date',
        'whs_id',
        'qty_receive',
        'product_price',
        'total_product_price',
        'status',
        'created_user',
        'updated_user',
    ];

    protected $casts = [
        'expired_date' => 'date',
        'product_price' => 'decimal:2',
        'total_product_price' => 'decimal:2',
    ];

    public function receive()
    {
        return $this->belongsTo(TrxVplReceive::class, 'receive_id', 'receive_id');
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

