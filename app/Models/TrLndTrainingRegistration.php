<?php

namespace App\Models;

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
}
