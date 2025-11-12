<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewInventoryGPS extends Model
{
    protected $connection = 'sqlsrv6';
    protected $table = "view_inventory";

    
}
