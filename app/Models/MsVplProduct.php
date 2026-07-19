<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsVplProduct extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'ms_vpl_product';

    protected $fillable = [
        'product_id',
        'cpnyid',
        'product_name',
        'product_type',
        'product_category',
        'product_source_type',
        'product_source_company',
        'product_source_tenant',
        'product_remark',
        'product_value',
        'product_uom',
        'product_check_exp',
        'status',
        'created_user',
        'updated_user',
    ];

    public function balances()
    {
        return $this->hasMany(MsVplProductBal::class, 'product_id', 'product_id');
    }

    public function details()
    {
        return $this->hasMany(MsVplProductDetail::class, 'product_id', 'product_id');
    }
}
