<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsTrainingEvent extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'ms_lnd_training';

    protected $fillable = [
        'training_id',
        'training_name',
        'category_id',
        'is_mandatory',
        'training_description',
        'training_type',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
    ];
}
