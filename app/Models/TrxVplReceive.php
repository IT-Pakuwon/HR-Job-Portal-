<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrxVplReceive extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'trx_vpl_receive';

    protected $fillable = [
        'receive_id',
        'receive_date',
        'cpnyid',
        'department',
        'user_penerima',
        'receive_type',
        'receive_company',
        'receive_tenant',
        'source_receive_id',
        'source_receive_dept',
        'receive_remark',
        'status',
        'created_user',
        'updated_user',
        'completed_user',
        'completed_at',
    ];

    protected $casts = [
        'receive_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(TrxVplReceiveDetail::class, 'receive_id', 'receive_id')
            ->orderBy('linenbr');
    }

    public function sourceReceive()
    {
        return $this->belongsTo(MsVplSourceReceive::class, 'source_receive_id', 'source_receive_id');
    }
}
