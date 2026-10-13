<?php

namespace App\Console\Commands;

use App\Http\Controllers\ApprovalDashboardController;
use App\Models\SysPushSubscription;
use App\Models\User;
use App\Services\DocumentNotificationService;
use App\Services\WebPushService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SendPushNotifications extends Command
{
    protected $signature   = 'notifications:push {--user= : only this username}';
    protected $description = 'Push new bell notifications to subscribed devices (My Page app)';

    // More than this many new items in one run are collapsed into a single summary push.
    private const MAX_INDIVIDUAL = 3;

    public function handle(): int
    {
        if (!WebPushService::configured()) {
            $this->warn('VAPID keys are not configured.');
            return self::FAILURE;
        }

        $query = SysPushSubscription::where('status', 'A');
        if ($u = $this->option('user')) $query->where('username', strtolower(trim($u)));

        foreach ($query->get()->groupBy('username') as $username => $subs) {
            try {
                $this->pushForUser((string) $username, $subs);
            } catch (\Throwable $e) {
                // a failure for one user must not stop the rest
                report($e);
            }
        }

        return self::SUCCESS;
    }

    // Waiting Approval rows, shaped like bell items (key / docid / label / message / href).
    private function waitingApprovalItems(string $username): \Illuminate\Support\Collection
    {
        try {
            $user = User::whereRaw('lower(username) = ?', [$username])->where('status', 'A')->first();
            if (!$user) return collect();

            return app(ApprovalDashboardController::class)->waitingForUser($user)->map(function ($r) {
                $desc = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($r['infohd'] ?? ''))));

                return [
                    'key'     => 'WAITAPPR_' . $r['docid'],
                    'docid'   => $r['docid'],
                    'label'   => 'Waiting Approval',
                    'message' => $desc !== '' ? $desc : 'This document is waiting for your approval.',
                    'href'    => rtrim((string) $r['url'], '/') . '/' . $r['hid'] . ($r['query'] ?? ''),
                ];
            });
        } catch (\Throwable $e) {
            report($e);

            return collect();
        }
    }

    private function pushForUser(string $username, $subs): void
    {
        // Everything the bell shows, plus documents newly waiting for this user's approval
        // (the bell leaves those out because the Approval dashboard already lists them).
        $items = collect(DocumentNotificationService::buildForUser($username))
            ->filter(fn ($i) => !empty($i['key']))
            ->concat($this->waitingApprovalItems($username))
            ->values();
        $keys  = $items->pluck('key')->all();

        $cacheKey = 'push_sent_' . $username;
        $sent = Cache::get($cacheKey);

        // First run for this user (or right after subscribing): remember what is already there, send nothing.
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
                WebPushService::send($group, [
                    'title' => $new->count() . ' new notifications',
                    'body'  => $new->take(3)->map(fn ($i) => $i['docid'] ?? '')->filter()->implode(', ') . ', …',
                    'url'   => $origin . '/my-page',
                    'tag'   => 'push-summary',
                ]);
                continue;
            }

            foreach ($new as $i) {
                $path = $i['href'] ?? (rtrim((string) ($i['url'] ?? ''), '/') . '/' . ($i['hid'] ?? ''));
                WebPushService::send($group, [
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
