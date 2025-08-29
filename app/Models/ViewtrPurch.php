<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class ViewtrPurch extends Model
{
    protected $connection = 'pgsql';
    protected $table = "v_tr_purch";  


}
