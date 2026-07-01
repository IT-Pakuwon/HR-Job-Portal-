<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrxVplTransfer extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'tr_vpl_transfer';

    protected $fillable = [
        'transfer_id',
        'transfer_date',
        'cpnyid',
        'department',
        'user_transfer',
        'vp_type',
        'transfertype',
        'transfer_remark',
        'ref_transfer_id',
        'status',
        'created_user',
        'updated_user',
        'completed_user',
        'completed_at',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(TrxVplTransferDetail::class, 'transfer_id', 'transfer_id')
            ->orderBy('linenbr');
    }
}
