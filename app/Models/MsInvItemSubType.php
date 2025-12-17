<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsInvItemSubType extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_inventory_sub_type";

    protected $fillable = [      
        'item_sub_type_id' , 'item_type_id' , 'item_sub_type_name' , 'status' , 'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at'

    ];
}
