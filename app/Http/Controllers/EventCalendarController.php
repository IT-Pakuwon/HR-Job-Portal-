<?php

namespace App\Http\Controllers;

use App\Models\MsDepartment;
use App\Models\MsEvent;
use App\Models\MsEventLocation;
use App\Models\SysAccessRight;
use App\Models\SysCalendar;
use App\Models\SysUserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventCalendarController extends Controller
{
    public const EVENT_TYPES = [
        'Casual Leasing Exhibition' => 'Casual Leasing Exhibition',
        'Promotion Event' => 'Promotion Event',
        'Mall Sales Promotion' => 'Mall Sales Promotion',
    ];

    public const EVENT_STATUSES = [
        'Booked' => 'Booked',
        'Confirmed' => 'Confirmed',
        'Paid' => 'Paid',
    ];

    private function isAdmin(): bool
    {
        $user = auth()->user();

        return $user && $user->hasRole('ADEVENTACCESS');
    }

    /**
     * GM has the same cross-department reach as admins within their own
     * company — sees every department's locations (Promotion, Casual Leasing,
     * Loyalty) and can modify any event there, via assertCanModify() — but
     * does NOT get the front-end isAdmin flag, which is admin-only UI chrome.
     */
    private function canSeeAllDepartments(): bool
    {
        $user = auth()->user();

        return $this->isAdmin() || ($user && $user->hasRole('GMACCESS'));
    }

    /**
     * Mirrors AccessRightMiddleware's own check, so the UI (New Event button,
     * calendar cell selection) can hide actions the user's roles don't grant
     * on EVENTCAL, instead of letting them hit a 403 after the fact.
     */
    private function hasEventCalRight(string $action): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        $roleIds = SysUserRole::where('username', $user->username)
            ->where('status', 'A')
            ->pluck('role_id');

        return SysAccessRight::whereIn('role_id', $roleIds)
            ->where('screen_id', 'EVENTCAL')
            ->where('access_name', $action)
            ->where('access_right', true)
            ->where('status', 'A')
            ->exists();
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
     * Locations the current user is allowed to see/book. Everyone — admins
     * and GM included — is scoped to their own company; admins and GM
     * additionally see every department within that company, while everyone
     * else also needs a department overlap with the location's allowed list.
     */
    private function visibleLocations(): \Illuminate\Support\Collection
    {
        $query = MsEventLocation::query()
            ->whereNull('deleted_at')
            ->where('status', 'A')
            ->with('company');

        $cpnyIds = $this->userCpnyIds();

        if (!empty($cpnyIds)) {
            $query->whereIn('cpny_id', $cpnyIds);
        }

        if ($this->canSeeAllDepartments()) {
            return $query->get()->sortBy('event_location_name')->values();
        }

        $userDeptIds = $this->userDepartmentIds();

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
        $eventRoleUsernames = SysUserRole::whereIn('role_id', ['VIEWEVENTACCESS', 'EVENTACCESS', 'GMACCESS'])
            ->where('status', 'A')
            ->pluck('username');

        $users = User::query()
            ->where('status', 'A')
            ->whereIn('username', $eventRoleUsernames)
            ->orderBy('name')
            ->get(['username', 'name']);

        $departmentNames = MsDepartment::query()->pluck('department_name', 'department_id');

        return response()
            ->view('pages.event_calendar.calendar', [
                'locations' => $this->visibleLocations(),
                'eventTypes' => self::EVENT_TYPES,
                'eventStatuses' => self::EVENT_STATUSES,
                'isAdmin' => $this->isAdmin(),
                'canCreate' => $this->hasEventCalRight('CREATE'),
                'users' => $users,
                'departmentNames' => $departmentNames,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function json(Request $request)
    {
        // status = 'X' covers both a soft-deleted event (destroy() also stamps
        // deleted_at) and one flipped inactive via the Edit form's Active toggle —
        // either way it must disappear from the calendar grid.
        $query = MsEvent::query()->whereNull('deleted_at')->where('status', 'A');

        $cpnyIds = $this->userCpnyIds();

        if (!empty($cpnyIds)) {
            // Coarse SQL-level pre-filter (cpnyid alone is never ambiguous); the
            // precise (cpnyid, event_location_id) pair check happens below.
            $query->whereIn('cpnyid', $cpnyIds);
        }

        $events = $query->get();

        $locationsByKey = $this->locationsByKey();

        $visibleKeys = $this->visibleLocations()
            ->map(fn (MsEventLocation $l) => $l->cpny_id.'|'.$l->event_location_id)
            ->all();

        $events = $events->filter(
            fn (MsEvent $event) => in_array($event->cpnyid.'|'.$event->event_location_id, $visibleKeys, true)
        );

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
                    // event_total_contract / pic_event_external(_hp) are sent to everyone here —
                    // the Edit modal reuses this same payload and needs the real values to avoid
                    // silently wiping them out on save. GM-only visibility is enforced purely in
                    // the read-only View modal (see calendar.js openViewModal), not at the API layer.
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
                        'event_total_contract' => $event->event_total_contract,
                        'pic_event' => $event->pic_event,
                        'pic_event_external' => $event->pic_event_external,
                        'pic_event_external_hp' => $event->pic_event_external_hp,
                        'status' => $event->status,
                        'created_by' => $event->created_user,
                        'created_by_name' => $creatorNames->get($event->created_user, $event->created_user),
                        'created_at' => optional($event->created_at)->format('Y-m-d H:i'),
                    ],
                ];
            }),
        ]);
    }

    // National holidays only (Cuti Bersama excluded), so the timeline can tint them like weekends.
    public function holidays()
    {
        $dates = SysCalendar::query()
            ->where('status', 'A')
            ->where('date_calendar_type', 'LIBUR_NASIONAL')
            ->whereNull('deleted_at')
            ->pluck('date_calendar')
            ->map(fn ($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
            ->values();

        return response()->json(['data' => $dates]);
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
            'event_total_contract' => 'nullable|numeric',
            'pic_event' => 'nullable|string|max:255',
            'pic_event_external' => 'nullable|string|max:255',
            'pic_event_external_hp' => 'nullable|string|max:50',
            'product_check_exp' => 'nullable|date',
            'status' => 'required|in:A,X',
        ];
    }

    /**
     * A single location can host at most 5 concurrently-active events on any
     * given day. "Concurrent" means date-range overlap, not an exact date
     * match, so a long-running event counts against every day it spans.
     */
    private function assertUnderEventCap(string $cpnyId, string $eventLocationId, string $startDate, string $endDate, ?int $excludeId = null): void
    {
        $overlapping = MsEvent::query()
            ->whereNull('deleted_at')
            ->where('status', 'A')
            ->where('cpnyid', $cpnyId)
            ->where('event_location_id', $eventLocationId)
            ->where('event_start_date', '<=', $endDate)
            ->where('event_end_date', '>=', $startDate)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->count();

        abort_if($overlapping >= 5, 422, 'This location already has 5 events scheduled during the selected dates.');
    }

    private function assertLocationVisible(int $locationRowId): void
    {
        // visibleLocationIds() already covers admins (every department within
        // their own company), so no separate admin bypass is needed here.
        abort_unless(
            in_array($locationRowId, $this->visibleLocationIds(), true),
            403,
            'You are not allowed to book this location.'
        );
    }

    /**
     * Anyone may edit/delete events they created themselves. Admins and GM may
     * additionally edit/delete any event within their own assigned
     * company/companies, but never outside of it.
     */
    private function assertCanModify(MsEvent $event): void
    {
        $canModify = $event->created_user === auth()->user()->username
            || ($this->canSeeAllDepartments() && in_array($event->cpnyid, $this->userCpnyIds(), true));

        abort_unless($canModify, 403, 'You can only edit or delete events you created.');
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
