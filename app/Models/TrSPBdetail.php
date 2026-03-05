<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrSPBdetail extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_spb_detail";

    protected $fillable = [

        'spbid' , 'spb_no' , 'inventoryid' , 'inventory_descr' , 'siteid' , 'qty' , 'uom' , 'type_multiplier' ,
        'base_multiplier' , 'base_qty' , 'base_uom' , 'unitcost' , 'totalcost' , 'note' , 'location_id' , 'sub_location_id' ,
        'budget_perpost' , 'budget_cpny_id' , 'budget_business_unit_id' , 'budget_department_fin_id' , 'budget_account_id' ,
        'budget_activity_id' , 'budget_activity_descr','reason_code' , 'stock_qty' , 'base_stock_qty' , 'issue_qty' , 'base_issue_qty' , 'return_qty' ,
        'base_return_qty' , 'sppb_qty' , 'base_sppb_qty' , 'spb_completeqty' , 'base_spb_completeqty' , 'sppbid' , 'status' ,
        'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at' , 'completed_by' , 'completed_at'
    ];

    // location_id (FK) -> MsLocation.locationid (PK)
    public function location()
    {
        return $this->belongsTo(MsLocation::class, 'location_id', 'location_id');
    }

    // sub_location_id (FK) -> MsSubLocation.sublocationid (PK)
    public function subLocation()
    {
        return $this->belongsTo(MsSubLocation::class, 'sub_location_id', 'sub_location_id');
    }

    public function spb()
    {
        return $this->belongsTo(TrSPB::class, 'spbid', 'spbid');
    }
}
