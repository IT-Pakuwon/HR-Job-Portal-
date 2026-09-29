<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\Tenancy\TsSite;

class TsSiteController extends Controller
{
    public function options()
    {
        $sites = TsSite::where('status', 'A')
            ->orderBy('sitename')
            ->get(['id', 'siteid', 'sitename']);

        return response()->json(['data' => $sites]);
    }

    public function siteTypes()
    {
        $types = TsSite::where('status', 'A')
            ->whereNotNull('sitetype')
            ->distinct()
            ->orderBy('sitetype')
            ->pluck('sitetype');

        return response()->json(['data' => $types]);
    }
}
