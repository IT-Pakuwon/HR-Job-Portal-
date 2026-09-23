<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vpladjustmentdetail extends Model
{
    protected $table = "vpl_trx_adjustment_detail";

    protected $fillable = [
        'adjustment_id',
        'linenbr',
        'product_id',
        'expired_date',
        'qty_adjustment',  
        'whs_id',           
        'created_user',
        'updated_user',
        'status'
    ];
}
