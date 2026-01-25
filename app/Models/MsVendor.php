<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsVendor extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_vendor";

    protected $fillable = [        
        'vendor_id' , 'vendor_name' , 'vendor_addr1' , 'vendor_addr2' , 'email' , 'contact_person' , 'phone_number' , 
        'npwp', 'contact_email', 'contact_number1', 'contact_number2', 'fax_no', 'post_cd',
        'status' , 'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at'
    ];
}
