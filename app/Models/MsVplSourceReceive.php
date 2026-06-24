<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsVplSourceReceive extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'ms_vpl_source_receive';

    protected $fillable = [
        'source_receive_id',
        'cpnyid',
        'source_receive_name',
        'status',
        'created_user',
        'updated_user',
    ];
}
