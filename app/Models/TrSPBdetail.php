<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrSPBdetail extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_spb_detail";

    protected $fillable = [
        // 'spbid',
        // 'spb_no',
        // 'inventoryid',
        // 'inventory_descr',
        // 'siteid',
        // 'qty',
        // 'uom',
        // 'type_multiplier',
        // 'base_multiplier',
        // 'base_qty',
        // 'base_uom',
        // 'note',
        // 'location_id',
        // 'sub_location_id',
        // 'budget_perpost',
        // 'budget_cpny_id',
        // 'budget_business_unit_id',
        // 'budget_department_fin_id',
        // 'budget_account_id',
        // 'budget_activity_id',
        // 'stock_qty',
        // 'spb_openqty',
        // 'issue_qty',
        // 'spb_completeqty',
        // 'status',
        // 'created_by',
        // 'updated_by'
        'spbid' , 'spb_no' , 'inventoryid' , 'inventory_descr' , 'siteid' , 'qty' , 'uom' , 'type_multiplier' , 'base_multiplier' ,
         'base_qty' , 'base_uom' , 'unitcost' , 'totalcost' , 'note' , 'location_id' , 'sub_location_id' , 'budget_perpost' ,
          'budget_cpny_id' , 'budget_business_unit_id' , 'budget_department_fin_id' , 'budget_account_id' , 'budget_activity_id' ,
           'reason_code' , 'stock_qty' , 'spb_openqty' , 'issue_qty' , 'sppb_qty' , 'spb_completeqty' , 'sppbid' , 'status' ,
            'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at' , 'completed_by' , 'completed_at'
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
