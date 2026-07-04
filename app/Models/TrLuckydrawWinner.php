<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrLuckydrawWinner extends Model
{
    // use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'tr_luckydraw_winner';

    protected $fillable = [
        'event_id',
        'customer_name',
        'company_name',
        'ref_nbr',
        'prize_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function event()
    {
        return $this->belongsTo(MsLuckydrawEvent::class, 'event_id', 'event_id');
    }

    public function prize()
    {
        return $this->belongsTo(MsLuckydrawPrize::class, 'prize_id', 'prize_id');
    }
}
