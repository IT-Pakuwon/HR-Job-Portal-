<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewStagingGRNReturn extends Model
{
    // use SoftDeletes;
    protected $connection = 'pgsql';
    protected $table = 'v_staging_grn_return';

}
