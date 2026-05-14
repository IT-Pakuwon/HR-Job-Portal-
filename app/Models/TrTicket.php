<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\TrAttachment;

class TrTicket extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'tr_ticket';

    protected $fillable = [
        'ticketid',
        'ticketdate',

        'cpny_id',
        'department_id',

        'ticket_type',
        'ticket_categoryid',
        'ticket_subcategoryid',

        'ticket_priority',
        'ticket_sla_days',
        'ticket_duedate',

        'user_peminta',

        'location_id',
        'sub_location_id',

        'issue_summary',
        'issue_descr',

        'pic_ticket',

        'solution_descr',

        'status',
        'status_pekerjaan',

        'reopen_descr',

        'response_by',
        'response_at',

        'completed_by',
        'completed_at',

        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'ticketdate' => 'date',
        'ticket_duedate' => 'datetime',
        'response_at' => 'datetime',
        'completed_at' => 'datetime',
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

    public function subcategory()
    {
        return $this->belongsTo(
            MsTicketSubcategory::class,
            'ticket_subcategoryid',
            'ticket_subcategoryid'
        );
    }

    public function priority()
    {
        return $this->belongsTo(
            MsTicketPriority::class,
            'ticket_priority',
            'ticket_priority'
        );
    }

    public function activities()
    {
        return $this->hasMany(
            TrTicketActivity::class,
            'ticketid',
            'ticketid'
        )
        ->orderBy('response_date');
    }

    public function attachments()
    {
        return $this->hasMany(
            TrAttachment::class,
            'refnbr',
            'ticketid'
        );
    }
}
