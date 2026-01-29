<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class Viewtrxall extends Model
{
    protected $connection = 'pgsql3';
    // protected $table = "viewtrxallnew";
    protected $table = "view_trx_career";
    
}
