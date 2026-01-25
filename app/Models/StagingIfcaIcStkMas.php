<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StagingIfcaIcStkMas  extends Model
{

    protected $connection = 'pgsql3';
    protected $table = "staging_ifca_ic_stkmas";
    protected $primaryKey = 'id';
    protected $fillable = [
        'stock_cd','stock_descs','uom_cd','group_cd','product_cd','catalog_no','type_cd','class_cd',
        'abc_flag','shelf_life','std_cost','serial_ctrl','lot_ctrl','voucher_flag','voucher_amt',
        'process_flag','create_date','process_dt','process_note','status','created_by','created_at','updated_by','updated_at',
    ];
}