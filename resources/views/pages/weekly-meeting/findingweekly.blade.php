<x-app-layout>
    <div class="mx-auto w-full max-w-7xl p-3">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b px-6 py-5 dark:border-gray-700">
                <h1 class="text-xl font-extrabold text-gray-800 dark:text-white">{{ $meeting->weeklymeeting_id }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $meeting->weeklymeeting_topic }} · {{ $meeting->weeklymeeting_date->format('d M Y') }}</p>
                <p class="mt-1 text-xs text-gray-400">Finding period: {{ $fromDate->format('d M Y') }} – {{ $toDate->format('d M Y') }}</p>
                <div class="mt-4">
                    <p class="mb-2 text-xs font-bold uppercase tracking-wide text-gray-400">Attendance</p>
                    <div class="flex flex-wrap gap-2">
                        @forelse ($participants as $participant)
                            <span class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700">
                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-indigo-600 text-[10px] font-bold text-white">
                                    {{ strtoupper(substr($participant->name_participant ?: $participant->user_participant, 0, 1)) }}
                                </span>
                                {{ $participant->name_participant ?: $participant->user_participant }}
                            </span>
                        @empty
                            <span class="text-sm text-gray-500">No attendance.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="flex overflow-x-auto border-b px-5 dark:border-gray-700">
                <button type="button" class="meeting-finding-tab shrink-0 border-b-2 border-indigo-600 px-4 py-3 text-sm font-bold text-indigo-600" data-tab="open">
                    <span class="inline-flex items-center gap-2"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2h8l4 4v16H4V2h4Z"/><path d="M8 2v5h8V2M8 13h8M8 17h5"/></svg>Open Finding ({{ $openFindings->count() }})</span>
                </button>
                <button type="button" class="meeting-finding-tab shrink-0 border-b-2 border-transparent px-4 py-3 text-sm font-bold text-gray-500" data-tab="close">
                    <span class="inline-flex items-center gap-2"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>Close Finding ({{ $closeFindings->count() }})</span>
                </button>
                <button type="button" class="meeting-finding-tab shrink-0 border-b-2 border-transparent px-4 py-3 text-sm font-bold text-gray-500" data-tab="project">
                    <span class="inline-flex items-center gap-2"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 10h6M9 14h6M9 18h6"/></svg>Project Finding</span>
                </button>
            </div>

            @foreach (['open' => [$openFindings, $openDepartments], 'close' => [$closeFindings, $closeDepartments]] as $tab => [$findings, $departments])
                <div id="{{ $tab }}FindingPanel" class="finding-tab-panel {{ $tab !== 'open' ? 'hidden' : '' }} p-5">
                    <div class="mb-4 flex justify-end">
                        <select class="department-filter rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700" data-tab="{{ $tab }}">
                            <option value="">All Departments</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department }}">{{ $department }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[950px] text-sm">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-700">
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
                                    <tr><td colspan="6" class="px-3 py-10 text-center text-gray-500">No findings in this period.</td></tr>
                                @endforelse
                                <tr class="no-filter-result hidden"><td colspan="6" class="px-3 py-10 text-center text-gray-500">No findings for this department.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            <div id="projectFindingPanel" class="finding-tab-panel hidden p-10 text-center text-gray-500">Project Finding table is not available yet.</div>
        </div>
    </div>

    <div id="findingDetailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="flex h-[92vh] max-h-[92vh] w-full max-w-6xl flex-col overflow-hidden rounded-xl bg-white shadow-xl dark:bg-gray-800">
            <div class="flex shrink-0 items-center justify-between border-b px-5 py-4 dark:border-gray-700">
                <div><h2 id="detailFindingId" class="text-lg font-extrabold">Finding Detail</h2><p id="detailFindingDate" class="mt-1 text-xs text-gray-500">-</p></div>
                <button type="button" class="btnCloseFindingModal text-2xl text-gray-500">&times;</button>
            </div>
            <div class="grid min-h-0 flex-1 grid-cols-1 overflow-y-auto lg:grid-cols-2 lg:overflow-hidden">
                <div class="overflow-y-auto border-b p-5 lg:border-b-0 lg:border-r dark:border-gray-700">
                    <div id="findingPhotos" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-1"></div>
                    <div id="findingSummary" class="mt-6 grid grid-cols-1 gap-4 border-t pt-5 sm:grid-cols-2 dark:border-gray-700"></div>
                </div>
                <div class="flex min-h-[70vh] flex-col lg:h-full lg:min-h-0 lg:overflow-hidden">
                    <div class="flex shrink-0 border-b px-5 dark:border-gray-700">
                        <button type="button" class="finding-detail-tab border-b-2 border-indigo-600 px-4 py-3 text-sm font-bold text-indigo-600" data-tab="issue">⚠ Issue</button>
                        <button type="button" class="finding-detail-tab border-b-2 border-transparent px-4 py-3 text-sm font-bold text-gray-500" data-tab="comment">▢ Comment <span id="modalCommentCount" class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs text-indigo-700">0</span></button>
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
                                    <span id="weeklyFindingFileLabel" class="truncate text-sm text-gray-500">No file chosen</span>
                                </div>
                                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white">Send Comment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(function () {
                const escapeHtml = value => $('<div>').text(value ?? '-').html();
                const detailUrl = @json(url('/finding'));
                let currentFindingId = null;
                const detailFields = [['Company','cpny_id'],['Department','department_id'],['Location','location_name'],['Sub Location','sub_location_name'],['Category','category_name'],['Item','item_name'],['Sub Item','subitem_name'],['Created By','created_by'],['Status','status_label'],['Completed By','completed_by']];

                $('.meeting-finding-tab').on('click', function () {
                    const tab = $(this).data('tab');
                    $('.meeting-finding-tab').removeClass('border-indigo-600 text-indigo-600').addClass('border-transparent text-gray-500');
                    $(this).removeClass('border-transparent text-gray-500').addClass('border-indigo-600 text-indigo-600');
                    $('.finding-tab-panel').addClass('hidden');
                    $(`#${tab}FindingPanel`).removeClass('hidden');
                });
                $('.department-filter').on('change', function () {
                    const panel = $(`#${$(this).data('tab')}FindingPanel`);
                    const selected = $(this).val();
                    let visible = 0;
                    panel.find('.finding-row').each(function () {
                        const show = !selected || $(this).data('department') === selected;
                        $(this).toggle(show);
                        if (show) visible++;
                    });
                    panel.find('.no-filter-result').toggleClass('hidden', visible > 0);
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
                        ? `<a href="${escapeHtml(a.url)}" target="_blank" class="overflow-hidden rounded-lg border"><img src="${escapeHtml(a.url)}" class="h-72 w-full bg-gray-100 object-contain"></a>`
                        : a.url ? `<a href="${escapeHtml(a.url)}" target="_blank" class="rounded-lg border p-4 text-indigo-600">📎 ${escapeHtml(a.name)}</a>` : '').join('')
                        : '<div class="rounded-lg border border-dashed p-8 text-center text-sm text-gray-500">No photo found.</div>');
                }
                function renderComments(comments) {
                    $('#modalCommentCount').text(comments.length);
                    $('#findingComments').html(comments.length ? comments.map(c => {
                        const files = (c.attachments || []).map(a => a.is_image && a.url
                            ? `<a href="${escapeHtml(a.url)}" target="_blank" class="w-20 overflow-hidden rounded-lg border"><img src="${escapeHtml(a.url)}" class="h-14 w-20 object-cover"></a>`
                            : a.url ? `<a href="${escapeHtml(a.url)}" target="_blank" class="w-full truncate text-sm font-semibold text-indigo-600">📎 ${escapeHtml(a.name)}</a>` : '').join('');
                        return `<div class="rounded-lg border p-3 dark:border-gray-700"><div class="flex justify-between gap-3"><strong class="text-sm">${escapeHtml(c.created_by)}</strong><span class="text-xs text-gray-400">${escapeHtml(c.created_at)}</span></div><p class="mt-2 whitespace-pre-wrap text-sm">${escapeHtml(c.comment)}</p><div class="mt-3 flex flex-wrap gap-2">${files}</div></div>`;
                    }).join('') : '<div class="p-8 text-center text-sm text-gray-500">No comments yet.</div>');
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
