<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vplrequest extends Model
{
    protected $table = "vpl_trx_request";

    protected $fillable = [
        'request_id',
        'request_date',
        'cpnyid',
        'department',
        'user',
        'vp_type',
        'requesttype',       
        'request_remark',    
        'ref_request_id',    
        'created_user',
        'updated_user',
        'completed_user',
        'status'
    ];
}
