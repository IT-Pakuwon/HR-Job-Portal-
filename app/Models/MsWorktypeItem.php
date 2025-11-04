<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsWorktypeItem extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_worktype_item";

    protected $fillable = [
        // 'vendor_id',
        // 'vendor_name',
        // 'vendor_addr1',
        // 'vendor_addr2',
        // 'email',
        // 'contact_person',
        // 'phone_number',
        // 'status',
        // 'created_by',
        // 'updated_by',
        'worktypeid' , 'item_class' , 'status' , 'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 
        'deleted_by' , 'deleted_at'    
    ];
}
