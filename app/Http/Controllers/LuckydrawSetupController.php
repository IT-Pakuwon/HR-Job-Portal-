<?php

namespace App\Http\Controllers;

use App\Models\MsLuckydrawEvent;
use App\Models\MsLuckydrawPrize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LuckydrawSetupController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $events = MsLuckydrawEvent::query()
            ->where('status', 'A')
            ->orderBy('event_name')
            ->get();

        return view('pages.spinwheel.luckydrawsetup', compact('events'));
    }

    public function eventJson(Request $request)
    {
        $data = MsLuckydrawEvent::query()
            ->where('status', '<>', 'X');

        return DataTables::of($data)

            ->addIndexColumn()

            ->addColumn('status_badge', function ($row) {
                return $row->status == 'A'
                    ? '<span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">Active</span>'
                    : '<span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-700">Inactive</span>';
            })

            ->rawColumns([
                'status_badge',
            ])

            ->make(true);
    }

    public function prizeJson(Request $request)
    {
        $data = MsLuckydrawPrize::query()

            ->where('ms_luckydraw_prize.status', '<>', 'X')

            ->leftJoin(
                'ms_luckydraw_event',
                'ms_luckydraw_event.event_id',
                '=',
                'ms_luckydraw_prize.event_id'
            )

            ->select(
                'ms_luckydraw_prize.*',
                'ms_luckydraw_event.event_name'
            );

        return DataTables::of($data)

            ->addIndexColumn()

            ->addColumn('event_name', function ($row) {
                return $row->event_name;
            })

            ->addColumn('status_badge', function ($row) {
                return $row->status == 'A'
                    ? '<span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">Active</span>'
                    : '<span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-700">Inactive</span>';
            })

            ->rawColumns([
                'status_badge',
            ])

            ->make(true);
    }

    private function nextEventId()
    {
        $last = MsLuckydrawEvent::query()
            ->where('event_id', 'like', 'EV%')
            ->orderByDesc('event_id')
            ->lockForUpdate()
            ->first();

        $next = $last ? ((int) substr($last->event_id, 2)) + 1 : 1;

        return 'EV'.str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    private function nextPrizeId()
    {
        $last = MsLuckydrawPrize::query()
            ->where('prize_id', 'like', 'PZ%')
            ->orderByDesc('prize_id')
            ->lockForUpdate()
            ->first();

        $next = $last ? ((int) substr($last->prize_id, 2)) + 1 : 1;

        return 'PZ'.str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    public function storeEvent(Request $request)
    {
        $request->validate([
            'event_name' => 'required|string|max:255',
            'event_date' => 'required|date',
            'status' => 'required|in:A,I',
        ]);

        DB::connection('pgsql5')->transaction(function () use ($request) {
            MsLuckydrawEvent::create([
                'event_id' => $this->nextEventId(),

                'event_name' => $request->event_name,

                'event_date' => $request->event_date,

                'status' => $request->status,

                'created_by' => auth()->user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Event created successfully',
        ]);
    }

    public function updateEvent(Request $request, $event_id)
    {
        $data = MsLuckydrawEvent::query()
            ->where('event_id', $event_id)
            ->firstOrFail();

        $request->validate([
            'event_name' => 'required|string|max:255',
            'event_date' => 'required|date',
            'status' => 'required|in:A,I',
        ]);

        DB::connection('pgsql5')->transaction(function () use ($request, $data) {
            $data->update([
                'event_name' => $request->event_name,

                'event_date' => $request->event_date,

                'status' => $request->status,

                'updated_by' => auth()->user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully',
        ]);
    }

    public function destroyEvent($event_id)
    {
        $data = MsLuckydrawEvent::query()
            ->where('event_id', $event_id)
            ->firstOrFail();

        $data->update([
            'status' => 'X',
            'deleted_by' => auth()->user()->username,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully',
        ]);
    }

    public function storePrize(Request $request)
    {
        $request->validate([
            'event_id' => 'required|string|max:20',
            'prize_name' => 'required|string|max:255',
            'status' => 'required|in:A,I',
        ]);

        DB::connection('pgsql5')->transaction(function () use ($request) {
            MsLuckydrawPrize::create([
                'prize_id' => $this->nextPrizeId(),

                'event_id' => $request->event_id,

                'prize_name' => $request->prize_name,

                'status' => $request->status,

                'created_by' => auth()->user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Prize created successfully',
        ]);
    }

    public function updatePrize(Request $request, $prize_id)
    {
        $data = MsLuckydrawPrize::query()
            ->where('prize_id', $prize_id)
            ->firstOrFail();

        $request->validate([
            'event_id' => 'required|string|max:20',
            'prize_name' => 'required|string|max:255',
            'status' => 'required|in:A,I',
        ]);

        DB::connection('pgsql5')->transaction(function () use ($request, $data) {
            $data->update([
                'event_id' => $request->event_id,

                'prize_name' => $request->prize_name,

                'status' => $request->status,

                'updated_by' => auth()->user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Prize updated successfully',
        ]);
    }

    public function destroyPrize($prize_id)
    {
        $data = MsLuckydrawPrize::query()
            ->where('prize_id', $prize_id)
            ->firstOrFail();

        $data->update([
            'status' => 'X',
            'deleted_by' => auth()->user()->username,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Prize deleted successfully',
        ]);
    }
}
