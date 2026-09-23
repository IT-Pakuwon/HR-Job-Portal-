<x-app-layout>
    <div class="mx-auto w-full max-w-9xl p-2">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div class="relative overflow-hidden border-b bg-gradient-to-r from-indigo-600 via-indigo-600 to-violet-600 px-6 py-6 text-white dark:border-white/[0.06]">
                <div class="pointer-events-none absolute -right-10 -top-20 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/20">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5V4a2 2 0 0 1 2-2h12v20H6a2 2 0 0 1-2-2.5Z"/><path d="M8 7h6M8 11h6M8 15h4"/></svg>
                    </span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl font-extrabold">Weekly Meeting</h1>
                            <span class="rounded-full bg-white/15 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide ring-1 ring-white/20">Meeting Workspace</span>
                        </div>
                        <p class="mt-1 text-sm text-indigo-100">Manage coordination meetings, attendance, findings, comments, and MOM.</p>
                    </div>
                </div>
                <a href="{{ route('weekly-meeting.create') }}" target="_blank" rel="noopener noreferrer"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-extrabold text-indigo-700 shadow-lg shadow-indigo-900/20 transition hover:-translate-y-0.5 hover:bg-indigo-50 hover:shadow-xl dark:bg-gray-800">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                    Add Weekly Meeting
                </a>
                </div>
            </div>

            <div class="overflow-x-auto px-5 pb-5">
                <table id="weeklyMeetingTable" class="w-full min-w-[850px] border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr class="bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="px-4 py-3 text-left font-medium">Action</th>
                            <th class="px-4 py-3 text-left font-medium">Created At</th>
                            <th class="px-4 py-3 text-left font-medium">Topic</th>
                            <th class="px-4 py-3 text-left font-medium">Comment</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="weeklyCommentModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="flex max-h-[85vh] w-full max-w-xl flex-col overflow-hidden rounded-xl bg-white shadow-xl dark:bg-gray-800">
            <div class="flex items-center justify-between border-b px-5 py-4 dark:border-gray-700">
                <h2 id="weeklyCommentTitle" class="font-extrabold">Comments</h2>
                <button type="button" class="btnCloseWeeklyComment text-2xl text-gray-500 dark:text-gray-400">&times;</button>
            </div>
            <div id="weeklyComments" class="min-h-0 flex-1 space-y-3 overflow-y-auto p-5"></div>
            <form id="weeklyCommentForm" class="shrink-0 border-t p-4 dark:border-gray-700">
                @csrf
                <textarea name="comment" required maxlength="500" rows="3"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700"
                    placeholder="Type a comment..."></textarea>
                <button type="submit" class="mt-3 w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-700">
                    Send Comment
                </button>
            </form>
        </div>
    </div>

    <div id="weeklyMomModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="flex max-h-[88vh] w-full max-w-4xl flex-col overflow-hidden rounded-xl bg-white shadow-xl dark:bg-gray-800">
            <div class="flex shrink-0 items-start justify-between gap-4 bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-5 text-white">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/20">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5V4a2 2 0 0 1 2-2h12v20H6a2 2 0 0 1-2-2.5Z"/><path d="M8 7h6M8 11h6M8 15h4"/></svg>
                    </span>
                    <div class="min-w-0">
                        <h2 id="weeklyMomTitle" class="text-xl font-extrabold">Minute of Meeting</h2>
                        <p id="weeklyMomTopic" class="mt-1 truncate text-sm font-semibold text-indigo-50">-</p>
                        <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
                            <span id="weeklyMomId" class="rounded-full bg-white/15 px-2.5 py-1 ring-1 ring-white/20">-</span>
                            <span id="weeklyMomDate" class="rounded-full bg-white/15 px-2.5 py-1 ring-1 ring-white/20">-</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btnCloseWeeklyMom inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/10 text-2xl text-white transition hover:bg-white/20">&times;</button>
            </div>
            <div class="min-h-0 flex-1 overflow-y-auto bg-gray-50 p-6 dark:bg-gray-900/40">
                <div id="weeklyMomLoading" class="p-10 text-center text-sm text-gray-500 dark:text-gray-400">Loading MOM...</div>
                <div id="weeklyMomContent" class="ql-editor hidden min-h-48 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800"></div>
            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">

    @push('styles')
        <style>
            #weeklyMomContent img {
                max-width: 100%;
                height: auto;
            }

            #weeklyMeetingTable_wrapper .dataTables_length,
            #weeklyMeetingTable_wrapper .dataTables_filter,
            #weeklyMeetingTable_wrapper .dt-length,
            #weeklyMeetingTable_wrapper .dt-search {
                margin-top: 1.25rem;
                margin-bottom: 1rem;
                color: #64748b;
                font-size: .875rem;
            }

            #weeklyMeetingTable_wrapper .dataTables_filter input,
            #weeklyMeetingTable_wrapper .dt-search input,
            #weeklyMeetingTable_wrapper .dataTables_length select,
            #weeklyMeetingTable_wrapper .dt-length select {
                border: 1px solid #e2e8f0;
                border-radius: .75rem;
                background: #fff;
                padding: .5rem .75rem;
                outline: none;
                transition: .2s ease;
            }

            #weeklyMeetingTable_wrapper .dataTables_filter input:focus,
            #weeklyMeetingTable_wrapper .dt-search input:focus {
                border-color: #818cf8;
                box-shadow: 0 0 0 3px rgb(99 102 241 / .12);
            }

            #weeklyMeetingTable tbody tr {
                transition: background-color .18s ease, transform .18s ease;
            }

            #weeklyMeetingTable tbody tr:hover {
                background: #f8faff;
            }

            #weeklyMeetingTable_wrapper .dataTables_info,
            #weeklyMeetingTable_wrapper .dt-info {
                padding-top: 1rem;
                color: #64748b;
                font-size: .875rem;
            }

            #weeklyMeetingTable_wrapper .dataTables_paginate,
            #weeklyMeetingTable_wrapper .dt-paging {
                padding-top: .75rem;
            }

            #weeklyMeetingTable_wrapper .paginate_button,
            #weeklyMeetingTable_wrapper .dt-paging-button {
                border-radius: .6rem !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            $(function () {
                const escapeHtml = value => $('<div>').text(value ?? '-').html();
                const baseUrl = @json(url('/weekly-meeting'));
                let activeMeetingId = null;
                const iconButton = 'inline-flex h-9 w-9 items-center justify-center rounded-xl border shadow-sm transition hover:-translate-y-0.5 hover:shadow';
                const table = $('#weeklyMeetingTable').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 25,
                    order: [[1, 'desc']],
                    ajax: @json(route('weekly-meeting.json')),
                    columns: [
                        {
                            data: 'weeklymeeting_id',
                            orderable: false,
                            searchable: false,
                            render: id => `<div class="flex items-center gap-1.5">
                                <button type="button" class="btnWeeklyComment ${iconButton} border-red-200 bg-red-50 text-red-600 hover:bg-red-100" data-id="${escapeHtml(id)}" title="Comment">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"/></svg>
                                </button>
                                <button type="button" class="btnWeeklyMom ${iconButton} border-emerald-200 bg-emerald-50 text-emerald-600 hover:bg-emerald-100" data-id="${escapeHtml(id)}" title="Minute of Meeting">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5V4a2 2 0 0 1 2-2h12v20H6a2 2 0 0 1-2-2.5Z"/><path d="M8 7h6M8 11h6M8 15h4"/></svg>
                                </button>
                                <a href="${baseUrl}/${encodeURIComponent(id)}?export=1" target="_blank" class="${iconButton} border-amber-200 bg-amber-50 text-amber-600 hover:bg-amber-100" title="Export">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14"/></svg>
                                </a>
                            </div>`
                        },
                        { data: 'weeklymeeting_date', defaultContent: '-' },
                        {
                            data: 'weeklymeeting_topic',
                            render: (value, type, row) => type === 'display'
                                ? `<a href="${baseUrl}/${encodeURIComponent(row.weeklymeeting_id)}" target="_blank" rel="noopener noreferrer" class="font-bold text-indigo-700 hover:underline">${escapeHtml(value)}</a>`
                                : value
                        },
                        {
                            data: 'comments_count',
                            orderable: false,
                            searchable: false,
                            render: (count, type, row) => `<button type="button" class="btnWeeklyComment inline-flex min-w-7 items-center justify-center gap-1 rounded-full bg-red-100 px-2 py-1 text-xs font-bold text-red-700 hover:bg-red-200" data-id="${escapeHtml(row.weeklymeeting_id)}" title="Open comments">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"/></svg>
                                ${Number(count || 0)}
                            </button>`
                        },
                        {
                            data: 'status_label',
                            render: status => status === 'Completed'
                                ? '<span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">Completed</span>'
                                : '<span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">Open</span>'
                        }
                    ]
                });

                async function loadComments() {
                    $('#weeklyComments').html('<div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">Loading...</div>');
                    const response = await $.get(`/comments/WOM/${encodeURIComponent(activeMeetingId)}`);
                    const comments = response.comments || [];
                    $('#weeklyComments').html(comments.length ? comments.map(comment => `
                        <div class="rounded-lg border p-3 dark:border-gray-700">
                            <div class="flex justify-between gap-3">
                                <strong class="text-sm">${escapeHtml(comment.name || comment.username)}</strong>
                                <span class="text-xs text-gray-400">${escapeHtml(comment.message_date)}</span>
                            </div>
                            <p class="mt-2 whitespace-pre-wrap text-sm">${escapeHtml(comment.message)}</p>
                        </div>
                    `).join('') : '<div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">No comments yet.</div>');
                }

                $('#weeklyMeetingTable').on('click', '.btnWeeklyComment', async function () {
                    activeMeetingId = $(this).data('id');
                    $('#weeklyCommentTitle').text(`Comments - ${activeMeetingId}`);
                    $('#weeklyCommentModal').removeClass('hidden').addClass('flex');
                    await loadComments();
                });
                $('#weeklyMeetingTable').on('click', '.btnWeeklyMom', async function () {
                    const meetingId = $(this).data('id');
                    $('#weeklyMomModal').removeClass('hidden').addClass('flex');
                    $('#weeklyMomTitle').text('Minute of Meeting');
                    $('#weeklyMomTopic').text('-');
                    $('#weeklyMomId').text(meetingId);
                    $('#weeklyMomDate').text('Loading...');
                    $('#weeklyMomLoading').removeClass('hidden').text('Loading MOM...');
                    $('#weeklyMomContent').addClass('hidden').empty();

                    try {
                        const response = await $.get(`${baseUrl}/${encodeURIComponent(meetingId)}/mom`);
                        $('#weeklyMomTitle').text('Minute of Meeting');
                        $('#weeklyMomTopic').text(response.topic || '-');
                        $('#weeklyMomId').text(response.weeklymeeting_id || meetingId);
                        $('#weeklyMomDate').text(response.date || '-');
                        $('#weeklyMomLoading').addClass('hidden');
                        if (response.content) {
                            $('#weeklyMomContent').html(response.content).removeClass('hidden');
                        } else {
                            $('#weeklyMomLoading').removeClass('hidden').text('No MOM content available.');
                        }
                    } catch (error) {
                        $('#weeklyMomLoading').removeClass('hidden').text(error.responseJSON?.message || 'MOM could not be loaded.');
                    }
                });
                $('.btnCloseWeeklyComment').on('click', () => $('#weeklyCommentModal').addClass('hidden').removeClass('flex'));
                $('.btnCloseWeeklyMom').on('click', () => $('#weeklyMomModal').addClass('hidden').removeClass('flex'));
                $('#weeklyCommentForm').on('submit', async function (event) {
                    event.preventDefault();
                    await $.post(`/comments/WOM/${encodeURIComponent(activeMeetingId)}`, $(this).serialize());
                    this.reset();
                    await loadComments();
                    table.ajax.reload(null, false);
                });
            });
        </script>
    @endpush
</x-app-layout>
