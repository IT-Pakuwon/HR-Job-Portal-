<?php

namespace App\Http\Controllers;

use App\Models\SysPushSubscription;
use App\Services\WebPushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

// Device registration for My Page push notifications (the app's Profile > bell button).
class PushSubscriptionController extends Controller
{
    public function key()
    {
        return response()->json(['key' => config('webpush.public_key'), 'enabled' => WebPushService::configured()]);
    }

    // Whether *this* browser is already subscribed (so the bell shows the right state).
    public function status(Request $request)
    {
        $endpoint = (string) $request->query('endpoint');

        return response()->json([
            'subscribed' => $endpoint !== '' && SysPushSubscription::where('username', $this->username($request))
                ->where('endpoint', $endpoint)->where('status', 'A')->exists(),
        ]);
    }

    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'endpoint'    => 'required|url|max:2000',
            'keys.p256dh' => 'required|string',
            'keys.auth'   => 'required|string',
        ]);
        $username = $this->username($request);

        $sub = SysPushSubscription::updateOrCreate(
            ['endpoint' => $data['endpoint']],
            [
                'username'   => $username,
                'p256dh'     => $data['keys']['p256dh'],
                'auth'       => $data['keys']['auth'],
                'origin'     => $request->getSchemeAndHttpHost(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'status'     => 'A',
            ]
        );

        // Start from "everything currently in the bell is already known" so enabling doesn't blast the backlog.
        Cache::forget('push_sent_' . $username);

        WebPushService::send(collect([$sub]), [
            'title' => 'Notifications enabled',
            'body'  => 'You will now get My Page notifications on this device.',
            'url'   => $sub->origin . '/my-page',
            'tag'   => 'push-enabled',
        ]);

        return response()->json(['success' => true]);
    }

    public function unsubscribe(Request $request)
    {
        $request->validate(['endpoint' => 'required|string']);

        SysPushSubscription::where('username', $this->username($request))
            ->where('endpoint', $request->input('endpoint'))->delete();

        return response()->json(['success' => true]);
    }

    private function username(Request $request): string
    {
        return strtolower(trim((string) $request->user()->username));
    }
}
