<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsBaseUom extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_base_uom";

    protected $fillable = [      
        'uomid',
        'uom_description',
        'status',
        'created_by',
        'updated_by'

    ];
}
