<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $session;

    public function __construct()
    {
        $this->baseUrl = config('services.whatsapp.url');
        $this->apiKey = config('services.whatsapp.api_key');
        $this->session = config('services.whatsapp.session', 'default');
    }

    public function sendText(
        string $chatId,
        string $message
    ): array {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'X-Api-Key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post(
                    "{$this->baseUrl}/api/sendtext",
                    [
                        'chatId' => $chatId,
                        'id' => null,
                        'reply_to' => null,
                        'text' => $message,
                        'linkPreview' => true,
                        'linkPreviewHighQuality' => false,
                        'session' => $this->session,
                    ]
                );

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'response' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error(
                'Whatsapp send failed',
                [
                    'chatId' => $chatId,
                    'error' => $e->getMessage(),
                ]
            );

            return [
                'success' => false,
                'status' => 500,
                'message' => $e->getMessage(),
            ];
        }
    }
}
