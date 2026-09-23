<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsLndTrainingFeedback extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'ms_lnd_training_feedback';

    public $timestamps = true;

    public const TYPE_SINGLE_CHOICE = 'Single Choice';
    public const TYPE_RATING = 'Rating';
    public const TYPE_LONG_TEXT = 'Long Text';

    protected $fillable = [
        'question_order',
        'question_text',
        'question_type',
        'question_value',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'question_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'A')->orderBy('question_order');
    }

    /**
     * "Ya,Tidak" / "1,2,3,4,5" -> ['Ya', 'Tidak'] / ['1','2','3','4','5'].
     * Empty for Long Text, which has no question_value.
     */
    public function getOptionsAttribute(): array
    {
        return collect(explode(',', (string) $this->question_value))
            ->map(fn ($v) => trim($v))
            ->filter()
            ->values()
            ->all();
    }
}
