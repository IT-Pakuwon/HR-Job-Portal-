<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsTicketType extends Model
{
    // use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'ms_ticket_type';

    protected $fillable = [
        'ticket_type',
        'ticket_type_name',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
