<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsKontrakPrefix extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'ms_kontrak_prefix';

    protected $fillable = [       
        'cpny_id',
        'business_unit_id',
        'autonbr_kontrak_prefix',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at'
    ];

    
}
