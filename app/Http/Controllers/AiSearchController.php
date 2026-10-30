<?php

namespace App\Http\Controllers;

use App\Services\AiSearch\AiOrchestrator;
use App\Services\AiSearch\OpenRouterClient;
use App\Services\AiSearch\SchemaCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiSearchController extends Controller
{
    public function index(Request $request, SchemaCatalog $catalog): View
    {
        return view('ai-search.index', [
            'aiConfigured' => app(OpenRouterClient::class)->configured(),
            'sources' => $catalog->sourcesForUser((string) $request->user()->getAuthIdentifier()),
        ]);
    }

    public function search(Request $request, AiOrchestrator $orchestrator): JsonResponse
    {
        $input = $request->validate([
            'question' => ['required', 'string', 'max:2000'],
            'source_id' => ['required', 'integer', 'min:1'],
        ], [
            'source_id.required' => 'Pilih sumber data. Jika pilihan sumber tidak muncul, muat ulang halaman dengan Ctrl+F5.',
        ]);

        $question = trim($input['question']);
        if ($question === '') {
            return response()->json([
                'message' => 'Pertanyaan tidak boleh kosong.',
                'errors' => ['question' => ['Pertanyaan tidak boleh kosong.']],
            ], 422);
        }

        $result = $orchestrator->search(
            $question,
            (string) $request->user()->getAuthIdentifier(),
            isset($input['source_id']) ? (int) $input['source_id'] : null
        );
        $status = $result['http_status'];
        unset($result['http_status']);

        return response()->json($result, $status);
    }
}
