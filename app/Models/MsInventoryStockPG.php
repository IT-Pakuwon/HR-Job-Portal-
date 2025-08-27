<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsInventoryStockPG extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_inventory_stock";

    protected $fillable = [
        'inventoryid',
        'inventory_descr',
        'item_type',
        'item_sub_type',
        'item_class',
        'item_sub_class',
        'item_category',
        'stock_unit',
        'purchase_unit',   
        'status',
        'created_by',
        'updated_by'
    ];
}
