<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BqDetail extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_bq_detail";
    
    protected $fillable = [
        'bqid' , 'sppjtid' , 'bq_no' , 'bq_line_no' , 'bq_descr' , 'qty' , 'uom' , 
        'est_material_price' , 'total_est_material_price' , 'est_jasa_price' , 'total_est_jasa_price' , 'bqtype',
        'kontrakcategory', 'kontrak_bq_id', 'kontrak_bq_type', 'kontrak_duration_qty',
        'status' , 'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at'
    ];
}
