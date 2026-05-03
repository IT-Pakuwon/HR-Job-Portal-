<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrTicket extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql5';
    protected $table = 'tr_ticket';
    protected $primaryKey = 'ticketid';
    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'ticketid',
        'ticketdate',
        'cpny_id',
        'department_id',
        'ticket_priority',
        'ticket_sla_days',
        'ticket_duedate',
        'ticket_type',
        'ticket_categoryid',
        'ticket_subcategoryid',
        'user_peminta',
        'location_id',
        'sub_location_id',
        'issue_summary',
        'issue_descr',
        'status',
        'pic_ticket',
        'pic_department',
        'pic_completed_ticket',
        'solution_descr',
        'status_pekerjaan',
        'reopen_ticket',
        'reopen_descr',
        'created_by',
        'updated_by',
        'deleted_by',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'ticketdate' => 'date',
        'ticket_duedate' => 'date',
        'completed_at' => 'datetime',
        'reopen_ticket' => 'boolean',
        'ticket_sla_days' => 'integer',
    ];

    public function type()
    {
        return $this->belongsTo(MsTicketType::class, 'ticket_type', 'ticket_type');
    }

    public function category()
    {
        return $this->belongsTo(MsTicketCategory::class, 'ticket_categoryid', 'ticket_categoryid');
    }

    public function subcategory()
    {
        return $this->belongsTo(MsTicketSubcategory::class, 'ticket_subcategoryid', 'ticket_subcategoryid');
    }

    public function priority()
    {
        return $this->belongsTo(MsTicketPriority::class, 'ticket_priority', 'ticket_priority');
    }

    public function activities()
    {
        return $this->hasMany(TrTicketActivity::class, 'ticketid', 'ticketid');
    }
}
