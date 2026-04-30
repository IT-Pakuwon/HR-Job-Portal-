<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrxAccess extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'trx_access';

    protected $fillable = [
        'docid',
        'access_date',
        'cpny_id',
        'department_id',
        'location_id',
        'user_peminta',
        'user_assign',
        'access_list',
        'keperluan',
        'access_type',
        'status',
        'created_by',
        'updated_by',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'access_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(TrxAccessDetail::class, 'docid', 'docid');
    }
}
