<?php

namespace App\Http\Controllers;

use App\Models\Autonbr;
use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\SysUserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Mail\MyAppResetPasswordMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

// "My Page": a stand-alone, installable (Add to Home Screen) page holding only the
// signed-in user's own work — Waiting Approval, Approval History, My Document,
// My Message, My Files. The manifest scope is limited to /my-page so the installed
// app cannot navigate into the rest of the system; document links leave that scope
// and so open in the device browser instead.
class MyPageController extends Controller
{
    // Sign-in, sign-out and password reset live under /my-page so the installed app never leaves its scope
    // (the normal /login is outside it, which makes the phone open the browser).

    // Email or username only. Only active (status A) accounts can sign in, so a duplicate email that also
    // exists on an inactive (X) row always lands on the active account. A username match always wins; an
    // email shared by several ACTIVE accounts is ambiguous and the user must use their username instead.
    // Returns [User|null, error message|null].
    private function resolveAccount(string $login): array
    {
        $login = mb_strtolower(trim($login));
        if ($login === '') return [null, null];

        $active = User::where('status', 'A')
            ->where(fn ($q) => $q->whereRaw('lower(username) = ?', [$login])->orWhereRaw('lower(email) = ?', [$login]))
            ->get();

        $byUsername = $active->filter(fn ($u) => mb_strtolower((string) $u->username) === $login);
        if ($byUsername->count() === 1) return [$byUsername->first(), null];

        $byEmail = $active->filter(fn ($u) => mb_strtolower((string) $u->email) === $login);
        if ($byEmail->count() === 1) return [$byEmail->first(), null];
        if ($byEmail->count() > 1) {
            return [null, 'This email is used by more than one account. Please sign in with your username instead.'];
        }

        return [null, null];
    }

    // Single-use marker: changes as soon as the password changes, which kills any reset link already sent.
    private function resetStamp(User $user): string
    {
        return hash('sha256', (string) $user->password);
    }

    public function loginForm()
    {
        return Auth::check() ? redirect()->route('my-page') : view('pages.my-page.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login'    => ['required'],
            'password' => ['required'],
        ]);

        [$user, $ambiguous] = $this->resolveAccount($credentials['login']);
        if ($ambiguous) throw ValidationException::withMessages(['login' => [$ambiguous]]);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages(['login' => ['These credentials do not match our records.']]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->route('my-page');
    }

    public function forgotForm()
    {
        return Auth::check() ? redirect()->route('my-page') : view('pages.my-page.forgot-password');
    }

