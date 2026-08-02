<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsLndTrainingDetail extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'ms_lnd_training_detail';

    protected $fillable = [
        'training_detail_id',
        'training_id',
        'training_detail_name',
        'training_poster',
        'job_level',
        'is_ext_speaker',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_ext_speaker' => 'boolean',
    ];

    public function details()
    {
        return $this->hasMany(MsLndTrainingSchedule::class, 'training_detail_id', 'training_detail_id')
            ->orderBy('schedule_date');
    }

    public function training()
    {
        return $this->belongsTo(MsTrainingEvent::class, 'training_id', 'training_id');
    }
}
