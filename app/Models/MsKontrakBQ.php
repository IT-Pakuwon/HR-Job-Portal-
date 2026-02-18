<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsKontrakBQ extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_kontrak_bq";
    
    protected $fillable = [
        'kontrak_bq_id','kontrak_bq_type','kontrakcategory','kontrak_bq_line_no','kontrak_bq_descr','kontrak_bq_uom','kontrak_duration_qty',
        'status' , 'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at'
    ];
}
