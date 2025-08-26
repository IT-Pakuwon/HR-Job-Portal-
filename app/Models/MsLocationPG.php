<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsLocationPG extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_location";

    protected $fillable = [
        'cpny_id',
        'location_id',
        'location_name',
        'status',
        'created_by',
        'updated_by'
    ];
}
