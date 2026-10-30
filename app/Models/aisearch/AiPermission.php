<?php

namespace App\Models\aisearch;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiPermission extends Model
{
    use HasFactory;

    protected $connection = 'pgsql9';

    protected $table = 'ai_permissions';

    protected $fillable = [
        'principal_type',
        'principal_key',
        'source_id',
        'schema_table_id',
        'module_name',
        'company_id',
        'department_id',
        'permission_type',
        'is_active',
    ];

    protected $casts = [
        'source_id' => 'integer',
        'schema_table_id' => 'integer',
        'is_active' => 'boolean',
    ];
}
