<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class TrAccess extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_access';

    protected $fillable = [
        'docid', 'access_date', 'cpny_id', 'department_id', 'location_id', 'user_peminta', 'user_assign', 'access_list', 'keperluan', 'access_type', 'status',
        'created_by', 'created_at', 'updated_by', 'updated_at', 'completed_by', 'completed_at',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

    public function details()
    {
        return $this->hasMany(
            TrAccessDetail::class,
            'docid',
            'docid'
        );
    }
}
