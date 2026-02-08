<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewStagingPO extends Model
{
    // use SoftDeletes;
    protected $connection = 'pgsql';
    protected $table = 'v_staging_po';

}
