<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsEntity extends Model
{
    protected $connection = 'pgsql2';
    protected $table = 'ms_entity';

    protected $fillable = [
        'entity_id',
        'entity_name',
        'area_id',
        'group_cpny_id',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];
}
