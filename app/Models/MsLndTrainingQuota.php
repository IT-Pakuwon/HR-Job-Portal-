<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsLndTrainingQuota extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'ms_lnd_training_quota';

    public $timestamps = true;

    protected $fillable = [
        'schedule_id',
        'training_id',
        'training_detail_id',
        'cpny_id',
        'quota_pax',
        'status',
        'created_by',
        'updated_by',
    ];

    public function detail()
    {
        return $this->belongsTo(MsLndTrainingSchedule::class, 'schedule_id', 'schedule_id');
    }
}
