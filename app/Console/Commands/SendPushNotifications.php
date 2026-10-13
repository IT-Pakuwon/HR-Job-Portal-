<?php

namespace App\Console\Commands;

use App\Models\SysPushSubscription;
use App\Services\PushNotificationFeed;
use App\Services\WebPushService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SendPushNotifications extends Command
{
    protected $signature   = 'notifications:push {--user= : only this username}';
    protected $description = 'Push new bell notifications and new waiting approvals to subscribed devices (My Page app)';

    // More than this many new items in one run are collapsed into a single summary push.
    private const MAX_INDIVIDUAL = 3;

    private int $sent = 0;

    public function handle(): int
    {
        // Heartbeat first, so /push/diagnose can tell whether the scheduler is actually running this command.
        Cache::forever('push_last_run', now()->toIso8601String());

        if (!WebPushService::configured()) {
            $this->warn('VAPID keys are not configured.');
            return self::FAILURE;
        }

        $query = SysPushSubscription::where('status', 'A');
        if ($u = $this->option('user')) $query->where('username', strtolower(trim($u)));

        $groups = $query->get()->groupBy('username');

        foreach ($groups as $username => $subs) {
            try {
                $this->pushForUser((string) $username, $subs);
            } catch (\Throwable $e) {
                // a failure for one user must not stop the rest
                report($e);
            }
        }

        Cache::forever('push_last_run_info', ['at' => now()->toIso8601String(), 'users' => $groups->count(), 'sent' => $this->sent]);

        return self::SUCCESS;
    }

    private function pushForUser(string $username, $subs): void
    {
        $items = PushNotificationFeed::itemsFor($username);
        $keys  = $items->pluck('key')->all();

        $cacheKey = 'push_sent_' . $username;
        $sent = Cache::get($cacheKey);

        // Normally seeded the moment the device subscribes. Fallback for a user with no record yet:
        // remember what is already there and send nothing.
        if ($sent === null) {
            Cache::put($cacheKey, $keys, now()->addDays(60));
            return;
        }

        $new = $items->reject(fn ($i) => in_array($i['key'], $sent, true))->values();
        if ($new->isEmpty()) return;

        // Group by the origin each device subscribed from, so links point at the right host.
        foreach ($subs->groupBy('origin') as $origin => $group) {
            $origin = rtrim((string) ($origin ?: config('app.url')), '/');

            if ($new->count() > self::MAX_INDIVIDUAL) {
                $this->sent += WebPushService::send($group, [
                    'title' => $new->count() . ' new notifications',
                    'body'  => $new->take(3)->map(fn ($i) => $i['docid'] ?? '')->filter()->implode(', ') . ', …',
                    'url'   => $origin . '/my-page',
                    'tag'   => 'push-summary',
                ]);
                continue;
            }

            foreach ($new as $i) {
                $path = $i['href'] ?? (rtrim((string) ($i['url'] ?? ''), '/') . '/' . ($i['hid'] ?? ''));
                $this->sent += WebPushService::send($group, [
                    'title' => trim(($i['docid'] ?? 'Notification') . ' · ' . ($i['label'] ?? '')),
                    'body'  => Str::limit(strip_tags((string) ($i['message'] ?? '')), 160),
                    'url'   => Str::startsWith($path, ['http://', 'https://']) ? $path : $origin . '/' . ltrim($path, '/'),
                    'tag'   => $i['key'],
                ]);
            }
        }

        Cache::put($cacheKey, array_slice(array_values(array_unique(array_merge($sent, $keys))), -500), now()->addDays(60));
    }
}
