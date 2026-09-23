<?php

namespace App\Models;

use App\Models\Concerns\NoTimezoneShift;
use Illuminate\Database\Eloquent\Model;

class TrxVplUsage extends Model
{
    use NoTimezoneShift;

    protected $connection = 'pgsql5';

    protected $table = 'tr_vpl_usage';

    protected $fillable = [
        'usage_id',
        'usage_date',
        'event_date',
        'cpnyid',
        'department',
        'user_peminta',
        'vp_type',
        'usagetype',
        'usage_remark',
        'ref_usage_id',
        'status',
        'created_user',
        'updated_user',
        'completed_user',
        'completed_at',
    ];

    protected $casts = [
        'usage_date' => 'date',
        'event_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(TrxVplUsageDetail::class, 'usage_id', 'usage_id')
            ->orderBy('linenbr');
    }
}
