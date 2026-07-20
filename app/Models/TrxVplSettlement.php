<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrxVplSettlement extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'tr_vpl_settlement';

    protected $fillable = [
        'settlement_id',
        'settlement_date',
        'usage_id',
        'cpnyid',
        'department',
        'user_peminta',
        'vp_type',
        'settlement_remark',
        'status',
        'created_user',
        'updated_user',
        'completed_user',
        'completed_at',
    ];

    protected $casts = [
        'settlement_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(TrxVplSettlementDetail::class, 'settlement_id', 'settlement_id')
            ->orderBy('linenbr');
    }

    public function usage()
    {
        return $this->belongsTo(TrxVplUsage::class, 'usage_id', 'usage_id');
    }
}
