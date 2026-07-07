<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div>
            {{-- Tab nav --}}
            <div class="mt-4 flex gap-1 border-b border-gray-200 dark:border-gray-700">
                <button type="button" id="tabBtnList"
                    class="approval-tab-btn rounded-t-lg border border-b-0 border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-indigo-600 dark:border-gray-700 dark:bg-gray-800 dark:text-indigo-400">
                    📋 Approval List
                </button>
                <button type="button" id="tabBtnForm"
                    class="approval-tab-btn rounded-t-lg border border-b-0 border-gray-200 bg-gray-50 px-5 py-2.5 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                    ➕ Add / Duplicate Approval
                </button>
            </div>

            {{-- ===================== TAB: Approval List ===================== --}}
            <div id="tabPanelList"
                class="rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h1 class="text-base font-bold text-gray-800 dark:text-white">📋 Ms Approval List</h1>
                    <button id="addApprovalBtn"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                        + Add Approval
                    </button>
                </div>

                <div class="mb-3 flex flex-wrap items-end gap-3">
                    <div class="min-w-50 flex-1">
                        <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                            Filter Doc Type
                        </label>
                        <select id="filterDoctype"
                            class="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm dark:bg-gray-700">
                            <option value="">All Document Type</option>
                            @foreach ($doctypes as $dt)
                                <option value="{{ $dt->doctype }}">{{ $dt->doctype }} - {{ $dt->doctype_descr }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="min-w-50 flex-1">
                        <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                            Filter Company
                        </label>
                        <select id="filterCompany"
                            class="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm dark:bg-gray-700">
                            <option value="">All Company</option>
                            @foreach ($companies as $c)
                                <option value="{{ $c->cpny_id }}">{{ $c->cpny_id }} - {{ $c->cpny_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="min-w-50 flex-1">
                        <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                            Filter Department
                        </label>
                        <select id="filterDept"
                            class="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm dark:bg-gray-700">
                            <option value="">All Department</option>
                            @foreach ($departments as $d)
                                <option value="{{ $d->department_id }}">{{ $d->department_id }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="min-w-45 flex-1">
                        <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                            Dept Type
                        </label>
                        <select id="filterDeptType"
                            class="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm dark:bg-gray-700">
                            <option value="">All (Finance + HR)</option>
                            <option value="FIN">Finance Departments</option>
                            <option value="HR">HR Departments</option>
                        </select>
                    </div>

                    <div class="mt-6">
                        <button id="clearUserFilters" type="button"
                            class="rounded-lg border px-3 py-1 text-sm text-gray-700 hover:bg-gray-100 dark:border-gray-500 dark:text-gray-200 dark:hover:bg-gray-600">
                            Clear Filter
                        </button>
                    </div>
                </div>

                <div class="rounded-base relative overflow-x-auto">
                    <table id="approvalTable" class="text-body w-full text-left text-sm rtl:text-right">
                        <thead
                            class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                            <tr>
                                <th></th>
                                <th class="col-actions w-24 px-2 py-3 text-center">Actions</th>
                                <th class="col-level w-16 px-2 py-3 text-center">Level</th>
                                <th class="col-doctype px-2 py-3 text-left">Doc Type</th>
                                <th class="px-2 py-3 text-left">Company</th>
                                <th class="px-2 py-3 text-left">Department</th>
                                <th class="col-name px-2 py-3 text-left">Name</th>
                                <th class="px-2 py-3 text-left">Type</th>
                                <th class="px-2 py-3 text-left">Condition</th>
                                <th class="col-start w-24 px-2 py-3 text-right">Start Nom</th>
                                <th class="col-end w-24 px-2 py-3 text-right">End Nom</th>
                                <th class="col-status w-24 px-2 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            {{-- ===================== TAB: Add / Duplicate Approval ===================== --}}
            <div id="tabPanelForm"
                class="hidden rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-[#0f172a] md:p-6">

                <div class="mb-5 flex items-center gap-3">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-linear-to-br from-indigo-600 to-violet-700 text-lg text-white shadow-sm">
                        <i class="fa-solid fa-diagram-project"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">
                            Approval Workflow
                        </div>
                        <h1 id="approvalFormTitle" class="text-base font-semibold text-gray-900 dark:text-white">
                            Add Approval
                        </h1>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-5 lg:items-start">
                    {{-- Copy from Existing Template (left panel) --}}
                    <div id="copyTemplateSection"
                        class="min-w-0 overflow-hidden rounded-lg border border-indigo-200 bg-indigo-50/60 dark:border-indigo-500/20 dark:bg-indigo-500/10 lg:col-span-2">
                        <div class="border-b border-indigo-100 px-4 py-3 dark:border-indigo-500/20">
                            <div class="flex items-center gap-2 text-sm font-semibold text-indigo-700 dark:text-indigo-300">
                                <i class="fa-solid fa-clone"></i> Copy from Existing Template
                            </div>
                            <p class="mt-1 text-xs leading-relaxed text-indigo-700/70 dark:text-indigo-300/70">
                                Pick an existing combination to copy its approval lines. They'll appear here as a
                                read-only reference and also get copied into the form on the right — nothing is
                                overwritten until you click Save.
                            </p>
                        </div>

                        <div class="p-4">
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-semibold uppercase tracking-wide text-indigo-700/70 dark:text-indigo-300/70">
                                        Source Doctype
                                    </label>
                                    <select id="copySrcDoctype"
                                        class="w-full rounded-lg border px-2 py-1 text-sm dark:bg-gray-700">
                                        <option value="">choose </option>
                                        @foreach ($doctypes as $dt)
                                            <option value="{{ $dt->doctype }}">{{ $dt->doctype }} -
                                                {{ $dt->doctype_descr }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-semibold uppercase tracking-wide text-indigo-700/70 dark:text-indigo-300/70">
                                        Source Company
                                    </label>
                                    <select id="copySrcCompany"
                                        class="w-full rounded-lg border px-2 py-1 text-sm dark:bg-gray-700">
                                        <option value="">choose </option>
                                        @foreach ($companies as $c)
                                            <option value="{{ $c->cpny_id }}">{{ $c->cpny_id }} —
                                                {{ $c->cpny_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-semibold uppercase tracking-wide text-indigo-700/70 dark:text-indigo-300/70">
                                        Source Department
                                    </label>
                                    <select id="copySrcDepartment"
                                        class="w-full rounded-lg border px-2 py-1 text-sm dark:bg-gray-700">
                                        <option value="">choose </option>
                                    </select>
                                </div>
                            </div>

                            <button type="button" id="loadTemplateBtn"
                                class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                                <i class="fa-solid fa-download"></i> Load Template
                            </button>

                            {{-- Read-only preview of the loaded template --}}
                            <div id="templatePreviewContainer"
                                class="mt-4 hidden border-t border-indigo-200/70 pt-4 dark:border-indigo-500/20">
                                <div
                                    class="mb-2 flex items-center gap-2 text-sm font-semibold text-indigo-700 dark:text-indigo-300">
                                    <i class="fa-solid fa-eye"></i> Reference — lines in the source template
                                </div>
                                <div
                                    class="overflow-x-auto rounded-lg border border-indigo-100 bg-white/60 dark:border-indigo-500/20 dark:bg-white/3">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr
                                                class="text-xs font-semibold uppercase tracking-wide text-indigo-700/60 dark:text-indigo-300/60">
                                                <th class="py-2 pr-2 pl-3 text-left">Lvl</th>
                                                <th class="py-2 pr-2 text-left">Name</th>
                                                <th class="py-2 pr-2 text-left">Type</th>
                                                <th class="py-2 pr-2 text-left">Condition</th>
                                                <th class="py-2 pr-3 text-right">Nominal</th>
                                            </tr>
                                        </thead>
                                        <tbody id="templatePreviewList"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Main form (right panel) --}}
                    <div class="min-w-0 lg:col-span-3">
                        <form id="approvalForm">
                            @csrf
                            <input type="hidden" id="id" name="id">

                            {{-- Target: Doctype, Company, Department --}}
                            <div
                                class="mb-4 rounded-lg border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/2">
                                <div
                                    class="flex items-center gap-2 border-b border-gray-100 px-4 py-3 text-sm font-semibold text-gray-900 dark:border-white/10 dark:text-gray-100">
                                    <i class="fa-solid fa-bullseye text-indigo-600 dark:text-indigo-400"></i>
                                    Target Doctype / Company / Department
                                </div>
                                <div class="grid grid-cols-1 gap-4 p-4 md:grid-cols-3">
                                    {{-- DOCTYPE --}}
                                    <div>
                                        <label
                                            class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Doctype</label>
                                        <select id="aprv_doctype" name="aprv_doctype"
                                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                            <option value="">choose </option>
                                            @foreach ($doctypes as $dt)
                                                <option value="{{ $dt->doctype }}">{{ $dt->doctype }} -
                                                    {{ $dt->doctype_descr }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- COMPANY --}}
                                    <div>
                                        <label
                                            class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Company</label>
                                        <select id="aprv_cpnyid_select" name="aprv_cpnyid"
                                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                            <option value="">choose </option>
                                            @foreach ($companies as $c)
                                                <option value="{{ $c->cpny_id }}">
                                                    {{ $c->cpny_id }} — {{ $c->cpny_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- DEPARTMENT --}}
                                    <div>
                                        <label
                                            class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Department</label>
                                        <select id="aprv_departementid" name="aprv_departementid"
                                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                            <option value="">choose </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Approval Lines --}}
                            <div
                                class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/2">
                                <div
                                    class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-white/10">
                                    <span
                                        class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        <i class="fa-solid fa-list-check text-indigo-600 dark:text-indigo-400"></i>
                                        Approval Lines
                                    </span>
                                    <button type="button" id="addLineBtn"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                                        <i class="fa-solid fa-plus"></i> Add Line
                                    </button>
                                </div>

                                <div class="p-4">
                                    <div
                                        class="mb-2 hidden grid-cols-12 gap-2 text-xs font-semibold uppercase tracking-wide text-gray-400 md:grid">
                                        <div class="col-span-1">Level</div>
                                        <div class="col-span-3">Name</div>
                                        <div class="col-span-2">Type</div>
                                        <div class="col-span-2">Condition</div>
                                        <div class="col-span-2">Start Nominal</div>
                                        <div class="col-span-2">End Nominal</div>
                                    </div>

                                    <div id="linesContainer" class="space-y-2">
                                        {{-- baris dynamic via JS --}}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 flex justify-end gap-2">
                                <button type="button" id="closeApprovalModal"
                                    class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 dark:bg-indigo-600 dark:hover:bg-indigo-500">
                                    <i class="fa-solid fa-floppy-disk"></i> Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- template options username (hidden, shared by Add/Duplicate tab and Edit modal) --}}
    <select id="usernameOptionsTemplate" class="hidden">
        <option value="">choose </option>
        @foreach ($users as $u)
            <option value="{{ $u->username }}">{{ $u->name }}</option>
        @endforeach
    </select>

    {{-- Edit modal (single approval line) --}}
    <div id="editApprovalModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
        <div class="relative max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-lg bg-white p-4 dark:bg-gray-700">
            <h2 id="editApprovalModalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">
                Edit Approval
            </h2>
            <form id="editApprovalForm">
                @csrf
                <input type="hidden" id="edit_id" name="id">

                <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-gray-700 dark:text-white">Doctype</label>
                        <select id="edit_aprv_doctype" name="aprv_doctype"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                            <option value="">choose </option>
                            @foreach ($doctypes as $dt)
                                <option value="{{ $dt->doctype }}">{{ $dt->doctype }} - {{ $dt->doctype_descr }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-gray-700 dark:text-white">Company</label>
                        <select id="edit_aprv_cpnyid_select" name="aprv_cpnyid"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                            <option value="">choose </option>
                            @foreach ($companies as $c)
                                <option value="{{ $c->cpny_id }}">{{ $c->cpny_id }} — {{ $c->cpny_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-gray-700 dark:text-white">Department</label>
                        <select id="edit_aprv_departementid" name="aprv_departementid"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                            <option value="">choose </option>
                        </select>
                    </div>
                </div>

                <div class="rounded-lg border bg-gray-50 p-3 dark:border-gray-600 dark:bg-gray-800">
                    <div class="mb-2 text-sm font-semibold text-gray-800 dark:text-gray-100">
                        Approval Line
                    </div>

                    <div
                        class="mb-1 hidden grid-cols-12 gap-2 text-sm font-semibold text-gray-600 md:grid dark:text-gray-300">
                        <div class="col-span-1">Level</div>
                        <div class="col-span-3">Name</div>
                        <div class="col-span-2">Type</div>
                        <div class="col-span-2">Condition</div>
                        <div class="col-span-2">Start Nominal</div>
                        <div class="col-span-2">End Nominal</div>
                    </div>

                    <div id="editLinesContainer" class="space-y-2">
                        {{-- baris dynamic via JS --}}
                    </div>
                </div>

                <div class="mt-4 flex justify-end space-x-2">
                    <button type="button" id="closeEditApprovalModal"
                        class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                    <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div id="saveOverlay" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/40">
        <div class="flex items-center gap-3 rounded-xl bg-white px-5 py-4 shadow-lg dark:bg-gray-800">
            <svg class="h-6 w-6 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                Saving approval...
            </div>
        </div>
    </div>

    {{-- Select2 CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const TYPE_OPTIONS = @json($type->pluck('category_name')->values());
    </script>

    <script>
        $(document).ready(function() {

            // ===== Tabs =====
            const activeTabClasses =
                'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400';
            const inactiveTabClasses =
                'bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400';

            function showTab(tab) {
                const isList = tab === 'list';

                $('#tabPanelList').toggleClass('hidden', !isList);
                $('#tabPanelForm').toggleClass('hidden', isList);

                $('#tabBtnList')
                    .toggleClass(activeTabClasses, isList)
                    .toggleClass(inactiveTabClasses, !isList);
                $('#tabBtnForm')
                    .toggleClass(activeTabClasses, !isList)
                    .toggleClass(inactiveTabClasses, isList);
            }

            $('#tabBtnList').on('click', function() {
                showTab('list');
            });
            $('#tabBtnForm').on('click', function() {
                showTab('form');
                // if entered directly (not via "+ Add Approval" / duplicate), seed a
                // blank line — but don't clobber in-progress work if the user just
                // switched away to the list tab and back.
                if ($('#linesContainer .line-row').length === 0) {
                    resetAddForm();
                }
            });

            // ===== DataTable =====
            let table = $('#approvalTable').DataTable({
                ajax: {
                    url: "{{ route('approvals.json') }}",
                    type: "GET",
                    dataSrc: 'data'
                },
                processing: true,
                serverSide: false,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],
                responsive: {
                    details: {
                        type: 'column',
                        target: 0 // 👈 this is REQUIRED
                    }
                },

                columnDefs: [{
                    targets: 0,
                    width: '28px',
                    className: 'dtr-control',
                    orderable: false
                }],
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'User',
                        className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '↓ CSV',
                        title: 'User',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                columns: [{
                        data: null,
                        defaultContent: ''
                    }, {
                        data: 'id',
                        className: 'text-center col-actions',
                        render: function(data, type, row) {
                            return `
                                        <div class="flex justify-center space-x-2">
                                            <label class="switch">
                                                <input type="checkbox" class="toggleStatus"
                                                    data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                                <span class="slider round"></span>
                                            </label>
                                            <button class="editApprovalBtn bg-blue-500 text-white px-2 py-1 rounded"
                                                data-id="${data}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="duplicateApprovalBtn bg-amber-500 text-white px-2 py-1 rounded"
                                                title="Duplicate this template"
                                                data-doctype="${row.aprv_doctype}"
                                                data-cpnyid="${row.aprv_cpnyid}"
                                                data-deptid="${row.aprv_departementid}">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    `;
                        }
                    },
                    {
                        data: 'aprv_leveling',
                        className: 'text-center col-level'
                    },
                    {
                        data: 'aprv_doctype',
                        className: 'col-doctype'
                    },
                    {
                        data: 'aprv_cpnyid'
                    },
                    {
                        data: 'aprv_departementid'
                    },
                    {
                        data: 'aprv_name',
                        className: 'col-name'
                    },
                    {
                        data: 'aprv_type'
                    },
                    {
                        data: 'aprv_condition'
                    },
                    {
                        data: 'aprv_start_nominal',
                        className: 'text-right col-start',
                        render: function(data) {
                            return data ? parseFloat(data).toLocaleString('id-ID') : '';
                        }
                    },
                    {
                        data: 'aprv_end_nominal',
                        className: 'text-right col-end',
                        render: function(data) {
                            return data ? parseFloat(data).toLocaleString('id-ID') : '';
                        }
                    },
                    {
                        data: 'status',
                        className: 'text-center col-status',
                        render: function(data) {
                            return data === 'A' ?
                                '<span class="bg-green-300/30 text-green-600 font-semibold px-4 py-1 rounded">Active</span>' :
                                '<span class="bg-red-300/30 text-red-600 font-semibold px-4 py-1 rounded">Inactive</span>';
                        }
                    }
                ]
            });

            // ===== INIT select2 =====
            $('#aprv_doctype, #aprv_departementid, #aprv_cpnyid_select').select2({
                width: '100%'
            });

            $('#copySrcDoctype, #copySrcCompany, #copySrcDepartment').select2({
                width: '100%'
            });

            $('#edit_aprv_doctype, #edit_aprv_departementid, #edit_aprv_cpnyid_select').select2({
                width: '100%',
                dropdownParent: $('#editApprovalModal')
            });

            $('#filterDoctype, #filterCompany, #filterDept, #filterDeptType').select2({
                width: '100%'
            });

            function applyColumnFilter(selectId, colIndex) {
                $(selectId).on('change', function() {
                    const val = $(this).val();
                    if (val) {
                        const regex = '^' + $.fn.dataTable.util.escapeRegex(val) + '$';
                        table.column(colIndex).search(regex, true, false).draw();
                    } else {
                        table.column(colIndex).search('', false, false).draw();
                    }
                });
            }

            // kolom: 3 = doctype, 4 = company, 5 = department
            applyColumnFilter('#filterDoctype', 3);
            applyColumnFilter('#filterCompany', 4);
            applyColumnFilter('#filterDept', 5);

            // Filter Department list, by source (Finance / HR), independent from Doc Type filter
            function loadFilterDepartmentsBySource(source) {
                const url = "{{ route('approvals.departments_by_source') }}" + "?source=" + encodeURIComponent(
                    source || '');
                const $fDept = $('#filterDept');
                const prev = $fDept.val();

                $fDept.empty().append('<option value="">Loading...</option>').val('').trigger('change');

                $.get(url, function(items) {
                    $fDept.empty().append('<option value="">All Department</option>');

                    items.forEach(function(it) {
                        const v = String(it.value ?? '').trim();
                        if (!v) return;
                        $fDept.append(`<option value="${v}">${it.text}</option>`);
                    });

                    if (prev && $fDept.find(`option[value="${prev}"]`).length) {
                        $fDept.val(prev).trigger('change');
                    } else {
                        $fDept.val('').trigger('change');
                    }
                });
            }

            $('#filterDeptType').on('change', function() {
                loadFilterDepartmentsBySource($(this).val() || '');
            });

            // populate the merged (All) list on first load
            loadFilterDepartmentsBySource('');

            $('#clearUserFilters').on('click', function() {
                $('#filterDoctype').val('').trigger('change');
                $('#filterCompany').val('').trigger('change');
                $('#filterDeptType').val('').trigger('change');
                $('#filterDept').val('').trigger('change');

                table.search('').columns().search('').draw();
            });

            // ===== Helpers shared by both line forms =====
            function escapeHtml(str) {
                return String(str ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function loadDepartmentsInto($dep, doctype, selectedValue = null) {
                const url = "{{ route('approvals.departments') }}" + "?doctype=" + encodeURIComponent(doctype ||
                    '');

                $dep.empty().append('<option value="">Loading...</option>').val('').trigger('change');

                $.get(url, function(items) {
                    $dep.empty().append('<option value="">choose </option>');

                    items.forEach(function(it) {
                        const v = String(it.value ?? '').trim();
                        if (!v) return;
                        $dep.append(`<option value="${v}">${it.text}</option>`);
                    });

                    if (selectedValue) {
                        $dep.val(String(selectedValue)).trigger('change');
                    } else {
                        $dep.val('').trigger('change');
                    }
                });
            }

            // Builds a self-contained "line form" controller (its own line index counter,
            // its own condition-options cache) so the Add/Duplicate tab and the Edit modal
            // never step on each other even though both exist in the DOM at the same time.
            function createLineForm(config) {
                let idxCounter = 0;
                let condOptions = [];

                function buildOptions(arr, selected) {
                    let html = `<option value=""></option>`;
                    const list = arr.slice();

                    // A value copied from a template (e.g. a different doctype) may not be
                    // one of the currently loaded options — inject it instead of silently
                    // dropping it, so the field still reflects what was actually copied.
                    if (selected && !list.some(v => String(v) === String(selected))) {
                        list.push(selected);
                    }

                    list.forEach(v => {
                        const sel = (selected && String(selected) === String(v)) ? 'selected' : '';
                        html += `<option value="${escapeHtml(v)}" ${sel}>${escapeHtml(v)}</option>`;
                    });
                    return html;
                }

                function rowTemplate(idx, data) {
                    const level = data?.aprv_leveling ?? '';
                    const typeVal = data?.aprv_type ?? '';
                    const condVal = data?.aprv_condition ?? '';
                    const startNom = data?.aprv_start_nominal ?? '';
                    const endNom = data?.aprv_end_nominal ?? '';

                    const fieldClass =
                        'w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none dark:border-white/10 dark:bg-white/5 dark:text-gray-100';
                    const labelClass = 'mb-1 block text-xs font-medium text-gray-500 md:hidden dark:text-gray-400';

                    return `
                        <div class="line-row group grid grid-cols-1 items-start gap-3 rounded-lg border border-gray-200 bg-gray-50/70 p-3 transition md:grid-cols-12 md:gap-2 md:hover:border-indigo-200 md:hover:bg-indigo-50/40 dark:border-white/10 dark:bg-white/2 md:dark:hover:border-indigo-500/30 md:dark:hover:bg-indigo-500/5" data-row="${idx}">
                        <div class="md:col-span-1">
                            <label class="${labelClass}">Level</label>
                            <input type="text" name="aprv_leveling[]"
                            class="level-input ${fieldClass}"
                            value="${level}" placeholder="0.00" inputmode="decimal" autocomplete="off" required>
                        </div>

                        <div class="md:col-span-3">
                            <label class="${labelClass}">Name</label>
                            <select name="aprv_username[${idx}][]"
                            class="sel-username w-full dark:bg-gray-700"
                            multiple required></select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="${labelClass}">Type</label>
                            <select name="aprv_type[]"
                            class="sel-type ${fieldClass}">
                            ${buildOptions(TYPE_OPTIONS, typeVal)}
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="${labelClass}">Condition</label>
                            <select name="aprv_condition[]"
                            class="sel-condition ${fieldClass}">
                            ${buildOptions(condOptions, condVal)}
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="${labelClass}">Start Nominal</label>
                            <input type="text" name="aprv_start_nominal[]"
                            class="nominal-input ${fieldClass}"
                            value="${startNom}" inputmode="decimal" autocomplete="off">
                        </div>

                        <div class="flex items-end gap-2 md:col-span-2">
                            <div class="flex-1">
                            <label class="${labelClass}">End Nominal</label>
                            <input type="text" name="aprv_end_nominal[]"
                                class="nominal-input ${fieldClass}"
                                value="${endNom}" inputmode="decimal" autocomplete="off">
                            </div>
                            <button type="button" title="Remove line"
                                class="removeLineBtn inline-flex h-[38px] w-9 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600 transition hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        </div>
                    `;
                }

                function addLineRow(data) {
                    const idx = idxCounter++;
                    $(config.containerSel).append(rowTemplate(idx, data || {}));

                    const $row = $(config.containerSel).find(`.line-row[data-row="${idx}"]`);

                    const optionsHtml = $('#usernameOptionsTemplate').html();
                    const $usernameSelect = $row.find('.sel-username');
                    $usernameSelect.html(optionsHtml);

                    const select2Opts = {
                        width: '100%'
                    };
                    if (config.dropdownParentSel) {
                        select2Opts.dropdownParent = $(config.dropdownParentSel);
                    }
                    $usernameSelect.select2(select2Opts);

                    if (data && data.aprv_username) {
                        let selected = data.aprv_username;
                        if (typeof selected === 'string') {
                            selected = selected.split(',').map(s => s.trim()).filter(Boolean);
                        }
                        $usernameSelect.val(selected).trigger('change');
                    }
                }

                function setConditionOptions(items) {
                    condOptions = items || [];
                    $(config.containerSel).find('.sel-condition').each(function() {
                        const current = $(this).val();
                        $(this).html(buildOptions(condOptions, current));
                    });
                }

                function reset() {
                    idxCounter = 0;
                    $(config.containerSel).empty();
                }

                return {
                    addLineRow,
                    setConditionOptions,
                    reset
                };
            }

            const addForm = createLineForm({
                containerSel: '#linesContainer',
                dropdownParentSel: null
            });
            const editForm = createLineForm({
                containerSel: '#editLinesContainer',
                dropdownParentSel: '#editApprovalModal'
            });

            function loadConditionsByDoctype(doctype, formCtx, callback) {
                const url = "{{ route('approvals.conditions') }}" + "?doctype=" + encodeURIComponent(doctype ||
                    '');
                $.get(url, function(items) {
                    formCtx.setConditionOptions(items);
                    if (callback) callback();
                });
            }

            // ADD line (Add/Duplicate tab only — Edit modal is always a single line)
            $('#addLineBtn').on('click', function() {
                addForm.addLineRow();
            });

            // Hapus line (minimal 1 per form)
            $(document).on('click', '.removeLineBtn', function() {
                const $container = $(this).closest('#linesContainer, #editLinesContainer');
                const total = $container.find('.line-row').length;
                if (total <= 1) return;
                $(this).closest('.line-row').remove();
            });

            function loadDepartmentsByDoctype(doctype, selectedValue = null) {
                loadDepartmentsInto($('#aprv_departementid'), doctype, selectedValue);
            }

            function loadEditDepartmentsByDoctype(doctype, selectedValue = null) {
                loadDepartmentsInto($('#edit_aprv_departementid'), doctype, selectedValue);
            }

            // Reload department & conditions when doctype changes (Add/Duplicate tab)
            $('#aprv_doctype').on('change', function() {
                const dt = $(this).val() || '';
                loadDepartmentsByDoctype(dt, null);
                loadConditionsByDoctype(dt, addForm);
            });

            // Reload department & conditions when doctype changes (Edit modal)
            $('#edit_aprv_doctype').on('change', function() {
                const dt = $(this).val() || '';
                loadEditDepartmentsByDoctype(dt, null);
                loadConditionsByDoctype(dt, editForm);
            });

            // Reload department list for the "copy from existing template" source picker
            $('#copySrcDoctype').on('change', function() {
                const dt = $(this).val() || '';
                loadDepartmentsInto($('#copySrcDepartment'), dt, null);
            });

            // ===== Add / Duplicate tab =====
            function resetAddForm() {
                $('#approvalFormTitle').text('Add Approval');
                $('#approvalForm')[0].reset();
                $('#id').val('');
                $('#addLineBtn').removeClass('hidden');

                $('#approvalForm button[type="submit"]')
                    .data('submitting', false)
                    .prop('disabled', false)
                    .text('Save');

                $('#closeApprovalModal').prop('disabled', false);

                $('#aprv_cpnyid_select').val('').trigger('change');
                $('#aprv_doctype').val('').trigger('change');

                $('#copyTemplateSection').removeClass('hidden');
                $('#copySrcDoctype').val('').trigger('change');
                $('#copySrcCompany').val('').trigger('change');
                $('#copySrcDepartment').empty().append('<option value="">choose </option>').val('').trigger(
                    'change');
                $('#templatePreviewList').empty();
                $('#templatePreviewContainer').addClass('hidden');

                addForm.reset();
                addForm.addLineRow();
            }

            // ADD Approval
            // NOTE: show the tab (removes display:none) *before* building the line
            // row — select2 measures the container width at init time, and it can't
            // do that correctly while the tab panel is still hidden.
            $('#addApprovalBtn').click(function() {
                showTab('form');
                resetAddForm();
            });

            // EDIT Approval (separate modal, single line)
            $(document).on('click', '.editApprovalBtn', function() {
                let id = $(this).data('id');

                $('#editApprovalModalTitle').text('Loading...');
                $('#editApprovalForm')[0].reset();
                $('#edit_id').val(id);

                $('#editApprovalForm button[type="submit"]')
                    .data('submitting', false)
                    .prop('disabled', false)
                    .text('Save');

                $('#closeEditApprovalModal').prop('disabled', false);

                editForm.reset();

                $('#editApprovalModal').removeClass('hidden');

                $.get(`/approvals/${id}/edit`, function(data) {
                    $('#editApprovalModalTitle').text('Edit Approval');

                    $('#edit_aprv_doctype').val(data.aprv_doctype).trigger('change.select2');
                    loadEditDepartmentsByDoctype(data.aprv_doctype, data.aprv_departementid);

                    $('#edit_aprv_cpnyid_select').val(data.aprv_cpnyid).trigger('change');

                    loadConditionsByDoctype(data.aprv_doctype, editForm, function() {
                        editForm.addLineRow({
                            aprv_leveling: data.aprv_leveling,
                            aprv_username: data.aprv_username,
                            aprv_type: data.aprv_type,
                            aprv_condition: data.aprv_condition,
                            aprv_start_nominal: data.aprv_start_nominal,
                            aprv_end_nominal: data.aprv_end_nominal,
                        });
                    });
                });
            });

            $('#closeEditApprovalModal').click(function() {
                $('#editApprovalModal').addClass('hidden');
            });

            // Load approval lines from an existing Doctype+Company+Department into the Add/Duplicate form
            function fetchAndApplyGroupLines(doctype, cpnyid, deptid, onDone) {
                $.get("{{ route('approvals.group') }}", {
                    doctype: doctype,
                    cpnyid: cpnyid,
                    departementid: deptid
                }, function(res) {
                    const lines = res.lines || [];

                    if (!lines.length) {
                        Swal.fire({
                            icon: 'info',
                            title: 'No data',
                            text: 'No approval lines found for that combination.'
                        });
                        return;
                    }

                    addForm.reset();

                    lines.forEach(function(l) {
                        addForm.addLineRow({
                            aprv_leveling: l.aprv_leveling,
                            aprv_username: l.aprv_username,
                            aprv_type: l.aprv_type,
                            aprv_condition: l.aprv_condition,
                            aprv_start_nominal: l.aprv_start_nominal,
                            aprv_end_nominal: l.aprv_end_nominal,
                        });
                    });

                    renderTemplatePreview(lines);

                    if (onDone) onDone(lines.length);
                });
            }

            // Read-only reference table for the source template (left panel)
            function renderTemplatePreview(lines) {
                const $list = $('#templatePreviewList');
                $list.empty();

                lines.forEach(function(l) {
                    const level = l.aprv_leveling ?? '';
                    const name = l.aprv_name || '-';
                    const type = l.aprv_type || '-';
                    const cond = l.aprv_condition || '-';
                    const startNom = l.aprv_start_nominal ? parseFloat(l.aprv_start_nominal).toLocaleString(
                        'id-ID') : '0';
                    const endNom = l.aprv_end_nominal ? parseFloat(l.aprv_end_nominal).toLocaleString('id-ID') :
                        '0';

                    $list.append(`
                        <tr class="border-t border-indigo-100 align-top dark:border-indigo-800">
                            <td class="py-1 pr-2 font-semibold text-gray-700 dark:text-gray-200">${escapeHtml(level)}</td>
                            <td class="py-1 pr-2 text-gray-600 dark:text-gray-300" title="${escapeHtml(name)}">${escapeHtml(name)}</td>
                            <td class="py-1 pr-2 text-gray-500 dark:text-gray-400">${escapeHtml(type)}</td>
                            <td class="py-1 pr-2 text-gray-500 dark:text-gray-400">${escapeHtml(cond)}</td>
                            <td class="py-1 whitespace-nowrap text-right text-gray-500 dark:text-gray-400">${startNom} — ${endNom}</td>
                        </tr>
                    `);
                });

                $('#templatePreviewContainer').toggleClass('hidden', lines.length === 0);
            }

            // "Load Template" button inside Add/Duplicate tab
            $('#loadTemplateBtn').on('click', function() {
                const srcDoctype = $('#copySrcDoctype').val();
                const srcCpny = $('#copySrcCompany').val();
                const srcDept = $('#copySrcDepartment').val();

                if (!srcDoctype || !srcCpny || !srcDept) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Choose source Doctype, Company, and Department first.'
                    });
                    return;
                }

                const targetEmpty = !$('#aprv_doctype').val() && !$('#aprv_cpnyid_select').val();

                const applyLines = function() {
                    fetchAndApplyGroupLines(srcDoctype, srcCpny, srcDept, function(count) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Template loaded',
                            text: count + ' line(s) copied. Adjust the target fields/lines as needed before saving.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    });
                };

                if (targetEmpty) {
                    // no target chosen yet: use the source combo as the starting target too
                    $('#aprv_doctype').val(srcDoctype).trigger('change.select2');
                    $('#aprv_cpnyid_select').val(srcCpny).trigger('change');
                    loadDepartmentsByDoctype(srcDoctype, srcDept);
                    loadConditionsByDoctype(srcDoctype, addForm, applyLines);
                } else {
                    // Target company/department may intentionally differ from the source
                    // (that's the whole point of copying a template across companies), so
                    // we keep them as-is. But the copied lines' conditions belong to the
                    // *source* doctype — always sync the target doctype to match it,
                    // otherwise conditions get fetched for the wrong doctype and silently
                    // fail to match (e.g. "ATK" not found -> blank Condition field).
                    if ($('#aprv_doctype').val() !== srcDoctype) {
                        const curDept = $('#aprv_departementid').val();
                        $('#aprv_doctype').val(srcDoctype).trigger('change.select2');
                        loadDepartmentsByDoctype(srcDoctype, curDept);
                    }
                    loadConditionsByDoctype(srcDoctype, addForm, applyLines);
                }
            });

            // Row-level "Duplicate" button: switch to Add/Duplicate tab pre-loaded with that row's whole group
            $(document).on('click', '.duplicateApprovalBtn', function() {
                const doctype = $(this).data('doctype');
                const cpnyid = $(this).data('cpnyid');
                const deptid = $(this).data('deptid');

                // show the tab first — select2 can't measure widths correctly inside
                // a display:none container, so it must already be visible before any
                // line rows (with the multi-select "Name" field) are built.
                showTab('form');
                resetAddForm();
                $('#approvalFormTitle').text('Duplicate Approval Template');

                $('#copySrcDoctype').val(doctype).trigger('change');
                $('#copySrcCompany').val(cpnyid).trigger('change');
                loadDepartmentsInto($('#copySrcDepartment'), doctype, deptid);

                addForm.reset();

                $('#aprv_doctype').val(doctype).trigger('change.select2');
                $('#aprv_cpnyid_select').val(cpnyid).trigger('change');
                loadDepartmentsByDoctype(doctype, deptid);

                loadConditionsByDoctype(doctype, addForm, function() {
                    fetchAndApplyGroupLines(doctype, cpnyid, deptid);
                });
            });

            // Toggle status
            $(document).on('change', '.toggleStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/approvals/${id}/toggle-status`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        status: newStatus
                    },
                    success: function() {
                        table.ajax.reload(null, false);
                    }
                });
            });

            // Submit Add/Duplicate form
            $('#approvalForm').submit(function(e) {
                e.preventDefault();

                const $submitBtn = $('#approvalForm button[type="submit"]');
                const $closeBtn = $('#closeApprovalModal');
                const $overlay = $('#saveOverlay');

                if ($submitBtn.data('submitting') === true) {
                    return;
                }

                const dt = $('#aprv_doctype').val();
                const cp = $('#aprv_cpnyid_select').val();
                const dep = $('#aprv_departementid').val();

                if (!dt || !cp || !dep) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Doctype, Company, dan Department wajib diisi.'
                    });
                    return;
                }

                let errorMsg = null;

                $('#approvalForm .level-input').each(function() {
                    const v = $(this).val().trim();
                    if (!isValidDecimal2(v)) {
                        errorMsg = 'Level harus angka dengan maksimal 2 angka di belakang koma.';
                        $(this).focus();
                        return false;
                    }
                });

                if (errorMsg) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Input tidak valid',
                        text: errorMsg
                    });
                    return;
                }

                $('#approvalForm .nominal-input').each(function() {
                    const v = $(this).val().trim();
                    if (!isValidNumeric(v)) {
                        errorMsg = 'Nominal hanya boleh angka (boleh desimal).';
                        $(this).focus();
                        return false;
                    }
                });

                if (errorMsg) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Input tidak valid',
                        text: errorMsg
                    });
                    return;
                }

                let formEl = document.getElementById('approvalForm');
                let formData = new FormData(formEl);

                // lock UI
                $submitBtn.data('submitting', true).prop('disabled', true).text('Saving...');
                $closeBtn.prop('disabled', true);
                $overlay.removeClass('hidden').addClass('flex');

                $.ajax({
                    url: "{{ route('approvals.store') }}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        showTab('list');
                        table.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Approval berhasil disimpan.',
                            timer: 1800,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        let msg = 'Gagal menyimpan data approval';
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors)
                                .map(function(arr) {
                                    return arr.join(', ');
                                })
                                .join('\n');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: msg
                        });
                    },
                    complete: function() {
                        $submitBtn.data('submitting', false).prop('disabled', false).text('Save');
                        $closeBtn.prop('disabled', false);
                        $overlay.removeClass('flex').addClass('hidden');
                    }
                });
            });

            $('#closeApprovalModal').click(function() {
                showTab('list');
            });

            // Submit Edit form
            $('#editApprovalForm').submit(function(e) {
                e.preventDefault();

                const $submitBtn = $('#editApprovalForm button[type="submit"]');
                const $closeBtn = $('#closeEditApprovalModal');
                const $overlay = $('#saveOverlay');

                if ($submitBtn.data('submitting') === true) {
                    return;
                }

                const dt = $('#edit_aprv_doctype').val();
                const cp = $('#edit_aprv_cpnyid_select').val();
                const dep = $('#edit_aprv_departementid').val();

                if (!dt || !cp || !dep) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Doctype, Company, dan Department wajib diisi.'
                    });
                    return;
                }

                let errorMsg = null;

                $('#editApprovalForm .level-input').each(function() {
                    const v = $(this).val().trim();
                    if (!isValidDecimal2(v)) {
                        errorMsg = 'Level harus angka dengan maksimal 2 angka di belakang koma.';
                        $(this).focus();
                        return false;
                    }
                });

                if (errorMsg) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Input tidak valid',
                        text: errorMsg
                    });
                    return;
                }

                $('#editApprovalForm .nominal-input').each(function() {
                    const v = $(this).val().trim();
                    if (!isValidNumeric(v)) {
                        errorMsg = 'Nominal hanya boleh angka (boleh desimal).';
                        $(this).focus();
                        return false;
                    }
                });

                if (errorMsg) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Input tidak valid',
                        text: errorMsg
                    });
                    return;
                }

                let id = $('#edit_id').val();
                let formEl = document.getElementById('editApprovalForm');
                let formData = new FormData(formEl);
                formData.append('_method', 'PUT');

                // lock UI
                $submitBtn.data('submitting', true).prop('disabled', true).text('Saving...');
                $closeBtn.prop('disabled', true);
                $overlay.removeClass('hidden').addClass('flex');

                $.ajax({
                    url: `/approvals/${id}`,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        $('#editApprovalModal').addClass('hidden');
                        table.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Approval berhasil disimpan.',
                            timer: 1800,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        let msg = 'Gagal menyimpan data approval';
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors)
                                .map(function(arr) {
                                    return arr.join(', ');
                                })
                                .join('\n');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: msg
                        });
                    },
                    complete: function() {
                        $submitBtn.data('submitting', false).prop('disabled', false).text('Save');
                        $closeBtn.prop('disabled', false);
                        $overlay.removeClass('flex').addClass('hidden');
                    }
                });
            });
        });

        function isValidDecimal2(val) {
            if (val === '') return true;
            return /^\d+(\.\d{1,2})?$/.test(val);
        }

        function isValidNumeric(val) {
            if (val === '') return true;
            return /^-?\d*(\.\d+)?$/.test(val);
        }

        $(document).on('input', '.level-input', function() {
            let v = $(this).val();
            v = v.replace(/[^0-9.]/g, '');
            let parts = v.split('.');
            if (parts.length > 2) {
                v = parts[0] + '.' + parts.slice(1).join('');
                parts = v.split('.');
            }
            if (parts.length === 2 && parts[1].length > 2) {
                v = parts[0] + '.' + parts[1].slice(0, 2);
            }
            $(this).val(v);
        });

        $(document).on('input', '.nominal-input', function() {
            let v = $(this).val();
            v = v.replace(/[^0-9.]/g, '');
            let parts = v.split('.');
            if (parts.length > 2) {
                v = parts[0] + '.' + parts.slice(1).join('');
            }
            $(this).val(v);
        });
    </script>
</x-app-layout>
