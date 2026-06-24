<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SelfPosting extends Model
{
    protected $connection = 'mysql3';
    protected $table = 'hr_trx_selfposting';

    // Primary key default: id (auto increment)
    protected $primaryKey = 'id';

    // Karena ada created_at & updated_at
    public $timestamps = true;

    protected $fillable = [
        'docid',
        'departementid',
        'division_id',
        'date',
        'job_title',
        'is_read',
        'status',
        'created_user',
        'updated_user',
        'completed_user',
        'completed_at',
    ];

    protected $casts = [
        'date' => 'date',
        'experience_start' => 'date',
        'experience_end' => 'date',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
