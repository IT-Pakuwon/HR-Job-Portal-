<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrPOdetail extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'tr_po_detail';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'ponbr','csid','cs_no','sppbjktid','sppbjktid_no',
        'inventory_type','inventoryid','inventory_descr','ponote_detail',
        'qty','uom','type_multiplier','base_multiplier','base_qty','base_uom',
        'unitcost','taxcodeid','taxamt','totalcost',
        'qty_received','base_qty_received','qty_completed','base_qty_completed',
        'received','completed','canceled',
        'account_id','activity_id',
        'status','created_by','updated_by',
    ];

    protected $casts = [
        'qty' => 'float',
        'unitcost' => 'float',
        'totalcost' => 'float',
        'taxamt' => 'float',
        'received' => 'boolean',
        'completed' => 'boolean',
        'canceled' => 'boolean',
    ];

    public function po()
    {
        return $this->belongsTo(TrPO::class, 'ponbr', 'ponbr');
    }
}
