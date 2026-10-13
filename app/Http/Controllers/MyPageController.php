<?php

namespace App\Http\Controllers;

use App\Models\Autonbr;
use App\Models\SysUserRole;
use Illuminate\Support\Facades\Auth;

// "My Page": a stand-alone, installable (Add to Home Screen) page holding only the
// signed-in user's own work — Waiting Approval, Approval History, My Document,
// My Message, My Files. The manifest scope is limited to /my-page so the installed
// app cannot navigate into the rest of the system; document links leave that scope
// and so open in the device browser instead.
class MyPageController extends Controller
{
    public function index()
    {
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
        ]);
    }

    public function manifest()
    {
        return response()->json([
            'name'             => 'My Page',
            'short_name'       => 'My Page',
            'description'      => 'Waiting approval, approval history, documents, messages and files.',
            'id'               => '/my-page',
            'start_url'        => '/my-page',
            'scope'            => '/my-page',
            'display'          => 'standalone',
            'orientation'      => 'any',
            'background_color' => '#0b1a6b',
            'theme_color'      => '#0b1a6b',
            'icons'            => [
                ['src' => asset('images/pwa/myapp-192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => asset('images/pwa/myapp-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => asset('images/pwa/myapp-1080.png'), 'sizes' => '1080x1080', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => asset('images/pwa/myapp-maskable-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
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
    e.waitUntil(self.registration.showNotification(d.title || 'My Page', {
        body: d.body || '',
        icon: d.icon,
        badge: d.badge,
        tag: d.tag,
        renotify: !!d.tag,
        data: { url: d.url || '/my-page' },
    }));
});

// Tapping a notification opens its document straight in the browser (a document URL is outside this app's scope).
self.addEventListener('notificationclick', e => {
    e.notification.close();
    const url = (e.notification.data && e.notification.data.url) || '/my-page';
    e.waitUntil(self.clients.openWindow(url));
});
JS;

        return response($js, 200, ['Content-Type' => 'application/javascript', 'Cache-Control' => 'no-cache']);
    }
}
