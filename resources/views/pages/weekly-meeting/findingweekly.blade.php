<x-app-layout>
    <div id="weeklyMeetingViewport" class="mx-auto w-full max-w-9xl p-3 lg:overflow-hidden">
        <div class="flex h-full flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="relative shrink-0 overflow-hidden border-b bg-gradient-to-br from-indigo-50 via-white to-violet-50 px-5 py-3 dark:border-gray-700 dark:from-indigo-950/40 dark:via-gray-800 dark:to-violet-950/30">
                <div class="pointer-events-none absolute -right-12 -top-16 h-44 w-44 rounded-full bg-indigo-200/30 blur-2xl dark:bg-indigo-500/10"></div>
                <div class="relative flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-200 dark:shadow-none">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5V4a2 2 0 0 1 2-2h12v20H6a2 2 0 0 1-2-2.5Z"/><path d="M8 7h6M8 11h6M8 15h4"/></svg>
                        </span>
                        <h1 class="text-xl font-extrabold text-gray-900 dark:text-white">{{ $meeting->weeklymeeting_id }}</h1>
                        <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">Weekly Meeting</span>
                    </div>
                    @if ($canApprove)
                        <button type="button" id="btnApproveWeeklyMeeting"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-700 hover:shadow">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 4 4L19 6"/></svg>
                            Approval
                        </button>
                    @endif
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $meeting->weeklymeeting_topic }} · {{ $meeting->weeklymeeting_date->format('d M Y') }}</p>
                <p class="mt-1 text-xs text-gray-400">Finding period: {{ $fromDate->format('d M Y') }} – {{ $toDate->format('d M Y') }}</p>
                <div class="relative mt-3 rounded-xl border border-white/80 bg-white/70 px-4 py-2.5 shadow-sm backdrop-blur dark:border-gray-600/60 dark:bg-gray-800/50">
                    <div class="mb-2 flex items-center gap-2">
                        <p class="text-xs font-extrabold uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">Attendance · {{ $participants->count() }}</p>
                        @if (strtoupper((string) $meeting->status) !== 'C')
                            <button type="button" id="btnEditWeeklyMeeting" title="Edit meeting and attendance"
                                class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-300">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            </button>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @forelse ($participants as $participant)
                            <span class="inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow dark:border-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-300">
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-[10px] font-bold text-white">
                                    {{ strtoupper(substr($participant->name_participant ?: $participant->user_participant, 0, 1)) }}
                                </span>
                                {{ $participant->name_participant ?: $participant->user_participant }}
                            </span>
                        @empty
                            <span class="text-sm text-gray-500 dark:text-gray-400">No attendance.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="grid min-h-0 flex-1 grid-cols-1 lg:grid-cols-3">
            <div class="flex min-h-0 min-w-0 flex-col border-b dark:border-gray-700 lg:col-span-2 lg:border-b-0 lg:border-r">
            <div class="flex shrink-0 overflow-x-auto border-b px-5 dark:border-gray-700">
                <button type="button" class="meeting-finding-tab shrink-0 border-b-2 border-indigo-600 px-4 py-3 text-sm font-bold text-indigo-600" data-tab="open">
                    <span class="inline-flex items-center gap-2"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2h8l4 4v16H4V2h4Z"/><path d="M8 2v5h8V2M8 13h8M8 17h5"/></svg>Open Finding ({{ $openFindings->count() }})</span>
                </button>
                <button type="button" class="meeting-finding-tab shrink-0 border-b-2 border-transparent px-4 py-3 text-sm font-bold text-gray-500 dark:text-gray-400" data-tab="close">
                    <span class="inline-flex items-center gap-2"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>Close Finding ({{ $closeFindings->count() }})</span>
                </button>
                <button type="button" class="meeting-finding-tab shrink-0 border-b-2 border-transparent px-4 py-3 text-sm font-bold text-gray-500 dark:text-gray-400" data-tab="project">
                    <span class="inline-flex items-center gap-2"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 10h6M9 14h6M9 18h6"/></svg>Project Finding</span>
                </button>
            </div>

            @foreach (['open' => [$openFindings, $openDepartmentCards], 'close' => [$closeFindings, $closeDepartmentCards]] as $tab => [$findings, $departmentCards])
                <div id="{{ $tab }}FindingPanel" class="finding-tab-panel min-h-0 flex-1 overflow-auto {{ $tab !== 'open' ? 'hidden' : '' }} p-5">
                    <div class="mb-4 flex flex-wrap gap-2">
                        @foreach ($departmentCards as $department)
                            <button type="button"
                                class="department-filter-button rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-bold text-gray-600 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700 hover:shadow dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                data-tab="{{ $tab }}" data-department="{{ $department['id'] }}">
                                {{ $department['name'] }} ({{ $department['count'] }})
                            </button>
                        @endforeach
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[950px] text-sm">
                            <thead class="sticky top-0 z-10 bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-3 py-2 text-left">Finding ID</th>
                                    <th class="px-3 py-2 text-left">Date</th>
                                    <th class="px-3 py-2 text-left">Department</th>
                                    <th class="px-3 py-2 text-left">Issue</th>
                                    <th class="px-3 py-2 text-left">PIC</th>
                                    <th class="px-3 py-2 text-left">Comment</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($findings as $finding)
                                    <tr class="finding-row border-t dark:border-gray-700" data-department="{{ $finding->department_id }}">
                                        <td class="px-3 py-3">
                                            <button type="button" class="btnFindingDetail inline-flex items-center rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 font-bold text-indigo-700 transition hover:bg-indigo-100" data-id="{{ $finding->finding_id }}">
                                                {{ $finding->finding_id }}
                                            </button>
                                        </td>
                                        <td class="px-3 py-3">{{ $finding->finding_date?->format('d M Y') }}</td>
                                        <td class="px-3 py-3">{{ $finding->department_id }}</td>
                                        <td class="max-w-md px-3 py-3">{{ $finding->issue }}</td>
                                        <td class="px-3 py-3">{{ $finding->comment_pics ?: '-' }}</td>
                                        <td class="px-3 py-3">
                                            <button type="button" class="btnCommentDetail inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-2 py-1.5 text-xs font-bold text-red-600 hover:bg-red-100" data-id="{{ $finding->finding_id }}">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"/></svg>
                                                {{ $finding->comments_count }}
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-3 py-10 text-center text-gray-500 dark:text-gray-400">No findings in this period.</td></tr>
                                @endforelse
                                <tr class="no-filter-result hidden"><td colspan="6" class="px-3 py-10 text-center text-gray-500 dark:text-gray-400">No findings for this department.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            <div id="projectFindingPanel" class="finding-tab-panel hidden min-h-0 flex-1 overflow-auto p-10 text-center text-gray-500 dark:text-gray-400">Project Finding table is not available yet.</div>
            </div>
            <aside class="flex min-h-0 min-w-0 flex-col bg-gray-50/50 p-5 dark:bg-white/[0.02] lg:col-span-1">
                <div class="mb-4 shrink-0">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5V4a2 2 0 0 1 2-2h12v20H6a2 2 0 0 1-2-2.5Z"/><path d="M8 7h6M8 11h6M8 15h4"/></svg>
                        <h2 class="font-extrabold text-gray-800 dark:text-white">Minute of Meeting</h2>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Text and pasted images are saved in one MOM document.</p>
                </div>
                <form id="momEditorForm" method="POST" action="{{ route('weekly-meeting.mom.store', $meeting->weeklymeeting_id) }}" class="flex min-h-0 flex-1 flex-col">
                @csrf
                    <input type="hidden" name="mom_descr" id="momEditorInput">
                    <div id="momEditor" class="min-h-0 flex-1 bg-white dark:bg-gray-800">{!! $momContent !!}</div>
                    @if (strtoupper((string) $meeting->status) !== 'C')
                        <div class="mt-4 flex justify-end">
                            <button type="submit" id="btnSaveMomEditor" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-bold text-white hover:bg-indigo-700">Save MOM</button>
                        </div>
                    @endif
                </form>
            </aside>
            </div>
        </div>
    </div>

    <div id="editWeeklyMeetingModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-gray-50 shadow-2xl dark:bg-gray-900">
            <div class="flex shrink-0 items-center justify-between border-b bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                <div><h2 class="text-lg font-extrabold">Edit Weekly Meeting</h2><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $meeting->weeklymeeting_id }}</p></div>
                <button type="button" class="btnCloseEditMeeting text-2xl text-gray-500 dark:text-gray-400">&times;</button>
            </div>
            <form id="editWeeklyMeetingForm" action="{{ route('weekly-meeting.update', $meeting->weeklymeeting_id) }}" class="min-h-0 overflow-y-auto">
                @csrf
                @method('PUT')
                <div class="space-y-5 p-5">
                    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-5 flex items-center gap-3 border-b pb-4 dark:border-gray-700">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5V4a2 2 0 0 1 2-2h12v20H6a2 2 0 0 1-2-2.5Z"/><path d="M8 7h6M8 11h6"/></svg>
                            </span>
                            <div><h3 class="font-extrabold">Meeting Information</h3><p class="text-xs text-gray-500 dark:text-gray-400">Topic, schedule, and meeting time.</p></div>
                        </div>
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="mb-1.5 block text-sm font-bold">Topic <span class="text-red-500">*</span></label>
                                <input type="text" name="weeklymeeting_topic" value="{{ $meeting->weeklymeeting_topic }}" required maxlength="500"
                                    class="w-full rounded-xl border border-gray-200 px-4 py-3 dark:border-gray-600 dark:bg-gray-700">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-bold">Date <span class="text-red-500">*</span></label>
                                <input type="date" name="weeklymeeting_date" value="{{ $meeting->weeklymeeting_date->format('Y-m-d') }}" required
                                    class="w-full rounded-xl border border-gray-200 px-4 py-3 dark:border-gray-600 dark:bg-gray-700">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-bold">Time <span class="text-red-500">*</span></label>
                                <input type="time" name="meeting_time" value="{{ $meeting->weeklymeeting_startdate->format('H:i') }}" required
                                    class="w-full rounded-xl border border-gray-200 px-4 py-3 dark:border-gray-600 dark:bg-gray-700">
                            </div>
                        </div>
                    </section>
                    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-4 flex items-center justify-between gap-3 border-b pb-4 dark:border-gray-700">
                            <div>
                                <div class="flex items-center gap-2"><h3 class="font-extrabold">Attendance</h3><span id="editParticipantCount" class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-700">0</span></div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Update the participant list for this meeting.</p>
                            </div>
                            <button type="button" id="btnAddEditParticipant" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">+ Add Participant</button>
                        </div>
                        <div id="editParticipantRows" class="space-y-3"></div>
                    </section>
                </div>
                <div class="sticky bottom-0 flex justify-end gap-3 border-t bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                    <button type="button" class="btnCloseEditMeeting rounded-xl border px-4 py-2.5 text-sm font-bold">Cancel</button>
                    <button type="submit" id="btnUpdateWeeklyMeeting" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Update</button>
                </div>
            </form>
        </div>
    </div>

    <div id="findingDetailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="flex h-[92vh] max-h-[92vh] w-full max-w-6xl flex-col overflow-hidden rounded-xl bg-white shadow-xl dark:bg-gray-800">
            <div class="flex shrink-0 items-center justify-between border-b px-5 py-4 dark:border-gray-700">
                <div><h2 id="detailFindingId" class="text-lg font-extrabold">Finding Detail</h2><p id="detailFindingDate" class="mt-1 text-xs text-gray-500 dark:text-gray-400">-</p></div>
                <button type="button" class="btnCloseFindingModal text-2xl text-gray-500 dark:text-gray-400">&times;</button>
            </div>
            <div class="grid min-h-0 flex-1 grid-cols-1 overflow-y-auto lg:grid-cols-2 lg:overflow-hidden">
                <div class="overflow-y-auto border-b p-5 lg:border-b-0 lg:border-r dark:border-gray-700">
                    <div id="findingPhotos" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-1"></div>
                    <div id="findingSummary" class="mt-6 grid grid-cols-1 gap-4 border-t pt-5 sm:grid-cols-2 dark:border-gray-700"></div>
                </div>
                <div class="flex min-h-[70vh] flex-col lg:h-full lg:min-h-0 lg:overflow-hidden">
                    <div class="flex shrink-0 border-b px-5 dark:border-gray-700">
                        <button type="button" class="finding-detail-tab border-b-2 border-indigo-600 px-4 py-3 text-sm font-bold text-indigo-600" data-tab="issue">⚠ Issue</button>
                        <button type="button" class="finding-detail-tab border-b-2 border-transparent px-4 py-3 text-sm font-bold text-gray-500 dark:text-gray-400" data-tab="comment">▢ Comment <span id="modalCommentCount" class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs text-indigo-700">0</span></button>
                    </div>
                    <div id="findingIssuePanel" class="min-h-0 flex-1 overflow-y-auto p-5"><div id="findingInformation" class="grid grid-cols-1 gap-4 sm:grid-cols-2"></div></div>
                    <div id="findingCommentPanel" class="hidden min-h-0 flex-1 flex-col overflow-hidden">
                        <div id="findingComments" class="min-h-0 flex-1 space-y-3 overflow-y-auto p-5"></div>
                        <form id="findingCommentForm" class="shrink-0 border-t bg-white p-4 dark:border-gray-700 dark:bg-gray-800" enctype="multipart/form-data">
                            @csrf
                            <textarea name="comment" required maxlength="5000" rows="2" class="h-16 w-full resize-none rounded-lg border px-3 py-2 text-sm dark:bg-gray-700" placeholder="Type a comment..."></textarea>
                            <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex min-w-0 items-center gap-3">
                                    <input type="file" name="attachments[]" id="weeklyFindingCommentFiles" multiple class="hidden">
                                    <label for="weeklyFindingCommentFiles" class="cursor-pointer rounded-lg bg-gray-900 px-3 py-2 text-sm font-bold text-white">Choose Files</label>
                                    <span id="weeklyFindingFileLabel" class="truncate text-sm text-gray-500 dark:text-gray-400">No file chosen</span>
                                </div>
                                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white">Send Comment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

    @push('styles')
        <style>
            #momEditor.ql-container {
                min-height: 0;
                overflow: hidden;
            }
            #momEditorForm .ql-toolbar {
                flex-shrink: 0;
            }
            #momEditor .ql-editor {
                height: 100%;
                min-height: 0;
                overflow-y: auto;
            }

            .dark #momEditor,
            .dark #momEditor .ql-editor,
            .dark .ql-toolbar {
                color: #e5e7eb;
                border-color: #4b5563;
            }

            #momEditor .ql-editor img {
                max-width: 100%;
                height: auto;
            }
            #editParticipantRows .select2-container .select2-selection--single {
                height: 44px;
                border-color: #e2e8f0;
                border-radius: .75rem;
                padding: .45rem .75rem;
            }
            #editParticipantRows .select2-selection__rendered { line-height: 26px; }
            #editParticipantRows .select2-selection__arrow { height: 42px; }
            .select2-container--open { z-index: 60; }
        </style>
    @endpush

    @push('scripts')
        <script>
            $(function () {
                const weeklyMeetingViewport = document.getElementById('weeklyMeetingViewport');
                function lockWeeklyMeetingViewport() {
                    if (window.matchMedia('(min-width: 1024px)').matches) {
                        window.scrollTo(0, 0);
                        const top = weeklyMeetingViewport.getBoundingClientRect().top;
                        weeklyMeetingViewport.style.height = `${Math.max(500, window.innerHeight - top)}px`;
                        document.documentElement.style.overflow = 'hidden';
                        document.body.style.overflow = 'hidden';
                    } else {
                        weeklyMeetingViewport.style.height = '';
                        document.documentElement.style.overflow = '';
                        document.body.style.overflow = '';
                    }
                }
                lockWeeklyMeetingViewport();
                window.addEventListener('resize', lockWeeklyMeetingViewport);

                const escapeHtml = value => $('<div>').text(value ?? '-').html();
                const detailUrl = @json(url('/finding'));
                const meetingUsers = @json($users);
                const initialMeetingParticipants = @json($participants->pluck('user_participant')->values());
                const approvalUrl = @json(route('weekly-meeting.approve', $meeting->weeklymeeting_id));
                const meetingCompleted = @json(strtoupper((string) $meeting->status) === 'C');
                let currentFindingId = null;
                const detailFields = [['Company','cpny_id'],['Department','department_id'],['Location','location_name'],['Sub Location','sub_location_name'],['Category','category_name'],['Item','item_name'],['Sub Item','subitem_name'],['Created By','created_by'],['Status','status_label'],['Completed By','completed_by']];
                const momEditor = new Quill('#momEditor', {
                    theme: 'snow',
                    readOnly: meetingCompleted,
                    placeholder: 'Write minute of meeting or paste an image here...',
                    modules: {
                        toolbar: meetingCompleted ? false : [
                            [{ header: [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            [{ color: [] }, { background: [] }],
                            ['blockquote', 'link', 'image'],
                            ['clean']
                        ]
                    }
                });
                $('#momEditorForm').on('submit', function (event) {
                    const html = momEditor.root.innerHTML;
                    if (momEditor.getText().trim() === '' && !momEditor.root.querySelector('img')) {
                        event.preventDefault();
                        Swal.fire('Validation', 'MOM content is required.', 'warning');
                        return;
                    }
                    $('#momEditorInput').val(html);
                    $('#btnSaveMomEditor').prop('disabled', true).text('Saving...');
                });

                const participantOptions = selected => '<option value="">Select Participant</option>' + meetingUsers.map(user =>
                    `<option value="${escapeHtml(user.username)}" ${user.username === selected ? 'selected' : ''}>${escapeHtml(user.name)} (${escapeHtml(user.username)})</option>`
                ).join('');
                function updateEditParticipantCount() {
                    $('#editParticipantCount').text($('#editParticipantRows .edit-participant-row').length);
                }
                function addEditParticipant(selected = '') {
                    const row = $(`
                        <div class="edit-participant-row flex items-center gap-2 rounded-xl border border-gray-100 bg-gray-50/70 p-2 dark:border-gray-700 dark:bg-gray-700/30">
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-gray-400 shadow-sm dark:bg-gray-700">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                            </span>
                            <div class="min-w-0 flex-1"><select name="participants[]" class="edit-participant-select w-full">${participantOptions(selected)}</select></div>
                            <button type="button" class="btnRemoveEditParticipant inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-50 font-bold text-red-600 hover:bg-red-100">&times;</button>
                        </div>
                    `);
                    $('#editParticipantRows').append(row);
                    row.find('.edit-participant-select').select2({
                        dropdownParent: $('#editWeeklyMeetingModal'),
                        placeholder: 'Search participant...',
                        allowClear: true,
                        width: '100%'
                    });
                    updateEditParticipantCount();
                }
                initialMeetingParticipants.forEach(addEditParticipant);
                updateEditParticipantCount();
                $('#btnAddEditParticipant').on('click', () => addEditParticipant());
                $('#editParticipantRows').on('click', '.btnRemoveEditParticipant', function () {
                    const row = $(this).closest('.edit-participant-row');
                    row.find('.edit-participant-select').select2('destroy');
                    row.remove();
                    updateEditParticipantCount();
                });
                $('#btnEditWeeklyMeeting').on('click', () => $('#editWeeklyMeetingModal').removeClass('hidden').addClass('flex'));
                $('.btnCloseEditMeeting').on('click', () => $('#editWeeklyMeetingModal').addClass('hidden').removeClass('flex'));
                $('#editWeeklyMeetingForm').on('submit', async function (event) {
                    event.preventDefault();
                    const button = $('#btnUpdateWeeklyMeeting').prop('disabled', true).text('Updating...');
                    try {
                        const response = await $.ajax({
                            url: this.action,
                            method: 'POST',
                            data: new FormData(this),
                            processData: false,
                            contentType: false
                        });
                        await Swal.fire({icon: 'success', title: 'Updated', text: response.message, confirmButtonColor: '#4f46e5'});
                        window.location.reload();
                    } catch (xhr) {
                        const errors = xhr.responseJSON?.errors;
                        const message = errors ? Object.values(errors).flat().join('\n') : (xhr.responseJSON?.message || 'Failed to update Weekly Meeting.');
                        Swal.fire({icon: 'error', title: 'Update Failed', text: message});
                    } finally {
                        button.prop('disabled', false).text('Update');
                    }
                });
                $('#btnApproveWeeklyMeeting').on('click', async function () {
                    const confirmation = await Swal.fire({
                        icon: 'question',
                        title: 'Approve Weekly Meeting?',
                        text: 'This meeting will be processed to the next approval step.',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Approve',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#059669'
                    });
                    if (!confirmation.isConfirmed) return;

                    const button = $(this).prop('disabled', true);
                    try {
                        const response = await $.post(approvalUrl, {_token: @json(csrf_token())});
                        await Swal.fire({icon: 'success', title: 'Approved', text: response.message, confirmButtonColor: '#059669'});
                        window.location.reload();
                    } catch (xhr) {
                        Swal.fire({icon: 'error', title: 'Approval Failed', text: xhr.responseJSON?.message || 'You cannot approve this meeting.'});
                        button.prop('disabled', false);
                    }
                });

                $('.meeting-finding-tab').on('click', function () {
                    const tab = $(this).data('tab');
                    $('.meeting-finding-tab').removeClass('border-indigo-600 text-indigo-600').addClass('border-transparent text-gray-500');
                    $(this).removeClass('border-transparent text-gray-500').addClass('border-indigo-600 text-indigo-600');
                    $('.finding-tab-panel').addClass('hidden');
                    $(`#${tab}FindingPanel`).removeClass('hidden');
                });
                $('.department-filter-button').on('click', function () {
                    const panel = $(`#${$(this).data('tab')}FindingPanel`);
                    const selected = String($(this).data('department') || '');
                    panel.find('.department-filter-button')
                        .removeClass('active border-indigo-300 bg-indigo-600 text-white')
                        .addClass('border-gray-200 bg-white text-gray-600');
                    $(this)
                        .removeClass('border-gray-200 bg-white text-gray-600')
                        .addClass('active border-indigo-300 bg-indigo-600 text-white');
                    let visible = 0;
                    panel.find('.finding-row').each(function () {
                        const show = !selected || String($(this).data('department')) === selected;
                        $(this).toggle(show);
                        if (show) visible++;
                    });
                    panel.find('.no-filter-result').toggleClass('hidden', visible > 0);
                });
                $('.finding-tab-panel').each(function () {
                    $(this).find('.department-filter-button').first().trigger('click');
                });

                function switchDetailTab(tab) {
                    $('.finding-detail-tab').removeClass('border-indigo-600 text-indigo-600').addClass('border-transparent text-gray-500');
                    $(`.finding-detail-tab[data-tab="${tab}"]`).removeClass('border-transparent text-gray-500').addClass('border-indigo-600 text-indigo-600');
                    $('#findingIssuePanel').toggleClass('hidden', tab !== 'issue');
                    $('#findingCommentPanel').toggleClass('hidden', tab !== 'comment').toggleClass('flex', tab === 'comment');
                }
                function renderInformation(finding) {
                    $('#findingSummary').html(detailFields.map(([label,key]) => `<div><p class="text-xs text-gray-400">${label}</p><p class="mt-1 text-sm font-semibold">${escapeHtml(finding[key])}</p></div>`).join(''));
                    $('#findingInformation').html([['Issue',finding.issue],['Solution',finding.solution],['User Solution',finding.user_solution]].map(([label,value]) => `<div class="sm:col-span-2"><p class="text-xs text-gray-400">${label}</p><div class="mt-1 whitespace-pre-wrap rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-700">${escapeHtml(value)}</div></div>`).join(''));
                }
                function renderPhotos(attachments) {
                    $('#findingPhotos').html(attachments.length ? attachments.map(a => a.is_image && a.url
                        ? `<a href="${escapeHtml(a.url)}" target="_blank" class="overflow-hidden rounded-lg border"><img src="${escapeHtml(a.url)}" class="h-72 w-full bg-gray-100 object-contain dark:bg-gray-900"></a>`
                        : a.url ? `<a href="${escapeHtml(a.url)}" target="_blank" class="rounded-lg border p-4 text-indigo-600">📎 ${escapeHtml(a.name)}</a>` : '').join('')
                        : '<div class="rounded-lg border border-dashed p-8 text-center text-sm text-gray-500 dark:text-gray-400">No photo found.</div>');
                }
                function renderComments(comments) {
                    $('#modalCommentCount').text(comments.length);
                    $('#findingComments').html(comments.length ? comments.map(c => {
                        const files = (c.attachments || []).map(a => a.is_image && a.url
                            ? `<a href="${escapeHtml(a.url)}" target="_blank" class="w-20 overflow-hidden rounded-lg border"><img src="${escapeHtml(a.url)}" class="h-14 w-20 object-cover"></a>`
                            : a.url ? `<a href="${escapeHtml(a.url)}" target="_blank" class="w-full truncate text-sm font-semibold text-indigo-600">📎 ${escapeHtml(a.name)}</a>` : '').join('');
                        return `<div class="rounded-lg border p-3 dark:border-gray-700"><div class="flex justify-between gap-3"><strong class="text-sm">${escapeHtml(c.created_by)}</strong><span class="text-xs text-gray-400">${escapeHtml(c.created_at)}</span></div><p class="mt-2 whitespace-pre-wrap text-sm">${escapeHtml(c.comment)}</p><div class="mt-3 flex flex-wrap gap-2">${files}</div></div>`;
                    }).join('') : '<div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">No comments yet.</div>');
                }
                async function openFinding(id, tab = 'issue') {
                    currentFindingId = id;
                    $('#findingDetailModal').removeClass('hidden').addClass('flex');
                    switchDetailTab(tab);
                    const response = await $.get(`${detailUrl}/${encodeURIComponent(id)}`);
                    $('#detailFindingId').text(response.data.finding_id || '-');
                    $('#detailFindingDate').text(response.data.finding_date_label || '-');
                    renderPhotos(response.attachments || []);
                    renderInformation(response.data);
                    renderComments(response.comments || []);
                }
                $('.btnFindingDetail').on('click', function () { openFinding($(this).data('id'), 'issue'); });
                $('.btnCommentDetail').on('click', function () { openFinding($(this).data('id'), 'comment'); });
                $('.finding-detail-tab').on('click', function () { switchDetailTab($(this).data('tab')); });
                $('.btnCloseFindingModal').on('click', () => $('#findingDetailModal').addClass('hidden').removeClass('flex'));
                $('#findingCommentForm').on('submit', async function (event) {
                    event.preventDefault();
                    await $.ajax({url:`${detailUrl}/${encodeURIComponent(currentFindingId)}/comments`,method:'POST',data:new FormData(this),processData:false,contentType:false});
                    this.reset();
                    $('#weeklyFindingFileLabel').text('No file chosen');
                    await openFinding(currentFindingId, 'comment');
                    location.reload();
                });
                $('#weeklyFindingCommentFiles').on('change', function () {
                    const files = Array.from(this.files || []);
                    $('#weeklyFindingFileLabel').text(files.length === 0 ? 'No file chosen' : files.length === 1 ? files[0].name : `${files.length} files selected`);
                });
            });
        </script>
    @endpush
</x-app-layout>
