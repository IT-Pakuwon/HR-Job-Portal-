<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsLndTrainingSchedule extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'ms_lnd_training_schedule';

    public $timestamps = true;

    protected $fillable = [
        'schedule_id',
        'training_id',
        'training_detail_id',
        'schedule_date',
        'schedule_start_time',
        'schedule_end_time',
        'places_id',
        'training_mode',
        'training_platform',
        'training_meeting_link',
        'registration_deadline',
        'training_speaker_username',
        'training_speaker_name',
        'training_ext_speaker_name',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
        'feedback_opened_at',
        'feedback_opened_by',
        'feedback_closed_at',
        'feedback_closed_by',
    ];

    protected $casts = [
        'feedback_opened_at' => 'datetime',
        'feedback_closed_at' => 'datetime',
        'schedule_date' => 'date',
        'deleted_at' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(MsLndTrainingDetail::class, 'training_detail_id', 'training_detail_id');
    }

    public function quota()
    {
        return $this->hasMany(MsLndTrainingQuota::class, 'schedule_id', 'schedule_id');
    }

    /**
     * HR-controlled gate: feedback_opened_at must be set, and if it was ever
     * closed, feedback_closed_at must be null again (re-opening clears it).
     */
    public function getIsFeedbackOpenAttribute(): bool
    {
        return $this->feedback_opened_at !== null && $this->feedback_closed_at === null;
    }

    /**
     * Certificates unlock once HR closes the feedback window for this
     * schedule — an explicit HR action rather than a date-based delay, so
     * there's no certificate until feedback has actually been wrapped up.
     * If HR re-opens feedback (which clears feedback_closed_at), this flips
     * back to false until they close it again.
     */
    public function getIsCertificateReadyAttribute(): bool
    {
        return $this->feedback_closed_at !== null;
    }

    /**
     * A schedule that has already happened can no longer take registrations
     * or waitlist joins, regardless of registration_deadline. Cutoff is the
     * session's own end time (falling back to end of day when unset), same
     * boundary HasAttendanceWindow uses for the event's start.
     */
    public function getIsScheduleOverAttribute(): bool
    {
        if (!$this->schedule_date) {
            return false;
        }

        $until = $this->schedule_end_time
            ? Carbon::parse($this->schedule_date->format('Y-m-d').' '.$this->schedule_end_time)
            : $this->schedule_date->copy()->endOfDay();

        return now()->greaterThan($until);
    }
}
