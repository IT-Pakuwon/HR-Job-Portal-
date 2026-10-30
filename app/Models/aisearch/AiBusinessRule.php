<?php

namespace App\Models\aisearch;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiBusinessRule extends Model
{
    use HasFactory;

    protected $connection = 'pgsql9';

    protected $table = 'ai_business_rules';

    protected $fillable = [
        'rule_code',
        'rule_name',
        'source_id',
        'module_name',
        'description',
        'rule_definition',
        'priority',
        'is_active',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'source_id' => 'integer',
        'rule_definition' => 'object',
        'priority' => 'integer',
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
    ];
}
