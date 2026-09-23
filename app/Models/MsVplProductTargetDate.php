<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsVplProductTargetDate extends Model
{
    protected $connection = 'pgsql5';

    // DB view: adjust name to match actual view in pgsql5
    protected $table = 'v_vpl_product_target_date';

    public $timestamps = false;
}
