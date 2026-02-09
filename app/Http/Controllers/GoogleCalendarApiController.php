<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserGoogle;
use App\Models\Task;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Google_Service_Calendar_Event;


class GoogleCalendarApiController extends Controller
{
    /**
     * Check Google connection status
     */
    // public function status()
    // {
    //     $connected = UserGoogle::where('username', auth()->user()->username)
    //         ->whereNull('deleted_at')
    //         ->exists();

    //     return response()->json([
    //         'connected' => $connected
    //     ]);
    // }

    public function status()
    {
        if (!auth()->check()) {
            return response()->json(['connected' => false]);
        }

        $connected = UserGoogle::where('username', auth()->user()->username)
            ->whereNull('deleted_at')
            ->exists();

        return response()->json(['connected' => $connected]);
    }


    /**
     * Load Google events for current week
     */

    public function events()
{
    $user = auth()->user();

    // 1️⃣ Always load LOCAL tasks
    $localTasks = Task::where('created_by', $user->username)
        ->get()
        ->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->task_title,
                'start' => $task->start_date
                    ? $task->start_date->toIso8601String()
                    : Carbon::parse($task->taskdate)->toIso8601String(),
                'end' => $task->end_date
                    ? $task->end_date->toIso8601String()
                    : null,
                'allDay' => $task->start_date === null,
                'description' => $task->task_description,
                'location' => $task->task_location,
                'fromGoogle' => false,
            ];
        });

    // 2️⃣ Check Google connection
    $google = UserGoogle::where('username', $user->username)
        ->whereNull('deleted_at')
        ->first();

    // ❌ NOT CONNECTED → return local only
    if (!$google) {
        return response()->json($localTasks->values());
    }

    // 3️⃣ Fetch Google events
    $service = GoogleCalendarService::make($google);

    $events = $service->events->listEvents('primary', [
        'singleEvents' => true,
        'orderBy' => 'startTime',
        'timeMin' => now()->subMonths(1)->toRfc3339String(),
        'timeMax' => now()->addMonths(1)->toRfc3339String(),
    ]);

    $googleEvents = collect($events->getItems())->map(function ($event) {
        return [
            'id' => 'google_' . $event->id, // 👈 avoid ID collision
            'title' => $event->summary ?? '(No title)',
            'start' => $event->start->dateTime ?? $event->start->date,
            'end' => $event->end->dateTime ?? $event->end->date,
            'allDay' => isset($event->start->date),
            'description' => $event->description ?? '',
            'location' => $event->location ?? '',
            'fromGoogle' => true,
        ];
    });

    // 4️⃣ MERGE local + google
    return response()->json(
        $localTasks->merge($googleEvents)->values()
    );
}
    // public function events()
    // {
    //     $google = UserGoogle::where('username', auth()->user()->username)
    //         ->whereNull('deleted_at')
    //         ->first();

    //     if (!$google) {
    //         return response()->json([]);
    //     }

    //     $service = GoogleCalendarService::make($google);

    //     $start = now()->startOfWeek()->toRfc3339String();
    //     $end   = now()->endOfWeek()->toRfc3339String();

    //     $events = $service->events->listEvents('primary', [
    //         'timeMin' => $start,
    //         'timeMax' => $end,
    //         'singleEvents' => true,
    //         'orderBy' => 'startTime',
    //     ]);

    //     $result = [];

    //     foreach ($events->getItems() as $event) {
    //         $startTime = $event->start->dateTime ?? $event->start->date;
    //         $endTime   = $event->end->dateTime ?? $event->end->date;
    //         $task = Task::updateOrCreate(
    //             ['google_event_id' => $event->id],
    //             [
    //                 'taskdate'       => Carbon::parse($startTime)->toDateString(),
    //                 'task_title'     => $event->summary ?? '(No title)',
    //                 'start_date'     => $event->start->dateTime ? Carbon::parse($startTime) : null,
    //                 'end_date'       => $event->end->dateTime ? Carbon::parse($endTime) : null,
    //                 'task_location'  => $event->location,
    //                 'sync_to_google' => true,

    //                 // ✅ FIX HERE
    //                 'status'         => Task::STATUS_MAP['PENDING'],
    //                 'updated_by'     => auth()->id(),
    //             ]
    //         );


    //         if ($task->wasRecentlyCreated) {
    //             $task->created_by = auth()->id();
    //             $task->save();
    //         }


    //         $result[] = [
    //             'id'    => $task->id,
    //             'title' => $task->task_title,

    //             'start' => $task->start_date
    //                 ? $task->start_date->toIso8601String()
    //                 : Carbon::parse($task->taskdate)->toIso8601String(),

    //             'end'   => $task->end_date
    //                 ? $task->end_date->toIso8601String()
    //                 : null,

    //             // ✅ THIS IS THE LINE YOU ASKED ABOUT
    //             'allDay' => $task->start_date === null,

    //             'description' => $task->task_description,
    //             'location'    => $task->task_location,
    //             'fromGoogle'  => true,
    //         ];


    //     }

    //     return response()->json(collect($events)->map(function ($event) {
    //         return [
    //             'id' => $event->id,
    //             'title' => $event->summary ?? '(No title)',

    //             // ✅ THIS IS THE KEY FIX
    //             'start' => $event->start->dateTime ?? $event->start->date,
    //             'end'   => $event->end->dateTime ?? $event->end->date,

    //             'allDay' => isset($event->start->date),
    //             'fromGoogle' => true,
    //         ];
    //     }));

    // }


    /**
     * Create Google event from Alpine modal
     */
    public function createEvent(Request $request)
    {
        $google = UserGoogle::where('username', auth()->user()->username)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $service = GoogleCalendarService::make($google);

        // ✅ ALL-DAY vs TIMED
        if ($request->all_day) {
            $start = Carbon::parse($request->deadline)->toDateString();
            $end   = Carbon::parse($request->deadline)->addDay()->toDateString();

            $eventData = [
                'summary' => $request->title,
                'description' => $request->description,
                'location' => $request->location,
                'start' => ['date' => $start],
                'end'   => ['date' => $end],
            ];
        } else {
            $start = Carbon::parse($request->deadline.' '.$request->start_time);
            $end   = Carbon::parse($request->deadline.' '.$request->end_time);

            $eventData = [
                'summary' => $request->title,
                'description' => $request->description,
                'location' => $request->location,
                'start' => [
                    'dateTime' => $start->toIso8601String(),
                    'timeZone' => config('app.timezone'),
                ],
                'end' => [
                    'dateTime' => $end->toIso8601String(),
                    'timeZone' => config('app.timezone'),
                ],
            ];
        }

        $created = $service->events->insert(
            'primary',
            new Google_Service_Calendar_Event($eventData)
        );

        // ✅ UPDATE EXISTING TASK (created earlier)
        Task::where('taskid', $request->taskid ?? null)
            ->orWhereNull('google_event_id')
            ->latest()
            ->update([
                'google_event_id' => $created->id,
            ]);

        return response()->json(['google_event_id' => $created->id]);
    }


}
