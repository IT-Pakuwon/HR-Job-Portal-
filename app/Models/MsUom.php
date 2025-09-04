<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsUom extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_uom";

    protected $fillable = [
        'inventoryid',
        'from_unit',
        'to_unit',
        'unitmultdiv',
        'unitrate',
        'status',
        'created_by',
        'updated_by'
    ];
}
