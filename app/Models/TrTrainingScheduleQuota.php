<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrTrainingScheduleQuota extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'tr_training_schedule_quota';

    public $timestamps = true;

    protected $fillable = [
        'schedule_detail_id',
        'cpny_id',
        'quota_pax',
        'created_by',
        'updated_by',
    ];

    public function detail()
    {
        return $this->belongsTo(TrTrainingScheduleDetail::class, 'schedule_detail_id', 'id');
    }
}
