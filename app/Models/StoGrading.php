<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoGrading extends Model
{
    protected $connection = 'pgsql3';
    protected $table = "hr_ms_sto_grading";
    
    protected $fillable = [
        'group_cpny_id',
        'grade_id',
        'grade_name',
        'grade_color_code',
        'status',
        'created_user',
        'created_at',
        'updated_user',
        'updated_at',
        'completed_user'
    ];

    /**
     * Legacy compatibility: ms_lnd_training_detail.job_level used to store a
     * numeric grade_id (e.g. "5") looked up here. Batches created after the
     * Level picker switched to hr_ms_sto_subgrading_joblevel.group_job_level
     * store that label text directly and need no lookup. Bulk-resolves a set
     * of job_level values at once — numeric ones through this table, anything
     * else (and any numeric value with no matching grade) passed through as-is.
     *
     * @return \Illuminate\Support\Collection<string, string> jobLevel => label
     */
    public static function labelsFor($jobLevels): \Illuminate\Support\Collection
    {
        $jobLevels = collect($jobLevels)->filter()->unique()->values();

        $numericIds = $jobLevels->filter(fn ($v) => ctype_digit((string) $v))->values();

        $names = $numericIds->isEmpty()
            ? collect()
            : static::whereIn('grade_id', $numericIds)->pluck('grade_name', 'grade_id');

        return $jobLevels->mapWithKeys(fn ($v) => [$v => $names[$v] ?? $v]);
    }
}

