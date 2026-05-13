<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrAccessDetail extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'tr_access_detail';

    protected $fillable = [
        'docid',
        'access_id',
        'access_descr',
        'access_response',
        'access_username',
        'access_password',
        'access_process',
        'access_startdate',
        'access_enddate',
        'access_pic',
        'group_category',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];

    protected $casts = [
        'access_startdate' => 'date',
        'access_enddate' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

    public function header()
    {
        return $this->belongsTo(TrAccess::class, 'docid', 'docid');
    }
}
