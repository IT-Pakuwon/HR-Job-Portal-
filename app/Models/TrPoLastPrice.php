<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrPoLastPrice extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_po_last_price";

    protected $fillable = [      
        'ponbr', 'podate', 'cpny_id', 'csid', 'sppbjktid', 'purchaser','vendorid', 'vendorname', 'inventory_type', 'inventory_sub_type', 'inventory_category', 'inventoryid', 'inventory_descr', 'qty', 'uom', 'siteid', 'unitcost', 'taxcodeid', 'taxamt', 'totalcost', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at'
    ];
}
