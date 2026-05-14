<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsTicketPriority extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'ms_ticket_priority';

    protected $fillable = [
        'ticket_type',
        'ticket_categoryid',
        'ticket_priority',
        'ticket_priority_name',
        'ticket_sla_days',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'ticket_sla_days' => 'integer',
    ];

    public function type()
    {
        return $this->belongsTo(
            MsTicketType::class,
            'ticket_type',
            'ticket_type'
        );
    }

    public function category()
    {
        return $this->belongsTo(
            MsTicketCategory::class,
            'ticket_categoryid',
            'ticket_categoryid'
        );
    }
}
