<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vpltransfer extends Model
{
    protected $table = "vpl_trx_transfer";

    protected $fillable = [
        'transfer_id',
        'transfer_date',
        'cpnyid',
        'department',
        'user',
        'vp_type',
        'transfertype',       
        'transfer_remark',    
        'ref_transfer_id',    
        'created_user',
        'updated_user',
        'completed_user',
        'status'
    ];
}
