<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewStagingSLPoDt extends Model
{
    // use SoftDeletes;
    protected $connection = 'pgsql4';
    protected $table = 'v_staging_insert_po_dt';

}