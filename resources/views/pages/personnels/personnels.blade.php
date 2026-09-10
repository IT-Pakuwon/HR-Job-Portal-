<x-app-layout>
    @php
        $isHcbp = auth()->user()->hasRole('HCBPACCESS');
        $isSby = ($group_cpny_id ?? '') === 'SBY';

        $xlCols = 5; // All, On Progress, Reject, Revise, Completed

        if ($isSby) {
            $xlCols++; // tambah 1 untuk Draft (Save as Draft / Copy Template khusus SBY)
        }

        if ($isHcbp) {
            $xlCols++; // tambah 1 untuk HCBP All
        }
    @endphp
    <div class="max-w-9xl mx-auto w-full p-2">
        {{-- <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5"> --}}

            <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-{{ $xlCols }}">

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

            {{-- On Progress --}}
            <a href="#" class="status-filter group block h-full" data-status="P">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⏳</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">On Progress</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $onProgress }}</p>
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

            {{-- Revise --}}
            <a href="#" class="status-filter group block h-full" data-status="D">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✏️</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Revise</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $revise }}</p>
                </div>
            </a>

            {{-- Draft --}}
            @if($isSby)
            <a href="#" class="status-filter group block h-full" data-status="H">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-yellow-700 bg-yellow-200/20 p-3 text-yellow-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-yellow-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📝</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Draft</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $draft }}</p>
                </div>
            </a>
            @endif

            {{-- Completed --}}
            <a href="#" class="status-filter group block h-full" data-status="C">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✅</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Completed</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $completed }}</p>
                </div>
            </a>
            @if($isHcbp)
            <a href="#" class="status-filter group block h-full" data-hcbp="1">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🌐</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">HCBP All</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $hcbpAll }}</p>
                </div>
            </a>
            @endif
        </div>
        <div
            class="mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div
                class="flex flex-row items-start justify-between gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] sm:flex-row sm:items-center">
                <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">Personnel
                    Requisition Form</h2>
                <div class="flex flex-row items-center gap-2">
                    @if(auth()->user()->hasRole('HCBPACCESS'))
                    <div class="flex items-center gap-2" id="hcbpFilters" style="display:none;">

                        <select id="filterStatus" class="border rounded px-3 py-2 text-sm">
                            <option value="">All Status</option>
                            <option value="P">On Progress</option>
                            <option value="R">Reject</option>
                            <option value="D">Revise</option>
                            <option value="C">Completed</option>
                            @if($isSby)
                            <option value="H">Draft</option>
                            @endif
                        </select>

                        <select id="filterDept" class="border rounded px-3 py-2 text-sm">
                            <option value="">All Department</option>
                            @foreach($departments ?? [] as $dept)
                                <option value="{{ $dept->department_id }}">{{ $dept->department_name }}</option>
                            @endforeach
                        </select>

                        {{-- APPLY --}}
                        <button id="applyFilter"
                            class="bg-indigo-600 text-white px-3 py-2 rounded text-sm hover:bg-indigo-700">
                            Apply
                        </button>

                        {{-- RESET --}}
                        <button id="resetFilter"
                            class="bg-gray-500 text-white px-3 py-2 rounded text-sm hover:bg-gray-600">
                            Reset
                        </button>

                    </div>
                    @endif
                    <a href="{{ url('/createpersonnels') }}"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                        <i class="fas fa-plus pr-2"></i>Create
                    </a>
                </div>

            </div>

            @php $hasAllDeptAccessFlag = $hasAllDeptAccess ?? false; @endphp
            <div id="allDeptFilterWrap" class="pill-filter-bar {{ $hasAllDeptAccessFlag ? '' : 'hidden' }} border-b border-gray-100 px-5 py-3 dark:border-white/[0.06]">
                <div class="flex flex-1 flex-col rounded-2xl border border-slate-200 bg-slate-50/60 shadow-sm sm:flex-row sm:items-stretch dark:border-slate-700/60 dark:bg-slate-800/40">
                    <div class="flex flex-1 flex-wrap items-stretch divide-y divide-slate-200 sm:flex-nowrap sm:divide-x sm:divide-y-0 dark:divide-slate-700/60">
                        @if($hasAllDeptAccessFlag)
                        <div class="flex flex-1 min-w-[180px] items-center gap-2 px-3 py-2">
                            <svg class="h-4 w-4 shrink-0 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                            </svg>
                            <select id="filterCompanyAllDept" class="w-full min-w-0 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-200">
                                <option value="">All Company</option>
                                @foreach($filterCompanies as $company)
                                    <option value="{{ $company->cpny_id }}">{{ $company->cpny_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-1 min-w-[180px] items-center gap-2 px-3 py-2">
                            <svg class="h-4 w-4 shrink-0 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <select id="filterDivisionAllDept" class="w-full min-w-0 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-200">
                                <option value="">All Division</option>
                                @foreach($filterDivisions as $division)
                                    <option value="{{ $division->division_id }}">{{ $division->division_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-1 min-w-[180px] items-center gap-2 px-3 py-2">
                            <svg class="h-4 w-4 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            <select id="filterDepartmentAllDept" class="w-full min-w-0 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-200">
                                <option value="">All Department</option>
                                @foreach($filterDepartments as $department)
                                    <option value="{{ $department->department_id }}" data-division="{{ $department->division_id }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        {{-- Job Posting Status — only relevant (and shown) while the Completed tab is active --}}
                        <div id="jobPostingStatusSegment" class="hidden flex-1 min-w-[180px] items-center gap-2 px-3 py-2">
                            <svg class="h-4 w-4 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <select id="filterJobPostingStatus" class="w-full min-w-0 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-200">
                                <option value="">All Job Posting Status</option>
                                <option value="P">Posted</option>
                                <option value="C">Closed</option>
                                <option value="U">Unposted</option>
                                <option value="H">Hold</option>
                                <option value="X">Cancelled</option>
                                <option value="NOTPOSTED">Not Posted</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center justify-end border-t border-slate-200 px-2 py-2 sm:border-l sm:border-t-0 dark:border-slate-700/60">
                        <button type="button" id="resetAllDeptFilters"
                            class="flex items-center gap-1.5 whitespace-nowrap rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-500 transition hover:bg-slate-200/70 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-700/60 dark:hover:text-slate-200">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" />
                            </svg>
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <style>
                #jobPostingStatusSegment { display: flex; }
                #jobPostingStatusSegment.hidden { display: none; }
                .pill-filter-bar .select2-container { width: 100% !important; }
                .pill-filter-bar .select2-selection--single {
                    height: 28px; display: flex; align-items: center;
                    border: none !important; background: transparent !important;
                    padding: 0 1.25rem 0 0; font-size: 0.75rem;
                }
                .pill-filter-bar .select2-container--open .select2-selection--single { box-shadow: none; }
                .pill-filter-bar .select2-selection__rendered {
                    padding: 0; line-height: 28px; color: #334155; font-weight: 600;
                    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
                }
                .pill-filter-bar .select2-selection__arrow { height: 28px; right: 0; }
                .pill-filter-bar .select2-selection__arrow b { border-color: #94a3b8 transparent transparent transparent; }
                .pill-filter-bar .select2-selection--single .select2-selection__clear {
                    position: absolute !important; right: 18px !important;
                    top: 50% !important; transform: translateY(-50%) !important;
                    margin: 0 !important; font-size: 15px !important;
                    font-weight: 400 !important; color: #94a3b8 !important;
                    z-index: 1;
                }
                .pill-filter-bar .select2-selection--single .select2-selection__clear:hover { color: #ef4444 !important; }
                .pill-filter-bar .select2-dropdown {
                    border-radius: 0.75rem; border: 1px solid #e2e8f0;
                    overflow: hidden; margin-top: 4px;
                    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.05);
                }
                .pill-filter-bar .select2-search__field {
                    border-radius: 0.5rem; border: 1px solid #e2e8f0;
                    padding: 0.375rem 0.5rem; font-size: 0.75rem; outline: none;
                }
                .pill-filter-bar .select2-search__field:focus {
                    border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
                }
                .pill-filter-bar .select2-results__option { font-size: 0.75rem; padding: 0.5rem 0.75rem; transition: background .1s; }
                .pill-filter-bar .select2-results__option--highlighted[aria-selected] { background-color: #EEF2FF; color: #4338CA; }
                .pill-filter-bar .select2-results__option[aria-selected="true"] { background-color: #E0E7FF; color: #3730A3; font-weight: 600; }

                .dark .pill-filter-bar .select2-selection__rendered { color: #e2e8f0; }
                .dark .pill-filter-bar .select2-selection__arrow b { border-color: #64748b transparent transparent transparent; }
                .dark .pill-filter-bar .select2-dropdown { background-color: #1e293b; border-color: #475569; }
                .dark .pill-filter-bar .select2-search__field { background-color: #334155; border-color: #475569; color: #e2e8f0; }
                .dark .pill-filter-bar .select2-results__option { color: #e2e8f0; }
                .dark .pill-filter-bar .select2-results__option--highlighted[aria-selected] { background-color: #4338CA; color: #fff; }
                .dark .pill-filter-bar .select2-results__option[aria-selected="true"] { background-color: #3730A3; color: #fff; }
                .dark .pill-filter-bar .select2-search__field:focus { border-color: #6366f1; }
            </style>

            <div class="relative overflow-hidden">
                <table id="personnelsTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th scope="col" class="w-32 px-4 py-3 text-left font-medium">
                                DocID
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-left font-medium">
                                Date
                            </th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">
                                Company
                            </th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">
                                Division
                            </th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">
                                Department
                            </th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">
                                Title
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-left font-medium">
                                Level
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-left font-medium">
                                User
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-left font-medium">
                                Status
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-left font-medium">
                                Job Posting Status
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-left font-medium">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        var currentUser = "{{ auth()->user()->username }}";
        var personnelsTable;
        $(document).ready(function() {
            const hasAllDeptAccess = @json($hasAllDeptAccess ?? false);

            function appendAllDeptFilters(url) {
                let finalUrl = url;
                if (hasAllDeptAccess) {
                    const separator = finalUrl.includes('?') ? '&' : '?';
                    finalUrl += separator
                        + 'company=' + encodeURIComponent($('#filterCompanyAllDept').val() || '')
                        + '&division=' + encodeURIComponent($('#filterDivisionAllDept').val() || '')
                        + '&department=' + encodeURIComponent($('#filterDepartmentAllDept').val() || '');
                }
                const jpSeparator = finalUrl.includes('?') ? '&' : '?';
                finalUrl += jpSeparator + 'jobposting_status=' + encodeURIComponent($('#filterJobPostingStatus').val() || '');
                return finalUrl;
            }

            function reloadAllDeptFilters() {
                if (!personnelsTable) return;
                const currentUrl = new URL(personnelsTable.ajax.url(), window.location.origin);
                if (hasAllDeptAccess) {
                    currentUrl.searchParams.set('company', $('#filterCompanyAllDept').val() || '');
                    currentUrl.searchParams.set('division', $('#filterDivisionAllDept').val() || '');
                    currentUrl.searchParams.set('department', $('#filterDepartmentAllDept').val() || '');
                }
                currentUrl.searchParams.set('jobposting_status', $('#filterJobPostingStatus').val() || '');
                personnelsTable.ajax.url(currentUrl.pathname + currentUrl.search).load();
            }

            // Job Posting Status filter only makes sense on the Completed tab —
            // shown/hidden by the status-tab click handler further below.
            $('#filterJobPostingStatus').select2({
                placeholder: 'All Job Posting Status',
                width: '100%',
                allowClear: true,
                dropdownParent: $('#allDeptFilterWrap')
            });
            $('#filterJobPostingStatus').on('change', reloadAllDeptFilters);

            function setJobPostingFilterVisible(status) {
                const showJobPosting = status === 'C';
                $('#jobPostingStatusSegment').toggleClass('hidden', !showJobPosting);
                if (!showJobPosting) {
                    $('#filterJobPostingStatus').val('').trigger('change.select2');
                }
                // The whole filter row only needs to be shown for non-AllDept users
                // when the Job Posting Status filter itself is visible.
                $('#allDeptFilterWrap').toggleClass('hidden', !(hasAllDeptAccess || showJobPosting));
            }

            if (hasAllDeptAccess) {
                $('#filterCompanyAllDept').select2({
                    placeholder: 'All Company',
                    width: '100%',
                    allowClear: true,
                    dropdownParent: $('#allDeptFilterWrap')
                });
                $('#filterDivisionAllDept').select2({
                    placeholder: 'All Division',
                    width: '100%',
                    allowClear: true,
                    dropdownParent: $('#allDeptFilterWrap')
                });
                $('#filterDepartmentAllDept').select2({
                    placeholder: 'All Department',
                    width: '100%',
                    allowClear: true,
                    dropdownParent: $('#allDeptFilterWrap')
                });

                // cache the full, group-scoped department list once so the division
                // filter can rebuild the dropdown client-side without another request
                const allDeptOptions = $('#filterDepartmentAllDept option[value!=""]').map(function() {
                    return {
                        value: $(this).val(),
                        text: $(this).text(),
                        division: $(this).data('division')
                    };
                }).get();

                function rebuildDeptOptions(divisionId) {
                    const $dept = $('#filterDepartmentAllDept');
                    $dept.empty().append('<option value="">All Department</option>');
                    allDeptOptions
                        .filter(o => !divisionId || String(o.division) == String(divisionId))
                        .forEach(o => {
                            $dept.append(`<option value="${o.value}" data-division="${o.division}">${o.text}</option>`);
                        });
                    $dept.val('').trigger('change');
                }

                $('#filterDivisionAllDept').on('change', function() {
                    rebuildDeptOptions($(this).val());
                    reloadAllDeptFilters();
                });

                $('#filterCompanyAllDept, #filterDepartmentAllDept').on('change', reloadAllDeptFilters);
            }

            $('#resetAllDeptFilters').on('click', function() {
                if (hasAllDeptAccess) {
                    rebuildDeptOptions('');
                    $('#filterCompanyAllDept, #filterDivisionAllDept').val('').trigger('change.select2');
                }
                $('#filterJobPostingStatus').val('').trigger('change.select2');
                reloadAllDeptFilters();
            });
            function toggleActionColumn(table, data) {

                let hasToggle = data.some(r =>
                    r.can_toggle &&
                    r.jobposting_status &&
                    r.status === 'C'
                );

                table.column(11).visible(hasToggle);
            }

            // Hanya inisialisasi tabel personnelsTable
            personnelsTable = $('#personnelsTable').DataTable({
                ajax: appendAllDeptFilters("{{ route('personnels.json') }}?status=P"),
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
                        title: 'list_PO',
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
                        title: 'list_PO',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],

                // 🔥 INIT
                initComplete: function(settings, json) {
                    let table = this.api();
                    toggleActionColumn(table, json.data);
                },

                // 🔥 RELOAD / FILTER / DRAW
                drawCallback: function(settings) {
                    let table = this.api();
                    let data = table.rows().data().toArray();
                    toggleActionColumn(table, data);
                },



                // order: [1, 'asc'],
                ordering: false,

                rowGroup: {
                    dataSrc: 'cpnyid', // Kelompokkan berdasarkan kolom 'cpnyid'
                    startRender: function(rows, group) {
                        // Cek apakah semua baris dalam grup saat ini tersembunyi (collapsed)
                        let isCollapsed = rows.nodes().to$().filter('.collapsed-group-row').length ===
                            rows.count();
                        let icon = isCollapsed ? '<i class="fas fa-plus-circle"></i>' :
                            '<i class="fas fa-minus-circle"></i>';

                        // Mengembalikan baris grup dengan ikon dan jumlah catatan
                        return $('<tr/>')
                            .append('<td colspan="' + rows.columns().count() + '">' + icon + ' ' +
                                group + ' (' + rows.count() + ' records)</td>')
                            .attr('data-group', group)
                            .addClass('group-row');
                    }
                },
                columns: [{
                        data: null,
                        defaultContent: ''
                    }, // DTR control
                    {
                        data: 'eid',
                        render: function(data, type, row) {
                            const showUrl = `/showpersonnels/${row.eid}`;
                            let mainUrl = showUrl;
                            let buttonClass =
                                'inline-flex justify-center items-center min-w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-gray-600 hover:bg-gray-700';
                            const buttonText = row.docid;

                            const isReviseOwner = (row.status === 'D' || row.status === 'H') && row.created_user === currentUser;

                            if (isReviseOwner) {
                                mainUrl = `/editpersonnels/${row.eid}`;
                                buttonClass =
                                    'inline-flex justify-center items-center min-w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-yellow-500 hover:bg-yellow-700';
                            }

                            const canCopyTemplate = row.status === 'C' && row.group_cpny_id === 'SBY';
                            const copyBtnHtml = canCopyTemplate ? `
                                <button type="button" class="copyTemplateBtn inline-flex h-9 w-9 items-center justify-center rounded bg-teal-500 text-white transition-colors duration-200 hover:bg-teal-600"
                                    title="Copy Template" data-eid="${row.eid}">
                                    <i class="fas fa-copy text-sm"></i>
                                </button>
                            ` : '';

                            if (isReviseOwner) {
                                return `
                                    <div class="flex items-center gap-2">
                                        <a href="${mainUrl}" class="${buttonClass}">
                                            ${buttonText}
                                        </a>

                                        <a href="${showUrl}"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded bg-sky-500 text-white transition-colors duration-200 hover:bg-sky-600"
                                        title="View">
                                            <i class="fas fa-eye text-sm"></i>
                                        </a>
                                        ${copyBtnHtml}
                                    </div>
                                `;
                            }

                            return `
                                <div class="flex items-center gap-2">
                                    <a href="${mainUrl}" class="${buttonClass}">
                                        ${buttonText}
                                    </a>
                                    ${copyBtnHtml}
                                </div>
                            `;
                        }
                    },
                    // {

                    //     data: 'eid',
                    //     render: function(data, type, row) {
                    //         let url = `/showpersonnels/${row.eid}`;
                    //         let buttonClass =
                    //             'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200  bg-gray-600 hover:bg-gray-700 ';
                    //         let buttonText = row.docid; // Menggunakan row.docid untuk teks tombol

                    //         // Cek apakah user yang login sama dengan created_user dan status = D (Revise/Draft)
                    //         if (row.status === 'D' && row.created_user === currentUser) {
                    //             url = `/editpersonnels/${row.eid}`;
                    //             buttonClass =
                    //                 'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-yellow-500 hover:bg-yellow-700';
                    //         }

                    //         return `<a href="${url}" class="px-3 py-1 ${buttonClass} text-white rounded">${buttonText}</a>`;
                    //     }
                    // },
                    {
                        data: 'date',
                        className: 'no-pointer'
                    },
                    {
                        data: 'cpnyid',
                        className: 'no-pointer'
                    },
                    {
                        data: 'division_id',
                        className: 'no-pointer'
                    },
                    {
                        data: 'departementid',
                        className: 'no-pointer'
                    },
                    {
                        data: 'job_title',
                        className: 'no-pointer'
                    },
                    {
                        data: 'job_level',
                        className: 'no-pointer'
                    },
                    {
                        data: 'created_user',
                        className: 'no-pointer'
                    },
                    {
                        data: 'status',
                        className: 'no-pointer',
                        render: function(data) {
                            let statusText = "";
                            let badgeClass = "";

                            if (data === 'D') {
                                statusText = "Revise";
                                badgeClass =
                                    "w-32 bg-amber-200/60 text-amber-800 dark:bg-amber-300/40 dark:text-amber-900 pointer-events-none border border-amber-600/40 font-semibold px-4 py-2 text-center rounded";
                            } else if (data === 'P') {
                                statusText = "On Progress";
                                badgeClass =
                                    "w-32 bg-orange-200/60 text-orange-800 dark:bg-orange-300/40 dark:text-orange-900 pointer-events-none border border-orange-600/40 font-semibold px-4 py-2 text-center rounded";
                            } else if (data === 'C') {
                                statusText = "Completed";
                                badgeClass =
                                    "w-32 bg-green-200/60 text-green-800 dark:bg-green-300/40 dark:text-green-900 pointer-events-none border border-green-600/40 font-semibold px-4 py-2 text-center rounded";
                            } else if (data === 'H') {
                                statusText = "Draft";
                                badgeClass =
                                    "w-32 bg-slate-200/60 text-slate-800 dark:bg-slate-300/40 dark:text-slate-900 pointer-events-none border border-slate-600/40 font-semibold px-4 py-2 text-center rounded";
                            } else if (data === 'X') {
                                statusText = "Cancel";
                                badgeClass =
                                    "w-32 bg-red-200/60 text-red-800 dark:bg-red-300/40 dark:text-red-900 pointer-events-none border border-red-600/40 font-semibold px-4 py-2 text-center rounded";
                            } else if (data === 'R') {
                                statusText = "Rejected";
                                badgeClass =
                                    "w-32 bg-red-200/60 text-red-800 dark:bg-red-300/40 dark:text-red-900 pointer-events-none border border-red-600/40 font-semibold px-4 py-2 text-center rounded";
                            } else {
                                statusText = data ?? "-";
                                badgeClass =
                                    "w-32 bg-gray-200/60 text-gray-700 dark:bg-gray-300/40 dark:text-gray-900 pointer-events-none border border-gray-500/40 font-semibold px-4 py-2 text-center rounded";
                            }

                            return `<span class="${badgeClass}">${statusText}</span>`;
                        }
                    },
                    {
                        data: 'jobposting_status',
                        render: function(data, type, row) {

                            let text = '';
                            let cls = '';

                           if (!data) {
                                text = 'Not Posted';
                                cls = 'bg-gray-200 text-gray-700';
                            } else if (data === 'U') {
                                text = 'Unposted';
                                cls = 'bg-gray-200 text-gray-600';
                            } else if (data === 'P') {
                                text = 'Posted';
                                cls = 'bg-blue-200 text-blue-800';
                            } else if (data === 'C') {
                                text = 'Closed';
                                cls = 'bg-green-200 text-green-800';
                            } else if (data === 'X') {
                                text = 'Cancelled';
                                cls = 'bg-red-200 text-red-800';
                            } else if (data === 'H') {
                                text = 'Hold';
                                cls = 'bg-orange-200 text-orange-800';
                                const reason = row.jobposting_reason ?? null;
                                const reasonIcon = reason
                                    ? ` <span class="jp-reason-icon cursor-pointer ml-1 align-middle" title="${reason}" data-reason="${reason}">ℹ️</span>`
                                    : '';
                                return `<span class="px-2 py-1 rounded ${cls}">${text}${reasonIcon}</span>`;
                            } else {
                                text = data;
                                cls = 'bg-gray-200 text-gray-700';
                            }
                            return `<span class="px-2 py-1 rounded ${cls}">${text}</span>`;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {

                            if (!row.can_toggle) return `<span class="text-gray-300 text-xs">-</span>`;
                            if (!row.jobposting_status) return `<span class="text-gray-400 text-sm">-</span>`;
                            if (row.status !== 'C') return `<span class="text-gray-300 text-xs">-</span>`;
                            if (row.jobposting_status === 'X') return `<span class="text-red-500 text-xs font-semibold">Cancelled</span>`;

                            // ❌ CANCELLED → no action (terminal)
                            if (row.jobposting_status === 'X') {
                                return `<span class="text-red-500 text-xs font-semibold">Cancelled</span>`;
                            }

                            const s = row.jobposting_status;
                            const id = row.docid;

                            const actionMap = {
                                U: [
                                    { label: '📢 Post',    action: 'post',   cls: 'text-blue-700'  },
                                    { label: '🔒 Close',   action: 'close',  cls: 'text-red-700'   },
                                    { label: '⏸ Hold',    action: 'hold',   cls: 'text-amber-700' },
                                    { label: '✖ Cancel',  action: 'cancel', cls: 'text-gray-600'  },
                                ],
                                P: [
                                    { label: '🔒 Close',   action: 'close',  cls: 'text-red-700'   },
                                    { label: '⏸ Hold',    action: 'hold',   cls: 'text-amber-700' },
                                    { label: '📥 Unpost', action: 'unpost', cls: 'text-yellow-700' },
                                    { label: '✖ Cancel',  action: 'cancel', cls: 'text-gray-600'  },
                                ],
                                C: [
                                    { label: '🔓 Reopen',  action: 'reopen', cls: 'text-green-700' },
                                    { label: '📥 Unpost', action: 'unpost', cls: 'text-yellow-700' },
                                    { label: '✖ Cancel',  action: 'cancel', cls: 'text-gray-600'  },
                                ],
                                H: [
                                    { label: '🔄 Open',    action: 'open',   cls: 'text-blue-700'  },
                                    { label: '📥 Unpost', action: 'unpost', cls: 'text-yellow-700' },
                                ],
                            };

                            const items = actionMap[s] ?? [];
                            if (!items.length) return `<span class="text-gray-300 text-xs">-</span>`;

                            const menuItems = items.map(i =>
                                `<button class="jp-action-item w-full text-left px-4 py-2 text-xs hover:bg-gray-100 ${i.cls}"
                                    data-docid="${id}" data-status="${s}" data-action="${i.action}">${i.label}</button>`
                            ).join('');

                            return `
                                <div class="jp-dropdown inline-block text-left">
                                    <button class="jp-dropdown-toggle inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 transition border border-gray-300 dark:bg-gray-900 dark:hover:bg-gray-700 dark:text-gray-300 dark:border-gray-700"
                                        data-docid="${id}">
                                        Action ▾
                                    </button>
                                </div>
                            `;
                        }
                    }
                ]
            });

                // Buat satu elemen menu fixed yang di-share semua baris
                const $jpMenu = $(`
                    <div id="jp-fixed-menu" class="hidden fixed z-[9999] w-40 rounded-md shadow-lg bg-white border border-gray-200 py-1 dark:bg-gray-800 dark:border-gray-700">
                    </div>
                `).appendTo('body');

                const actionLabels = {
                    post:   { label: '📢 Post',    cls: 'text-blue-700'   },
                    close:  { label: '🔒 Close',   cls: 'text-red-700'    },
                    hold:   { label: '⏸ Hold',    cls: 'text-amber-700'  },
                    unpost: { label: '📥 Unpost',  cls: 'text-yellow-700' },
                    cancel: { label: '✖ Cancel',  cls: 'text-gray-600'   },
                    reopen: { label: '🔓 Reopen',  cls: 'text-green-700'  },
                    open:   { label: '🔄 Open',    cls: 'text-blue-700'   },
                };

                const statusActions = {
                    U: ['post', 'close', 'hold', 'cancel'],
                    P: ['close', 'hold', 'unpost', 'cancel'],
                    C: ['reopen', 'unpost', 'cancel'],
                    H: ['open', 'unpost'],
                };

                // Toggle dropdown open/close
                $('#personnelsTable').on('click', '.jp-dropdown-toggle', function(e) {
                    e.stopPropagation();

                    const btn      = $(this);
                    const docid    = btn.data('docid');
                    const jpStatus = btn.closest('tr').find('[data-jp-status]').data('jp-status')
                                  || btn.closest('td').prev('[data-jp-status]').data('jp-status')
                                  || btn.closest('tr').find('.jp-status-cell').data('jp-status');

                    // Ambil status dari data yg sudah di-render di kolom Job Posting Status
                    const rowData  = personnelsTable.row(btn.closest('tr')).data();
                    const s        = rowData ? rowData.jobposting_status : null;

                    if (!s || !statusActions[s]) {
                        $jpMenu.addClass('hidden');
                        return;
                    }

                    // Build menu items
                    const items = statusActions[s].map(action => {
                        const cfg = actionLabels[action];
                        return `<button class="jp-action-item w-full text-left px-4 py-2 text-xs hover:bg-gray-100 ${cfg.cls}"
                            data-docid="${docid}" data-status="${s}" data-action="${action}">${cfg.label}</button>`;
                    }).join('');

                    $jpMenu.html(items);

                    // Posisi fixed berdasarkan koordinat tombol
                    const rect = this.getBoundingClientRect();
                    $jpMenu.css({
                        top:  rect.bottom + window.scrollY,
                        left: rect.right - 160 + window.scrollX,
                    });

                    const isVisible = !$jpMenu.hasClass('hidden');
                    $jpMenu.toggleClass('hidden', isVisible);
                });

                // Show hold reason on click
                $('#personnelsTable').on('click', '.jp-reason-icon', function(e) {
                    e.stopPropagation();
                    const reason = $(this).data('reason');
                    Swal.fire({
                        title: 'Hold Reason',
                        text: reason,
                        icon: 'info',
                        confirmButtonColor: '#f59e0b',
                    });
                });

                // Copy Template (Completed PRF -> new draft)
                $('#personnelsTable').on('click', '.copyTemplateBtn', function(e) {
                    e.stopPropagation();
                    e.preventDefault();

                    const $btn = $(this);
                    const eid = $btn.data('eid');

                    Swal.fire({
                        title: 'Copy this PRF?',
                        text: 'A new draft PRF will be created with all fields copied from this one.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Copy',
                        confirmButtonColor: '#0d9488',
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        $btn.prop('disabled', true);

                        $.ajax({
                            url: `/personnels/${eid}/copy`,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    toastr.success('Draft PRF created from template.');
                                    window.location.href = `/editpersonnels/${response.hash}`;
                                } else {
                                    toastr.error(response.message || 'Failed to copy PRF.');
                                    $btn.prop('disabled', false);
                                }
                            },
                            error: function(xhr) {
                                toastr.error(xhr.responseJSON?.message || 'Failed to copy PRF.');
                                $btn.prop('disabled', false);
                            }
                        });
                    });
                });

                // Close dropdown saat klik di luar
                $(document).on('click', function() {
                    $jpMenu.addClass('hidden');
                });

                // Handle pilihan dari dropdown
                $(document).on('click', '.jp-action-item', function(e) {
                    e.stopPropagation();
                    $jpMenu.addClass('hidden');

                    const btn    = $(this);
                    const docid  = btn.data('docid');
                    const status = btn.data('status');
                    const action = btn.data('action');

                    const confirmMap = {
                        post:   { title: 'Post Job Posting',    text: 'Publish this job posting?',              confirmText: 'Yes, Post',    color: '#2563eb', targetStatus: 'P', successText: 'Posted'    },
                        close:  { title: 'Close Job Posting',   text: 'Close this job posting?',                confirmText: 'Yes, Close',   color: '#dc2626', targetStatus: 'C', successText: 'Closed'    },
                        reopen: { title: 'Reopen Job Posting',  text: 'Reopen this job posting?',               confirmText: 'Yes, Reopen',  color: '#16a34a', targetStatus: 'P', successText: 'Reopened'  },
                        open:   { title: 'Open Job Posting',    text: 'Post this job again?',                   confirmText: 'Yes, Open',    color: '#2563eb', targetStatus: 'P', successText: 'Opened'    },
                        unpost: { title: 'Unpost Job Posting',  text: 'Move back to Unposted?',                 confirmText: 'Yes, Unpost',  color: '#ca8a04', targetStatus: 'U', successText: 'Unposted'  },
                        cancel: { title: 'Cancel Job Posting',  text: 'You are about to cancel this posting.',  confirmText: 'Yes, Cancel',  color: '#6b7280', targetStatus: 'X', successText: 'Cancelled' },
                    };

                    if (action === 'hold') {
                        Swal.fire({
                            title: 'Reason for Hold',
                            input: 'textarea',
                            inputPlaceholder: 'Enter reason...',
                            inputAttributes: { 'aria-label': 'Reason' },
                            showCancelButton: true,
                            confirmButtonText: 'Submit',
                            confirmButtonColor: '#f59e0b',
                            preConfirm: (value) => {
                                if (!value) Swal.showValidationMessage('Reason is required');
                                return value;
                            }
                        }).then((res) => {
                            if (res.isConfirmed) processStatus(docid, 'H', 'Put on Hold', btn, res.value);
                        });
                        return;
                    }

                    const cfg = confirmMap[action];
                    if (!cfg) return;

                    Swal.fire({
                        title: cfg.title,
                        text: cfg.text,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: cfg.confirmText,
                        confirmButtonColor: cfg.color,
                    }).then((result) => {
                        if (result.isConfirmed) processStatus(docid, cfg.targetStatus, cfg.successText, btn);
                    });
                });

            // Event listener untuk klik pada baris grup (collapse/expand) untuk personnelsTable
            $('#personnelsTable tbody').on('click', 'tr.group-row', function() {
                let groupName = $(this).data('group');
                let iconElement = $(this).find('i');

                personnelsTable.rows().every(function() {
                    if (this.data().cpnyid ===
                        groupName
                    ) { // Sesuaikan dengan nama properti data yang digunakan untuk grouping
                        $(this.node()).toggleClass('collapsed-group-row');
                    }
                });

                // Mengganti ikon plus/minus
                if (iconElement.hasClass('fa-plus-circle')) {
                    iconElement.removeClass('fa-plus-circle').addClass('fa-minus-circle');
                } else {
                    iconElement.removeClass('fa-minus-circle').addClass('fa-plus-circle');
                }
            });


            // Filter status akan memfilter data di personnelsTable
            $('.status-filter').on('click', function(e) {
                e.preventDefault();

                let selectedStatus = $(this).data('status');
                let isHcbp = $(this).data('hcbp');

                let newUrl = "{{ route('personnels.json') }}";

                if (isHcbp) {

                    $('#hcbpFilters').show();

                    let status = $('#filterStatus').val();
                    let dept = $('#filterDept').val();

                    setJobPostingFilterVisible(status);

                    newUrl += "?hcbp=1"
                        + "&status=" + encodeURIComponent(status ?? '')
                        + "&department=" + encodeURIComponent(dept ?? '');

                } else {

                    $('#hcbpFilters').hide();

                    setJobPostingFilterVisible(selectedStatus);

                    newUrl += "?status=" + encodeURIComponent(selectedStatus ?? '');
                }

                personnelsTable.ajax.url(appendAllDeptFilters(newUrl)).load();
            });

            // 🔥 APPLY FILTER
            $('#applyFilter').on('click', function() {

                let status = $('#filterStatus').val();
                let dept = $('#filterDept').val();

                setJobPostingFilterVisible(status);

                let newUrl = "{{ route('personnels.json') }}"
                    + "?hcbp=1"
                    + "&status=" + encodeURIComponent(status ?? '')
                    + "&department=" + encodeURIComponent(dept ?? '');

                console.log("APPLY URL:", newUrl); // debug

                personnelsTable.ajax.url(appendAllDeptFilters(newUrl)).load();
            });

            $('#resetFilter').on('click', function() {

                $('#filterStatus').val('');
                $('#filterDept').val('');

                setJobPostingFilterVisible('');

                let newUrl = "{{ route('personnels.json') }}?hcbp=1";

                console.log("RESET URL:", newUrl);

                personnelsTable.ajax.url(appendAllDeptFilters(newUrl)).load();
            });
        });
        // Make each .grid-col-1 set independent
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

      function processStatus(docid, status, successText, btn = null, reason = null) {

            $.post('/jobposting/toggle-status', {
                docid,
                status,
                reason, // ✅ SEND REASON
                _token: '{{ csrf_token() }}'
            })
            .done(() => {
                Swal.fire({
                    icon: 'success',
                    title: successText,
                    timer: 1200,
                    showConfirmButton: false
                });

                personnelsTable.ajax.reload(null, true);
            })
            .fail(() => {
                Swal.fire('Failed', 'Something went wrong', 'error');
            });
        }
    </script>

</x-app-layout>
