<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrPerizinanActivity extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'tr_perizinan_activity';

    protected $casts = [
        'response_date' => 'datetime',
    ];

    protected $fillable = [        
        'perizinan_id',
        'pic_perizinan',
        'response_date',
        'response_descr',
        'status_pekerjaan',
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
