<?php

namespace App\Services;

use App\Models\SysPushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

// Sends Web Push messages to a user's subscribed devices. Tapping one opens `url` directly in the browser
// (see the notificationclick handler in MyPageController::serviceWorker()).
class WebPushService
{
    public static function configured(): bool
    {
        return (bool) (config('webpush.public_key') && config('webpush.private_key'));
    }

    /**
     * @param  \Illuminate\Support\Collection<SysPushSubscription>  $subs
     * @param  array  $payload  title, body, url (absolute), tag, icon?
     * Returns the number of messages accepted by the push services. Dead subscriptions (404/410) are deleted.
     */
    public static function send($subs, array $payload): int
    {
        if (!self::configured() || $subs->isEmpty()) return 0;

        $webPush = new WebPush(['VAPID' => [
            'subject'    => config('webpush.subject'),
            'publicKey'  => config('webpush.public_key'),
            'privateKey' => config('webpush.private_key'),
        ]], ['TTL' => 3600, 'urgency' => 'normal']);

        $byEndpoint = [];
        foreach ($subs as $sub) {
            $byEndpoint[$sub->endpoint] = $sub;
            $webPush->queueNotification(
                Subscription::create(['endpoint' => $sub->endpoint, 'keys' => ['p256dh' => $sub->p256dh, 'auth' => $sub->auth]]),
                json_encode($payload + [
                    'icon'  => asset('images/pwa/myapp-192.png'),
                    // Android keeps only the alpha channel of the badge, so it must be a white glyph on a
                    // transparent background — the coloured 192 icon rendered as a solid white square.
                    'badge' => asset('images/pwa/myapp-badge.png'),
                ])
            );
        }

        $ok = 0;
        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) { $ok++; continue; }

            if ($report->isSubscriptionExpired() && isset($byEndpoint[$report->getEndpoint()])) {
                $byEndpoint[$report->getEndpoint()]->delete();
            } else {
                Log::warning('WebPush failed', ['reason' => $report->getReason()]);
            }
        }

        return $ok;
    }
}
