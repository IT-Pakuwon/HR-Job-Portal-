<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrLndTrainingRegistration extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'tr_lnd_training_registration';

    public $timestamps = true;

    /** Approval status ONLY. */
    public const STATUS_PENDING = 'P';
    public const STATUS_APPROVED = 'C';
    public const STATUS_REJECTED = 'R';

    /** Registration lifecycle (status_registration). */
    public const REG_STATUS_WAITLISTED = 'W';
    public const REG_STATUS_OFFERED = 'O';
    public const REG_STATUS_CANCELLED = 'X';

    protected $fillable = [
        'training_regist_id',
        'training_regist_date',
        'training_id',
        'training_detail_id',
        'schedule_id',
        'schedule_date',
        'cpny_id',
        'department_id',
        'user_registration',
        'qty_registration',
        'status',
        'status_registration',
        'process_registration_user',
        'process_registration_date',
        'attendance_code',
        'completed_by',
        'completed_at',
        'certificate_notified_at',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'training_regist_date' => 'date',
        'schedule_date' => 'date',
        'qty_registration' => 'integer',
        'process_registration_date' => 'datetime',
        'completed_at' => 'datetime',
        'certificate_notified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(MsLndTrainingSchedule::class, 'schedule_id', 'schedule_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_registration', 'username');
    }

    /**
     * training_regist_id is a 1:1 key per row (see TrainingRegistrationController::register()),
     * so this reliably scopes to just this participant's own scan/undo history —
     * ordered oldest first so it reads as a timeline.
     */
    public function attendances()
    {
        return $this->hasMany(TrLndTrainingAttendance::class, 'training_regist_id', 'training_regist_id')
            ->orderBy('attendance_datetime');
    }

    public function feedbackAnswers()
    {
        return $this->hasMany(TrLndTrainingFeedbackAnswer::class, 'training_regist_id', 'training_regist_id')
            ->orderBy('question_order');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Actually holds a seat (not waitlisted/offered/cancelled). Approval
     * status alone is no longer enough — a waitlisted row also reaches 'C'
     * when its batch approval completes, without ever holding a seat.
     */
    public function scopeSeated($query)
    {
        return $query->whereNull('status_registration');
    }

    /**
     * Effective status exposed to the UI — lifecycle flag wins over the
     * approval flag (a cancelled/waitlisted/offered row never renders as
     * plain "pending/approved").
     */
    public function getEffectiveStatusAttribute(): ?string
    {
        if ($this->status_registration) {
            return $this->status_registration;
        }

        return $this->status;
    }

    /**
     * Waitlist offer expiry = process_registration_date + 24 hours.
     */
    public function getOfferExpiresAtAttribute(): ?\Illuminate\Support\Carbon
    {
        return $this->process_registration_date?->copy()->addHours(24);
    }

    /**
     * Late = checked in more than 30 minutes after the session's own start
     * time. Null (not "false") when the person never attended, since
     * lateness is only meaningful once completed_at exists.
     */
    public function getIsLateAttendanceAttribute(): ?bool
    {
        if (!$this->completed_at) {
            return null;
        }

        $scheduleDate = $this->schedule_date ?? $this->schedule?->schedule_date;
        $startTime = $this->schedule?->schedule_start_time;

        if (!$scheduleDate || !$startTime) {
            return false;
        }

        $eventStart = Carbon::parse($scheduleDate->format('Y-m-d').' '.$startTime);

        return $this->completed_at->greaterThan($eventStart->addMinutes(30));
    }

    /**
     * Attendance star: 4 for on-time, 3 for late (>30 min), 0 if never
     * checked in. Re-derives from completed_at rather than being stored, so
     * HCDEV's unmarkAttend() correction can't leave a stale value behind.
     */
    public function getAttendanceStarsAttribute(): int
    {
        if (!$this->completed_at) {
            return 0;
        }

        return $this->is_late_attendance ? 3 : 4;
    }

    /**
     * +1 for submitting feedback. Submission itself is only possible while
     * attended and the schedule's feedback window is open (see
     * TrainingFeedbackController::submit), so an answer existing already
     * proves it was earned honestly — no separate "was it still open" check
     * needed here.
     */
    public function getFeedbackStarsAttribute(): int
    {
        return $this->feedbackAnswers()->exists() ? 1 : 0;
    }

    public function getStarsAttribute(): int
    {
        return $this->attendance_stars + $this->feedback_stars;
    }
}
