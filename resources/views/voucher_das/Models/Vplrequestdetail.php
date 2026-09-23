<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vplrequestdetail extends Model
{
    protected $table = "vpl_trx_request_detail";

    protected $fillable = [
        'request_id',
        'linenbr',
        'product_id',
        'expired_date',
        'whs_id',
        'qty_request',
        'purpose_id',  
        'purpose_remark',
        'ref_request_id',           
        'created_user',
        'updated_user',
        'status'
    ];
}
