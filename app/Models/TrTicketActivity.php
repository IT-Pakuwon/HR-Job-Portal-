<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrTicketActivity extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'tr_ticket_activity';

    public $timestamps = true;

    protected $fillable = [
        'ticketid',

        'cpny_id',
        'department_id',

        'pic_ticket',

        'activity_type',
        'activity_title',
        'activity_message',

        'working_start_date',
        'working_end_date',

        'response_date',

        'status_pekerjaan',
        'status',

        'is_system',

        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'response_date' => 'datetime',
        'working_start_date' => 'datetime',
        'working_end_date' => 'datetime',
        'is_system' => 'boolean',
    ];

    public function ticket()
    {
        return $this->belongsTo(
            TrTicket::class,
            'ticketid',
            'ticketid'
        );
    }

    public function getDurationMinutesAttribute()
    {
        if (
            !$this->working_start_date
            || !$this->working_end_date
        ) {
            return null;
        }

        return $this->working_start_date
            ->diffInMinutes($this->working_end_date);
    }
}
