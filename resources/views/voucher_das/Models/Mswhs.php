<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mswhs extends Model
{
    protected $table = "vpl_ms_warehouse";
    protected $primaryKey = 'id';
    protected $fillable = [       
        'whs_id',
        'cpnyid',
        'whs_name',
        'whs_type',
        'status',
        'created_user',
        'updated_user'        
    ];
}
