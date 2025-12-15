<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsTenant extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_tenant";

    protected $fillable = [
       
        'unit_id' , 'cpny_id' , 'store_name' , 'floor_id' , 'store_no' , 'status' , 'created_by' , 'created_at' , 
        'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at'
    ];
}
