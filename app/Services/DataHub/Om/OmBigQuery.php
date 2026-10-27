<?php

namespace App\Services\DataHub\Om;

use App\Services\BigQueryService;
use Illuminate\Support\Facades\Cache;

/** Runs OM Dashboard BigQuery SQL: parameter binding, retry on transient errors, result cache. */
class OmBigQuery
{
    public function run(string $sql, array $params = []): array
    {
        $cacheKey = 'om_bq_'.sha1($sql.'|'.json_encode($params));

        return Cache::remember($cacheKey, 86400, function () use ($sql, $params) {
            $attempts = 3;
            $lastException = null;

            for ($i = 1; $i <= $attempts; $i++) {
                try {
                    $bigQuery = app(BigQueryService::class)->getClient();

                    $query = $bigQuery->query($sql);

                    if (! empty($params)) {
                        $query->parameters($params);
                    }

                    $startTime = microtime(true);

                    $rows = iterator_to_array($bigQuery->runQuery($query));

                    $duration = round(microtime(true) - $startTime, 2);

                    \Log::info('OM BigQuery duration', [
                        'duration_seconds' => $duration,
                        'params' => $params,
                        'sql_preview' => substr(preg_replace('/\s+/', ' ', trim($sql)), 0, 180),
                    ]);

                    return array_map(function ($row) {
                        return $this->normalize($row);
                    }, $rows);

                } catch (\Throwable $e) {
                    $lastException = $e;

                    $message = $e->getMessage();

                    $canRetry =
                        str_contains($message, 'cURL error 56') ||
                        str_contains($message, 'SSL_read') ||
                        str_contains($message, 'unexpected eof') ||
                        str_contains($message, 'Maximum execution time') ||
                        str_contains($message, 'timed out') ||
                        str_contains($message, 'ServiceException');

                    if (! $canRetry || $i === $attempts) {
                        throw $e;
                    }

                    sleep(1);
                }
            }

            throw $lastException;
        });
    }

    private function normalize($value)
    {
        if (is_array($value)) {
            $result = [];

            foreach ($value as $key => $item) {
                $result[$key] = $this->normalize($item);
            }

            return $result;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_object($value)) {
            if (method_exists($value, 'get')) {
                return $this->normalize($value->get());
            }

            if (method_exists($value, '__toString')) {
                return (string) $value;
            }
        }

        return $value;
    }

    public function cached(string $key, callable $callback, int $seconds = 3600): mixed
    {
        return Cache::remember($key, $seconds, $callback);
    }
}
