<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrxAccessDetail extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'trx_access_detail';

    protected $fillable = [
        'docid',
        'access_id',
        'access_descr',
        'access_process',
        'access_pic',
        'group_category',
        'status',
        'created_by',
        'updated_by',
    ];

    public function header()
    {
        return $this->belongsTo(TrxAccess::class, 'docid', 'docid');
    }
}
