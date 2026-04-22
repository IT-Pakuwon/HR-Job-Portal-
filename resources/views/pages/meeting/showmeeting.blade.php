<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $statusText = match ($meeting->status) {
            'D' => 'Draft',
            'P' => 'On Progress',
            'C' => 'Completed',
            'R' => 'Rejected',
            'X' => 'Cancelled',
            default => $meeting->status ?: '-',
        };

        $statusClass = match ($meeting->status) {
            'D' => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
            'P' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
            'C' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-800/30 dark:text-emerald-300',
            'R' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
            'X' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
        };

        $fmtDateTime = function ($v) {
            return $v ? \Carbon\Carbon::parse($v)->format('d M Y H:i') : '-';
        };

        $fmtDate = function ($v) {
            return $v ? \Carbon\Carbon::parse($v)->format('d M Y') : '-';
        };

        $rowClass = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
        $labelClass = 'flex items-center gap-2 text-gray-500 sm:min-w-44';
        $valueClass = 'break-words font-medium text-gray-900 dark:text-gray-300 sm:flex-1';

        $fields = [
            [
                'icon' => 'hashtag',
                'label' => 'Doc ID',
                'value' => e($meeting->docid),
            ],
            [
                'icon' => 'calendar-days',
                'label' => 'Meeting Date',
                'value' => $fmtDate($meeting->meeting_date),
            ],
            [
                'icon' => 'user',
                'label' => 'User Peminta',
                'value' => e($meeting->user_peminta ?? '-'),
            ],
            [
                'icon' => 'clock',
                'label' => 'Start Meeting',
                'value' => $fmtDateTime($meeting->start_meeting_time),
            ],
            [
                'icon' => 'clock',
                'label' => 'End Meeting',
                'value' => $fmtDateTime($meeting->end_meeting_time),
            ],
            [
                'icon' => 'chat-bubble-bottom-center-text',
                'label' => 'Meeting Title',
                'value' => e($meeting->meeting_title ?? '-'),
            ],
            [
                'icon' => 'document-text',
                'label' => 'Meeting Description',
                'value' => e($meeting->meeting_descr ?? '-'),
            ],
            [
                'icon' => 'building-office-2',
                'label' => 'Company',
                'value' => e($meeting->cpny_id ?? '-'),
            ],
            [
                'icon' => 'squares-2x2',
                'label' => 'Department',
                'value' => e($meeting->department_id ?? '-'),
            ],
            [
                'icon' => 'map-pin',
                'label' => 'Location',
                'value' => e($meeting->location_id ?? '-'),
            ],
            [
                'icon' => 'home-modern',
                'label' => 'Room',
                'value' => e($meeting->room_name ?? '-'),
            ],
            [
                'icon' => 'computer-desktop',
                'label' => 'Accessories',
                'value' => e($meeting->acc_name ?? '-'),
            ],
            [
                'icon' => 'users',
                'label' => 'Total Participant',
                'value' => e($meeting->total_participant ?? '-'),
            ],
            [
                'icon' => 'user-group',
                'label' => 'Participant List',
                'value' => e($meeting->participant_list ?? '-'),
            ],
            [
                'icon' => 'user-group',
                'label' => 'External Participant',
                'value' => e($meeting->participant_external_list ?? '-'),
            ],
            [
                'icon' => 'video-camera',
                'label' => 'Zoom ID',
                'value' => e($meeting->zoom_id ?? '-'),
            ],
            [
                'icon' => 'video-camera',
                'label' => 'Info Zoom',
                'value' => e($meeting->info_zoom ?? '-'),
            ],
            [
                'icon' => 'video-camera',
                'label' => 'MS Teams Event ID',
                'value' => e($meeting->msteams_event_id ?? '-'),
            ],
            [
                'icon' => 'link',
                'label' => 'MS Teams Join URL',
                'value' => !empty($meeting->msteams_join_url)
                    ? '<a href="' . e($meeting->msteams_join_url) . '" target="_blank" class="text-indigo-600 hover:underline dark:text-indigo-400">'
                        . e($meeting->msteams_join_url) .
                      '</a>'
                    : '-',
            ],
            [
                'icon' => 'key',
                'label' => 'MS Teams Passcode',
                'value' => e($meeting->msteams_passcode ?? '-'),
            ],
            [
                'icon' => 'finger-print',
                'label' => 'MS Teams Meeting ID',
                'value' => e($meeting->msteams_meetingid ?? '-'),
            ],
            [
                'icon' => 'arrow-right-circle',
                'label' => 'Check In',
                'value' => $fmtDateTime($meeting->check_in),
            ],
            [
                'icon' => 'arrow-left-circle',
                'label' => 'Check Out',
                'value' => $fmtDateTime($meeting->check_out),
            ],
            [
                'icon' => 'check-badge',
                'label' => 'Checked By',
                'value' => e($meeting->checked_by ?? '-'),
            ],
            [
                'icon' => 'calendar',
                'label' => 'Checked At',
                'value' => $fmtDateTime($meeting->checked_at),
            ],
            [
                'icon' => 'user-circle',
                'label' => 'Created By',
                'value' => e($meeting->created_by ?? '-'),
            ],
            [
                'icon' => 'calendar',
                'label' => 'Created At',
                'value' => $fmtDateTime($meeting->created_at),
            ],
            [
                'icon' => 'user-circle',
                'label' => 'Updated By',
                'value' => e($meeting->updated_by ?? '-'),
            ],
            [
                'icon' => 'calendar',
                'label' => 'Updated At',
                'value' => $fmtDateTime($meeting->updated_at),
            ],
        ];
    @endphp

    <div class="max-w-9xl mx-auto p-2">
        <div class="flex w-full flex-col gap-6 xl:flex-col">
            <div class="flex w-full items-stretch gap-6 xl:flex-row">

                {{-- Left card --}}
                <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-[8px] dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            <span class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">ID</span>
                            {{ $meeting->docid }}
                        </h1>

                        {{-- <div class="flex items-center gap-3">
                            <span class="{{ $statusClass }} inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold transition-colors duration-200">
                                {{ $statusText }}
                            </span>
                        </div> --}}
                        <div class="flex items-center gap-3">
                            <button type="button" id="editMeetingBtn"
                                class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                Edit
                            </button>

                            <span class="{{ $statusClass }} inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold transition-colors duration-200">
                                {{ $statusText }}
                            </span>
                        </div>
                    </header>

                    <div class="flex flex-1 flex-col overflow-y-auto px-4 py-[8px]">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm sm:grid-cols-2">
                            @foreach ($fields as $f)
                                <div class="{{ $rowClass }}">
                                    <div class="{{ $labelClass }} whitespace-nowrap break-words">
                                        <x-dynamic-component :component="'heroicon-o-' . $f['icon']" class="h-5 w-5 text-gray-400" />
                                        <span>{{ $f['label'] }}</span>
                                    </div>
                                    <span class="{{ $valueClass }}">{!! $f['value'] !!}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Right card - Comments only --}}
                <div class="flex w-full max-w-xl flex-col rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="sticky top-0 z-10 flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                        <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                            Comments
                        </h2>
                    </header>

                    <div class="flex h-full flex-col">
                        <div id="commentList" class="custom-scrollbar flex-1 flex-col space-y-4 overflow-y-auto p-4">
                            <p class="py-4 text-center italic text-gray-500">Loading comments...</p>
                        </div>

                        <div class="flex items-center gap-3 border-t border-gray-200 p-4 dark:border-gray-700">
                            <input id="commentInput" type="text" placeholder="Write a comment..."
                                class="flex-1 rounded-lg bg-gray-100 px-3 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-400">
                            <button id="postCommentBtn" type="button"
                                class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition-all duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Post 🚀
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Modal Edit Meeting --}}
                <div id="schedule-show" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
                    <div class="w-full max-w-4xl rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 id="meetingModalTitle" class="text-lg font-bold text-gray-800 dark:text-white">Edit Meeting</h2>
                            <button type="button" id="closeScheduleModal"
                                class="rounded-md px-3 py-1 text-sm text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                ✕
                            </button>
                        </div>

                        <form id="meetingForm" action="{{ url('/updatemeeting/' . $meeting->id) }}" method="post">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-white">
                                        Start - End <span class="text-red-500">*</span>
                                    </label>
                                    {{-- <input type="text" id="datetimes" name="datetimes"
                                        value="{{ \Carbon\Carbon::parse($meeting->start_meeting_time)->format('Y-m-d h:i A') }} - {{ \Carbon\Carbon::parse($meeting->end_meeting_time)->format('Y-m-d h:i A') }}"
                                        class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        readonly required> --}}
                                    <input type="text" id="datetimes" name="datetimes"
                                        value="{{ \Carbon\Carbon::parse($meeting->start_meeting_time)->format('Y-m-d h:i A') }} - {{ \Carbon\Carbon::parse($meeting->end_meeting_time)->format('Y-m-d h:i A') }}"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        required>
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-white">
                                        Room <span class="text-red-500">*</span>
                                    </label>
                                    {{-- <select id="room_id_display"
                                        class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        disabled>
                                        <option value="{{ $meeting->room_id }}">{{ $meeting->room_name ?? $meeting->room_id }}</option>
                                    </select>
                                    <input type="hidden" id="room_id" name="room_id" value="{{ $meeting->room_id }}"> --}}
                                    <select id="room_id" name="room_id"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        required>
                                        @foreach (($rooms ?? []) as $room)
                                            <option value="{{ $room->room_id }}" {{ (string) $meeting->room_id === (string) $room->room_id ? 'selected' : '' }}>
                                                {{ $room->room_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-white">
                                        Title <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="title" name="title"
                                        value="{{ $meeting->meeting_title }}"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        placeholder="Title Meeting" required>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-white">
                                        Description <span class="text-red-500">*</span>
                                    </label>
                                    <textarea id="descr" name="descr" rows="4"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        placeholder="Enter Description ..." required>{{ $meeting->meeting_descr }}</textarea>
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-white">
                                        Accessories
                                    </label>
                                    <select id="acc_id" name="acc_id[]"
                                        class="meeting-multi w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        multiple>
                                        @foreach (($accessories ?? []) as $acc)
                                            <option value="{{ $acc->acc_id }}">
                                                {{ $acc->acc_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-white">
                                        Participant
                                    </label>
                                    <input type="number" id="participant" name="participant"
                                        value="{{ $meeting->total_participant }}"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        placeholder="Number Of Participant" min="1" required>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-white">
                                        Email To
                                    </label>
                                    <select id="username" name="username[]"
                                        class="meeting-multi w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        multiple required>
                                        @foreach (($users ?? []) as $u)
                                            <option value="{{ $u->username }}|{{ $u->meeting_email }}">
                                                {{ $u->name }} ({{ $u->meeting_email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-white">
                                        Reason <span class="text-red-500">*</span>
                                    </label>
                                    <textarea id="reason" name="reason" rows="3"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        placeholder="Enter reason for this meeting..." required></textarea>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-white">
                                        <input type="checkbox" id="is_external_participant"
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        External Participant
                                    </label>
                                </div>

                                <div id="externalParticipantSection" class="hidden md:col-span-2">
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <div>
                                            <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-white">
                                                External Participant
                                            </label>
                                            <input type="text" id="external_participant" name="external_participant"
                                                value="{{ $meeting->external_participant }}"
                                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                                placeholder="Nama external participant">
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-white">
                                                Email External
                                            </label>
                                            <input type="text" id="participant_external_list" name="participant_external_list"
                                                value="{{ $meeting->participant_external_list }}"
                                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                                placeholder="email1@mail.com,email2@mail.com">
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                Pisahkan beberapa email dengan koma.
                                            </p>
                                        </div>
                                    </div>
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
                                    <span id="submitText">Update</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- dayjs & toastr --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/plugin/relativeTime.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>  

    <script>
        dayjs.extend(dayjs_plugin_relativeTime);

        $(document).ready(function() {
            const refnbr = "{{ $meeting->docid }}";
            const doctype = "MTR";

            loadComments(refnbr, doctype);

            function loadComments(refnbr, doctype) {
                let commentList = $('#commentList');
                commentList.html('<p class="text-gray-500 italic">Loading comments...</p>');

                $.ajax({
                    url: `/comments/${doctype}/${refnbr}`,
                    type: 'GET',
                    success: function(response) {
                        commentList.empty();

                        if (!response.comments || response.comments.length === 0) {
                            commentList.append(
                                '<p class="text-gray-500 text-sm italic">No comments yet. Be the first to comment!</p>'
                            );
                            return;
                        }

                        response.comments.forEach(comment => {
                            const timeStr = comment.message_date ?? comment.created_at;
                            const timeAgo = timeStr ? dayjs(timeStr).fromNow() : '';

                            commentList.append(`
                                <div class="px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2">
                                    <p class="text-sm font-semibold">
                                        ${comment.username}
                                        <span class="text-sm text-gray-500">(${timeAgo})</span>
                                    </p>
                                    <p class="text-gray-800 dark:text-gray-200">${comment.message}</p>
                                </div>
                            `);
                        });
                    },
                    error: function(xhr) {
                        console.error("Error fetching comments:", xhr.responseText);
                        commentList.html('<p class="text-red-500 italic">Failed to load comments.</p>');
                    }
                });
            }

            function addComment() {
                let input = $('#commentInput').val().trim();

                if (input === "") {
                    toastr.warning("Please enter a comment.");
                    return;
                }

                $('#postCommentBtn').prop('disabled', true).text('Posting... 🚀');

                $.ajax({
                    url: `/comments/${doctype}/${refnbr}`,
                    type: 'POST',
                    data: {
                        comment: input,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === "success") {
                            loadComments(refnbr, doctype);
                            $('#commentInput').val('');
                        } else {
                            toastr.error(response.message || 'Failed to post comment.');
                        }
                    },
                    error: function(xhr) {
                        console.error("Error adding comment:", xhr);
                        toastr.error(xhr.responseJSON ? xhr.responseJSON.message : "Unknown Error");
                    },
                    complete: function() {
                        $('#postCommentBtn').prop('disabled', false).text('Post 🚀');
                    }
                });
            }

            $(document).on('click', '#postCommentBtn', function(e) {
                e.preventDefault();
                addComment();
            });

            $('#commentInput').keypress(function(event) {
                if (event.which === 13 && !event.shiftKey) {
                    event.preventDefault();
                    addComment();
                }
            });
        });
    </script>

    <script>
        let accTom = null;
        let userTom = null;

        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('schedule-show');
            const editBtn = document.getElementById('editMeetingBtn');
            const closeBtn = document.getElementById('closeScheduleModal');
            const cancelBtn = document.getElementById('cancelScheduleModal');
            const externalCheckbox = document.getElementById('is_external_participant');
            const externalSection = document.getElementById('externalParticipantSection');

            const selectedAcc = @json(
                collect(explode(',', (string) ($meeting->acc_id ?? '')))
                    ->map(fn($v) => trim($v))
                    ->filter()
                    ->values()
                    ->all()
            );

            const selectedParticipantEmails = @json(
                collect(explode(',', (string) ($meeting->participant_list ?? '')))
                    ->map(fn($v) => trim($v))
                    ->filter()
                    ->values()
                    ->all()
            );

            function openModal() {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            function toggleExternalParticipant() {
                if (externalCheckbox.checked) {
                    externalSection.classList.remove('hidden');
                } else {
                    externalSection.classList.add('hidden');
                }
            }

            if (!accTom) {
                accTom = new TomSelect('#acc_id', {
                    plugins: ['remove_button'],
                    create: false,
                    persist: false,
                    placeholder: 'Select accessories'
                });
            }

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

            if (selectedAcc.length) {
                accTom.setValue(selectedAcc, true);
            }

            if (selectedParticipantEmails.length) {
                const allOptions = Array.from(document.querySelectorAll('#username option'));
                const selectedUserValues = [];

                selectedParticipantEmails.forEach(function (email) {
                    const match = allOptions.find(function (opt) {
                        const parts = String(opt.value || '').split('|');
                        const optEmail = (parts[1] || '').trim();
                        return optEmail === email;
                    });

                    if (match) {
                        selectedUserValues.push(match.value);
                    }
                });

                if (selectedUserValues.length) {
                    userTom.setValue(selectedUserValues, true);
                }
            }

            if ("{{ !empty($meeting->external_participant) || !empty($meeting->participant_external_list) ? 1 : 0 }}" == "1") {
                externalCheckbox.checked = true;
            }

            toggleExternalParticipant();

            // editBtn?.addEventListener('click', function () {
            //     openModal();
            // });
            editBtn?.addEventListener('click', function () {
                openModal();

                setTimeout(function () {
                    $('#datetimes').data('daterangepicker')?.remove(); // reset dulu
                    initDateTimePicker();
                }, 200);
            });

            closeBtn?.addEventListener('click', function () {
                closeModal();
            });

            cancelBtn?.addEventListener('click', function () {
                closeModal();
            });

            externalCheckbox?.addEventListener('change', toggleExternalParticipant);

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
                $('#submitText').text('Updating...');
                $('#loadingSpinner').removeClass('hidden');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message || 'Meeting berhasil diupdate.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        let message = 'Gagal update meeting.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: message
                        });
                    },
                    complete: function() {
                        $('#submitBtn').prop('disabled', false);
                        $('#submitText').text('Update');
                        $('#loadingSpinner').addClass('hidden');
                    }
                });
            });
        });
    </script>
    <script>
        $(document).on('change', '#room_id', function () {
            const roomId = $(this).val();

            if (!roomId || !accTom) return;

            accTom.clear();
            accTom.clearOptions();

            $.ajax({
                url: "{{ url('infoacc') }}_" + roomId,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    $.each(response, function (key, value) {
                        accTom.addOption({
                            value: key,
                            text: value
                        });
                    });

                    accTom.refreshOptions(false);
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil accessories.'
                    });
                }
            });
        });
    </script>
    <script>
        function initDateTimePicker() {
            if (typeof moment === 'undefined') {
                console.error('moment.js belum termuat');
                return;
            }

            if ($('#datetimes').data('daterangepicker')) {
                $('#datetimes').data('daterangepicker').remove();
            }

            $('#datetimes').daterangepicker({
                autoUpdateInput: true,
                timePicker: true,
                timePicker24Hour: false,
                timePickerSeconds: false,
                locale: {
                    format: 'YYYY-MM-DD hh:mm A',
                    cancelLabel: 'Clear'
                },
                startDate: moment("{{ \Carbon\Carbon::parse($meeting->start_meeting_time)->format('Y-m-d h:i A') }}", 'YYYY-MM-DD hh:mm A'),
                endDate: moment("{{ \Carbon\Carbon::parse($meeting->end_meeting_time)->format('Y-m-d h:i A') }}", 'YYYY-MM-DD hh:mm A')
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            initDateTimePicker();
        });
    </script>
</x-app-layout>