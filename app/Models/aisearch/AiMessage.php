<?php

namespace App\Models\aisearch;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiMessage extends Model
{
    use HasFactory;

    protected $connection = 'pgsql9';

    protected $table = 'ai_messages';

    public const UPDATED_AT = null;

    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'content_format',
        'metadata',
    ];

    protected $casts = [
        'conversation_id' => 'integer',
        'metadata' => 'object',
    ];
}
