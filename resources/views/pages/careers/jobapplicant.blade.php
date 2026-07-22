<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'jobpostings' ? 'HR' : '';
    @endphp
    <style>
        tr.group-alt {
            background-color: rgba(250, 204, 21, 0.06);
        }
    </style>
    <div class="max-w-9xl mx-auto p-2">
        {{-- Tab nav --}}
        <div class="flex gap-1 border-b border-gray-200 dark:border-gray-700">
            <button type="button" id="tabBtnList"
                class="applicant-tab-btn rounded-t-lg border border-b-0 border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-indigo-600 dark:border-gray-700 dark:bg-gray-800 dark:text-indigo-400">
                📄 Applicant List
            </button>
            @if(auth()->user()->hasRole('RECACCALLDEPT'))
            <button type="button" id="tabBtnDuplicates"
                class="applicant-tab-btn rounded-t-lg border border-b-0 border-gray-200 bg-gray-50 px-5 py-2.5 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                🧬 Duplicate Users
                <span id="dupCountBadge"
                    class="ml-1 hidden rounded-full bg-red-500 px-2 py-0.5 text-xs font-bold text-white"></span>
            </button>
            @endif
        </div>

        <div id="tabPanelList" class="flex flex-col gap-4">
        <div class="grid auto-rows-fr grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6">

            {{-- All Status --}}
            <a href="#" class="status-filter group block h-full" data-status="">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📄</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">All</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $all }}</p>
                </div>
            </a>

            {{-- Unchecked --}}
            <a href="#" class="status-filter group block h-full" data-status="is_read_N">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⏳</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Unchecked</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $unchecked }}</p>
                </div>
            </a>

            {{-- Checked --}}
            <a href="#" class="status-filter group block h-full" data-status="is_read_Y">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✏️</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Checked</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $checked }}</p>
                </div>
            </a>

            {{-- Reject --}}
            <a href="#" class="status-filter group block h-full" data-status="R">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⛔️</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Reject</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $reject }}</p>
                </div>
            </a>

            {{-- Approved --}}
            <a href="#" class="status-filter group block h-full" data-status="C">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✅</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Approved</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $approved }}</p>
                </div>
            </a>

            {{-- Transferred --}}
            <a href="#" class="status-filter group block h-full" data-status="T">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-violet-700 bg-violet-200/20 p-3 text-violet-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-violet-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🔄</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Transfer</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $transferred }}</p>
                </div>
            </a>

        </div>
        <div
            class="mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div
                class="flex flex-col items-start justify-between gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] sm:flex-row sm:items-center">
                <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">Applicant List</h2>
            </div>

            <div id="applicantsFilters" class="grid grid-cols-1 gap-3 px-5 pt-4 sm:grid-cols-6 lg:grid-cols-12">

                @if($canFilterJobTL)
                <!-- ROW 1 : Job Title - Job Level -->
                <div class="col-span-1 sm:col-span-6 lg:col-span-12">
                    <select id="filterJobTL"
                        class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </select>
                </div>
                @endif

                <!-- ROW 2 : Reset -->
                <div class="col-span-1 sm:col-span-2 lg:col-span-2">
                    <button id="btnResetFilters"
                        class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">
                        Reset
                    </button>
                </div>

            </div>


            <div class="relative mt-4 overflow-hidden">
                <table id="applicantsTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th scope="col" class="w-32 px-4 py-3 text-center font-medium">
                                DocID
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-left font-medium">
                                Date
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-left font-medium">
                                Name
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-left font-medium">
                                Education
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-left font-medium">
                                Religion
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-center font-medium">
                                Height
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-center font-medium">
                                Weight
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-center font-medium">
                                Last Working
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-center font-medium">
                                Score
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-center font-medium">
                                Step
                            </th>
                            <th scope="col" class="w-28 px-4 py-3 text-center font-medium">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-[#0f172a]">
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>

        <div id="rowDupPanel"
            class="mt-2 hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div class="flex items-start justify-between border-b border-gray-100 px-5 py-2 dark:border-white/[0.06]">
                <div>
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">🧬 Matched
                        Applications</h2>
                    <p id="rowDupPanelTitle" class="mt-1 text-sm text-gray-500 dark:text-gray-400"></p>
                </div>
                <button type="button" id="rowDupPanelClose"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">✕</button>
            </div>

            <div class="relative overflow-hidden">
                <table class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-24 px-4 py-3 text-left font-medium">Action</th>
                            <th class="px-4 py-3 text-left font-medium">Matched By</th>
                            <th class="px-4 py-3 text-left font-medium">DocID</th>
                            <th class="px-4 py-3 text-left font-medium">Job Title — Level</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Apply Date</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                            <th class="px-4 py-3 text-left font-medium">Step</th>
                        </tr>
                    </thead>
                    <tbody id="rowDupPanelBody" class="divide-y divide-gray-200 dark:divide-gray-700"></tbody>
                </table>
            </div>
        </div>
        </div>

        @if(auth()->user()->hasRole('RECACCALLDEPT'))
        <div id="tabPanelDuplicates"
            class="mt-2 hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div class="border-b border-gray-100 px-5 py-2 dark:border-white/[0.06]">
                <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">🧬 Duplicate Users</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Applicants sharing the same KTP ID and Date of Birth — likely the same person who applied
                    more than once. Review which jobs they applied to and each application's current status.
                </p>
            </div>

            <div class="relative overflow-hidden">
                <table id="dupApplicantsTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th class="w-28 px-4 py-3 text-left font-medium">Action</th>
                            <th class="px-4 py-3 text-left font-medium">Matched By</th>
                            <th class="px-4 py-3 text-left font-medium">Full Name</th>
                            <th class="px-4 py-3 text-left font-medium">KTP ID</th>
                            <th class="px-4 py-3 text-left font-medium">Date of Birth</th>
                            <th class="px-4 py-3 text-left font-medium">DocID</th>
                            <th class="px-4 py-3 text-left font-medium">Job Title — Level</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Apply Date</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        @endif

    </div>

    <!-- Remap Modal -->
    <div id="remapModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
        <div class="w-full max-w-2xl transform rounded-2xl bg-white p-8 shadow-2xl transition-all duration-300 scale-95 opacity-0" id="remapModalContent">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Remap Applicant</h2>
                    <p class="text-sm text-gray-500">Old mapping → <strong>Transfer Candidate</strong>. New job apply will be created.</p>
                </div>
                <button id="closeRemapModal" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
            </div>

            <input type="hidden" id="remapApplyId">

            <div class="mb-5 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 flex items-start gap-3">
                <span class="mt-0.5 text-amber-500 text-base">📌</span>
                <div class="flex-1">
                    <p class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-2">Current Job Applied</p>
                    <div class="grid grid-cols-2 gap-x-6 gap-y-1 text-sm">
                        <div>
                            <span class="text-xs text-gray-400">Job ID</span>
                            <p class="font-medium text-gray-800" id="remapCurrentJobId">—</p>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400">Job Title</span>
                            <p class="font-medium text-gray-800" id="remapCurrentJobTitle">—</p>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400">Company</span>
                            <p class="text-gray-700" id="remapCurrentCompany">—</p>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400">Division</span>
                            <p class="text-gray-700" id="remapCurrentDivision">—</p>
                        </div>
                        <div class="col-span-2">
                            <span class="text-xs text-gray-400">Department</span>
                            <p class="text-gray-700" id="remapCurrentDepartment">—</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Select New Job Posting</label>
                <select id="remapJobSelect" style="width:100%">
                    <option value="">Select Job Posting</option>
                </select>
            </div>

            <div class="flex justify-end gap-3">
                <button id="closeRemapModalBtn" class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
                <button id="saveRemap" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">Save Remap</button>
            </div>
        </div>
    </div>

    <script>
        var currentUser = "{{ auth()->user()->username }}";
        var canRemap = {{ auth()->user()->hasRole('RECACCALLDEPT') ? 'true' : 'false' }};
    </script>


    {{-- <script>
                $(document).ready(function() {
                    let currentStatus = '';
                    let applicantTable = $('#applicantsTable').DataTable({
                        responsive: true,
                        processing: true,
                        serverSide: true,
                        searching: true,
                        paging: true,
                        info: true,
                        lengthChange: true,
                        pageLength: 10,
                        ajax: {
                            url: "{{ route('jobapplicant.json') }}",
                            type: 'GET',
                            data: function(d) {
                                d.status = currentStatus;
                            }
                        },
                        order: [
                            [8, 'desc']
                        ],
                        columns: [{
                                data: 'docid',
                                render: function(data, type, row) {
                                    return `<a href="/showcareers/${row.id}" target="_blank" class="px-4 py-2.5 bg-indigo-500 text-white rounded hover:bg-indigo-700">${data}</a>`;
                                }
                            },
                            {
                                data: 'apply_date'
                            },
                            {
                                data: 'fullname'
                            },
                            {
                                data: 'education_name'
                            },
                            {
                                data: 'religion'
                            },
                            {
                                data: 'height',
                                className: 'small-col'
                            },
                            {
                                data: 'weight',
                                className: 'small-col'
                            },
                            {
                                data: 'company_name'
                            },
                            {
                                data: 'match_score_percentage',
                                className: 'small-col'
                            },
                            {
                                data: 'prev_apply_step',
                                render: function(data) {
                                    const labelMap = {
                                        'JOAPHC': 'Job Apply HC',
                                        'JOAPUS': 'Job Apply User',
                                        'WIHC': 'Create Schedule Interview HC',
                                        'IHC': 'Interview HC',
                                        'WIU': 'Create Schedule Interview User',
                                        'IU': 'Interview User',
                                        'WPT': 'Waiting Psycho Test',
                                        'PT': 'Psycho Test',
                                        'OFF': 'Offering',
                                        'JOIN': 'Join'
                                    };
                                    return `<span class=\"w-32 bg-blue-300/30 text-blue-600 text-sm font-semibold px-4 py-2 text-center rounded\">${labelMap[data] || data}</span>`;
                                }
                            }
                        ],
                        rowCallback: function(row, data, index) {
                            if (data.is_read === 'N') {
                                $(row).css('color', 'blue');
                            } else {
                                $(row).css('color', 'black');
                            }
                        }
                    });
                    $('#applicantsTable thead th').eq(5).addClass('small-col');
                    $('#applicantsTable thead th').eq(6).addClass('small-col');
                    $('#applicantsTable thead th').eq(8).addClass('small-col');

                    // Event handler status-filter
                    $('.status-filter').on('click', function(e) {
                        e.preventDefault();
                        $('.status-filter').removeClass('active');
                        $(this).addClass('active');
                        currentStatus = $(this).data('status');
                        applicantTable.ajax.reload();
                    });
                });
            </script> --}}


    <script>
        $(document).ready(function() {
            let currentStatus = '';

            // Definisi kolom (data + name HARUS diisi untuk server-side)
            const colDefs = [{
                    data: 'docid',
                    name: 'docid',
                    type: 'text',
                    title: 'DocID'
                },
                {
                    data: 'apply_date',
                    name: 'apply_date',
                    type: 'text',
                    title: 'Date'
                },
                {
                    data: 'fullname',
                    name: 'fullname',
                    type: 'text',
                    title: 'Name'
                },
                {
                    data: 'education_name',
                    name: 'education_name',
                    type: 'text',
                    title: 'Education'
                },
                {
                    data: 'religion',
                    name: 'religion',
                    type: 'text',
                    title: 'Religion'
                },
                {
                    data: 'height',
                    name: 'height',
                    type: 'text',
                    title: 'Height'
                },
                {
                    data: 'weight',
                    name: 'weight',
                    type: 'text',
                    title: 'Weight'
                },
                {
                    data: 'company_name',
                    name: 'company_name',
                    type: 'text',
                    title: 'Last Working'
                },
                {
                    data: 'match_score_percentage',
                    name: 'match_score_percentage',
                    type: 'text',
                    title: 'Score'
                },
                {
                    data: 'apply_step',
                    name: 'apply_step',
                    type: 'select',
                    title: 'Step'
                },
            ];

            const stepLabelMap = {
                'JOAPHC': 'Job Apply HC',
                'JOAPUS': 'Job Apply User',
                'WIHC': 'Create Schedule Interview HC',
                'IHC': 'Interview HC',
                'WIU': 'Create Schedule Interview User',
                'IU': 'Interview User',
                'WPT': 'Waiting Psycho Test',
                'PT': 'Psycho Test',
                'OFF': 'Offering',
                'JOIN': 'Join'
            };



            // const $thead = $('#applicantsTable thead');
            // const $filterRow = $('<tr class="filters"></tr>');

            // colDefs.forEach(def => {
            //     let ctl = '';
            //     if (def.type === 'select' && def.name === 'prev_apply_step') {
            //         ctl = `
        // <select class="col-filter select-filter" data-colname="${def.name}">
        //     <option value="">All</option>
        //     ${Object.entries(stepLabelMap)
        //         .map(([k,v]) => `<option value="${k}">${v}</option>`)
        //         .join('')}
        // </select>`;
            //     } else {
            //         ctl = `<input type="text" class="col-filter input-filter">`;
            //     }
            //     $filterRow.append($('<th>').html(ctl));
            // });

            // $thead.append($filterRow);

            const columnFilters = [{
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
                    placeholder: 'Full Name',
                    className: 'whitespace-normal break-words'
                },
                {
                    index: 4,
                    type: 'text',
                    placeholder: 'Education',
                    className: 'whitespace-normal break-words'
                },
                {
                    index: 5,
                    type: 'text',
                    placeholder: 'Religion'
                },
                {
                    index: 6,
                    type: 'text',
                    placeholder: 'Height'
                },
                {
                    index: 7,
                    type: 'text',
                    placeholder: 'Weight'
                },
                {
                    index: 8,
                    type: 'text',
                    placeholder: 'Company',
                    className: 'whitespace-normal break-words'
                },
                {
                    index: 9,
                    type: 'text',
                    placeholder: 'Score'
                },
                {
                    index: 10,
                    type: 'select',
                    placeholder: 'Step',
                    // options: stepLabelMap
                }
            ];

            const $filters = $('#applicantsFilters');

            columnFilters.forEach(col => {
                let $el;

                if (col.type === 'select') {
                    $el = $(`
        <select id="filterStep"
            class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm
                   focus:border-blue-500 focus:ring-1 focus:ring-blue-500 truncate text-ellipsis">
            <option value="">All Step</option >
        </select>
    `);


                    if (col.options) {
                        Object.entries(col.options).forEach(([val, label]) => {
                            $el.append(`<option value="${val}">${label}</option>`);
                        });
                    }

                    $el.on('change', function() {
                        applicantTable
                            .column(col.index)
                            .search(this.value || '')
                            .draw();
                    });

                } else {
                    $el = $(`
            <input type="text"
                 class="w-full rounded-md border border-gray-200 px-3 py-2  text-sm
               focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                placeholder="Search ${col.placeholder}">
        `);

                    let debounce;
                    $el.on('input', function() {
                        clearTimeout(debounce);
                        const val = this.value;
                        debounce = setTimeout(() => {
                            applicantTable
                                .column(col.index)
                                .search(val)
                                .draw();
                        }, 300);
                    });
                }

                $filters.append($el);
            });

            // ===== Init DataTable =====
            const applicantTable = $('#applicantsTable').DataTable({
                responsive: {
                    details: {
                        type: 'column',
                        target: 0 // FIRST column
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
                        targets: [1, 2], // DocID, Date (important)
                        responsivePriority: 2
                    },
                    {
                        targets: [3, 4, 5, 6, 7, 8, 9, 10], // less important columns
                        responsivePriority: 100
                    }
                ],

                processing: true,
                serverSide: true,
                searching: true, // global search tetap bisa
                paging: true,
                info: true,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],

                // 🔥 ADD THIS
                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'Job_Applicants',
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
                        title: 'Job_Applicants',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                // 🔥 END ADD
                pageLength: 10,
                orderCellsTop: true, // penting utk 2 baris thead
                ajax: {
                    url: "{{ route('jobapplicant.json') }}",
                    type: 'GET',
                    data: function(d) {
                        d.status = currentStatus;
                        d.job_tl_exact = $('#filterJobTL').val() || '';
                    }
                },
                order: [
                    [2, 'desc']
                ],
                columns: [{
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false,
                        searchable: false,
                        data: null,
                        defaultContent: ''
                    },
                    {
                        data: 'docid',
                        name: 'docid',
                        render: function(data, type, row) {
                            return `<a href="/showcareers/${row.eid}" target="_blank" class= 'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200  bg-gray-600 hover:bg-gray-700 '>${data}</a>`;
                        }
                    },
                    {
                        data: 'apply_date',
                        name: 'apply_date'
                    },
                    {
                        data: 'fullname',
                        name: 'fullname'
                    },
                    {
                        data: 'education_name',
                        name: 'education_name'
                    },
                    {
                        data: 'religion',
                        name: 'religion'
                    },
                    {
                        data: 'height',
                        name: 'height',
                        className: 'small-col'
                    },
                    {
                        data: 'weight',
                        name: 'weight',
                        className: 'small-col'
                    },
                    {
                        data: 'company_name',
                        name: 'company_name'
                    },
                    {
                        data: 'match_score_percentage',
                        name: 'match_score_percentage',
                        className: 'small-col'
                    },
                    {
                        data: 'apply_step',
                        name: 'apply_step',
                        render: function(data) {
                            const label = stepLabelMap[data] || data;
                            return `<span class="inline-flex justify-center items-center w-[120px] bg-blue-300/30 text-blue-600 text-sm font-semibold px-3 py-1.5 text-center rounded whitespace-normal break-words"> ${label} </span>`;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (!canRemap) return `<span class="text-xs text-gray-400">—</span>`;
                            if (row.status === 'R' || row.status === 'C') {
                                return `<span class="text-xs text-gray-400">—</span>`;
                            }
                            return `<button class="remap-btn inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-md bg-indigo-50 text-indigo-600 hover:bg-indigo-100 border border-indigo-200"
                                data-id="${row.eid}" data-docid="${row.docid}"
                                data-job-id="${row.docidposting}" data-job-title="${row.job_title || ''}"
                                data-company="${row.posting_cpnyid || ''}" data-division="${row.division_name || ''}" data-department="${row.department_name || ''}">
                                🔄 Remap
                            </button>`;
                        }
                    }
                ],
                rowCallback: function(row, data) {
                    // reset dulu
                    $(row).css('color', '');

                    if (data.status === 'R') {
                        // merah (Tailwind red-600)
                        $(row).css('color', '#dc2626');
                    } else if (data.is_read === 'N') {
                        // biru (Tailwind blue-600)
                        $(row).css('color', '#2563eb');
                    } else {
                        $(row).css('color', 'black');
                    }
                },
                initComplete: function() {
                    const api = this.api();

                    // Input text → debounce
                    let debounce;
                    $('#applicantsTable thead').on('input', 'input.col-filter', function() {
                        const colName = $(this).data('colname');
                        const val = this.value;
                        clearTimeout(debounce);
                        debounce = setTimeout(function() {
                            api.column(colName + ':name').search(val)
                                .draw(); // <-- pakai selector :name
                        }, 300);
                    });

                    // Select (Step)
                    $('#applicantsTable thead').on('change', 'select.col-filter', function() {
                        const colName = $(this).data('colname');
                        api.column(colName + ':name').search(this.value)
                            .draw(); // <-- pakai selector :name
                    });
                }
            });

            applicantTable.on('xhr.dt', function(e, settings, json) {
                console.log('XHR:', json);

                if (!json || !Array.isArray(json.steps) || !json.steps.length) {
                    return;
                }

                const $stepFilter = $('#filterStep');

                if ($stepFilter.children('option').length > 1) return;

                json.steps.forEach(step => {
                    const label = stepLabelMap[step] || step;
                    $stepFilter.append(`<option value="${step}">${label}</option>`);
                });
            });


            // kecilkan tiga header kolom numerik
            $('#applicantsTable thead tr:eq(0) th').eq(5).addClass('small-col');
            $('#applicantsTable thead tr:eq(0) th').eq(6).addClass('small-col');
            $('#applicantsTable thead tr:eq(0) th').eq(8).addClass('small-col');

            if ($('#filterJobTL').length) {
                $('#filterJobTL').select2({
                    placeholder: 'Filter by Job Title — Job Level',
                    allowClear: true,
                    width: 'resolve',
                    ajax: {
                        url: "{{ route('jobfilters.tl') }}", // endpoint gabungan
                        dataType: 'json',
                        delay: 200,
                        data: params => ({
                            q: params.term || ''
                        }), // pencarian server (opsional)
                        processResults: data => ({
                            // server sudah kirim {id:'Title|||Level', text:'Title — Level'}
                            results: data
                        }),
                        cache: true
                    }
                });

                // reload tabel saat filter berubah
                $('#filterJobTL').on('change', function() {
                    applicantTable.ajax.reload();
                });
            }

            // // reset
            // $('#btnResetFilters').on('click', function() {
            //     $('#filterJobTL').val(null).trigger('change');
            //     applicantTable.ajax.reload();
            // });
            $('#btnResetFilters').on('click', function() {
                $('#applicantsFilters input, #applicantsFilters select').val('');
                applicantTable.search('').columns().search('').draw();
            });



            // Filter tombol status (All/Unchecked/Checked/Reject/Approved)
            $('.status-filter').on('click', function(e) {
                e.preventDefault();
                $('.status-filter').removeClass('active');
                $(this).addClass('active');
                currentStatus = $(this).data('status') || '';
                applicantTable.ajax.reload();
            });

            // ── REMAP ─────────────────────────────────────────────────
            function openRemapModal() {
                $('#remapModal').removeClass('hidden').addClass('flex');
                setTimeout(() => {
                    $('#remapModalContent').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
                }, 10);
            }

            function closeRemapModal() {
                $('#remapModalContent').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
                setTimeout(() => {
                    $('#remapModal').addClass('hidden').removeClass('flex');
                }, 200);
            }

            $('#closeRemapModal, #closeRemapModalBtn').on('click', closeRemapModal);

            function loadRemapJobPostings() {
                $.ajax({
                    url: "{{ route('jobposting.list') }}",
                    type: 'GET',
                    success: function(res) {
                        const $sel = $('#remapJobSelect');
                        $sel.empty().append('<option value="">Select Job Posting</option>');
                        res.forEach(item => {
                            const badge = item.status === 'U' ? '[Unposted]' : '[Posted]';
                            $sel.append(`<option value="${item.docid}">${item.docid} - ${item.job_name} ${badge}</option>`);
                        });
                        if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
                        $sel.select2({
                            dropdownParent: $('#remapModal'),
                            placeholder: '🔍 Search Job Posting...',
                            width: '100%',
                            allowClear: true,
                        });
                    }
                });
            }

            $(document).on('click', '.remap-btn', function() {
                const $btn = $(this);
                $('#remapApplyId').val($btn.data('id'));
                $('#remapCurrentJobId').text($btn.data('jobId') || '—');
                $('#remapCurrentJobTitle').text($btn.data('jobTitle') || '—');
                $('#remapCurrentCompany').text($btn.data('company') || '—');
                $('#remapCurrentDivision').text($btn.data('division') || '—');
                $('#remapCurrentDepartment').text($btn.data('department') || '—');
                loadRemapJobPostings();
                openRemapModal();
            });

            $('#saveRemap').on('click', function() {
                const applyId  = $('#remapApplyId').val();
                const newJobId = $('#remapJobSelect').val();

                if (!newJobId) {
                    Swal.fire('Incomplete', 'Please select a job posting.', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Remap Applicant?',
                    text: 'Current mapping will be set to Transfer Candidate and a new apply record will be created.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Remap',
                    confirmButtonColor: '#4f46e5',
                }).then(result => {
                    if (!result.isConfirmed) return;
                    $.post("{{ route('jobapplicant.remap') }}", {
                        apply_id:  applyId,
                        new_jobid: newJobId,
                        _token: '{{ csrf_token() }}'
                    }).done(function() {
                        closeRemapModal();
                        Swal.fire({ icon: 'success', title: 'Remapped!', timer: 1200, showConfirmButton: false });
                        applicantTable.ajax.reload(null, false);
                    }).fail(function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.error || 'Remap failed.', 'error');
                    });
                });
            });

            // ── DUPLICATE USERS TAB ──────────────────────────────────
            let dupApplicantTable = null;
            let dupApplicantTableLoaded = false;

            const statusBadgeClass = {
                'Unchecked': 'bg-blue-300/30 text-blue-600',
                'Checked': 'bg-gray-300/30 text-gray-600',
                'Reject': 'bg-red-300/30 text-red-600',
                'Approved': 'bg-green-300/30 text-green-600',
                'Transfer': 'bg-violet-300/30 text-violet-600'
            };

            function updateDupCountBadge(count) {
                if (count > 0) {
                    $('#dupCountBadge').removeClass('hidden').text(count);
                } else {
                    $('#dupCountBadge').addClass('hidden');
                }
            }

            // ── ROW CLICK → SHOW KTP+DOB MATCHED JOBS PANEL (BELOW TABLE) ─
            // Klik baris (bukan link/tombol di dalamnya) → kalau applicant ini
            // (matched by KTP+DOB) punya lebih dari 1 job apply, tampilkan
            // panel terpisah di bawah tabel. Kalau tidak ada duplikat, tidak
            // terjadi apa-apa.
            $('#applicantsTable tbody').on('click', 'tr', function(e) {
                if ($(e.target).closest('a, button, .dtr-control').length) return;

                const $tr = $(this);
                const rowData = applicantTable.row($tr).data();
                if (!rowData || !rowData.docid) return;

                // toggle kalau baris yang sama diklik lagi
                if ($tr.hasClass('row-dup-active')) {
                    $tr.removeClass('row-dup-active');
                    $('#rowDupPanel').addClass('hidden');
                    return;
                }

                $.getJSON("{{ route('jobapplicant.rowduplicates') }}", { docid: rowData.docid })
                    .done(function(json) {
                        const rows = json.data || [];
                        if (!rows.length) return;

                        $('#applicantsTable tbody tr').removeClass('row-dup-active');
                        $tr.addClass('row-dup-active');

                        $('#rowDupPanelTitle').text(
                            `${rowData.fullname || 'This applicant'} applied to ${rows.length} jobs — matched by KTP + DOB`
                        );

                        const bodyRows = rows.map(function(r) {
                            const jobTL = [r.job_title, r.job_level].filter(Boolean).join(' — ') || '—';
                            const isThisRow = r.docid === rowData.docid;
                            const cls = statusBadgeClass[r.status_label] || 'bg-gray-300/30 text-gray-600';
                            const matchedByBadge = `<span class="inline-block rounded bg-amber-200/60 px-2 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-300 dark:text-amber-900">${r.matched_by || 'KTP + DOB'}</span>`;

                            return `<tr class="${isThisRow ? 'bg-indigo-50 dark:bg-indigo-900/20' : ''}">
                                <td class="px-4 py-3">
                                    <a href="/showcareers/${r.eid}" target="_blank" class="inline-flex justify-center items-center px-3 py-1.5 text-xs font-semibold text-white rounded bg-gray-600 hover:bg-gray-700">View</a>
                                </td>
                                <td class="px-4 py-3">${matchedByBadge}</td>
                                <td class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">${r.docid}${isThisRow ? ' <span class="ml-1 text-[10px] font-bold text-indigo-500">(this)</span>' : ''}</td>
                                <td class="px-4 py-3">${jobTL}</td>
                                <td class="px-4 py-3">${r.company_name || '—'}</td>
                                <td class="px-4 py-3">${r.apply_date || '—'}</td>
                                <td class="px-4 py-3"><span class="inline-flex justify-center items-center px-3 py-1.5 text-sm font-semibold text-center rounded ${cls}">${r.status_label}</span></td>
                                <td class="px-4 py-3">${r.step_label}</td>
                            </tr>`;
                        }).join('');

                        $('#rowDupPanelBody').html(bodyRows);
                        $('#rowDupPanel').removeClass('hidden');
                        $('#rowDupPanel')[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    });
            });

            $('#rowDupPanelClose').on('click', function() {
                $('#applicantsTable tbody tr').removeClass('row-dup-active');
                $('#rowDupPanel').addClass('hidden');
            });

            @if(auth()->user()->hasRole('RECACCALLDEPT'))
            function initDupApplicantTable() {
                if (dupApplicantTableLoaded) return;
                dupApplicantTableLoaded = true;

                let lastGroupKey = null;
                let groupToggle = false;

                dupApplicantTable = $('#dupApplicantsTable').DataTable({
                    ajax: {
                        url: "{{ route('jobapplicant.duplicates.json') }}",
                        dataSrc: function(json) {
                            updateDupCountBadge(json.data ? json.data.length : 0);
                            return json.data;
                        }
                    },
                    processing: true,
                    serverSide: false,
                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],
                    order: [],
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
                        orderable: false
                    }],
                    dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
                    createdRow: function(row, data) {
                        if (data.group_key !== lastGroupKey) {
                            groupToggle = !groupToggle;
                            lastGroupKey = data.group_key;
                        }
                        if (groupToggle) {
                            $(row).addClass('group-alt');
                        }
                    },
                    columns: [{
                            data: null,
                            defaultContent: ''
                        },
                        {
                            data: 'eid',
                            orderable: false,
                            searchable: false,
                            render: function(data) {
                                return `<a href="/showcareers/${data}" target="_blank" class="inline-flex justify-center items-center px-3 py-1.5 text-xs font-semibold text-white rounded bg-gray-600 hover:bg-gray-700">View</a>`;
                            }
                        },
                        {
                            data: 'matched_by',
                            orderable: false,
                            searchable: false,
                            render: function(data) {
                                return `<span class="mr-1 inline-block rounded bg-amber-200/60 px-2 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-300 dark:text-amber-900">${data || 'KTP + DOB'}</span>`;
                            }
                        },
                        {
                            data: 'full_name'
                        },
                        {
                            data: 'ktp_id'
                        },
                        {
                            data: 'date_of_birth'
                        },
                        {
                            data: 'docid'
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                return [row.job_title, row.job_level].filter(Boolean).join(' — ');
                            }
                        },
                        {
                            data: 'company_name'
                        },
                        {
                            data: 'apply_date'
                        },
                        {
                            data: 'status_label',
                            render: function(data) {
                                const cls = statusBadgeClass[data] || 'bg-gray-300/30 text-gray-600';
                                return `<span class="inline-flex justify-center items-center px-3 py-1.5 text-sm font-semibold text-center rounded ${cls}">${data}</span>`;
                            }
                        }
                    ]
                });
            }

            // Preload the duplicate count badge even before the tab is opened
            $.getJSON("{{ route('jobapplicant.duplicates.json') }}", function(json) {
                updateDupCountBadge(json.data ? json.data.length : 0);
            });
            @endif

            function activateApplicantTab(tab) {
                const isList = tab === 'list';
                $('#tabPanelList').toggleClass('hidden', !isList);
                $('#tabPanelDuplicates').toggleClass('hidden', isList);

                $('#tabBtnList')
                    .toggleClass('bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400', isList)
                    .toggleClass('bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400', !isList);
                $('#tabBtnDuplicates')
                    .toggleClass('bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400', !isList)
                    .toggleClass('bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400', isList);

                if (isList) {
                    applicantTable.columns.adjust().draw(false);
                } else {
                    initDupApplicantTable();
                    if (dupApplicantTable) dupApplicantTable.columns.adjust().draw(false);
                }
            }

            $('#tabBtnList').on('click', function() {
                activateApplicantTab('list');
            });
            $('#tabBtnDuplicates').on('click', function() {
                activateApplicantTab('duplicates');
            });
        });
        // Make each row of .status-filter independent
        document.querySelectorAll('.grid-col-1').forEach(grid => {
            const filters = grid.querySelectorAll('.status-filter');

            filters.forEach(btn => {
                btn.addEventListener('click', e => {
                    e.preventDefault();
                    filters.forEach(s => s.classList.remove('active'));
                    btn.classList.add('active');
                });
            });
        });
    </script>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


</x-app-layout>
