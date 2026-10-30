<?php

namespace App\Services\AiSearch;

class QueryValidator
{
    public function validate(array $plan, array $catalog): array
    {
        $operation = $plan['operation'] ?? null;
        if (! in_array($operation, ['count', 'group_count', 'select'], true)) {
            throw new AiSearchException('Pencarian mendukung jumlah, frekuensi, dan daftar data satu tabel. Join, rentang tanggal, dan penjumlahan nominal belum didukung.');
        }
        $allowed = ['operation', 'schema', 'table', 'filters'];
        $allowed = array_merge($allowed, match ($operation) {
            'count' => ['job_title'],
            'group_count' => ['column', 'limit'],
            'select' => ['columns', 'limit', 'order_by', 'direction'],
        });
        if (array_diff(array_keys($plan), $allowed)) {
            throw new AiSearchException('Rencana pencarian berisi parameter yang tidak didukung.');
        }
        $tableName = $plan['table'] ?? null;
        $this->identifier($tableName);
        $tables = $catalog['tables'] ?? [];
        // Legacy single-table catalogs are accepted for compatibility with existing callers.
        if (isset($catalog['table'], $catalog['schema'])) {
            $tables[$catalog['schema'].'.'.$catalog['table']] = $catalog;
        }
        $schema = $plan['schema'] ?? null;
        if ($schema === null) {
            $matches = array_filter($tables, fn ($entry) => $entry['table'] === $tableName);
            if (count($matches) !== 1) {
                throw new AiSearchException('Tabel tidak diizinkan atau nama tabel ambigu. Tentukan schema.', 403);
            }
            $schema = array_values($matches)[0]['schema'];
        }
        $this->identifier($schema);
        $table = $tables[$schema.'.'.$tableName] ?? null;
        if (! $table) {
            throw new AiSearchException('Tabel tidak diizinkan pada sumber yang dipilih.', 403);
        }
        $filters = $plan['filters'] ?? [];
        if (! is_array($filters) || ! array_is_list($filters) || count($filters) > 5) {
            throw new AiSearchException('Maksimal lima filter diperbolehkan.');
        }
        if (array_key_exists('job_title', $plan)) {
            if ($filters !== [] || $tableName !== 'hr_trx_prf') {
                throw new AiSearchException('Filter job_title tidak sesuai dengan tabel pencarian.');
            }
            $filters[] = ['column' => 'job_title', 'value' => $plan['job_title']];
        }
        foreach ($filters as &$filter) {
            if (! is_array($filter) || array_diff(array_keys($filter), ['column', 'value', 'operator'])
                || ! isset($filter['column'], $filter['value']) || ! is_string($filter['value'])
                || trim($filter['value']) === '' || mb_strlen($filter['value']) > 500) {
                throw new AiSearchException('Filter harus berisi kolom dan nilai teks yang valid.');
            }
            $this->column($filter['column'], $table);
            $operator = $filter['operator'] ?? 'equals';
            if (! in_array($operator, ['equals', 'contains'], true)) {
                throw new AiSearchException('Operator filter harus equals atau contains.');
            }
            $filter = ['column' => $filter['column'], 'value' => trim($filter['value']), 'operator' => $operator];
        }
        unset($filter);
        $validated = [
            'operation' => $operation, 'schema' => $schema, 'table' => $tableName,
            'source_id' => $catalog['source_id'], 'filters' => $filters,
        ];
        if ($operation !== 'count') {
            $limit = $plan['limit'] ?? 10;
            if (! is_int($limit) || $limit < 1 || $limit > 50) {
                throw new AiSearchException('Batas hasil harus 1 sampai 50.');
            }
            $validated['limit'] = $limit;
        }
        if ($operation === 'group_count') {
            $this->column($plan['column'] ?? null, $table);
            $validated['column'] = $plan['column'];
        }
        if ($operation === 'select') {
            $selected = $plan['columns'] ?? null;
            if (! is_array($selected) || ! array_is_list($selected) || count($selected) < 1 || count($selected) > 10
                || count(array_unique($selected, SORT_REGULAR)) !== count($selected)) {
                throw new AiSearchException('Pilih 1 sampai 10 kolom untuk ditampilkan.');
            }
            foreach ($selected as $column) {
                $this->column($column, $table);
            }
            $validated['columns'] = $selected;
            if (isset($plan['order_by'])) {
                $this->column($plan['order_by'], $table);
                $direction = $plan['direction'] ?? 'asc';
                if (! in_array($direction, ['asc', 'desc'], true)) {
                    throw new AiSearchException('Urutan harus asc atau desc.');
                }
                $validated['order_by'] = $plan['order_by'];
                $validated['direction'] = $direction;
            } elseif (isset($plan['direction'])) {
                throw new AiSearchException('Kolom pengurutan belum ditentukan.');
            }
        }

        return $validated;
    }

    private function identifier(mixed $name): void
    {
        if (! is_string($name) || ! preg_match('/^[a-z_][a-z0-9_]*$/i', $name)) {
            throw new AiSearchException('Nama schema, tabel, atau kolom tidak valid.');
        }
    }

    private function column(mixed $column, array $table): void
    {
        $this->identifier($column);
        if (! in_array($column, $table['columns'] ?? [], true)) {
            throw new AiSearchException('Kolom '.$column.' tidak diizinkan. Periksa katalog dan penanda sensitif.', 403);
        }
    }
}
