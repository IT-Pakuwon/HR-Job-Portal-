<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessUnit extends Model
{
    protected $connection = 'pgsql2';
    protected $table = "ms_business_unit";

    protected $fillable = [        
        'business_unit_id' , 'cpny_id' , 'business_unit_name' , 'ifca_entity_cd','solomon_cpny_id','status' , 
        'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at'
    ];
}
