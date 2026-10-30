<?php

namespace App\Models\aisearch;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiSchemaColumn extends Model
{
    use HasFactory;

    protected $connection = 'pgsql9';

    protected $table = 'ai_schema_columns';

    protected $fillable = [
        'schema_table_id',
        'column_name',
        'data_type',
        'ordinal_position',
        'description',
        'is_primary_key',
        'is_nullable',
        'is_sensitive',
        'is_queryable',
        'sample_value',
    ];

    protected $casts = [
        'schema_table_id' => 'integer',
        'ordinal_position' => 'integer',
        'is_primary_key' => 'boolean',
        'is_nullable' => 'boolean',
        'is_sensitive' => 'boolean',
        'is_queryable' => 'boolean',
    ];
}
