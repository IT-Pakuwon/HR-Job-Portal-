<?php

namespace App\Models\aisearch;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiSchemaTable extends Model
{
    use HasFactory;

    protected $connection = 'pgsql9';

    protected $table = 'ai_schema_tables';

    protected $fillable = [
        'source_id',
        'schema_name',
        'table_name',
        'object_type',
        'display_name',
        'module_name',
        'description',
        'is_active',
        'is_queryable',
        'estimated_row_count',
        'last_synced_at',
    ];

    protected $casts = [
        'source_id' => 'integer',
        'is_active' => 'boolean',
        'is_queryable' => 'boolean',
        'estimated_row_count' => 'integer',
        'last_synced_at' => 'datetime',
    ];
}
