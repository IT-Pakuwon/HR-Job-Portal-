<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vplrequestdetailtemp extends Model
{
    protected $table = "vpl_trx_request_detail_temp";

    protected $fillable = [
        'refid',
        'linenbr',
        'product_id',
        'expired_date',
        'whs_id',
        'requesttype',
        'qty_request',
        'qty_return',
        'purpose_id',  
        'purpose_remark',
        'ref_request_id',           
        'created_user',
        'updated_user',
        'status'
    ];
}
