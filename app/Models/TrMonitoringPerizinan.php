<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrMonitoringPerizinan extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_monitoring_perizinan';

    protected $fillable = [
        'perizinanid',
        'perizinandate',
        'cpny_id',
        'site_id',
        'department_id',
        'user_peminta',
        'perizinancategory',
        'perizinandescr',
        'perizinannote',
        'perizinan_qty',
        'startdate',
        'enddate',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'perizinandate' => 'date',
        'startdate'     => 'date',
        'enddate'       => 'date',
        'completed_at'  => 'datetime',
    ];
}
