<x-app-layout>
    <style>
        /* Active / Selected state */
        .scope-filter.active .scope-card {
            transform: scale(1.02);
        }

        /* Issue New Jobs */
        .scope-filter[data-scope="issuejobsnew"].active .scope-card {
            background-color: rgb(254 215 170);
            border-color: rgb(194 65 12);
            color: rgb(194 65 12);
        }

        /* Issue Jobs */
        .scope-filter[data-scope="issuejobs"].active .scope-card {
            background-color: rgb(221 214 254);
            border-color: rgb(91 33 182);
            color: rgb(91 33 182);
        }

        /* SPPB Jobs */
        .scope-filter[data-scope="onprogress"].active .scope-card {
            background-color: rgb(191 219 254);
            border-color: rgb(29 78 216);
            color: rgb(29 78 216);
        }

        /* Issue On Progress */
        .scope-filter[data-scope="issueprogress"].active .scope-card {
            background-color: rgb(254 202 202);
            border-color: rgb(185 28 28);
            color: rgb(185 28 28);
        }

        /* SPPB On Progress */
        .scope-filter[data-scope="sppbprogress"].active .scope-card {
            background-color: rgb(254 249 195);
            border-color: rgb(133 77 14);
            color: rgb(133 77 14);
        }

        .no-border { border: none !important; }
        .grid { width: 100%; }

        select, textarea, input { width: 100%; }
        table.dataTable { width: 100% !important; }
        .dataTables_wrapper { width: 100%; }

        @media (max-width: 600px) {
            .dataTables_wrapper { padding: 0 10px; }
        }

        /* === Filter Section === */
        #issueTable_filter {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }

        #issueTable_filter label { margin-right: 2px; }

        #issueTable_filter input {
            width: auto;
            padding: 0.25rem 0.5rem;
            min-width: 80px;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
        }

        #issueTable_wrapper { width: 100%; }

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

        #issueTable_info { margin: 10px 0; }
        .dataTables_paginate { margin: 10px 0; }

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
        #issueTable tbody tr.collapsed-group-row { display: none; }

        #issueTable tr.group-row {
            background-color: #e6e6e6;
            font-weight: bold;
            cursor: pointer;
            user-select: none;
            color: #333;
        }

        #issueTable tr.group-row:hover { background-color: #d4d4d4; }

        #issueTable tr.group-row .fas {
            margin-right: 8px;
            width: 16px;
            text-align: center;
        }

        #issueTable tr.group-row td {
            padding: 10px !important;
            border-bottom: 1px solid #ddd;
        }

        #issueTable tr.group-row td:first-child { border-left: none; }

        /* Switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 22px;
        }

        .switch input { opacity: 0; width: 0; height: 0; }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
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

        input:checked + .slider { background-color: #4CAF50; }
        input:checked + .slider:before { transform: translateX(18px); }
    </style>

    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">

            {{-- 1. Issue New Jobs (TrSPB) --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="issuejobsnew">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">🆕</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-base font-medium leading-tight">
                                Issue New Jobs
                            </p>
                        </div>

                        <p class="shrink-0 text-xl font-bold">{{ $issuejobsnew }}</p>
                    </div>
                </a>
            </button>

            {{-- 2. Issue Jobs (TrSPB) --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="issuejobs">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">📦</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-base font-medium leading-tight">
                                Issue Jobs
                            </p>
                        </div>

                        <p class="shrink-0 text-xl font-bold">{{ $issuejobs }}</p>
                    </div>
                </a>
            </button>

            {{-- 3. SPPB Jobs (TrSPB) --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="onprogress">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">📑</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-base font-medium leading-tight">
                                SPPB Jobs
                            </p>
                        </div>

                        <p class="shrink-0 text-xl font-bold">{{ $sppbjobs }}</p>
                    </div>
                </a>
            </button>

            {{-- 4. Issue On Progress (TrIssue) --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="issueprogress">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">⏳</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-base font-medium leading-tight">
                                Issue On Progress
                            </p>
                        </div>

                        <p class="shrink-0 text-xl font-bold">{{ $issueprogress }}</p>
                    </div>
                </a>
            </button>

            {{-- 5. SPPB On Progress (TrIssue) --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="sppbprogress">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-yellow-700 bg-yellow-200/20 p-3 text-yellow-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-yellow-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">📌</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-base font-medium leading-tight">
                                SPPB On Progress
                            </p>
                        </div>

                        <p class="shrink-0 text-xl font-bold">{{ $sppbprogress }}</p>
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
                    let scope = 'issuejobsnew';
                    const $title = $('h1.text-xl.font-extrabold');
                    const $thead = $('#issueTable thead');

                    const titleMap = {
                        issuejobsnew: 'Issue - New Jobs',
                        issuejobs: 'Issue - Jobs',
                        onprogress: 'SPPB - Jobs',
                        issueprogress: 'Issue - On Progress',
                        sppbprogress: 'SPPB - On Progress',
                    };

                    const spbScopes   = ['issuejobsnew', 'issuejobs', 'onprogress'];
                    const issueScopes = ['issueprogress'];
                    const sppbScopes  = ['sppbprogress'];
                    const allowedScopes = [...spbScopes, ...issueScopes, ...sppbScopes];

                    function scopeType(sc) {
                        if (spbScopes.includes(sc))   return 'spb';
                        if (issueScopes.includes(sc)) return 'issue';
                        if (sppbScopes.includes(sc))  return 'sppb';
                        return 'spb';
                    }

                    function headerFor(sc) {
                        const type = scopeType(sc);
                        if (type === 'spb') {
                            // TrSPB header
                            return `
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">SPB ID</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">SPB Date</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Company</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Keperluan</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Created By</th>
                            `;
                        }
                        if (type === 'issue') {
                            // TrIssue header
                            return `
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Issue ID</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Issue Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Issue Type</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">SPB ID</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Company</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Created By</th>
                            `;
                        }
                        // SPPB (TrSPPB) header
                        return `
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">SPPB ID</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">SPPB Date</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Company</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Request Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                        `;
                    }

                    function renderSpbLink(row) {
                        const label = row.spbid ?? '';
                        const hash = row.spb_eid || row.spb_hash || row.hash || row.id;
                        if (!label) return '';
                        const url = `/showspbs/${encodeURIComponent(hash ?? '')}`;
                        return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-semibold rounded bg-gray-600 text-white hover:bg-gray-700">${label}</a>`;
                    }

                    function renderSppbLink(row) {
                        const label = row.sppbid ?? '';
                        const hash = row.eid || row.sppb_hash || row.hash || row.id;
                        if (!label) return '';
                        const url = `/showsppbs/${encodeURIComponent(hash ?? '')}`; // sesuaikan dengan route detail SPPB-mu
                        return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-semibold rounded bg-gray-600 text-white hover:bg-gray-700">${label}</a>`;
                    }

                    function renderIssueLinkCell(_value, _type, row) {
                        const label = row.issueid ?? '';
                        const hash = row.eid || row.issue_eid || row.issue_hash || row.hash || row.id;

                        if (!label) return '';
                        if (!hash) {
                            return `<span class="inline-flex items-center px-3 py-1.5 text-sm font-semibold rounded bg-gray-400 text-white">${label}</span>`;
                        }

                        const statusRaw = (row.status ?? row.xstatus ?? '').toString().trim().toUpperCase();
                        const creator = (row.created_by ?? row.createdby ?? '').toString();

                        const isRevise = statusRaw === 'D';
                        const isOwner = creator === (currentUser ?? '');
                        if (isRevise && isOwner) {
                            const url = `/editissues/${encodeURIComponent(hash)}`;
                            return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-semibold rounded bg-amber-600 text-white hover:bg-amber-700">${label}</a>`;
                        }
                        const url = `/showissue/${encodeURIComponent(hash)}`;
                        return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-semibold rounded bg-gray-600 text-white hover:bg-gray-700">${label}</a>`;
                    }

                    // function renderPlusCreate(row) {
                    //     const url = `{{ route('issue.create') }}` + `?spbid=${encodeURIComponent(row.spb_eid ?? '')}`;
                    //     return `<a href="${url}" class="inline-flex justify-center items-center px-3 py-1.5 text-sm font-medium text-white rounded bg-blue-600 hover:bg-blue-700">
                    //         <i class="fas fa-plus"></i>
                    //     </a>`;
                    // }
                    function renderIssueCreate(row) {
                        const url = `{{ route('issue.create') }}` + `?spbid=${encodeURIComponent(row.spb_eid ?? '')}`;
                        return `<a href="${url}" class="inline-flex justify-center items-center px-3 py-1.5 text-sm font-medium text-white rounded bg-blue-600 hover:bg-blue-700">
                            <i class="fas fa-plus"></i>
                        </a>`;
                    }

                    function renderSppbCreate(row) {
                        const url = `{{ route('sppb.create') }}` + `?spbid=${encodeURIComponent(row.spb_eid ?? '')}`;
                        return `<a href="${url}" class="inline-flex justify-center items-center px-3 py-1.5 text-sm font-medium text-white rounded bg-amber-600 hover:bg-amber-700">
                            <i class="fas fa-plus"></i>
                        </a>`;
                    }


                    function columnsFor(sc) {
                        const type = scopeType(sc);
                        if (type === 'spb') {
                            const isSppbJobs = (sc === 'onprogress'); // scope SPPB Jobs

                            return [
                                {
                                    data: null,
                                    orderable: false,
                                    searchable: false,
                                    render: (_v, _t, row) => {
                                        // ⬇️ kalau scope SPPB Jobs → pakai route('sppb.create')
                                        if (isSppbJobs) {
                                            return renderSppbCreate(row);
                                        }
                                        // selain itu (Issue New Jobs / Issue Jobs) → route('issue.create')
                                        return renderIssueCreate(row);
                                    }
                                },
                                {
                                    data: 'spbid',
                                    defaultContent: '',
                                    render: (_v, _t, row) => renderSpbLink(row)
                                },
                                {
                                    data: 'spbdate',
                                    defaultContent: '',
                                    render: (value, _t, row) => value || row.spbdate_fmt || '',
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

                        if (type === 'issue') {
                            return [
                                {
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
                        // SPPB (sppbprogress)
                        return [
                            {
                                data: 'sppbid',
                                defaultContent: '',
                                render: (_v, _t, row) => renderSppbLink(row)
                            },
                            {
                                data: 'sppbdate',
                                defaultContent: '',
                                render: (_v, _t, row) => row.sppbdate_fmt ?? row.sppbdate ?? '',
                                className: 'text-center'
                            },
                            {
                                data: 'cpny_id',
                                defaultContent: '',
                                className: 'text-center'
                            },
                            {
                                data: 'department_id',
                                defaultContent: '',
                                className: 'text-center'
                            },
                            {
                                data: 'requesttype_name',
                                defaultContent: ''
                            },
                            {
                                data: 'keperluan',
                                defaultContent: ''
                            },
                            {
                                data: 'status',
                                defaultContent: ''
                            },
                        ];
                    }

                    function orderFor(sc) {
                        const type = scopeType(sc);
                        if (type === 'spb') {
                            return [
                                [2, 'desc'], // spbdate
                                [1, 'desc']  // spbid
                            ];
                        }
                        if (type === 'issue') {
                            return [
                                [1, 'desc'], // issuedate
                                [0, 'desc']  // issueid
                            ];
                        }
                        // sppb
                        return [
                            [1, 'desc'], // sppbdate
                            [0, 'desc']  // sppbid
                        ];
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
                                url: "{{ route('spbjobs.json') }}",
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
                    if (savedScope && allowedScopes.includes(savedScope)) {
                        scope = savedScope;
                    }

                    updateTitle(scope);
                    highlightActive(scope);
                    rebuild(scope);

                    // switch scope
                    $('.scope-filter').on('click', function(e) {
                        e.preventDefault();
                        scope = $(this).data('scope') || 'issuejobsnew';
                        if (!allowedScopes.includes(scope)) scope = 'issuejobsnew';
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
