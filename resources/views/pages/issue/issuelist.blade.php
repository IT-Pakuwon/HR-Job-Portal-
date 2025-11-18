<x-app-layout>
    <style>
        /* Active / Selected state */
        .scope-filter.active .scope-card {
            transform: scale(1.02);
        }

        /* Issue Jobs */
        .scope-filter[data-scope="issuejobs"].active .scope-card {
            background-color: rgb(254 215 170);
            border-color: rgb(194 65 12);
            color: rgb(194 65 12);
        }

        /* On Progress */
        .scope-filter[data-scope="onprogress"].active .scope-card {
            background-color: rgb(191 219 254);
            border-color: rgb(29 78 216);
            color: rgb(29 78 216);
        }

        /* Completed */
        .scope-filter[data-scope="completed"].active .scope-card {
            background-color: rgb(187 247 208);
            border-color: rgb(21 128 61);
            color: rgb(21 128 61);
        }

        /* All */
        .scope-filter[data-scope="all"].active .scope-card {
            background-color: rgb(229 231 235);
            border-color: rgb(31 41 55);
            color: rgb(31 41 55);
        }

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

        /* === Filter Section === */
        #issueTable_filter {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }

        #issueTable_filter label {
            margin-right: 2px;
        }

        #issueTable_filter input {
            width: auto;
            padding: 0.25rem 0.5rem;
            min-width: 80px;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
        }

        #issueTable_wrapper {
            width: 100%;
        }

        #issueTable td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding: 10px;
            max-width: 200px;
        }

        #issueTable th {
            padding: 10px;
            max-width: 200px;
        }

        #issueTable_length {
            width: auto;
            display: flex;
            justify-content: flex-start;
        }

        #issueTable_length select {
            width: auto;
            padding: 0.25rem 0.5rem;
            min-width: 80px;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
        }

        #issueTable_info {
            margin: 10px 0;
        }

        .dataTables_paginate {
            margin: 10px 0;
        }

        #issueTable tbody tr {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        #issueTable tbody tr:hover {
            background-color: #8f8f8f11;
            cursor: pointer;
        }

        #issueTable tbody tr td {
            padding: 8px;
            line-height: 2;
        }

        #issueTable th:nth-child(1),
        #issueTable td:nth-child(1),
        #issueTable th:nth-child(4),
        #issueTable td:nth-child(4) {
            width: 120px;
            text-align: center;
        }

        /* Group row (optional style kept) */
        #issueTable tbody tr.collapsed-group-row {
            display: none;
        }

        #issueTable tr.group-row {
            background-color: #e6e6e6;
            font-weight: bold;
            cursor: pointer;
            user-select: none;
            color: #333;
        }

        #issueTable tr.group-row:hover {
            background-color: #d4d4d4;
        }

        #issueTable tr.group-row .fas {
            margin-right: 8px;
            width: 16px;
            text-align: center;
        }

        #issueTable tr.group-row td {
            padding: 10px !important;
            border-bottom: 1px solid #ddd;
        }

        #issueTable tr.group-row td:first-child {
            border-left: none;
        }

        /* Switch */
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

    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-7">


            {{-- Issue Jobs --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="issuejobs">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">📦</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-base font-medium leading-tight">Issue Jobs</p>
                        </div>

                        <p class="shrink-0 text-xl font-bold">{{ $issuejobs }}</p>
                    </div>
                </a>
            </button>

            {{-- Return Jobs --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="returnjobs">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">↩️</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-base font-medium leading-tight">Return Jobs</p>
                        </div>

                        <p class="shrink-0 text-xl font-bold">{{ $returnjobs }}</p>
                    </div>
                </a>
            </button>

            {{-- On Progress --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="onprogress">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">⏳</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-base font-medium leading-tight">On Progress</p>
                        </div>

                        <p class="shrink-0 text-xl font-bold">{{ $onProgress }}</p>
                    </div>
                </a>
            </button>

            {{-- Rejected --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="rejected">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">❌</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-base font-medium leading-tight">Rejected</p>
                        </div>

                        <p class="shrink-0 text-xl font-bold">{{ $rejected }}</p>
                    </div>
                </a>
            </button>

            {{-- Revise --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="revise">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-yellow-700 bg-yellow-200/20 p-3 text-yellow-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-yellow-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">🛠️</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-base font-medium leading-tight">Revise</p>
                        </div>

                        <p class="shrink-0 text-xl font-bold">{{ $revise }}</p>
                    </div>
                </a>
            </button>

            {{-- Completed --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="completed">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">✅</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-base font-medium leading-tight">Completed</p>
                        </div>

                        <p class="shrink-0 text-xl font-bold">{{ $completed }}</p>
                    </div>
                </a>
            </button>

            {{-- All --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="all">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-lg active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">🧾</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-base font-medium leading-tight">All</p>
                        </div>

                        <p class="shrink-0 text-xl font-bold">{{ $all }}</p>
                    </div>
                </a>
            </button>

        </div>


        <div class="grid">
            <div class="mt-6 rounded-2xl bg-white dark:bg-gray-800">
                <div
                    class="flex flex-col items-start justify-between gap-4 border-b border-gray-200 p-4 sm:flex-row sm:items-center dark:border-gray-700">
                    <h1 class="text-xl font-extrabold text-gray-700 dark:text-white">Issue</h1>
                </div>

                <div class="overflow-x-auto p-6">
                    <table id="issueTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr id="thead-row"></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>
                </div>
            </div>

            <script>
                const currentUser = @json(auth()->user()->username ?? '');

                $(function() {
                    let scope = 'issuejobs';
                    const $title = $('h1.text-xl.font-extrabold');
                    const $thead = $('#issueTable thead');

                    const titleMap = {
                        issuejobs: 'Issue - Jobs',
                        returnjobs: 'Issue - Return Jobs',
                        onprogress: 'Issue - On Progress',
                        rejected: 'Issue - Rejected',
                        revise: 'Issue - Revise',
                        completed: 'Issue - Completed',
                        all: 'Issue - All',
                    };

                    function headerFor(sc) {
                        if (sc === 'issuejobs') {
                            return `
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">SPB ID</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">SPB Date</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Company</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Keperluan</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Created By</th>
                            `;
                        }
                        // scopes TrIssue (6 kolom)
                        return `
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Issue ID</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Issue Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Issue Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">SPB ID</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Company</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Created By</th>
                        `;
                    }

                    function renderSpbLink(row) {
                        const label = row.spbid ?? '';
                        const hash = row.spb_eid || row.spb_hash || row.hash || row.id;
                        if (!label) return '';
                        const url = `/showspbs/${encodeURIComponent(hash ?? '')}`;
                        return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-semibold rounded bg-gray-600 text-white hover:bg-gray-700">${label}</a>`;
                    }

                    function renderIssueLinkCell(_value, _type, row) {
                        const label = row.issueid ?? '';
                        const hash = row.eid || row.issue_eid || row.issue_hash || row.hash || row.id;

                        if (!label) return '';
                        if (!hash) {
                            // aman meski hash tidak ada
                            return `<span class="inline-flex items-center px-3 py-1.5 text-sm font-semibold rounded bg-gray-400 text-white">${label}</span>`;
                        }

                        const statusRaw = (row.status ?? row.xstatus ?? '').toString().trim().toUpperCase();
                        const creator = (row.created_by ?? row.createdby ?? '').toString();

                        const isRevise = statusRaw === 'D';
                        const isOwner = creator === (currentUser ?? '');
                        console.log({
                            statusRaw,
                            isRevise,
                            creator,
                            currentUser,
                            isOwner
                        });

                        if (isRevise && isOwner) {
                            const url = `/editissues/${encodeURIComponent(hash)}`;
                            return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-semibold rounded bg-amber-600 text-white hover:bg-amber-700">${label}</a>`;
                        }
                        const url = `/showissue/${encodeURIComponent(hash)}`;
                        return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-semibold rounded bg-gray-600 text-white hover:bg-gray-700">${label}</a>`;
                    }

                    function columnsFor(sc) {
                        if (sc === 'issuejobs') {
                            return [{
                                    data: null,
                                    orderable: false,
                                    searchable: false,
                                    render: (_v, t, row) => renderPlusCreate(row)
                                },
                                {
                                    data: 'spbid',
                                    defaultContent: '',
                                    render: (_v, _t, row) => renderSpbLink(row)
                                },
                                {
                                    data: 'spbdate',
                                    defaultContent: '',
                                    render: (_v, _t, row) => row.spbdate_fmt ?? row.spbdate ?? '',
                                    className: 'text-center'
                                },
                                {
                                    data: 'cpny_id',
                                    defaultContent: '',
                                    className: 'text-center'
                                },
                                {
                                    data: 'keperluan',
                                    defaultContent: ''
                                },
                                {
                                    data: 'created_by',
                                    defaultContent: ''
                                },
                            ];
                        }
                        // TrIssue scopes (6 kolom)
                        return [{
                                data: 'issueid',
                                defaultContent: '',
                                render: renderIssueLinkCell
                            },
                            {
                                data: 'issuedate',
                                defaultContent: '',
                                render: (_v, _t, row) => row.issuedate_fmt ?? row.issuedate ?? '',
                                className: 'text-center'
                            },
                            {
                                data: 'issuetype',
                                defaultContent: ''
                            },
                            {
                                data: 'spbid',
                                defaultContent: ''
                            },
                            {
                                data: 'cpny_id',
                                defaultContent: '',
                                className: 'text-center'
                            },
                            {
                                data: 'created_by',
                                defaultContent: ''
                            },
                        ];
                    }

                    function renderPlusCreate(row) {
                        const url = `{{ route('issue.create') }}` + `?spbid=${encodeURIComponent(row.spbid ?? '')}`;
                        return `<a href="${url}" class="inline-flex justify-center items-center px-3 py-1.5 text-sm font-medium text-white rounded bg-blue-600 hover:bg-blue-700">
                            <i class="fas fa-plus"></i>
                        </a>`;
                    }

                    function orderFor(sc) {
                        if (sc === 'issuejobs') return [
                            [2, 'desc'],
                            [1, 'desc']
                        ]; // spbdate desc, spbid desc
                        return [
                            [1, 'desc'],
                            [0, 'desc']
                        ]; // issuedate desc, issueid desc
                    }

                    function updateTitle(sc) {
                        $title.text(titleMap[sc] ?? 'Issue');
                    }

                    function highlightActive(sc) {
                        $('.scope-filter').removeClass('active')
                            .each(function() {
                                if ($(this).data('scope') === sc) $(this).addClass('active');
                            });
                    }

                    function resetThead(sc) {
                        $thead.empty().append('<tr id="thead-row"></tr>');
                        $('#thead-row').html(headerFor(sc));
                    }

                    function rebuild(sc) {
                        if ($.fn.DataTable.isDataTable('#issueTable')) {
                            $('#issueTable').DataTable().clear().destroy();
                        }
                        resetThead(sc);

                        $('#issueTable').DataTable({
                            processing: true,
                            serverSide: true,
                            deferRender: true,
                            pageLength: 25,
                            lengthMenu: [10, 25, 50, 100, 250],
                            order: orderFor(sc),
                            ajax: {
                                url: "{{ route('issuelist.json') }}",
                                type: "GET",
                                data: function(d) {
                                    d.scope = sc;
                                }
                            },
                            columns: columnsFor(sc),
                            searchDelay: 400,
                            stateSave: false,
                            responsive: true
                        });
                    }

                    // init
                    const savedScope = localStorage.getItem('activeIssueScope');
                    if (savedScope) scope = savedScope;

                    updateTitle(scope);
                    highlightActive(scope);
                    rebuild(scope);

                    // switch scope
                    $('.scope-filter').on('click', function(e) {
                        e.preventDefault();
                        scope = $(this).data('scope') || 'issuejobs';
                        localStorage.setItem('activeIssueScope', scope);
                        updateTitle(scope);
                        highlightActive(scope);
                        rebuild(scope);
                    });
                });
            </script>
        </div>
    </div>
</x-app-layout>
