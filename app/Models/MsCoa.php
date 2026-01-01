<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsCoa extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_coa";
    
    protected $fillable = [       
         'account_id', 'cpny_id', 'account_descr', 'account_type', 'category_type', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at'
    ];

   

}
