<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrSPPJdetail extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_sppj_detail";

    protected $fillable = [
        'sppjid',
        'sppj_no',
        'sppj_type',
        'sppj_category',
        'inventoryid',
        'inventory_descr',
        'qty',
        'uom',
        'note', 
        'type_multiplier',
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
        'rejectordered',
        'completeordered',
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
