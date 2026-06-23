<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mssource extends Model
{
    protected $table = "vpl_ms_source_receive";
    protected $primaryKey = 'id';
    protected $fillable = [       
        'source_receive_id',
        'cpnyid',
        'source_receive_name',       
        'status',
        'created_user',
        'updated_user'        
    ];
}
