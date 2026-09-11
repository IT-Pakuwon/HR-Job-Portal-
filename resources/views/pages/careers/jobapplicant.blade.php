<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'jobpostings' ? 'HR' : '';
    @endphp
    <style>
        tr.group-alt {
            background-color: rgba(250, 204, 21, 0.06);
        }

        #applicantsTable tr.row-checked td { color: #000000; }
        #applicantsTable tr.row-unchecked td { color: #2563eb; }
        #applicantsTable tr.row-reject td { color: #dc2626; }

        .dark #applicantsTable tr.row-checked td { color: #ffffff; }
        .dark #applicantsTable tr.row-unchecked td { color: #22d3ee; }
        .dark #applicantsTable tr.row-reject td { color: #f87171; }

        .legend-dot-checked { background-color: #000000; }
        .legend-dot-unchecked { background-color: #2563eb; }
        .legend-dot-reject { background-color: #dc2626; }

        .dark .legend-dot-checked { background-color: #ffffff; }
        .dark .legend-dot-unchecked { background-color: #22d3ee; }
        .dark .legend-dot-reject { background-color: #f87171; }

        table#applicantsTable td.step-col,
        table#applicantsTable th.step-col {
            width: 150px !important;
            max-width: 150px !important;
        }

        /* Filter panel — make select2 fields look like the bordered text inputs beside them */
        select.app-filter-field + .select2-container .select2-selection--single {
            height: 38px; display: flex; align-items: center;
            border-radius: 0.5rem; border: 1px solid #e2e8f0 !important;
            background-color: #fff; padding: 0 1.75rem 0 0.75rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }
        select.app-filter-field + .select2-container--open .select2-selection--single {
            border-color: #818cf8 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
        }
        select.app-filter-field + .select2-container .select2-selection__rendered {
            padding: 0; line-height: 1; color: #334155; font-size: 0.875rem;
        }
        select.app-filter-field + .select2-container .select2-selection__arrow { height: 38px; right: 8px; }
        select.app-filter-field + .select2-container .select2-selection__placeholder { color: #94a3b8; }

        .dark select.app-filter-field + .select2-container .select2-selection--single {
            background-color: #1e293b; border-color: #475569 !important;
        }
        .dark select.app-filter-field + .select2-container .select2-selection__rendered { color: #e2e8f0; }

        .jobapp-select2-dropdown {
            border-radius: 0.75rem !important; border: 1px solid #e2e8f0 !important;
            overflow: hidden; margin-top: 4px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.05);
        }
        .jobapp-select2-dropdown .select2-search__field {
            border-radius: 0.5rem; border: 1px solid #e2e8f0;
            padding: 0.375rem 0.5rem; font-size: 0.8rem; outline: none;
        }
        .jobapp-select2-dropdown .select2-search__field:focus {
            border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
        }
        .jobapp-select2-dropdown .select2-results__option { font-size: 0.8rem; padding: 0.5rem 0.75rem; }
        .jobapp-select2-dropdown .select2-results__option--highlighted[aria-selected] { background-color: #EEF2FF; color: #4338CA; }
        .jobapp-select2-dropdown .select2-results__option[aria-selected="true"] { background-color: #E0E7FF; color: #3730A3; font-weight: 600; }

        .dark .jobapp-select2-dropdown { background-color: #1e293b; border-color: #475569 !important; }
        .dark .jobapp-select2-dropdown .select2-search__field { background-color: #334155; border-color: #475569; color: #e2e8f0; }
        .dark .jobapp-select2-dropdown .select2-results__option { color: #e2e8f0; }
        .dark .jobapp-select2-dropdown .select2-results__option--highlighted[aria-selected] { background-color: #4338CA; color: #fff; }
        .dark .jobapp-select2-dropdown .select2-results__option[aria-selected="true"] { background-color: #3730A3; color: #fff; }
    </style>
    <div class="max-w-9xl mx-auto p-2">
        {{-- Tab nav --}}
        <div class="flex gap-1">
            <button type="button" id="tabBtnList"
                class="applicant-tab-btn rounded-t-lg border border-b-0 border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-indigo-600 dark:border-gray-700 dark:bg-gray-800 dark:text-indigo-400">
                📄 Applicant List
            </button>
            @if(auth()->user()->hasRole('RECACCALLDEPT'))
            <button type="button" id="tabBtnDuplicates"
                class="applicant-tab-btn rounded-t-lg border border-b-0 border-gray-200 bg-gray-50 px-5 py-2.5 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                🧬 Duplicate Applicant
                <span id="dupCountBadge"
                    class="ml-1 hidden rounded-full bg-red-500 px-2 py-0.5 text-sm font-bold text-white"></span>
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

        <div id="applicantFiltersCard"
            class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div class="flex items-center justify-between px-5 py-3">
                <button type="button" id="btnToggleFilters" class="flex items-center gap-1.5 text-sm font-bold uppercase tracking-wider text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h18M6 9.75h12M9.75 15h4.5" />
                    </svg>
                    Filters
                    <svg id="filtersChevron" class="h-3.5 w-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <button type="button" id="btnResetFilters"
                    class="flex items-center gap-1.5 whitespace-nowrap rounded-lg px-3 py-1.5 text-sm font-semibold text-slate-500 transition hover:bg-slate-200/70 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-700/60 dark:hover:text-slate-200">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" />
                    </svg>
                    Reset
                </button>
            </div>

            <div id="applicantFiltersBody" class="border-t border-gray-100 px-5 pb-4 pt-4 dark:border-white/[0.06]">
                @if($canFilterJobTL)
                <div class="mb-3">
                    <label for="filterJobTL" class="mb-1 block text-sm font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                        Job Title &amp; Level
                    </label>
                    <select id="filterJobTL"
                        class="app-filter-field w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    </select>
                </div>
                @endif

                <div id="applicantsFilters" class="flex flex-wrap lg:flex-nowrap items-start gap-3 overflow-x-auto pb-1"></div>
            </div>
        </div>

        <div
            class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div
                class="flex flex-col items-start justify-between gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] sm:flex-row sm:items-center">
                <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">Applicant List</h2>
                <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                    <span class="flex items-center gap-1.5">
                        <span class="legend-dot-checked h-2 w-2 rounded-full"></span>
                        Checked
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="legend-dot-unchecked h-2 w-2 rounded-full"></span>
                        Unchecked
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="legend-dot-reject h-2 w-2 rounded-full"></span>
                        Reject
                    </span>
                </div>
            </div>


            <div class="relative mt-4 overflow-hidden">
                <table id="applicantsTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-sm uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
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
                                Job Applied
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
                            <th scope="col" class="step-col w-32 px-4 py-3 text-center font-medium">
                                Step
                            </th>
                            <th scope="col" class="w-28 px-4 py-3 text-center font-medium">
                                Status
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
                            class="border-b border-gray-100 bg-gray-50/70 text-sm uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-24 px-4 py-3 text-left font-medium">Action</th>
                            <th class="px-4 py-3 text-left font-medium">Matched By</th>
                            <th class="px-4 py-3 text-left font-medium">DocID</th>
                            <th class="px-4 py-3 text-left font-medium">Name</th>
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
                            class="border-b border-gray-100 bg-gray-50/70 text-sm uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
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
        <div class="w-full max-w-2xl transform rounded-2xl bg-white p-8 shadow-2xl transition-all duration-300 scale-95 opacity-0 dark:bg-gray-800" id="remapModalContent">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200">Remap Applicant</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Old mapping → <strong>Transfer Candidate</strong>. New job apply will be created.</p>
                </div>
                <button id="closeRemapModal" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
            </div>

            <input type="hidden" id="remapApplyId">

            <div class="mb-5 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 flex items-start gap-3">
                <span class="mt-0.5 text-amber-500 text-base">📌</span>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-amber-700 uppercase tracking-wide mb-2">Current Job Applied</p>
                    <div class="grid grid-cols-2 gap-x-6 gap-y-1 text-sm">
                        <div>
                            <span class="text-sm text-gray-400">Job ID</span>
                            <p class="font-medium text-gray-800 dark:text-gray-200" id="remapCurrentJobId">—</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-400">Job Title</span>
                            <p class="font-medium text-gray-800 dark:text-gray-200" id="remapCurrentJobTitle">—</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-400">Company</span>
                            <p class="text-gray-700 dark:text-gray-300" id="remapCurrentCompany">—</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-400">Division</span>
                            <p class="text-gray-700 dark:text-gray-300" id="remapCurrentDivision">—</p>
                        </div>
                        <div class="col-span-2">
                            <span class="text-sm text-gray-400">Department</span>
                            <p class="text-gray-700 dark:text-gray-300" id="remapCurrentDepartment">—</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-300">Select New Job Posting</label>
                <select id="remapJobSelect" style="width:100%">
                    <option value="">Select Job Posting</option>
                </select>
            </div>

            <div class="flex justify-end gap-3">
                <button id="closeRemapModalBtn" class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-gray-400 dark:border-gray-700">Cancel</button>
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
                                    return `<a href="/showcareers/${row.id}" class="px-4 py-2.5 bg-indigo-500 text-white rounded hover:bg-indigo-700">${data}</a>`;
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

            // Filters card — collapsible, remembers the user's choice
            (function() {
                const STORAGE_KEY = 'jobapplicant-filters-collapsed';
                const $body = $('#applicantFiltersBody');
                const $chevron = $('#filtersChevron');

                function setCollapsed(collapsed) {
                    $body.toggleClass('hidden', collapsed);
                    $chevron.toggleClass('-rotate-90', collapsed);
                    try {
                        localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
                    } catch (e) {}
                }

                let startCollapsed = true;
                try {
                    const stored = localStorage.getItem(STORAGE_KEY);
                    if (stored !== null) {
                        startCollapsed = stored === '1';
                    }
                } catch (e) {}
                setCollapsed(startCollapsed);

                $('#btnToggleFilters').on('click', function() {
                    setCollapsed(!$body.hasClass('hidden'));
                });
            })();

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
                    placeholder: 'DocID',
                    span: 'w-32 shrink-0'
                },
                {
                    index: 2,
                    type: 'text',
                    placeholder: 'Apply Date',
                    title: 'Type a single date, or a range like 2026-08-01 - 2026-08-31',
                    hint: 'e.g. 2026-08-01 - 2026-08-31',
                    span: 'w-40 shrink-0'
                },
                {
                    index: 3,
                    type: 'text',
                    placeholder: 'Full Name',
                    className: 'whitespace-normal break-words',
                    span: 'flex-1 min-w-[140px]'
                },
                {
                    index: 5,
                    type: 'text',
                    placeholder: 'Education',
                    className: 'whitespace-normal break-words',
                    span: 'flex-1 min-w-[140px]'
                },
                {
                    index: 6,
                    type: 'text',
                    placeholder: 'Religion',
                    span: 'w-36 shrink-0'
                },
                {
                    index: 7,
                    type: 'text',
                    placeholder: 'Height',
                    title: 'Type a value, a range like 160-180, or >=170',
                    hint: 'e.g. 160-180 or >=170',
                    span: 'w-24 shrink-0'
                },
                {
                    index: 8,
                    type: 'text',
                    placeholder: 'Weight',
                    title: 'Type a value, a range like 60-80, or >=70',
                    hint: 'e.g. 60-80 or >=70',
                    span: 'w-24 shrink-0'
                },
                {
                    index: 9,
                    type: 'select2tags',
                    placeholder: 'Company',
                    className: 'whitespace-normal break-words',
                    span: 'flex-1 min-w-[140px]'
                },
                {
                    index: 10,
                    type: 'text',
                    placeholder: 'Score',
                    title: 'Type a value, a range like 70-90, or >=80',
                    hint: 'e.g. 70-90 or >=80',
                    span: 'w-24 shrink-0'
                },
                {
                    index: 11,
                    type: 'select',
                    placeholder: 'Step',
                    span: 'w-36 shrink-0'
                    // options: stepLabelMap
                }
            ];

            const $filters = $('#applicantsFilters');
            const filterFieldClass = 'app-filter-field w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200';

            columnFilters.forEach(col => {
                let $el;
                const $wrap = $(`<div class="flex flex-col gap-1 ${col.span || 'flex-1 min-w-[140px]'}"></div>`);
                const $label = $(`<label class="text-sm font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">${col.placeholder}</label>`);
                $wrap.append($label);

                if (col.type === 'select') {
                    $el = $(`
        <select id="filterStep"
            class="${filterFieldClass}">
            <option value="">All Step</option >
        </select>
    `);

                    if (col.options) {
                        Object.entries(col.options).forEach(([val, label]) => {
                            $el.append(`<option value="${val}">${label}</option>`);
                        });
                    }

                    $wrap.append($el);
                    $filters.append($wrap);

                    $el.select2({
                        placeholder: 'All Step',
                        width: '100%',
                        allowClear: true,
                        dropdownCssClass: 'jobapp-select2-dropdown'
                    });

                    $el.on('change', function() {
                        applicantTable
                            .column(col.index)
                            .search(this.value || '')
                            .draw();
                    });

                } else if (col.type === 'select2tags') {
                    $el = $(`<select id="filterCompany" class="${filterFieldClass}"></select>`);

                    $wrap.append($el);
                    $filters.append($wrap);

                    $el.select2({
                        tags: true,
                        placeholder: `Search ${col.placeholder}`,
                        width: '100%',
                        allowClear: true,
                        multiple: false,
                        dropdownCssClass: 'jobapp-select2-dropdown'
                    });

                    $el.on('change', function() {
                        applicantTable
                            .column(col.index)
                            .search(this.value || '')
                            .draw();
                    });

                } else {
                    $el = $(`
            <input type="text"
                 class="${filterFieldClass}"
                placeholder="${col.placeholder}"${col.title ? ` title="${col.title}"` : ''}>
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

                    $wrap.append($el);

                    if (col.hint) {
                        $wrap.append(`<p class="text-sm text-slate-400 dark:text-slate-500">${col.hint}</p>`);
                    }

                    $filters.append($wrap);
                }
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
                        targets: [3, 4, 5, 6, 7, 8, 9, 10, 11, 12], // less important columns
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
                            return `<a href="/showcareers/${row.eid}" target="_blank" rel="noopener noreferrer" class='inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-gray-600 hover:bg-gray-700'>${data}</a>`;
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
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'whitespace-normal break-words',
                        render: function(data, type, row) {
                            const jobTL = [row.job_title, row.job_level].filter(Boolean).join(' — ');
                            return jobTL || '<span class="text-gray-400">—</span>';
                        }
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
                        className: 'step-col',
                        render: function(data) {
                            const label = stepLabelMap[data] || data;
                            return `<span class="inline-flex justify-center items-center w-[120px] bg-blue-300/30 text-blue-600 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-semibold px-3 py-1.5 text-center rounded whitespace-normal break-words"> ${label} </span>`;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            let label, cls;
                            if (row.status === 'T') {
                                label = 'Transfer';
                                cls = 'bg-violet-300/30 text-violet-600 dark:bg-violet-500/20 dark:text-violet-300';
                            } else if (row.status === 'R') {
                                label = 'Reject';
                                cls = 'bg-red-300/30 text-red-600 dark:bg-red-500/20 dark:text-red-300';
                            } else if (row.status === 'C') {
                                label = 'Approved';
                                cls = 'bg-green-300/30 text-green-600 dark:bg-green-500/20 dark:text-green-300';
                            } else if (row.is_read === 'N') {
                                label = 'Unchecked';
                                cls = 'bg-blue-300/30 text-blue-600 dark:bg-blue-500/20 dark:text-blue-300';
                            } else {
                                label = 'Checked';
                                cls = 'bg-gray-300/30 text-gray-600 dark:bg-gray-500/20 dark:text-gray-300';
                            }
                            return `<span class="inline-flex justify-center items-center px-3 py-1.5 text-sm font-semibold text-center rounded ${cls}">${label}</span>`;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (!canRemap) return `<span class="text-sm text-gray-400">—</span>`;
                            if (row.status === 'R' || row.status === 'C') {
                                return `<span class="text-sm text-gray-400">—</span>`;
                            }
                            return `<button class="remap-btn inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium rounded-md bg-indigo-50 text-indigo-600 hover:bg-indigo-100 border border-indigo-200"
                                data-id="${row.eid}" data-docid="${row.docid}"
                                data-job-id="${row.docidposting}" data-job-title="${row.job_title || ''}"
                                data-company="${row.posting_cpnyid || ''}" data-division="${row.division_name || ''}" data-department="${row.department_name || ''}">
                                🔄 Remap
                            </button>`;
                        }
                    }
                ],
                rowCallback: function(row, data) {
                    // reset dulu — pakai custom class (lihat <style> di atas), reaktif ke toggle .dark tanpa redraw
                    $(row).removeClass('row-checked row-unchecked row-reject');

                    if (data.status === 'R') {
                        $(row).addClass('row-reject');
                    } else if (data.is_read === 'N') {
                        $(row).addClass('row-unchecked');
                    } else {
                        $(row).addClass('row-checked');
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
            $('#applicantsTable thead tr:eq(0) th').eq(6).addClass('small-col');
            $('#applicantsTable thead tr:eq(0) th').eq(7).addClass('small-col');
            $('#applicantsTable thead tr:eq(0) th').eq(9).addClass('small-col');

            if ($('#filterJobTL').length) {
                $('#filterJobTL').select2({
                    placeholder: 'Filter by Job Title — Job Level',
                    allowClear: true,
                    width: 'resolve',
                    dropdownCssClass: 'jobapp-select2-dropdown',
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

            $('#btnResetFilters').on('click', function() {
                $('#applicantsFilters input').val('');
                $('#applicantsFilters select').val('').trigger('change.select2');

                if ($('#filterJobTL').length && $('#filterJobTL').val()) {
                    $('#filterJobTL').val(null).trigger('change.select2');
                    applicantTable.ajax.reload();
                }

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
                            const matchedByBadge = `<span class="inline-block rounded bg-amber-200/60 px-2 py-0.5 text-sm font-semibold text-amber-800 dark:bg-amber-300 dark:text-amber-900">${r.matched_by || 'KTP + DOB'}</span>`;

                            return `<tr class="${isThisRow ? 'bg-indigo-50 dark:bg-indigo-900/20' : ''}">
                                <td class="px-4 py-3">
                                    <a href="/showcareers/${r.eid}" class="inline-flex justify-center items-center px-3 py-1.5 text-sm font-semibold text-white rounded bg-gray-600 hover:bg-gray-700">View</a>
                                </td>
                                <td class="px-4 py-3">${matchedByBadge}</td>
                                <td class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">${r.docid}${isThisRow ? ' <span class="ml-1 text-sm font-bold text-indigo-500">(this)</span>' : ''}</td>
                                <td class="px-4 py-3">${r.full_name || '—'}</td>
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
                                return `<a href="/showcareers/${data}" class="inline-flex justify-center items-center px-3 py-1.5 text-sm font-semibold text-white rounded bg-gray-600 hover:bg-gray-700">View</a>`;
                            }
                        },
                        {
                            data: 'matched_by',
                            orderable: false,
                            searchable: false,
                            render: function(data) {
                                return `<span class="mr-1 inline-block rounded bg-amber-200/60 px-2 py-0.5 text-sm font-semibold text-amber-800 dark:bg-amber-300 dark:text-amber-900">${data || 'KTP + DOB'}</span>`;
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
