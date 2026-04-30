<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrItrecommend extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'tr_itrecommend';

    protected $fillable = [
        'docid',
        'itrecommend_date',
        'cpny_id',
        'department_id',
        'location_id',
        'user_peminta',
        'keperluan',
        'assetnbr',
        'ticketnbr',
        'recommend_type',
        'waranty',
        'recommendation',
        'recommend_pic',
        'status',
        'created_by',
        'updated_by',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'itrecommend_date' => 'date',
        'completed_at'     => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(TrItrecommendDetail::class, 'docid', 'docid');
    }
}
