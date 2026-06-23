<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vpladjustment extends Model
{
    protected $table = "vpl_trx_adjustment";

    protected $fillable = [
        'adjustment_id',
        'adjustment_date',
        'cpnyid',
        'department',
        'user',    
        'adjustment_remark',        
        'created_user',
        'updated_user',
        'completed_user',        
        'status'
    ];
}
