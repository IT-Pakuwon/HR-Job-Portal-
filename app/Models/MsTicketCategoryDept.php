<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsTicketCategoryDept extends Model
{
    // use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'ms_ticket_category_dept';

    protected $fillable = [
        'ticket_type',
        'ticket_categoryid',
        'department_id',
        'username',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
