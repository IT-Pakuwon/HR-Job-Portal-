<?php

namespace App\Http\Controllers\Vms;

use App\Http\Controllers\Controller;
use App\Services\Vms\SetupCatalog;
use Illuminate\Http\Request;

class SetupController extends Controller
{
    public function index(Request $request, SetupCatalog $catalog)
    {
        abort_unless($request->user() && in_array('admin', $request->user()->roles(), true), 403);
        $filters = $request->validate([
            'tab' => ['nullable', 'string', 'max:30'],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50,100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $definition = $catalog->definition((string) $request->route('section'), $filters['tab'] ?? null);
        $rows = $catalog->query($definition, trim($filters['search'] ?? ''))
            ->paginate($filters['per_page'] ?? 10)->withQueryString();

        return response()->view('pages.vms-setup.index', $definition + compact('rows', 'filters'))
            ->header('Cache-Control', 'no-store, private');
    }
}
