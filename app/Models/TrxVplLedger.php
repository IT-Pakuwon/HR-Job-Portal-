<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrxVplLedger extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'trx_vpl_ledger';

    protected $fillable = [
        'refnbr',
        'refdate',
        'cpnyid',
        'type',
        'postdate',
        'perpost',
        'linenbr',
        'product_id',
        'expired_date',
        'whs_id',
        'qty',
        'reference_refnbr',
        'purpose_id',
        'status',
        'created_user',
        'updated_user',
    ];

    protected $casts = [
        'refdate' => 'date',
        'postdate' => 'date',
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
