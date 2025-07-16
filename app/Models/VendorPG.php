<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPG extends Model
{
    protected $connection = 'pgsql';
    protected $table = "vendor";

    protected $fillable = [
        'name',
        'contact',
        'phone',
        'address',
        'terms',
    ];
}
