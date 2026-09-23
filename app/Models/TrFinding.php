<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrFinding extends Model
{
    protected $connection = 'pgsql7';
    protected $table = 'tr_finding';

    protected $fillable = [
        'finding_id',
        'finding_date',
        'cpny_id',
        'department_id',
        'location_id',
        'sub_location_id',
        'finding_category',
        'finding_item',
        'finding_subitem',
        'issue',
        'solution',
        'user_solution',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
        'completed_by',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'finding_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }
}
