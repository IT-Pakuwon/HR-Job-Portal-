<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrTicketActivity extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'tr_ticket_activity';

    protected $fillable = [
        'ticketid',
        'cpny_id',
        'department_id',
        'pic_ticket',
        'response_date',
        'response_summary',
        'response_descr',
        'working_start_date',
        'working_end_date',
        'status_pekerjaan',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'response_date' => 'datetime',
        'working_start_date' => 'datetime',
        'working_end_date' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(TrTicket::class, 'ticketid', 'ticketid');
    }
}
