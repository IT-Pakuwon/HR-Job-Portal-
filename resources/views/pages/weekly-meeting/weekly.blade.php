<x-app-layout>
    <div class="mx-auto w-full max-w-9xl p-2">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-white/[0.06] sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-extrabold text-gray-800 dark:text-white">Weekly Meeting</h1>
                    <p class="mt-1 text-sm text-gray-500">Weekly coordination meeting records.</p>
                </div>
                <a href="{{ route('weekly-meeting.create') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-700">
                    + Add Weekly Meeting
                </a>
            </div>

            <div class="overflow-x-auto">
                <table id="weeklyMeetingTable" class="w-full min-w-[850px] border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr class="bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="px-4 py-3 text-left font-medium">Action</th>
                            <th class="px-4 py-3 text-left font-medium">Date</th>
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
                <button type="button" class="btnCloseWeeklyComment text-2xl text-gray-500">&times;</button>
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

    @push('scripts')
        <script>
            $(function () {
                const escapeHtml = value => $('<div>').text(value ?? '-').html();
                const baseUrl = @json(url('/weekly-meeting'));
                let activeMeetingId = null;
                const iconButton = 'inline-flex h-8 w-8 items-center justify-center rounded-lg border transition';
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
                                <a href="${baseUrl}/${encodeURIComponent(id)}" class="${iconButton} border-indigo-200 bg-indigo-50 text-indigo-600 hover:bg-indigo-100" title="View">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <a href="${baseUrl}/${encodeURIComponent(id)}#mom" class="${iconButton} border-emerald-200 bg-emerald-50 text-emerald-600 hover:bg-emerald-100" title="Minute of Meeting">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5V4a2 2 0 0 1 2-2h12v20H6a2 2 0 0 1-2-2.5Z"/><path d="M8 7h6M8 11h6M8 15h4"/></svg>
                                </a>
                                <a href="${baseUrl}/${encodeURIComponent(id)}?export=1" target="_blank" class="${iconButton} border-amber-200 bg-amber-50 text-amber-600 hover:bg-amber-100" title="Export">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14"/></svg>
                                </a>
                            </div>`
                        },
                        { data: 'weeklymeeting_date', defaultContent: '-' },
                        {
                            data: 'weeklymeeting_topic',
                            render: (value, type, row) => type === 'display'
                                ? `<a href="${baseUrl}/${encodeURIComponent(row.weeklymeeting_id)}" class="font-bold text-indigo-700 hover:underline">${escapeHtml(value)}</a>`
                                : value
                        },
                        {
                            data: 'comments_count',
                            orderable: false,
                            searchable: false,
                            render: count => `<span class="inline-flex min-w-7 items-center justify-center rounded-full bg-red-100 px-2 py-1 text-xs font-bold text-red-700">${Number(count || 0)}</span>`
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
                    $('#weeklyComments').html('<div class="p-8 text-center text-sm text-gray-500">Loading...</div>');
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
                    `).join('') : '<div class="p-8 text-center text-sm text-gray-500">No comments yet.</div>');
                }

                $('#weeklyMeetingTable').on('click', '.btnWeeklyComment', async function () {
                    activeMeetingId = $(this).data('id');
                    $('#weeklyCommentTitle').text(`Comments - ${activeMeetingId}`);
                    $('#weeklyCommentModal').removeClass('hidden').addClass('flex');
                    await loadComments();
                });
                $('.btnCloseWeeklyComment').on('click', () => $('#weeklyCommentModal').addClass('hidden').removeClass('flex'));
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
