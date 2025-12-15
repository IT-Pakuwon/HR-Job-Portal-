<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsInventory extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_inventory";

    protected $fillable = [      
        'id', 'inventoryid', 'inventory_descr', 'item_type', 'item_sub_type', 'item_class', 
        'item_sub_class', 'item_category', 'stock_unit', 'purchase_unit', 'status', 'created_by', 
        'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at'
    ];
}
