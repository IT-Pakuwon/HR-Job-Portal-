<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    // use SoftDeletes;

    public const STATUS_MAP = [
        'PENDING' => 'P',
        'ACTIVE'  => 'A',
        'DONE'    => 'D',
        'CANCEL'  => 'C',
    ];

    public const STATUS_LABELS = [
        'P' => 'Pending',
        'A' => 'Active',
        'D' => 'Done',
        'C' => 'Cancelled',
    ];


    protected $connection = 'pgsql2'; // same as UserGoogle
    protected $table = 'tr_task';

    protected $fillable = [
        'taskid',
        'taskdate',
        'task_title',
        'start_date',
        'end_date',
        'task_description',
        'task_location',
        'sync_to_google',
        'google_event_id',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'taskdate' => 'date',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'sync_to_google' => 'boolean',
    ];
}
