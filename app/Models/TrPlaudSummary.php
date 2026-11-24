<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrPlaudSummary extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'tr_plaud_summary';

    protected $fillable = [
        'plaud_file_id',
        'plaud_username',
        'plaud_name',
        'meeting_title',
        'summary_type',
        'summary_title',
        'summary_content',
        'summary_format',
        'action_items',
        'summary_metadata',
        'sync_status',
        'last_sync_at',
        'sync_error',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'action_items'     => 'array',
        'summary_metadata' => 'array',
        'last_sync_at'     => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(MsPlaudAccount::class, 'plaud_username', 'plaud_username');
    }

    public function meeting()
    {
        return $this->belongsTo(TrPlaudMeeting::class, 'plaud_file_id', 'plaud_file_id');
    }
}
