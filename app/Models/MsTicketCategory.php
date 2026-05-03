<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsTicketCategory extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql5';
    protected $table = 'ms_ticket_category';
    protected $primaryKey = 'ticket_categoryid';
    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'ticket_categoryid',
        'ticket_category_name',
        'ticket_type',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function type()
    {
        return $this->belongsTo(MsTicketType::class, 'ticket_type', 'ticket_type');
    }

    public function subcategories()
    {
        return $this->hasMany(MsTicketSubcategory::class, 'ticket_categoryid', 'ticket_categoryid');
    }

    public function priorities()
    {
        return $this->hasMany(MsTicketPriority::class, 'ticket_categoryid', 'ticket_categoryid');
    }

    public function departments()
    {
        return $this->hasMany(MsTicketCategoryDept::class, 'ticket_categoryid', 'ticket_categoryid');
    }
}
