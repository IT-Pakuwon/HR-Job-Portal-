<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vplledger extends Model
{
    protected $table = "vpl_trx_ledger";

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
        'created_user',
        'updated_user',
        'status'
    ];
}
