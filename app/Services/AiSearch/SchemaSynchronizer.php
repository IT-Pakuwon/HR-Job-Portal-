<?php

namespace App\Services\AiSearch;

use App\Models\aisearch\AiDataSource;
use App\Models\aisearch\AiSchemaColumn;
use App\Models\aisearch\AiSchemaRelation;
use App\Models\aisearch\AiSchemaTable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SchemaSynchronizer
{
    public function sync(int $sourceId): array
    {
        $source = AiDataSource::findOrFail($sourceId);
        $config = config('database.connections.'.$source->connection_name);
        if (!is_array($config) || !in_array($source->driver, ['pgsql', 'mysql'], true)
            || ($config['driver'] ?? null) !== $source->driver || !empty($config['prefix'])) {
            throw new InvalidArgumentException('Konfigurasi koneksi sumber tidak sesuai.');
        }

        $connection = DB::connection($source->connection_name);
        $schema = $source->default_schema ?: ($source->driver === 'pgsql' ? 'public' : $connection->getDatabaseName());
        if ($source->driver === 'mysql' && $schema !== $connection->getDatabaseName()) {
            throw new InvalidArgumentException('default_schema MySQL harus sama dengan nama database koneksi.');
        }

        // Read only system metadata; do not sample or copy business records.
        if ($source->driver === 'pgsql') {
            $objects = $connection->select(
                "SELECT c.relname AS name, CASE c.relkind WHEN 'v' THEN 'VIEW' WHEN 'm' THEN 'MATERIALIZED_VIEW' ELSE 'TABLE' END AS object_type
                 FROM pg_catalog.pg_class c JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
                 WHERE n.nspname = ? AND c.relkind IN ('r', 'p', 'v', 'm') ORDER BY c.relname", [$schema]
            );
        } else {
            $objects = $connection->select(
                "SELECT TABLE_NAME AS name, CASE TABLE_TYPE WHEN 'VIEW' THEN 'VIEW' ELSE 'TABLE' END AS object_type
                 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME", [$schema]
            );
        }

        $builder = $connection->getSchemaBuilder();
        $metadata = [];
        foreach ($objects as $object) {
            if (in_array($object->name, config('ai-search.excluded_tables.'.$sourceId, []), true)) {
                continue;
            }
            $qualified = $schema.'.'.$object->name;
            $columns = $builder->getColumns($qualified);
            $primary = [];
            foreach ($builder->getIndexes($qualified) as $index) {
                if ($index['primary']) {
                    $primary = array_merge($primary, $index['columns']);
                }
            }
            $metadata[] = [
                'name' => $object->name,
                'type' => $object->object_type,
                'columns' => $columns,
                'primary' => $primary,
                'foreign_keys' => $object->object_type === 'TABLE' ? $builder->getForeignKeys($qualified) : [],
            ];
        }

        return DB::connection('pgsql9')->transaction(function () use ($sourceId, $source, $schema, $metadata) {
            $locked = AiDataSource::whereKey($sourceId)->lockForUpdate()->firstOrFail();
            if ($locked->connection_name !== $source->connection_name || $locked->default_schema !== $source->default_schema
                || $locked->driver !== $source->driver) {
                throw new InvalidArgumentException('Konfigurasi sumber berubah selama sinkronisasi.');
            }
            $counts = ['tables' => 0, 'columns' => 0, 'relations' => 0, 'skipped_relations' => 0];
            $tables = [];
            $columns = [];
            foreach ($metadata as $object) {
                $table = AiSchemaTable::firstOrNew([
                    'source_id' => $sourceId, 'schema_name' => $schema, 'table_name' => $object['name'],
                ]);
                if (!$table->exists) {
                    $table->is_active = false;
                    $table->is_queryable = false;
                } elseif ($table->object_type !== $object['type']) {
                    $table->is_active = false;
                    $table->is_queryable = false;
                }
                $table->object_type = $object['type'];
                $table->last_synced_at = now();
                $table->save();
                $tables[$object['name']] = $table;
                $counts['tables']++;

                // Move existing positions out of the way to handle column reordering
                // without violating UNIQUE(schema_table_id, ordinal_position).
                $existing = AiSchemaColumn::where('schema_table_id', $table->id)->get();
                $offset = (int) $existing->max('ordinal_position') + count($object['columns']) + 1;
                foreach ($existing as $column) {
                    $column->ordinal_position += $offset;
                    $column->save();
                }
                $seen = [];
                foreach ($object['columns'] as $position => $definition) {
                    $column = AiSchemaColumn::firstOrNew([
                        'schema_table_id' => $table->id, 'column_name' => $definition['name'],
                    ]);
                    if (!$column->exists) {
                        $column->is_sensitive = false;
                        $column->is_queryable = false;
                        $column->description = $definition['comment'] ?? null;
                    } elseif ($column->data_type !== $definition['type']) {
                        $column->is_queryable = false;
                    }
                    $column->data_type = $definition['type'];
                    $column->ordinal_position = $position + 1;
                    $column->is_nullable = $definition['nullable'];
                    $column->is_primary_key = in_array($definition['name'], $object['primary'], true);
                    $column->save();
                    $columns[$object['name']][$definition['name']] = $column;
                    $seen[] = $column->id;
                    $counts['columns']++;
                }
                AiSchemaColumn::where('schema_table_id', $table->id)->whereNotIn('id', $seen)->update(['is_queryable' => false]);
            }

            // Keep removed objects for audit/manual metadata, but revoke queryability.
            AiSchemaTable::where('source_id', $sourceId)->where('schema_name', $schema)
                ->whereNotIn('id', array_map(fn ($table) => $table->id, $tables))
                ->update(['is_active' => false, 'is_queryable' => false]);

            foreach ($metadata as $object) {
                foreach ($object['foreign_keys'] as $foreign) {
                    // The catalog stores one column per edge. A composite FK cannot
                    // be represented safely as independent single-column joins.
                    if (count($foreign['columns']) !== 1 || count($foreign['foreign_columns']) !== 1
                        || $foreign['foreign_schema'] !== $schema
                        || !isset($tables[$foreign['foreign_table']])) {
                        $counts['skipped_relations']++;
                        continue;
                    }
                    $from = $columns[$object['name']][$foreign['columns'][0]] ?? null;
                    $to = $columns[$foreign['foreign_table']][$foreign['foreign_columns'][0]] ?? null;
                    if (!$from || !$to || $from->id === $to->id) {
                        $counts['skipped_relations']++;
                        continue;
                    }
                    AiSchemaRelation::firstOrCreate([
                        'from_table_id' => $from->schema_table_id, 'from_column_id' => $from->id,
                        'to_table_id' => $to->schema_table_id, 'to_column_id' => $to->id,
                    ], [
                        'relation_name' => $foreign['name'], 'relation_type' => 'MANY_TO_ONE',
                        'join_type' => 'INNER', 'is_active' => false,
                    ]);
                    $counts['relations']++;
                }
            }
            $locked->last_synced_at = now();
            $locked->save();
            return $counts;
        });
    }
}
