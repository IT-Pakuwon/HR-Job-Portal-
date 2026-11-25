<x-app-layout>
    <x-spp-list-styles />
    <x-spp-list-scripts />
    @php
        $currentPage = Route::currentRouteName() == 'wos' ? 'HR' : '';
    @endphp


    <div class="max-w-9xl mx-auto w-full p-2">
        @php
            $statusCards = [
                [['data-job' => 'H'], 'WO List', '🕒', 'blue', $wojobs, true, null, 'job-filter', true],
                [['data-status' => 'P'], 'On Progress', '⏳', 'yellow', $onProgress, false],
                [['data-status' => 'X'], 'Cancel', '⛔', 'red', $cancel, false],
                [['data-status' => 'C'], 'Completed', '✅', 'green', $completed, false],
                [['data-status' => ''], 'All', '📦', 'all', $all, false],
            ];
            if ($isAdmin) {
                $statusCards[] = [[], 'WO Jobs All', '🌐', 'purple', $adminAll, false, null, 'admin-all-filter', true];
            }
        @endphp
        <x-status-filter-cards :custom="$statusCards" />

        <div class="dt-pretty mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="WO Jobs List" subtitle="Work orders assigned for execution">
                {{-- Date range group --}}
                <div class="flex h-10 items-center gap-2 rounded-lg border border-gray-200 bg-slate-50 px-3 transition focus-within:border-blue-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-blue-200 dark:border-gray-600 dark:bg-white/5">
                    <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <input type="date" id="filterDateFrom" class="w-32 border-0 bg-transparent p-0 text-[13px] text-gray-700 focus:outline-none focus:ring-0 dark:text-white" />
                    <span class="text-xs text-gray-400">&ndash;</span>
                    <input type="date" id="filterDateTo" class="w-32 border-0 bg-transparent p-0 text-[13px] text-gray-700 focus:outline-none focus:ring-0 dark:text-white" />
                </div>

                {{-- Business Unit (Select2) --}}
                <div class="sppb-f relative w-52">
                    <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg></span>
                    <select id="filterBusinessUnit">
                        <option value="">All Business Unit</option>
                    </select>
                </div>

                {{-- Reset --}}
                <button id="resetFilters" type="button"
                    class="inline-flex h-10 items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-100 hover:text-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Reset
                </button>
            </x-list-page-header>

            <div class="relative overflow-hidden">
                <table id="wosTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                DocID</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Date</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Company</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Department</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Business Unit
                            </th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                PIC</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Location</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Work Type</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                WO Request</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Description</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Status Pekerjaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        var currentUser = "{{ auth()->user()->username }}";

        $(document).ready(function() {
            // 🔥 default: tampilkan On Hold (H)
            let jobStatusFilter = 'H';
            let adminAllFilter  = false;

            const table = $('#wosTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],



                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'List_WOJobs',
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
                        title: 'List_WOJobs',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
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

                ajax: {
                    url: "{{ route('wos.jsonJobs') }}",
                    type: "GET",
                    data: function(d) {
                        d.job_status  = adminAllFilter ? '' : (jobStatusFilter ?? '');
                        d.admin_all   = adminAllFilter ? 1 : 0;
                        d.business_unit = $('#filterBusinessUnit').val();
                        d.date_from = $('#filterDateFrom').val();
                        d.date_to   = $('#filterDateTo').val();
                    }
                },
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: null,
                        defaultContent: ''
                    },
                    {
                        data: 'woid',
                        render: function(data, type, row) {
                            return sppDocCell(row, data || row.eid, 'wos', currentUser, true);
                        }
                    },
                    {
                        data: 'wodate',
                        className: 'text-left'
                    },
                    {
                        data: 'cpny_id',
                        className: 'text-left w-32'
                    },
                    {
                        data: 'department_id',
                        className: 'text-left whitespace-normal break-words'
                    },
                    {
                        data: 'budget_business_unit_id',
                        defaultContent: '-',
                        className: 'text-left'
                    },
                    {
                        data: 'pic_wo',
                        defaultContent: '-',
                        className: 'text-left'
                    },
                    {
                        data: null,
                        render: function(data, type, row) {

                            const loc = row.location_name ?? '';
                            const sub = row.sub_location_name ?? '';

                            if (!loc && !sub) return '-';

                            return `${loc}${sub ? ' - ' + sub : ''}`;
                        }
                    }, {
                        data: 'worktype_name',
                        defaultContent: '-',
                        className: 'text-left'
                    },
                    {
                        data: 'worequest',
                        defaultContent: '-',
                        className: 'text-left'
                    },
                    {
                        data: 'keperluan',
                        className: 'text-left whitespace-normal break-words',
                        width: '120px'
                    },
                    {
                        data: 'status_pekerjaan', // ini dok-status; kalau mau ganti ke job status tinggal pakai 'status_pekerjaan'
                        className: 'text-left',
                        render: function(data, type, row) {
                            // map dok-status (boleh dibiarkan)
                            return sppStatusBadge(data === 'H' ? 'Hold' : data);
                        }
                    }
                    // Jika ingin menampilkan job status juga, tambah 1 kolom lagi render dari row.status_pekerjaan
                ],
                searchDelay: 400,
                stateSave: true,
                responsive: true
            });

            const $buSelect = $('#filterBusinessUnit');

            function initSelect2() {
                if ($buSelect.hasClass('select2-hidden-accessible')) {
                    $buSelect.select2('destroy');
                }
                $buSelect.select2({
                    placeholder: 'All Business Unit',
                    allowClear: true,
                    width: '100%',
                    dropdownCssClass: 'sppb-s2-dropdown',
                });
                // trigger table reload on change
                $buSelect.off('change.bu').on('change.bu', function() {
                    table.ajax.reload();
                });
            }

            function loadBusinessUnits(isAdminAll) {
                const params = isAdminAll ? '?admin_all=1' : '';
                $.get('/wo-business-units' + params, function(data) {
                    const current = $buSelect.val();

                    $buSelect.empty().append('<option value="">All Business Unit</option>');
                    data.forEach(val => {
                        $buSelect.append(`<option value="${val}">${val}</option>`);
                    });

                    initSelect2();

                    if (current && data.includes(current)) $buSelect.val(current).trigger('change');
                });
            }

            loadBusinessUnits(false);

            // Helper highlight: aktifkan tombol sesuai state aktif
            function setActiveCards() {
                document.querySelectorAll('.status-filter, .job-filter, .admin-all-filter').forEach(b => b.classList.remove('active'));
                if (adminAllFilter) {
                    const btn = document.querySelector('.admin-all-filter');
                    if (btn) btn.classList.add('active');
                } else {
                    const btn = document.querySelector(
                        `.status-filter[data-status="${jobStatusFilter}"], .job-filter[data-job="${jobStatusFilter}"]`
                    );
                    if (btn) btn.classList.add('active');
                }
            }

            // initial
            setActiveCards();

            // Semua kartu pakai status-pekerjaan:
            // - Kartu On Hold punya class .job-filter data-job="H"
            // - Kartu lain .status-filter data-status=""|"P"|"R"|"C"
            $('.status-filter').on('click', function(e) {
                e.preventDefault();
                if (adminAllFilter) { adminAllFilter = false; loadBusinessUnits(false); }
                jobStatusFilter = $(this).data('status') || '';
                setActiveCards();
                table.ajax.reload(null, true);
            });

            $('.job-filter').on('click', function(e) {
                e.preventDefault();
                if (adminAllFilter) { adminAllFilter = false; loadBusinessUnits(false); }
                jobStatusFilter = $(this).data('job') || '';
                setActiveCards();
                table.ajax.reload(null, true);
            });

            // Admin-only: WO Jobs All (semua perusahaan)
            $('.admin-all-filter').on('click', function(e) {
                e.preventDefault();
                adminAllFilter  = true;
                jobStatusFilter = '';
                setActiveCards();
                loadBusinessUnits(true);
                table.ajax.reload(null, true);
            });

            $('#filterDateFrom, #filterDateTo').on('change', function() {
                table.ajax.reload();
            });

            $('#resetFilters').on('click', function() {
                $('#filterDateFrom').val('');
                $('#filterDateTo').val('');
                $buSelect.val('').trigger('change');
                table.ajax.reload();
            });
        });
    </script>
</x-app-layout>
