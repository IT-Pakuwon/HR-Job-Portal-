<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class TrPOReuse extends Model
{
    // use SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'tr_po_reuse';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [       
        'cpny_id', 'ponbr', 'po_no', 'csid', 'sppbjktid', 'cs_no', 'sppbjkt_no', 'inventory_type', 'inventory_sub_type', 
        'inventory_category', 'inventoryid', 'inventory_descr', 'qty', 'uom', 'siteid', 'type_multiplier', 'base_multiplier', 
        'base_qty', 'base_uom', 'budget_perpost', 'budget_cpny_id', 
        'budget_business_unit_id', 'budget_department_fin_id', 'budget_account_id', 'budget_activity_id', 
        'budget_activity_descr', 'openordered', 'ordered', 'rejectordered', 'completeordered', 'status', 
        'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at', 'completed_by', 'completed_at'
    ];

    protected $casts = [
        // numerik
        'qty'                 => 'decimal:2',
        'base_qty'            => 'decimal:2',
        'openordered'         => 'decimal:2',
        'ordered'             => 'decimal:2',
        'rejectordered'       => 'decimal:2',
        'completedordered'    => 'decimal:2',

        // tanggal
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
        'deleted_at'          => 'datetime',
        'completed_at'          => 'datetime',
    ];

    // Relasi ke PO
    public function po()
    {
        return $this->belongsTo(TrPO::class, 'ponbr', 'ponbr');
    }
}
