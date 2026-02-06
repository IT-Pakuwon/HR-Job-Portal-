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
            $task = Task::updateOrCreate(
                ['google_event_id' => $event->id],
                [
                    'taskdate'       => Carbon::parse($startTime)->toDateString(),
                    'task_title'     => $event->summary ?? '(No title)',
                    'start_date'     => $event->start->dateTime ? Carbon::parse($startTime) : null,
                    'end_date'       => $event->end->dateTime ? Carbon::parse($endTime) : null,
                    'task_location'  => $event->location,
                    'sync_to_google' => true,

                    // ✅ FIX HERE
                    'status'         => Task::STATUS_MAP['PENDING'],
                    'updated_by'     => auth()->id(),
                ]
            );


            if ($task->wasRecentlyCreated) {
                $task->created_by = auth()->id();
                $task->save();
            }


            $result[] = [
                'id'    => $task->id,
                'title' => $task->task_title,

                'start' => $task->start_date
                    ? $task->start_date->toIso8601String()
                    : Carbon::parse($task->taskdate)->toIso8601String(),

                'end'   => $task->end_date
                    ? $task->end_date->toIso8601String()
                    : null,

                // ✅ THIS IS THE LINE YOU ASKED ABOUT
                'allDay' => $task->start_date === null,

                'description' => $task->task_description,
                'location'    => $task->task_location,
                'fromGoogle'  => true,
            ];


        }

        return response()->json(collect($events)->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->summary ?? '(No title)',

                // ✅ THIS IS THE KEY FIX
                'start' => $event->start->dateTime ?? $event->start->date,
                'end'   => $event->end->dateTime ?? $event->end->date,

                'allDay' => isset($event->start->date),
                'fromGoogle' => true,
            ];
        }));

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
            'description' => $request->description,
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

        $task = Task::create([
            'taskid'           => uniqid('TASK'),
            'taskdate'         => $start->toDateString(),
            'task_title'       => $request->title,
            'task_description' => $request->description,
            'start_date'       => $start,
            'end_date'         => $end,
            'task_location'    => $request->location,
            'sync_to_google'   => true,
            'google_event_id'  => $created->id,

            // ✅ FIX HERE
            'status'           => Task::STATUS_MAP['PENDING'],
            'created_by'       => auth()->id(),
            'updated_by'       => auth()->id(),
        ]);


        return response()->json([
            'task_id' => $task->id,
            'google_event_id' => $created->id,
        ]);
    }

}
