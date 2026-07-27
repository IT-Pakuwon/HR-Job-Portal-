<x-app-layout>
    @php
        $maSby = Route::is('manage-approvals-sby') || Route::is('manage-approvals-sby.*');
        $maBase = $maSby ? '/manage-approvals-sby' : '/manage-approvals';
    @endphp
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        .manage-approval-table tbody tr { transition: background-color .1s ease; }
        .manage-approval-table tbody tr:nth-child(even) { background-color: rgb(249 250 251); }
        .dark .manage-approval-table tbody tr:nth-child(even) { background-color: rgb(255 255 255 / .02); }
        .manage-approval-table tbody tr:hover { background-color: rgb(238 242 255); }
        .dark .manage-approval-table tbody tr:hover { background-color: rgb(99 102 241 / .08); }
        .manage-approval-table tbody td { padding: .6rem .75rem; vertical-align: middle; border-bottom: 1px solid rgb(243 244 246); }
        .dark .manage-approval-table tbody td { border-bottom-color: rgb(255 255 255 / .06); }
        .select2-container .select2-selection--single,
        .select2-container .select2-selection--multiple { min-height: 34px; }
    </style>

    {{-- template options username (hidden, cloned into the Set Status/Edit Line pickers) --}}
    <select id="usernameOptionsTemplate" class="hidden">
        @foreach ($allUsers as $u)
            <option value="{{ $u->username }}">{{ $u->username }} - {{ $u->name }}{{ $u->status !== 'A' ? ' (inactive)' : '' }}</option>
        @endforeach
    </select>

    <div class="max-w-9xl mx-auto w-full p-2">
        <div>
            {{-- Tab nav --}}
            <div class="flex gap-1 border-b border-gray-200 dark:border-gray-700">
                <button type="button" id="tabBtnSearch"
                    class="manage-approval-tab-btn rounded-t-lg border border-b-0 border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-indigo-600 dark:border-gray-700 dark:bg-gray-800 dark:text-indigo-400">
                    🔍 Search & Fix
                </button>
                <button type="button" id="tabBtnTransfer"
                    class="manage-approval-tab-btn rounded-t-lg border border-b-0 border-gray-200 bg-gray-50 px-5 py-2.5 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                    🔁 Transfer User
                </button>
            </div>

            {{-- ===================== TAB: Search & Fix ===================== --}}
            <div id="tabPanelSearch"
                class="rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800 md:p-6">

                <div class="mb-5 flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-linear-to-br from-indigo-600 to-violet-700 text-lg text-white shadow-sm">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-bold text-gray-800 dark:text-white">Manage Approval</h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Search approval lines by document, approver, or status — then set their status or correct them directly.</p>
                    </div>
                </div>

                <div class="mb-4 flex flex-wrap items-end gap-3 rounded-lg border border-gray-100 bg-gray-50/70 p-3 dark:border-white/5 dark:bg-white/2">
                    <div class="min-w-50 flex-1">
                        <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">Refnbr</label>
                        <input type="text" id="filterRefnbr" placeholder="e.g. RB2507-0001"
                            class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none dark:border-white/10 dark:bg-white/5 dark:text-gray-100">
                    </div>

                    <div class="min-w-50 flex-1">
                        <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">Doc Type</label>
                        <select id="filterDoctype" class="w-full dark:bg-gray-700">
                            <option value="">All Document Type</option>
                            @foreach ($doctypes as $dt)
                                <option value="{{ $dt['doctype'] }}">{{ $dt['doctype'] }} - {{ $dt['doctype_descr'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="min-w-50 flex-1">
                        <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">Username</label>
                        <select id="filterUsername" class="w-full dark:bg-gray-700">
                            <option value="">Any approver</option>
                            @foreach ($allUsers as $u)
                                <option value="{{ $u->username }}">{{ $u->username }} - {{ $u->name }}{{ $u->status !== 'A' ? ' (inactive)' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="min-w-40 flex-1">
                        <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">Status</label>
                        <select id="filterStatus" class="w-full dark:bg-gray-700">
                            <option value="">All</option>
                            <option value="P">Waiting</option>
                            <option value="A">Approved</option>
                            <option value="R">Rejected</option>
                            <option value="D">Revise</option>
                            <option value="X">Cancelled</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button id="searchBtn" type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i> Search
                        </button>
                        <button id="clearFiltersBtn" type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-100 dark:border-gray-500 dark:text-gray-200 dark:hover:bg-gray-600">
                            <i class="fa-solid fa-xmark text-xs"></i> Clear
                        </button>
                    </div>
                </div>

                <div class="rounded-base relative max-h-125 overflow-y-auto overflow-x-auto rounded-lg border border-gray-200 shadow-sm dark:border-gray-700">
                    <table id="manageApprovalTable" class="manage-approval-table text-body w-full text-left text-sm rtl:text-right">
                        <thead class="text-body sticky top-0 z-10 border-b border-gray-200 bg-white text-sm dark:border-gray-700 dark:bg-gray-800">
                            <tr>
                                <th class="px-3 py-3 text-left">Refnbr</th>
                                <th class="px-3 py-3 text-left">Doc Type</th>
                                <th class="w-16 px-3 py-3 text-center">Level</th>
                                <th class="px-3 py-3 text-left">Approver</th>
                                <th class="w-28 px-3 py-3 text-center">Approval Status</th>
                                <th class="w-28 px-3 py-3 text-center">Header Status</th>
                                <th class="px-3 py-3 text-left">Updated</th>
                                <th class="w-44 px-3 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            {{-- ===================== TAB: Transfer User ===================== --}}
            <div id="tabPanelTransfer"
                class="hidden rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-[#0f172a] md:p-6">

                <div class="mb-5 flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-linear-to-br from-rose-500 to-orange-500 text-lg text-white shadow-sm">
                        <i class="fa-solid fa-arrow-right-arrow-left"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-bold text-gray-800 dark:text-white">Transfer Pending Approvals</h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Move every currently-waiting approval from one user to another (e.g. when someone resigns).
                            Only steps with status <strong>Waiting</strong> are affected; approved/rejected history is untouched.
                        </p>
                    </div>
                </div>

                <div class="mb-4 flex flex-wrap items-end gap-3 rounded-lg border border-gray-100 bg-gray-50/70 p-3 dark:border-white/5 dark:bg-white/2">
                    <div class="min-w-50 flex-1">
                        <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">From User</label>
                        <select id="transferFrom" class="w-full dark:bg-gray-700">
                            <option value="">Select user…</option>
                            @foreach ($allUsers as $u)
                                <option value="{{ $u->username }}">{{ $u->username }} - {{ $u->name }}{{ $u->status !== 'A' ? ' (inactive)' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="min-w-50 flex-1">
                        <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">To User(s)</label>
                        <select id="transferTo" class="w-full dark:bg-gray-700" multiple>
                            @foreach ($users as $u)
                                <option value="{{ $u->username }}">{{ $u->username }} - {{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="min-w-40 flex-1">
                        <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">Doc Type (optional)</label>
                        <select id="transferDoctype" class="w-full dark:bg-gray-700">
                            <option value="">All Document Type</option>
                            @foreach ($doctypes as $dt)
                                <option value="{{ $dt['doctype'] }}">{{ $dt['doctype'] }} - {{ $dt['doctype_descr'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="min-w-40 flex-1">
                        <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">Refnbr (optional)</label>
                        <input type="text" id="transferRefnbr"
                            class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none dark:border-white/10 dark:bg-white/5 dark:text-gray-100">
                    </div>

                    <div>
                        <button id="transferPreviewBtn" type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700">
                            <i class="fa-solid fa-eye text-xs"></i> Preview Transfer
                        </button>
                    </div>
                </div>

                <div id="transferWarning" class="mb-3 hidden rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-800 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-300"></div>

                <div id="transferResultsWrap" class="hidden">
                    <div class="mb-2 flex items-center justify-between">
                        <span id="transferCountLabel" class="text-sm font-medium text-gray-600 dark:text-gray-300"></span>
                        <button id="transferConfirmBtn" type="button" disabled
                            class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-50">
                            <i class="fa-solid fa-right-left text-xs"></i> Confirm Transfer
                        </button>
                    </div>
                    <div class="rounded-base relative max-h-125 overflow-y-auto overflow-x-auto rounded-lg border border-gray-200 shadow-sm dark:border-gray-700">
                        <table id="transferPreviewTable" class="manage-approval-table text-body w-full text-left text-sm rtl:text-right">
                            <thead class="text-body sticky top-0 z-10 border-b border-gray-200 bg-white text-sm dark:border-gray-700 dark:bg-[#0f172a]">
                                <tr>
                                    <th class="w-10 px-3 py-3"></th>
                                    <th class="px-3 py-3 text-left">Refnbr</th>
                                    <th class="px-3 py-3 text-left">Doc Type</th>
                                    <th class="w-16 px-3 py-3 text-center">Level</th>
                                    <th class="px-3 py-3 text-left">Current Approver</th>
                                    <th class="w-24 px-3 py-3 text-center">Status</th>
                                    <th class="px-3 py-3 text-left">Company</th>
                                    <th class="px-3 py-3 text-left">Department</th>
                                    <th class="px-3 py-3 text-left">Created At</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL: Set Status ===================== --}}
    <div id="setStatusModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4 dark:bg-black/70">
        <div class="w-full max-w-lg overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-[#0f172a]">
            <div class="flex items-center justify-between border-b border-slate-200 bg-rose-50/60 px-5 py-3 dark:border-white/10 dark:bg-rose-500/5">
                <h2 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-white">
                    <i class="fa-solid fa-sliders text-rose-500"></i> Set Approval Status
                </h2>
                <button type="button" id="setStatusModalClose" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&times;</button>
            </div>
            <div class="space-y-3 px-5 py-4">
                <div class="flex flex-wrap gap-x-4 gap-y-1 rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-500 dark:bg-white/5 dark:text-gray-400">
                    <div><span class="font-semibold text-gray-600 dark:text-gray-300">Refnbr:</span> <span id="setStatusRefnbr"></span></div>
                    <div><span class="font-semibold text-gray-600 dark:text-gray-300">Doc Type:</span> <span id="setStatusDoctype"></span></div>
                    <div><span class="font-semibold text-gray-600 dark:text-gray-300">Level:</span> <span id="setStatusLevel"></span></div>
                    <div><span class="font-semibold text-gray-600 dark:text-gray-300">Current:</span> <span id="setStatusCurrent"></span></div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">New Status</label>
                    <select id="setStatusNewStatus" class="w-full dark:bg-gray-700">
                        <option value="P">Waiting</option>
                        <option value="A">Approved</option>
                        <option value="R">Rejected</option>
                        <option value="D">Revise</option>
                        <option value="X">Cancelled</option>
                    </select>
                </div>

                <div id="setStatusWarning" class="hidden rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-xs text-amber-800 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-300"></div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">Approver(s)</label>
                    <select id="setStatusUsername" class="w-full dark:bg-gray-700" multiple></select>
                    <p class="mt-1 text-xs text-gray-400">Shows the last person who acted on this step. Pick from ms_user to correct it here too if needed.</p>
                </div>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-3 dark:border-white/10">
                <button type="button" id="setStatusCancelBtn" class="rounded-lg border px-4 py-1.5 text-sm text-gray-700 hover:bg-gray-100 dark:border-gray-500 dark:text-gray-200 dark:hover:bg-gray-600">Cancel</button>
                <button type="button" id="setStatusConfirmBtn" class="rounded-lg bg-rose-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-rose-700">Confirm</button>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL: Edit Line ===================== --}}
    <div id="editLineModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4 dark:bg-black/70">
        <div class="w-full max-w-lg overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-[#0f172a]">
            <div class="flex items-center justify-between border-b border-slate-200 bg-indigo-50/60 px-5 py-3 dark:border-white/10 dark:bg-indigo-500/5">
                <h2 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-white">
                    <i class="fa-solid fa-pen text-indigo-500"></i> Edit Approval Line
                </h2>
                <button type="button" id="editLineModalClose" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&times;</button>
            </div>
            <div class="space-y-3 px-5 py-4">
                <div class="flex flex-wrap gap-x-4 gap-y-1 rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-500 dark:bg-white/5 dark:text-gray-400">
                    <div><span class="font-semibold text-gray-600 dark:text-gray-300">Refnbr:</span> <span id="editLineRefnbr"></span></div>
                    <div><span class="font-semibold text-gray-600 dark:text-gray-300">Doc Type:</span> <span id="editLineDoctype"></span></div>
                    <div><span class="font-semibold text-gray-600 dark:text-gray-300">Level:</span> <span id="editLineLevel"></span></div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">Approver(s)</label>
                    <select id="editLineUsername" class="w-full dark:bg-gray-700" multiple></select>
                    <p class="mt-1 text-xs text-gray-400">Pick from ms_user — names are filled in automatically.</p>
                </div>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-3 dark:border-white/10">
                <button type="button" id="editLineCancelBtn" class="rounded-lg border px-4 py-1.5 text-sm text-gray-700 hover:bg-gray-100 dark:border-gray-500 dark:text-gray-200 dark:hover:bg-gray-600">Cancel</button>
                <button type="button" id="editLineConfirmBtn" class="rounded-lg bg-indigo-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-indigo-700">Save</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        (function() {
            const csrfToken = '{{ csrf_token() }}';

            function showTab(name) {
                const isSearch = name === 'search';
                $('#tabPanelSearch').toggleClass('hidden', !isSearch);
                $('#tabPanelTransfer').toggleClass('hidden', isSearch);
                $('#tabBtnSearch').toggleClass('text-indigo-600 bg-white dark:bg-gray-800 dark:text-indigo-400', isSearch);
                $('#tabBtnSearch').toggleClass('text-gray-500 bg-gray-50 dark:bg-gray-900 dark:text-gray-400', !isSearch);
                $('#tabBtnTransfer').toggleClass('text-indigo-600 bg-white dark:bg-gray-800 dark:text-indigo-400', !isSearch);
                $('#tabBtnTransfer').toggleClass('text-gray-500 bg-gray-50 dark:bg-gray-900 dark:text-gray-400', isSearch);
            }
            $('#tabBtnSearch').on('click', () => showTab('search'));
            $('#tabBtnTransfer').on('click', () => showTab('transfer'));

            // ===== ms_user picker helper (shared by Set Status + Edit Line modals) =====
            function initUsernameSelect($select, $dropdownParent) {
                if (!$select.data('select2Init')) {
                    $select.html($('#usernameOptionsTemplate').html());
                    $select.select2({
                        width: '100%',
                        dropdownParent: $dropdownParent,
                        placeholder: 'Select approver(s)…'
                    });
                    $select.data('select2Init', true);
                }
            }
            function setUsernameSelectValue($select, raw) {
                const values = (raw || '').split(/[;,]/).map(s => s.trim()).filter(Boolean);
                $select.val(values).trigger('change');
            }

            const statusLabels = { P: 'Waiting', A: 'Approved', R: 'Rejected', D: 'Revise', X: 'Cancelled', C: 'Completed', H: 'Draft/Hold' };
            const statusClasses = {
                P: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                A: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                R: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                D: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                X: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                C: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                H: 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
            };
            function statusBadge(status) {
                if (!status) return '<span class="text-gray-400">—</span>';
                const cls = statusClasses[status] || 'bg-gray-100 text-gray-600';
                const label = statusLabels[status] || status;
                return `<span class="inline-block rounded-full px-2 py-0.5 text-xs font-semibold ${cls}">${label}</span>`;
            }
            function doctypeBadge(label) {
                if (!label) return '<span class="text-gray-400">—</span>';
                return `<span class="inline-block rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">${label}</span>`;
            }

            // ===== Search table =====
            let table = $('#manageApprovalTable').DataTable({
                ajax: {
                    url: "{{ $maSby ? route('manage-approvals-sby.json') : route('manage-approvals.json') }}",
                    type: 'GET',
                    data: function(d) {
                        d.refnbr = $('#filterRefnbr').val();
                        d.doctype = $('#filterDoctype').val();
                        d.username = $('#filterUsername').val();
                        d.status = $('#filterStatus').val();
                    },
                    dataSrc: function(json) {
                        if (json.message) {
                            Swal.fire({ icon: 'info', title: 'Search', text: json.message, timer: 2200, showConfirmButton: false });
                        }
                        return json.data || [];
                    }
                },
                processing: true,
                serverSide: false,
                ordering: true,
                order: [],
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                columns: [
                    { data: 'refnbr', render: (v) => `<span class="font-medium text-gray-800 dark:text-gray-100">${v}</span>` },
                    { data: 'doctype_label', render: doctypeBadge },
                    { data: 'aprv_leveling', className: 'text-center', type: 'num' },
                    {
                        data: null,
                        render: (row) => `<div>${row.aprv_username ?? ''}</div><div class="text-xs text-gray-400">${row.aprv_name ?? ''}</div>`
                    },
                    { data: 'status', className: 'text-center', render: statusBadge },
                    { data: 'header_status', className: 'text-center', render: statusBadge },
                    {
                        data: null,
                        render: (row) => `<div>${row.updated_by ?? ''}</div><div class="text-xs text-gray-400">${row.updated_at ?? ''}</div>`
                    },
                    {
                        data: null,
                        className: 'text-center',
                        orderable: false,
                        render: (row) => {
                            const setStatusBtn = row.set_status_allowed
                                ? `<button class="setStatusBtn inline-flex items-center gap-1 rounded-full bg-rose-500 px-2.5 py-1 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-rose-600"
                                    data-id="${row.id}" data-refnbr="${row.refnbr}" data-doctype="${row.doctype_label}"
                                    data-level="${row.aprv_leveling}" data-status="${row.status}" data-username="${row.aprv_username ?? ''}">
                                    <i class="fa-solid fa-sliders text-[10px]"></i>Set Status
                                </button>`
                                : `<button class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-400 cursor-not-allowed dark:bg-gray-700 dark:text-gray-500" disabled><i class="fa-solid fa-sliders text-[10px]"></i>Set Status</button>`;
                            return `<div class="flex justify-center gap-1.5">
                                ${setStatusBtn}
                                <button class="editLineBtn inline-flex items-center gap-1 rounded-full bg-indigo-500 px-2.5 py-1 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-indigo-600"
                                    data-id="${row.id}" data-refnbr="${row.refnbr}" data-doctype="${row.doctype_label}"
                                    data-level="${row.aprv_leveling}" data-username="${row.aprv_username ?? ''}" data-name="${row.aprv_name ?? ''}">
                                    <i class="fa-solid fa-pen text-[10px]"></i>Edit
                                </button>
                            </div>`;
                        }
                    },
                ],
            });

            // ===== select2-ify all dropdowns =====
            $('#filterDoctype, #filterStatus').select2({ width: '100%' });
            $('#filterUsername').select2({ width: '100%', placeholder: 'Any approver' });
            $('#transferFrom, #transferDoctype').select2({ width: '100%' });
            $('#transferTo').select2({ width: '100%', placeholder: 'Select user(s)…' });

            $('#searchBtn').on('click', () => table.ajax.reload());
            $('#clearFiltersBtn').on('click', function() {
                $('#filterRefnbr').val('');
                $('#filterDoctype').val('').trigger('change');
                $('#filterUsername').val('').trigger('change');
                $('#filterStatus').val('').trigger('change');
                table.clear().draw();
            });
            $('#filterRefnbr').on('keydown', function(e) {
                if (e.key === 'Enter') table.ajax.reload();
            });
            $('#filterDoctype, #filterStatus, #filterUsername').on('change', () => table.ajax.reload());

            // ===== Set Status modal =====
            let setStatusRowId = null;

            const warningToneAmber = 'border-amber-300 bg-amber-50 text-amber-800 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-300';
            const warningToneRed = 'border-red-400 bg-red-50 text-red-800 dark:border-red-700 dark:bg-red-900/20 dark:text-red-300';
            function setWarningTone(isDanger) {
                $('#setStatusWarning')
                    .removeClass(warningToneAmber + ' ' + warningToneRed)
                    .addClass(isDanger ? warningToneRed : warningToneAmber);
            }

            function runSetStatusPreview() {
                if (!setStatusRowId) return;

                $.ajax({
                    url: `{{ $maBase }}/${setStatusRowId}/set-status`,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    data: { preview: 1, new_status: $('#setStatusNewStatus').val() },
                    success: function(res) {
                        if (!res.success) {
                            setWarningTone(true);
                            $('#setStatusWarning').removeClass('hidden').text(res.message);
                            $('#setStatusConfirmBtn').prop('disabled', true);
                            return;
                        }
                        setWarningTone(!!res.newer_generation_conflict);
                        $('#setStatusWarning').removeClass('hidden').text(res.warning);
                        $('#setStatusConfirmBtn').prop('disabled', false);
                    },
                    error: function(xhr) {
                        setWarningTone(true);
                        $('#setStatusWarning').removeClass('hidden').text(xhr.responseJSON?.message || 'Preview failed.');
                        $('#setStatusConfirmBtn').prop('disabled', true);
                    }
                });
            }

            $(document).on('click', '.setStatusBtn', function() {
                setStatusRowId = $(this).data('id');

                $('#setStatusRefnbr').text($(this).data('refnbr'));
                $('#setStatusDoctype').text($(this).data('doctype'));
                $('#setStatusLevel').text($(this).data('level'));
                $('#setStatusCurrent').html(statusBadge($(this).data('status')));
                $('#setStatusWarning').addClass('hidden').text('');

                $('#setStatusModal').removeClass('hidden').addClass('flex');

                if (!$('#setStatusNewStatus').data('select2Init')) {
                    $('#setStatusNewStatus').select2({ width: '100%', dropdownParent: $('#setStatusModal') });
                    $('#setStatusNewStatus').data('select2Init', true);
                }
                initUsernameSelect($('#setStatusUsername'), $('#setStatusModal'));
                setUsernameSelectValue($('#setStatusUsername'), $(this).data('username'));

                // .trigger('change') syncs select2's display and also fires the
                // preview via the #setStatusNewStatus change handler below.
                $('#setStatusNewStatus').val('P').trigger('change');
            });

            $('#setStatusNewStatus').on('change', runSetStatusPreview);

            $('#setStatusModalClose, #setStatusCancelBtn').on('click', function() {
                $('#setStatusModal').removeClass('flex').addClass('hidden');
                setStatusRowId = null;
            });

            $('#setStatusConfirmBtn').on('click', function() {
                if (!setStatusRowId) return;

                const selected = $('#setStatusUsername').val() || [];
                if (!selected.length) {
                    Swal.fire({ icon: 'warning', title: 'Pick an approver', text: 'Select at least one approver from the list.' });
                    return;
                }

                const newStatus = $('#setStatusNewStatus').val();
                const newStatusLabel = $('#setStatusNewStatus option:selected').text();

                Swal.fire({
                    icon: 'warning',
                    title: 'Are you sure?',
                    text: `This will set the step to "${newStatusLabel}". This modifies live transactional data.`,
                    showCancelButton: true,
                    confirmButtonText: 'Yes, set status'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: `{{ $maBase }}/${setStatusRowId}/set-status`,
                        type: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        data: {
                            new_status: newStatus,
                            aprv_username: selected.join(','),
                        },
                        success: function(res) {
                            if (!res.success) {
                                Swal.fire({ icon: 'error', title: 'Set status failed', text: res.message });
                                return;
                            }
                            $('#setStatusModal').removeClass('flex').addClass('hidden');
                            table.ajax.reload(null, false);
                            Swal.fire({ icon: 'success', title: 'Status updated', timer: 1800, showConfirmButton: false });
                        },
                        error: function(xhr) {
                            Swal.fire({ icon: 'error', title: 'Set status failed', text: xhr.responseJSON?.message || 'Request failed.' });
                        }
                    });
                });
            });

            // ===== Edit line modal =====
            let editLineRowId = null;

            $(document).on('click', '.editLineBtn', function() {
                editLineRowId = $(this).data('id');
                $('#editLineRefnbr').text($(this).data('refnbr'));
                $('#editLineDoctype').text($(this).data('doctype'));
                $('#editLineLevel').text($(this).data('level'));
                $('#editLineModal').removeClass('hidden').addClass('flex');
                initUsernameSelect($('#editLineUsername'), $('#editLineModal'));
                setUsernameSelectValue($('#editLineUsername'), $(this).data('username'));
            });

            $('#editLineModalClose, #editLineCancelBtn').on('click', function() {
                $('#editLineModal').removeClass('flex').addClass('hidden');
                editLineRowId = null;
            });

            $('#editLineConfirmBtn').on('click', function() {
                if (!editLineRowId) return;

                const selected = $('#editLineUsername').val() || [];
                if (!selected.length) {
                    Swal.fire({ icon: 'warning', title: 'Pick an approver', text: 'Select at least one approver from the list.' });
                    return;
                }

                $.ajax({
                    url: `{{ $maBase }}/${editLineRowId}/update-line`,
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    data: {
                        aprv_username: selected.join(','),
                    },
                    success: function() {
                        $('#editLineModal').removeClass('flex').addClass('hidden');
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Saved', timer: 1500, showConfirmButton: false });
                    },
                    error: function(xhr) {
                        Swal.fire({ icon: 'error', title: 'Save failed', text: xhr.responseJSON?.message || 'Request failed.' });
                    }
                });
            });

            // ===== Transfer tab =====
            let transferPreviewRows = [];

            $('#transferPreviewBtn').on('click', function() {
                const from = $('#transferFrom').val();
                const to = $('#transferTo').val() || [];

                if (!from || !to.length) {
                    Swal.fire({ icon: 'warning', title: 'Pick both users', text: 'Select a From User and at least one To User.' });
                    return;
                }
                if (to.includes(from)) {
                    Swal.fire({ icon: 'warning', title: 'Same user', text: 'To User can\'t include the From User.' });
                    return;
                }

                $.ajax({
                    url: "{{ $maSby ? route('manage-approvals-sby.transfer.preview') : route('manage-approvals.transfer.preview') }}",
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    data: {
                        from_username: from,
                        to_usernames: to,
                        doctype: $('#transferDoctype').val(),
                        refnbr: $('#transferRefnbr').val(),
                    },
                    success: function(res) {
                        transferPreviewRows = res.rows || [];

                        $('#transferWarning').toggleClass('hidden', !res.warning).text(res.warning || '');

                        const $tbody = $('#transferPreviewTable tbody').empty();
                        transferPreviewRows.forEach((row) => {
                            $tbody.append(`
                                <tr>
                                    <td><input type="checkbox" class="transferRowCheck" data-id="${row.id}" checked></td>
                                    <td><span class="font-medium text-gray-800 dark:text-gray-100">${row.refnbr}</span></td>
                                    <td>${doctypeBadge(row.aprv_doctype)}</td>
                                    <td class="text-center">${row.aprv_leveling}</td>
                                    <td>
                                        <div>${row.aprv_username ?? ''}</div>
                                        <div class="text-xs text-gray-400">${row.aprv_name ?? ''}</div>
                                    </td>
                                    <td class="text-center">${statusBadge(row.status)}</td>
                                    <td>${row.aprv_cpnyid ?? ''}</td>
                                    <td>${row.aprv_departementid ?? ''}</td>
                                    <td class="text-xs text-gray-400">${row.created_at ?? ''}</td>
                                </tr>
                            `);
                        });

                        $('#transferCountLabel').text(`${transferPreviewRows.length} pending approval(s) found`);
                        $('#transferResultsWrap').toggleClass('hidden', transferPreviewRows.length === 0);
                        $('#transferConfirmBtn').prop('disabled', transferPreviewRows.length === 0);
                    },
                    error: function(xhr) {
                        Swal.fire({ icon: 'error', title: 'Preview failed', text: xhr.responseJSON?.message || 'Request failed.' });
                    }
                });
            });

            $('#transferConfirmBtn').on('click', function() {
                const rowIds = $('.transferRowCheck:checked').map(function() { return $(this).data('id'); }).get();

                if (!rowIds.length) {
                    Swal.fire({ icon: 'warning', title: 'No rows selected', text: 'Check at least one row to transfer.' });
                    return;
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Are you sure?',
                    text: `This will transfer ${rowIds.length} pending approval(s). This modifies live transactional data.`,
                    showCancelButton: true,
                    confirmButtonText: 'Yes, transfer'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: "{{ $maSby ? route('manage-approvals-sby.transfer.confirm') : route('manage-approvals.transfer.confirm') }}",
                        type: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        data: {
                            from_username: $('#transferFrom').val(),
                            to_usernames: $('#transferTo').val() || [],
                            row_ids: rowIds,
                        },
                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Transferred',
                                text: `${res.rows_changed} row(s) transferred.` + (res.skipped.length ? ` ${res.skipped.length} row(s) skipped (changed since preview).` : ''),
                            });
                            $('#transferPreviewBtn').trigger('click');
                        },
                        error: function(xhr) {
                            Swal.fire({ icon: 'error', title: 'Transfer failed', text: xhr.responseJSON?.message || 'Request failed.' });
                        }
                    });
                });
            });
        })();
    </script>
</x-app-layout>
