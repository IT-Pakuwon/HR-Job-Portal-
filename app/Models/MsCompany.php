<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsCompany extends Model
{
    protected $connection = 'pgsql2';
    protected $table = "ms_company";

    protected $fillable = [       
        'cpny_id', 'cpny_name', 'address_line1', 'address_line2', 'city', 
        'province', 'postalcode', 'phone', 'fax', 'tax_registration', 
        'tax_address_line', 'warehouse_note', 'status', 'created_by', 
        'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at'
    ];
}
