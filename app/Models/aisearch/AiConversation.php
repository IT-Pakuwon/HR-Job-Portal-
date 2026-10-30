<?php

namespace App\Models\aisearch;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiConversation extends Model
{
    use HasFactory;

    protected $connection = 'pgsql9';

    protected $table = 'ai_conversations';

    protected $fillable = [
        'user_key',
        'title',
        'status',
        'context',
        'last_message_at',
    ];

    protected $casts = [
        'context' => 'object',
        'last_message_at' => 'datetime',
    ];
}
