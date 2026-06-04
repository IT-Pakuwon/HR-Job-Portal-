<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsPerizinanCategory extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'ms_perizinan_category';

    protected $fillable = [
        'perizinancategory',
        'perizinancategory_descr',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
