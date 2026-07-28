<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrWeeklyMeetingParticipant extends Model
{
    protected $connection = 'pgsql7';
    protected $table = 'tr_weeklymeeting_participant';

    protected $fillable = [
        'weeklymeeting_id',
        'cpny_id',
        'order_participant',
        'user_participant',
        'name_participant',
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
            'order_participant' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function weeklyMeeting(): BelongsTo
    {
        return $this->belongsTo(
            TrWeeklyMeeting::class,
            'weeklymeeting_id',
            'weeklymeeting_id'
        );
    }
}
