<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsFindingSubItem extends Model
{
    protected $connection = 'pgsql7';
    protected $table = 'ms_finding_subitem';

    protected $fillable = [
        'finding_subitem',
        'finding_subitem_name',
        'categoryid',
        'finding_item',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
