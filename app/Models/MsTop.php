<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsTop extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'ms_top';

    protected $fillable = [
        'topid',
        'top_name',
        'top_type',
        'top_days',
        'is_rfca',
        'is_fastapprove',
        'status',
        'created_by',        
        'updated_by'
    ];

    
}
