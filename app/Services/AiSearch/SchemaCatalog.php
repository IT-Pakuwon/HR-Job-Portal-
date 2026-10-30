<?php

namespace App\Services\AiSearch;

use App\Models\aisearch\AiDataSource;
use App\Models\aisearch\AiPermission;
use App\Models\aisearch\AiSchemaColumn;
use App\Models\aisearch\AiSchemaTable;
use Illuminate\Support\Facades\Auth;

class SchemaCatalog
{
    public const CONNECTION = 'pgsql9';

    public function __construct(private CatalogAccessPolicy $policy) {}

    public function sourcesForUser(string $userKey): array
    {
        $sources = [];
        foreach (AiDataSource::where('is_active', true)->where('driver', 'pgsql')->orderBy('source_name')->get() as $source) {
            try {
                $catalog = $this->forUser($userKey, (int) $source->id);
                $sources[] = ['id' => $catalog['source_id'], 'name' => $source->source_name, 'table_count' => count($catalog['tables'])];
            } catch (AiSearchException $exception) {
                // Do not advertise sources with no authorized, queryable tables.
            }
        }

        return $sources;
    }

    public function forUser(string $userKey, ?int $sourceId = null): array
    {
        $user = Auth::user();
        if (! $user || (string) $user->getAuthIdentifier() !== $userKey) {
            throw new AiSearchException('Identitas pengguna tidak sesuai.', 403);
        }
        $source = AiDataSource::whereKey($sourceId ?? config('ai-search.source_id'))->where('is_active', true)->first();
        if (! $source || $source->driver !== 'pgsql') {
            throw new AiSearchException('Sumber tidak aktif atau bukan PostgreSQL.', 403);
        }
        $roles = method_exists($user, 'roleIds') ? $user->roleIds()->all() : [];
        $permissions = AiPermission::where('is_active', true)->where(function ($query) use ($userKey, $roles) {
            $query->where(function ($query) use ($userKey) {
                $query->where('principal_type', 'USER')->where('principal_key', $userKey);
            })->orWhere(function ($query) use ($roles) {
                $query->where('principal_type', 'ROLE')->whereIn('principal_key', $roles);
            });
        })->get();
        $tables = AiSchemaTable::where('source_id', $source->id)
            ->where('is_active', true)->where('is_queryable', true)->orderBy('schema_name')->orderBy('table_name')
            ->get()->filter(fn ($table) => $this->policy->allows($permissions, (int) $source->id, $table));
        $columns = AiSchemaColumn::whereIn('schema_table_id', $tables->pluck('id'))
            ->where('is_queryable', true)->where('is_sensitive', false)->orderBy('ordinal_position')->get()
            ->groupBy('schema_table_id');
        $catalogTables = [];
        foreach ($tables as $table) {
            if (in_array($table->table_name, config('ai-search.excluded_tables.'.$source->id, []), true)) {
                continue;
            }
            $catalogTables[$table->schema_name.'.'.$table->table_name] = [
                'schema' => $table->schema_name, 'table' => $table->table_name,
                'label' => $table->display_name ?: (config('ai-search.table_labels', [])[$table->schema_name.'.'.$table->table_name] ?? null),
                'columns' => ($columns->get($table->id) ?? collect())->pluck('column_name')->all(),
            ];
        }
        if ($catalogTables === []) {
            throw new AiSearchException('Tidak ada tabel aktif yang diizinkan pada sumber ini. Periksa ai_permissions dan katalog.', 403);
        }

        return [
            'source_id' => (int) $source->id, 'source_name' => $source->source_name,
            'connection_name' => $source->connection_name, 'tables' => $catalogTables,
        ];
    }
}
