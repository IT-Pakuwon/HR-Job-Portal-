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
    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-5">
            <div
                class="flex items-center gap-4 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 dark:border-white dark:text-white">
                <span class="text-xl">✏️</span>
                <div class="flex flex-grow items-center justify-between">
                    <p class="text-lg font-medium">My CS</p>
                    <p id="count-sppk" class="text-right text-xl font-extrabold">{{ $myAll }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600">
                <span class="text-xl">⏳</span>
                <div class="flex flex-grow items-center justify-between">
                    <p class="text-lg font-medium">On Progress</p>
                    <p id="count-sppb" class="text-right text-xl font-extrabold">{{ $myProgress }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600">
                <span class="text-xl">⛔️</span>
                <div class="flex flex-grow items-center justify-between">
                    <p class="text-lg font-medium">Rejected</p>
                    <p id="count-sppj" class="text-right text-xl font-extrabold">{{ $myRejected }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600">
                <span class="text-xl">✅</span>
                <div class="flex flex-grow items-center justify-between">
                    <p class="text-lg font-medium">Completed</p>
                    <p id="count-sppt" class="text-right text-xl font-extrabold">{{ $myCompleted }}</p>
                </div>
            </div>
            <div
                class="flex items-center gap-4 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600">
                <span class="text-xl">📄</span>
                <div class="flex flex-grow items-center justify-between">
                    <p class="text-lg font-medium">All CS</p>
                    <p id="count-all" class="text-right text-xl font-extrabold">{{ $all }}</p>
                </div>
            </div>
        </div>

        <div class="grid">
            <div class="mt-6 rounded-2xl bg-white dark:bg-gray-800">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="flex gap-2 p-4">
                        <button
                            class="tab-btn active border-b-2 border-indigo-600 px-4 py-2 text-xl font-semibold text-indigo-700 dark:text-indigo-300"
                            data-tab="my">My CS</button>
                        <button
                            class="tab-btn border-indigo-600 px-4 py-2 text-xl font-semibold text-indigo-700 dark:text-indigo-300"
                            data-tab="progress">Onprogress CS</button>
                        <button
                            class="tab-btn border-indigo-600 px-4 py-2 text-xl font-semibold text-indigo-700 dark:text-indigo-300"
                            data-tab="rejected">Rejected CS</button>
                        <button
                            class="tab-btn border-indigo-600 px-4 py-2 text-xl font-semibold text-indigo-700 dark:text-indigo-300"
                            data-tab="completed">Completed CS</button>
                        <button
                            class="tab-btn border-indigo-600 px-4 py-2 text-xl font-semibold text-indigo-700 dark:text-indigo-300"
                            data-tab="all">All CS</button>
                    </nav>
                </div>


                <div class="overflow-x-auto p-6">
                    {{-- Table Template --}}
                    @foreach (['my' => 'tblMy', 'progress' => 'tblProgress', 'rejected' => 'tblRejected', 'completed' => 'tblCompleted', 'all' => 'tblAll'] as $tab => $tbl)
                        <div id="tab-{{ $tab }}" class="tab-pane {{ $tab == 'my' ? 'block' : 'hidden' }}">
                            <table id="{{ $tbl }}"
                                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Action</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            SPPB/J/K/T ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            CS Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            User</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Company</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Department</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Created By</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Note</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Assign Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Submit Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                            Days</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
    $(function () {
        function fmtDate(v){
            if(!v) return '';
            const d = new Date(v);
            return Number.isNaN(d.getTime()) ? v : d.toLocaleDateString('id-ID');
        }
        function renderCSBtn(_v,row){
            return `<a href="/showcs/${row.eid}" class="inline-flex items-center rounded px-3 py-1.5 bg-indigo-600 text-white hover:bg-indigo-700 text-sm font-semibold">${row.csid ?? ''}</a>`;
        }
        const showMap = { PB:'showsppbs', PJ:'showsppjs', PK:'showsppks', PT:'showsppts' };
        function renderSPPBtn(_v,row){
            const prefix = (row.sppbjkt_prefix || '').toUpperCase();
            const srcId  = row.sppbjkt_src_id;
            const src_eid  = row.sppbjkid_eid;
            const docNo  = row.sppbjktid || '';
            const base   = showMap[prefix];
            if(!prefix || !srcId || !base) return docNo;
            // const url = `/${base}/${srcId}`;
            const url = `/${base}/${src_eid}`;
            return `<a href="${url}" class="inline-flex items-center rounded px-3 py-1.5 bg-emerald-600 text-white hover:bg-emerald-700 text-sm font-semibold">${docNo}</a>`;
        }
        function renderDays(v){ return (v==null) ? '' : String(v); }

            function renderCSBtn(_v, row) {
                return `<a href="/showcs/${row.eid}" class="rounded px-6 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-700">${row.csid ?? ''}</a>`;
            }
            const showMap = {
                PB: 'showsppbs',
                PJ: 'showsppjs',
                PK: 'showsppks',
                PT: 'showsppts'
            };

            function renderSPPBtn(_v, row) {
                const prefix = (row.sppbjkt_prefix || '').toUpperCase();
                const srcId = row.sppbjkt_src_id;
                const docNo = row.sppbjktid || '';
                const base = showMap[prefix];
                if (!prefix || !srcId || !base) return docNo;
                const url = `/${base}/${srcId}`;
                return `<a href="${url}" class="rounded px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-700">${docNo}</a>`;
            }

            function renderDays(v) {
                return (v == null) ? '' : String(v);
            }

            const commonCols = [{
                    data: 'csid',
                    className: 'text-left',
                    render: (_v, t, row) => renderCSBtn(_v, row)
                },
                {
                    data: 'sppbjktid',
                    className: 'text-left',
                    render: (v, t, row) => renderSPPBtn(v, row)
                },
                {
                    data: 'csdate',
                    className: 'text-left',
                    render: (v) => fmtDate(v)
                },
                {
                    data: 'user_peminta',
                    className: 'text-left',
                    defaultContent: '-'
                },
                {
                    data: 'cpny_id',
                    className: 'text-left'
                },
                {
                    data: 'department_id',
                    className: 'text-left'
                },
                {
                    data: 'created_by',
                    className: 'text-left'
                },
                {
                    data: 'csnote',
                    className: 'text-left',
                    defaultContent: '-'
                },
                {
                    data: 'assigndate',
                    className: 'text-left',
                    render: (v) => fmtDate(v)
                },
                {
                    data: 'submitdate',
                    className: 'text-left',
                    render: (v) => fmtDate(v)
                },
                {
                    data: 'days',
                    className: 'text-lefts',
                    render: (v) => renderDays(v)
                },
            ];

            const opts = {
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100, 250],
                order: [
                    [2, 'desc'],
                    [0, 'desc']
                ],
                columns: commonCols,
                searchDelay: 400,
                stateSave: true,
                responsive: true
            };

            const tblMy = $('#tblMy').DataTable({
                ...opts,
                ajax: {
                    url: "{{ route('cslist.my.json') }}",
                    type: "GET"
                }
            });
            const tblProgress = $('#tblProgress').DataTable({
                ...opts,
                ajax: {
                    url: "{{ route('cslist.onprogress.json') }}",
                    type: "GET"
                }
            });
            const tblRejected = $('#tblRejected').DataTable({
                ...opts,
                ajax: {
                    url: "{{ route('cslist.rejected.json') }}",
                    type: "GET"
                }
            });
            const tblCompleted = $('#tblCompleted').DataTable({
                ...opts,
                ajax: {
                    url: "{{ route('cslist.completed.json') }}",
                    type: "GET"
                }
            });
            const tblAll = $('#tblAll').DataTable({
                ...opts,
                ajax: {
                    url: "{{ route('cslist.all.json') }}",
                    type: "GET"
                }
            });

            function setActiveTab(key) {
                $('.tab-btn').removeClass('active bg-indigo-50 text-indigo-700');
                $(`.tab-btn[data-tab="${key}"]`).addClass('active bg-indigo-50 text-indigo-700');
                $('.tab-pane').addClass('hidden').removeClass('block');
                $(`#tab-${key}`).removeClass('hidden').addClass('block');
                if (key === 'my') tblMy.columns.adjust();
                if (key === 'progress') tblProgress.columns.adjust();
                if (key === 'rejected') tblRejected.columns.adjust();
                if (key === 'completed') tblCompleted.columns.adjust();
                if (key === 'all') tblAll.columns.adjust();
                $.get("{{ route('cslist.counts') }}").done(function(r) {
                    $('#k-myAll').text(r.myAll);
                    $('#k-myProgress').text(r.myProgress);
                    $('#k-myRejected').text(r.myRejected);
                    $('#k-myCompleted').text(r.myCompleted);
                    $('#k-all').text(r.all);
                });
            }
            $('.tab-btn').on('click', function() {
                setActiveTab($(this).data('tab'));
            });
            setActiveTab('my');
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
