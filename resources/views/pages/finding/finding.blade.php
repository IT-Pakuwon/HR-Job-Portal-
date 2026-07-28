<x-app-layout>
    <div class="mx-auto w-full max-w-9xl p-2">
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $cards = [
                    ['my', 'My Finding', $myFinding, 'border-indigo-600 bg-indigo-50 text-indigo-700'],
                    ['open', 'Open', $openFinding, 'border-amber-600 bg-amber-50 text-amber-700'],
                    ['close', 'Close', $closeFinding, 'border-emerald-600 bg-emerald-50 text-emerald-700'],
                    ['all', 'All', $allFinding, 'border-slate-600 bg-slate-100 text-slate-700'],
                ];
            @endphp

            @foreach ($cards as [$filter, $label, $count, $color])
                <a href="#" class="status-filter block h-full {{ $filter === 'my' ? 'active' : '' }}"
                    data-filter="{{ $filter }}" data-label="{{ $label }}">
                    <div class="status-card flex h-full items-center justify-between gap-3 rounded-lg border p-4 transition hover:-translate-y-1 hover:shadow-md {{ $color }}">
                        <p class="text-sm font-semibold">{{ $label }}</p>
                        <p class="text-xl font-bold">{{ number_format($count) }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-4 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-3 dark:border-white/[0.06] xl:flex-row xl:items-center xl:justify-between">
                <h1 id="tableTitle" class="text-base font-extrabold text-gray-700 dark:text-white">My Finding</h1>
                <div class="flex flex-wrap items-center gap-2">
                    <select id="filterDepartment" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700">
                        <option value="">All Departments</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department }}">{{ $department }}</option>
                        @endforeach
                    </select>
                    <select id="filterLocation" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700">
                        <option value="">All Locations</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->location_id }}">{{ $location->location_name ?: $location->location_id }}</option>
                        @endforeach
                    </select>
                    <select id="filterCategory" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->categoryid }}">{{ $category->category_name ?: $category->categoryid }}</option>
                        @endforeach
                    </select>
                    <select id="filterItem" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700">
                        <option value="">All Items</option>
                        @foreach ($findingItems as $item)
                            <option value="{{ $item->finding_item }}">{{ $item->finding_name ?: $item->finding_item }}</option>
                        @endforeach
                    </select>
                    <select id="filterStatus" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700">
                        <option value="">All Statuses</option>
                        <option value="open">Open</option>
                        <option value="close">Close</option>
                    </select>
                    <button type="button" id="btnResetFindingFilters"
                        class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                        Reset
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="findingTable" class="w-full min-w-[1100px] border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr class="bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="px-4 py-3 text-left font-medium">Finding ID</th>
                            <th class="px-4 py-3 text-left font-medium">Date</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Department</th>
                            <th class="px-4 py-3 text-left font-medium">Location</th>
                            <th class="px-4 py-3 text-left font-medium">Category</th>
                            <th class="px-4 py-3 text-left font-medium">Item</th>
                            <th class="px-4 py-3 text-left font-medium">Issue</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                            <th class="px-4 py-3 text-left font-medium">Created By</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="findingDetailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="flex h-[92vh] max-h-[92vh] w-full max-w-6xl flex-col overflow-hidden rounded-xl bg-white shadow-xl dark:bg-gray-800">
            <div class="flex items-center justify-between border-b px-5 py-4 dark:border-gray-700">
                <div>
                    <h2 id="detailFindingId" class="text-lg font-extrabold text-gray-900 dark:text-white">Finding Detail</h2>
                    <p id="detailFindingDate" class="mt-1 text-xs text-gray-500">-</p>
                </div>
                <button type="button" class="btnCloseFindingModal text-2xl leading-none text-gray-500 hover:text-gray-800 dark:hover:text-white">&times;</button>
            </div>

            <div class="grid min-h-0 flex-1 grid-cols-1 overflow-y-auto lg:grid-cols-2 lg:overflow-hidden">
                <div class="overflow-y-auto border-b p-5 dark:border-gray-700 lg:border-b-0 lg:border-r">
                    <div id="findingPhotos" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-1"></div>
                    <div id="findingSummary" class="mt-6 grid grid-cols-1 gap-4 border-t pt-5 dark:border-gray-700 sm:grid-cols-2"></div>
                </div>
                <div class="flex min-h-[70vh] flex-col lg:h-full lg:min-h-0 lg:max-h-full lg:overflow-hidden">
                    <div class="flex shrink-0 border-b px-5 dark:border-gray-700">
                        <button type="button" class="finding-tab border-b-2 border-indigo-600 px-4 py-3 text-sm font-bold text-indigo-600" data-tab="issue">
                            <span class="inline-flex items-center gap-2">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4m0 4h.01M10.3 3.6 2.2 18a2 2 0 0 0 1.8 3h16a2 2 0 0 0 1.8-3L13.7 3.6a2 2 0 0 0-3.4 0Z"/></svg>
                                Issue
                            </span>
                        </button>
                        <button type="button" class="finding-tab border-b-2 border-transparent px-4 py-3 text-sm font-bold text-gray-500" data-tab="comment">
                            <span class="inline-flex items-center gap-2">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"/></svg>
                                Comment <span id="modalCommentCount" class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs text-indigo-700">0</span>
                            </span>
                        </button>
                    </div>
                    <div id="findingIssuePanel" class="min-h-0 flex-1 overflow-y-auto p-5">
                        <div id="findingInformation" class="grid grid-cols-1 gap-4 sm:grid-cols-2"></div>
                    </div>
                    <div id="findingCommentPanel" class="hidden min-h-0 flex-1 flex-col overflow-hidden">
                        <div id="findingComments" class="min-h-[340px] flex-1 space-y-3 overflow-y-auto overscroll-contain p-4 sm:p-5 lg:min-h-0"></div>
                        <form id="findingCommentForm" class="shrink-0 border-t bg-white p-3 dark:border-gray-700 dark:bg-gray-800 sm:p-4" enctype="multipart/form-data">
                            @csrf
                            <textarea name="comment" id="findingCommentInput" rows="2" required maxlength="5000"
                                class="h-16 w-full resize-none rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 sm:h-20"
                                placeholder="Type a comment..."></textarea>
                            <div class="mt-2 flex flex-col gap-2 sm:mt-3 sm:flex-row sm:items-center sm:justify-between sm:gap-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <input type="file" name="attachments[]" id="findingCommentAttachments" multiple class="hidden">
                                    <label for="findingCommentAttachments"
                                        class="shrink-0 cursor-pointer rounded-lg bg-gray-900 px-3 py-2 text-sm font-bold text-white transition hover:bg-gray-700">
                                        Choose Files
                                    </label>
                                    <span id="findingCommentFileLabel" class="truncate text-sm text-gray-500">No file chosen</span>
                                </div>
                                <button type="submit" id="btnSaveFindingComment"
                                    class="shrink-0 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-700">
                                    Send Comment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .status-filter.active .status-card {
                box-shadow: 0 0 0 3px rgb(99 102 241 / .22);
                transform: translateY(-2px);
            }

        </style>
    @endpush

    @push('scripts')
        <script>
            $(function () {
                let activeFilter = 'my';
                const escapeHtml = value => $('<div>').text(value ?? '-').html();
                const table = $('#findingTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: false,
                    pageLength: 25,
                    order: [[1, 'desc']],
                    ajax: {
                        url: @json(route('finding.json')),
                        data: data => {
                            data.filter = activeFilter;
                            data.department_id = $('#filterDepartment').val();
                            data.location_id = $('#filterLocation').val();
                            data.finding_category = $('#filterCategory').val();
                            data.finding_item = $('#filterItem').val();
                            data.status = $('#filterStatus').val();
                        }
                    },
                    columns: [
                        {
                            data: 'finding_id',
                            render: (data, type, row) => type === 'display'
                                ? `<div class="inline-flex items-center gap-2">
                                    <button type="button" class="btnFindingDetail inline-flex items-center rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 font-bold text-indigo-700 transition hover:bg-indigo-100" data-id="${escapeHtml(data)}">${escapeHtml(data || '-')}</button>
                                    <button type="button" class="btnCommentDetail inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-2 py-1.5 text-xs font-bold text-red-600 transition hover:bg-red-100 hover:text-red-700" data-id="${escapeHtml(data)}" title="Open comments">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"/></svg>
                                        ${Number(row.comments_count || 0)}
                                    </button>
                                </div>`
                                : data
                        },
                        { data: 'finding_date_label', name: 'finding_date', defaultContent: '-' },
                        { data: 'cpny_id', defaultContent: '-' },
                        { data: 'department_id', defaultContent: '-' },
                        { data: 'location_name', name: 'location_id', defaultContent: '-' },
                        { data: 'category_name', name: 'finding_category', defaultContent: '-' },
                        { data: 'item_name', name: 'finding_item', defaultContent: '-' },
                        {
                            data: 'issue',
                            defaultContent: '-',
                            render: (data, type) => type === 'display'
                                ? `<span class="block max-w-xs truncate" title="${escapeHtml(data)}">${escapeHtml(data)}</span>`
                                : data
                        },
                        {
                            data: 'status_label',
                            render: data => data === 'Close'
                                ? '<span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">Close</span>'
                                : '<span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">Open</span>'
                        },
                        { data: 'created_by', defaultContent: '-' }
                    ]
                });

                const $detailModal = $('#findingDetailModal');
                const detailUrl = @json(url('/finding'));
                let currentFindingId = null;
                const detailFields = [
                    ['Company', 'cpny_id'],
                    ['Department', 'department_id'],
                    ['Location', 'location_name'],
                    ['Sub Location', 'sub_location_name'],
                    ['Category', 'category_name'],
                    ['Item', 'item_name'],
                    ['Sub Item', 'subitem_name'],
                    ['Created By', 'created_by'],
                    ['Status', 'status_label'],
                    ['Completed By', 'completed_by'],
                ];

                function closeFindingModal() {
                    $detailModal.addClass('hidden').removeClass('flex');
                }

                function renderFindingInformation(finding) {
                    const fields = detailFields.map(([label, key]) => `
                        <div>
                            <p class="text-xs text-gray-400">${label}</p>
                            <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-100">${escapeHtml(finding[key])}</p>
                        </div>
                    `).join('');
                    $('#findingSummary').html(fields);

                    const descriptions = [
                        ['Issue', finding.issue],
                        ['Solution', finding.solution],
                        ['User Solution', finding.user_solution],
                    ].map(([label, value]) => `
                        <div class="sm:col-span-2">
                            <p class="text-xs text-gray-400">${label}</p>
                            <div class="mt-1 whitespace-pre-wrap rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-700">${escapeHtml(value)}</div>
                        </div>
                    `).join('');

                    $('#findingInformation').html(descriptions);
                }

                function renderFindingPhotos(attachments) {
                    const $photos = $('#findingPhotos');
                    if (!attachments.length) {
                        $photos.html('<div class="rounded-lg border border-dashed p-8 text-center text-sm text-gray-500">No photo found.</div>');
                        return;
                    }

                    $photos.html(attachments.map(attachment => {
                        if (attachment.is_image && attachment.url) {
                            return `<a href="${escapeHtml(attachment.url)}" target="_blank" rel="noopener noreferrer" class="group overflow-hidden rounded-lg border dark:border-gray-700">
                                <img src="${escapeHtml(attachment.url)}" alt="${escapeHtml(attachment.name)}" class="h-72 w-full bg-gray-100 object-contain transition group-hover:scale-[1.02]">
                            </a>`;
                        }

                        return attachment.url
                            ? `<a href="${escapeHtml(attachment.url)}" target="_blank" rel="noopener noreferrer" class="rounded-lg border p-4 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 dark:border-gray-700">📎 ${escapeHtml(attachment.name)}</a>`
                            : `<div class="rounded-lg border p-4 text-sm text-gray-500 dark:border-gray-700">📎 ${escapeHtml(attachment.name)} (preview unavailable)</div>`;
                    }).join(''));
                }

                function renderFindingComments(comments) {
                    $('#modalCommentCount').text(comments.length);
                    const commentHtml = comments.length
                        ? comments.map(comment => {
                            const attachments = comment.attachments || [];
                            const attachmentHtml = attachments.length
                                ? `<div class="mt-3 flex flex-wrap items-start gap-2">
                                    ${attachments.map(attachment => {
                                        if (attachment.is_image && attachment.url) {
                                            return `<a href="${escapeHtml(attachment.url)}" target="_blank" rel="noopener noreferrer" class="w-20 overflow-hidden rounded-lg border dark:border-gray-600 sm:w-24">
                                                <img src="${escapeHtml(attachment.url)}" alt="${escapeHtml(attachment.name)}" class="h-14 w-20 bg-gray-100 object-cover sm:h-16 sm:w-24">
                                            </a>`;
                                        }
                                        return attachment.url
                                            ? `<a href="${escapeHtml(attachment.url)}" target="_blank" rel="noopener noreferrer" class="w-full truncate rounded-lg bg-gray-50 px-3 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 dark:bg-gray-700">📎 ${escapeHtml(attachment.name)}</a>`
                                            : `<div class="w-full truncate rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-500 dark:bg-gray-700">📎 ${escapeHtml(attachment.name)}</div>`;
                                    }).join('')}
                                </div>`
                                : '';
                            return `
                            <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-100">${escapeHtml(comment.created_by)}</span>
                                    <span class="text-xs text-gray-400">${escapeHtml(comment.created_at)}</span>
                                </div>
                                <p class="mt-2 whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-200">${escapeHtml(comment.comment)}</p>
                                ${attachmentHtml}
                            </div>
                        `}).join('')
                        : '<div class="rounded-lg border border-dashed p-8 text-center text-sm text-gray-500">No comments yet.</div>';
                    $('#findingComments').html(commentHtml);
                }

                function switchFindingTab(tab) {
                    $('.finding-tab')
                        .removeClass('border-indigo-600 text-indigo-600')
                        .addClass('border-transparent text-gray-500');
                    $(`.finding-tab[data-tab="${tab}"]`)
                        .removeClass('border-transparent text-gray-500')
                        .addClass('border-indigo-600 text-indigo-600');
                    $('#findingIssuePanel').toggleClass('hidden', tab !== 'issue');
                    $('#findingCommentPanel')
                        .toggleClass('hidden', tab !== 'comment')
                        .toggleClass('flex', tab === 'comment');
                }

                async function openFinding(findingId, tab = 'issue') {
                    currentFindingId = findingId;
                    $detailModal.removeClass('hidden').addClass('flex');
                    switchFindingTab(tab);
                    $('#detailFindingId').text(findingId);
                    $('#detailFindingDate').text('Loading...');
                    $('#findingPhotos').html('<div class="p-8 text-center text-sm text-gray-500">Loading photos...</div>');
                    $('#findingSummary').html('<div class="p-4 text-center text-sm text-gray-500 sm:col-span-2">Loading summary...</div>');
                    $('#findingInformation').html('<div class="p-8 text-center text-sm text-gray-500">Loading information...</div>');
                    $('#findingComments').html('<div class="p-8 text-center text-sm text-gray-500">Loading comments...</div>');

                    try {
                        const response = await $.get(`${detailUrl}/${encodeURIComponent(findingId)}`);
                        $('#detailFindingId').text(response.data.finding_id || '-');
                        $('#detailFindingDate').text(response.data.finding_date_label || '-');
                        renderFindingPhotos(response.attachments || []);
                        renderFindingInformation(response.data);
                        renderFindingComments(response.comments || []);
                    } catch (error) {
                        closeFindingModal();
                        Swal.fire('Error', error.responseJSON?.message || 'Finding detail could not be loaded.', 'error');
                    }
                }

                $('#findingTable').on('click', '.btnFindingDetail', function () {
                    openFinding($(this).data('id'), 'issue');
                });
                $('#findingTable').on('click', '.btnCommentDetail', function () {
                    openFinding($(this).data('id'), 'comment');
                });
                $('.finding-tab').on('click', function () {
                    switchFindingTab($(this).data('tab'));
                });

                $('#findingCommentForm').on('submit', async function (event) {
                    event.preventDefault();
                    if (!currentFindingId) return;
                    const $button = $('#btnSaveFindingComment');
                    $button.prop('disabled', true).text('Sending...');

                    try {
                        await $.ajax({
                            url: `${detailUrl}/${encodeURIComponent(currentFindingId)}/comments`,
                            method: 'POST',
                            data: new FormData(this),
                            processData: false,
                            contentType: false,
                        });
                        this.reset();
                        $('#findingCommentFileLabel').text('No file chosen');
                        await openFinding(currentFindingId, 'comment');
                        table.ajax.reload(null, false);
                    } catch (error) {
                        const errors = error.responseJSON?.errors;
                        const message = errors ? Object.values(errors).flat()[0] : (error.responseJSON?.message || 'Comment could not be saved.');
                        Swal.fire('Error', message, 'error');
                    } finally {
                        $button.prop('disabled', false).text('Send Comment');
                    }
                });

                $('#findingCommentAttachments').on('change', function () {
                    const files = Array.from(this.files || []);
                    const label = files.length === 0
                        ? 'No file chosen'
                        : files.length === 1
                            ? files[0].name
                            : `${files.length} files selected`;
                    $('#findingCommentFileLabel').text(label);
                });

                $('.btnCloseFindingModal').on('click', closeFindingModal);
                $detailModal.on('click', function (event) {
                    if (event.target === this) closeFindingModal();
                });

                $('.status-filter').on('click', function (event) {
                    event.preventDefault();
                    activeFilter = $(this).data('filter');
                    $('.status-filter').removeClass('active');
                    $(this).addClass('active');
                    $('#tableTitle').text($(this).data('label'));
                    table.ajax.reload();
                });

                $('#filterDepartment, #filterLocation, #filterCategory, #filterItem, #filterStatus')
                    .on('change', () => table.ajax.reload());
                $('#btnResetFindingFilters').on('click', function () {
                    $('#filterDepartment, #filterLocation, #filterCategory, #filterItem, #filterStatus').val('');
                    table.ajax.reload();
                });
            });
        </script>
    @endpush
</x-app-layout>
