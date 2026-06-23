<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Msproduct extends Model
{
    protected $table = "vpl_ms_product";
    protected $primaryKey = 'id';
    protected $fillable = [       
        'product_id',
        'cpnyid',
        'product_name',  
        'product_type',
        'product_category',     
        'product_source_type',
        'product_source_company',
        'product_source_tenant',
        'product_remark',
        'product_value',
        'product_uom',
        'product_check_exp',       
        'status',
        'created_user',
        'updated_user'        
    ];
}