    // Always answers the same way, so the form can't be used to find out which accounts exist.
    public function sendResetLink(Request $request)
    {
        $data = $request->validate(['login' => ['required', 'string', 'max:255']]);

        [$user, $ambiguous] = $this->resolveAccount($data['login']);
        if ($ambiguous) throw ValidationException::withMessages(['login' => [$ambiguous]]);

        if ($user && filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            try {
                $url = URL::temporarySignedRoute('my-page.reset', now()->addMinutes(60), [
                    'u' => $user->username,
                    's' => $this->resetStamp($user),
                ]);
                Mail::to($user->email)->send(new MyAppResetPasswordMail($user, $url));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return back()->with('status', 'If that account exists, we have emailed a reset link to its address. It is valid for 60 minutes.');
    }

    public function resetForm(Request $request)
    {
        [$user] = $this->resolveAccount((string) $request->query('u'));
        $valid = $user && hash_equals($this->resetStamp($user), (string) $request->query('s'));

        return view('pages.my-page.reset-password', ['valid' => $valid, 'u' => $request->query('u'), 's' => $request->query('s')]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'u'        => ['required', 'string'],
            's'        => ['required', 'string'],
            'password' => ['required', 'string', PasswordRule::default(), 'confirmed'],
        ]);

        [$user] = $this->resolveAccount($request->input('u'));
        if (!$user || !hash_equals($this->resetStamp($user), $request->input('s'))) {
            return redirect()->route('my-page.forgot')->withErrors(['login' => 'This reset link has already been used or has expired. Please request a new one.']);
        }

        $user->forceFill(['password' => Hash::make($request->input('password'))])->save();

        return redirect()->route('my-page.login')->with('status', 'Password updated. You can sign in now.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('my-page.login');
    }

    public function index()
    {
        // Not behind the global 'auth' middleware: that would send guests to /login (outside the app's scope).
        if (!Auth::check()) {
            return redirect()->route('my-page.login');
        }

        $user = Auth::user();

        return view('pages.my-page.index', [
            'doctypes' => Autonbr::query()
                ->select('doctype', 'doctype_descr')
                ->where('status', 'A')
                ->groupBy('doctype', 'doctype_descr')
                ->orderBy('doctype')
                ->get(),
            'akses_cc' => SysUserRole::where('username', $user->username)->where('role_id', 'COSTCTRLACCESS')->exists(),
            'isGA' => $user->hasRole('GAACCESS'),
            // shown on the Profile screen
            'originCpnyName' => $user->origin_cpny_id
                ? (MsCompany::where('cpny_id', $user->origin_cpny_id)->value('cpny_name') ?? $user->origin_cpny_id)
                : null,
            'originDepartmentName' => $user->origin_department_id
                ? (MsDepartment::where('department_id', $user->origin_department_id)->value('department_name') ?? $user->origin_department_id)
                : null,
        ]);
    }

    // changes whenever the icon files change, so installed apps fetch the new icon instead of a cached one
    private function iconVersion(): int
    {
        return (int) @filemtime(public_path('images/pwa/myapp-512.png'));
    }

    public function manifest()
    {
        return response()->json([
            'name'             => 'My APP',
            'short_name'       => 'My APP',
            'description'      => 'Waiting approval, approval history, documents, messages and files.',
            'id'               => '/my-page',
            'start_url'        => '/my-page',
            'scope'            => '/my-page',
            'display'          => 'standalone',
            'orientation'      => 'any',
            'background_color' => '#0b1a6b',
            'theme_color'      => '#0b1a6b',
            'icons'            => [
                ['src' => asset('images/pwa/myapp-192.png') . '?v=' . $this->iconVersion(), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => asset('images/pwa/myapp-512.png') . '?v=' . $this->iconVersion(), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => asset('images/pwa/myapp-1080.png') . '?v=' . $this->iconVersion(), 'sizes' => '1080x1080', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => asset('images/pwa/myapp-maskable-512.png') . '?v=' . $this->iconVersion(), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json']);
    }

    // Pass-through only: the data is per-user and authenticated, so nothing is cached.
    // The fetch handler exists because browsers require one before offering "Install".
    public function serviceWorker()
    {
        $js = <<<'JS'
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', e => e.waitUntil(self.clients.claim()));
self.addEventListener('fetch', () => {});

self.addEventListener('push', e => {
    let d = {};
    try { d = e.data ? e.data.json() : {}; } catch (err) { d = { body: e.data && e.data.text() }; }
    const jobs = [self.registration.showNotification(d.title || 'My APP', {
        body: d.body || '',
        icon: d.icon,
        badge: d.badge,
        tag: d.tag,
        renotify: !!d.tag,
        data: { url: d.url || '/my-page' },
    })];
    // home-screen icon badge = documents waiting for this user's approval
    if (typeof d.count === 'number' && self.navigator && self.navigator.setAppBadge) {
        jobs.push(d.count > 0 ? self.navigator.setAppBadge(d.count) : self.navigator.clearAppBadge());
    }
    e.waitUntil(Promise.all(jobs.map(p => Promise.resolve(p).catch(() => {}))));
});

// Tapping a notification lands inside the app. If it is already open it is brought forward and told which document to
// show (/my-page?open=<path>); otherwise the app is launched with that link. Either way the document opens in the
// app's own review sheet, so the phone never switches to a browser.
self.addEventListener('notificationclick', e => {
    e.notification.close();
    const raw = (e.notification.data && e.notification.data.url) || '/my-page';
    e.waitUntil((async () => {
        const url = new URL(raw, self.location.origin);
        const open = url.searchParams.get('open');
        const wins = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
        const app = wins.find(w => new URL(w.url).pathname.indexOf('/my-page') === 0);
        if (app) {
            if (open) app.postMessage({ type: 'mp-open', path: open });
            return app.focus();
        }
        return self.clients.openWindow(url.href);
    })());
});
JS;

        return response($js, 200, ['Content-Type' => 'application/javascript', 'Cache-Control' => 'no-cache']);
    }
}
