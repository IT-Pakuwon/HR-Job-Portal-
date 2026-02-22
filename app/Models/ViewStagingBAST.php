<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewStagingBAST extends Model
{
    // use SoftDeletes;
    protected $connection = 'pgsql';
    protected $table = 'v_staging_bast';

}
