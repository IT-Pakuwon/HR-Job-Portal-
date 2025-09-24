<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsTenant extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_tenant";

    protected $fillable = [
        'unit_id',
        'cpny_id',
        'store_name',
        'floor_id',
        'store_no',
        'status',     
        'created_by',
        'updated_by', 
    ];
}
