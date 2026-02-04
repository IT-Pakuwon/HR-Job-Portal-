<?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use Laravel\Socialite\Facades\Socialite;
    use Google_Client;
    use Google_Service_Calendar;
    use Google_Service_Calendar_Event;
    use Carbon\Carbon;

    class GoogleCalendarController extends Controller
    {
        /* ================= OAUTH ================= */
        public function redirect()
        {
            return Socialite::driver('google')
                ->scopes(['https://www.googleapis.com/auth/calendar.events'])
                ->redirect();
        }

        public function callback(Request $request)
        {
            $googleUser = Socialite::driver('google')->user();

            $request->session()->put('google_token', [
                'access_token'  => $googleUser->token,
                'refresh_token' => $googleUser->refreshToken,
                'expires_in'    => $googleUser->expiresIn,
                'created_at'    => time(),
            ]);

            return redirect('/modules');
        }

        /* ================= CREATE EVENT ================= */
        public function createEvent(Request $request)
        {
            $token = $request->session()->get('google_token');
            if (!$token) {
                return response()->json(['error' => 'Google not connected'], 403);
            }

            /* ================= CLIENT ================= */
            $client = new Google_Client();
            $client->setAccessToken($token);

            // 🔄 Refresh token safely
            if ($client->isAccessTokenExpired() && isset($token['refresh_token'])) {
                $newToken = $client->fetchAccessTokenWithRefreshToken(
                    $token['refresh_token']
                );

                $request->session()->put('google_token', array_merge(
                    $token,
                    $newToken,
                    ['created_at' => time()]
                ));

                $client->setAccessToken(
                    $request->session()->get('google_token')
                );
            }

            $service = new Google_Service_Calendar($client);

            /* ================= DATE / TIME LOGIC ================= */

            if ($request->start_time) {

                $start = Carbon::parse(
                    $request->deadline . ' ' . $request->start_time
                );

                $end = $request->end_time
                    ? Carbon::parse($request->deadline . ' ' . $request->end_time)
                    : $start->copy()->addHour();

                $startPayload = ['dateTime' => $start->toRfc3339String()];
                $endPayload   = ['dateTime' => $end->toRfc3339String()];

            } else {
                // All-day event
                $startPayload = ['date' => $request->deadline];
                $endPayload = [
                    'date' => Carbon::parse($request->deadline)
                        ->addDay()
                        ->toDateString()
                ];
            }

            /* ================= EVENT ================= */
            $event = new Google_Service_Calendar_Event([
                'summary'     => $request->title,
                'location'    => $request->location ?: null,
                'description' => $request->link ?: null,
                'start'       => $startPayload,
                'end'         => $endPayload,
            ]);

            $service->events->insert('primary', $event);

            return response()->json(['status' => 'synced']);
        }



        /* ================= LIST EVENTS ================= */
        public function listEvents(Request $request)
        {
            $token = $request->session()->get('google_token');
            if (!$token) return response()->json(['error' => 'Google not connected'], 403);

            $client = new Google_Client();
            $client->setAccessToken($token);

            if ($client->isAccessTokenExpired() && isset($token['refresh_token'])) {
                $newToken = $client->fetchAccessTokenWithRefreshToken($token['refresh_token']);
                $request->session()->put('google_token', array_merge(
                    $token,
                    $newToken,
                    ['created_at' => time()]
                ));
            }

            $service = new Google_Service_Calendar($client);

            $events = $service->events->listEvents('primary', [
                'singleEvents' => true,
                'orderBy' => 'startTime',
                'maxResults' => 20,
                'timeMin' => now()->startOfDay()->toRfc3339String(),
            ]);

            return response()->json(
                collect($events->getItems())->map(fn ($e) => [
                    'id' => $e->getId(),
                    'title' => $e->getSummary(),
                    'start' => $e->getStart()->getDate()
                        ?? $e->getStart()->getDateTime(),
                    'location' => $e->getLocation(),
                    'link' => $e->getDescription(),
                ])
            );
        }
    }
