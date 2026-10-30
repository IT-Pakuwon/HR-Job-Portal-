<?php

namespace App\Services\AiSearch;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

class DatabaseManager
{
    public function __construct(private SchemaCatalog $catalog) {}

    public function forSource(int $sourceId, string $userKey): Connection
    {
        $catalog = $this->catalog->forUser($userKey, $sourceId);
        $name = $catalog['connection_name'];
        $config = config('database.connections.'.$name);
        if (! is_array($config) || ($config['driver'] ?? null) !== 'pgsql' || ! empty($config['prefix'])) {
            throw new AiSearchException('Konfigurasi koneksi sumber tidak sesuai.', 503);
        }

        return DB::connection($name);
    }
}
