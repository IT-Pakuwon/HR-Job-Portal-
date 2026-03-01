<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StagingIfcaIcStkIssue  extends Model
{
    protected $connection = 'pgsql4';
    protected $table = "staging_ifca_ic_stk_issue";
    protected $primaryKey = 'id';
    protected $fillable = [

        'cpny_id' , 'entity_cd' , 'issue_id' , 'issue_date' , 'issuehd_descs' , 'reference_no' , 'spb_id' , 'wo_id' , 
        'ref_issue_id' , 'department_id' , 'user_peminta' , 'keeper' , 'total_record' , 'line_no' , 'item_cd' , 
        'item_remark' , 'uom' , 'issue_qty' , 'budget_business_unit_id' , 'budget_department_fin_id' , 'budget_account_id' , 
        'integration_type' , 'ic_location' , 'trx_cd' , 'div_cd' , 'dept_cd' , 
        'solomon_reason_cd' , 'solomon_acct_cd' , 'solomon_allocation_cd' , 'solomon_subaccount_dept' , 
        'process_flag' , 'create_date' , 'process_dt' , 'process_note' , 'status' , 'created_by' , 'created_at' , 
        'updated_by' , 'updated_at' , 'reviewed_by' , 'reviewed_at' , 'reviewed_note'
        ];
}