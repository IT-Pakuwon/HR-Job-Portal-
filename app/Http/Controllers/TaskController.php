<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function move(Request $request, $id)
    {
        Task::where('id', $id)->update([
            'start_date' => Carbon::parse($request->start),
            'end_date'   => Carbon::parse($request->end),
            'updated_by' => auth()->user()->username,
        ]);

        // 🔥 later: sync update to Google here if google_event_id exists

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
            'status' => 'pending',
            'created_by' => auth()->user()->username,
            'updated_by' => auth()->user()->username,
        ]);

        return response()->json($task);
    }

    
}
