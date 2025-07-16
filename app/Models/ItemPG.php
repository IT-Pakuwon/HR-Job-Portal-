<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPG extends Model
{
    protected $connection = 'pgsql';
    protected $table = "item";

    protected $fillable = [
        'description',
        'qty',
        'uom',       
    ];
}
