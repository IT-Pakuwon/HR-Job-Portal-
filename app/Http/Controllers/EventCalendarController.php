<?php

namespace App\Http\Controllers;

use App\Models\MsEvent;
use App\Models\MsEventLocation;
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
        'Booked' => 'Booked',
        'Confirmed' => 'Confirmed',
        'Paid' => 'Paid',
    ];

    private function isAdmin(): bool
    {
        $user = auth()->user();

        return $user && in_array('admin', $user->roles(), true);
    }

    private function userCpnyIds(): array
    {
        $user = auth()->user();

        return is_string($user->cpny_id)
            ? array_filter(array_map('trim', explode(',', $user->cpny_id)))
            : (array) $user->cpny_id;
    }

    private function userDepartmentIds(): array
    {
        $user = auth()->user();

        return is_string($user->department_id)
            ? array_filter(array_map('trim', explode(',', $user->department_id)))
            : (array) $user->department_id;
    }

    private function firstUserDepartmentId(): ?string
    {
        return $this->userDepartmentIds()[0] ?? null;
    }

    /**
     * Locations the current user is allowed to see/book.
     * Admins see every active location; everyone else is scoped to their
     * own company AND a department overlap with the location's allowed list.
     */
    private function visibleLocations(): \Illuminate\Support\Collection
    {
        $query = MsEventLocation::query()
            ->whereNull('deleted_at')
            ->where('status', 'A')
            ->with('company');

        if ($this->isAdmin()) {
            return $query->get()->sortBy('event_location_name')->values();
        }

        $cpnyIds = $this->userCpnyIds();
        $userDeptIds = $this->userDepartmentIds();

        if (!empty($cpnyIds)) {
            $query->whereIn('cpny_id', $cpnyIds);
        }

        return $query->get()
            ->filter(fn (MsEventLocation $location) => array_intersect($location->departmentIds(), $userDeptIds) !== [])
            ->sortBy('event_location_name')
            ->values();
    }

    private function visibleLocationIds(): array
    {
        return $this->visibleLocations()->pluck('id')->all();
    }

    /**
     * ms_event.event_location_id is only unique per company (the same business
     * code can exist under multiple companies), so matching events/locations
     * for the timeline must always key on the (cpnyid, event_location_id) pair,
     * never event_location_id alone.
     */
    private function locationsByKey(): \Illuminate\Support\Collection
    {
        return MsEventLocation::query()
            ->whereNull('deleted_at')
            ->with('company')
            ->get()
            ->keyBy(fn (MsEventLocation $l) => $l->cpny_id.'|'.$l->event_location_id);
    }

    public function index()
    {
        $users = User::query()
            ->where('status', 'A')
            ->orderBy('name')
            ->get(['username', 'name']);

        return response()
            ->view('pages.event_calendar.calendar', [
                'locations' => $this->visibleLocations(),
                'eventTypes' => self::EVENT_TYPES,
                'eventStatuses' => self::EVENT_STATUSES,
                'isAdmin' => $this->isAdmin(),
                'users' => $users,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function json(Request $request)
    {
        $isAdmin = $this->isAdmin();

        $query = MsEvent::query()->whereNull('deleted_at');

        if (!$isAdmin) {
            // Coarse SQL-level pre-filter (cpnyid alone is never ambiguous); the
            // precise (cpnyid, event_location_id) pair check happens below.
            $query->whereIn('cpnyid', $this->userCpnyIds());
        }

        $events = $query->get();

        $locationsByKey = $this->locationsByKey();

        if (!$isAdmin) {
            $visibleKeys = $this->visibleLocations()
                ->map(fn (MsEventLocation $l) => $l->cpny_id.'|'.$l->event_location_id)
                ->all();

            $events = $events->filter(
                fn (MsEvent $event) => in_array($event->cpnyid.'|'.$event->event_location_id, $visibleKeys, true)
            );
        }

        $creatorNames = User::query()
            ->whereIn('username', $events->pluck('created_user')->filter()->unique())
            ->pluck('name', 'username');

        return response()->json([
            'success' => true,
            'data' => $events->values()->map(function (MsEvent $event) use ($creatorNames, $locationsByKey) {
                $location = $locationsByKey->get($event->cpnyid.'|'.$event->event_location_id);

                return [
                    'id' => $event->id,
                    'resourceId' => optional($location)->id,
                    'event_id' => $event->event_id,
                    'title' => $event->event_name,
                    'start' => optional($event->event_start_date)->format('Y-m-d'),
                    'end' => optional($event->event_end_date)->addDay()->format('Y-m-d'),
                    'allDay' => true,
                    'extendedProps' => [
                        'event_id' => $event->event_id,
                        'cpnyid' => $event->cpnyid,
                        'cpny_name' => optional(optional($location)->company)->cpny_name,
                        'event_company_name' => $event->event_company_name,
                        'event_type' => $event->event_type,
                        'event_status' => $event->event_status,
                        'location_row_id' => optional($location)->id,
                        'event_location_id' => $event->event_location_id,
                        'event_start_date' => optional($event->event_start_date)->format('Y-m-d'),
                        'event_end_date' => optional($event->event_end_date)->format('Y-m-d'),
                        'event_total_area' => optional($location)->event_total_area,
                        'event_description' => $event->event_description,
                        'pic_event' => $event->pic_event,
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
            // The client submits the location's row id (globally unique), not
            // event_location_id, since that business code is only unique per
            // company and a bare string match would be ambiguous.
            'location_row_id' => 'required|integer|exists:pgsql5.ms_event_location,id',
            'event_name' => 'required|string|max:255',
            'event_company_name' => 'nullable|string|max:255',
            'event_type' => 'required|in:'.implode(',', array_keys(self::EVENT_TYPES)),
            'event_status' => 'required|in:'.implode(',', array_keys(self::EVENT_STATUSES)),
            'event_start_date' => 'required|date',
            'event_end_date' => 'required|date|after_or_equal:event_start_date',
            'event_description' => 'nullable|string',
            'pic_event' => 'nullable|string|max:255',
            'product_check_exp' => 'nullable|date',
            'status' => 'required|in:A,X',
        ];
    }

    private function assertLocationVisible(int $locationRowId): void
    {
        abort_unless(
            $this->isAdmin() || in_array($locationRowId, $this->visibleLocationIds(), true),
            403,
            'You are not allowed to book this location.'
        );
    }

    /**
     * Non-admins may only edit/delete events they created themselves.
     */
    private function assertCanModify(MsEvent $event): void
    {
        abort_unless(
            $this->isAdmin() || $event->created_user === auth()->user()->username,
            403,
            'You can only edit or delete events you created.'
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $this->assertLocationVisible((int) $data['location_row_id']);

        $location = MsEventLocation::query()->whereNull('deleted_at')->findOrFail($data['location_row_id']);
        unset($data['location_row_id']);

        $event = DB::connection('pgsql5')->transaction(function () use ($data, $location) {
            return MsEvent::create([
                'event_id' => $this->nextEventId(),
                'event_create_date' => now(),
                ...$data,
                'event_location_id' => $location->event_location_id,
                'cpnyid' => $location->cpny_id,
                'department_id' => $this->firstUserDepartmentId(),
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

        $this->assertCanModify($event);

        $data = $request->validate($this->rules());

        $this->assertLocationVisible((int) $data['location_row_id']);

        $location = MsEventLocation::query()->whereNull('deleted_at')->findOrFail($data['location_row_id']);
        unset($data['location_row_id']);

        DB::connection('pgsql5')->transaction(function () use ($data, $event, $location) {
            $event->update([
                ...$data,
                'event_location_id' => $location->event_location_id,
                'cpnyid' => $location->cpny_id,
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

        $this->assertCanModify($event);

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

        $this->assertCanModify($event);

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
