<?php

namespace App\Http\Controllers;

use App\Models\SysPushPref;
use App\Models\TrApproval;
use App\Models\SysPushSubscription;
use App\Services\PushNotificationFeed;
use App\Services\PushPreferences;
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

        // What the scheduler sees for the signed-in user
        try {
            $username = $this->username($request);
            $items = PushNotificationFeed::itemsFor($username);
            $waiting = $items->filter(fn ($i) => str_starts_with($i['key'], 'WAITAPPR_'));
            $sent = Cache::get('push_sent_' . $username);

            $lineDocs = TrApproval::query()
                ->where('status', 'P')->whereNotNull('aprv_datebefore')
                ->whereRaw("(',' || lower(regexp_replace(coalesce(aprv_username,''), '\s+', '', 'g')) || ',') like ?", ['%,' . $username . ',%'])
                ->pluck('refnbr')->map(fn ($r) => strtoupper(trim($r)))->unique()->values();
            $found = $waiting->pluck('docid')->map(fn ($r) => strtoupper(trim($r)));
            $missing = $lineDocs->diff($found)->values();

            $checks['my_feed_items'] = $items->count();
            $checks['my_waiting_approval_items'] = $waiting->count();
            $checks['my_approval_documents_in_the_system'] = $lineDocs->count();
            $checks['my_approvals_the_list_cannot_find'] = $missing->isEmpty()
                ? 0
                : 'NO — ' . $missing->count() . ' approval(s) are waiting for you but the document lookup cannot find them, so they never appear in the app or in notifications: ' . $missing->take(10)->implode(', ');
            $checks['already_notified_keys_remembered'] = $sent === null ? 'none yet (set when notifications are enabled)' : count($sent);
            $checks['items_that_the_next_run_will_push'] = $sent === null ? 'n/a' : $items->reject(fn ($i) => in_array($i['key'], $sent, true))->count();

            $prefs = PushPreferences::forUser($username);
            $checks['my_notification_settings'] = $prefs;
            $checks['quiet_hours_active_now'] = PushPreferences::inQuietHours($prefs) ? 'yes (pushes are being held until quiet hours end)' : 'no';
        } catch (\Throwable $e) {
            $checks['my_feed'] = 'ERROR: ' . $e->getMessage();
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
        $waiting = 0;
        try {
            $items = PushNotificationFeed::itemsFor($username);
            $waiting = $items->filter(fn ($i) => str_starts_with($i['key'], 'WAITAPPR_'))->count();
            Cache::put('push_sent_' . $username, $items->pluck('key')->all(), now()->addDays(60));
        } catch (\Throwable $e) {
            Log::warning('Push subscribe: could not seed known notifications', ['err' => $e->getMessage()]);
            Cache::forget('push_sent_' . $username);
        }

        // Confirmation push. It also says how many approvals are already waiting, because those were just marked as known
        // and will not be pushed again. If it can't be sent (e.g. OpenSSL not set up on the server) the device is still registered.
        $welcomeSent = false;
        try {
            $welcomeSent = WebPushService::send(collect([$sub]), [
                'title' => 'Notifications enabled',
                'body'  => 'You will now get My APP notifications on this device.'
                    . ($waiting > 0 ? ' You have ' . $waiting . ' document' . ($waiting > 1 ? 's' : '') . ' waiting for your approval.' : ''),
                'url'   => rtrim((string) $sub->origin, '/') . '/my-page',
                'tag'   => 'push-enabled',
                'count' => $waiting,
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

    // POST /push/test — sends one notification to this user's own devices right now. Separates "can this phone receive
    // pushes at all" (this works) from "is the scheduler / document lookup producing them" (see /push/diagnose).
    public function test(Request $request)
    {
        $subs = SysPushSubscription::where('username', $this->username($request))->where('status', 'A')->get();

        if ($subs->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No device is registered. Turn on notifications first (the bell).']);
        }

        try {
            $sent = WebPushService::send($subs, [
                'title' => 'Test notification',
                'body'  => 'If you can read this, push notifications reach this device.',
                'url'   => rtrim((string) ($subs->first()->origin ?: config('app.url')), '/') . '/my-page',
                'tag'   => 'push-test',
            ]);
        } catch (\Throwable $e) {
            Log::error('Push test failed', ['err' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Could not send: ' . $e->getMessage()]);
        }

        return response()->json(['success' => $sent > 0, 'devices' => $subs->count(), 'sent' => $sent,
            'message' => $sent > 0 ? 'Test sent to ' . $sent . ' device(s).' : 'The push service did not accept it. See /push/diagnose.']);
    }

    // GET /push/health — a short, human-readable status for the signed-in user, shown under "Send a test notification".
    // Tells apart: the scheduled job isn't running / the server cache doesn't work / the job runs but finds nothing new.
    public function health(Request $request)
    {
        $username = $this->username($request);
        $out = [];

        try {
            $last = Cache::get('push_last_run');
            $age = $last ? now()->diffInSeconds(\Illuminate\Support\Carbon::parse($last)) : null;
            $out['scheduler_running'] = $age !== null && $age <= 180;
            $out['last_run_seconds_ago'] = $age;
            $out['last_run'] = Cache::get('push_last_run_info');
        } catch (\Throwable $e) {
            $out['scheduler_error'] = $e->getMessage();
        }

        try {
            $probe = (string) microtime(true);
            Cache::put('push_probe_' . $username, $probe, 60);
            $out['cache_ok'] = Cache::get('push_probe_' . $username) === $probe;
        } catch (\Throwable $e) {
            $out['cache_ok'] = false;
            $out['cache_error'] = $e->getMessage();
        }

        try {
            $items = PushNotificationFeed::itemsFor($username);
            $sent = Cache::get('push_sent_' . $username);
            $out['waiting_found'] = $items->filter(fn ($i) => str_starts_with($i['key'], 'WAITAPPR_'))->count();
            $out['remembered'] = $sent === null ? null : count($sent);
            $out['would_push_next'] = $sent === null ? null : $items->reject(fn ($i) => in_array($i['key'], $sent, true))->count();
            $out['quiet_now'] = PushPreferences::inQuietHours(PushPreferences::forUser($username));
        } catch (\Throwable $e) {
            $out['feed_error'] = $e->getMessage();
        }

        try {
            $out['devices'] = SysPushSubscription::where('username', $username)->where('status', 'A')->count();
        } catch (\Throwable $e) {
            $out['devices'] = null;
            $out['devices_error'] = 'The push tables are missing or unreachable in this database: ' . \Illuminate\Support\Str::limit($e->getMessage(), 160);
        }

        // why the scheduled job last failed, if it did
        $out['last_error'] = Cache::get('push_last_error');

        return response()->json($out);
    }

    // Notification settings shown in My APP > Profile > bell menu.
    public function prefs(Request $request)
    {
        return response()->json(PushPreferences::forUser($this->username($request)));
    }

    public function savePrefs(Request $request)
    {
        $data = $request->validate([
            'approvals'  => 'required|boolean',
            'updates'    => 'required|boolean',
            'comments'   => 'required|boolean',
            'others'     => 'required|boolean',
            'quiet_on'   => 'required|boolean',
            'quiet_from' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'quiet_to'   => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'tz'         => 'nullable|string|max:64|timezone',
        ]);
        $username = $this->username($request);

        try {
            SysPushPref::updateOrCreate(['username' => $username], $data);
        } catch (\Throwable $e) {
            Log::error('Push prefs: could not save', ['err' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Could not save (is the sys_push_pref table created on the server?)'], 500);
        }

        return response()->json(['success' => true]);
    }

    private function username(Request $request): string
    {
        return strtolower(trim((string) $request->user()->username));
    }
}
