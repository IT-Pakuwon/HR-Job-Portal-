<?php

namespace App\Services\AiSearch;

class QueryExecutor
{
    public function __construct(
        private DatabaseManager $databases,
        private QueryValidator $validator,
        private SchemaCatalog $catalog
    ) {}

    public function execute(array $validatedPlan, string $userKey): array
    {
        // Re-read column permissions after the provider call, not a fabricated catalog.
        $catalog = $this->catalog->forUser($userKey, $validatedPlan['source_id']);
        if ($catalog['source_id'] !== $validatedPlan['source_id']) {
            throw new AiSearchException('Sumber pencarian tidak diizinkan.', 403);
        }
        $input = $validatedPlan;
        unset($input['source_id']);
        $plan = $this->validator->validate($input, $catalog);
        $connection = $this->databases->forSource($plan['source_id'], $userKey);

        return $connection->transaction(function () use ($connection, $plan) {
            $connection->statement('SET TRANSACTION READ ONLY');
            $connection->statement("SET LOCAL statement_timeout = '5s'");
            $qualified = $plan['schema'].'.'.$plan['table'];
            $query = $connection->table($qualified)->useWritePdo();
            $grammar = $connection->getQueryGrammar();
            foreach ($plan['filters'] as $filter) {
                // Identifiers are catalog-approved and quoted; values are bound.
                $column = $grammar->wrap($filter['column']);
                $text = 'LOWER(TRIM(CAST('.$column.' AS TEXT)))';
                if ($filter['operator'] === 'contains') {
                    $query->whereRaw('POSITION(LOWER(?) IN '.$text.') > 0', [$filter['value']]);
                } else {
                    $query->whereRaw($text.' = LOWER(?)', [$filter['value']]);
                }
            }
            $scope = 'Sumber ID '.$plan['source_id'].', tabel '.$qualified.'. Semua periode dan status kecuali yang dibatasi filter. Variasi teks tetap terpisah; data uji juga dihitung.';
            if ($plan['operation'] === 'select') {
                if (isset($plan['order_by'])) {
                    $query->orderBy($plan['order_by'], $plan['direction']);
                } else {
                    foreach ($plan['columns'] as $column) {
                        $query->orderByRaw('CAST('.$grammar->wrap($column).' AS TEXT) ASC');
                    }
                }
                $rows = $query->select($plan['columns'])->limit($plan['limit'])->get();

                return [
                    'operation' => 'select', 'columns' => $plan['columns'],
                    'rows' => $rows->map(fn ($row) => array_map(fn ($column) => $row->{$column}, $plan['columns']))->all(),
                    'message' => 'Ditampilkan '.$rows->count().' baris dari '.$qualified.'.',
                    'scope' => $scope.' Maksimal '.$plan['limit'].' baris.',
                ];
            }
            if ($plan['operation'] === 'count') {
                $count = (int) $query->count();
                $title = collect($plan['filters'])->firstWhere('column', 'job_title')['value'] ?? null;
                $unit = $plan['table'] === 'hr_trx_prf' ? 'dokumen PRF' : 'baris';
                $message = 'Ditemukan '.$count.' '.$unit.($title !== null ? ' untuk jabatan '.$title : '').'.';

                return [
                    'operation' => 'count', 'jumlah_prf' => $count, 'job_title' => $title,
                    'message' => $message, 'scope' => $scope,
                    'columns' => [$plan['table'] === 'hr_trx_prf' ? 'Jumlah PRF' : 'Jumlah baris'], 'rows' => [[$count]],
                ];
            }

            $column = $grammar->wrap($plan['column']);
            // Group by raw trimmed value so case variants are not silently merged.
            $expression = 'NULLIF(TRIM(CAST('.$column." AS TEXT)), '')";
            $rows = $query->selectRaw($expression.' AS nilai, COUNT(*) AS jumlah_prf')
                ->groupByRaw($expression)->orderByDesc('jumlah_prf')->orderBy('nilai')
                ->limit($plan['limit'])->get();
            $top = $rows->first();
            $label = $plan['table'] === 'hr_trx_prf' && $plan['column'] === 'reason_vacancy' ? 'Alasan PRF' : $plan['column'];
            $unit = $plan['table'] === 'hr_trx_prf' ? 'dokumen PRF' : 'baris';
            $message = $top
                ? $label.' terbanyak: '.($top->nilai ?? '(Tidak diisi)').' ('.$top->jumlah_prf.' '.$unit.').'
                : 'Tidak ada data yang sesuai.';

            return [
                'operation' => 'group_count', 'group_column' => $plan['column'],
                'message' => $message,
                'scope' => $scope.' Menampilkan maksimal '.$plan['limit'].' kelompok; jumlah seri dapat muncul.',
                'columns' => [$label, $plan['table'] === 'hr_trx_prf' ? 'Jumlah PRF' : 'Jumlah baris'],
                'rows' => $rows->map(fn ($row) => [$row->nilai ?? '(Tidak diisi)', (int) $row->jumlah_prf])->all(),
            ];
        });
    }
}
