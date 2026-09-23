<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vpltransferdetail extends Model
{
    protected $table = "vpl_trx_transfer_detail";

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
        'created_user',
        'updated_user',
        'status'
    ];
}
