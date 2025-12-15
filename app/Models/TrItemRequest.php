<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrItemRequest extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_item_req";

    protected $fillable = [
        'irid',
        'irdate',
        'cpny_id',
        'department_id',
        'inventory_type',
        'inventory_descr_req',
        'inventoryid',
        'pic_item_req',
        'pic_completed_item_req',
        'status',
        'created_by',            
        'updated_by'           

    ];
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

    public function inventory()
    {
        // tr_item_req.inventoryid  -> ms_inventory.inventoryid
        return $this->belongsTo(MsInventory::class, 'inventoryid', 'inventoryid');
    }
   
    
}
