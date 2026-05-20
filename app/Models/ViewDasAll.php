<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class ViewDasAll extends Model
{
    // protected $connection = 'mysql2';
    // protected $table = "viewtrxalldas";
    protected $connection = 'pgsql5';
    protected $table = "v_all_das";
    public $timestamps = false;
    


}
