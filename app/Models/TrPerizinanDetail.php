<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrPerizinanDetail extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'tr_perizinan_detail';

    protected $fillable = [        
        'perizinan_id',
        'item_perizinan',
        'qty_perizinan',       
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
        'completed_by',
        'completed_at'
    ];
}
