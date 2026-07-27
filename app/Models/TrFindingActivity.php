<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrFindingActivity extends Model
{
    protected $connection = 'pgsql7';
    protected $table = 'tr_finding_activity';

    protected $fillable = [
        'finding_id',
        'activity_date',
        'activity_descr',
        'status_activity',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'datetime',
        ];
    }
}
