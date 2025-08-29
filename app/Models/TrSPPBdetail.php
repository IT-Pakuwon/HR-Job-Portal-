<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrSPPBdetail extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_sppb_detail";

    protected $fillable = [
        'sppbid',
        'sppb_no',
        'sppb_type',
        'sppb_category',
        'inventoryid',
        'inventory_descr',
        'qty',
        'uom',
        'note', 
        'base_multiplier',
        'base_uom',
        'base_qty',
        'budget_perpost',
        'budget_cpny_id',
        'budget_business_unit_id',
        'budget_department_fin_id',
        'budget_account_id',
        'budget_activity_id',
        'location_id',
        'sub_location_id',
        'assignby',
        'assigndate',
        'assignpurchasing',
        'openordered',
        'ordered',
        'status',
        'created_by',
        'updated_by'
    ];

    // location_id (FK) -> MsLocationPG.locationid (PK)
    public function location()
    {
        return $this->belongsTo(MsLocationPG::class, 'location_id', 'location_id');
    }

    // sub_location_id (FK) -> MsSubLocationPG.sublocationid (PK)
    public function subLocation()
    {
        return $this->belongsTo(MsSubLocationPG::class, 'sub_location_id', 'sub_location_id');
    }
}
