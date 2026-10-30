<?php

namespace App\Models\aisearch;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiDataSource extends Model
{
    use HasFactory;

    protected $connection = 'pgsql9';

    protected $table = 'ai_data_sources';

    protected $fillable = [
        'source_code',
        'source_name',
        'driver',
        'connection_name',
        'default_schema',
        'description',
        'is_active',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];
}
