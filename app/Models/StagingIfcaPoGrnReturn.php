<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StagingIfcaPoGrnReturn  extends Model
{
    protected $connection = 'pgsql4';
    protected $table = "staging_ifca_po_grn_return";
    protected $primaryKey = 'id';
    protected $fillable = [
        'cpny_id' , 'entity_cd' , 'return_no' , 'return_date' , 'supplier_cd' , 'keeper' , 'keeper_date' , 
        'reference_no' , 'return_descs' , 'grn_no' , 'total_record' , 'receipt_line' , 'order_line' , 
        'item_cd' , 'item_type' , 'item_descr' , 'uom_cd' , 'return_qty' , 'process_flag' , 'create_date' , 
        'process_dt' , 'process_note' , 'status' , 'created_by' , 'created_at' , 'updated_by' , 'updated_at'
        ];
}
