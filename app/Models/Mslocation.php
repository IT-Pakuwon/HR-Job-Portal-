<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsLocation extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_location";

    protected $fillable = [       
        'cpny_id', 'location_id', 'location_name', 'status', 'created_by', 'created_at', 'updated_by', 
        'updated_at', 'deleted_by', 'deleted_at'
    ];
}
