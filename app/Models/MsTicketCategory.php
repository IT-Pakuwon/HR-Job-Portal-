<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsTicketCategory extends Model
{
    // use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'ms_ticket_category';

    protected $fillable = [
        'ticket_categoryid',
        'ticket_category_name',
        'ticket_type',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function subcategories()
    {
        return $this->hasMany(MsTicketSubcategory::class, 'ticket_categoryid', 'ticket_categoryid');
    }
}
