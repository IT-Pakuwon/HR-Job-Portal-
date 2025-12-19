<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class TrPOdetail extends Model
{
    // use SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'tr_po_detail';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [       

        'ponbr' , 'po_no' , 'csid' , 'cs_no' , 'sppbjktid' , 'sppbjktid_no' , 'inventory_type' , 'inventory_sub_type' , 
        'inventory_category' , 'inventoryid' , 'inventory_descr' , 'ponote_detail' , 'qty' , 'uom' , 'siteid' , 'type_multiplier' ,
         'base_multiplier' , 'base_qty' , 'base_uom' , 'unitcost' , 'taxcodeid' , 'taxamt' , 'totalcost' , 'qty_received' , 
         'base_qty_received' , 'qty_return' , 'base_qty_return' , 'qty_completed' , 'base_qty_completed' , 'received' , 'completed' , 
         'canceled' , 'budget_perpost' , 'budget_cpny_id' , 'budget_business_unit_id' , 'budget_department_fin_id' , 
         'budget_account_id' , 'budget_activity_id' , 'budget_activity_descr' , 'status' , 'created_by' , 'created_at' , 
         'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at'
    ];

    protected $casts = [
        // numerik
        'qty'                 => 'decimal:2',
        'base_qty'            => 'decimal:2',
        'unitcost'            => 'decimal:2',
        'taxamt'              => 'decimal:2',
        'totalcost'           => 'decimal:2',
        'qty_received'        => 'decimal:2',
        'base_qty_received'   => 'decimal:2',
        'qty_return'          => 'decimal:2',
        'base_qty_return'     => 'decimal:2',
        'qty_completed'       => 'decimal:2',
        'base_qty_completed'  => 'decimal:2',
        'type_multiplier'     => 'integer',
        'base_multiplier'     => 'integer',

        // boolean
        'received'            => 'boolean',
        'completed'           => 'boolean',
        'canceled'            => 'boolean',

        // tanggal
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
        'deleted_at'          => 'datetime',
    ];

    // Relasi ke PO
    public function po()
    {
        return $this->belongsTo(TrPO::class, 'ponbr', 'ponbr');
    }
}
