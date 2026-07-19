<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsTrainingEvent extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'ms_training_event';

    protected $fillable = [
        'training_id',
        'training_name',
        'category_id',
        'is_mandatory',
        'description',
        'training_type',
        'speaker_external',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
    ];
}
