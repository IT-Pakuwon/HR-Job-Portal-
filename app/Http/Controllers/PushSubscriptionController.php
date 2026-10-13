<?php

namespace App\Http\Controllers;

use App\Models\SysPushSubscription;
use App\Services\PushNotificationFeed;
use App\Services\WebPushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

// Device registration for My Page push notifications (the app's Profile > bell button).
class PushSubscriptionController extends Controller
{
    public function key()
    {
        return response()->json(['key' => config('webpush.public_key'), 'enabled' => WebPushService::configured()]);
    }

    // GET /push/diagnose — open it in the browser (while logged in) to see exactly which server requirement is missing.
    public function diagnose(Request $request)
    {
        $checks = [];

        $checks['vapid_keys_configured'] = WebPushService::configured();
        $checks['package_installed (minishlink/web-push)'] = class_exists(\Minishlink\WebPush\WebPush::class);

        try {
            $checks['table_exists (sys_push_subscription)'] = Schema::connection('pgsql2')->hasTable('sys_push_subscription');
        } catch (\Throwable $e) {
            $checks['table_exists (sys_push_subscription)'] = 'ERROR: ' . $e->getMessage();
        }

        $checks['openssl_extension'] = extension_loaded('openssl');
        $ec = function_exists('openssl_pkey_new')
            ? @openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC])
            : false;
        $checks['openssl_can_create_ec_key (needed to send)'] = $ec !== false
            ? true
            : 'FAILED: ' . (function_exists('openssl_error_string') ? (openssl_error_string() ?: 'unknown') : 'no openssl')
                . ' — on Windows/XAMPP set OPENSSL_CONF to the openssl.cnf file and restart the web server';
        $last = Cache::get('push_last_run');
        $age = $last ? now()->diffInSeconds(\Illuminate\Support\Carbon::parse($last)) : null;
        $checks['scheduler_running (notifications:push ran in the last 3 min)'] = ($age !== null && $age <= 180)
            ? true
            : 'NO — notifications:push has ' . ($last ? 'not run since ' . $last . ' (' . round($age / 60, 1) . ' min ago)' : 'never run')
                . '. Check the cron / Task Scheduler entry for "php artisan schedule:run" (every minute).';
        $checks['last_push_run'] = Cache::get('push_last_run_info') ?: 'none yet';
        $checks['curl_extension'] = extension_loaded('curl');
        $checks['bcmath_or_gmp'] = extension_loaded('bcmath') || extension_loaded('gmp');
        $checks['https'] = $request->isSecure() ?: 'NO — push only works over HTTPS (localhost excepted)';
        $checks['php'] = PHP_VERSION . ' / ' . PHP_OS_FAMILY;
        $checks['app_url'] = config('app.url');

        try {
            $checks['my_devices_registered'] = SysPushSubscription::where('username', $this->username($request))->where('status', 'A')->count();
        } catch (\Throwable $e) {
            $checks['my_devices_registered'] = 'ERROR: ' . $e->getMessage();
        }

        $problems = collect($checks)->filter(fn ($v, $k) => $v === false || (is_string($v) && (str_starts_with($v, 'ERROR') || str_starts_with($v, 'FAILED') || str_starts_with($v, 'NO'))))->keys()->values();

        return response()->json(['ok' => $problems->isEmpty(), 'problems' => $problems, 'checks' => $checks], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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

        try {
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
        } catch (\Throwable $e) {
            Log::error('Push subscribe: could not save subscription', ['err' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Could not save this device (is the sys_push_subscription table created on the server?)'], 500);
        }

        // Record everything that exists right now as "already known" so enabling doesn't blast the backlog, and so a
        // document submitted a moment later is still pushed (seeding here, not on the scheduler's next tick, avoids a race).
        try {
            Cache::put('push_sent_' . $username, PushNotificationFeed::itemsFor($username)->pluck('key')->all(), now()->addDays(60));
        } catch (\Throwable $e) {
            Log::warning('Push subscribe: could not seed known notifications', ['err' => $e->getMessage()]);
            Cache::forget('push_sent_' . $username);
        }

        // Confirmation push. If it can't be sent (e.g. OpenSSL not set up on the server) the device is still registered.
        $welcomeSent = false;
        try {
            $welcomeSent = WebPushService::send(collect([$sub]), [
                'title' => 'Notifications enabled',
                'body'  => 'You will now get My Page notifications on this device.',
                'url'   => $sub->origin . '/my-page',
                'tag'   => 'push-enabled',
            ]) > 0;
        } catch (\Throwable $e) {
            Log::error('Push subscribe: welcome push failed', ['err' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'welcome_sent' => $welcomeSent]);
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
