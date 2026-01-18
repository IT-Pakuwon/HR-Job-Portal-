<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StagingIfcaPoItem extends Model
{

    protected $connection = 'pgsql3';
    protected $table = "staging_ifca_po_item";
    protected $primaryKey = 'id';
    protected $fillable = [
        'item_cd','item_descs','uom_cd','item_type','stock_cd','item_remarks','costcode',
        'expense_acct','asset_acct','management_acct',
        'latest_cost','std_cost','budget_rate',
        'supplier_cd','product_cd','leadtime',
        'alt_supplier_cd','alt_product_cd','alt_leadtime',
        'process_flag','create_date','process_dt','process_note',
        'status','created_by','created_at','updated_by','updated_at',
    ];
    
    protected $casts = [
        'latest_cost' => 'decimal:4',
        'std_cost'    => 'decimal:2',
        'budget_rate' => 'decimal:4',
        'leadtime'    => 'decimal:0',
        'alt_leadtime'=> 'decimal:0',
        'create_date' => 'datetime',
        'process_dt'  => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];
}