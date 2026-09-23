<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsLuckydrawPrize extends Model
{
    // use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'ms_luckydraw_prize';

    protected $fillable = [
        'prize_id',
        'event_id',
        'prize_name',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function event()
    {
        return $this->belongsTo(MsLuckydrawEvent::class, 'event_id', 'event_id');
    }

    public function winners()
    {
        return $this->hasMany(TrLuckydrawWinner::class, 'prize_id', 'prize_id');
    }
}
