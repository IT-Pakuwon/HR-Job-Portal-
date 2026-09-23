<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsLuckydrawEvent extends Model
{
    // use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'ms_luckydraw_event';

    protected $fillable = [
        'event_id',
        'event_name',
        'event_date',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'event_date' => 'date:Y-m-d',
    ];

    public function prizes()
    {
        return $this->hasMany(MsLuckydrawPrize::class, 'event_id', 'event_id');
    }

    public function participants()
    {
        return $this->hasMany(TrLuckydraw::class, 'event_id', 'event_id');
    }

    public function winners()
    {
        return $this->hasMany(TrLuckydrawWinner::class, 'event_id', 'event_id');
    }
}
