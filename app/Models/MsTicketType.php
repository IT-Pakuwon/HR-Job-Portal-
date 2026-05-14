<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsTicketType extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'ms_ticket_type';

    protected $primaryKey = 'ticket_type';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'ticket_type',
        'ticket_type_name',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function categories()
    {
        return $this->hasMany(
            MsTicketCategory::class,
            'ticket_type',
            'ticket_type'
        );
    }

    public function priorities()
    {
        return $this->hasMany(
            MsTicketPriority::class,
            'ticket_type',
            'ticket_type'
        );
    }
}
