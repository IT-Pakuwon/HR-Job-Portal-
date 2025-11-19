<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrReceiptdetail extends Model
{
   
    protected $connection = 'pgsql';
    protected $table = 'tr_receipt_detail';

    // Primary Key
    protected $primaryKey = 'id';

    // Kolom yang bisa diisi (mass assignment)
    protected $fillable = [
        // 'receiptnbr',
        // 'receipt_no',
        // 'ponbr',
        // 'po_no',
        // 'csid',
        // 'cs_no',
        // 'sppbjktid',
        // 'sppbjktid_no',
        // 'inventory_type',
        // 'inventoryid',
        // 'inventory_descr',
        // 'qtyordered',
        // 'uom',
        // 'siteid',
        // 'type_multiplier',
        // 'base_multiplier',
        // 'base_qty',
        // 'base_uom',
        // 'unitcost',
        // 'taxcodeid',
        // 'taxamt',
        // 'totalcost',
        // 'receipttype',
        // 'qty_open_ordered',
        // 'base_qty_open_ordered',
        // 'qty_received',
        // 'base_qty_received',
        // 'qty_return',
        // 'base_qty_return',
        // 'ref_receiptnbr',
        // 'budget_perpost',
        // 'budget_cpny_id',
        // 'budget_business_unit_id',
        // 'budget_department_fin_id',
        // 'budget_account_id',
        // 'budget_activity_id',
        // 'budget_activity_descr',
        // 'status',
        // 'created_by',
        // 'updated_by',
        // 'deleted_by',
        'receiptnbr' , 'receipt_no' , 'ponbr' , 'po_no' , 'csid' , 'cs_no' , 'sppbjktid' , 'sppbjktid_no' , 
        'inventory_type' , 'inventory_sub_type' , 'inventory_category' , 'inventoryid' , 'inventory_descr' , 
        'receiptnote_detail' , 'qtyordered' , 'uom' , 'siteid' , 'type_multiplier' , 'base_multiplier' , 
        'base_qty' , 'base_uom' , 'unitcost' , 'taxcodeid' , 'taxamt' , 'totalcost' , 'receipttype' , 
        'qty_open_ordered' , 'base_qty_open_ordered' , 'qty_received' , 'base_qty_received' , 'qty_return' , 
        'base_qty_return' , 'ref_receiptnbr' , 'budget_perpost' , 'budget_cpny_id' , 'budget_business_unit_id' , 
        'budget_department_fin_id' , 'budget_account_id' , 'budget_activity_id' , 'budget_activity_descr' , 
        'status' , 'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at'
        ];

    // Kolom tanggal
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

   
}
