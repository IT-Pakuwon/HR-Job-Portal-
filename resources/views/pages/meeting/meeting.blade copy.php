<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- ALERTS --}}
        @if (session('success'))
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                <span>✔</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <div class="font-medium mb-1">Terjadi kesalahan</div>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="opacity-90">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- HEADER --}}
        <div class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

            {{-- LEFT --}}
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                    Meeting Calendar
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Schedule and manage your meetings
                </p>
            </div>

            {{-- RIGHT ACTIONS --}}
            <div class="flex flex-wrap items-center gap-2">

                <a href="{{ url('/meetingteams') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-black transition">
                    Booking Teams / Zoom
                </a>

                <a href="{{ url('/meetinglist') }}"
                    class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    All Meetings
                </a>

                <a href="{{ url('/list_zoom') }}"
                    class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    Zoom List
                </a>

            </div>
        </div>

        {{-- CALENDAR CONTAINER --}}
        <div class="relative rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

            {{-- subtle top bar --}}
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-gray-800">
                <div class="text-sm font-medium text-gray-600 dark:text-gray-300">
                    Calendar View
                </div>
            </div>

            {{-- CALENDAR --}}
            <div class="p-4">
                <div id="calendar" class="text-sm"></div>
            </div>

        </div>

        {{-- Modal --}}
        <div id="schedule-show"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-black/30 backdrop-blur-sm p-4">

            <div class="w-full max-w-3xl rounded-2xl bg-white dark:bg-gray-900 shadow-xl">

                {{-- HEADER --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
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

                    <div class="px-6 py-5 space-y-6">

                        {{-- SECTION: BASIC --}}
                        <div class="space-y-4">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                {{-- DATETIME --}}
                                <div>
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                        Start - End *
                                    </label>
                                    <input type="text" id="datetimes" name="datetimes" readonly required
                                        class="mt-1 w-full rounded-md bg-gray-100 dark:bg-gray-800 border-0 px-3 py-2 text-sm focus:ring-1 focus:ring-gray-300">
                                </div>

                                {{-- ROOM --}}
                                <div>
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                        Room *
                                    </label>
                                    <select id="room_id_display" disabled
                                        class="mt-1 w-full rounded-md bg-gray-100 dark:bg-gray-800 border-0 px-3 py-2 text-sm">
                                    </select>
                                    <input type="hidden" id="room_id" name="room_id">
                                </div>

                            </div>

                            {{-- TITLE --}}
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                    Title *
                                </label>
                                <input type="text" id="title" name="title" required
                                    placeholder="Meeting title..."
                                    class="mt-1 w-full rounded-md bg-transparent border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm focus:ring-1 focus:ring-gray-300">
                            </div>

                            {{-- DESCRIPTION --}}
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                    Description *
                                </label>
                                <textarea name="descr" rows="3" required
                                    placeholder="Write a short description..."
                                    class="mt-1 w-full rounded-md bg-transparent border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm focus:ring-1 focus:ring-gray-300"></textarea>
                            </div>

                        </div>

                        {{-- SECTION: DETAILS --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            {{-- ACCESSORIES --}}
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                    Accessories
                                </label>
                                <select id="acc_id" name="acc_id[]" multiple
                                    class="meeting-multi mt-1 w-full rounded-md border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm bg-transparent">
                                </select>
                            </div>

                            {{-- PARTICIPANT --}}
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                    Participants
                                </label>
                                <input type="number" id="participant" name="participant" min="1" required
                                    placeholder="Number of participants"
                                    class="mt-1 w-full rounded-md border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm bg-transparent">
                            </div>

                        </div>

                        {{-- EMAIL --}}
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                Email To *
                            </label>
                            <select id="username" name="username[]" multiple required
                                class="meeting-multi mt-1 w-full rounded-md border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm bg-transparent">
                                @foreach ($users as $u)
                                    <option value="{{ $u->username }}|{{ $u->meeting_email }}">
                                        {{ $u->name }} ({{ $u->meeting_email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- EXTERNAL TOGGLE --}}
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="is_external_participant"
                                class="h-4 w-4 rounded border-gray-300">
                            <label class="text-sm text-gray-600 dark:text-gray-300">
                                External Participant
                            </label>
                        </div>

                        {{-- EXTERNAL SECTION --}}
                        <div id="externalParticipantSection" class="hidden space-y-4">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                <div>
                                    <label class="text-xs text-gray-500">External Name</label>
                                    <input type="text" id="external_participant" name="external_participant"
                                        placeholder="Name..."
                                        class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm">
                                </div>

                                <div>
                                    <label class="text-xs text-gray-500">External Email</label>
                                    <input type="text" id="participant_external_list"
                                        name="participant_external_list"
                                        placeholder="email1@mail.com, email2@mail.com"
                                        class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2 text-sm">
                                    <p class="mt-1 text-xs text-gray-400">
                                        Separate emails with comma
                                    </p>
                                </div>

                            </div>

                        </div>

                    </div>

                    {{-- FOOTER --}}
                    <div class="flex justify-between items-center px-6 py-4 border-t border-gray-200 dark:border-gray-700">

                        <button type="button" id="cancelScheduleModal"
                            class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-white">
                            Cancel
                        </button>

                        <button type="submit" id="submitBtn"
                            class="inline-flex items-center gap-2 rounded-md bg-gray-900 text-white px-4 py-2 text-sm hover:bg-black transition disabled:opacity-60">

                            <svg id="loadingSpinner"
                                class="hidden h-4 w-4 animate-spin"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/>
                                <path fill="currentColor" class="opacity-75"
                                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                            </svg>

                            <span id="submitText">Create Meeting</span>
                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.20/index.global.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.20/index.global.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let calendarInstance = null;


        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const modal = document.getElementById('schedule-show');
            const closeModalBtn = document.getElementById('closeScheduleModal');
            const cancelModalBtn = document.getElementById('cancelScheduleModal');

            function openModal() {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            function resetSubmitState() {
                $('#submitBtn').prop('disabled', false);
                $('#submitText').text('Submit');
                $('#loadingSpinner').addClass('hidden');
            }

            function resetMeetingForm() {
                $('#meetingForm')[0].reset();
                $('#room_id').val('');
                $('#room_id_display').empty();

                if (accTom) {
                    accTom.clear();
                    accTom.clearOptions();
                }

                if (userTom) {
                    userTom.clear();
                }

                $('#external_participant').val('');
                $('#participant_external_list').val('');
                $('#is_external_participant').prop('checked', false);
                $('#externalParticipantSection').addClass('hidden');

                resetSubmitState();
            }

            closeModalBtn.addEventListener('click', function() {
                closeModal();
                resetMeetingForm();
            });

            cancelModalBtn.addEventListener('click', function() {
                closeModal();
                resetMeetingForm();
            });

            // calendarInstance = new FullCalendar.Calendar(calendarEl, {
            // schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',

            // initialView: isMobile ? 'timeGridDay' : 'resourceTimelineDay',
            // height: isMobile ? 'auto' : '75vh',

            // headerToolbar: {
            //     left: 'today prev,next',
            //     center: 'title',
            //     right: isMobile
            //         ? 'timeGridDay,timeGridWeek'
            //         : 'resourceTimelineDay,timeGridWeek,dayGridMonth,listWeek'
            // },

            // resources: isMobile ? [] : [
            //     @foreach ($rooms as $showroom)
            //     {
            //         id: @json($showroom->room_id),
            //         title: @json($showroom->room_name),
            //         eventColor: @json($showroom->eventcolor ?: '#2563eb')
            //     },
            //     @endforeach
            // ],

            // events: [
            //     @foreach ($meetings as $showmeeting)
            //     {
            //         id: @json($showmeeting->id),
            //         resourceId: @json($showmeeting->room_id),
            //         start: @json(\Carbon\Carbon::parse($showmeeting->start_meeting_time)->format('Y-m-d H:i:s')),
            //         end: @json(\Carbon\Carbon::parse($showmeeting->end_meeting_time)->format('Y-m-d H:i:s')),
            //         title: @json(trim(($showmeeting->user_peminta ? $showmeeting->user_peminta . ' - ' : '') . $showmeeting->meeting_title)),
            //         extendedProps: {
            //             type: @json($showmeeting->external_participant ? 'external' : 'internal'),
            //             isTeams: @json($showmeeting->is_teams ?? false)
            //         },
            //         url: "{{ url('/showmeeting/' . \Vinkla\Hashids\Facades\Hashids::encode($showmeeting->id)) }}"
            //     },
            //     @endforeach
            // ],

            // selectable: true,
            // eventOverlap: false,
            // slotEventOverlap: false,

            // select: function(info) {

            //     const start = info.start;
            //     const end = info.end;
            //     const resourceId = info.resource ? info.resource.id : null;

            //     // 🚫 conflict check (only if resource exists)
            //     const hasConflict = calendarInstance.getEvents().some(e => {
            //         if (!resourceId) return false;

            //         return (
            //             e.getResources()[0]?.id === resourceId &&
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
            //             text: 'Room already booked in this time slot.'
            //         });
            //         return;
            //     }

            //     // normal flow
            //     const addstart = moment(info.startStr).format('YYYY-MM-DD hh:mm A');
            //     const addend = moment(info.endStr).format('YYYY-MM-DD hh:mm A');

            //     const id = info.resource ? info.resource.id : null;
            //     const roomTitle = info.resource ? info.resource.title : '';

            //     resetMeetingForm();

            //     $('#datetimes').val(addstart + ' - ' + addend);
            //     $('#room_id').val(id);

            //     if (id) {
            //         $('#room_id_display')
            //             .empty()
            //             .append(`<option value="${id}">${roomTitle}</option>`);
            //     }

            //     openModal();
            // },

            // eventClick: function(info) {
            //     info.jsEvent.preventDefault();
            //     if (info.event.url) {
            //         window.location.href = info.event.url;
            //     }
            // },

            // eventContent: function(arg) {
            //     const title = arg.event.title;
            //     const type = arg.event.extendedProps.type;
            //     const isTeams = arg.event.extendedProps.isTeams;

            //     return {
            //         html: `
            //             <div class="fc-custom-event">
            //                 <div class="fc-event-title">${title}</div>
            //                 <div class="fc-event-meta">
            //                     ${type === 'external' ? 'External' : 'Internal'} • ${isTeams ? 'Teams' : 'No Teams'}
            //                 </div>
            //             </div>
            //         `
            //     };
            // }

            //     select: function(info) {

            //     const start = info.start;
            //     const end = info.end;
            //     const resourceId = info.resource.id;

            //     const hasConflict = calendarInstance.getEvents().some(e => {
            //         return (
            //             e.getResources()[0]?.id === resourceId &&
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
            //             text: 'Room already booked in this time slot.'
            //         });
            //         return;
            //     }

            //     // lanjut normal
            // },

            //     selectOverlap: function(event) {
            //         return event.rendering === 'background';
            //     },

            //     eventClick: function(info) {
            //         info.jsEvent.preventDefault(); // supaya tidak reload default

            //         if (info.event.url) {
            //             window.location.href = info.event.url;
            //         }
            //     },

            //     select: function(info) {
            //         var addstart = moment(info.startStr).format('YYYY-MM-DD hh:mm A');
            //         var addend = moment(info.endStr).format('YYYY-MM-DD hh:mm A');
            //         var adddate = moment(info.endStr).format('YYYY-MM-DD');

            //         var dateblock = @json($dateblock);
            //         var usergroups = @json($user->groups ?? '');
            //         var hasCsAccess = @json($hasCsAccess);
            //         var id = info.resource ? info.resource.id : null;
            //         var roomTitle = info.resource ? info.resource.title : '';

            //         resetMeetingForm();

            //         $('#datetimes').val(addstart + ' - ' + addend);
            //         $('#room_id').val(id);
            //         $('#room_id_display').empty().append('<option value="' + id + '">' + roomTitle + '</option>');

            //         $.ajax({
            //             url: 'infoacc_' + id,
            //             type: 'get',
            //             dataType: 'json',
            //             success: function(response) {
            //                 if (accTom) {
            //                     accTom.clear();
            //                     accTom.clearOptions();

            //                     $.each(response, function(key, value) {
            //                         accTom.addOption({
            //                             value: key,
            //                             text: value
            //                         });
            //                     });

            //                     accTom.refreshOptions(false);
            //                 }
            //             },
            //             error: function() {
            //                 Swal.fire({
            //                     icon: 'error',
            //                     title: 'Error',
            //                     text: 'Gagal mengambil accessories.'
            //                 });
            //             }
            //         });

            //         if (adddate > dateblock) {
            //             Swal.fire({
            //                 icon: 'warning',
            //                 title: 'Cannot Create',
            //                 text: 'Cannot create for selected date.'
            //             });
            //         } else if (!hasCsAccess && (id == 'd' || id == 'h')) {
            //             Swal.fire({
            //                 icon: 'warning',
            //                 title: 'Room Restricted',
            //                 text: 'Unable to book this room, Please contact Reception!'
            //             });
            //         } else {
            //             openModal();
            //         }
            //     }
            // });

            const isMobile = window.innerWidth < 768;

            calendarInstance = new FullCalendar.Calendar(calendarEl, {
                schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',

                now: new Date(),
                scrollTime: '08:00:00',
                editable: false,
                selectable: true,

                eventOverlap: false,
                slotEventOverlap: false,
                eventMaxStack: 3,
                slotDuration: "00:30:00",

                height: isMobile ? 'auto' : '75vh',
                contentHeight: 'auto',
                expandRows: true,

                initialView: isMobile ? 'timeGridDay' : 'resourceTimelineDay',

                headerToolbar: {
                    left: 'today prev,next',
                    center: 'title',
                    right: isMobile
                        ? 'timeGridDay,timeGridWeek'
                        : 'resourceTimelineDay,timeGridWeek,dayGridMonth,listWeek'
                },

                resourceAreaHeaderContent: isMobile ? '' : 'Rooms',

                resources: isMobile ? [] : [
                    @foreach ($rooms as $showroom)
                    {
                        id: @json($showroom->room_id),
                        title: @json($showroom->room_name),
                        eventColor: @json($showroom->eventcolor ?: '#2563eb')
                    },
                    @endforeach
                ],

                events: [
                    @foreach ($meetings as $showmeeting)
                    {
                        id: @json($showmeeting->id),
                        resourceId: @json($showmeeting->room_id),
                        start: @json(\Carbon\Carbon::parse($showmeeting->start_meeting_time)->format('Y-m-d H:i:s')),
                        end: @json(\Carbon\Carbon::parse($showmeeting->end_meeting_time)->format('Y-m-d H:i:s')),
                        title: @json(trim(($showmeeting->user_peminta ? $showmeeting->user_peminta . ' - ' : '') . $showmeeting->meeting_title)),
                        extendedProps: {
                            type: @json($showmeeting->external_participant ? 'external' : 'internal'),
                            isTeams: @json($showmeeting->is_teams ?? false)
                        },
                        url: "{{ url('/showmeeting/' . \Vinkla\Hashids\Facades\Hashids::encode($showmeeting->id)) }}"
                    },
                    @endforeach
                ],

                eventContent: function(arg) {
                    const title = arg.event.title;
                    const type = arg.event.extendedProps.type;
                    const isTeams = arg.event.extendedProps.isTeams;

                    return {
                        html: `
                            <div class="fc-custom-event">
                                <div class="fc-event-title">${title}</div>
                                <div class="fc-event-meta">
                                    ${type === 'external' ? 'External' : 'Internal'} • ${isTeams ? 'Teams' : 'No Teams'}
                                </div>
                            </div>
                        `
                    };
                },

                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    if (info.event.url) {
                        window.location.href = info.event.url;
                    }
                },

                selectOverlap: function(event) {
                    return event.display === 'background';
                },

                select: function(info) {

                    const start = info.start;
                    const end = info.end;
                    const resourceId = info.resource ? info.resource.id : null;

                    // =========================
                    // 🔥 DOUBLE BOOKING CHECK
                    // =========================
                    const hasConflict = calendarInstance.getEvents().some(e => {

                        if (resourceId) {
                            return (
                                e.getResources()[0]?.id === resourceId &&
                                (
                                    (start >= e.start && start < e.end) ||
                                    (end > e.start && end <= e.end) ||
                                    (start <= e.start && end >= e.end)
                                )
                            );
                        }

                        return false;
                    });

                    if (hasConflict) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Time Conflict',
                            text: 'Room already booked in this time slot.'
                        });
                        return;
                    }

                    // =========================
                    // 🧠 FORMAT DATE
                    // =========================
                    const addstart = moment(info.startStr).format('YYYY-MM-DD hh:mm A');
                    const addend = moment(info.endStr).format('YYYY-MM-DD hh:mm A');
                    const adddate = moment(info.endStr).format('YYYY-MM-DD');

                    const dateblock = @json($dateblock);
                    const hasCsAccess = @json($hasCsAccess);

                    const id = info.resource ? info.resource.id : null;
                    const roomTitle = info.resource ? info.resource.title : '';

                    resetMeetingForm();

                    $('#datetimes').val(addstart + ' - ' + addend);
                    $('#room_id').val(id);

                    if (id) {
                        $('#room_id_display')
                            .empty()
                            .append(`<option value="${id}">${roomTitle}</option>`);

                        // =========================
                        // 🔥 LOAD ACCESSORIES
                        // =========================
                        $.ajax({
                            url: 'infoacc_' + id,
                            type: 'get',
                            dataType: 'json',
                            success: function(response) {
                                if (accTom) {
                                    accTom.clear();
                                    accTom.clearOptions();

                                    $.each(response, function(key, value) {
                                        accTom.addOption({
                                            value: key,
                                            text: value
                                        });
                                    });

                                    accTom.refreshOptions(false);
                                }
                            }
                        });
                    }

                    // =========================
                    // 🚫 VALIDATION
                    // =========================
                    if (adddate > dateblock) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Cannot Create',
                            text: 'Cannot create for selected date.'
                        });
                        return;
                    }

                    if (!hasCsAccess && (id === 'd' || id === 'h')) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Room Restricted',
                            text: 'Unable to book this room, Please contact Reception!'
                        });
                        return;
                    }

                    // =========================
                    // ✅ OPEN MODAL
                    // =========================
                    openModal();
                }
            });

            calendarInstance.render();

            $('#meetingForm').on('submit', function(e) {
                e.preventDefault();

                const $form = $(this);
                const formData = $form.serialize();
                const participant = $('#participant').val();
                const users = $('#username').val();

                 if (!participant || participant <= 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validation',
                        text: 'Participant harus diisi dan berupa angka lebih dari 0'
                    });
                    return false;
                }

                if (!users || users.length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validation',
                        text: 'Email To wajib diisi minimal 1'
                    });
                    return false;
                }

                $('#submitBtn').prop('disabled', true);
                $('#submitText').text('Loading...');
                $('#loadingSpinner').removeClass('hidden');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val(),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function() {
                        resetSubmitState();
                        closeModal();

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Meeting berhasil disimpan.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        resetSubmitState();

                        let message = 'Gagal menyimpan meeting.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: message
                        });
                    }
                });
            });

            const externalCheckbox = document.getElementById('is_external_participant');
            const externalSection = document.getElementById('externalParticipantSection');

            function toggleExternalParticipant() {
                if (externalCheckbox.checked) {
                    externalSection.classList.remove('hidden');
                } else {
                    externalSection.classList.add('hidden');
                    $('#external_participant').val('');
                    $('#participant_external_list').val('');
                }
            }

            externalCheckbox.addEventListener('change', toggleExternalParticipant);
            toggleExternalParticipant();


        });
    </script>

    <script>
        let accTom = null;
        let userTom = null;

        function initTomSelect() {
            if (!userTom) {
                userTom = new TomSelect('#username', {
                    plugins: ['remove_button'],
                    create: false,
                    persist: false,
                    maxOptions: 1000,
                    placeholder: 'Select email recipients',
                    searchField: ['text', 'value']
                });
            }

            if (!accTom) {
                accTom = new TomSelect('#acc_id', {
                    plugins: ['remove_button'],
                    create: false,
                    persist: false,
                    placeholder: 'Select accessories'
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            initTomSelect();
        });
    </script>

</x-app-layout>
