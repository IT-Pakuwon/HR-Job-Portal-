<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'jobpostings' ? 'HR' : '';
    @endphp

    <style>
        .select2-container {
            width: 100% !important;
            max-width: 100% !important;
        }

        .select2-selection__rendered {
            display: block !important;
            max-width: 100% !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
        }

        .select2-dropdown {
            z-index: 99999 !important;
        }
    </style>
    <div class="max-w-9xl mx-auto p-2">

        {{-- Filter Cards --}}
        <div class="grid auto-rows-fr grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-8">

            <a href="#" class="status-filter group block h-full" data-status="">
                <div class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📄</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">All</p>
                    </div>
                    <p class="shrink-0 text-base font-bold">{{ $all }}</p>
                </div>
            </a>

            <a href="#" class="status-filter group block h-full" data-status="is_read_N">
                <div class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⏳</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Unchecked</p>
                    </div>
                    <p class="shrink-0 text-base font-bold">{{ $unchecked }}</p>
                </div>
            </a>

            <a href="#" class="status-filter group block h-full" data-status="is_read_Y">
                <div class="status-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✏️</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Checked</p>
                    </div>
                    <p class="shrink-0 text-base font-bold">{{ $checked }}</p>
                </div>
            </a>

            <a href="#" class="status-filter group block h-full" data-status="R">
                <div class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✕</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Rejected</p>
                    </div>
                    <p class="shrink-0 text-base font-bold">{{ $reject }}</p>
                </div>
            </a>

            <a href="#" class="status-filter group block h-full" data-status="mapping">
                <div class="status-card flex h-full items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-md active:scale-95">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🔗</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Mapping</p>
                    </div>
                    <p class="shrink-0 text-base font-bold">{{ $mapped }}</p>
                </div>
            </a>

            <a href="#" class="status-filter group block h-full" data-status="unmapping">
                <div class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🔓</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Unmapping</p>
                    </div>
                    <p class="shrink-0 text-base font-bold">{{ $unmapped }}</p>
                </div>
            </a>

            <a href="#" class="status-filter group block h-full" data-status="tagged">
                <div class="status-card flex h-full items-center gap-3 rounded-lg border border-fuchsia-700 bg-fuchsia-200/20 p-3 text-fuchsia-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-fuchsia-100 hover:shadow-md active:scale-95">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🏷️</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Tagged</p>
                    </div>
                    <p class="shrink-0 text-base font-bold">{{ $tagged }}</p>
                </div>
            </a>

            <a href="#" class="status-filter group block h-full" data-status="untagged">
                <div class="status-card flex h-full items-center gap-3 rounded-lg border border-yellow-700 bg-yellow-200/20 p-3 text-yellow-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-yellow-100 hover:shadow-md active:scale-95">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🔖</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Untagged</p>
                    </div>
                    <p class="shrink-0 text-base font-bold">{{ $untagged }}</p>
                </div>
            </a>

        </div>

        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center dark:border-gray-700">
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Self Register Applicant</h1>
            </div>

            {{-- Division / Department Filter --}}
            <div class="flex gap-3">
                <select id="filterDivision" class="w-full" style="width:100%">
                    <option value="">Filter by Division</option>
                    @foreach($divisions as $div)
                        <option value="{{ $div->division_id }}">{{ $div->division_name }}</option>
                    @endforeach
                </select>
                <select id="filterDepartment" class="w-full" style="width:100%">
                    <option value="">Filter by Department</option>
                </select>
            </div>

            {{-- Column Search Filters --}}
            <div id="applicantsFilters" class="mb-2 grid grid-cols-1 gap-3 sm:grid-cols-5 lg:grid-cols-9">
                <button id="btnResetFilters" class="rounded-md border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">
                    Reset
                </button>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="applicantsTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th scope="col" class="w-32 px-4 py-3 text-center">
                                DocID
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-left">
                                Date
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-left">
                                Name
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-left">
                                Divisi
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-left">
                                Department
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-left">
                                Education
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-left">
                                Religion
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-center">
                                Height
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-center">
                                Weight
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-center">
                                Last Working
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-center">
                                Tagged
                            </th>
                            <th scope="col" class="w-40 px-4 py-3 text-center">
                                Job Mapping
                            </th>
                            <th scope="col" class="w-28 px-4 py-3 text-center">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Tagging Modal -->
        <div id="taggingModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
            <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-2xl">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-800">Tag Applicant</h2>
                    <button id="closeTaggingModal" class="text-gray-400 hover:text-gray-600 text-xl font-bold">✕</button>
                </div>

                <input type="hidden" id="tagApplicantId">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Division</label>
                    <select id="tagDivisionSelect" class="w-full" style="width:100%">
                        <option value="">-- Select Division --</option>
                        @foreach($divisions as $div)
                            <option value="{{ $div->division_id }}">{{ $div->division_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <select id="tagDeptSelect" class="w-full" style="width:100%">
                        <option value="">-- Select Division first --</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3">
                    <button id="closeTaggingModalBtn" class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button id="saveTagging" class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700">Save Tag</button>
                </div>
            </div>
        </div>

        <!-- Mapping Modal -->
        <div id="mappingModal"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40  ">

            <div
                class="w-full max-w-5xl transform rounded-2xl bg-white p-8 shadow-2xl transition-all duration-300 scale-95 opacity-0"
                id="mappingModalContent">

                <!-- Header -->
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">
                            Mapping Applicant
                        </h2>
                        <p class="text-sm text-gray-500">
                            Assign candidate to job posting
                        </p>
                    </div>

                    <button id="closeMappingModal"
                        class="text-gray-400 hover:text-gray-600 text-lg">
                        ✕
                    </button>
                </div>

                <!-- Hidden -->
                <input type="hidden" id="mapApplicantId">

                <!-- Mapping Row -->
              <div class="mb-8 flex items-center gap-4 w-full">

                    <!-- DOC ID -->
                    <div
                        class="min-w-[200px] rounded-xl bg-gray-100 px-5 py-3 text-center text-base font-semibold text-gray-700 shadow-inner">
                        <span id="mapDocId">DOCID</span>
                    </div>

                    <!-- Arrow -->
                    <div class="text-gray-400 text-2xl">→</div>

                    <!-- Select -->
                    <div class="flex-1 min-w-0">
                        <select id="jobPostingSelect"
                            class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm
                                focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500
                                hover:border-gray-400 transition">
                        </select>
                    </div>

                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-2">
                    <button id="closeMappingModalBtn"
                        class="rounded-lg px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700">
                        Cancel
                    </button>

                    <button id="saveMapping"
                        class="rounded-xl px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow">
                        Save Mapping
                    </button>
                </div>
            </div>
        </div>

    </div>


    <script>
        var currentUser = "{{ auth()->user()->username }}";
    </script>




    <script>
        $(document).ready(function() {
            let currentStatus = '';

            // Filter input per kolom (index harus sesuai kolom DataTables)
            const columnFilters = [
                {
                    index: 1,
                    type: 'text',
                    placeholder: 'DocID'
                },
                {
                    index: 2,
                    type: 'text',
                    placeholder: 'Apply Date'
                },
                {
                    index: 3,
                    type: 'text',
                    placeholder: 'Full Name'
                },
                {
                    index: 4,
                    type: 'text',
                    placeholder: 'Division' // 🔥 NEW
                },
                {
                    index: 5,
                    type: 'text',
                    placeholder: 'Department' // 🔥 NEW
                },
                {
                    index: 6,
                    type: 'text',
                    placeholder: 'Education'
                },
                {
                    index: 7,
                    type: 'text',
                    placeholder: 'Religion'
                },
                {
                    index: 8,
                    type: 'text',
                    placeholder: 'Height'
                },
                {
                    index: 9,
                    type: 'text',
                    placeholder: 'Weight'
                },
                {
                    index: 10,
                    type: 'text',
                    placeholder: 'Company'
                }
            ];

            const $filters = $('#applicantsFilters');

            columnFilters.forEach(col => {
                const $el = $(`
                    <input type="text"
                        class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm
                            focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        placeholder="Search ${col.placeholder}">
                `);

                let debounce;
                $el.on('input', function() {
                    clearTimeout(debounce);
                    const val = this.value;
                    debounce = setTimeout(() => {
                        applicantTable.column(col.index).search(val).draw();
                    }, 300);
                });

                $filters.append($el);
            });

            const applicantTable = $('#applicantsTable').DataTable({
                responsive: {
                    details: {
                        type: 'column',
                        target: 0
                    }
                },

                columnDefs: [{
                        targets: 0,
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false,
                        searchable: false,
                        responsivePriority: 1
                    },
                    {
                        targets: [1, 2],
                        responsivePriority: 2
                    },
                    {
                        targets: [3, 4, 5, 6, 7, 8],
                        responsivePriority: 100
                    }
                ],

                processing: true,
                serverSide: true,
                searching: true,
                paging: true,
                info: true,

                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'Applicant_List',
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
                        title: 'Applicant_List',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],

                pageLength: 10,

                ajax: {
                    url: "{{ route('selfregister.json') }}",
                    type: 'GET',
                    data: function(d) {
                        d.status          = currentStatus;
                        d.division_filter = $('#filterDivision').val() || '';
                        d.department_filter = $('#filterDepartment').val() || '';
                    }
                },

                // sort by DocID (kolom index 1)
                order: [
                    [1, 'desc']
                ],

                columns: [{ // 0 responsive control
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false,
                        searchable: false,
                        data: null,
                        defaultContent: ''
                    },
                    { // 1 docid
                        data: 'docid',
                        name: 'docid',
                        render: function(data, type, row) {
                            return `<a href="/showselfregister/${row.eid}" target="_blank"
                                class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm font-semibold text-white rounded  bg-gray-600 hover:bg-gray-700 ">
                                ${data}
                            </a>`;
                        }
                    },
                    {
                        data: 'apply_date',
                        name: 'apply_date'
                    }, // 2
                    {
                        data: 'fullname',
                        name: 'fullname'
                    },
                    {
                        data: 'division_name',
                        name: 'division_name'
                    },
                    {
                        data: 'department_name',
                        name: 'department_name'
                    },// 3
                    {
                        data: 'education_name',
                        name: 'education_name'
                    }, // 4
                    {
                        data: 'religion',
                        name: 'religion'
                    }, // 5
                    {
                        data: 'height',
                        name: 'height',
                        className: 'small-col'
                    }, // 6
                    {
                        data: 'weight',
                        name: 'weight',
                        className: 'small-col'
                    }, // 7
                    {
                        data: 'company_name',
                        name: 'company_name'
                    }, // 8
                    { // 9 — Tagged
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (!row.is_tagged) {
                                return `<span class="text-xs text-gray-400">—</span>`;
                            }
                            return `<span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-fuchsia-100 text-fuchsia-700">
                                🏷 ${row.division_name || '—'} · ${row.department_name || '—'}
                            </span>`;
                        }
                    },
                    { // 10 — Job Mapping
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (row.status === 'R') return `<span class="text-xs text-gray-400">—</span>`;
                            if (!row.jobposting_docid) return `<span class="text-xs text-gray-400">—</span>`;
                            return `<span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                ✓ ${row.job_name || row.jobposting_docid}
                            </span>`;
                        }
                    },
                    { // 11 — Action
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {

                            // ❌ REJECTED
                            if (row.status === 'R') {
                                return `<span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-600">Rejected</span>`;
                            }

                            // ✅ SUDAH MAPPED → Re-map / Undo / Reject
                            if (row.jobposting_docid) {
                                const items = [
                                    `<button class="slf-action-item w-full text-left px-4 py-2 text-xs hover:bg-gray-100 text-indigo-600"
                                        data-action="remap" data-id="${row.eid}" data-docid="${row.docid}">🔄 Re-map</button>`,
                                    `<button class="slf-action-item w-full text-left px-4 py-2 text-xs hover:bg-gray-100 text-red-500"
                                        data-action="undo" data-id="${row.eid}" data-job="${row.jobposting_docid}">↩ Undo Mapping</button>`,
                                    `<button class="slf-action-item w-full text-left px-4 py-2 text-xs hover:bg-gray-100 text-red-600"
                                        data-action="reject" data-id="${row.eid}">✕ Reject</button>`,
                                ].join('');

                                return `
                                    <div class="slf-dropdown relative inline-block">
                                        <button class="slf-toggle inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300"
                                            data-id="${row.eid}">Action ▾</button>
                                    </div>
                                    <div class="slf-menu-data" data-id="${row.eid}" style="display:none">${items}</div>`;
                            }

                            // 🔵 BELUM MAPPED → Tag / Map / Reject
                            const tagLabel = row.is_tagged ? '🏷 Re-tag' : '🏷 Tag';
                            const items = [
                                `<button class="slf-action-item w-full text-left px-4 py-2 text-xs hover:bg-gray-100 text-purple-600"
                                    data-action="tag" data-id="${row.eid}" data-docid="${row.docid}"
                                    data-division="${row.division_id || ''}" data-department="${row.department_id || ''}">${tagLabel}</button>`,
                                `<button class="slf-action-item w-full text-left px-4 py-2 text-xs hover:bg-gray-100 text-indigo-600"
                                    data-action="map" data-id="${row.eid}" data-docid="${row.docid}">+ Map</button>`,
                                `<button class="slf-action-item w-full text-left px-4 py-2 text-xs hover:bg-gray-100 text-red-500"
                                    data-action="reject" data-id="${row.eid}">✕ Reject</button>`,
                            ].join('');

                            return `
                                <div class="slf-dropdown relative inline-block">
                                    <button class="slf-toggle inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300"
                                        data-id="${row.eid}">Action ▾</button>
                                </div>
                                <div class="slf-menu-data" data-id="${row.eid}" style="display:none">${items}</div>`;
                        }
                    },
                ],

                rowCallback: function(row, data) {
                    if (data.status === 'R') {
                        $(row).css('color', '#dc2626');
                    } else if (!data.is_read || data.is_read === 'N') {
                        $(row).css('color', '#2563eb');
                    } else {
                        $(row).css('color', 'black');
                    }
                }
            });

            // ── FIXED DROPDOWN (self register) ───────────────────────
            const $slfMenu = $(`
                <div id="slf-fixed-menu" class="hidden fixed z-[9999] w-44 rounded-md shadow-lg bg-white border border-gray-200 py-1"></div>
            `).appendTo('body');

            // Toggle
            $(document).on('click', '.slf-toggle', function(e) {
                e.stopPropagation();
                const id   = $(this).data('id');
                const html = $(`.slf-menu-data[data-id="${id}"]`).html();

                $slfMenu.html(html || '');

                const rect = this.getBoundingClientRect();
                const slfW = 176;
                $slfMenu.css({
                    top:  rect.bottom + 4,
                    left: Math.max(8, Math.min(rect.right - slfW, window.innerWidth - slfW - 8)),
                });

                const isOpen = !$slfMenu.hasClass('hidden');
                $slfMenu.toggleClass('hidden', isOpen);
            });

            // Close on outside click
            $(document).on('click', function() { $slfMenu.addClass('hidden'); });

            // Handle action item
            $(document).on('click', '.slf-action-item', function(e) {
                e.stopPropagation();
                $slfMenu.addClass('hidden');

                const btn    = $(this);
                const action = btn.data('action');
                const id     = btn.data('id');

                if (action === 'tag') {
                    $('#tagApplicantId').val(id);
                    const div  = btn.data('division')   || '';
                    const dept = btn.data('department') || '';
                    $('#taggingModal').removeClass('hidden').addClass('flex');
                    if (div) {
                        $('#tagDivisionSelect').val(div).trigger('change');
                        $('#tagDivisionSelect').one('deptLoaded', function() {
                            $('#tagDeptSelect').val(dept).trigger('change');
                        });
                    } else {
                        $('#tagDivisionSelect').val(null).trigger('change');
                    }

                } else if (action === 'map' || action === 'remap') {
                    $('#mapApplicantId').val(id);
                    $('#mapDocId').text(btn.data('docid'));
                    loadJobPostings();
                    $('#mappingModal').removeClass('hidden').addClass('flex');
                    setTimeout(() => {
                        $('#mappingModalContent').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
                    }, 10);

                } else if (action === 'reject') {
                    Swal.fire({
                        title: 'Reject Applicant',
                        text: 'Are you sure you want to reject this applicant?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Reject',
                        confirmButtonColor: '#dc2626',
                    }).then(result => {
                        if (!result.isConfirmed) return;
                        $.post("{{ route('applicant.reject.store') }}", {
                            applicant_id: id,
                            _token: '{{ csrf_token() }}'
                        }).done(function() {
                            Swal.fire({ icon: 'success', title: 'Rejected', timer: 1200, showConfirmButton: false });
                            applicantTable.ajax.reload(null, false);
                        }).fail(function() {
                            Swal.fire('Error', 'Failed to reject.', 'error');
                        });
                    });

                } else if (action === 'undo') {
                    Swal.fire({
                        title: 'Undo Mapping?',
                        text: 'Remove the mapping for this applicant?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Undo',
                        confirmButtonColor: '#dc2626',
                    }).then(result => {
                        if (!result.isConfirmed) return;
                        $.post("{{ route('applicant.mapping.rollback') }}", {
                            applicant_id: id,
                            jobposting_docid: btn.data('job'),
                            _token: '{{ csrf_token() }}'
                        }).done(function() {
                            Swal.fire({ icon: 'success', title: 'Mapping removed', timer: 1200, showConfirmButton: false });
                            applicantTable.ajax.reload(null, false);
                        }).fail(function() {
                            Swal.fire('Error', 'Rollback failed.', 'error');
                        });
                    });
                }
            });

            // ── DIVISION / DEPARTMENT FILTER ─────────────────────────
            $('#filterDivision').select2({
                placeholder: 'Filter by Division',
                allowClear: true,
                width: '100%',
                dropdownParent: $('body'),
            });

            $('#filterDepartment').select2({
                placeholder: 'Filter by Department',
                allowClear: true,
                width: '100%',
                dropdownParent: $('body'),
            });

            $('#filterDivision').on('change', function () {
                const divId = $(this).val();
                const $dept = $('#filterDepartment');

                $dept.empty().append('<option value="">Filter by Department</option>');
                if ($dept.hasClass('select2-hidden-accessible')) $dept.select2('destroy');
                $dept.select2({ placeholder: 'Filter by Department', allowClear: true, width: '100%' });

                if (divId) {
                    $.get("{{ route('applicant.departments') }}", { division_id: divId }, function (data) {
                        data.forEach(d => $dept.append(`<option value="${d.department_id}">${d.department_name}</option>`));
                    });
                }

                applicantTable.ajax.reload();
            });

            $('#filterDepartment').on('change', function () {
                applicantTable.ajax.reload();
            });

            // Init division Select2 once
            $('#tagDivisionSelect').select2({
                dropdownParent: $('#taggingModal'),
                placeholder: '🔍 Search Division...',
                width: '100%',
                allowClear: true,
            });

            function initDeptSelect2() {
                const $dept = $('#tagDeptSelect');
                if ($dept.hasClass('select2-hidden-accessible')) {
                    $dept.select2('destroy');
                }
                $dept.select2({
                    dropdownParent: $('#taggingModal'),
                    placeholder: '🔍 Search Department...',
                    width: '100%',
                    allowClear: true,
                });
            }

            // Init dept Select2 with empty state
            initDeptSelect2();

            $('#tagDivisionSelect').on('change', function() {
                const divId = $(this).val();
                const $dept = $('#tagDeptSelect');

                if (!divId) {
                    if ($dept.hasClass('select2-hidden-accessible')) $dept.select2('destroy');
                    $dept.html('<option value="">-- Select Division first --</option>');
                    initDeptSelect2();
                    return;
                }

                if ($dept.hasClass('select2-hidden-accessible')) $dept.select2('destroy');
                $dept.html('<option value="">Loading...</option>');
                initDeptSelect2();

                $.get("{{ route('applicant.departments') }}", { division_id: divId }, function(data) {
                    if ($dept.hasClass('select2-hidden-accessible')) $dept.select2('destroy');
                    $dept.html('<option value="">-- Select Department --</option>');
                    data.forEach(d => {
                        $dept.append(`<option value="${d.department_id}">${d.department_name}</option>`);
                    });
                    initDeptSelect2();
                    $('#tagDivisionSelect').trigger('deptLoaded');
                });
            });

            $('#saveTagging').on('click', function() {
                const applicantId = $('#tagApplicantId').val();
                const divisionId  = $('#tagDivisionSelect').val();
                const departmentId = $('#tagDeptSelect').val();

                if (!divisionId || !departmentId) {
                    Swal.fire('Incomplete', 'Please select both division and department.', 'warning');
                    return;
                }

                $.post("{{ route('applicant.tag.store') }}", {
                    applicant_id: applicantId,
                    division_id: divisionId,
                    department_id: departmentId,
                    _token: '{{ csrf_token() }}'
                }).done(function() {
                    Swal.fire({ icon: 'success', title: 'Tagged!', timer: 1200, showConfirmButton: false });
                    $('#taggingModal').addClass('hidden').removeClass('flex');
                    applicantTable.ajax.reload(null, false);
                }).fail(function() {
                    Swal.fire('Error', 'Failed to save tag.', 'error');
                });
            });

            $('#closeTaggingModal, #closeTaggingModalBtn').on('click', function() {
                $('#taggingModal').addClass('hidden').removeClass('flex');
            });

            // reset filters
            $('#btnResetFilters').on('click', function() {
                $('#applicantsFilters input').val('');
                $('#filterDivision').val(null).trigger('change.select2');
                $('#filterDepartment').empty().append('<option value="">Filter by Department</option>').val(null).trigger('change.select2');
                applicantTable.search('').columns().search('').draw();
            });

            // filter status card
            $('.status-filter').on('click', function(e) {
                e.preventDefault();
                $('.status-filter').removeClass('active');
                $(this).addClass('active');
                currentStatus = $(this).data('status') || '';
                applicantTable.ajax.reload();
            });

            // ==============================
            // CLOSE MODAL (FIXED ❗)
            // ==============================
            function closeModal() {
                $('#mappingModalContent')
                    .removeClass('scale-100 opacity-100')
                    .addClass('scale-95 opacity-0');

                setTimeout(() => {
                    $('#mappingModal').addClass('hidden').removeClass('flex');
                }, 200);
            }

            $('#closeMappingModal, #closeMappingModalBtn').on('click', closeModal);


            // ==============================
            // LOAD JOB POSTING
            // ==============================
            function loadJobPostings() {
                $.ajax({
                    url: "{{ route('jobposting.list') }}",
                    type: "GET",
                    success: function (res) {
                        let $select = $('#jobPostingSelect');

                        $select.empty().append(`<option value="">Select Job Posting</option>`);

                        res.forEach(item => {
                            $select.append(`
                                <option value="${item.docid}" data-status="${item.status}">
                                    ${item.docid} - ${item.job_name}
                                </option>
                            `);
                        });

                        // 🔥 INIT HERE
                        initSelect2();
                    }
                });
            }

            function initSelect2() {
                $('#jobPostingSelect').select2({
                    dropdownParent: $('#mappingModal'),
                    placeholder: "🔍 Search Job Posting...",
                    width: '100%',
                    allowClear: true,
                    templateResult: formatJob,
                    templateSelection: formatJobSelection
                });
            }

            function formatJob(data) {
                if (!data.id) return data.text;

                let text = data.text;
                let [doc, info] = text.split(' - ');

                const status = $(data.element).data('status');
                const statusCfg = {
                    P: { label: 'Posted',   bg: '#dbeafe', color: '#1d4ed8' },
                    U: { label: 'Unposted', bg: '#f3f4f6', color: '#374151' },
                };
                const cfg = statusCfg[status] ?? { label: status, bg: '#f3f4f6', color: '#374151' };
                const badge = `<span style="font-size:10px; font-weight:600; padding:2px 7px; border-radius:999px;
                    background:${cfg.bg}; color:${cfg.color}; margin-left:6px;">${cfg.label}</span>`;

                return $(`
                    <div class="py-2 px-1">
                        <div style="font-size:14px; font-weight:600; color:#111827; display:flex; align-items:center;">
                            ${info || '-'} ${badge}
                        </div>
                        <div style="font-size:12px; color:#6b7280; margin-top:2px;">
                            ${doc}
                        </div>
                    </div>
                `);
            }

            function formatJobSelection(data) {
                if (!data.id) return data.text;

                let [doc, info] = data.text.split(' - ');

                return `${doc} - ${info}`;
            }

            $('#jobPostingSelect').select2({
                dropdownParent: $('#mappingModal'),
                width: '100%',
                placeholder: "🔍 Search job...",
            });
            // ==============================
            // SAVE MAPPING
            // ==============================
            $('#saveMapping').on('click', function () {
                let applicantId = $('#mapApplicantId').val();
                let jobPostingId = $('#jobPostingSelect').val();

                if (!jobPostingId) {
                    alert('Please select job posting');
                    return;
                }

                $.ajax({
                    url: "{{ route('applicant.mapping.store') }}",
                    type: "POST",
                    data: {
                        applicant_id: applicantId,
                       jobposting_docid: jobPostingId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function () {
                        alert('Mapping saved!');

                        closeModal();

                        $('#applicantsTable').DataTable().ajax.reload();
                    },
                    error: function () {
                        alert('Failed to save mapping');
                    }
                });
            });
        });
    </script>


    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


</x-app-layout>
