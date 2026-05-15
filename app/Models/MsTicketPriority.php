<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsTicketPriority extends Model
{
    // use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'ms_ticket_priority';

    protected $fillable = [
        'ticket_type',
        'ticket_categoryid',
        'ticket_priority',
        'ticket_priority_name',
        'ticket_sla_days',
        'is_default',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
