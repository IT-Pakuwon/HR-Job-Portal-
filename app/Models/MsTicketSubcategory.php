<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsTicketSubcategory extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'ms_ticket_subcategory';

    protected $fillable = [
        'ticket_subcategoryid',
        'ticket_subcategory_name',
        'ticket_type',
        'ticket_categoryid',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
