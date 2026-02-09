<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewLastorderBq extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'v_lastorder_bq';

    // view biasanya tidak punya primary key & tidak auto increment
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    // optional: biar bisa mass-assign kalau dibutuhkan
    protected $guarded = [];
}
