<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrxVplAdjustment extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'tr_vpl_adjustment';

    protected $fillable = [
        'adjustment_id',
        'adjustment_date',
        'cpnyid',
        'department',
        'user_adjustment',
        'vp_type',
        'adjustment_remark',
        'status',
        'created_user',
        'updated_user',
        'completed_user',
        'completed_at',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(TrxVplAdjustmentDetail::class, 'adjustment_id', 'adjustment_id')
            ->orderBy('linenbr');
    }
}
