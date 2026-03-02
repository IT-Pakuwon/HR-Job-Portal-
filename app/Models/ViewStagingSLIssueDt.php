<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewStagingSLIssueDt extends Model
{
    // use SoftDeletes;
    protected $connection = 'pgsql4';
    protected $table = 'v_staging_insert_issue_dt';

}