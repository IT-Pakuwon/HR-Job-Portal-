<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsFindingItem extends Model
{
    protected $connection = 'pgsql7';
    protected $table = 'ms_finding_item';

    protected $fillable = [
        'finding_item',
        'finding_name',
        'ref_department_id',
        'categoryid',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
