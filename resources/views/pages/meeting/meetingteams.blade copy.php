<x-app-layout>
    <style>
      

        .fc .fc-timeline-slot-lane {
            height: 120px !important;
        }
    </style>
    <div class="max-w-9xl mx-auto w-full p-2">
        @if (session('success'))
            <div class="mb-4 rounded-lg border border-green-300 bg-green-100 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-300 bg-red-100 px-4 py-3 text-sm text-red-800">
                <div class="font-semibold">Terjadi kesalahan:</div>
                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
            <div class="mb-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-lg font-extrabold text-gray-700 dark:text-white">Meeting Calendar</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-300">
                        Request Meeting Room
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ url('/meeting') }}"
                        class="inline-flex items-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                        Request Meeting
                    </a>

                    <a href="{{ url('/meetinglist') }}"
                        class="inline-flex items-center rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-300">
                        All Meeting
                    </a>

                    <a href="{{ url('/list_zoom') }}"
                        class="inline-flex items-center rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-600">
                        List Teams Meeting
                    </a>
                </div>
            </div>

             

            <div class="rounded-lg border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-gray-800">
                <div id="calendar"></div>
            </div>
        </div>

        {{-- Modal --}}
        <div id="schedule-show" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-4xl rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white">Create Meeting</h2>
                    <button type="button" id="closeScheduleModal"
                        class="rounded-md px-3 py-1 text-sm text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                        ✕
                    </button>
                </div>

                <form id="meetingForm" action="{{ url('/saveteams') }}" method="post">
                    @csrf

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-white">
                                Start - End <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="datetimes" name="datetimes"
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                readonly required>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-white">
                                Room <span class="text-red-500">*</span>
                            </label>
                            <select id="room_id_display"
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                disabled>
                            </select>
                            <input type="hidden" id="room_id" name="room_id">
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-white">
                                Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="title" name="title"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="Title Meeting" required>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-white">
                                Description <span class="text-red-500">*</span>
                            </label>
                            <textarea name="descr" rows="4"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="Enter Description ..." required></textarea>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-white">
                                Accessories
                            </label>
                            <select id="acc_id" name="acc_id[]"
                                class="meeting-multi w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                multiple>
                            </select>
                        </div>                                               
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" id="cancelScheduleModal"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-white dark:hover:bg-gray-700">
                            Close
                        </button>

                        <button type="submit" id="submitBtn"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
                            <svg id="loadingSpinner" class="mr-2 hidden h-4 w-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <span id="submitText">Submit</span>
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
                    right: 'resourceTimelineDay,resourceTimelineThreeDays,timeGridWeek,dayGridMonth,listWeek'
                },
                initialView: 'resourceTimelineDay',
                views: {
                    resourceTimelineThreeDays: {
                        type: 'resourceTimeline',
                        duration: { days: 3 },
                        buttonText: '3 days'
                    }
                },
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

                events: [
                    @foreach ($meetings as $showmeeting)
                        {
                            id: @json($showmeeting->id),
                            resourceId: @json($showmeeting->room_id),
                            start: @json(\Carbon\Carbon::parse($showmeeting->start_meeting_time)->format('Y-m-d H:i:s')),
                            end: @json(\Carbon\Carbon::parse($showmeeting->end_meeting_time)->format('Y-m-d H:i:s')),
                            title: @json(trim(($showmeeting->user_peminta ? $showmeeting->user_peminta . ' - ' : '') . $showmeeting->meeting_title)),
                            url: "{{ url('/showmeeting/' . \Vinkla\Hashids\Facades\Hashids::encode($showmeeting->id)) }}"
                        },
                    @endforeach
                ],

                // selectOverlap: function(event) {
                //     return event.rendering === 'background';
                // },
                selectOverlap: true,

                eventClick: function(info) {
                    info.jsEvent.preventDefault(); // supaya tidak reload default

                    if (info.event.url) {
                        window.location.href = info.event.url;
                    }
                },

                select: function(info) {
                    var addstart = moment(info.startStr).format('YYYY-MM-DD hh:mm A');
                    var addend = moment(info.endStr).format('YYYY-MM-DD hh:mm A');
                    var adddate = moment(info.endStr).format('YYYY-MM-DD');

                    var dateblock = @json($dateblock);
                    var usergroups = @json($user->groups ?? '');
                    var hasCsAccess = @json($hasCsAccess);
                    var id = info.resource ? info.resource.id : null;
                    var roomTitle = info.resource ? info.resource.title : '';

                    resetMeetingForm();

                    $('#datetimes').val(addstart + ' - ' + addend);
                    $('#room_id').val(id);
                    $('#room_id_display').empty().append('<option value="' + id + '">' + roomTitle + '</option>');

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
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal mengambil accessories.'
                            });
                        }
                    });

                    if (adddate > dateblock) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Cannot Create',
                            text: 'Cannot create for selected date.'
                        });
                    } else if (!hasCsAccess && (id == 'd' || id == 'h')) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Room Restricted',
                            text: 'Unable to book this room, Please contact Reception!'
                        });
                    } else {
                        openModal();
                    }
                }
            });

            calendarInstance.render();

            $('#meetingForm').on('submit', function(e) {
                e.preventDefault();

                const $form = $(this);
                const formData = $form.serialize();               

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
    </script>
   
</x-app-layout>