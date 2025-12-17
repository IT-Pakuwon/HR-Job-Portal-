<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsInvItemSubClass extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_inventory_item_sub_class";

    protected $fillable = [      
        'item_sub_class_id' , 'item_class_id' , 'item_sub_class_name' , 'autonbr' , 'status' , 'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at'         

    ];
}
