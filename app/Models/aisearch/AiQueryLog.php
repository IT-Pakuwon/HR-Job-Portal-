<?php

namespace App\Models\aisearch;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiQueryLog extends Model
{
    use HasFactory;

    protected $connection = 'pgsql9';

    protected $table = 'ai_query_logs';

    public $timestamps = false;

    protected $fillable = [
        'conversation_id',
        'message_id',
        'user_key',
        'source_id',
        'query_text',
        'query_hash',
        'query_parameters',
        'status',
        'rows_returned',
        'duration_ms',
        'error_code',
        'error_message',
        'executed_at',
    ];

    protected $casts = [
        'conversation_id' => 'integer',
        'message_id' => 'integer',
        'source_id' => 'integer',
        'query_parameters' => 'array',
        'rows_returned' => 'integer',
        'duration_ms' => 'integer',
        'executed_at' => 'datetime',
    ];
}
