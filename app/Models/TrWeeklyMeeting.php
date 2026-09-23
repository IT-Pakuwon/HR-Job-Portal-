<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrWeeklyMeeting extends Model
{
    protected $connection = 'pgsql7';
    protected $table = 'tr_weeklymeeting';

    protected $fillable = [
        'weeklymeeting_id',
        'weeklymeeting_date',
        'cpny_id',
        'department_id',
        'weeklymeeting_startdate',
        'weeklymeeting_enddate',
        'weeklymeeting_topic',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
        'completed_by',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'weeklymeeting_date' => 'date',
            'weeklymeeting_startdate' => 'datetime',
            'weeklymeeting_enddate' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function findings(): HasMany
    {
        return $this->hasMany(
            TrWeeklyMeetingFinding::class,
            'weeklymeeting_id',
            'weeklymeeting_id'
        );
    }

    public function participants(): HasMany
    {
        return $this->hasMany(
            TrWeeklyMeetingParticipant::class,
            'weeklymeeting_id',
            'weeklymeeting_id'
        );
    }

    public function minutes(): HasMany
    {
        return $this->hasMany(
            TrWeeklyMeetingMom::class,
            'weeklymeeting_id',
            'weeklymeeting_id'
        );
    }
}
