<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


use App\Models\BudgetDetail;
class TrSPPBdetail extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_sppb_detail";

    protected $fillable = [
        'sppbid' , 'sppb_no' , 'inventory_type' , 'inventory_sub_type' , 'inventory_category' , 'inventoryid' , 'inventory_descr' ,
        'qty' , 'uom' , 'siteid' , 'type_multiplier' , 'base_multiplier' , 'base_qty' , 'base_uom' , 'note' , 'location_id' ,
        'sub_location_id' , 'budget_perpost' , 'budget_cpny_id' , 'budget_business_unit_id' , 'budget_department_fin_id' ,
        'budget_account_id' , 'budget_activity_id' , 'budget_activity_descr' , 'assignby' , 'assigndate' , 'assignpurchasing' ,
        'openordered' , 'ordered' , 'rejectordered' , 'completeordered' , 'spbid' , 'spb_no' , 'status' , 'created_by' , 'created_at' ,
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

        public function budget()
    {
        $table = $this->getTable();

        return $this->hasOne(BudgetDetail::class, 'account_id', 'budget_account_id')
            ->whereColumn('ms_budget.cpny_id', $table.'.budget_cpny_id')
            ->whereColumn('ms_budget.business_unit_id', $table.'.budget_business_unit_id')
            ->whereColumn('ms_budget.department_fin_id', $table.'.budget_department_fin_id')
            ->whereColumn('ms_budget.activity_id', $table.'.budget_activity_id')
            ->whereColumn('ms_budget.perpost', $table.'.budget_perpost');
    }
}
