<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsLndTrainingQuota extends Model
{
    use SoftDeletes;

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
        'deleted_by',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function detail()
    {
        return $this->belongsTo(MsLndTrainingSchedule::class, 'schedule_id', 'schedule_id');
    }
}
