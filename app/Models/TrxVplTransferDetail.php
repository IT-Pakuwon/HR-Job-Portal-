<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrxVplTransferDetail extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'trx_vpl_transfer_detail';

    protected $fillable = [
        'transfer_id',
        'linenbr',
        'product_id',
        'expired_date',
        'from_whs_id',
        'to_whs_id',
        'qty_available',
        'qty_transfer',
        'ref_transfer_id',
        'status',
        'created_user',
        'updated_user',
    ];

    protected $casts = [
        'expired_date' => 'date',
    ];

    public function transfer()
    {
        return $this->belongsTo(TrxVplTransfer::class, 'transfer_id', 'transfer_id');
    }

    public function product()
    {
        return $this->belongsTo(MsVplProduct::class, 'product_id', 'product_id');
    }

    public function fromWarehouse()
    {
        return $this->belongsTo(MsVplWarehouse::class, 'from_whs_id', 'whs_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(MsVplWarehouse::class, 'to_whs_id', 'whs_id');
    }
}
