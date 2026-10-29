<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrPlaudMeeting extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'tr_plaud_meeting';

    protected $fillable = [
        'plaud_file_id',
        'plaud_username',
        'plaud_name',
        'meeting_title',
        'meeting_date',
        'duration_seconds',
        'transcript_content',
        'transcript_format',
        'transcript_status',
        'transcript_synced_at',
        'sync_status',
        'last_sync_at',
        'sync_error',
        'plaud_metadata',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'meeting_date'         => 'datetime',
        'duration_seconds'     => 'integer',
        'transcript_synced_at' => 'datetime',
        'last_sync_at'         => 'datetime',
        'plaud_metadata'       => 'array',
    ];

    public function account()
    {
        return $this->belongsTo(MsPlaudAccount::class, 'plaud_username', 'plaud_username');
    }

    /** Summaries Plaud generated for this recording (same plaud_file_id). */
    public function summaries()
    {
        return $this->hasMany(TrPlaudSummary::class, 'plaud_file_id', 'plaud_file_id');
    }
}
