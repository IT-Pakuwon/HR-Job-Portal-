<?php

namespace App\Models\aisearch;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiSchemaRelation extends Model
{
    use HasFactory;

    protected $connection = 'pgsql9';

    protected $table = 'ai_schema_relations';

    protected $fillable = [
        'relation_name',
        'from_table_id',
        'from_column_id',
        'to_table_id',
        'to_column_id',
        'relation_type',
        'join_type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'from_table_id' => 'integer',
        'from_column_id' => 'integer',
        'to_table_id' => 'integer',
        'to_column_id' => 'integer',
        'is_active' => 'boolean',
    ];
}
