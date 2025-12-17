<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsInvItemClass extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_inventory_item_class";

    protected $fillable = [      
         'item_class_id' , 'item_class_name' , 'item_sub_type_id' , 'status' , 'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at'

    ];
}
