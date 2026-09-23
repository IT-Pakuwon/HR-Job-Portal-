<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vplreceive extends Model
{
    protected $table = "vpl_trx_receive";

    protected $fillable = [
        'receive_id',
        'receive_date',
        'cpnyid',
        'department',
        'user',
        'vp_type',
        'receive_type',
        'receive_company',
        'receive_tenant',
        'source_receive_id',
        'source_receive_dept',
        'receive_remark',        
        'created_user',
        'updated_user',
        'completed_user',        
        'status'
    ];
}
