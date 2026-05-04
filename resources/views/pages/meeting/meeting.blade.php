<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-4">

        {{-- HEADER --}}
        <div
            class="mb-4 rounded-2xl border border-gray-200 bg-white/70 p-5 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">

            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                {{-- LEFT --}}
                <div class="flex items-center gap-3">

                    {{-- ICON --}}
                    <div
                        class="{{ request()->is('meeting')
                            ? 'bg-blue-100 text-blue-600 dark:bg-blue-500/10'
                            : 'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/10' }} flex h-10 w-10 items-center justify-center rounded-xl">

                        @if (request()->is('meeting'))
                            <!-- calendar -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        @else
                            <!-- video -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 6h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z" />
                            </svg>
                        @endif
                    </div>

                    {{-- TITLE --}}
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ request()->is('meeting') ? 'Meeting Calendar' : 'Booking Teams / Zoom' }}
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ request()->is('meeting')
                                ? 'Manage and schedule meetings efficiently'
                                : 'Schedule and manage your online meetings' }}
                        </p>
                    </div>

                </div>

                {{-- RIGHT (TABS) --}}
                <div class="flex items-center gap-1 rounded-xl bg-gray-100/80 p-1 dark:bg-white/5">

                    <a href="{{ url('/meeting') }}"
                        class="{{ request()->is('meeting')
                            ? 'bg-white text-gray-900 shadow-sm dark:bg-white/10 dark:text-white'
                            : 'text-gray-600 hover:bg-white/60 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10' }} flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition">

                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8 7V3m8 4V3m-9 8h10" />
                        </svg>

                        Calendar
                    </a>

                    <a href="{{ url('/meetingteams') }}"
                        class="{{ request()->is('meetingteams')
                            ? 'bg-white text-gray-900 shadow-sm dark:bg-white/10 dark:text-white'
                            : 'text-gray-600 hover:bg-white/60 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10' }} flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition">

                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 6h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z" />
                        </svg>

                        Teams / Zoom
                    </a>

                      @if (auth()->check() && auth()->user()->user_role === 'admin')

                        <a href="{{ route('meetingroom.setup.index') }}"
                            class="{{ request()->is('meetingroom/setup*')
                                ? 'bg-white text-gray-900 shadow-sm dark:bg-white/10 dark:text-white'
                                : 'text-gray-600 hover:bg-white/60 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10' }} flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition">

                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3 7h18M5 7v10a2 2 0 002 2h10a2 2 0 002-2V7M9 11h6M9 15h3" />

                            </svg>

                            Setup

                        </a>

                    @endif


                </div>

            </div>
        </div>
        {{-- CALENDAR --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div id="calendar"></div>
        </div>


        <!-- 🔥 CREATE MODAL -->
        <div id="schedule-show" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/30 p-4">

            <div class="flex max-h-[90vh] w-full max-w-3xl flex-col rounded-2xl bg-white shadow-xl dark:bg-gray-900">
                <div class="space-y-6 overflow-y-auto px-6 py-5">
                    {{-- HEADER --}}
                    <div
                        class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Create Meeting
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Schedule a new meeting session
                            </p>
                        </div>

                        <button type="button" id="closeScheduleModal"
                            class="rounded-md p-2 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800">
                            ✕
                        </button>
                    </div>

                    {{-- FORM --}}
                    <form id="meetingForm" action="{{ url('/savemeeting') }}" method="post">
                        @csrf

                        <div class="space-y-6 px-6 py-5">

                            {{-- SECTION: BASIC --}}
                            <div class="space-y-4">

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="req text-xs text-gray-500">Start</label>
                                            <input type="text" id="start_datetime" name="start_datetime"
                                                class="mt-1 w-full rounded-md border px-3 py-2 text-sm" required>
                                        </div>

                                        <div>
                                            <label class="req text-xs text-gray-500">End</label>
                                            <input type="text" id="end_datetime" name="end_datetime"
                                                class="mt-1 w-full rounded-md border px-3 py-2 text-sm" required>
                                        </div>

                                    </div>

                                    {{-- ROOM --}}
                                    <div>
                                        <label class="req text-xs font-medium text-gray-500 dark:text-gray-400">
                                            Room
                                        </label>
                                        <select id="room_id" name="room_id"
                                            class="mt-1 w-full rounded-md border px-3 py-2 text-sm" required>

                                            @foreach ($rooms as $room)
                                                <option value="{{ $room->room_id }}">
                                                    {{ $room->room_name }}
                                                </option>
                                            @endforeach

                                        </select>

                                    </div>

                                </div>

                                {{-- TITLE --}}
                                <div>
                                    <label class="req text-xs font-medium text-gray-500 dark:text-gray-400">
                                        Title
                                    </label>
                                    <input type="text" id="title" name="title" required
                                        placeholder="Meeting title..."
                                        class="mt-1 w-full rounded-md border border-gray-200 bg-transparent px-3 py-2 text-sm focus:ring-1 focus:ring-gray-300 dark:border-gray-700">
                                </div>

                                {{-- DESCRIPTION --}}
                                <div>
                                    <label class="req text-xs font-medium text-gray-500 dark:text-gray-400">
                                        Description
                                    </label>
                                    <textarea id="descr" name="descr" rows="3" required placeholder="Write a short description..."
                                        class="mt-1 w-full rounded-md border border-gray-200 bg-transparent px-3 py-2 text-sm focus:ring-1 focus:ring-gray-300 dark:border-gray-700"></textarea>
                                </div>

                            </div>

                            {{-- SECTION: DETAILS --}}
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                                {{-- ACCESSORIES --}}
                                <div>
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                        Accessories
                                    </label>
                                    <select id="acc_id" name="acc_id[]" multiple
                                        class="meeting-multi mt-1 w-full rounded-md border border-gray-200 bg-transparent px-3 py-2 text-sm dark:border-gray-700">
                                    </select>
                                </div>

                                {{-- PARTICIPANT --}}
                                <div>
                                    <label class="req text-xs font-medium text-gray-500 dark:text-gray-400">
                                        Participants
                                    </label>
                                    <input type="number" id="participant" name="participant" min="1" required
                                        placeholder="Number of participants"
                                        class="mt-1 w-full rounded-md border border-gray-200 bg-transparent px-3 py-2 text-sm dark:border-gray-700">
                                </div>

                            </div>

                            {{-- INTERNAL PARTICIPANT --}}
                            <div id="internalSection">
                                <div>
                                </div>
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div class='flex-1'>
                                        <label class="req text-xs font-medium text-gray-500">
                                            Internal PIC Name
                                        </label>
                                        <input type="text" id="internal_pic" name="internal_pic"
                                            value="{{ auth()->user()->name }}" readonly
                                            class="mt-1 w-full rounded-md border bg-gray-100 px-3 py-2 text-sm"
                                            required>
                                    </div>
                                    <div class="flex-1">
                                        <label class="req mt-3 block text-xs font-medium text-gray-500">
                                            Email To
                                        </label>
                                        <select id="username" name="username[]" multiple>
                                            @foreach ($users as $u)
                                                <option value="{{ $u->name }}|{{ $u->meeting_email }}"
                                                    data-email="{{ $u->meeting_email }}"
                                                    data-name="{{ $u->name }}">
                                                    {{ $u->name }} ({{ $u->meeting_email }})
                                                </option>
                                            @endforeach
                                        </select required>
                                    </div>
                                </div>

                            </div>
                            {{-- EXTERNAL TOGGLE --}}
                            <div class="flex items-center gap-2">
                                <input type="checkbox" id="is_external_participant" name="external_participant"
                                    value="1" class="h-4 w-4 rounded border-gray-300">
                                <label class="text-sm text-gray-600 dark:text-gray-300">
                                    External Participant
                                </label>
                            </div>

                            {{-- EXTERNAL SECTION --}}
                            <div id="externalParticipantSection" class="hidden space-y-4">

                                <table class="w-full overflow-hidden rounded-lg border text-sm">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="p-2 text-left">Name</th>
                                            <th class="p-2 text-left">Email</th>
                                            <th class="p-2 text-left">Company</th>
                                            <th class="w-10 p-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="externalTableBody"></tbody>
                                </table>

                                <button type="button" onclick="addExternalRow()"
                                    class="rounded bg-gray-900 px-3 py-1 text-xs text-white">
                                    + Add Participant
                                </button>

                            </div>

                        </div>

                        {{-- FOOTER --}}
                        <div
                            class="flex items-center justify-between border-t border-gray-200 px-6 py-4 dark:border-gray-700">

                            <button type="button" id="cancelScheduleModal"
                                class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-white">
                                Cancel
                            </button>

                            <button type="button" id="submitBtn"
                                class="inline-flex items-center gap-2 rounded-md bg-gray-900 px-4 py-2 text-sm text-white transition hover:bg-black disabled:opacity-60">

                                <svg id="loadingSpinner" class="hidden h-4 w-4 animate-spin"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4" class="opacity-25" />
                                    <path fill="currentColor" class="opacity-75"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                </svg>

                                <span id="submitText">Create Meeting</span>
                            </button>

                        </div>

                    </form>
                </div>
            </div>
        </div>


        <!-- 🔥 VIEW / EDIT MODAL -->
        <div id="event-show"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4 backdrop-blur-sm">

            <div class="w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <!-- HEADER -->
                <div class="flex items-center justify-between border-b bg-gray-50 px-6 py-4 dark:bg-gray-800">
                    <div class="flex items-center gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Meeting Detail
                            </h2>
                            <p class="text-xs text-gray-500">Full meeting information</p>
                        </div>

                        <!-- STATUS BADGE -->
                        <span id="view_status" class="rounded bg-green-100 px-2 py-1 text-xs text-green-600">
                            Active
                        </span>
                    </div>

                    <button id="closeEventModal"
                        class="text-lg text-gray-400 hover:text-gray-700 dark:hover:text-white">
                        ✕
                    </button>
                </div>

                <!-- CONTENT -->
                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-5">

                        <!-- LEFT -->
                        <div class="space-y-5 md:col-span-3">

                            <div>
                                <p class="text-[11px] uppercase text-gray-400">Title</p>
                                <p id="view_title" class="text-lg font-semibold"></p>
                            </div>

                            <div class="grid grid-cols-2 gap-4 text-sm">

                                <div>
                                    <p class="text-[11px] uppercase text-gray-400">Time</p>
                                    <p id="view_time"></p>
                                </div>

                                <div>
                                    <p class="text-[11px] uppercase text-gray-400">Room</p>
                                    <p id="view_room"></p>
                                </div>

                                <div>
                                    <p class="text-[11px] uppercase text-gray-400">PIC</p>
                                    <p id="view_pic"></p>
                                </div>

                                <div>
                                    <p class="text-[11px] uppercase text-gray-400">Type</p>
                                    <p id="view_type"></p>
                                </div>

                                <div>
                                    <p class="text-[11px] uppercase text-gray-400">Participants</p>
                                    <p id="view_count"></p>
                                </div>

                                <div>
                                    <p class="text-[11px] uppercase text-gray-400">Teams</p>
                                    <p id="view_teams"></p>
                                </div>

                            </div>

                            <div>
                                <p class="text-[11px] uppercase text-gray-400">Description</p>
                                <p id="view_descr"
                                    class="whitespace-pre-line text-[11px] text-gray-700 dark:text-gray-300">
                                </p>
                            </div>

                        </div>

                        <!-- RIGHT -->
                        <div class="md:col-span-2">
                            <div class="flex h-full flex-col rounded-xl border bg-gray-50 p-4 dark:bg-gray-800">

                                <div class="mb-3 flex justify-between">
                                    <p class="text-sm font-semibold">Participants</p>
                                    <span id="view_count_badge" class="rounded bg-gray-200 px-2 py-1 text-xs"></span>
                                </div>

                                <div id="view_participants" class="flex-1 space-y-2 overflow-y-auto pr-1"></div>

                            </div>
                        </div>

                    </div>
                </div>


                <!-- TEAMS LINK BAR -->
                <div id="teamsBar" class="flex hidden items-center justify-between border-t bg-blue-50 px-6 py-3">

                    <span class="text-sm font-medium text-blue-700">
                        💬 Microsoft Teams Meeting
                    </span>

                    <div class="flex items-center gap-2">

                        <!-- COPY BUTTON -->
                        <button id="copyTeamsBtn"
                            class="rounded border border-blue-300 px-3 py-1.5 text-sm text-blue-700 hover:bg-blue-100">
                            Copy Link
                        </button>

                        <!-- JOIN BUTTON -->
                        <a id="teamsLink" href="#" target="_blank"
                            class="rounded bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700">
                            Join
                        </a>

                    </div>
                </div>

                <!-- FOOTER -->
                <div class="flex items-center justify-between border-t bg-gray-50 px-6 py-4">

                    <button id="closeEventModal2"
                        class="rounded-md border px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">
                        Close
                    </button>

                    <div class="flex gap-2">
                        <button id="editMeetingBtn"
                            class="hidden rounded-md bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
                            Edit
                        </button>

                        <button id="cancelMeetingBtn"
                            class="hidden rounded-md bg-red-500 px-4 py-2 text-sm text-white hover:bg-red-600">
                            Cancel
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>
</x-app-layout>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.20/index.global.min.css">

<script src="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.20/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/themes/light-border.css" />
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>

<script>
    window.minBookingDate = @json(now()->startOfDay()->format('Y-m-d'));
    window.maxBookingDate = @json($maxBookingDate->format('Y-m-d'));
    window.currentUserId = @json(auth()->user()->username);
    window.hasCSACCESS = @json($hasCsAccess ?? false);
    window.editMeetingId = null;
    window.isEditMode = false;
    window.currentUsername = @json(auth()->user()->name);
    document.addEventListener('DOMContentLoaded', function() {
        const RESTRICTED_ROOMS = [
            'Meeting Room 33-1',
            'Meeting Room 33-5',
            'Meeting Room 1 P6 - Mall Gandaria'
        ];

        window.endPicker = flatpickr("#end_datetime", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today",
            time_24hr: true

        });

        window.startPicker = flatpickr("#start_datetime", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true,
            minDate: "today",
            onChange: function(selectedDates) {
                if (selectedDates.length) {
                    const end = new Date(selectedDates[0]);
                    end.setHours(end.getHours() + 1);
                    window.endPicker.setDate(end);
                }
            }
        });

        const calendarEl = document.getElementById('calendar');
        const isMobile = window.innerWidth < 768;

        window.calendar = new FullCalendar.Calendar(calendarEl, {
            schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',

            initialView: isMobile ? 'timeGridDay' : 'resourceTimelineDay',

            resourceOrder: function(a, b) {
                return parseInt(a.id) - parseInt(b.id);
            },

            height: isMobile ? 'auto' : '75vh',
            contentHeight: 'auto',
            expandRows: false, // 🔥 IMPORTANT

            selectable: true,
            selectMirror: true,
            selectOverlap: false,

            // // 🔥 TIMELINE FIX
            // interaction: true,
            longPressDelay: 0,
            eventLongPressDelay: 0,
            selectLongPressDelay: 0,

            slotMinWidth: 30,

            headerToolbar: {
                left: 'today prev,next',
                center: 'title',
                right: isMobile ?
                    'timeGridDay,timeGridWeek,listWeek' :
                    'resourceTimelineDay,timeGridWeek,dayGridMonth,listWeek'
            },

            resources: isMobile ? [] : [
                @foreach ($rooms as $room)
                    {
                        id: @json($room->room_id),
                        title: @json($room->room_name),
                    },
                @endforeach
            ],


            events: '/calendar-json',
            // eventContent: function(arg) {
            //     const title = arg.event.title;
            //     const user = arg.event.extendedProps.user;
            //     const room = arg.event.extendedProps.room;
            //     const type = arg.event.extendedProps.type;
            //     const isTeams = arg.event.extendedProps.isTeams;

            //     return {
            //         html: `
            //         <div class="fc-event-custom">

            //             <div class="fc-event-title">
            //                 ${user ? user + ' - ' : ''}${title}
            //             </div>

            //             <div class="fc-event-room">
            //                 📍 ${room || '-'}
            //             </div>

            //             <div class="fc-event-meta">
            //                 ${type === 'external' ? 'External' : 'Internal'} •
            //                 ${isTeams ? 'Teams' : 'No Teams'}
            //             </div>

            //         </div>
            //     `
            //     };
            // },

            eventContent: function(arg) {
                const title = arg.event.title;
                const user = arg.event.extendedProps.user;
                const room = arg.event.extendedProps.room;
                const type = arg.event.extendedProps.type;
                const isTeams = arg.event.extendedProps.isTeams;

                const viewType = arg.view.type;

                // =========================
                // ✅ WEEK & MONTH (MINIMAL)
                // =========================
                if (viewType === 'timeGridWeek') {
                    return {
                        html: `
                        <div class="px-1 leading-tight">

                            <div class="font-medium text-[11px] whitespace-normal break-words">
                                ${title}
                            </div>

                            <div class="text-[10px] opacity-80 whitespace-normal break-words">
                                ${user || ''}
                            </div>

                        </div>
                    `
                    };
                }

                // =========================
                // ✅ TIMELINE / DAY (FULL)
                // =========================
                return {
                    html: `
                <div class="fc-event-custom">

                    <div class="fc-event-title">
                        ${user ? user + ' - ' : ''}${title}
                    </div>

                    <div class="fc-event-room">
                        📍 ${room || '-'}
                    </div>

                    <div class="fc-event-meta">
                        ${type === 'external' ? 'External' : 'Internal'} •
                        ${isTeams ? 'Teams' : 'No Teams'}
                    </div>

                </div>
                `
                };
            },

            eventDidMount: function(info) {

                const p = info.event.extendedProps;

                const start = moment(info.event.start).format('DD MMM YYYY HH:mm');
                const end = info.event.end ?
                    moment(info.event.end).format('HH:mm') :
                    '-';

                let status = '';

                if (p.status === 'X') {
                    status = '❌ Cancelled';
                } else if (!p.teams_url) {
                    status = '🎥 No Teams/Zoom';
                } else {
                    status = p.isTeams ? '💬 Teams Ready' : '🎥 Zoom Ready';
                }

                const html = `
                <div class="text-xs space-y-2">

                    <div class="font-semibold text-gray-900">
                        ${info.event.title}
                    </div>

                    <div class="text-gray-500">
                        ${start} → ${end}
                    </div>

                    <div>📍 ${p.room || '-'}</div>
                    <div>👤 ${p.user || '-'}</div>

                    <div class="text-[11px] px-2 py-1 rounded bg-gray-100 inline-block">
                        ${status}
                    </div>

                </div>
            `;

                tippy(info.el, {
                    content: html,
                    allowHTML: true,
                    theme: 'light-border',
                    placement: 'top',
                    animation: 'scale',
                });
            },

            eventClick: function(info) {

                info.jsEvent.preventDefault();
                document.activeElement?.blur();

                const e = info.event;
                window.selectedEvent = e;
                const props = e.extendedProps;

                // ✅ DEFINE FIRST (VERY IMPORTANT)
                const editBtn = document.getElementById('editMeetingBtn');
                const cancelBtn = document.getElementById('cancelMeetingBtn');

                const currentUserId = window.currentUserId;
                const creatorId = props.username; // ✅ FIXED FIELD

                // ✅ SAFETY CHECK (avoid null crash)
                if (!editBtn || !cancelBtn) return;

                if (creatorId === currentUserId) {
                    editBtn.classList.remove('hidden');
                    cancelBtn.classList.remove('hidden');
                } else {
                    editBtn.classList.add('hidden');
                    cancelBtn.classList.add('hidden');
                }
                // 🔥 BASIC INFO FILL
                document.getElementById('view_title').innerText = e.title || '-';

                document.getElementById('view_time').innerText =
                    moment(e.start).format('DD MMM YYYY HH:mm') + ' - ' +
                    (e.end ? moment(e.end).format('HH:mm') : '-');

                document.getElementById('view_room').innerText = props.room || '-';

                document.getElementById('view_pic').innerText = props.user || '-';

                document.getElementById('view_type').innerText =
                    props.type === 'external' ? 'External' : 'Internal';

                document.getElementById('view_count').innerText =
                    props.participant_count || participantsList.length || 0;

                document.getElementById('view_count_badge').innerText =
                    props.participant_count || participantsList.length || 0;

                // =========================
                // DETECT TYPE (IMPORTANT)
                // =========================
                const isExternal = props.type === 'external';
                let participantsList = props.participants;

                if (typeof participantsList === 'string') {
                    try {
                        participantsList = JSON.parse(participantsList);
                    } catch {
                        participantsList = [];
                    }
                }

                if (!Array.isArray(participantsList)) {
                    participantsList = [];
                }

                const merged = participantsList.map(p => {

                    if (typeof p === 'object') {
                        return {
                            name: p.name || '',
                            email: p.email || '',
                            company: p.company || '',
                            type: p.type || (isExternal ? 'external' : 'internal')
                        };
                    }

                    return {
                        name: p,
                        type: isExternal ? 'external' : 'internal'
                    };
                });

                const totalParticipants = merged.length;

                document.getElementById('view_count').innerText =
                    totalParticipants;

                document.getElementById('view_count_badge').innerText =
                    `${totalParticipants} Participants`;

                // 🔥 SPLIT INTERNAL / EXTERNAL
                const internal = participantsList.filter(p => p.type === 'internal');
                const external = participantsList.filter(p => p.type === 'external');

                // ==========================
                // INTERNAL (TomSelect)
                // ==========================
                if (window.userTom) {
                    window.userTom.clear(true);

                    internal.forEach(p => {
                        const match = Object.keys(window.userTom.options).find(v => {
                            return window.userTom.options[v].$option.dataset
                                .email === p.email;
                        });

                        if (match) {
                            window.userTom.addItem(match, true);
                        }
                    });

                    document.getElementById('internal_pic').value =
                        props.internal_pic || "{{ auth()->user()->name }}";
                }

                // ==========================
                // EXTERNAL (TABLE)
                // ==========================
                const toggle = document.getElementById('is_external_participant');
                const externalSection = document.getElementById('externalParticipantSection');
                const tbody = document.getElementById('externalTableBody');

                // RESET FIRST
                toggle.checked = false;
                externalSection.classList.add('hidden');
                tbody.innerHTML = '';

                // IF HAVE EXTERNAL
                if (external.length > 0) {
                    toggle.checked = true;
                    externalSection.classList.remove('hidden');

                    external.forEach(p => {
                        addExternalRow(p.name, p.email, p.company);
                    });
                }

                // document.getElementById('view_teams').innerText =
                //     props.isTeams ? 'Available (Teams)' : 'Not Available';
                const teamsBar = document.getElementById('teamsBar');
                const teamsLink = document.getElementById('teamsLink');
                const teamsText = document.getElementById('view_teams');

                // reset
                teamsBar.classList.add('hidden');
                teamsLink.href = '#';

                // ✅ PRIORITY 1: TEAMS LINK
                if (props.isTeams && props.teams_url) {

                    teamsBar.classList.remove('hidden');
                    teamsLink.href = props.teams_url;

                    teamsText.innerHTML = `
                        <span class="flex items-center gap-2 text-sm text-blue-700 font-medium">
                            💬 Microsoft Teams Meeting
                        </span>
                    `;

                    // ✅ PRIORITY 2: ACCESSORIES NAME
                } else if (props.accessories && props.accessories.length) {

                    teamsText.innerHTML = `
                        <span class="flex items-center gap-2 text-sm text-gray-700 font-medium">
                            🎧 ${props.accessories.join(', ')}
                        </span>
                    `;

                    // ❌ NONE
                } else {
                    teamsText.innerText = 'Not Available';
                }

                document.getElementById('view_descr').innerText =
                    props.description || '-';

                document.getElementById('view_status').innerText =
                    props.status === 'X' ? 'Cancelled' : 'Active';

                document.getElementById('view_status').className =
                    props.status === 'X' ?
                    'text-xs px-2 py-1 rounded bg-red-100 text-red-600' :
                    'text-xs px-2 py-1 rounded bg-green-100 text-green-600';

                // =========================
                // RIGHT SIDE (PARTICIPANTS)
                // =========================
                const container = document.getElementById('view_participants');
                container.innerHTML = '';

                // 🔥 NORMALIZE DATA
                // const merged = participantsList.map(p => {
                //     if (typeof p === 'object') {
                //         return {
                //             name: p.name || '',
                //             email: p.email || '',
                //             company: p.company || '',
                //             type: p.type || (isExternal ? 'external' : 'internal')
                //         };
                //     }
                //     return {
                //         name: p,
                //         type: isExternal ? 'external' : 'internal'
                //     };
                // });

                document.getElementById('copyTeamsBtn')?.addEventListener('click', function() {

                    const link = document.getElementById('teamsLink').href;

                    if (!link || link === '#') return;

                    navigator.clipboard.writeText(link)
                        .then(() => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Copied!',
                                text: 'Teams link copied to clipboard',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        })
                        .catch(() => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed',
                                text: 'Could not copy link'
                            });
                        });
                });

                let expanded = false;

                function renderParticipants() {
                    container.innerHTML = '';

                    const list = expanded ? merged : merged.slice(0, 5);

                    list.forEach(p => {
                        const displayName = p.name || p.email || 'Unknown';
                        const initials = displayName.charAt(0).toUpperCase();

                        const badge = p.type === 'external' ?
                            `<span class="text-[10px] px-2 py-0.5 rounded bg-blue-100 text-blue-600">External</span>` :
                            `<span class="text-[10px] px-2 py-0.5 rounded bg-gray-200 text-gray-700">Internal</span>`;

                        container.innerHTML += `
                <div class="flex items-center gap-3 bg-white dark:bg-gray-900 p-2 rounded-lg border">

                    <div class="w-8 h-8 rounded-full bg-gray-900 text-white flex items-center justify-center text-xs font-semibold">
                        ${initials}
                    </div>

                    <div class="flex-1">

                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-800 dark:text-white">
                                ${displayName}
                            </p>
                            ${badge}
                        </div>

                        ${p.email ? `<p class="text-xs text-gray-500">${p.email}</p>` : ''}
                        ${p.company ? `<p class="text-xs text-gray-400">${p.company}</p>` : ''}

                    </div>

                </div>
            `;
                    });

                    // 🔥 SHOW MORE / LESS
                    if (merged.length > 5) {
                        const btn = document.createElement('button');

                        btn.className = 'text-xs text-blue-500 mt-2 w-full text-center';
                        btn.innerText = expanded ?
                            'Show less' :
                            `+${merged.length - 5} more`;

                        btn.onclick = () => {
                            expanded = !expanded;
                            renderParticipants();
                        };

                        container.appendChild(btn);
                    }
                }

                renderParticipants();

                const modal = document.getElementById('event-show');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            },

            eventDrop: function(info) {

                const event = info.event;
                const hasCSACCESS = window.hasCSACCESS;

                // get room_id safely
                const resourceId =
                    event.getResources()?.[0]?.id ||
                    event.extendedProps.room_id ||
                    null;

                const roomName = event.getResources()?.[0]?.title || '';

                if (RESTRICTED_ROOMS.includes(roomName) && !window.hasCSACCESS) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Restricted Room',
                        text: "We're sorry, this room is managed by Receptionist. Please contact them for booking."
                    });

                    info.revert(); // 🔥 VERY IMPORTANT
                    return;
                }

                // ✅ continue your update logic here if you have one
            },

            eventResize: function(info) {

                const event = info.event;
                const hasCSACCESS = window.hasCSACCESS;

                const resourceId =
                    event.getResources()?.[0]?.id ||
                    event.extendedProps.room_id ||
                    null;

                const roomName = event.getResources()?.[0]?.title || '';

                if (RESTRICTED_ROOMS.includes(roomName) && !window.hasCSACCESS) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Restricted Room',
                        text: "We're sorry, this room is managed by Receptionist. Please contact them for booking."
                    });

                    info.revert(); // 🔥 IMPORTANT
                    return;
                }

                // ✅ continue update logic if any
            },

            // select: function(info) {
            //     document.activeElement?.blur();

            //     const today = new Date();

            //     today.setHours(0, 0, 0, 0);

            //     const selectedDate = new Date(info.start);

            //     selectedDate.setHours(0, 0, 0, 0);

            //     if (selectedDate < today) {

            //         Swal.fire({
            //             icon: 'warning',
            //             title: 'Invalid Date',
            //             text: 'Cannot create meeting on past dates'
            //         });

            //         window.calendar.unselect();
            //         return;
            //     }

            //     const selectedDate = moment(info.start).format('YYYY-MM-DD');

            //     if (selectedDate < window.minBookingDate) {

            //         Swal.fire({
            //             icon: 'warning',
            //             title: 'Invalid Date',
            //             text: 'Booking for past dates is not allowed.'
            //         });

            //         window.calendar.unselect();
            //         return;
            //     }

            //     if (selectedDate > window.maxBookingDate) {

            //         Swal.fire({
            //             icon: 'warning',
            //             title: 'Booking Limit',
            //             text: `Booking cannot exceed ${moment(window.maxBookingDate).format('DD MMM YYYY')}`
            //         });

            //         window.calendar.unselect();
            //         return;
            //     }

            //     let resourceId = info.resource ? info.resource.id : null;

            //     const roomName = info.resource?.title || '';

            //     if (RESTRICTED_ROOMS.includes(roomName) && !window.hasCSACCESS) { // 🔥 use real ID
            //         Swal.fire({
            //             icon: 'warning',
            //             title: 'Restricted Room',
            //             text: "We're sorry, this room is managed by Receptionist. Please contact them for booking."
            //         });

            //         window.calendar.unselect();
            //         return;
            //     }
            //     if (!resourceId) {
            //         const fallbackRoom = @json($rooms->first()->room_id ?? null);

            //         if (!fallbackRoom) {
            //             Swal.fire({
            //                 icon: 'error',
            //                 title: 'No Room Configured',
            //                 text: 'Please contact admin'
            //             });
            //             return;
            //         }

            //         resourceId = fallbackRoom;
            //     }

            //     if (!resourceId) {
            //         Swal.fire({
            //             icon: 'warning',
            //             title: 'No Room Available',
            //             text: 'Cannot create meeting without a room'
            //         });
            //         window.calendar.unselect();
            //         return;
            //     }

            //     const start = info.start;
            //     const end = info.end;

            //     const hasConflict = window.calendar.getEvents().some(e => {

            //         const resources = e.getResources();

            //         const eventResourceId =
            //             resources && resources.length > 0 && resources[0] ?
            //             resources[0].id :
            //             null;

            //         return (
            //             eventResourceId === resourceId &&
            //             (
            //                 (start >= e.start && start < e.end) ||
            //                 (end > e.start && end <= e.end) ||
            //                 (start <= e.start && end >= e.end)
            //             )
            //         );
            //     });

            //     if (hasConflict) {
            //         Swal.fire({
            //             icon: 'warning',
            //             title: 'Time Conflict',
            //             text: 'Room already booked'
            //         });

            //         window.calendar.unselect();
            //         return;
            //     }

            //     // 🔥 DIRECT OPEN YOUR REAL MODAL
            //     openModal(start, end, resourceId);

            //     window.calendar.unselect();
            // }

            select: function(info) {

                document.activeElement?.blur();

                const selectedDate = moment(info.start).format('YYYY-MM-DD');

                // ❌ past date
                if (selectedDate < window.minBookingDate) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Date',
                        text: 'Booking for past dates is not allowed.'
                    });

                    window.calendar.unselect();
                    return;
                }

                // ❌ exceed max booking
                if (selectedDate > window.maxBookingDate) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Booking Limit',
                        text: `Booking cannot exceed ${moment(window.maxBookingDate).format('DD MMM YYYY')}`
                    });

                    window.calendar.unselect();
                    return;
                }

                let resourceId = info.resource ? info.resource.id : null;

                const roomName = info.resource?.title || '';

                if (RESTRICTED_ROOMS.includes(roomName) && !window.hasCSACCESS) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Restricted Room',
                        text: "We're sorry, this room is managed by Receptionist. Please contact them for booking."
                    });

                    window.calendar.unselect();
                    return;
                }

                if (!resourceId) {

                    const fallbackRoom = @json($rooms->first()->room_id ?? null);

                    if (!fallbackRoom) {

                        Swal.fire({
                            icon: 'error',
                            title: 'No Room Configured',
                            text: 'Please contact admin'
                        });

                        return;
                    }

                    resourceId = fallbackRoom;
                }

                if (!resourceId) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'No Room Available',
                        text: 'Cannot create meeting without a room'
                    });

                    window.calendar.unselect();
                    return;
                }

                const start = info.start;
                const end = info.end;

                const hasConflict = window.calendar.getEvents().some(e => {

                    const resources = e.getResources();

                    const eventResourceId =
                        resources && resources.length > 0 && resources[0]
                            ? resources[0].id
                            : null;

                    return (
                        eventResourceId === resourceId &&
                        (
                            (start >= e.start && start < e.end) ||
                            (end > e.start && end <= e.end) ||
                            (start <= e.start && end >= e.end)
                        )
                    );
                });

                if (hasConflict) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Time Conflict',
                        text: 'Room already booked'
                    });

                    window.calendar.unselect();
                    return;
                }

                openModal(start, end, resourceId);

                window.calendar.unselect();
            }

        });



        calendar.render();
        const toggle = document.getElementById('is_external_participant');
        const externalSection = document.getElementById('externalParticipantSection');

        if (toggle && externalSection) {
            toggle.addEventListener('change', function() {

                if (this.checked) {
                    externalSection.classList.remove('hidden');

                    if (!document.getElementById('externalTableBody').children.length) {
                        addExternalRow(); // add first row
                    }

                } else {
                    externalSection.classList.add('hidden');
                    document.getElementById('externalTableBody').innerHTML = '';
                }
            });
        }
        document.getElementById('closeScheduleModal')?.addEventListener('click', closeModal);
        document.getElementById('cancelScheduleModal')?.addEventListener('click', closeModal);

        // // click outside modal = close
        // document.getElementById('schedule-show')?.addEventListener('click', function(e) {
        //     if (e.target === this) {
        //         closeModal();
        //     }
        // });

        document.getElementById('closeEventModal')?.addEventListener('click', closeEventModal);
        document.getElementById('closeEventModal2')?.addEventListener('click', closeEventModal);

        document.getElementById('editMeetingBtn')?.addEventListener('click', function() {

            const currentEvent = window.selectedEvent;
            if (!currentEvent) return;

            openEditModal(currentEvent);
        });

        const form = document.getElementById('meetingForm');
        const submitBtn = document.getElementById('submitBtn');

        if (form && submitBtn) {

            form.addEventListener('submit', function(e) {
                e.preventDefault();
            });

            submitBtn.addEventListener('click', function(e) {
                e.preventDefault();

                if (submitBtn.disabled) return;
                const requiredFields = [{
                        id: 'start_datetime',
                        label: 'Start Time'
                    },
                    {
                        id: 'end_datetime',
                        label: 'End Time'
                    },
                    {
                        id: 'room_id',
                        label: 'Room'
                    },
                    {
                        id: 'title',
                        label: 'Title'
                    },
                    {
                        id: 'descr',
                        label: 'Description'
                    },
                    {
                        id: 'participant',
                        label: 'Participants'
                    },
                    {
                        id: 'internal_pic',
                        label: 'Internal PIC Name'
                    } // 🔥 added
                ];

                for (let field of requiredFields) {

                    if (field.id === 'start_datetime') {
                        if (!window.startPicker.selectedDates.length) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Incomplete Form',
                                text: 'Please fill Start Time'
                            });
                            return;
                        }
                        continue;
                    }

                    if (field.id === 'end_datetime') {
                        if (!window.endPicker.selectedDates.length) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Incomplete Form',
                                text: 'Please fill End Time'
                            });
                            return;
                        }
                        continue;
                    }

                    const el = document.getElementById(field.id);

                    if (!el || !el.value || el.value.trim() === '') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Incomplete Form',
                            text: `Please fill ${field.label}`
                        });

                        el?.focus();
                        return;
                    }
                }

                const isExternal = document.getElementById('is_external_participant')?.checked;

                if (isExternal) {
                    // 🔥 CHECK EXTERNAL TABLE
                    const rows = document.querySelectorAll('#externalTableBody tr');

                    if (rows.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Incomplete Form',
                            text: 'Please add at least one external participant'
                        });
                        return;
                    }

                    // optional: validate each row
                    let valid = true;

                    rows.forEach(row => {
                        const name = row.querySelector('[name="external_name[]"]').value.trim();
                        const email = row.querySelector('[name="external_email[]"]').value
                            .trim();

                        if (!name || !email) {
                            valid = false;
                        }
                    });

                    if (!valid) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Incomplete Form',
                            text: 'Please fill all external participant fields'
                        });
                        return;
                    }

                } else {
                    // 🔥 CHECK INTERNAL
                    if (!window.userTom || window.userTom.getValue().length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Incomplete Form',
                            text: 'Please select at least one participant'
                        });
                        return;
                    }
                }

                const start = window.startPicker.selectedDates[0];
                const end = window.endPicker.selectedDates[0];

                if (end <= start) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Time',
                        text: 'End time must be after start time'
                    });
                    return;
                }
                const spinner = document.getElementById('loadingSpinner');
                const text = document.getElementById('submitText');

                spinner.classList.remove('hidden');
                text.innerText = 'Saving...';
                submitBtn.disabled = true;

                const formData = new FormData(form);

                formData.append(
                    'datetimes',
                    moment(start).format('YYYY-MM-DD hh:mm A') + ' - ' +
                    moment(end).format('YYYY-MM-DD hh:mm A')
                );

                const url = window.isEditMode ?
                    `/updatemeeting/${window.editMeetingId}` :
                    form.action;

                if (window.isEditMode) {
                    formData.append('_method', 'PUT');
                }

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value,
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(async res => {
                        const textRes = await res.text();
                        try {
                            return JSON.parse(textRes);
                        } catch {
                            throw new Error("Server error");
                        }
                    })
                    .then(res => {
                        if (!res.success) throw new Error(res.message);

                        closeModal();

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: res.message
                        });

                        window.calendar?.refetchEvents();
                    })
                    .catch(err => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: err.message
                        });
                    })
                    .finally(() => {
                        spinner.classList.add('hidden');
                        text.innerText = window.isEditMode ? 'Update Meeting' : 'Create Meeting';
                        submitBtn.disabled = false;
                    });
            });
        }

        document.getElementById('room_id').addEventListener('change', function() {
            const roomId = this.value;

            // reload accessories
            fetch(`/get-accessories/${roomId}`)
                .then(res => res.json())
                .then(data => {

                    if (!window.accTom) return;

                    window.accTom.clearOptions();

                    Object.entries(data).forEach(([id, name]) => {
                        window.accTom.addOption({
                            value: id,
                            text: name
                        });
                    });

                    if (!window.isEditMode) {
                        window.accTom.clear();
                    }
                    window.accTom.refreshOptions(false);
                });
        });

        document.getElementById('cancelMeetingBtn')?.addEventListener('click', function() {

            const event = window.selectedEvent;
            if (!event) return;

            const id = event.id; // ✅ this is HASH

            Swal.fire({
                title: 'Cancel this meeting?',
                text: 'This action cannot be undone',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, cancel it'
            }).then((result) => {

                if (!result.isConfirmed) return;

                fetch(`/cancel-meeting/${id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name=_token]')
                                .value,
                            'Accept': 'application/json'
                        }
                    })
                    .then(async res => {
                        const text = await res.text();
                        try {
                            return JSON.parse(text);
                        } catch {
                            throw new Error('Server error');
                        }
                    })
                    .then(res => {
                        if (!res.success) throw new Error(res.message);

                        Swal.fire({
                            icon: 'success',
                            title: 'Cancelled',
                            text: res.message
                        });

                        closeEventModal();

                        // 🔥 REFRESH CALENDAR
                        window.calendar?.refetchEvents();
                    })
                    .catch(err => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: err.message
                        });
                    });

            });
        });

    });

    function openModal(start, end, resourceId) {

        window.isEditMode = false;
        window.editMeetingId = null;
        document.getElementById('submitText').innerText = 'Create Meeting';
        document.querySelector('#schedule-show h2').innerText = 'Create Meeting';

        const modal = document.getElementById('schedule-show');

        if (!modal) return;

        const startStr = moment(start).format('YYYY-MM-DD HH:mm');
        const endStr = moment(end).format('YYYY-MM-DD HH:mm');



        if (!window.userTom) {
            const el = document.querySelector('#username');
            if (el) {
                window.userTom = new TomSelect(el, {
                    plugins: ['remove_button'],
                    create: false,
                    persist: false,
                    maxOptions: 1000,
                    placeholder: 'Search participants...',
                    searchField: ['text', 'value'],
                    openOnFocus: true
                });
            }
        }

        if (!window.accTom) {
            const el = document.querySelector('#acc_id');
            if (el) {
                window.accTom = new TomSelect(el, {
                    plugins: ['remove_button'],
                    create: false,
                    persist: false,
                    placeholder: 'Select accessories...',
                });
            }
        }

        // if (window.userTom) {

        //     if (!window.userTom._hasListener) {
        //         window.userTom.on('change', function(values) {

        //             if (!values.length) return;

        //             const first = values[0];
        //             const option = window.userTom.options[first];
        //             const name = option.$option.dataset.name;
        //             const email = option.$option.dataset.email;

        //             if (option && option.$option) {
        //                 const name = option.$option.dataset.name;

        //                 if (name) {
        //                     document.getElementById('internal_pic').value = name;
        //                 }
        //             }
        //         });

        //         window.userTom._hasListener = true;
        //     };
        // }

        const form = document.getElementById('meetingForm');
        if (form) form.reset();

        window.startPicker.setDate(startStr);
        window.endPicker.setDate(endStr);
        document.getElementById('internal_pic').value = "{{ auth()->user()->name }}";


        if (window.userTom) window.userTom.clear(true);
        if (window.accTom) window.accTom.clear(true);

        const toggle = document.getElementById('is_external_participant');
        const externalSection = document.getElementById('externalParticipantSection');

        if (toggle) toggle.checked = false;
        if (externalSection) externalSection.classList.add('hidden');
        document.getElementById('externalTableBody').innerHTML = '';

        const roomSelect = document.getElementById('room_id');
        roomSelect.value = resourceId;
        roomSelect.dispatchEvent(new Event('change'));

        // const roomMap = @json($rooms->pluck('room_name', 'room_id'));
        // document.getElementById('room_id_display').innerHTML =
        //     `<option value="${resourceId}">${roomMap[resourceId] ?? '-'}</option>`;


        // fetch(`/get-accessories/${resourceId}`)
        //     .then(res => {
        //         if (!res.ok) throw new Error('Failed to load accessories');
        //         return res.json();
        //     })
        //     .then(data => {
        //         if (!window.accTom) return;

        //         window.accTom.clearOptions();

        //         const isEmpty = !data || Object.keys(data).length === 0;

        //         if (isEmpty) {
        //             window.accTom.disable();
        //             window.accTom.settings.placeholder = 'Teams / Zoom not available';

        //             window.accTom.addOption({
        //                 value: '',
        //                 text: 'No accessories available'
        //             });

        //             window.accTom.refreshOptions(false);

        //         } else {

        //             window.accTom.enable();
        //             window.accTom.settings.placeholder = 'Select accessories...';

        //             Object.entries(data).forEach(([id, name]) => {
        //                 window.accTom.addOption({
        //                     value: id,
        //                     text: name
        //                 });
        //             });

        //             window.accTom.refreshOptions(false);
        //         }
        //     })
        // .catch(() => {
        //     if (window.accTom) {
        //         window.accTom.disable();
        //         window.accTom.settings.placeholder = 'Failed to load accessories';
        //     }
        // });

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function openEditModal(event) {

        const toggle = document.getElementById('is_external_participant');
        const externalSection = document.getElementById('externalParticipantSection');
        const tbody = document.getElementById('externalTableBody');

        document.querySelector('#schedule-show h2').innerText = 'Edit Meeting';

        if (!window.userTom) {
            const el = document.querySelector('#username');
            if (el) {
                window.userTom = new TomSelect(el, {
                    plugins: ['remove_button'],
                    create: false,
                    persist: false,
                });
            }
        }

        if (!window.accTom) {
            const el = document.querySelector('#acc_id');
            if (el) {
                window.accTom = new TomSelect(el, {
                    plugins: ['remove_button'],
                });
            }
        }


        const roomId =
            event.getResources()?.[0]?.id ||
            event.extendedProps.room_id ||
            null;
        const roomMap = @json($rooms->pluck('room_name', 'room_id'));

        // document.getElementById('room_id_display').innerHTML =
        //     `<option value="${roomId}">${roomMap[roomId] ?? '-'}</option>`;

        const props = event.extendedProps;

        window.isEditMode = true;
        window.editMeetingId = event.id;

        document.getElementById('title').value = event.title || '';
        document.querySelector('[name="descr"]').value = props.description || '';

        window.startPicker.setDate(event.start);

        if (event.end) {
            window.endPicker.setDate(event.end);
        } else {
            const end = new Date(event.start);
            end.setHours(end.getHours() + 1);
            window.endPicker.setDate(end);
        }


        document.getElementById('room_id').value = roomId;

        const roomSelect = document.getElementById('room_id');
        roomSelect.value = roomId;
        roomSelect.dispatchEvent(new Event('change'));


        let participants = props.participants;

        if (typeof participants === 'string') {
            try {
                participants = JSON.parse(participants);
            } catch {
                participants = [];
            }
        }

        if (!Array.isArray(participants)) participants = [];

        document.getElementById('participant').value = participants.length;

        const internalList = participants.filter(p => (p.type || 'internal') === 'internal');
        const externalList = participants.filter(p => (p.type || 'external') === 'external');


        if (window.userTom) {

            window.userTom.clear(true); // silent clear

            internalList.forEach(p => {

                const value = `${p.name}|${p.email}`;

                // 🔥 inject option kalau belum ada
                if (!window.userTom.options[value]) {
                    window.userTom.addOption({
                        value: value,
                        text: `${p.name} (${p.email})`
                    });
                }

                // select it
                window.userTom.addItem(value, true);

            });

            document.getElementById('internal_pic').value =
                props.internal_pic || "{{ auth()->user()->name }}";
        }


        toggle.checked = false;
        externalSection.classList.add('hidden');
        tbody.innerHTML = '';

        if (externalList.length > 0) {
            toggle.checked = true;
            externalSection.classList.remove('hidden');

            externalList.forEach(p => {
                addExternalRow(p.name, p.email, p.company);
            });
        }


        fetch(`/get-accessories/${roomId}`)
            .then(res => res.json())
            .then(data => {

                if (!window.accTom) return;

                window.accTom.clearOptions();

                Object.entries(data).forEach(([id, name]) => {
                    window.accTom.addOption({
                        value: id,
                        text: name
                    });
                });

                window.accTom.refreshOptions(false);

                if (props.accessories) {
                    props.accessories.forEach(acc => {

                        // ensure option exists
                        if (!window.accTom.options[acc.id]) {
                            window.accTom.addOption({
                                value: acc.id,
                                text: acc.name
                            });
                        }

                        window.accTom.addItem(acc.id, true);
                    });
                }
            });


        document.getElementById('submitText').innerText = 'Update Meeting';

        closeEventModal();

        const modal = document.getElementById('schedule-show');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }


    function closeModal() {
        const modal = document.getElementById('schedule-show');
        modal.classList.add('hidden');
        modal.classList.remove('flex'); // 🔥 IMPORTANT
    }

    function closeEventModal() {
        const modal = document.getElementById('event-show');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function loadAccessories(roomId) {

        fetch(`/get-accessories/${roomId}`)
            .then(res => res.json())
            .then(data => {

                const select = document.getElementById('acc_id');
                select.innerHTML = '';

                Object.entries(data).forEach(([id, name]) => {
                    const opt = document.createElement('option');
                    opt.value = id;
                    opt.textContent = name;
                    select.appendChild(opt);
                });

            });
    }

    function addExternalRow(name = '', email = '', company = '') {

        name = (name === 'null' || !name) ? '' : name;
        email = email || '';
        company = company || '';

        const row = `
        <tr class="border-b last:border-0 hover:bg-gray-50 dark:hover:bg-gray-800 transition">

            <td class="p-3">
                <input name="external_name[]" value="${name}"
                    placeholder="Full name"
                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm">
            </td>

            <td class="p-3">
                <input
                    type="email"
                    name="external_email[]"
                    value="${email}"
                    placeholder="example@gmail.com"
                    required
                    pattern="^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$"
                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm"
                >
            </td>

            <td class="p-3">
                <input name="external_company[]" value="${company}"
                    placeholder="Company"
                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm">
            </td>

            <td class="p-3 text-center">
                <button type="button"
                    class="text-red-500 hover:bg-red-50 p-2 rounded-lg transition"
                    onclick="this.closest('tr').remove()">
                    ✕
                </button>
            </td>

        </tr>
        `;

        document.getElementById('externalTableBody')
            .insertAdjacentHTML('beforeend', row);
    }
</script>
