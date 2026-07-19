<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrTrainingScheduleDetail extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'tr_training_schedule_detail';

    public $timestamps = true;

    protected $fillable = [
        'docid',
        'linenbr',
        'schedule_date',
        'start_time',
        'end_time',
        'mode',
        'location',
        'platform',
        'meeting_link',
        'registration_deadline',
        'status',
        'attendance_locked_at',
        'attendance_locked_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'attendance_locked_at' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(TrTrainingSchedule::class, 'docid', 'docid');
    }

    public function quota()
    {
        return $this->hasMany(TrTrainingScheduleQuota::class, 'schedule_detail_id', 'id');
    }

    public function registrations()
    {
        return $this->hasMany(TrTrainingRegistration::class, 'schedule_detail_id', 'id');
    }
}
