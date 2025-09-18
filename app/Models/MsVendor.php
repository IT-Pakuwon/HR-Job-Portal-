<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsVendor extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_vendor";

    protected $fillable = [
        'vendor_id',
        'vendor_name',
        'vendor_addr1',
        'vendor_addr2',
        'email',
        'contact_person',
        'phone_number',
        'status',
        'created_by',
        'updated_by',
    ];
}
