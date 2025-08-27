<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'sppbs' ? 'HR' : '';
    @endphp
    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid-col-1 grid gap-6 xl:grid-cols-5 xl:grid-rows-1">
            {{-- All Status --}}
            <button>
                <a href="#" class="status-filter" data-status="">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600">
                        <span class="text-xl">📄</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">All</p>
                            <p class="text-right text-xl font-extrabold">{{ $all }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- On Progress Status --}}
            <button>
                <a href="#" class="status-filter" data-status="P">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600">
                        <span class="text-xl">⏳</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">On Progress</p>
                            <p class="text-right text-xl font-extrabold">{{ $onProgress }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- Reject Status --}}
            <button>
                <a href="#" class="status-filter" data-status="R">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600">
                        <span class="text-xl">⛔️</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">Reject</p>
                            <p class="text-right text-xl font-extrabold">{{ $reject }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- Revise / Draft Status --}}
            <button>
                <a href="#" class="status-filter" data-status="D">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 dark:border-white dark:text-white">
                        <span class="text-xl">✏️</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">Revise</p>
                            <p class="text-right text-xl font-extrabold">{{ $revise }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- Completed Status --}}
            <button>
                <a href="#" class="status-filter" data-status="C">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600">
                        <span class="text-xl">✅</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">Completed</p>
                            <p class="text-right text-xl font-extrabold">{{ $completed }}</p>
                        </div>
                    </div>
                </a>
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

                /* Sppb Table Specific Styles */
                #sppbsTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    align-items: center;
                }

                #sppbsTable_filter label {
                    margin-right: 2px;
                }

                #sppbsTable_filter input {
                    width: 200px;
                }

                #sppbsTable_wrapper {
                    width: 100%;
                }

                #sppbsTable td {
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                #sppbsTable th,
                #sppbsTable td {
                    padding: 10px;
                    max-width: 200px;
                }

                #sppbsTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }

                #sppbsTable_length select {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                }

                #sppbsTable_length select option {
                    padding: 5px;
                }

                #sppbsTable_info {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                .dataTables_paginate {
                    /* This class is for all DataTables paginations */
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                #sppbsTable tbody tr td {
                    padding: 8px 8px;
                    line-height: 2;
                }

                #sppbsTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }

                #sppbsTable tbody tr:hover {
                    background-color: #8f8f8f11;
                    opacity: 100%;
                    cursor: pointer;
                }

                #sppbsTable tbody tr:hover td {
                    /* color: black; */
                }

                #sppbsTable th:nth-child(1),
                #sppbsTable td:nth-child(1) {
                    width: 120px;
                    text-align: center;
                }

                #sppbsTable th:nth-child(4),
                #sppbsTable td:nth-child(4) {
                    width: 120px;
                    text-align: center;
                }

                /* --- Custom Styles for RowGroup Collapse/Expand (Applied to sppbsTable) --- */
                /* Initially hide rows in collapsed groups */
                #sppbsTable tbody tr.collapsed-group-row {
                    display: none;
                }

                /* Style for group rows */
                #sppbsTable tr.group-row {
                    background-color: #e6e6e6;
                    /* Light gray background for group headers */
                    font-weight: bold;
                    cursor: pointer;
                    user-select: none;
                    /* Prevent text selection on click */
                    color: #333;
                    /* Darker text for group headers */
                }

                #sppbsTable tr.group-row:hover {
                    background-color: #d4d4d4;
                    /* Slightly darker on hover */
                }

                /* Icon styling */
                #sppbsTable tr.group-row .fas {
                    margin-right: 8px;
                    width: 16px;
                    /* Ensure consistent icon width */
                    text-align: center;
                }

                /* Adjust padding for group rows to look consistent with other cells */
                #sppbsTable tr.group-row td {
                    padding: 10px !important;
                    border-bottom: 1px solid #ddd;
                    /* Separator for groups */
                }

                /* Remove border from the first td in group row to match the colspan */
                #sppbsTable tr.group-row td:first-child {
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
                    {{-- Changed text-3xl to text-xl --}}
                    <h1 class="text-xl font-extrabold text-gray-700 dark:text-white">Request SPPB</h1>
                    <a href="{{ url('/createsppbs') }}"
                        class="inline-flex items-center rounded-xl bg-indigo-600 px-6 py-2 text-base font-semibold text-white transition-colors duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <i class="fas fa-plus pr-2"></i>Create
                    </a>
                </div>

                <div class="overflow-x-auto p-6"> {{-- Padding applied here instead of outer container --}}
                    <table id="sppbsTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col"
                                    class="w-32 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    DocID
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Date
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Company
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Department
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Request Type
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Description
                                </th>
                                <th scope="col"
                                    class="w-32 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            {{-- Table rows will be populated here by JavaScript/DataTables --}}
                        </tbody>
                    </table>
                </div>
            </div>


            <script>
                var currentUser = "{{ auth()->user()->username }}";
                $(document).ready(function() {
                    // simpan status filter global
                    let statusFilter = 'P'; // default

                    const table = $('#sppbsTable').DataTable({
                        processing: true,
                        serverSide: true,
                        deferRender: true,
                        // ==== SCROLLER OPSIONAL (butuh plugin DataTables Scroller) ====
                        // scrollY: '60vh',
                        // scroller: true,

                        pageLength: 25,
                        lengthMenu: [10, 25, 50, 100, 250],

                        ajax: {
                            url: "{{ route('sppbs.json') }}",
                            type: "GET",
                            data: function(d) {
                                d.status = statusFilter ?? ''; // kirim status ke server
                            }
                        },

                        order: [
                            [1, 'desc'],
                            [0, 'desc']
                        ], // Date desc, lalu DocID desc

                        columns: [
                            // DocID (button link)
                            {
                                data: 'sppbid',
                                render: function(data, type, row) {
                                    let url = `/showsppbs/${row.id}`;
                                    let cls =
                                        'px-4 py-2.5 bg-indigo-500 text-white rounded hover:bg-indigo-700';
                                    let text = data || row.id;
                                    if (row.status === 'D' && row.created_by === currentUser) {
                                        url = `/editsppbs/${row.id}`;
                                        cls =
                                            'px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-700';
                                    }
                                    return `<a href="${url}" class="${cls}">${text}</a>`;
                                }
                            },
                            {
                                data: 'sppbdate',
                                className: 'text-center'
                            },
                            {
                                data: 'cpny_id',
                                className: 'text-center'
                            },
                            {
                                data: 'department_id',
                                className: 'text-center'
                            },
                            {
                                data: 'requesttype_name',
                                defaultContent: '-',
                                className: 'text-center'
                            },
                            {
                                data: 'keperluan'
                            },
                            {
                                data: 'status',
                                className: 'text-center',
                                render: function(data) {
                                    const map = {
                                        'D': {
                                            t: 'Revise',
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
                                    return `<span class="w-32 inline-block ${it.c} font-semibold px-4 py-2 text-center rounded">${it.t}</span>`;
                                }
                            }
                        ],

                        // Tweak untuk kinerja
                        searchDelay: 400, // debounce search
                        stateSave: true, // simpan state tabel (opsional)
                        responsive: true
                    });

                    // Ganti status filter → reload data tanpa rebuild tabel
                    $('.status-filter').on('click', function(e) {
                        e.preventDefault();
                        statusFilter = $(this).data('status') || '';
                        table.ajax.reload(null, true); // reset ke page 1
                    });
                });
            </script>


        </div>
    </div>
</x-app-layout>
