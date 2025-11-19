<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrIssuedetail extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_issue_detail";

    protected $fillable = [
        // 'issueid',
        // 'issue_no',
        // 'spbid',
        // 'spb_no',
        // 'issuetype',
        // 'inventoryid',
        // 'inventory_descr',
        // 'siteid',
        // 'qty',
        // 'uom',
        // 'type_multiplier',
        // 'base_multiplier',
        // 'base_qty',
        // 'base_uom',
        // 'unitcost',
        // 'totalcost',
        // 'note',
        // 'location_id',
        // 'sub_location_id',
        // 'budget_perpost',
        // 'budget_cpny_id',
        // 'budget_business_unit_id',
        // 'budget_department_fin_id',
        // 'budget_account_id',
        // 'budget_activity_id',
        // 'reason_code',
        // 'issue_qty',
        // 'status',
        // 'created_by',
        // 'updated_by'
        'issueid' , 'issue_no' , 'spbid' , 'spb_no' , 'issuetype' , 'inventoryid' , 'inventory_descr' , 'siteid' , 'qty' , 'uom' , 
        'type_multiplier' , 'base_multiplier' , 'base_qty' , 'base_uom' , 'unitcost' , 'totalcost' , 'issuenote_detail' , 
        'location_id',         'sub_location_id' , 'budget_perpost' , 'budget_cpny_id' , 'budget_business_unit_id' , 
        'budget_department_fin_id', 'budget_account_id' , 'budget_activity_id' , 'reason_code' , 'issue_qty' , 'status' , 
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
