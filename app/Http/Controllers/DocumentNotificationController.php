<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\DocumentNotificationService;

class DocumentNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['data' => []], 401);

        $username = strtolower(trim((string) $user->username));
        $cacheKey = 'doc_notif_' . $username;

        // Track this user as active so the scheduler pre-warms their cache
        $activeKey = 'doc_notif_active_users';
        $active    = Cache::get($activeKey, []);
        if (!in_array($username, $active)) {
            $active[] = $username;
            Cache::put($activeKey, $active, now()->addMinutes(10));
        }

        // Serve from cache if available (scheduler refreshes every minute)
        if (($cached = Cache::get($cacheKey)) !== null) {
            return response()->json(['data' => $cached]);
        }

        // Cache miss (first load) — build live and cache for 90 seconds
        $data = DocumentNotificationService::buildForUser($username);
        Cache::put($cacheKey, $data, now()->addSeconds(90));

        return response()->json(['data' => $data]);
    }
}
