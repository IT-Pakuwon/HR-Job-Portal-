<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrxVplUsage extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'trx_vpl_usage';

    protected $fillable = [
        'usage_id',
        'usage_date',
        'cpnyid',
        'department',
        'user_peminta',
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
        'completed_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(TrxVplUsageDetail::class, 'usage_id', 'usage_id')
            ->orderBy('linenbr');
    }
}
