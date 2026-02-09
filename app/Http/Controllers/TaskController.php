<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\UserGoogle;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function move(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $task->update([
            'start_date' => Carbon::parse($request->start),
            'end_date'   => Carbon::parse($request->end),
            'updated_by' => auth()->user()->username,
        ]);

        // 🔁 SYNC TO GOOGLE
        if ($task->google_event_id) {
            $google = UserGoogle::where('username', auth()->user()->username)
                ->whereNull('deleted_at')
                ->first();

            if ($google) {
                try {
                    $service = GoogleCalendarService::make($google);

                    $event = $service->events->get('primary', $task->google_event_id);

                    $event->setStart([
                        'dateTime' => $task->start_date->toIso8601String(),
                        'timeZone' => config('app.timezone'),
                    ]);

                    $event->setEnd([
                        'dateTime' => $task->end_date->toIso8601String(),
                        'timeZone' => config('app.timezone'),
                    ]);

                    $service->events->update('primary', $event->getId(), $event);
                } catch (\Throwable $e) {
                    logger()->error('Google update failed', ['e' => $e->getMessage()]);
                }
            }
        }

        return response()->json(['ok' => true]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'deadline' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'location' => 'nullable|string',
            'description' => 'nullable|string',
            'sync_to_google' => 'boolean',
        ]);

        /**
         * ✅ ALWAYS produce valid datetime
         */
        if ($request->start_time) {
            $startDate = Carbon::parse($request->deadline.' '.$request->start_time);

            $endDate = $request->end_time
                ? Carbon::parse($request->deadline.' '.$request->end_time)
                : $startDate->copy()->addHour();
        } else {
            // fallback: all-day-ish task
            $startDate = Carbon::parse($request->deadline)->startOfDay();
            $endDate   = $startDate->copy()->addHour();
        }

        $task = Task::create([
            'taskid' => uniqid('TASK'),
            'taskdate' => $startDate->toDateString(),
            'task_title' => $request->title,
            'start_date' => $startDate,   // ✅ datetime
            'end_date' => $endDate,       // ✅ datetime
            'task_description' => $request->description,
            'task_location' => $request->location,
            'sync_to_google' => $request->sync_to_google ?? false,
            'status' => Task::STATUS_MAP['PENDING'],
            'created_by' => auth()->user()->username,
            'updated_by' => auth()->user()->username,
        ]);

        return response()->json($task);
    }

public function destroy($id)
{
    $task = Task::findOrFail($id);

    // 🔒 SECURITY: only owner can delete
    abort_if(
        $task->created_by !== auth()->user()->username,
        403,
        'You are not allowed to delete this task'
    );

    // 🧹 Delete from Google if linked
    if ($task->sync_to_google && $task->google_event_id) {
        $google = UserGoogle::where('username', auth()->user()->username)
            ->whereNull('deleted_at')
            ->first();

        if ($google) {
            try {
                $service = GoogleCalendarService::make($google);
                $service->events->delete('primary', $task->google_event_id);
            } catch (\Throwable $e) {
                // Do NOT block DB delete
                logger()->warning('Google delete failed', [
                    'task_id' => $task->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    // 🗑 Soft delete local DB
    $task->delete();

    return response()->json(['success' => true]);
}



    
}
