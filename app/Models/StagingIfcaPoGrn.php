<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StagingIfcaPoGrn  extends Model
{

    protected $connection = 'pgsql4';
    protected $table = "staging_ifca_po_grn";
    protected $primaryKey = 'id';
    protected $fillable = [
        'cpny_id' , 'entity_cd' , 'grn_no' , 'grn_date' , 'supplier_cd' , 'reference_no' , 'order_no' , 'total_record' , 
        'order_line' , 'item_cd' , 'uom_cd' , 'rec_qty' , 'process_flag' , 'create_date' , 'process_dt' , 'process_note' , 
        'status' , 'created_by' , 'created_at' , 'updated_by' , 'updated_at'

        ];
}
