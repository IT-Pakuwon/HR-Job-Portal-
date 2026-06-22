<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StagingIfcaIcStkIssueReturn  extends Model
{
    protected $connection = 'pgsql4';
    protected $table = "staging_ifca_ic_stk_receipt";
    protected $primaryKey = 'id';
    protected $fillable = [       
        'cpny_id' , 'entity_cd' , 'issuereturn_id' , 'issuereturn_date' , 'receipthd_descs' , 'reference_no' , 
        'department_id' , 'keeper' , 'keeper_date' , 'ic_location' , 'trx_cd' , 'div_cd' , 'dept_cd' , 'total_record' , 
        'line_no' , 'item_cd' , 'item_remark' , 'uom' , 'receipt_qty' , 'unit_cost' , 'process_flag' , 'create_date' , 
        'process_dt' , 'process_note' , 'status' , 'created_by' , 'created_at' , 'updated_by' , 'updated_at'
        ];
}