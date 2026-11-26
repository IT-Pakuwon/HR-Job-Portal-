<x-app-layout>
    <x-spp-list-styles />
    <x-spp-list-scripts />
    @php
        $isHcbp = auth()->user()->hasRole('HCBPACCESS');
        $isSby = ($group_cpny_id ?? '') === 'SBY';

        $xlCols = 6; // All, On Progress, Reject, Revise, Completed, Cancel

        if ($isSby) {
            $xlCols++; // tambah 1 untuk Draft (Save as Draft / Copy Template khusus SBY)
        }

        if ($isHcbp) {
            $xlCols++; // tambah 1 untuk HCBP All
        }
    @endphp
    <div class="max-w-9xl mx-auto w-full p-2">
        {{-- <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5"> --}}

            @php
            $statusCards = [
                [['data-status' => ''], 'All', '📦', 'all', $all, false],
                [['data-status' => 'P'], 'On Progress', '⏳', 'yellow', $onProgress, true],
                [['data-status' => 'R'], 'Reject', '⛔', 'red', $reject, false],
                [['data-status' => 'D'], 'Revise', '✏️', 'blue', $revise, false],
            ];
            if ($isSby) {
                $statusCards[] = [['data-status' => 'H'], 'Draft', '📝', 'pink', $draft, false];
            }
            $statusCards[] = [['data-status' => 'C'], 'Completed', '✅', 'green', $completed, false];
            $statusCards[] = [['data-status' => 'X'], 'Cancel', '🚫', 'red', $cancel, false];
            if ($isHcbp) {
                $statusCards[] = [['data-hcbp' => '1'], 'HCBP All', '🌐', 'purple', $hcbpAll, false];
            }
        @endphp
        <x-status-filter-cards :custom="$statusCards" />
        <div class="dt-pretty mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            @php $hasAllDeptAccessFlag = $hasAllDeptAccess ?? false; @endphp
            <x-list-page-header title="Personnel Requisition Form" subtitle="Create, track and manage personnel requisitions" :create-url="url('/createpersonnels')">
            <div id="allDeptFilterWrap" class="{{ ($hasAllDeptAccessFlag) ? '' : 'hidden' }}">
                <div class="flex flex-wrap items-center gap-3">
                    @if($hasAllDeptAccessFlag)
                    <div class="sppb-f relative w-full sm:w-48">
                        <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg></span>
                        <select id="filterCompanyAllDept">
                            <option value="">All Company</option>
                            @foreach($filterCompanies as $company)
                                <option value="{{ $company->cpny_id }}">{{ $company->cpny_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sppb-f relative w-full sm:w-48">
                        <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg></span>
                        <select id="filterDivisionAllDept">
                            <option value="">All Division</option>
                            @foreach($filterDivisions as $division)
                                <option value="{{ $division->division_id }}">{{ $division->division_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sppb-f relative w-full sm:w-48">
                        <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg></span>
                        <select id="filterDepartmentAllDept">
                            <option value="">All Department</option>
                            @foreach($filterDepartments as $department)
                                <option value="{{ $department->department_id }}" data-division="{{ $department->division_id }}">{{ $department->department_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    {{-- Job Posting Status - only relevant (and shown) while the Completed tab is active --}}
                    <div id="jobPostingStatusSegment" class="sppb-f relative w-full sm:w-48">
                        <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>
                        <select id="filterJobPostingStatus">
                            <option value="">All Job Posting Status</option>
                            <option value="P">Posted</option>
                            <option value="C">Closed</option>
                            <option value="U">Unposted</option>
                            <option value="H">Hold</option>
                            <option value="X">Cancelled</option>
                            <option value="NOTPOSTED">Not Posted</option>
                        </select>
                    </div>
                    <button type="button" id="resetAllDeptFilters"
                        class="inline-flex h-10 items-center rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-gray-50 hover:shadow-md active:translate-y-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                        <i class="fas fa-rotate-right mr-2 text-xs"></i>Reset
                    </button>
                </div>
            </div>
                @if(auth()->user()->hasRole('HCBPACCESS'))
                    <div class="flex flex-wrap items-center gap-2" id="hcbpFilters" style="display:none;">
                        <div class="sppb-f relative w-44">
                            <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>
                            <select id="filterStatus">
                                <option value="">All Status</option>
                                <option value="P">On Progress</option>
                                <option value="R">Reject</option>
                                <option value="D">Revise</option>
                                <option value="C">Completed</option>
                                <option value="X">Cancel</option>
                                @if($isSby)
                                <option value="H">Draft</option>
                                @endif
                            </select>
                        </div>
                        <div class="sppb-f relative w-56">
                            <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg></span>
                            <select id="filterDept">
                                <option value="">All Department</option>
                                @foreach($departments ?? [] as $dept)
                                    <option value="{{ $dept->department_id }}">{{ $dept->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button id="applyFilter" type="button"
                            class="inline-flex h-10 items-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white transition hover:-translate-y-0.5 hover:bg-blue-500 hover:shadow-md active:translate-y-0">
                            Apply
                        </button>
                        <button id="resetFilter" type="button"
                            class="inline-flex h-10 items-center rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-gray-50 hover:shadow-md active:translate-y-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            <i class="fas fa-rotate-right mr-2 text-xs"></i>Reset
                        </button>
                    </div>
                @endif
            </x-list-page-header>

            <style>
                #jobPostingStatusSegment.hidden { display: none; }
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
            // HCBP header dropdowns use the shared Select2 skin
            $('#filterStatus, #filterDept').each(function() {
                $(this).select2({ width: '100%', minimumResultsForSearch: this.id === 'filterStatus' ? Infinity : 8, dropdownCssClass: 'sppb-s2-dropdown' });
            });
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
                dropdownCssClass: 'sppb-s2-dropdown'
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
                    dropdownCssClass: 'sppb-s2-dropdown'
                });
                $('#filterDivisionAllDept').select2({
                    placeholder: 'All Division',
                    width: '100%',
                    dropdownCssClass: 'sppb-s2-dropdown'
                });
                $('#filterDepartmentAllDept').select2({
                    placeholder: 'All Department',
                    width: '100%',
                    dropdownCssClass: 'sppb-s2-dropdown'
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
                        text: '<i class="fa-solid fa-file-excel"></i> Excel',
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
                        text: '<i class="fa-solid fa-file-csv"></i> CSV',
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
                                'group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white';
                            const buttonText = row.docid;

                            const isReviseOwner = (row.status === 'D' || row.status === 'H') && row.created_user === currentUser;

                            if (isReviseOwner) {
                                mainUrl = `/editpersonnels/${row.eid}`;
                                buttonClass =
                                    'group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide tabular-nums text-amber-800 transition-all duration-200 hover:-translate-y-0.5 hover:border-amber-500 hover:bg-amber-500 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300';
                            }

                            const canCopyTemplate = row.status === 'C' && row.group_cpny_id === 'SBY';
                            const copyBtnHtml = canCopyTemplate ? `
                                <button type="button" class="copyTemplateBtn inline-flex h-8 w-8 items-center justify-center rounded-md border border-teal-100 bg-teal-50 text-teal-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-teal-600 hover:bg-teal-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90"
                                    title="Copy Template" data-eid="${row.eid}">
                                    <i class="fas fa-copy text-sm"></i>
                                </button>
                            ` : '';

                            if (isReviseOwner) {
                                return `
                                    <div class="flex items-center gap-2">
                                        <a href="${mainUrl}" class="${buttonClass}" title="${isReviseOwner ? 'Edit' : 'Open'}"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 ${String(isReviseOwner ? 'fa-solid fa-pen text-[10px]' : 'fa-regular fa-file-lines').includes('fa-pen') ? 'group-hover/doc:rotate-12' : 'group-hover/doc:-rotate-6'}"><i class="${isReviseOwner ? 'fa-solid fa-pen text-[10px]' : 'fa-regular fa-file-lines'}"></i></span><span>${buttonText}</span></a>

                                        <a href="${showUrl}"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-blue-100 bg-blue-50 text-blue-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90"
                                        title="View">
                                            <i class="fas fa-eye text-sm"></i>
                                        </a>
                                        ${copyBtnHtml}
                                    </div>
                                `;
                            }

                            return `
                                <div class="flex items-center gap-2">
                                    <a href="${mainUrl}" class="${buttonClass}" title="Open"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 group-hover/doc:-rotate-6"><i class="fa-regular fa-file-lines"></i></span><span>${buttonText}</span></a>
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
                            return sppStatusBadge(data);
                        }
                    },
                    {
                        data: 'jobposting_status',
                        render: function(data, type, row) {

                            let text = '';
                            let cls = '';

                           if (!data) {
                                text = 'Not Posted';
                                cls = 'bg-slate-50 text-slate-600 ring-slate-200';
                            } else if (data === 'U') {
                                text = 'Unposted';
                                cls = 'bg-slate-50 text-slate-600 ring-slate-200';
                            } else if (data === 'P') {
                                text = 'Posted';
                                cls = 'bg-blue-50 text-blue-700 ring-blue-200';
                            } else if (data === 'C') {
                                text = 'Closed';
                                cls = 'bg-green-50 text-green-700 ring-green-200';
                            } else if (data === 'X') {
                                text = 'Cancelled';
                                cls = 'bg-rose-50 text-rose-700 ring-rose-200';
                            } else if (data === 'H') {
                                text = 'Hold';
                                cls = 'bg-orange-50 text-orange-700 ring-orange-200';
                                const reason = row.jobposting_reason ?? null;
                                const reasonIcon = reason
                                    ? ` <span class="jp-reason-icon cursor-pointer ml-1 align-middle" title="${reason}" data-reason="${reason}">ℹ️</span>`
                                    : '';
                                return `<span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset ${cls}">${text}${reasonIcon}</span>`;
                            } else {
                                text = data;
                                cls = 'bg-slate-50 text-slate-600 ring-slate-200';
                            }
                            return `<span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset ${cls}">${text}</span>`;
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
                $('.status-filter').removeClass('active');
                $(this).addClass('active');

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

                $('#filterStatus').val('').trigger('change.select2');
                $('#filterDept').val('').trigger('change.select2');

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
