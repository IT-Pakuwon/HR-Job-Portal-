<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-4">

        {{-- HEADER --}}
        <div class="mb-4 rounded-2xl border border-gray-200 bg-white/70 backdrop-blur p-5 shadow-sm dark:border-white/10 dark:bg-white/5">

            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                {{-- LEFT --}}
                <div class="flex items-center gap-3">

                    {{-- ICON --}}
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl
                        {{ request()->is('meeting')
                            ? 'bg-blue-100 text-blue-600 dark:bg-blue-500/10'
                            : 'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/10' }}">

                        @if(request()->is('meeting'))
                            <!-- calendar -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        @else
                            <!-- video -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                            : 'text-gray-600 hover:bg-white/60 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10' }}
                        flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition">

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
                            : 'text-gray-600 hover:bg-white/60 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10' }}
                        flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition">

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

            <div class="mt-4 rounded-lg bg-blue-50 px-4 py-3 text-sm text-blue-700 border border-blue-100">
                ℹ️ Multiple bookings are allowed — you can proceed even if the slot is already booked.
            </div>
        </div>

        {{-- CALENDAR --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div id="calendar"></div>
        </div>

        {{-- Modal --}}
        <div id="schedule-show"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-4xl rounded-2xl bg-white shadow-xl dark:bg-gray-900 max-h-[90vh] flex flex-col">
                <div class="overflow-y-auto">

                        {{-- HEADER --}}
                        <div class="flex items-center justify-between border-b px-6 py-4 dark:border-gray-700">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Create Meeting
                                </h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Fill the details below to schedule a meeting
                                </p>
                            </div>

                            <button type="button" id="closeScheduleModal"
                                class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 dark:hover:bg-gray-800">
                                ✕
                            </button>
                        </div>

                        {{-- BODY --}}
                        <form id="meetingForm" action="{{ url('/saveteams') }}" method="post">
                            @csrf

                            <div class="space-y-5 px-6 py-5">

                                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                                    {{-- DATE --}}
                                    <div>
                                        <label class="req text-xs font-medium text-gray-500 dark:text-gray-400">
                                            Start - End
                                        </label>
                                        <input type="text" id="datetimes" name="datetimes"
                                            class="mt-1 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            readonly required>
                                    </div>

                                    {{-- ROOM --}}
                                    <div>
                                        <label class="req text-xs font-medium text-gray-500 dark:text-gray-400">
                                            Room
                                        </label>
                                        <select id="room_id_display"
                                            class="mt-1 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            disabled>
                                        </select>
                                        <input type="hidden" id="room_id" name="room_id">
                                    </div>

                                    {{-- TITLE --}}
                                    <div class="md:col-span-2">
                                        <label class="req text-xs font-medium text-gray-500 dark:text-gray-400">
                                            Title
                                        </label>
                                        <input type="text" id="title" name="title"
                                            class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            placeholder="e.g. Weekly Sync Meeting" required>
                                    </div>

                                    {{-- DESCRIPTION --}}
                                    <div class="md:col-span-2">
                                        <label class="req text-xs font-medium text-gray-500 dark:text-gray-400">
                                            Description
                                        </label>
                                        <textarea name="descr" rows="4"
                                            class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            placeholder="Write meeting details..." required></textarea>
                                    </div>

                                    {{-- ACCESSORIES --}}
                                    <div class="md:col-span-2">
                                        <label class="req text-xs font-medium text-gray-500 dark:text-gray-400">
                                            Accessories
                                        </label>
                                        <select id="acc_id" name="acc_id[]"
                                            class="meeting-multi mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            multiple>
                                        </select>
                                    </div>

                                </div>

                            </div>

                            {{-- FOOTER --}}
                            <div
                                class="flex items-center justify-between rounded-b-2xl border-t bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-800/50">

                                <button type="button" id="cancelScheduleModal"
                                    class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700">
                                    Cancel
                                </button>

                                <button type="submit" id="submitBtn"
                                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-60">

                                    <svg id="loadingSpinner" class="hidden h-4 w-4 animate-spin"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                        </path>
                                    </svg>

                                    <span id="submitText">Create Meeting</span>
                                </button>

                            </div>

                        </form>
                    </div>
                </div>
        </div>

        <!-- MODAL -->
        <div id="viewMeetingModal"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">

            <div class="w-full max-w-2xl rounded-2xl border bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">

                <!-- HEADER -->
                <div class="flex items-center justify-between border-b px-6 py-4 dark:border-gray-700">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Meeting Details
                        </h2>
                        <p class="text-xs text-gray-500">Teams / Zoom Booking</p>
                    </div>

                    <button onclick="closeViewModal()"
                        class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
                        ✕
                    </button>
                </div>

                <!-- BODY -->
                <div class="space-y-8 px-8 py-6 text-sm">

                    <!-- TITLE -->
                    <div>
                        <h2 id="view_title" class="text-xl font-semibold text-gray-900 dark:text-white"></h2>
                        <p id="view_time" class="mt-1 text-sm text-gray-500"></p>
                    </div>

                    <!-- META GRID (NOTION STYLE) -->
                    <div class="grid grid-cols-2 gap-x-10 gap-y-4 text-sm">

                        <div>
                            <div class="text-xs text-gray-400">Platform</div>
                            <div id="view_room" class="font-medium text-gray-900 dark:text-white"></div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-400">Accessories</div>
                            <div id="view_acc" class="text-gray-700 dark:text-gray-300"></div>
                        </div>

                    </div>

                    <!-- DESCRIPTION -->
                    <div>
                        <div class="mb-1 text-xs text-gray-400">Description</div>
                        <div id="view_desc" class="leading-relaxed text-gray-700 dark:text-gray-300"></div>
                    </div>

                    <!-- PARTICIPANTS -->
                    <div>
                        <div class="mb-1 text-xs text-gray-400">Participants</div>
                        <div id="view_participants" class="leading-relaxed text-gray-700 dark:text-gray-300"></div>
                    </div>

                    <!-- MEETING LINK (🔥 CLEAN NOTION STYLE) -->
                    <div class="space-y-4 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:bg-gray-800">

                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium uppercase tracking-wide text-gray-400">
                                Meeting Link
                            </span>
                            <div id="link_status_badge"></div>
                        </div>

                        <div id="view_teams"></div>

                        @if (auth()->user()->user_role === 'admin')
                            <div id="link_action_area"></div>
                        @endif

                    </div>

                </div>

                <!-- FOOTER -->
                <div class="flex items-center justify-between border-t px-6 py-4 dark:border-gray-700">

                    <button onclick="cancelMeeting()"
                        class="rounded-lg bg-red-500 px-4 py-2 text-sm text-white hover:bg-red-600">
                        Cancel Meeting
                    </button>

                    <button onclick="closeViewModal()"
                        class="rounded-lg bg-gray-200 px-4 py-2 text-sm dark:bg-gray-700 dark:text-white">
                        Close
                    </button>

                </div>

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        let calendarInstance = null;
        let currentEventId = null;

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

            calendarInstance = new FullCalendar.Calendar(calendarEl, {
                schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
                now: new Date(),
                scrollTime: '08:00:00',
                editable: false,
                selectable: true,
                aspectRatio: 1.6,
                headerToolbar: {
                    left: 'today prev,next',
                    center: 'title',
                    right: 'resourceTimelineDay,timeGridWeek,dayGridMonth,listWeek'
                },
                initialView: 'resourceTimelineDay',
                resourceAreaHeaderContent: 'Rooms',

                resources: [
                    @foreach ($rooms as $showroom)
                        {
                            id: @json($showroom->room_id),
                            title: @json($showroom->room_name),
                            eventColor: @json($showroom->eventcolor ?: '#2563eb')
                        },
                    @endforeach
                ],

                events: '/calendar-json',
                // selectOverlap: function(event) {
                //     return event.rendering === 'background';
                // },
                selectOverlap: true,



                eventClick: function(info) {
                    info.jsEvent.preventDefault();

                    openViewMeetingModal(info.event);
                },

                eventContent: function(arg) {
                    const p = arg.event.extendedProps;

                    let status = '';
                    let bg = '';

                    if (!p.teams_url) {
                        status = 'Waiting';
                        bg = 'bg-yellow-100 text-yellow-700';
                    } else if (p.isTeams) {
                        status = 'Teams';
                        bg = 'bg-blue-100 text-blue-700';
                    } else {
                        status = 'Zoom';
                        bg = 'bg-purple-100 text-purple-700';
                    }

                    return {
                        html: `
                        <div class="rounded-lg px-2 py-1 text-[11px] space-y-1">

                            <div class="font-semibold truncate">
                                ${p.user || ''}
                            </div>

                            <div class="truncate opacity-90">
                                ${arg.event.title}
                            </div>

                            <div class="flex items-center justify-between">

                                <span class="text-[10px] px-1.5 py-0.5 rounded ${bg}">
                                    ${status}
                                </span>

                                <span class="text-[10px] text-gray-400">
                                    ${moment(arg.event.start).format('HH:mm')}
                                </span>

                            </div>

                        </div>
                        `
                    };
                },
                select: function(info) {

                    const start = moment(info.startStr);
                    const end = moment(info.endStr);

                    resetMeetingForm();

                    // Fill form
                    $('#datetimes').val(
                        start.format('YYYY-MM-DD hh:mm A') + ' - ' + end.format(
                            'YYYY-MM-DD hh:mm A')
                    );

                    $('#room_id').val(info.resource.id);
                    $('#room_id_display')
                        .empty()
                        .append(`<option>${info.resource.title}</option>`);

                    // 🔥 LOAD ACCESSORIES (CORRECT ENDPOINT)
                    fetch(`/get-accessories/${info.resource.id}`)
                        .then(res => res.json())
                        .then(data => {
                            if (accTom) {
                                accTom.clear();
                                accTom.clearOptions();

                                Object.entries(data).forEach(([id, name]) => {
                                    accTom.addOption({
                                        value: id,
                                        text: name
                                    });
                                });

                                accTom.refreshOptions(false);
                            }
                        })
                        .catch(() => {
                            Swal.fire('Error', 'Failed to load accessories', 'error');
                        });

                    // OPEN MODAL
                    document.getElementById('schedule-show').classList.remove('hidden');
                    document.getElementById('schedule-show').classList.add('flex');
                }

            });

            calendarInstance.render();

            $('#meetingForm').on('submit', function(e) {
                e.preventDefault();

                const $form = $(this);
                let formData = $form.serialize();

                // 👉 extract datetime
                let datetimeVal = $('#datetimes').val();

                if (datetimeVal && datetimeVal.includes(' - ')) {
                    let [start, end] = datetimeVal.split(' - ');

                    formData += `&start_datetime=${encodeURIComponent(start)}`;
                    formData += `&end_datetime=${encodeURIComponent(end)}`;
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



        });
    </script>

    <script>
        let accTom = null;

        function initTomSelect() {

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


        function renderLinkSection(props) {

            const container = document.getElementById('view_teams');
            const badge = document.getElementById('link_status_badge');
            const action = document.getElementById('link_action_area');

            const isAdmin = !!action;
            const canEditTeams =
            props.username === '{{ auth()->user()->username }}';

            // =========================
            // ✅ HAS LINK
            // =========================
            if (props.teams_url) {

                container.innerHTML = `
                    <div class="flex items-center justify-between gap-3">

                        <a href="${props.teams_url}" target="_blank"
                            class="text-blue-600 font-medium hover:underline">
                            Open Meeting
                        </a>

                        <div class="flex items-center gap-2">
                            <button onclick="copyLink('${props.teams_url}')"
                                class="text-xs px-2 py-1 rounded bg-gray-200 hover:bg-gray-300">
                                Copy
                            </button>
                            ${canEditTeams ? `
                                <button onclick="enableEdit()"
                                    class="text-xs px-2 py-1 rounded bg-gray-100 hover:bg-gray-200">
                                    Edit
                                </button>
                            ` : ''}

                        </div>

                    </div>
                `;

                badge.innerHTML = `
                    <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">
                        Ready
                    </span>
                `;

                if (isAdmin) {
                    action.innerHTML = ''; // no extra button needed
                }

            } else {

                // =========================
                // ❌ NO LINK
                // =========================

                container.innerHTML = `
                    <div class="text-gray-400 text-sm">
                        Waiting for meeting link
                    </div>
                `;

                badge.innerHTML = `
                    <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded">
                        Pending
                    </span>
                `;

                if (isAdmin) {
                    action.innerHTML = `
                        <button onclick="enableEdit('')"
                            class="mt-2 px-3 py-1 text-sm bg-blue-600 text-white rounded">
                            Process
                        </button>
                    `;
                }
            }
        }

        function enableEdit() {

            const action = document.getElementById('link_action_area');

            const currentTitle =
                document.getElementById('view_title').innerText || '';

            const currentDesc =
                document.getElementById('view_desc').innerText || '';

            action.innerHTML = `

                <div class="space-y-4">

                    <div>
                        <label class="block mb-1 text-xs text-gray-500">
                            Meeting Title
                        </label>

                        <input
                            type="text"
                            id="edit_meeting_title"
                            value="${currentTitle}"
                            class="w-full rounded-lg border border-gray-200
                            dark:border-gray-700 px-3 py-2 text-sm
                            bg-white dark:bg-gray-900">
                    </div>

                    <div class="grid grid-cols-2 gap-4">

                        <div>
                            <label class="block mb-1 text-xs text-gray-500">
                                Start Time
                            </label>

                            <input
                                type="text"
                                id="edit_start_datetime"
                                class="w-full rounded-lg border border-gray-200
                                dark:border-gray-700 px-3 py-2 text-sm
                                bg-white dark:bg-gray-900">
                        </div>

                        <div>
                            <label class="block mb-1 text-xs text-gray-500">
                                End Time
                            </label>

                            <input
                                type="text"
                                id="edit_end_datetime"
                                class="w-full rounded-lg border border-gray-200
                                dark:border-gray-700 px-3 py-2 text-sm
                                bg-white dark:bg-gray-900">
                        </div>

                    </div>

                    <div>
                        <label class="block mb-1 text-xs text-gray-500">
                            Description
                        </label>

                        <textarea
                            id="edit_meeting_desc"
                            rows="4"
                            class="w-full rounded-lg border border-gray-200
                            dark:border-gray-700 px-3 py-2 text-sm
                            bg-white dark:bg-gray-900">${currentDesc}</textarea>
                    </div>

                    <div class="flex justify-end gap-2">

                        <button
                            onclick="closeEditMode()"
                            class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-sm">
                            Cancel
                        </button>

                        <button
                            onclick="saveTeamsMeeting()"
                            class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm">
                            Save Changes
                        </button>

                    </div>

                </div>
            `;

            // 🔥 GET CURRENT EVENT
            const event = calendarInstance.getEventById(currentEventId);

            // 🔥 START PICKER
            flatpickr("#edit_start_datetime", {
                enableTime: true,
                dateFormat: "Y-m-d h:i K",
                time_24hr: false,
                defaultDate: event ? event.start : null
            });

            // 🔥 END PICKER
            flatpickr("#edit_end_datetime", {
                enableTime: true,
                dateFormat: "Y-m-d h:i K",
                time_24hr: false,
                defaultDate: event ? event.end : null
            });
        }

        // function saveMeetingLink() {

        //     const link = document.getElementById('edit_meeting_link').value;

        //     if (!link) {
        //         Swal.fire('Warning', 'Please input meeting link', 'warning');
        //         return;
        //     }

        //     $.ajax({
        //         url: '/updateteams/' + currentEventId,
        //         type: 'PUT',
        //         data: {
        //             meeting_link: link,
        //             _token: $('input[name="_token"]').val()
        //         },
        //         success: function() {

        //             Swal.fire('Success', 'Link saved', 'success');

        //             // update modal UI
        //             renderLinkSection({
        //                 teams_url: link
        //             });

        //             // 🔥 UPDATE EVENT LIVE (NO RELOAD)
        //             const event = calendarInstance.getEventById(currentEventId);

        //             if (event) {
        //                 event.setExtendedProp('teams_url', link);

        //                 // 🔵 DONE = BLUE
        //                 event.setProp('backgroundColor', '#3b82f6');
        //                 event.setProp('borderColor', '#3b82f6');
        //             }

        //             // optional sync
        //             calendarInstance.refetchEvents();
        //         },
        //         error: function() {
        //             Swal.fire('Error', 'Failed to save link', 'error');
        //         }
        //     });
        // }

        function saveMeetingLink() {

            const link =
                document.getElementById('edit_meeting_link').value;

            const title =
                document.getElementById('edit_meeting_title').value;

            const descr =
                document.getElementById('edit_meeting_desc').value;

            $.ajax({
                url: '/updateteams/' + currentEventId,
                type: 'PUT',
                data: {
                    meeting_link: link,
                    title: title,
                    descr: descr,
                    _token: $('input[name="_token"]').val()
                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Updated',
                        text: response.message || 'Meeting updated'
                    });

                    // =========================
                    // UPDATE MODAL UI
                    // =========================

                    document.getElementById('view_title').innerText = title;
                    document.getElementById('view_desc').innerText = descr;

                    renderLinkSection({
                        teams_url: link
                    });

                    // =========================
                    // UPDATE FULLCALENDAR LIVE
                    // =========================

                    const event = calendarInstance.getEventById(currentEventId);

                    if (event) {

                        event.setProp('title', title);

                        event.setExtendedProp('description', descr);

                        event.setExtendedProp('teams_url', link);

                        event.setExtendedProp('isTeams', !!link);

                        // optional visual refresh
                        event.setProp(
                            'backgroundColor',
                            link ? '#2563eb' : '#eab308'
                        );

                        event.setProp(
                            'borderColor',
                            link ? '#2563eb' : '#eab308'
                        );
                    }

                    // optional sync
                    calendarInstance.refetchEvents();
                },

                error: function(xhr) {

                    let message = 'Failed to update meeting';

                    if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message
                    });
                }
            });
        }

        function saveTeamsMeeting() {

            const title =
                document.getElementById('edit_meeting_title').value;

            const descr =
                document.getElementById('edit_meeting_desc').value;

            const startDatetime =
                document.getElementById('edit_start_datetime').value;

            const endDatetime =
                document.getElementById('edit_end_datetime').value;

            const datetimes =
                `${startDatetime} - ${endDatetime}`;

            $.ajax({

                url: '/updateteams/' + currentEventId,

                type: 'PUT',

                data: {
                    title,
                    descr,
                    datetimes,
                    _token: $('input[name="_token"]').val()
                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Updated',
                        text: response.message
                    });

                    // =========================
                    // UPDATE MODAL
                    // =========================

                    document.getElementById('view_title').innerText =
                        title;

                    document.getElementById('view_desc').innerText =
                        descr;

                    // =========================
                    // UPDATE FULLCALENDAR
                    // =========================

                    const event =
                        calendarInstance.getEventById(currentEventId);

                    if (event) {

                        const [startRaw, endRaw] =
                            datetimes.split(' - ');

                        event.setProp('title', title);

                        event.setStart(moment(startRaw).toDate());

                        event.setEnd(moment(endRaw).toDate());

                        event.setExtendedProp(
                            'description',
                            descr
                        );
                    }

                    calendarInstance.refetchEvents();

                    closeEditMode();
                },

                error: function(xhr) {

                    let message =
                        'Failed to update Teams meeting';

                    if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message
                    });
                }
            });
        }

        function closeEditMode() {
            document.getElementById('link_action_area').innerHTML = '';
        }


        function copyLink(link) {
            navigator.clipboard.writeText(link);
            Swal.fire('Copied!', 'Link copied to clipboard', 'success');
        }

        function openViewMeetingModal(event) {

            currentEventId = event.id;

            if (document.activeElement) {
                document.activeElement.blur();
            }

            const props = event.extendedProps || {};

            document.getElementById('view_title').innerText = event.title;

            document.getElementById('view_time').innerText =
                moment(event.start).format('ddd, DD MMM YYYY HH:mm') +
                ' → ' +
                moment(event.end).format('HH:mm');

            document.getElementById('view_room').innerText = props.room || '-';
            document.getElementById('view_desc').innerText = props.description || '-';

            // document.getElementById('view_acc').innerText =
            //     (props.accessories && props.accessories.length) ?
            //     props.accessories.join(', ') :
            //     '-';

            document.getElementById('view_acc').innerText =
            (props.accessories && props.accessories.length)
                ? props.accessories.map(a => a.name).join(', ')
                : '-';

            let participantsHtml = '-';

            if (props.participants && props.participants.length) {
                participantsHtml = props.participants.map(p => {
                    let company = p.company ? ` (${p.company})` : '';
                    return `${p.name} - ${p.email}${company}`;
                }).join('<br>');
            }

            document.getElementById('view_participants').innerHTML = participantsHtml;

            const teamsEl = document.getElementById('view_teams');
            const badge = document.getElementById('link_status_badge');
            const input = document.getElementById('edit_meeting_link');

            renderLinkSection(props);

            document.getElementById('viewMeetingModal').classList.remove('hidden');
            document.getElementById('viewMeetingModal').classList.add('flex');
        }

        function closeViewModal() {
            const modal = document.getElementById('viewMeetingModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function cancelMeeting() {

            Swal.fire({
                title: 'Cancel this meeting?',
                text: "This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: 'Yes, cancel it',
                cancelButtonText: 'No'
            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({
                        url: '/cancel-meeting/' + currentEventId,
                        type: 'POST',
                        data: {
                            _token: $('input[name="_token"]').val()
                        },
                        success: function() {

                            Swal.fire({
                                icon: 'success',
                                title: 'Cancelled',
                                text: 'Meeting has been cancelled.'
                            }).then(() => {
                                window.location.reload();
                            });

                        },
                        error: function() {
                            Swal.fire('Error', 'Failed to cancel meeting', 'error');
                        }
                    });

                }

            });
        }
    </script>

</x-app-layout>
