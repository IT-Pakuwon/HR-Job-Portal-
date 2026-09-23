<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrLndTrainingFeedbackAnswer extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'tr_lnd_training_feedback_answer';

    public $timestamps = true;

    protected $fillable = [
        'training_regist_id',
        'training_id',
        'training_detail_id',
        'schedule_id',
        'schedule_date',
        'cpny_id',
        'department_id',
        'user_registration',
        'question_order',
        'question_text',
        'question_type',
        'question_value',
        'answer_text',
        'answer_number',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'question_order' => 'integer',
        'answer_number' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function registration()
    {
        return $this->belongsTo(TrLndTrainingRegistration::class, 'training_regist_id', 'training_regist_id');
    }
}
