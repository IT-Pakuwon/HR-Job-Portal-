<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewInventoryMovementDetail extends Model
{
    protected $connection = 'pgsql'; // same as your other models

    protected $table = 'v_inventory_movement_detail';

    protected $primaryKey = null; // view usually has no PK

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];
}
