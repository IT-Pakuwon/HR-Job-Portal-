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

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'ticketid',
        'cpny_id',
        'department_id',
        'pic_ticket',
        'response_date',
        'response_summary',
        'response_descr',
        'status_pekerjaan',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'response_date' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(TrTicket::class, 'ticketid', 'ticketid');
    }
}
