<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsSubLocation extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_sub_location";

    protected $fillable = [
        'cpny_id', 'sub_location_id', 'location_id', 'sub_location_name', 'status', 
        'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at'
    ];
}
