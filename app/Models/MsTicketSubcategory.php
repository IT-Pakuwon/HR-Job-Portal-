<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsTicketSubcategory extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql5';
    protected $table = 'ms_ticket_subcategory';
    protected $primaryKey = 'ticket_subcategoryid';
    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

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

    public function type()
    {
        return $this->belongsTo(MsTicketType::class, 'ticket_type', 'ticket_type');
    }

    public function category()
    {
        return $this->belongsTo(MsTicketCategory::class, 'ticket_categoryid', 'ticket_categoryid');
    }
}
