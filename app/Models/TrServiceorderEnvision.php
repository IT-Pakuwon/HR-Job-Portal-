<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrServiceorderEnvision extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'tr_serviceorder_envision';

    protected $fillable = [
        'serviceorderid',
        'serviceorderdate',
        'user_pembuat',
        'schedule_date',
        'visit_date_start',
        'visit_date_end',
        'user_pic',
        'job_type',
        'ticketid',
        'serviceorder_descr',
        'serviceorder_action',
        'job_status',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'serviceorderdate' => 'date',
        'schedule_date' => 'date',
        'visit_date_start' => 'datetime',
        'visit_date_end' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(
            TrTicket::class,
            'ticketid',
            'ticketid'
        );
    }
}
