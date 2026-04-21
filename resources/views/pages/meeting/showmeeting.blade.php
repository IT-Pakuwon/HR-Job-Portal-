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

                        <div class="flex items-center gap-3">
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

            </div>
        </div>
    </div>

    {{-- dayjs & toastr --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/plugin/relativeTime.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        dayjs.extend(dayjs_plugin_relativeTime);

        $(document).ready(function() {
            const refnbr = "{{ $meeting->docid }}";
            const doctype = "MT";

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
</x-app-layout>