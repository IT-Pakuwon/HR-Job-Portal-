<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Msproductdetail extends Model
{
    protected $table = "vpl_ms_product_detail";
    protected $primaryKey = 'id';
    protected $fillable = [       
        'product_id',
        'cpnyid',
        'expired_date',  
        'whs_id',
        'qty_available',     
        'qty_reserved',   
        'status',
        'created_user',
        'updated_user'        
    ];
}
