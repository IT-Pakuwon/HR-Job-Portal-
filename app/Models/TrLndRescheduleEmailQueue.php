<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrLndRescheduleEmailQueue extends Model
{
    public const STATUS_PENDING = 'P';
    public const STATUS_SENT = 'S';
    public const STATUS_FAILED = 'F';

    protected $connection = 'pgsql5';

    protected $table = 'tr_lnd_reschedule_email_queue';

    public $timestamps = true;

    protected $fillable = [
        'schedule_id',
        'training_regist_id',
        'old_date',
        'new_date',
        'time_label',
        'status',
        'attempts',
        'last_error',
        'sent_at',
        'created_by',
    ];

    protected $casts = [
        'old_date' => 'date',
        'new_date' => 'date',
        'attempts' => 'integer',
        'sent_at' => 'datetime',
    ];
}
