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

    public function markRead(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['success' => false], 401);

        // Accepts either a single "key" (click-through) or a "keys" array
        // (batch dismiss when the dropdown closes with unread items still visible).
        $keys = $request->input('keys');
        if (!is_array($keys)) {
            $single = trim((string) $request->input('key'));
            $keys = $single !== '' ? [$single] : [];
        }
        $keys = array_values(array_unique(array_filter(array_map('trim', $keys))));
        if (empty($keys)) return response()->json(['success' => false], 422);

        $username = strtolower(trim((string) $user->username));
        $readCacheKey = 'doc_notif_read_' . $username;

        $readKeys = Cache::get($readCacheKey, []);
        foreach ($keys as $key) {
            if (!in_array($key, $readKeys, true)) {
                $readKeys[] = $key;
            }
        }
        $readKeys = array_slice($readKeys, -300);
        Cache::put($readCacheKey, $readKeys, now()->addDays(60));

        // Force the notification list to rebuild without this item on the next poll,
        // instead of waiting up to 90 seconds for the existing cache to expire.
        Cache::forget('doc_notif_' . $username);

        return response()->json(['success' => true]);
    }
}
