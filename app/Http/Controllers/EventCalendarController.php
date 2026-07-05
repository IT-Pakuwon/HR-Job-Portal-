<?php

namespace App\Http\Controllers;

use App\Models\MsCompany;
use App\Models\MsEvent;
use App\Models\MsLocation;
use App\Models\MsSubLocation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventCalendarController extends Controller
{
    public const EVENT_TYPES = [
        'Casual Leasing' => 'Casual Leasing',
        'Promotion Event' => 'Promotion Event',
        'Operation/Internal Event' => 'Operation/Internal Event',
    ];

    public const EVENT_STATUSES = [
        'Final Event' => 'Final Event',
        'Tentative Event' => 'Tentative Event',
    ];

    public function index()
    {
        $companies = MsCompany::query()
            ->where('status', 'A')
            ->orderBy('cpny_name')
            ->get(['cpny_id', 'cpny_name']);

        $locations = MsLocation::query()
            ->where('status', 'A')
            ->orderBy('location_name')
            ->get(['cpny_id', 'location_id', 'location_name']);

        $subLocations = MsSubLocation::query()
            ->where('status', 'A')
            ->orderBy('sub_location_name')
            ->get(['cpny_id', 'sub_location_id', 'location_id', 'sub_location_name']);

        return view('pages.event_calendar.calendar', [
            'companies' => $companies,
            'locations' => $locations,
            'subLocations' => $subLocations,
            'eventTypes' => self::EVENT_TYPES,
            'eventStatuses' => self::EVENT_STATUSES,
        ]);
    }

    public function json(Request $request)
    {
        $user = auth()->user();

        $cpnyIds = is_string($user->cpny_id)
            ? array_filter(array_map('trim', explode(',', $user->cpny_id)))
            : (array) $user->cpny_id;

        $query = MsEvent::query()->whereNull('deleted_at');

        if (!empty($cpnyIds)) {
            $query->whereIn('cpnyid', $cpnyIds);
        }

        $events = $query->with(['location', 'subLocation'])->get();

        $creatorNames = User::query()
            ->whereIn('username', $events->pluck('created_user')->filter()->unique())
            ->pluck('name', 'username');

        return response()->json([
            'success' => true,
            'data' => $events->map(function (MsEvent $event) use ($creatorNames) {
                return [
                    'id' => $event->id,
                    'event_id' => $event->event_id,
                    'title' => $event->event_name,
                    'start' => optional($event->event_start_date)->format('Y-m-d'),
                    'end' => optional($event->event_end_date)->addDay()->format('Y-m-d'),
                    'allDay' => true,
                    'extendedProps' => [
                        'event_id' => $event->event_id,
                        'cpnyid' => $event->cpnyid,
                        'event_company_name' => $event->event_company_name,
                        'event_type' => $event->event_type,
                        'event_status' => $event->event_status,
                        'location_id' => $event->location_id,
                        'location_name' => optional($event->location)->location_name,
                        'sub_location_id' => $event->sub_location_id,
                        'sub_location_name' => optional($event->subLocation)->sub_location_name,
                        'event_start_date' => optional($event->event_start_date)->format('Y-m-d'),
                        'event_end_date' => optional($event->event_end_date)->format('Y-m-d'),
                        'event_total_area' => $event->event_total_area,
                        'event_description' => $event->event_description,
                        'product_check_exp' => $event->product_check_exp,
                        'status' => $event->status,
                        'created_by' => $event->created_user,
                        'created_by_name' => $creatorNames->get($event->created_user, $event->created_user),
                    ],
                ];
            }),
        ]);
    }

    private function nextEventId(): string
    {
        $last = MsEvent::query()
            ->where('event_id', 'like', 'EVT%')
            ->orderByDesc('event_id')
            ->lockForUpdate()
            ->first();

        $next = $last ? ((int) substr($last->event_id, 3)) + 1 : 1;

        return 'EVT'.str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    private function rules(): array
    {
        return [
            'cpnyid' => 'required|string|exists:pgsql2.ms_company,cpny_id',
            'location_id' => 'required|string|exists:pgsql.ms_location,location_id',
            'sub_location_id' => 'nullable|string',
            'event_name' => 'required|string|max:255',
            'event_company_name' => 'nullable|string|max:255',
            'event_type' => 'required|in:'.implode(',', array_keys(self::EVENT_TYPES)),
            'event_status' => 'required|in:'.implode(',', array_keys(self::EVENT_STATUSES)),
            'event_start_date' => 'required|date',
            'event_end_date' => 'required|date|after_or_equal:event_start_date',
            'event_total_area' => 'nullable|numeric',
            'event_description' => 'nullable|string',
            'product_check_exp' => 'nullable|date',
            'status' => 'required|in:A,X',
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $event = DB::connection('pgsql5')->transaction(function () use ($data) {
            return MsEvent::create([
                'event_id' => $this->nextEventId(),
                'event_create_date' => now(),
                ...$data,
                'created_user' => auth()->user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Event created successfully',
            'data' => $event,
        ]);
    }

    public function update(Request $request, $id)
    {
        $event = MsEvent::query()->whereNull('deleted_at')->findOrFail($id);

        $data = $request->validate($this->rules());

        DB::connection('pgsql5')->transaction(function () use ($data, $event) {
            $event->update([
                ...$data,
                'updated_user' => auth()->user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully',
            'data' => $event,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:A,X',
        ]);

        $event = MsEvent::query()->whereNull('deleted_at')->findOrFail($id);

        $event->update([
            'status' => $request->status,
            'updated_user' => auth()->user()->username,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Event status updated successfully',
        ]);
    }

    public function destroy($id)
    {
        $event = MsEvent::query()->whereNull('deleted_at')->findOrFail($id);

        $event->update([
            'status' => 'X',
            'deleted_by' => auth()->user()->username,
            'deleted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully',
        ]);
    }
}
