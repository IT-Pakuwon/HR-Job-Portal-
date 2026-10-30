<?php

namespace App\Services\AiSearch;

use App\Models\aisearch\AiQueryLog;
use Throwable;

class AiOrchestrator
{
    public function __construct(
        private SchemaCatalog $catalog,
        private OpenRouterClient $provider,
        private QueryValidator $validator,
        private QueryExecutor $executor
    ) {}

    public function search(string $question, string $userKey, ?int $sourceId = null): array
    {
        $start = microtime(true);
        $catalog = null;
        $audit = null;
        try {
            // Authorize before sharing the question with the provider.
            $catalog = $this->catalog->forUser($userKey, $sourceId);
            $plan = $this->validator->validate($this->provider->plan($question, $catalog), $catalog);
            // Ensure audit storage is available before executing a query.
            $audit = AiQueryLog::create([
                'user_key' => $userKey, 'source_id' => $catalog['source_id'],
                'status' => 'REJECTED', 'error_code' => 'NOT_COMPLETED', 'executed_at' => now(),
            ]);
            $result = $this->executor->execute($plan, $userKey);
            $audit->update([
                'status' => 'SUCCESS', 'rows_returned' => count($result['rows']),
                'duration_ms' => (int) ((microtime(true) - $start) * 1000), 'error_code' => null,
            ]);

            return [
                'status' => 'success',
                'message' => $result['message'],
                'data' => $result, 'http_status' => 200,
            ];
        } catch (Throwable $exception) {
            $expected = $exception instanceof AiSearchException;
            $values = [
                'user_key' => $userKey, 'source_id' => $catalog['source_id'] ?? null,
                'status' => $expected && $exception->httpStatus < 500 ? 'REJECTED' : 'FAILED',
                'error_code' => $expected ? 'SEARCH_'.$exception->httpStatus : 'SEARCH_FAILED',
                'duration_ms' => (int) ((microtime(true) - $start) * 1000), 'executed_at' => now(),
            ];
            try {
                $audit ? $audit->update($values) : AiQueryLog::create($values);
            } catch (Throwable $auditException) {
                // Return failure even when audit storage is unavailable.
            }

            return [
                'status' => 'error',
                'message' => $expected ? $exception->getMessage() : 'Pencarian gagal. Periksa koneksi sumber dan katalog pgsql9.',
                'data' => [], 'http_status' => $expected ? $exception->httpStatus : 503,
            ];
        }
    }
}
