<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'wos' ? 'HR' : '';
    @endphp

    <style>
        /* Active / Selected state */
        .status-filter.active .status-card {
            transform: scale(1.02);
        }

        .status-filter[data-status=""].active .status-card {
            background-color: rgb(254 215 170);
            /* orange-200 */
            border-color: rgb(194 65 12)
        }

        .status-filter[data-status="P"].active .status-card {
            background-color: rgb(191 219 254);
            /* blue-200 */
            border-color: rgb(29 78 216);
            /* blue-700 */
        }

        .status-filter[data-status="R"].active .status-card {
            background-color: rgb(254 202 202);
            /* red-200 */
            border-color: rgb(185 28 28);
            /* red-700 */
        }

        .status-filter[data-status="D"].active .status-card {
            background-color: rgb(229 231 235);
            /* gray-200 */
            border-color: rgb(31 41 55);
            /* gray-700 */
        }

        .status-filter[data-status="C"].active .status-card {
            background-color: rgb(187 247 208);
            /* green-200 */
            border-color: rgb(21 128 61);
            /* green-700 */
        }
    </style>

    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid-col-1 grid gap-6 xl:grid-cols-5 xl:grid-rows-1">

            {{-- 🔥 On Hold (status_pekerjaan = H) - kiri paling awal --}}
            <button type="button" class="job-filter group block" data-job="H">
                <div
                    class="status-card flex items-center gap-4 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-lg active:scale-95">
                    <span class="text-xl group-hover:animate-pulse">🕒</span>
                    <div class="flex flex-grow items-center justify-between">
                        <p class="text-lg font-medium">On Hold</p>
                        <p class="text-right text-xl font-extrabold">{{ $wojobs }}</p>
                    </div>
                </div>
            </button>

            {{-- On Progress (P) --}}
            <button type="button" class="status-filter group block" data-status="P">
                <div
                    class="status-card flex items-center gap-4 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-lg active:scale-95">
                    <span class="text-xl group-hover:animate-pulse">⏳</span>
                    <div class="flex flex-grow items-center justify-between">
                        <p class="text-lg font-medium">On Progress</p>
                        <p class="text-right text-xl font-extrabold">{{ $onProgress }}</p>
                    </div>
                </div>
            </button>

            {{-- Reject (R) --}}
            <button type="button" class="status-filter group block" data-status="R">
                <div
                    class="status-card flex items-center gap-4 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-lg active:scale-95">
                    <span class="text-xl group-hover:animate-pulse">⛔️</span>
                    <div class="flex flex-grow items-center justify-between">
                        <p class="text-lg font-medium">Reject</p>
                        <p class="text-right text-xl font-extrabold">{{ $reject }}</p>
                    </div>
                </div>
            </button>

            {{-- Completed (C) --}}
            <button type="button" class="status-filter group block" data-status="C">
                <div
                    class="status-card flex items-center gap-4 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-lg active:scale-95">
                    <span class="text-xl group-hover:animate-pulse">✅</span>
                    <div class="flex flex-grow items-center justify-between">
                        <p class="text-lg font-medium">Completed</p>
                        <p class="text-right text-xl font-extrabold">{{ $completed }}</p>
                    </div>
                </div>
            </button>

            {{-- All (dok status semua/terserah filter lain) --}}
            <button type="button" class="status-filter group block" data-status="">
                <div
                    class="status-card flex items-center gap-4 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-lg active:scale-95">
                    <span class="text-xl group-hover:animate-pulse">📄</span>
                    <div class="flex flex-grow items-center justify-between">
                        <p class="text-lg font-medium">All</p>
                        <p class="text-right text-xl font-extrabold">{{ $all }}</p>
                    </div>
                </div>
            </button>
        </div>


        <div class="grid">
            <style>
                .no-border {
                    border: none !important;
                }

                .grid {
                    width: 100%;
                }

                select,
                textarea,
                input {
                    width: 100%;
                    /* Make all input elements take full width */
                }

                table.dataTable {
                    width: 100% !important;
                }

                .dataTables_wrapper {
                    width: 100%;
                }

                @media (max-width: 600px) {
                    .dataTables_wrapper {
                        padding: 0 10px;
                    }
                }

                /* Wo Table Specific Styles */
                #wosTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    align-items: center;
                }

                #wosTable_filter label {
                    margin-right: 2px;
                }

                #wosTable_filter input {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }

                #wosTable_wrapper {
                    width: 100%;
                }

                #wosTable td {
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                #wosTable th,
                #wosTable td {
                    padding: 10px;
                    max-width: 200px;
                }

                #wosTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }

                #wosTable_length select {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }

                #wosTable_length select option {
                    padding: 5px;
                }

                #wosTable_info {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                .dataTables_paginate {
                    /* This class is for all DataTables paginations */
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                #wosTable tbody tr td {
                    padding: 8px 8px;
                    line-height: 2;
                }

                #wosTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }

                #wosTable tbody tr:hover {
                    background-color: #8f8f8f11;
                    opacity: 100%;
                    cursor: pointer;
                }

                #wosTable tbody tr:hover td {
                    /* color: black; */
                }

                #wosTable th:nth-child(1),
                #wosTable td:nth-child(1) {
                    width: 120px;
                    text-align: center;
                }

                #wosTable th:nth-child(4),
                #wosTable td:nth-child(4) {
                    width: 120px;
                    text-align: center;
                }

                /* --- Custom Styles for RowGroup Collapse/Expand (Applied to wosTable) --- */
                /* Initially hide rows in collapsed groups */
                #wosTable tbody tr.collapsed-group-row {
                    display: none;
                }

                /* Style for group rows */
                #wosTable tr.group-row {
                    background-color: #e6e6e6;
                    /* Light gray background for group headers */
                    font-weight: bold;
                    cursor: pointer;
                    user-select: none;
                    /* Prevent text selection on click */
                    color: #333;
                    /* Darker text for group headers */
                }

                #wosTable tr.group-row:hover {
                    background-color: #d4d4d4;
                    /* Slightly darker on hover */
                }

                /* Icon styling */
                #wosTable tr.group-row .fas {
                    margin-right: 8px;
                    width: 16px;
                    /* Ensure consistent icon width */
                    text-align: center;
                }

                /* Adjust padding for group rows to look consistent with other cells */
                #wosTable tr.group-row td {
                    padding: 10px !important;
                    border-bottom: 1px solid #ddd;
                    /* Separator for groups */
                }

                /* Remove border from the first td in group row to match the colspan */
                #wosTable tr.group-row td:first-child {
                    border-left: none;
                }

                /* ✅ Custom Switch Button (Global, if used elsewhere) */
                .switch {
                    position: relative;
                    display: inline-block;
                    width: 40px;
                    height: 22px;
                }

                .switch input {
                    opacity: 0;
                    width: 0;
                    height: 0;
                }

                .slider {
                    position: absolute;
                    cursor: pointer;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background-color: #ccc;
                    transition: .4s;
                    border-radius: 34px;
                }

                .slider:before {
                    position: absolute;
                    content: "";
                    height: 16px;
                    width: 16px;
                    left: 3px;
                    bottom: 3px;
                    background-color: white;
                    transition: .4s;
                    border-radius: 50%;
                }

                input:checked+.slider {
                    background-color: #4CAF50;
                }

                input:checked+.slider:before {
                    transform: translateX(18px);
                }
            </style>

            <div class="mt-6 rounded-2xl bg-white dark:bg-gray-800">
                <div
                    class="flex flex-col items-start justify-between gap-4 border-b border-gray-200 p-4 sm:flex-row sm:items-center dark:border-gray-700">
                    <h1 class="text-xl font-extrabold text-gray-700 dark:text-white">Jobs WO</h1>
                </div>

                <div class="overflow-x-auto p-6">
                    <table id="wosTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="w-32 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    DocID</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Date</th>
                                <th
                                    class="w-32 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Company</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Department</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Work Type</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    WO Request</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Description</th>
                                <th
                                    class="w-32 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Status Pekerjaan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>
                </div>
            </div>


            <script>
                var currentUser = "{{ auth()->user()->username }}";

                $(document).ready(function() {
                    // 🔥 default: tampilkan On Hold (H)
                    let jobStatusFilter = 'H';

                    const table = $('#wosTable').DataTable({
                        processing: true,
                        serverSide: true,
                        deferRender: true,
                        pageLength: 25,
                        lengthMenu: [10, 25, 50, 100, 250],
                        ajax: {
                            url: "{{ route('wos.jsonJobs') }}",
                            type: "GET",
                            data: function(d) {
                                d.job_status = jobStatusFilter ?? ''; // 🔥 hanya ini yg dikirim
                            }
                        },
                        order: [
                            [0, 'desc']
                        ],
                        columns: [{
                                data: 'woid',
                                render: function(data, type, row) {
                                    let url = `/showwos/${row.eid}`;
                                    let cls =
                                        'shrink-0 px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-700 text-sm';
                                    const text = data || row.eid;

                                    if (row.status === 'D' && row.created_by === currentUser) {
                                        url = `/editwos/${row.eid}`;
                                        cls =
                                            'shrink-0 px-3 py-1.5 bg-yellow-500 text-white rounded hover:bg-yellow-700 text-sm';
                                    }

                                    return `
                                        <a href="${url}" class="${cls}">${text}</a>
                                    `;

                                }
                            },
                            {
                                data: 'wodate',
                                className: 'text-left'
                            },
                            {
                                data: 'cpny_id',
                                className: 'text-center w-32'
                            },
                            {
                                data: 'department_id',
                                className: 'text-center whitespace-normal break-words'
                            },
                            {
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
                                data: 'keperluan'
                            },
                            {
                                data: 'status_pekerjaan', // ini dok-status; kalau mau ganti ke job status tinggal pakai 'status_pekerjaan'
                                className: 'text-left',
                                render: function(data, type, row) {
                                    // map dok-status (boleh dibiarkan)
                                    const map = {
                                        'H': {
                                            t: 'Hold',
                                            c: 'bg-gray-300/30 text-gray-600'
                                        },
                                        'P': {
                                            t: 'On Progress',
                                            c: 'bg-blue-300/30 text-blue-600'
                                        },
                                        'C': {
                                            t: 'Completed',
                                            c: 'bg-green-300/30 text-green-600'
                                        },
                                        'X': {
                                            t: 'Cancel',
                                            c: 'bg-red-300/30 text-red-600'
                                        },
                                        'R': {
                                            t: 'Rejected',
                                            c: 'bg-red-300/30 text-red-600'
                                        },
                                    };
                                    const it = map[data] || {
                                        t: data || '-',
                                        c: 'bg-gray-300/30 text-gray-600'
                                    };
                                    return `<span class="w-32 inline-block ${it.c} font-semibold px-3 py-1.5 text-base text-center rounded">${it.t}</span>`;
                                }
                            }
                            // Jika ingin menampilkan job status juga, tambah 1 kolom lagi render dari row.status_pekerjaan
                        ],
                        searchDelay: 400,
                        stateSave: true,
                        responsive: true
                    });

                    // Helper highlight: aktifkan tombol sesuai jobStatusFilter
                    function setActiveCards() {
                        document.querySelectorAll('.status-filter, .job-filter').forEach(b => b.classList.remove('active'));
                        const btn = document.querySelector(
                            `.status-filter[data-status="${jobStatusFilter}"], .job-filter[data-job="${jobStatusFilter}"]`
                            );
                        if (btn) btn.classList.add('active');
                    }

                    // initial
                    setActiveCards();

                    // Semua kartu pakai status-pekerjaan:
                    // - Kartu On Hold punya class .job-filter data-job="H"
                    // - Kartu lain .status-filter data-status=""|"P"|"R"|"C"
                    $('.status-filter').on('click', function(e) {
                        e.preventDefault();
                        jobStatusFilter = $(this).data('status') || ''; // '' = All job statuses
                        setActiveCards();
                        table.ajax.reload(null, true);
                    });

                    $('.job-filter').on('click', function(e) {
                        e.preventDefault();
                        jobStatusFilter = $(this).data('job') || '';
                        setActiveCards();
                        table.ajax.reload(null, true);
                    });
                });
            </script>

        </div>
    </div>
</x-app-layout>
