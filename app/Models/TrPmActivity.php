<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// One row per change to a Project/Team or one of its Tasks — written by
// App\Services\PmActivityLogger, read back by the History drawer and a
// Task's Activity tab (see database/sql/pm_activity_log.sql).
class TrPmActivity extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_pm_activity';
    public $timestamps = false;

    protected $fillable = [
        'scope_type',
        'scope_id',
        'task_id',
        'action',
        'description',
        'changes',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];
}
