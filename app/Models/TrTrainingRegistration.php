<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrTrainingRegistration extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'tr_training_registration';

    public $timestamps = true;

    public const STATUS_PENDING = 'P';
    public const STATUS_APPROVED = 'C';
    public const STATUS_REJECTED = 'R';
    public const STATUS_CANCELLED = 'X';
    public const STATUS_WAITLISTED = 'W';
    public const STATUS_OFFERED = 'O';

    protected $fillable = [
        'docid',
        'schedule_detail_id',
        'username',
        'cpny_id',
        'department_id',
        'status',
        'approved_at',
        'offered_at',
        'offer_expires_at',
        'attendance_code',
        'attended_at',
        'attended_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'offered_at' => 'datetime',
        'offer_expires_at' => 'datetime',
        'attended_at' => 'datetime',
    ];

    public function scheduleDetail()
    {
        return $this->belongsTo(TrTrainingScheduleDetail::class, 'schedule_detail_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }

    public function certificates()
    {
        return $this->hasMany(TrTrainingCertificate::class, 'registration_id', 'id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeWaitlisted($query)
    {
        return $query->where('status', self::STATUS_WAITLISTED);
    }

    public function scopeOffered($query)
    {
        return $query->where('status', self::STATUS_OFFERED);
    }
}
