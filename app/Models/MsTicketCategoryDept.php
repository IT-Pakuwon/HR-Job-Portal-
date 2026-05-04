<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsTicketCategoryDept extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql5';
    protected $table = 'ms_ticket_category_dept';
    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

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

    public function type()
    {
        return $this->belongsTo(MsTicketType::class, 'ticket_type', 'ticket_type');
    }

    public function category()
    {
        return $this->belongsTo(MsTicketCategory::class, 'ticket_categoryid', 'ticket_categoryid');
    }
}
