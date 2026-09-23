<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vplreceivedetail extends Model
{
    protected $table = "vpl_trx_receive_detail";

    protected $fillable = [
        'receive_id',
        'linenbr',
        'product_id',
        'expired_date',
        'qty_receive',  
        'whs_id',           
        'created_user',
        'updated_user',
        'status'
    ];
}
