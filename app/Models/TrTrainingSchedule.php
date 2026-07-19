<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrTrainingSchedule extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'tr_training_schedule';

    protected $fillable = [
        'docid',
        'training_id',
        'grade_id',
        'poster',
        'speaker_username',
        'created_by',
        'updated_by',
    ];

    public function details()
    {
        return $this->hasMany(TrTrainingScheduleDetail::class, 'docid', 'docid')
            ->orderBy('linenbr');
    }

    public function training()
    {
        return $this->belongsTo(MsTrainingEvent::class, 'training_id', 'training_id');
    }
}
