<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StagingIfcaPoApprove  extends Model
{
    protected $connection = 'pgsql4';
    protected $table = "staging_ifca_po_approve";
    protected $primaryKey = 'id';
    protected $fillable = [
        'cpny_id' , 'entity_cd' , 'order_no' , 'order_type' , 'order_date' , 'supplier_cd' , 'remark' , 
        'ref_no_sppbjkt' , 'ref_no_cs' , 'department_id' , 'user_peminta' , 'purchaser' , 'topid' , 
        'credit_terms' , 'currency_cd' , 'currency_rate' , 'total_record' , 'order_line' , 'item_cd' , 'item_remark' , 
        'uom' , 'order_qty' , 'item_cost' , 'schedule_dt' , 'acct_type' , 'location_cd' , 
        'budget_business_unit_id' , 'budget_department_fin_id' , 'budget_account_id' , 
        'integration_type' , 'acct_cd' , 'div_cd' , 'dept_cd' , 'solomon_acct_cd' , 'solomon_allocation_cd' , 
        'solomon_subaccount_dept' , 'process_flag' , 'create_date' , 'process_dt' , 'process_note' , 'status' , 
        'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 'reviewed_by' , 'reviewed_at' , 'reviewed_note'
        ];
}
