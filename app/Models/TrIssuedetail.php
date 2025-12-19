<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrIssuedetail extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_issue_detail";

    protected $fillable = [       
        'issueid' , 'issue_no' , 'spbid' , 'spb_no' , 'inventoryid' , 'inventory_descr' , 'siteid' , 'qty' , 'uom' , 
        'type_multiplier' , 'base_multiplier' , 'base_qty' , 'base_uom' , 'unitcost' , 'totalcost' , 'issuenote_detail' , 
        'location_id' , 'sub_location_id' , 'budget_perpost' , 'budget_cpny_id' , 'budget_business_unit_id' , 
        'budget_department_fin_id' , 'budget_account_id' , 'budget_activity_id' , 'reason_code' , 'issuetype' , 'issue_qty' , 
        'base_issue_qty' , 'qty_return' , 'base_qty_return' , 'ref_issuenbr' , 'status' , 'created_by' , 'created_at' , 
        'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at' , 'completed_by' , 'completed_at'
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
}
