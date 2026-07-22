<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            @php
                $attachmentListUrlTemplate = route('attachments.list', [
                    'doctype' => 'MIK',
                    'refnbr' => '__REFNBR__',
                ]);
                $cards = [
                    ['all', 'All Permits', $allPerizinan, 'border-slate-600 bg-slate-100 text-slate-700'],
                    ['active', 'Active', $activePerizinan, 'border-blue-600 bg-blue-50 text-blue-700'],
                    ['expiring', 'Expiring ≤ 30 Days', $expiringPerizinan, 'border-amber-600 bg-amber-50 text-amber-700'],
                    ['expired', 'Expired', $expiredPerizinan, 'border-red-600 bg-red-50 text-red-700'],
                    ['completed', 'Completed', $completedPerizinan, 'border-emerald-600 bg-emerald-50 text-emerald-700'],
                ];
            @endphp

            @foreach ($cards as [$filter, $label, $count, $color])
                <a href="#" class="status-filter block h-full" data-filter="{{ $filter }}">
                    <div class="status-card flex h-full items-center justify-between gap-3 rounded-lg border p-3 transition hover:-translate-y-1 hover:shadow-md {{ $color }}">
                        <p class="text-sm font-semibold">{{ $label }}</p>
                        <p class="text-lg font-bold">{{ number_format($count) }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-4 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-white/[0.06]">
                <h1 id="tableTitle" class="text-base font-extrabold text-gray-700 dark:text-white">All Permits</h1>
                <button type="button" id="btnCreatePerizinan"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                    + Create
                </button>
            </div>

            <div class="relative overflow-hidden">
                <table id="perizinanTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr class="bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="dtr-control w-10 px-4 py-3"></th>
                            <th class="px-4 py-3 text-left font-medium">Action</th>
                            <th class="px-4 py-3 text-left font-medium">Permit ID</th>
                            <th class="px-4 py-3 text-left font-medium">Date</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Site</th>
                            <th class="px-4 py-3 text-left font-medium">Category</th>
                            <th class="px-4 py-3 text-left font-medium">Title</th>
                            <th class="px-4 py-3 text-left font-medium">Start Date</th>
                            <th class="px-4 py-3 text-left font-medium">End Date</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                            <th class="px-4 py-3 text-left font-medium">Information</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="perizinanModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="max-h-[95vh] w-full max-w-5xl overflow-y-auto rounded-xl bg-white shadow-xl dark:bg-gray-800">
            <div class="flex items-center justify-between border-b px-5 py-4 dark:border-gray-700">
                <h2 id="modalTitle" class="text-lg font-bold text-gray-800 dark:text-white">Create Permit</h2>
                <button type="button" class="btnCloseModal text-2xl text-gray-500 hover:text-gray-800">&times;</button>
            </div>

            <form id="perizinanForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="editPerizinanId">
                <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">
                    <div class="grid grid-cols-1 gap-4 md:col-span-2 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Company <span class="text-red-500">*</span></label>
                        <select id="cpnyid" name="cpnyid" class="w-full rounded-lg border px-3 py-2" required>
                            <option value="">Select Company</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company }}">{{ $company }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Site <span class="text-red-500">*</span></label>
                        <select id="site_id" name="site_id" class="w-full rounded-lg border px-3 py-2" required disabled>
                            <option value="">Select Company first</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Department <span class="text-red-500">*</span></label>
                        <select id="departementid" name="departementid" class="w-full rounded-lg border px-3 py-2" required disabled>
                            <option value="">Select Company first</option>
                        </select>
                    </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Permit Category <span class="text-red-500">*</span></label>
                        <select id="perizinan_category" name="perizinan_category" class="w-full rounded-lg border px-3 py-2" required>
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->perizinan_category }}">
                                    {{ $category->perizinancategory_descr ?: $category->perizinan_category }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">User Approval <span class="text-red-500">*</span></label>
                        <select id="user_approval" name="user_approval[]"
                            class="user-select2 w-full rounded-lg border px-3 py-2" multiple required>
                            @foreach ($approvers as $approver)
                                <option value="{{ $approver->username }}">{{ $approver->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">You can select more than one user.</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold">Permit Title <span class="text-red-500">*</span></label>
                        <input type="text" id="perizinan_title" name="perizinan_title" class="w-full rounded-lg border px-3 py-2" maxlength="255" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold">Description</label>
                        <textarea id="perizinan_descr" name="perizinan_descr" rows="3" class="w-full rounded-lg border px-3 py-2"></textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Start Date <span class="text-red-500">*</span></label>
                        <input type="date" id="startdate" name="startdate" class="w-full rounded-lg border px-3 py-2" required>
                    </div>
                    <div>
                        <div class="mb-1 flex items-center justify-between gap-3">
                            <label for="enddate" class="block text-sm font-semibold">End Date</label>
                            <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="hidden" name="expired_date" value="0">
                                <input type="checkbox" id="expired_date" name="expired_date" value="1"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" checked>
                                <span>Has an expiration date</span>
                            </label>
                        </div>
                        <input type="date" id="enddate" name="enddate" class="w-full rounded-lg border px-3 py-2" required>
                        <p id="noExpiryHint" class="mt-1 hidden text-xs text-gray-500">This permit has no expiration date.</p>
                    </div>
                </div>

                <div class="px-5 pb-5">
                    <div class="mb-2 flex items-center justify-between">
                        <h3 class="font-bold text-gray-800 dark:text-white">Permit Details</h3>
                        <button type="button" id="btnAddRow" class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700">+ Add Row</button>
                    </div>
                    <div class="overflow-x-auto rounded-lg border">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-3 py-2 text-left">Permit Item</th>
                                    <th class="w-40 px-3 py-2 text-left">Qty</th>
                                    <th class="w-20 px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody id="detailRows"></tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <label class="mb-1 block text-sm font-semibold">Attachment</label>
                        <input type="file" id="attachments" name="attachments[]" multiple
                            class="w-full rounded-lg border px-3 py-2" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <p class="mt-1 text-xs text-gray-500">Maximum 10 MB per file. New files will be appended in edit mode.</p>
                    </div>
                    <div id="formErrors" class="mt-4 hidden rounded-lg bg-red-50 p-3 text-sm text-red-700"></div>
                </div>

                <div class="flex justify-end gap-3 border-t px-5 py-4 dark:border-gray-700">
                    <button type="button" class="btnCloseModal rounded-lg border px-4 py-2 text-sm font-semibold">Cancel</button>
                    <button type="submit" id="btnSave" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        <span id="saveSpinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        <span id="saveText">Save</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="permitDetailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="flex max-h-[94vh] w-full max-w-7xl flex-col overflow-hidden rounded-xl bg-white shadow-xl dark:bg-gray-800">
            <div class="flex items-center justify-between border-b px-6 py-4 dark:border-gray-700">
                <div>
                    <div class="flex items-center gap-3">
                        <h2 id="detailPermitId" class="text-xl font-extrabold text-gray-900 dark:text-white">-</h2>
                        <span id="detailStatus" class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">-</span>
                    </div>
                    <p id="detailTitle" class="mt-1 text-sm text-gray-500">-</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="btnDetailAction" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">⚡ Action</button>
                    <button type="button" class="btnCloseDetail rounded-lg border px-4 py-2 text-sm font-semibold">Close</button>
                </div>
            </div>

            <div class="grid min-h-0 flex-1 grid-cols-1 overflow-y-auto lg:grid-cols-2 lg:overflow-hidden">
                <div class="overflow-y-auto border-r p-6 dark:border-gray-700">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div><p class="text-xs text-gray-400">Permit Date</p><p id="detailDate" class="mt-1 font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">Category</p><p id="detailCategory" class="mt-1 font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">Company</p><p id="detailCompany" class="mt-1 font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">Site</p><p id="detailSite" class="mt-1 font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">Department</p><p id="detailDepartment" class="mt-1 font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">Requester</p><p id="detailRequester" class="mt-1 font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">Start Date</p><p id="detailStartDate" class="mt-1 font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">End Date</p><p id="detailEndDate" class="mt-1 font-medium">-</p></div>
                        <div class="sm:col-span-2"><p class="text-xs text-gray-400">Approvers</p><p id="detailApprovers" class="mt-1 font-medium">-</p></div>
                        <div class="sm:col-span-2"><p class="text-xs text-gray-400">Description</p><div id="detailDescription" class="mt-2 rounded-lg bg-gray-50 p-4 text-sm whitespace-pre-wrap dark:bg-gray-700">-</div></div>
                    </div>

                    <h3 class="mb-2 mt-6 font-bold">Permit Items</h3>
                    <div class="overflow-hidden rounded-lg border dark:border-gray-700">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700"><tr><th class="px-3 py-2 text-left">Item</th><th class="w-28 px-3 py-2 text-right">Quantity</th></tr></thead>
                            <tbody id="detailItems"></tbody>
                        </table>
                    </div>

                    <div class="mb-2 mt-6 flex items-center justify-between">
                        <h3 class="font-bold">Attachments</h3>
                        <span id="detailAttachmentCount" class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-bold text-indigo-700">0</span>
                    </div>
                    <div class="overflow-hidden rounded-lg border dark:border-gray-700">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-3 py-2 text-left">Filename</th>
                                    <th class="px-3 py-2 text-left">Created By</th>
                                    <th class="px-3 py-2 text-left">Date</th>
                                    <th class="px-3 py-2 text-right">Size</th>
                                </tr>
                            </thead>
                            <tbody id="detailAttachments"></tbody>
                        </table>
                    </div>
                </div>

                <div class="overflow-y-auto p-6">
                    <h3 class="mb-5 font-bold text-gray-800 dark:text-white">Tracking Timeline</h3>
                    <div id="activityTimeline" class="space-y-4"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="activityModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-lg rounded-xl bg-white shadow-xl dark:bg-gray-800">
            <div class="flex items-center justify-between border-b px-5 py-4 dark:border-gray-700">
                <h2 class="text-lg font-bold">Permit Action</h2>
                <button type="button" class="btnCloseActivity text-2xl text-gray-500">&times;</button>
            </div>
            <form id="activityForm">
                @csrf
                <input type="hidden" id="activityPermitId">
                <div class="space-y-4 p-5">
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Response <span class="text-red-500">*</span></label>
                        <textarea id="response_descr" name="response_descr" rows="4" class="w-full rounded-lg border px-3 py-2" required></textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Work Status <span class="text-red-500">*</span></label>
                        <select id="status_pekerjaan" name="status_pekerjaan" class="w-full rounded-lg border px-3 py-2" required>
                            <option value="">Select Status</option>
                            <option value="PROCESS">Process</option>
                            <option value="WAITING">Waiting</option>
                            <option value="DONE">Done</option>
                        </select>
                    </div>
                    <div id="activityErrors" class="hidden rounded-lg bg-red-50 p-3 text-sm text-red-700"></div>
                </div>
                <div class="flex justify-end gap-3 border-t px-5 py-4 dark:border-gray-700">
                    <button type="button" class="btnCloseActivity rounded-lg border px-4 py-2 text-sm font-semibold">Cancel</button>
                    <button type="submit" id="btnSaveActivity" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        <span id="activitySpinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        <span>Save Action</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            let activeFilter = 'all';
            const titles = {
                all: 'All Permits',
                active: 'Active Permits',
                expiring: 'Permits Expiring ≤ 30 Days',
                expired: 'Expired Permits',
                completed: 'Completed Permits'
            };

            const escapeHtml = (value) => $('<div>').text(value ?? '').html();
            const formatDate = (value) => {
                if (!value) return '-';
                const date = new Date(`${value}T00:00:00`);
                return Number.isNaN(date.getTime()) ? escapeHtml(value) : date.toLocaleDateString('en-GB');
            };
            const statusBadge = (value) => {
                const status = (value || '-').toString().toUpperCase();
                const color = status === 'C'
                    ? 'bg-emerald-100 text-emerald-700'
                    : status === 'X'
                        ? 'bg-red-100 text-red-700'
                        : 'bg-blue-100 text-blue-700';
                return `<span class="rounded-full px-2 py-1 text-xs font-semibold ${color}">${escapeHtml(status)}</span>`;
            };

            const table = $('#perizinanTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                pageLength: 25,
                ajax: {
                    url: @json(route('perizinan.json')),
                    data: function (data) {
                        data.filter = activeFilter;
                    }
                },
                order: [[9, 'asc']],
                columns: [
                    { data: null, defaultContent: '', orderable: false, searchable: false, className: 'dtr-control' },
                    {
                        data: 'perizinan_id', orderable: false, searchable: false,
                        render: (value) => `<div class="flex gap-1">
                            <button type="button" class="btnEdit rounded bg-amber-500 px-3 py-1 text-xs font-semibold text-white hover:bg-amber-600" data-id="${escapeHtml(value)}">Edit</button>
                            <button type="button" class="btnActivity rounded bg-indigo-600 px-3 py-1 text-xs font-semibold text-white hover:bg-indigo-700" data-id="${escapeHtml(value)}">Action</button>
                        </div>`
                    },
                    {
                        data: 'perizinan_id',
                        render: (value) => `<button type="button" class="btnShowPermit inline-flex items-center rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 font-bold text-indigo-700 transition hover:bg-indigo-100" data-id="${escapeHtml(value)}">${escapeHtml(value || '-')}</button>`
                    },
                    { data: 'perizinan_date', render: formatDate },
                    { data: 'cpny_id', defaultContent: '-' },
                    { data: 'site_name', defaultContent: '-' },
                    { data: 'category_name', defaultContent: '-' },
                    { data: 'perizinan_title', defaultContent: '-' },
                    { data: 'startdate', render: formatDate },
                    { data: 'enddate', render: formatDate },
                    { data: 'status', render: statusBadge },
                    { data: 'information', defaultContent: '-', orderable: false }
                ]
            });

            $('.status-filter').on('click', function (event) {
                event.preventDefault();
                activeFilter = $(this).data('filter');
                $('#tableTitle').text(titles[activeFilter] || titles.all);
                $('.status-card').removeClass('ring-2 ring-indigo-500');
                $(this).find('.status-card').addClass('ring-2 ring-indigo-500');
                table.ajax.reload();
            });

            $('.status-filter[data-filter="all"] .status-card').addClass('ring-2 ring-indigo-500');

            const $modal = $('#perizinanModal');
            const $detailModal = $('#permitDetailModal');
            const $activityModal = $('#activityModal');
            const baseUrl = @json(url('/perizinan'));
            const departmentUrl = @json(route('perizinan.departments'));
            const siteUrl = @json(route('perizinan.sites'));
            const attachmentListUrlTemplate = @json($attachmentListUrlTemplate);

            $('#user_approval').select2({
                placeholder: 'Search user approval...',
                allowClear: true,
                width: '100%',
                dropdownParent: $modal
            });

            const formatDateTime = (value) => {
                if (!value) return '-';
                const date = new Date(value);
                return Number.isNaN(date.getTime()) ? escapeHtml(value) : date.toLocaleString('en-GB');
            };

            function statusClass(status) {
                return ({
                    PROCESS: 'bg-blue-100 text-blue-700',
                    WAITING: 'bg-amber-100 text-amber-700',
                    DONE: 'bg-emerald-100 text-emerald-700'
                })[(status || '').toUpperCase()] || 'bg-gray-100 text-gray-700';
            }

            function openActivityModal(permitId) {
                $('#activityForm')[0].reset();
                $('#activityPermitId').val(permitId);
                $('#activityErrors').addClass('hidden').empty();
                $activityModal.removeClass('hidden').addClass('flex');
            }

            function formatFileSize(bytes) {
                const size = Number(bytes || 0);
                if (!size) return '-';
                if (size < 1024) return `${size} B`;
                if (size < 1048576) return `${(size / 1024).toFixed(1)} KB`;
                return `${(size / 1048576).toFixed(1)} MB`;
            }

            function loadPermitAttachments(permitId) {
                const $tbody = $('#detailAttachments');
                $('#detailAttachmentCount').text('0');
                $tbody.html('<tr><td colspan="4" class="px-3 py-6 text-center text-gray-500">Loading attachments...</td></tr>');

                const listUrl = attachmentListUrlTemplate.replace('__REFNBR__', encodeURIComponent(permitId));
                $.get(listUrl)
                    .done(function (response) {
                        const rows = response.success ? (response.attachments || []) : [];
                        $('#detailAttachmentCount').text(rows.length);
                        if (!rows.length) {
                            $tbody.html('<tr><td colspan="4" class="px-3 py-6 text-center text-gray-500">No attachments found.</td></tr>');
                            return;
                        }

                        $tbody.html(rows.map(attachment => {
                            const baseName = attachment.name || attachment.display_name || attachment.filename || 'Attachment';
                            const extension = attachment.extention ? `.${attachment.extention}` : '';
                            const fileName = baseName.toLowerCase().endsWith(extension.toLowerCase()) ? baseName : `${baseName}${extension}`;
                            const fileLink = attachment.url
                                ? `<a href="${escapeHtml(attachment.url)}" target="_blank" rel="noopener noreferrer" class="font-semibold text-indigo-600 hover:underline">📎 ${escapeHtml(fileName)}</a>`
                                : `<span>📎 ${escapeHtml(fileName)} <span class="text-xs text-red-500">(link unavailable)</span></span>`;
                            return `<tr class="border-t dark:border-gray-700">
                                <td class="px-3 py-2">${fileLink}</td>
                                <td class="px-3 py-2">${escapeHtml(attachment.created_user || attachment.created_by || '-')}</td>
                                <td class="px-3 py-2">${formatDateTime(attachment.created_at)}</td>
                                <td class="px-3 py-2 text-right">${formatFileSize(attachment.size)}</td>
                            </tr>`;
                        }).join(''));
                    })
                    .fail(function () {
                        $tbody.html('<tr><td colspan="4" class="px-3 py-6 text-center text-red-500">Failed to load attachments.</td></tr>');
                    });
            }

            function closeActivityModal() {
                if ($('#btnSaveActivity').prop('disabled')) return;
                $activityModal.addClass('hidden').removeClass('flex');
            }

            async function showPermit(permitId) {
                const response = await $.get(`${baseUrl}/${encodeURIComponent(permitId)}`);
                const permit = response.data;
                $('#detailPermitId').text(permit.perizinan_id || '-');
                $('#detailTitle').text(permit.perizinan_title || '-');
                $('#detailStatus').attr('class', `rounded-full px-3 py-1 text-xs font-bold ${statusClass(permit.status)}`).text(permit.status || '-');
                $('#detailDate').text(formatDate(permit.perizinan_date));
                $('#detailCategory').text(permit.category?.perizinancategory_descr || permit.perizinan_category || '-');
                $('#detailCompany').text(permit.cpny_id || '-');
                $('#detailSite').text(permit.site?.site_name || permit.site_id || '-');
                $('#detailDepartment').text(permit.department?.department_name || permit.department_fin_id || '-');
                $('#detailRequester').text(permit.user_peminta || '-');
                $('#detailStartDate').text(formatDate(permit.startdate));
                $('#detailEndDate').text(permit.expired_date ? formatDate(permit.enddate) : 'No expiration date');
                $('#detailApprovers').text((permit.user_approval || '').split(',').filter(Boolean).join(', ') || '-');
                $('#detailDescription').text(permit.perizinan_descr || '-');
                $('#btnDetailAction').data('id', permit.perizinan_id);
                loadPermitAttachments(permit.perizinan_id);

                const items = permit.details || [];
                $('#detailItems').html(items.length ? items.map(item => `
                    <tr class="border-t dark:border-gray-700">
                        <td class="px-3 py-2">${escapeHtml(item.item_perizinan || '-')}</td>
                        <td class="px-3 py-2 text-right">${escapeHtml(item.qty_perizinan ?? '-')}</td>
                    </tr>`).join('') : '<tr><td colspan="2" class="px-3 py-6 text-center text-gray-500">No permit items.</td></tr>');

                const activities = permit.activities || [];
                $('#activityTimeline').html(activities.length ? activities.map(activity => {
                    const status = (activity.status_pekerjaan || '-').toUpperCase();
                    return `<div class="relative border-l-2 border-gray-200 pb-2 pl-6 dark:border-gray-600">
                        <span class="absolute -left-2.5 top-0 h-5 w-5 rounded-full bg-indigo-500 ring-4 ring-white dark:ring-gray-800"></span>
                        <div class="rounded-lg border p-4 dark:border-gray-700">
                            <div class="flex items-start justify-between gap-3">
                                <p class="font-semibold">${escapeHtml(activity.response_descr || '-')}</p>
                                <span class="rounded-full px-2.5 py-1 text-xs font-bold ${statusClass(status)}">${escapeHtml(status)}</span>
                            </div>
                            <p class="mt-2 text-xs text-gray-400">${escapeHtml(activity.pic_perizinan || '-')} · ${formatDateTime(activity.response_date)}</p>
                        </div>
                    </div>`;
                }).join('') : '<div class="rounded-lg border border-dashed p-8 text-center text-sm text-gray-500">No tracking activity yet.</div>');

                $detailModal.removeClass('hidden').addClass('flex');
                $('body').addClass('overflow-hidden');
            }

            function addDetailRow(detail = {}) {
                $('#detailRows').append(`
                    <tr class="border-t detail-row">
                        <td class="p-2"><input type="text" name="item_perizinan[]" value="${escapeHtml(detail.item_perizinan || '')}" class="w-full rounded-lg border px-3 py-2" maxlength="255" required></td>
                        <td class="p-2"><input type="number" name="qty_perizinan[]" value="${escapeHtml(detail.qty_perizinan || 1)}" class="w-full rounded-lg border px-3 py-2" min="0.01" step="0.01" required></td>
                        <td class="p-2 text-center"><button type="button" class="btnRemoveRow rounded bg-red-500 px-2 py-1 text-white">&times;</button></td>
                    </tr>`);
            }

            function setSaving(saving) {
                $('#btnSave').prop('disabled', saving).toggleClass('opacity-60', saving);
                $('#saveSpinner').toggleClass('hidden', !saving);
                $('#saveText').text(saving ? 'Saving...' : 'Save');
            }

            function syncExpiryField() {
                const hasExpiry = $('#expired_date').is(':checked');
                $('#enddate').prop('disabled', !hasExpiry).prop('required', hasExpiry);
                $('#noExpiryHint').toggleClass('hidden', hasExpiry);
                if (!hasExpiry) $('#enddate').val('');
            }

            function openModal() {
                $modal.removeClass('hidden').addClass('flex');
                $('body').addClass('overflow-hidden');
            }

            function closeModal() {
                if ($('#btnSave').prop('disabled')) return;
                $modal.addClass('hidden').removeClass('flex');
                $('body').removeClass('overflow-hidden');
            }

            async function loadDepartments(companyId, selected = '') {
                const $department = $('#departementid');
                $department.prop('disabled', true).html('<option value="">Loading...</option>');
                if (!companyId) {
                    $department.html('<option value="">Select Company first</option>');
                    return;
                }
                const rows = await $.get(departmentUrl, { cpny_id: companyId });
                $department.html('<option value="">Select Department</option>');
                rows.forEach(row => $department.append(new Option(row.department_name, row.department_fin_id)));
                $department.prop('disabled', false).val(selected);
            }

            async function loadSites(companyId, selected = '') {
                const $site = $('#site_id');
                $site.prop('disabled', true).html('<option value="">Loading...</option>');
                if (!companyId) {
                    $site.html('<option value="">Select Company first</option>');
                    return;
                }
                const rows = await $.get(siteUrl, { cpny_id: companyId });
                $site.html('<option value="">Select Site</option>');
                rows.forEach(row => $site.append(new Option(row.site_name, row.siteid)));
                $site.prop('disabled', false).val(selected);
            }

            function resetForm() {
                $('#perizinanForm')[0].reset();
                $('#user_approval').val(null).trigger('change');
                $('#expired_date').prop('checked', true);
                syncExpiryField();
                $('#editPerizinanId').val('');
                $('#modalTitle').text('Create Permit');
                $('#site_id').prop('disabled', true).html('<option value="">Select Company first</option>');
                $('#departementid').prop('disabled', true).html('<option value="">Select Company first</option>');
                $('#detailRows').empty();
                $('#formErrors').addClass('hidden').empty();
                addDetailRow();
            }

            $('#btnCreatePerizinan').on('click', function () {
                resetForm();
                openModal();
            });
            $('.btnCloseModal').on('click', closeModal);
            $('#cpnyid').on('change', function () {
                loadSites(this.value);
                loadDepartments(this.value);
            });
            $('#expired_date').on('change', syncExpiryField);
            $('#btnAddRow').on('click', () => addDetailRow());
            $(document).on('click', '.btnRemoveRow', function () {
                if ($('.detail-row').length === 1) {
                    $(this).closest('tr').find('input').val('');
                    return;
                }
                $(this).closest('tr').remove();
            });

            $(document).on('click', '.btnShowPermit', async function () {
                try {
                    await showPermit($(this).data('id'));
                } catch (error) {
                    Swal.fire('Error', error.responseJSON?.message || 'Failed to load permit details.', 'error');
                }
            });

            $(document).on('click', '.btnActivity', function () {
                openActivityModal($(this).data('id'));
            });
            $('#btnDetailAction').on('click', function () {
                openActivityModal($(this).data('id'));
            });
            $('.btnCloseDetail').on('click', function () {
                $detailModal.addClass('hidden').removeClass('flex');
                $('body').removeClass('overflow-hidden');
            });
            $('.btnCloseActivity').on('click', closeActivityModal);

            $('#activityForm').on('submit', function (event) {
                event.preventDefault();
                const permitId = $('#activityPermitId').val();
                const $button = $('#btnSaveActivity');
                $button.prop('disabled', true).addClass('opacity-60');
                $('#activitySpinner').removeClass('hidden');
                $('#activityErrors').addClass('hidden').empty();

                $.ajax({
                    url: `${baseUrl}/${encodeURIComponent(permitId)}/activities`,
                    method: 'POST',
                    data: $(this).serialize(),
                    success: async function (response) {
                        $activityModal.addClass('hidden').removeClass('flex');
                        table.ajax.reload(null, false);
                        await showPermit(permitId);
                        Swal.fire({ icon: 'success', title: 'Success', text: response.message, timer: 1300, showConfirmButton: false });
                    },
                    error: function (xhr) {
                        const errors = xhr.responseJSON?.errors;
                        const messages = errors ? Object.values(errors).flat() : [xhr.responseJSON?.message || 'Failed to save activity.'];
                        $('#activityErrors').removeClass('hidden').html(messages.map(message => `<div>• ${escapeHtml(message)}</div>`).join(''));
                        Swal.fire('Error', messages[0], 'error');
                    },
                    complete: function () {
                        $button.prop('disabled', false).removeClass('opacity-60');
                        $('#activitySpinner').addClass('hidden');
                    }
                });
            });

            $(document).on('click', '.btnEdit', async function () {
                resetForm();
                const id = $(this).data('id');
                setSaving(true);
                try {
                    const response = await $.get(`${baseUrl}/${encodeURIComponent(id)}/edit`);
                    const data = response.data;
                    $('#editPerizinanId').val(data.perizinan_id);
                    $('#modalTitle').text(`Edit Permit - ${data.perizinan_id}`);
                    $('#cpnyid').val(data.cpny_id);
                    await Promise.all([
                        loadSites(data.cpny_id, data.site_id),
                        loadDepartments(data.cpny_id, data.department_fin_id)
                    ]);
                    $('#perizinan_category').val(data.perizinan_category);
                    $('#perizinan_title').val(data.perizinan_title);
                    $('#perizinan_descr').val(data.perizinan_descr);
                    $('#startdate').val((data.startdate || '').substring(0, 10));
                    $('#expired_date').prop('checked', Boolean(data.expired_date));
                    syncExpiryField();
                    $('#enddate').val((data.enddate || '').substring(0, 10));
                    $('#user_approval').val((data.user_approval || '').split(',').filter(Boolean)).trigger('change');
                    $('#detailRows').empty();
                    (response.details.length ? response.details : [{}]).forEach(addDetailRow);
                    openModal();
                } catch (error) {
                    Swal.fire('Error', error.responseJSON?.message || 'Failed to load permit data.', 'error');
                } finally {
                    setSaving(false);
                }
            });

            $('#perizinanForm').on('submit', function (event) {
                event.preventDefault();
                const id = $('#editPerizinanId').val();
                const formData = new FormData(this);
                if (id) formData.append('_method', 'PUT');
                setSaving(true);
                $('#formErrors').addClass('hidden').empty();

                $.ajax({
                    url: id ? `${baseUrl}/${encodeURIComponent(id)}` : baseUrl,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        Swal.fire({ icon: 'success', title: 'Success', text: response.message, timer: 1400, showConfirmButton: false })
                            .then(() => window.location.href = response.redirect || baseUrl);
                    },
                    error: function (xhr) {
                        setSaving(false);
                        const errors = xhr.responseJSON?.errors;
                        const messages = errors ? Object.values(errors).flat() : [xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to save data.'];
                        $('#formErrors').removeClass('hidden').html(messages.map(message => `<div>• ${escapeHtml(message)}</div>`).join(''));
                        Swal.fire('Error', messages[0], 'error');
                    }
                });
            });
        });
    </script>
</x-app-layout>
