<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsVplProductBal extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'ms_vpl_product_bal';

    protected $fillable = [
        'year',
        'perpost',
        'product_id',
        'expired_date',
        'cpnyid',
        'whs_id',
        'begqty',
        'period01in',
        'period01out',
        'period02in',
        'period02out',
        'period03in',
        'period03out',
        'period04in',
        'period04out',
        'period05in',
        'period05out',
        'period06in',
        'period06out',
        'period07in',
        'period07out',
        'period08in',
        'period08out',
        'period09in',
        'period09out',
        'period10in',
        'period10out',
        'period11in',
        'period11out',
        'period12in',
        'period12out',
        'status',
        'created_user',
        'updated_user',
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
