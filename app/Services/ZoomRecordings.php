<?php

namespace App\Services;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ZoomRecordings
{
    public function __construct(private ZoomApi $zoom)
    {
    }

    public function get($meetingId, string $account = 'business'): array
    {
        $key = 'zoom:recordings:'.hash('sha256', $account.'|'.$meetingId);

        return Cache::remember($key, 120, function () use ($meetingId, $account) {
            try {
                return $this->zoom->getRecordings($meetingId, $account);
            } catch (ClientException $e) {
                if ($e->getResponse()->getStatusCode() === 404) {
                    return [];
                }
                throw $e;
            }
        });
    }

    public function playableFiles(array $recording): array
    {
        return array_values(array_filter($recording['recording_files'] ?? [], static fn ($file) =>
            ($file['status'] ?? '') === 'completed'
            && in_array(strtoupper((string) ($file['file_type'] ?? '')), ['MP4', 'M4A'], true)
            && preg_match('~^https://~i', (string) ($file['play_url'] ?? ''))
        ));
    }

    public function hasPlayable($meetingId, ?string $account, bool $ended): bool
    {
        if (!$meetingId || !$ended) {
            return false;
        }

        try {
            return $this->playableFiles($this->get($meetingId, $account ?: 'business')) !== [];
        } catch (\Throwable $e) {
            Log::warning('zoom recording availability failed', ['meeting_id' => $meetingId, 'message' => $e->getMessage()]);
            return false;
        }
    }
}
