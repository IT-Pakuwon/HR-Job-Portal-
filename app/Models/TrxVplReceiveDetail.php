<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrxVplReceiveDetail extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'trx_vpl_receive_detail';

    protected $fillable = [
        'receive_id',
        'linenbr',
        'product_id',
        'expired_date',
        'whs_id',
        'qty_receive',
        'status',
        'created_user',
        'updated_user',
    ];

    protected $casts = [
        'expired_date' => 'date',
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
