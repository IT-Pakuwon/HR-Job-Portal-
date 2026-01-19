<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;

class GoogleCalendarController extends Controller
{
    /**
     * Redirect user to Google OAuth
     */
    public function redirect()
    {
        return Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/calendar.events'])
            ->redirect();
    }

    /**
     * OAuth callback — STORE TOKEN IN SESSION (NO DB)
     */
    public function callback(Request $request)
    {
        $googleUser = Socialite::driver('google')->user();

        $token = [
            'access_token'  => $googleUser->token,
            'refresh_token' => $googleUser->refreshToken,
            'expires_in'    => $googleUser->expiresIn,
            'created_at'    => time(),
        ];

        // ✅ Store token in SESSION (TEMPORARY)
        $request->session()->put('google_token', $token);

        return redirect('/modules?google=connected');
    }

    /**
     * Create Google Calendar Event (SESSION TOKEN)
     */
public function createEvent(Request $request)
{
    $token = $request->session()->get('google_token');

    if (!$token) {
        return response()->json(['error' => 'Google not connected'], 403);
    }

    $client = new Google_Client();
    $client->setAccessToken($token);

    if ($client->isAccessTokenExpired() && isset($token['refresh_token'])) {
        $client->fetchAccessTokenWithRefreshToken($token['refresh_token']);

        // ✅ MERGE token safely
        $request->session()->put('google_token', array_merge(
            $token,
            $client->getAccessToken(),
            ['created_at' => time()]
        ));
    }

    $service = new Google_Service_Calendar($client);

    $event = new Google_Service_Calendar_Event([
        'summary' => $request->title,
        'start' => [
            'date' => $request->deadline,
        ],
        'end' => [
            // ✅ MUST BE +1 DAY
            'date' => Carbon::parse($request->deadline)->addDay()->toDateString(),
        ],
    ]);

    $service->events->insert('primary', $event);

    return response()->json(['status' => 'synced']);
}

    public function listEvents(Request $request) {
    $token = $request->session()->get('google_token');

    if (!$token) {
        return response()->json(['error' => 'Google not connected'], 403);
    }

    $client = new \Google_Client();
    $client->setAccessToken($token);

    // Refresh token if expired
    if ($client->isAccessTokenExpired() && isset($token['refresh_token'])) {
        $client->fetchAccessTokenWithRefreshToken($token['refresh_token']);
        $request->session()->put('google_token', $client->getAccessToken());
    }

    $service = new \Google_Service_Calendar($client);

    $events = $service->events->listEvents('primary', [
        'maxResults' => 20,
        'orderBy' => 'startTime',
        'singleEvents' => true,
        'timeMin' => now()->startOfDay()->toRfc3339String(),
    ]);

    $data = collect($events->getItems())->map(function ($event) {
        return [
            'id' => $event->getId(),
            'title' => $event->getSummary(),
            'start' => $event->getStart()->getDate() 
                ?? $event->getStart()->getDateTime(),
            'end' => $event->getEnd()->getDate() 
                ?? $event->getEnd()->getDateTime(),
        ];
    });

    return response()->json($data);
    }
}
