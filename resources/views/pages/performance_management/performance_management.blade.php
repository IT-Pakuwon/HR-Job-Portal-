<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- ── TABS ────────────────────────────────────────────────────────────── --}}
        <div>
            <div class="flex gap-1 border-b border-gray-200 dark:border-gray-700 mb-0">
                <button type="button" id="tab-compare"
                    class="pmTabBtn px-5 py-2.5 text-sm font-semibold rounded-t-lg border border-b-0 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400"
                    onclick="switchPmTab('compare')">
                    🔄 Comparison
                </button>
                <button type="button" id="tab-user"
                    class="pmTabBtn px-5 py-2.5 text-sm font-semibold rounded-t-lg border border-b-0 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                    onclick="switchPmTab('user')">
                    👤 User
                </button>
                <button type="button" id="tab-duplicates"
                    class="pmTabBtn px-5 py-2.5 text-sm font-semibold rounded-t-lg border border-b-0 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                    onclick="switchPmTab('duplicates')">
                    🧬 Duplicates
                </button>
            </div>

            {{-- ── TAB 1: Comparison ──────────────────────────────────────────── --}}
            <div id="panel-compare"
                class="rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-2 dark:border-white/[0.06]">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">
                        🔄 Talenta (Live) vs Local Cache (users_talenta)
                    </h2>
                    <div class="flex items-center gap-3">
                        <span id="pendingCount" class="text-xs font-medium text-gray-500 dark:text-gray-400"></span>
                        <button id="syncAllBtn"
                            class="inline-flex h-9 items-center justify-center rounded-lg bg-indigo-600 px-4 text-sm font-medium text-white transition hover:bg-indigo-500">
                            ⇅ Sync All Pending
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-3 p-3">
                    {{-- Panel A: Talenta Live --}}
                    <div class="rounded-lg border border-gray-100 dark:border-white/[0.06]">
                        <div class="flex items-center justify-between px-3 py-2 border-b border-gray-100 dark:border-white/[0.06]">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                Talenta (Live)
                                <span class="ml-1 font-normal text-gray-400 dark:text-gray-500">— click a row to compare with Local Cache →</span>
                            </h3>
                            <select id="liveStatusFilter" class="text-xs rounded-md border-gray-200 dark:border-gray-700 dark:bg-gray-800">
                                <option value="" selected>All</option>
                                <option value="new">New</option>
                                <option value="changed">Changed</option>
                                <option value="different">Different Information</option>
                                <option value="resigned">Resigned</option>
                            </select>
                        </div>
                        <div class="relative overflow-x-auto">
                            <table id="liveTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                                <thead>
                                    <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                        <th class="px-3 py-2 text-left font-medium">Status</th>
                                        <th class="px-3 py-2 text-left font-medium">Name</th>
                                        <th class="px-3 py-2 text-left font-medium">NPK</th>
                                        <th class="px-3 py-2 text-left font-medium">Position</th>
                                        <th class="px-3 py-2 text-left font-medium">Job Level</th>
                                        <th class="px-3 py-2 text-left font-medium">Organization</th>
                                        <th class="px-3 py-2 text-left font-medium">Employment Status</th>
                                        <th class="w-28 px-3 py-2 text-left font-medium">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Panel B: Local Cache --}}
                    <div class="rounded-lg border border-gray-100 dark:border-white/[0.06]">
                        <div class="flex items-center justify-between px-3 py-2 border-b border-gray-100 dark:border-white/[0.06]">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Local Cache (users_talenta)</h3>
                            <select id="localStatusFilter" class="text-xs rounded-md border-gray-200 dark:border-gray-700 dark:bg-gray-800">
                                <option value="" selected>All</option>
                                <option value="changed">Changed</option>
                                <option value="different">Different Information</option>
                                <option value="resigned">Resigned</option>
                            </select>
                        </div>
                        <div class="relative overflow-x-auto">
                            <table id="localTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                                <thead>
                                    <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                        <th class="px-3 py-2 text-left font-medium">Status</th>
                                        <th class="px-3 py-2 text-left font-medium">Name</th>
                                        <th class="px-3 py-2 text-left font-medium">NPK</th>
                                        <th class="px-3 py-2 text-left font-medium">Position</th>
                                        <th class="px-3 py-2 text-left font-medium">Job Level</th>
                                        <th class="px-3 py-2 text-left font-medium">Organization</th>
                                        <th class="px-3 py-2 text-left font-medium">Employment Status</th>
                                        <th class="w-24 px-3 py-2 text-left font-medium">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── TAB 2: User ─────────────────────────────────────────────────── --}}
            <div id="panel-user"
                class="hidden rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-2 dark:border-white/[0.06]">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">
                        👤 Talenta People Without a User Account
                    </h2>
                </div>

                <div class="relative overflow-x-auto">
                    <table id="candidatesTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-8 px-2 py-3 text-center"></th>
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Name</th>
                                <th class="px-4 py-3 text-left font-medium">NPK</th>
                                <th class="px-4 py-3 text-left font-medium">Email (Talenta)</th>
                                <th class="px-4 py-3 text-left font-medium">Position</th>
                                <th class="px-4 py-3 text-left font-medium">Job Level</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between border-b border-t border-gray-100 px-5 py-2 dark:border-white/[0.06]">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">
                        🚫 Users Whose Talenta Record Has Resigned
                    </h2>
                </div>

                <div class="relative overflow-x-auto">
                    <table id="resignedUsersTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-8 px-2 py-3 text-center"></th>
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Name</th>
                                <th class="px-4 py-3 text-left font-medium">Username</th>
                                <th class="px-4 py-3 text-left font-medium">NPK</th>
                                <th class="px-4 py-3 text-left font-medium">Position</th>
                                <th class="px-4 py-3 text-left font-medium">Job Level</th>
                                <th class="px-4 py-3 text-left font-medium">Employment Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between border-b border-t border-gray-100 px-5 py-2 dark:border-white/[0.06]">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">
                        🔗 das Users with user_id_talenta = NULL
                    </h2>
                    <span class="text-xs text-gray-400 dark:text-gray-500">Matched to Local Cache (users_talenta) where employee_id = NPK</span>
                </div>

                <div class="relative overflow-x-auto">
                    <table id="missingLinkTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-8 px-2 py-3 text-center"></th>
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Name</th>
                                <th class="px-4 py-3 text-left font-medium">Username</th>
                                <th class="px-4 py-3 text-left font-medium">NPK</th>
                                <th class="px-4 py-3 text-left font-medium">Matched Talenta Name</th>
                                <th class="px-4 py-3 text-left font-medium">Employment Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            {{-- ── TAB 3: Duplicates ───────────────────────────────────────────── --}}
            <div id="panel-duplicates"
                class="hidden rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-2 dark:border-white/[0.06]">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">
                        🧬 Local Cache — Possible Duplicate People
                    </h2>
                    <span class="text-xs text-gray-400 dark:text-gray-500">Same name + birth date + identity number</span>
                </div>

                <div class="relative overflow-x-auto">
                    <table id="dupLocalTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="px-3 py-2 text-left font-medium">Group</th>
                                <th class="px-3 py-2 text-left font-medium">Name</th>
                                <th class="px-3 py-2 text-left font-medium">Birth Date</th>
                                <th class="px-3 py-2 text-left font-medium">Identity Number</th>
                                <th class="px-3 py-2 text-left font-medium">NPK</th>
                                <th class="px-3 py-2 text-left font-medium">Position</th>
                                <th class="px-3 py-2 text-left font-medium">Organization</th>
                                <th class="px-3 py-2 text-left font-medium">Employment Status</th>
                                <th class="w-36 px-3 py-2 text-left font-medium">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between border-b border-t border-gray-100 px-5 py-2 dark:border-white/[0.06]">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">
                        🧬 Talenta (Live) — Possible Duplicate People
                    </h2>
                    <span class="text-xs text-gray-400 dark:text-gray-500">Same name + birth date + identity number</span>
                </div>

                <div class="relative overflow-x-auto">
                    <table id="dupLiveTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="px-3 py-2 text-left font-medium">Group</th>
                                <th class="px-3 py-2 text-left font-medium">Name</th>
                                <th class="px-3 py-2 text-left font-medium">Birth Date</th>
                                <th class="px-3 py-2 text-left font-medium">Identity Number</th>
                                <th class="px-3 py-2 text-left font-medium">NPK</th>
                                <th class="px-3 py-2 text-left font-medium">Position</th>
                                <th class="px-3 py-2 text-left font-medium">Organization</th>
                                <th class="px-3 py-2 text-left font-medium">Employment Status</th>
                                <th class="w-36 px-3 py-2 text-left font-medium">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal: Create User -->
        <div id="createUserModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 class="mb-4 text-base font-bold text-gray-800 dark:text-white">Create User</h2>
                <form id="createUserForm">
                    @csrf
                    <input type="hidden" id="cu_user_id" name="user_id">

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Name</label>
                        <input type="text" id="cu_name" class="w-full rounded-lg border px-3 py-2 bg-gray-100 dark:bg-gray-600" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">NPK</label>
                        <input type="text" id="cu_npk" class="w-full rounded-lg border px-3 py-2 bg-gray-100 dark:bg-gray-600" readonly>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-gray-700 dark:text-white">Position</label>
                            <input type="text" id="cu_position" class="w-full rounded-lg border px-3 py-2 bg-gray-100 dark:bg-gray-600" readonly>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Job Level</label>
                            <input type="text" id="cu_job_level" class="w-full rounded-lg border px-3 py-2 bg-gray-100 dark:bg-gray-600" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Role</label>
                        <input type="text" value="User" class="w-full rounded-lg border px-3 py-2 bg-gray-100 dark:bg-gray-600" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Username <span class="text-red-500">*</span></label>
                        <input type="text" id="cu_username" name="username"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Password</label>
                        <input type="text" id="cu_password" name="password"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" value="pakuwon1234#">
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Email</label>
                        <input type="email" id="cu_email" name="email"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" value="noemail@test.com">
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeCreateUserModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="loadingOverlay" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/40">
        <div class="flex items-center gap-3 rounded-xl bg-white px-6 py-4 shadow-lg dark:bg-gray-800">
            <svg class="h-6 w-6 animate-spin text-indigo-600" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Processing...</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function showLoading() { $('#loadingOverlay').removeClass('hidden'); }
        function hideLoading() { $('#loadingOverlay').addClass('hidden'); }

        function pmStatusBadge(status, fields) {
            const map = {
                new: 'bg-blue-300/30 text-blue-600',
                changed: 'bg-amber-300/30 text-amber-600',
                different: 'bg-slate-300/30 text-slate-600',
                same: 'bg-green-300/30 text-green-600',
                local_only: 'bg-gray-300/30 text-gray-600',
            };
            const label = {
                new: 'New',
                changed: 'Changed',
                different: 'Different',
                same: 'Same',
                local_only: 'Local Only',
            };
            const cls = map[status] || map.same;
            const hasFields = fields && fields.length;
            const title = hasFields ? ` title="${fields.join(', ')}"` : '';
            const cursor = hasFields ? ' cursor-help' : '';
            return `<span class="inline-block rounded px-2 py-1 text-xs font-semibold ${cls}${cursor}"${title}>${label[status] || status}${hasFields ? ' ⓘ' : ''}</span>`;
        }

        function pmEmploymentStatusBadge(statusTalenta) {
            if (!statusTalenta) return '-';
            const isResigned = /resign/i.test(statusTalenta);
            const cls = isResigned ? 'bg-red-300/30 text-red-600' : 'bg-green-300/30 text-green-600';
            return `<span class="inline-block rounded px-2 py-1 text-xs font-semibold ${cls}">${statusTalenta}</span>`;
        }

        const initedPmTabs = { compare: false, user: false, duplicates: false };
        const pmActiveClasses = 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400';
        const pmInactiveClasses = 'bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400';

        function switchPmTab(tab) {
            const panels = { compare: '#panel-compare', user: '#panel-user', duplicates: '#panel-duplicates' };
            const btns = { compare: '#tab-compare', user: '#tab-user', duplicates: '#tab-duplicates' };

            Object.keys(panels).forEach(function(key) {
                const isActive = key === tab;
                $(panels[key]).toggleClass('hidden', !isActive);
                $(btns[key]).toggleClass(pmActiveClasses, isActive).toggleClass(pmInactiveClasses, !isActive);
            });

            if (!initedPmTabs[tab]) {
                initedPmTabs[tab] = true;
                if (tab === 'compare') { initLiveTable(); initLocalTable(); }
                if (tab === 'user') { initCandidatesTable(); initResignedUsersTable(); initMissingLinkTable(); }
                if (tab === 'duplicates') { initDupLocalTable(); initDupLiveTable(); }
            } else if (tab === 'compare') {
                window.liveTable && window.liveTable.columns.adjust();
                window.localTable && window.localTable.columns.adjust();
            } else if (tab === 'user') {
                window.candidatesTable && window.candidatesTable.columns.adjust();
                window.resignedUsersTable && window.resignedUsersTable.columns.adjust();
                window.missingLinkTable && window.missingLinkTable.columns.adjust();
            } else if (tab === 'duplicates') {
                window.dupLocalTable && window.dupLocalTable.columns.adjust();
                window.dupLiveTable && window.dupLiveTable.columns.adjust();
            }
        }

        $(document).ready(function() {

            // =========================================================
            // Comparison
            // =========================================================
            window.initLiveTable = function() {
                window.liveTable = $('#liveTable').DataTable({
                    ajax: {
                        url: "{{ route('performance-management.compare.live.json') }}",
                        type: "GET",
                        dataSrc: function(json) {
                            const total = json.data.length;
                            const pending = json.data.filter(r => r.diff_status === 'new' || r.diff_status === 'changed').length;
                            $('#pendingCount').text(`${pending} of ${total} need sync`);
                            return json.data;
                        },
                        error: function(xhr) { console.error('AJAX Error:', xhr.responseText); }
                    },
                    processing: true,
                    serverSide: false,
                    autoWidth: false,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                    order: [],
                    columnDefs: [
                        { targets: 0, orderable: false },
                        { targets: 7, orderable: false, searchable: false, className: 'text-center' },
                    ],
                    createdRow: function(row, data) {
                        $(row).attr('data-employee-id', data.employee_id || '')
                            .addClass('cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/40');
                    },
                    columns: [
                        {
                            data: 'diff_status',
                            render: (data, type, row) => pmStatusBadge(data, row.changed_fields)
                        },
                        { data: 'name' },
                        { data: 'employee_id', defaultContent: '-' },
                        { data: 'job_position', defaultContent: '-' },
                        { data: 'job_level', defaultContent: '-' },
                        { data: 'organization_name', defaultContent: '-' },
                        { data: 'status_talenta', render: (d) => pmEmploymentStatusBadge(d) },
                        {
                            data: 'user_id',
                            render: function(data, type, row) {
                                let buttons = '';
                                if (row.diff_status === 'new') {
                                    buttons += `<button class="syncOneBtn bg-indigo-600 text-white px-2 py-1 rounded text-xs" data-user-id="${data}">Sync</button>`;
                                } else if (row.diff_status === 'changed') {
                                    buttons += `<button class="syncOneBtn bg-amber-600 text-white px-2 py-1 rounded text-xs" data-user-id="${data}">Update</button>`;
                                }
                                const isResigned = /resign/i.test(row.status_talenta || '');
                                if (isResigned && row.local_status && row.local_status !== 'X') {
                                    buttons += `<button class="deactivateLocalBtn bg-red-600 text-white px-2 py-1 rounded text-xs mt-1" data-user-id="${data}">Deactivate</button>`;
                                }
                                return `<div class="flex flex-col items-start gap-1">${buttons}</div>`;
                            }
                        },
                    ]
                });

                // Clicking a Live row narrows the Local panel down to the same person
                // (matched by NPK), so the two panels can actually be compared side by
                // side instead of showing unrelated people on the same visual row.
                $('#liveTable tbody').on('click', 'tr', function(e) {
                    if ($(e.target).closest('button').length) return;

                    const npk = $(this).data('employee-id') || '';
                    const alreadySelected = $(this).hasClass('pm-row-selected');

                    $('#liveTable tbody tr').removeClass('pm-row-selected bg-indigo-50 dark:bg-indigo-900/20');

                    if (alreadySelected || !npk) {
                        window.localTable.column(2).search('').draw();
                        return;
                    }

                    $(this).addClass('pm-row-selected bg-indigo-50 dark:bg-indigo-900/20');
                    window.localTable.column(2)
                        .search('^' + $.fn.dataTable.util.escapeRegex(npk) + '$', true, false)
                        .draw();
                });

                $('#liveStatusFilter').on('change', function() {
                    const val = $(this).val();
                    if (val === 'resigned') {
                        window.liveTable.column(0).search('').draw(false);
                        window.liveTable.column(6).search('resign', true, false).draw();
                    } else {
                        window.liveTable.column(6).search('').draw(false);
                        window.liveTable.column(0).search(val, true, false).draw();
                    }
                });
                $('#liveStatusFilter').trigger('change');
            }

            window.initLocalTable = function() {
                window.localTable = $('#localTable').DataTable({
                    ajax: {
                        url: "{{ route('performance-management.compare.local.json') }}",
                        type: "GET",
                        dataSrc: 'data',
                        error: function(xhr) { console.error('AJAX Error:', xhr.responseText); }
                    },
                    processing: true,
                    serverSide: false,
                    autoWidth: false,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                    order: [],
                    columns: [
                        { data: 'diff_status', render: (data, type, row) => pmStatusBadge(data, row.changed_fields) },
                        { data: 'name' },
                        { data: 'employee_id', defaultContent: '-' },
                        { data: 'job_position', defaultContent: '-' },
                        { data: 'job_level', defaultContent: '-' },
                        { data: 'organization_name', defaultContent: '-' },
                        { data: 'status_talenta', render: (d) => pmEmploymentStatusBadge(d) },
                        {
                            data: 'user_id',
                            render: function(data, type, row) {
                                const isResigned = /resign/i.test(row.status_talenta || '');
                                if (!isResigned || row.status === 'X') return '';
                                return `<button class="deactivateLocalBtn bg-red-600 text-white px-2 py-1 rounded text-xs" data-user-id="${data}">Deactivate</button>`;
                            }
                        },
                    ]
                });

                $('#localStatusFilter').on('change', function() {
                    const val = $(this).val();
                    if (val === 'resigned') {
                        window.localTable.column(0).search('').draw(false);
                        window.localTable.column(6).search('resign', true, false).draw();
                    } else {
                        window.localTable.column(6).search('').draw(false);
                        window.localTable.column(0).search(val, true, false).draw();
                    }
                });
            }

            $(document).on('click', '.syncOneBtn', function() {
                const userId = $(this).data('user-id');
                showLoading();
                $.ajax({
                    url: "{{ route('performance-management.compare.sync') }}",
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { user_id: userId },
                    success: function() {
                        hideLoading();
                        window.liveTable.ajax.reload(null, false);
                        window.localTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        hideLoading();
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Gagal sync data' });
                    }
                });
            });

            $(document).on('click', '.deactivateLocalBtn', function() {
                const userId = $(this).data('user-id');
                Swal.fire({
                    icon: 'warning',
                    title: 'Deactivate this record?',
                    text: 'Sets status to X in the local cache (users_talenta) because this person is Resigned in Talenta.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, deactivate',
                    confirmButtonColor: '#dc2626',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    showLoading();
                    $.ajax({
                        url: "{{ route('performance-management.compare.deactivate') }}",
                        type: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: { user_id: userId },
                        success: function() {
                            hideLoading();
                            window.liveTable.ajax.reload(null, false);
                            window.localTable.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            hideLoading();
                            Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Gagal deactivate data' });
                        }
                    });
                });
            });

            $('#syncAllBtn').click(function() {
                Swal.fire({
                    icon: 'question',
                    title: 'Sync all pending data?',
                    text: 'This will insert every New record and update every Changed record in the local cache. For Changed records, the previous job position/level/organization is archived into old_job_position, old_job_level, and old_organization_name before being overwritten.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, sync all',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    showLoading();
                    $.ajax({
                        url: "{{ route('performance-management.compare.sync-all') }}",
                        type: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        success: function(res) {
                            hideLoading();
                            window.liveTable.ajax.reload(null, false);
                            window.localTable.ajax.reload(null, false);
                            Swal.fire({ icon: 'success', title: 'Synced', text: `${res.synced} record(s) synced`, timer: 1800, showConfirmButton: false });
                        },
                        error: function(xhr) {
                            hideLoading();
                            Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Gagal sync semua data' });
                        }
                    });
                });
            });

            // =========================================================
            // User
            // =========================================================
            window.initCandidatesTable = function() {
                window.candidatesTable = $('#candidatesTable').DataTable({
                    ajax: {
                        url: "{{ route('performance-management.users.json') }}",
                        type: "GET",
                        dataSrc: 'data',
                        error: function(xhr) { console.error('AJAX Error:', xhr.responseText); }
                    },
                    processing: true,
                    serverSide: false,
                    autoWidth: false,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                    responsive: { details: { type: 'column', target: 0 } },
                    columnDefs: [
                        { targets: 0, className: 'dtr-control', orderable: false, searchable: false, width: '28px', defaultContent: '' },
                        { targets: 1, orderable: false, searchable: false, className: 'text-center' },
                    ],
                    columns: [
                        { data: null, defaultContent: '' },
                        {
                            data: 'user_id',
                            render: function(data, type, row) {
                                return `<button class="createUserBtn bg-blue-500 text-white px-2 py-1 rounded"
                                    data-user-id="${data}"
                                    data-name="${row.name}"
                                    data-npk="${row.employee_id ?? ''}"
                                    data-position="${row.job_position ?? ''}"
                                    data-job-level="${row.job_level ?? ''}">+ Create</button>`;
                            }
                        },
                        { data: 'name' },
                        { data: 'employee_id', defaultContent: '-' },
                        { data: 'email', defaultContent: '-' },
                        { data: 'job_position', defaultContent: '-' },
                        { data: 'job_level', defaultContent: '-' },
                    ]
                });
            }

            window.initResignedUsersTable = function() {
                window.resignedUsersTable = $('#resignedUsersTable').DataTable({
                    ajax: {
                        url: "{{ route('performance-management.users.resigned.json') }}",
                        type: "GET",
                        dataSrc: 'data',
                        error: function(xhr) { console.error('AJAX Error:', xhr.responseText); }
                    },
                    processing: true,
                    serverSide: false,
                    autoWidth: false,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                    responsive: { details: { type: 'column', target: 0 } },
                    columnDefs: [
                        { targets: 0, className: 'dtr-control', orderable: false, searchable: false, width: '28px', defaultContent: '' },
                        { targets: 1, orderable: false, searchable: false, className: 'text-center' },
                    ],
                    columns: [
                        { data: null, defaultContent: '' },
                        {
                            data: 'id',
                            render: function(data) {
                                return `<button class="deactivateUserBtn bg-red-600 text-white px-2 py-1 rounded" data-id="${data}">Deactivate</button>`;
                            }
                        },
                        { data: 'name' },
                        { data: 'username' },
                        { data: 'npk', defaultContent: '-' },
                        { data: 'position', defaultContent: '-' },
                        { data: 'job_level', defaultContent: '-' },
                        { data: 'status_talenta', render: (d) => pmEmploymentStatusBadge(d) },
                    ]
                });
            }

            window.initMissingLinkTable = function() {
                window.missingLinkTable = $('#missingLinkTable').DataTable({
                    ajax: {
                        url: "{{ route('performance-management.users.missing-link.json') }}",
                        type: "GET",
                        dataSrc: 'data',
                        error: function(xhr) { console.error('AJAX Error:', xhr.responseText); }
                    },
                    processing: true,
                    serverSide: false,
                    autoWidth: false,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                    responsive: { details: { type: 'column', target: 0 } },
                    columnDefs: [
                        { targets: 0, className: 'dtr-control', orderable: false, searchable: false, width: '28px', defaultContent: '' },
                        { targets: 1, orderable: false, searchable: false, className: 'text-center' },
                    ],
                    columns: [
                        { data: null, defaultContent: '' },
                        {
                            data: 'id',
                            render: function(data, type, row) {
                                if (row.is_resigned) {
                                    return `<button class="deactivateUnlinkedBtn bg-red-600 text-white px-2 py-1 rounded"
                                        data-id="${data}" data-name="${row.name}">Deactivate</button>`;
                                }
                                if (!row.match_user_id) {
                                    const title = row.npk
                                        ? 'No users_talenta record with a matching NPK'
                                        : 'No NPK, and no unique users_talenta record with a matching name';
                                    return `<span class="text-xs text-gray-400" title="${title}">No match</span>`;
                                }
                                const label = row.match_by === 'name' ? 'Link (by name)' : 'Link';
                                return `<button class="linkUserBtn bg-emerald-600 text-white px-2 py-1 rounded"
                                    data-id="${data}" data-talenta-user-id="${row.match_user_id}"
                                    data-name="${row.name}" data-match-name="${row.match_name ?? ''}"
                                    data-match-by="${row.match_by ?? ''}" data-match-npk="${row.match_npk ?? ''}">${label}</button>`;
                            }
                        },
                        { data: 'name' },
                        { data: 'username', defaultContent: '-' },
                        { data: 'npk', defaultContent: '-' },
                        { data: 'match_name', defaultContent: '-' },
                        { data: 'match_status', render: (d) => pmEmploymentStatusBadge(d) },
                    ]
                });
            }

            // =========================================================
            // Duplicates
            // =========================================================
            function dupTableOptions(routeUrl) {
                return {
                    ajax: {
                        url: routeUrl,
                        type: "GET",
                        dataSrc: 'data',
                        error: function(xhr) { console.error('AJAX Error:', xhr.responseText); }
                    },
                    processing: true,
                    serverSide: false,
                    autoWidth: false,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                    order: [[0, 'asc']],
                    createdRow: function(row, data) {
                        // Alternate a subtle band per group so the 2+ rows that make up
                        // one duplicate cluster are visually easy to tell apart from the next.
                        if (data.group_id % 2 === 0) {
                            $(row).addClass('bg-amber-50/60 dark:bg-amber-900/10');
                        }
                    },
                    columns: [
                        { data: 'group_id', render: (d, type, row) => type === 'display' ? `#${d} <span class="text-gray-400">(${row.group_size})</span>` : d },
                        { data: 'name' },
                        { data: 'birth_date', defaultContent: '-' },
                        { data: 'identity_number', defaultContent: '-' },
                        { data: 'employee_id', defaultContent: '-' },
                        { data: 'job_position', defaultContent: '-' },
                        { data: 'organization_name', defaultContent: '-' },
                        { data: 'status_talenta', render: (d) => pmEmploymentStatusBadge(d) },
                        {
                            data: 'user_id',
                            render: function(data, type, row) {
                                return `<button class="migrateDupBtn bg-emerald-600 text-white px-2 py-1 rounded text-xs"
                                    data-user-id="${data}" data-group-id="${row.group_id}">Migrate to this NPK</button>`;
                            }
                        },
                    ]
                };
            }

            window.initDupLocalTable = function() {
                window.dupLocalTable = $('#dupLocalTable').DataTable(
                    dupTableOptions("{{ route('performance-management.duplicates.local.json') }}")
                );
            }

            window.initDupLiveTable = function() {
                window.dupLiveTable = $('#dupLiveTable').DataTable(
                    dupTableOptions("{{ route('performance-management.duplicates.live.json') }}")
                );
            }

            $(document).on('click', '.migrateDupBtn', function() {
                const targetUserId = $(this).data('user-id');
                const groupId = $(this).data('group-id');

                const table = $(this).closest('table').attr('id') === 'dupLocalTable' ? window.dupLocalTable : window.dupLiveTable;
                const group = table.rows().data().toArray().filter(r => r.group_id === groupId);
                const target = group.find(r => r.user_id === targetUserId);
                const sources = group.filter(r => r.user_id !== targetUserId);

                if (!target || !sources.length) return;

                const sourceList = sources.map(s => `${s.name} (NPK ${s.employee_id})`).join(', ');

                Swal.fire({
                    icon: 'warning',
                    title: 'Migrate to this NPK?',
                    html: `<div class="text-left text-sm">
                        <b>${target.name}</b> (NPK ${target.employee_id}) becomes the primary record.<br><br>
                        These will be retired and repointed to it: <b>${sourceList}</b><br><br>
                        This updates their das login account (NPK, position, job level), deactivates their local cache record,
                        and moves their hr_ms_approval config (own row + anywhere they're listed as an approver) to the new NPK.
                    </div>`,
                    showCancelButton: true,
                    confirmButtonText: 'Yes, migrate',
                    confirmButtonColor: '#059669',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    showLoading();
                    $.ajax({
                        url: "{{ route('performance-management.duplicates.migrate') }}",
                        type: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: {
                            target_user_id: targetUserId,
                            source_user_ids: sources.map(s => s.user_id),
                        },
                        success: function(res) {
                            hideLoading();
                            window.dupLocalTable.ajax.reload(null, false);
                            window.dupLiveTable.ajax.reload(null, false);
                            window.liveTable && window.liveTable.ajax.reload(null, false);
                            window.localTable && window.localTable.ajax.reload(null, false);
                            window.resignedUsersTable && window.resignedUsersTable.ajax.reload(null, false);
                            const notesHtml = (res.notes || []).map(n => `<div class="text-xs text-left">• ${n}</div>`).join('');
                            Swal.fire({ icon: 'success', title: 'Migrated', html: notesHtml || 'Done.' });
                        },
                        error: function(xhr) {
                            hideLoading();
                            Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Gagal migrate data' });
                        }
                    });
                });
            });

            $(document).on('click', '.deactivateUserBtn', function() {
                const id = $(this).data('id');
                Swal.fire({
                    icon: 'warning',
                    title: 'Deactivate this user?',
                    text: 'Sets this das user account\'s status to X because their linked Talenta record is Resigned.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, deactivate',
                    confirmButtonColor: '#dc2626',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    showLoading();
                    $.ajax({
                        url: "{{ route('performance-management.users.deactivate') }}",
                        type: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: { id: id },
                        success: function() {
                            hideLoading();
                            window.resignedUsersTable.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            hideLoading();
                            Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Gagal deactivate user' });
                        }
                    });
                });
            });

            $(document).on('click', '.linkUserBtn', function() {
                const id = $(this).data('id');
                const talentaUserId = $(this).data('talenta-user-id');
                const name = $(this).data('name');
                const matchName = $(this).data('match-name');
                const matchBy = $(this).data('match-by');
                const matchNpk = $(this).data('match-npk');

                const byNameNote = matchBy === 'name'
                    ? `<br><br>This account had no NPK, matched by <b>name</b> instead — NPK <b>${matchNpk}</b> will also be copied onto it.`
                    : '';

                Swal.fire({
                    icon: 'question',
                    title: 'Link this user to Talenta?',
                    html: `<div class="text-left text-sm">
                        Links das account <b>${name}</b> to Talenta record <b>${matchName}</b> (matched by ${matchBy === 'name' ? 'name' : 'NPK'}).${byNameNote}<br><br>
                        This sets <code>user_id_talenta</code>${matchBy === 'name' ? ' and <code>npk</code>' : ''} on the account — it does not change any other data.
                    </div>`,
                    showCancelButton: true,
                    confirmButtonText: 'Yes, link',
                    confirmButtonColor: '#059669',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    showLoading();
                    $.ajax({
                        url: "{{ route('performance-management.users.link') }}",
                        type: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: { id: id, talenta_user_id: talentaUserId },
                        success: function() {
                            hideLoading();
                            window.missingLinkTable.ajax.reload(null, false);
                            window.candidatesTable && window.candidatesTable.ajax.reload(null, false);
                            Swal.fire({ icon: 'success', title: 'Linked', timer: 1200, showConfirmButton: false });
                        },
                        error: function(xhr) {
                            hideLoading();
                            Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Gagal link user' });
                        }
                    });
                });
            });

            $(document).on('click', '.deactivateUnlinkedBtn', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');

                Swal.fire({
                    icon: 'warning',
                    title: 'Deactivate this user?',
                    html: `<div class="text-left text-sm">
                        Sets das account <b>${name}</b>'s status to X because the Talenta record matching its NPK is Resigned.
                    </div>`,
                    showCancelButton: true,
                    confirmButtonText: 'Yes, deactivate',
                    confirmButtonColor: '#dc2626',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    showLoading();
                    $.ajax({
                        url: "{{ route('performance-management.users.deactivate-unlinked') }}",
                        type: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: { id: id },
                        success: function() {
                            hideLoading();
                            window.missingLinkTable.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            hideLoading();
                            Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Gagal deactivate user' });
                        }
                    });
                });
            });

            $(document).on('click', '.createUserBtn', function() {
                const d = $(this).data();
                $('#createUserForm')[0].reset();
                $('#cu_user_id').val(d.userId);
                $('#cu_name').val(d.name);
                $('#cu_npk').val(d.npk);
                $('#cu_position').val(d.position);
                $('#cu_job_level').val(d.jobLevel);
                $('#cu_username').val('');
                $('#cu_password').val('pakuwon1234#');
                $('#cu_email').val('noemail@test.com');
                $('#createUserModal').removeClass('hidden');
            });

            $('#closeCreateUserModal').click(function() {
                $('#createUserModal').addClass('hidden');
            });

            $('#createUserForm').submit(function(e) {
                e.preventDefault();

                if ($('#createUserForm button[type="submit"]').prop('disabled')) return;
                $('#createUserForm button[type="submit"]').prop('disabled', true);

                showLoading();

                $.ajax({
                    url: "{{ route('performance-management.users.store') }}",
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: $('#createUserForm').serialize(),
                    success: function() {
                        hideLoading();
                        $('#createUserForm button[type="submit"]').prop('disabled', false);
                        $('#createUserModal').addClass('hidden');
                        window.candidatesTable.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Success', text: 'User created successfully', timer: 1500, showConfirmButton: false });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#createUserForm button[type="submit"]').prop('disabled', false);
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Gagal membuat user' });
                    }
                });
            });

            // Initialize the first (visible) tab
            initedPmTabs.compare = true;
            initLiveTable();
            initLocalTable();
        });
    </script>
</x-app-layout>
