<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrLuckydraw extends Model
{
    // use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'tr_luckydraw';

    protected $fillable = [
        'event_id',
        'event_date',
        'customer_name',
        'company_name',
        'ref_nbr',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'event_date' => 'date:Y-m-d',
    ];

    public function event()
    {
        return $this->belongsTo(MsLuckydrawEvent::class, 'event_id', 'event_id');
    }
}
