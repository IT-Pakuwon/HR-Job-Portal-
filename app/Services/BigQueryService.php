<?php

namespace App\Services;

use Google\Cloud\BigQuery\BigQueryClient;

class BigQueryService
{
    private BigQueryClient $client;

    public function __construct()
    {
        $keyFile = base_path(env('BQ_KEY_FILE', 'storage/app/gcs/ifca-pkwjakarta-44cec31f1c1a.json'));
        $projectId = env('GCS_PROJECT_ID', 'ifca-pkwjakarta');

        $this->client = new BigQueryClient([
            'projectId'  => $projectId,
            'keyFilePath' => $keyFile,
        ]);
    }

    /**
     * Run a SQL query and return rows as an array of associative arrays.
     */
    public function query(string $sql, array $params = []): array
    {
        $options = ['query' => $sql, 'useLegacySql' => false];

        if (!empty($params)) {
            $options['queryParameters'] = $params;
        }

        $queryJobConfig = $this->client->query($sql)->useLegacySql(false);

        if (!empty($params)) {
            $queryJobConfig->parameters($params);
        }

        $results = $this->client->runQuery($queryJobConfig);

        $rows = [];
        foreach ($results as $row) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * List all datasets in the project (useful for verifying connectivity).
     */
    public function listDatasets(): array
    {
        $datasets = [];
        foreach ($this->client->datasets() as $dataset) {
            $datasets[] = $dataset->id();
        }
        return $datasets;
    }

    /**
     * List all tables grouped by dataset.
     * Returns: [ 'dataset_id' => ['table1', 'table2', ...], ... ]
     */
    public function listTables(): array
    {
        $result = [];
        foreach ($this->client->datasets() as $dataset) {
            $datasetId = $dataset->id();
            $result[$datasetId] = [];
            foreach ($dataset->tables() as $table) {
                $result[$datasetId][] = $table->id();
            }
        }
        return $result;
    }

    /**
     * Get column schema for a specific table.
     * Returns: [ ['name' => 'col', 'type' => 'STRING', 'mode' => 'NULLABLE'], ... ]
     */
    public function tableSchema(string $datasetId, string $tableId): array
    {
        $table  = $this->client->dataset($datasetId)->table($tableId);
        $fields = $table->info()['schema']['fields'] ?? [];

        return array_map(fn($f) => [
            'name' => $f['name'],
            'type' => $f['type'],
            'mode' => $f['mode'] ?? 'NULLABLE',
        ], $fields);
    }

    public function getClient(): BigQueryClient
    {
        return $this->client;
    }
}
