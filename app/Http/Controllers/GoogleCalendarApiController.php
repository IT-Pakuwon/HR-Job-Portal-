<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserGoogle;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Google_Service_Calendar_Event;

class GoogleCalendarApiController extends Controller
{
    /**
     * Check Google connection status
     */
    public function status()
    {
        $connected = UserGoogle::where('username', auth()->user()->username)
            ->whereNull('deleted_at')
            ->exists();

        return response()->json([
            'connected' => $connected
        ]);
    }

    /**
     * Load Google events for current week
     */
    public function events()
    {
        $google = UserGoogle::where('username', auth()->user()->username)
            ->whereNull('deleted_at')
            ->first();

        if (!$google) {
            return response()->json([]);
        }

        $service = GoogleCalendarService::make($google);

        $start = now()->startOfWeek()->toRfc3339String();
        $end   = now()->endOfWeek()->toRfc3339String();

        $events = $service->events->listEvents('primary', [
            'timeMin' => $start,
            'timeMax' => $end,
            'singleEvents' => true,
            'orderBy' => 'startTime',
        ]);

        $result = [];

        foreach ($events->getItems() as $event) {
            $startTime = $event->start->dateTime ?? $event->start->date;
            $endTime   = $event->end->dateTime ?? $event->end->date;

            $result[] = [
                'id' => $event->id,
                'title' => $event->summary,
                'start' => $startTime,
                'end' => $endTime,
                'location' => $event->location,
                'link' => $event->htmlLink,
            ];
        }

        return response()->json($result);
    }

    /**
     * Create Google event from Alpine modal
     */
    public function createEvent(Request $request)
    {
        $google = UserGoogle::where('username', auth()->user()->username)
            ->whereNull('deleted_at')
            ->first();

        if (!$google) {
            return response()->json(['message' => 'Google not connected'], 403);
        }

        $service = GoogleCalendarService::make($google);

        $start = Carbon::parse($request->deadline . ' ' . ($request->start_time ?? '09:00'));
        $end   = $request->end_time
            ? Carbon::parse($request->deadline . ' ' . $request->end_time)
            : $start->copy()->addHour();

        $event = new Google_Service_Calendar_Event([
            'summary' => $request->title,
            'location' => $request->location,
            'description' => $request->link,
            'start' => [
                'dateTime' => $start->toIso8601String(),
                'timeZone' => config('app.timezone'),
            ],
            'end' => [
                'dateTime' => $end->toIso8601String(),
                'timeZone' => config('app.timezone'),
            ],
        ]);

        $created = $service->events->insert('primary', $event);

        return response()->json([
            'id' => $created->id
        ]);
    }
}
