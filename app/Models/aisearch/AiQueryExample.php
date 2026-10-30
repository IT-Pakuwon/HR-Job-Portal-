<?php

namespace App\Models\aisearch;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiQueryExample extends Model
{
    use HasFactory;

    protected $connection = 'pgsql9';

    protected $table = 'ai_query_examples';

    protected $fillable = [
        'source_id',
        'module_name',
        'question',
        'query_plan',
        'sql_example',
        'explanation',
        'is_verified',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'source_id' => 'integer',
        'query_plan' => 'object',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];
}
