<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class UserSyncController extends Controller
{
    public function index()
    {
        return view('pages.usersync.index');
    }

    public function run(Request $request)
    {
        $request->validate([
            'since' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'chunk' => ['nullable', 'integer', 'min:50', 'max:5000'],
        ]);

        $since = $request->input('since');
        $chunk = (int) ($request->input('chunk') ?: 500);

        // build args untuk command
        $args = ['--chunk' => $chunk];
        if ($since) $args['--since'] = $since;

        // jalankan command
        Artisan::call('sync:users-das-to-pg', $args);

        $output = Artisan::output(); // ambil output command (info/line)

        return response()->json([
            'ok' => true,
            'message' => 'Sync executed',
            'output' => trim($output),
        ]);
    }
}