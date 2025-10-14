<x-app-layout>
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
        #canvassTable_filter {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }

        #canvassTable_filter label {
            margin-right: 2px;
        }

        #canvassTable_filter input {
            width: 200px;
        }

        #canvassTable_wrapper {
            width: 100%;
        }

        #canvassTable td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #canvassTable th,
        #canvassTable td {
            padding: 10px;
            max-width: 200px;
        }

        .dataTables_wrapper {
            display: flex;
            flex-wrap: wrap;
            /* wrap on small screens */
            gap: 1rem;
            /* spacing between length and filter */
            margin-bottom: 1rem;
            align-items: center;
            /* vertically center items */
        }

        /* Length selector: take full width on small screens */
        .dataTables_wrapper .dataTables_length {
            flex: 1 1 300px;
            /* grow, shrink, min-width 300px */
            display: flex;
            align-items: center;
        }

        /* Search filter: also full width */
        .dataTables_wrapper .dataTables_filter {
            flex: 1 1 300px;
            /* grow, shrink, min-width 300px */
            display: flex;
            justify-content: flex-end;
            /* search aligned right */
            align-items: center;
        }

        /* Style the select and input nicely */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            width: auto;
            padding: 5px;
            min-width: 80px;
            padding: 0.25rem 0.5rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
        }

        /* Optional: spacing inside the label for "Show entries" */
        .dataTables_wrapper .dataTables_length label {
            width: auto;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }



        /* Option padding */
        #canvassTable_length select option {
            padding: 5px;
        }


        #canvassTable_info {
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .dataTables_paginate {
            /* This class is for all DataTables paginations */
            margin-top: 10px;
            margin-bottom: 10px;
        }

        #canvassTable tbody tr td {
            padding: 8px 8px;
            line-height: 2;
        }

        #canvassTable tbody tr {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        #canvassTable tbody tr:hover {
            background-color: #8f8f8f11;
            opacity: 100%;
            cursor: pointer;
        }

        #canvassTable tbody tr:hover td {
            /* color: black; */
        }

        #canvassTable th:nth-child(1),
        #canvassTable td:nth-child(1) {
            width: 120px;
            text-align: center;
        }

        #canvassTable th:nth-child(4),
        #canvassTable td:nth-child(4) {
            width: 120px;
            text-align: center;
        }

        /* --- Custom Styles for RowGroup Collapse/Expand (Applied to canvassTable) --- */
        /* Initially hide rows in collapsed groups */
        #canvassTable tbody tr.collapsed-group-row {
            display: none;
        }

        /* Style for group rows */
        #canvassTable tr.group-row {
            background-color: #e6e6e6;
            /* Light gray background for group headers */
            font-weight: bold;
            cursor: pointer;
            user-select: none;
            /* Prevent text selection on click */
            color: #333;
            /* Darker text for group headers */
        }

        #canvassTable tr.group-row:hover {
            background-color: #d4d4d4;
            /* Slightly darker on hover */
        }

        /* Icon styling */
        #canvassTable tr.group-row .fas {
            margin-right: 8px;
            width: 16px;
            /* Ensure consistent icon width */
            text-align: center;
        }

        /* Adjust padding for group rows to look consistent with other cells */
        #canvassTable tr.group-row td {
            padding: 10px !important;
            border-bottom: 1px solid #ddd;
            /* Separator for groups */
        }

        /* Remove border from the first td in group row to match the colspan */
        #canvassTable tr.group-row td:first-child {
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
    {{-- Select2 CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- Toastr (kalau belum ada di layout) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    @php
        $currentPage = Route::currentRouteName() == 'canvass' ? 'HR' : '';
    @endphp
    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-5">
            <div
                class="flex items-center gap-4 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600">
                <span class="text-xl">📄</span>
                <div class="flex flex-grow items-center justify-between">
                    <p class="text-lg font-medium">All</p>
                    <p id="count-all" class="text-right text-xl font-extrabold">{{ $all }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600">
                <span class="text-xl">⏳</span>
                <div class="flex flex-grow items-center justify-between">
                    <p class="text-lg font-medium">SPPB</p>
                    <p id="count-sppb" class="text-right text-xl font-extrabold">{{ $sppb }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600">
                <span class="text-xl">⛔️</span>
                <div class="flex flex-grow items-center justify-between">
                    <p class="text-lg font-medium">SPPJ</p>
                    <p id="count-sppj" class="text-right text-xl font-extrabold">{{ $sppj }}</p>
                </div>
            </div>

            <div
                class="flex items-center gap-4 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 dark:border-white dark:text-white">
                <span class="text-xl">✏️</span>
                <div class="flex flex-grow items-center justify-between">
                    <p class="text-lg font-medium">SPPK</p>
                    <p id="count-sppk" class="text-right text-xl font-extrabold">{{ $sppk }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600">
                <span class="text-xl">✅</span>
                <div class="flex flex-grow items-center justify-between">
                    <p class="text-lg font-medium">SPPT</p>
                    <p id="count-sppt" class="text-right text-xl font-extrabold">{{ $sppt }}</p>
                </div>
            </div>
        </div>

        <div class="grid">
            <div class="mt-6 rounded-2xl bg-white dark:bg-gray-800">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="flex gap-2 p-4">
                        <button
                            class="tab-btn active border-b-2 border-indigo-600 px-4 py-2 text-xl font-semibold text-indigo-700 dark:text-indigo-300"
                            data-tab="mine">CS Jobs</button>
                        <button
                            class="tab-btn px-4 py-2 text-xl font-semibold hover:border-b-2 hover:border-gray-400 dark:hover:border-gray-500"
                            data-tab="entry">Entry CS</button>
                        <button
                            class="tab-btn px-4 py-2 text-xl font-semibold hover:border-b-2 hover:border-gray-400 dark:hover:border-gray-500"
                            data-tab="revision">My Revision</button>
                        <button
                            class="tab-btn px-4 py-2 text-xl font-semibold hover:border-b-2 hover:border-gray-400 dark:hover:border-gray-500"
                            data-tab="all">All Jobs</button>
                        <button
                            class="tab-btn px-4 py-2 text-xl font-semibold hover:border-b-2 hover:border-gray-400 dark:hover:border-gray-500"
                            data-tab="sppbjkt">SPPBJKT IN Progress</button>
                    </nav>
                </div>


                <div class="overflow-x-auto p-6">
                    <!-- Tabs header -->
                    {{-- <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                        <nav class="flex gap-2">
                            <button
                                class="tab-btn active rounded-t bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300"
                                data-tab="mine">CS Jobs</button>
                            <button
                                class="tab-btn rounded-t px-4 py-2 text-sm font-semibold hover:bg-gray-100 dark:hover:bg-gray-700"
                                data-tab="entry">Entry CS</button>
                            <button
                                class="tab-btn rounded-t px-4 py-2 text-sm font-semibold hover:bg-gray-100 dark:hover:bg-gray-700"
                                data-tab="all">All Jobs</button>
                            <button
                                class="tab-btn rounded-t px-4 py-2 text-sm font-semibold hover:bg-gray-100 dark:hover:bg-gray-700"
                                data-tab="revision">My Revision</button>
                            <button
                                class="tab-btn rounded-t px-4 py-2 text-sm font-semibold hover:bg-gray-100 dark:hover:bg-gray-700"
                                data-tab="sppbjkt">SPPBJKT IN Progress</button>
                        </nav>

                    </div> --}}

                    <!-- CS Jobs (mine) -->
                    <div id="tab-mine" class="tab-pane block">
                        <table id="tblMine" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="w-32 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                        Action</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">DocID
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Assign Date</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Date</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Company</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Name</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Assign Purchasing</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Assign By</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                        Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            </tbody>
                        </table>
                    </div>

                    <!-- Entry CS (draft/H milik saya) -->
                    <div id="tab-entry" class="tab-pane hidden">
                        <table id="tblEntryCS" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">CSID
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Date</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Company</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">User
                                        Peminta</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Note
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                        Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            </tbody>
                        </table>
                    </div>


                    <!-- All Jobs -->
                    <div id="tab-all" class="tab-pane hidden">
                        <table id="tblAll" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">DocID
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Assign Date</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Date</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Company</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Name</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Assign Purchasing</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Assign By</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                        Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            </tbody>
                        </table>
                    </div>

                    <!-- My Revision -->
                    <div id="tab-revision" class="tab-pane hidden">
                        <table id="tblRevision" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="w-2 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                        Action</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                        DocID</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Assign Date</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Date</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Company</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Name</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Assign Purchasing</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Assign By</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                        Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            </tbody>
                        </table>
                    </div>

                    <!-- SPPBJKT IN Progress -->
                    <div id="tab-sppbjkt" class="tab-pane hidden">
                        <table id="tblSppbjkt" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                        DocID</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Assign Date</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Date</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Company</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Name</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Assign Purchasing</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Assign By</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                        Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(function() {
            let docTypeFilter = ''; // '' = all

            // const mapCreateUrl = { SPPB:'createcs_sppb', SPPJ:'createcs_sppj', SPPK:'createcs_sppk', SPPT:'createcs_sppt' };
            const mapShowUrl = {
                SPPB: 'showsppbs',
                SPPJ: 'showsppjs',
                SPPK: 'showsppks',
                SPPT: 'showsppts'
            };

            function buildCreateUrl(row) {
                // /createcs/{doc}/{src}/{row}
                const r = row.row_id ?? '';
                // return `/createcs/${row.doc_type}/${row.src_id}`;
                return `/createcs/${row.doc_type}/${row.eid}`;
            }

            function renderDocBtn(row) {
                const base = mapShowUrl[row.doc_type] || '#';
                // const url = `/${base}/${row.src_id}`;
                const url = `/${base}/${row.eid}`;
                return `<a href="${url}" class="rounded px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-700 w-32">${row.doc_no}</a>`;
            }

            function colSetWithoutCreate() {
                return [
                    // 0) DocID (button to show page)
                    {
                        data: null,
                        className: 'text-left',
                        render: (_d, _t, row) => renderDocBtn(row)
                    },
                    // 1) assigndate
                    {
                        data: 'assigndate',
                        className: 'text-center',
                        render: (v) => v ? (isNaN(new Date(v)) ? v : new Date(v).toLocaleDateString('id-ID')) :
                            ''
                    },
                    // 2) doc_date
                    {
                        data: 'doc_date',
                        className: 'text-left',
                        render: (v) => v ? (isNaN(new Date(v)) ? v : new Date(v).toLocaleDateString('id-ID')) :
                            ''
                    },
                    // 3) cpny_id
                    {
                        data: 'cpny_id',
                        className: 'text-left'
                    },
                    // 4) created_by_name
                    {
                        data: 'created_by_name',
                        className: 'text-left',
                        defaultContent: '-'
                    },
                    // 5) assignpurchasing
                    {
                        data: 'assignpurchasing',
                        className: 'text-left',
                        defaultContent: ''
                    },
                    // 6) assignby
                    {
                        data: 'assignby',
                        className: 'text-left',
                        defaultContent: ''
                    },
                    // 7) department_id
                    {
                        data: 'department_id',
                        className: 'text-left'
                    },
                    // 8) keperluan
                    {
                        data: 'keperluan',
                        className: 'text-left'
                    },
                ];
            }

            function colSetWithCreate() {
                const createCol = {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-left',
                    render: function(_d, _t, row) {
                        const url = buildCreateUrl(row);
                        return `<a href="${url}" class="inline-flex items-center rounded bg-indigo-600 px-6 py-2 text-base font-semibold text-white transition-colors duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <i class="fas fa-plus"></i>
                    </a>`;
                    }
                };
                return [createCol, ...colSetWithoutCreate()];
            }

            // Tables
            const tblMine = $('#tblMine').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100, 250],
                ajax: {
                    url: "{{ route('csjobs.mine.json') }}",
                    type: "GET",
                    data: (d) => {
                        d.doc = docTypeFilter;
                    }
                },
                // indeks: 0 create, 1 doc_no(btn), 2 assigndate, 3 doc_date
                order: [
                    [3, 'desc'],
                    [1, 'desc']
                ],
                columns: colSetWithCreate(),
                searchDelay: 400,
                stateSave: true,
                responsive: true
            });

            const tblAll = $('#tblAll').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100, 250],
                ajax: {
                    url: "{{ route('csjobs.all.json') }}",
                    type: "GET",
                    data: (d) => {
                        d.doc = docTypeFilter;
                    }
                },
                // indeks: 0 doc_no(btn), 1 assigndate, 2 doc_date
                order: [
                    [2, 'desc'],
                    [0, 'desc']
                ],
                columns: colSetWithoutCreate(),
                searchDelay: 400,
                stateSave: true,
                responsive: true
            });

            const tblRevision = $('#tblRevision').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100, 250],
                ajax: {
                    url: "{{ route('csjobs.revision.json') }}",
                    type: "GET",
                    data: (d) => {
                        d.doc = docTypeFilter;
                    }
                },
                order: [
                    [3, 'desc'],
                    [1, 'desc']
                ],
                columns: colSetWithCreate(),
                searchDelay: 400,
                stateSave: true,
                responsive: true
            });

            const tblSppbjkt = $('#tblSppbjkt').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100, 250],
                ajax: {
                    url: "{{ route('csjobs.sppbjkt.progress.json') }}",
                    type: "GET",
                    data: (d) => {
                        d.doc = docTypeFilter;
                    }
                },
                order: [
                    [2, 'desc'],
                    [0, 'desc']
                ],
                columns: colSetWithoutCreate(),
                searchDelay: 400,
                stateSave: true,
                responsive: true
            });

            // === ENTRY CS table (TrCS status H & created_by = user login) ===
            const tblEntryCS = $('#tblEntryCS').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100, 250],
                ajax: {
                    url: "{{ route('csjobs.entry.json') }}",
                    type: "GET"
                },
                order: [
                    [1, 'desc'],
                    [0, 'desc']
                ], // csdate desc, csid desc
                columns: [{
                        data: 'csid',
                        className: 'text-left',
                        render: (v, _t, row) =>
                            `<a href="/editcs/${row.eid}" class="rounded px-6 py-2
                        bg-amber-500 text-white hover:bg-amber-600 text-sm font-semibold">
                        ${v}
                        </a>`
                    },
                    {
                        data: 'csdate',
                        className: 'text-center',
                        render: (v) => v ? (isNaN(new Date(v)) ? v : new Date(v).toLocaleDateString(
                            'id-ID')) : ''
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
                        data: 'user_peminta',
                        className: 'text-left',
                        defaultContent: '-'
                    },
                    {
                        data: 'csnote',
                        className: 'text-left',
                        defaultContent: '-'
                    },
                ],
                searchDelay: 400,
                stateSave: true,
                responsive: true
            });



            function fetchCountsForTab(tabKey) {
                $.get("{{ route('csjobs.counts') }}", {
                        tab: tabKey
                    })
                    .done(function(res) {
                        $('#count-all').text(res.all);
                        $('#count-sppb').text(res.sppb);
                        $('#count-sppj').text(res.sppj);
                        $('#count-sppk').text(res.sppk);
                        $('#count-sppt').text(res.sppt);
                    })
                    .fail(function() {
                        // optional: toastr.warning('Gagal mengambil count.');
                    });
            }


            // Tabs switching
            function setActiveTab(key) {
                $('.tab-btn').removeClass(
                    'active bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300');
                $(`.tab-btn[data-tab="${key}"]`).addClass(
                    'active bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300');
                $('.tab-pane').addClass('hidden').removeClass('block');
                $(`#tab-${key}`).removeClass('hidden').addClass('block');
                if (key === 'mine') {
                    tblMine.columns.adjust();
                }
                if (key === 'entry') {
                    tblEntryCS.columns.adjust();
                } // <— NEW
                if (key === 'all') {
                    tblAll.columns.adjust();
                }
                if (key === 'revision') {
                    tblRevision.columns.adjust();
                }
                if (key === 'sppbjkt') {
                    tblSppbjkt.columns.adjust();
                }

                fetchCountsForTab(key);
            }

            $('.tab-btn').on('click', function() {
                setActiveTab($(this).data('tab'));
            });
            setActiveTab('mine');
        });
    </script>

    <script>
        document.querySelectorAll(".tab-btn").forEach((btn) => {
            btn.addEventListener("click", function() {
                document.querySelectorAll(".tab-btn").forEach((b) => {
                    b.classList.remove("active", "border-b-2", "border-indigo-600",
                        "text-indigo-700", "dark:text-indigo-300");
                });
                this.classList.add("active", "border-b-2", "border-indigo-600", "text-indigo-700",
                    "dark:text-indigo-300");
            });
        });
    </script>



</x-app-layout>
