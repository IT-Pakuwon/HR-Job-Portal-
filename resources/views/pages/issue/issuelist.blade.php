<x-app-layout>

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6">
            {{-- Return Jobs (paling kiri) --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="returnjobs">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">↩️</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium leading-tight">Return Jobs</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $returnjobs }}</p>
                    </div>
                </a>
            </button>

            {{-- All --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="all">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md dark:border-white dark:text-white dark:hover:bg-gray-700">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🧾</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium leading-tight">All</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $all }}</p>
                    </div>
                </a>
            </button>

            {{-- On Progress --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="onprogress">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⏳</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium leading-tight">On Progress</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $onProgress }}</p>
                    </div>
                </a>
            </button>

            {{-- Revise --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="revise">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-yellow-700 bg-yellow-200/20 p-3 text-yellow-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-yellow-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🛠️</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium leading-tight">Revise</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $revise }}</p>
                    </div>
                </a>
            </button>

            {{-- Rejected --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="rejected">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">❌</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium leading-tight">Rejected</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $rejected }}</p>
                    </div>
                </a>
            </button>

            {{-- Completed --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="completed">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✅</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium leading-tight">Completed</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $completed }}</p>
                    </div>
                </a>
            </button>
        </div>
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Issue</h1>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="issueTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr id="thead-row"></tr>
                    </thead>
                    <tbody>
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        const currentUser = @json(auth()->user()->username ?? '');
        const dtControlColumn = {
            data: null,
            className: 'dtr-control',
            orderable: false,
            searchable: false,
            defaultContent: ''
        };

        $(function() {
            // default scope sekarang 'all'
            let scope = 'all';
            const $title = $('h1.text-base.font-extrabold');
            const $thead = $('#issueTable thead');

            const titleMap = {
                returnjobs: 'Issue - Return Jobs',
                onprogress: 'Issue - On Progress',
                rejected: 'Issue - Rejected',
                revise: 'Issue - Revise',
                completed: 'Issue - Completed',
                all: 'Issue - All',
            };


            function headerFor(sc) {

                if (sc === 'returnjobs') {
                    return `
                    <th></th>
                                <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Issue ID</th>
                                <th class="px-6 py-3 text-center  text-sm  font-semibold uppercase tracking-wider">Issue Date</th>
                                <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Issue Type</th>
                                <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">SPB ID</th>
                                <th class="px-6 py-3 text-center  text-sm  font-semibold uppercase tracking-wider">Company</th>
                                <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Created By</th>
                                <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Status</th>
                            `;
                }

                // Default scopes
                return `
                <th></th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Issue ID</th>
                            <th class="px-6 py-3 text-center  text-sm  font-semibold uppercase tracking-wider">Issue Date</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Issue Type</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">SPB ID</th>
                            <th class="px-6 py-3 text-center  text-sm  font-semibold uppercase tracking-wider">Company</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Created By</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Status</th>
                        `;
            }


            function renderIssueLinkCell(_value, _type, row) {
                const label = row.issueid ?? '';
                const hash = row.eid || row.issue_eid || row.issue_hash || row.hash || row.id;

                if (!label) return '';
                if (!hash) {
                    return `<span class="inline-flex items-center px-3 py-1.5  text-sm  font-semibold rounded bg-gray-400 text-white">${label}</span>`;
                }

                const statusRaw = (row.status ?? row.xstatus ?? '').toString().trim().toUpperCase();
                const creator = (row.created_by ?? row.createdby ?? '').toString();

                const isRevise = statusRaw === 'D';
                const isOwner = creator === (currentUser ?? '');

                if (isRevise && isOwner) {
                    const url = `/editissues/${encodeURIComponent(hash)}`;
                    return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5  text-sm  font-semibold rounded bg-amber-600 text-white hover:bg-amber-700">${label}</a>`;
                }
                const url = `/showissue/${encodeURIComponent(hash)}`;
                return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5  text-sm  font-semibold rounded bg-gray-600 text-white hover:bg-gray-700">${label}</a>`;
            }

            function renderReturnPlusButton(row) {
                const issueHash = row.issue_eid ?? row.eid ?? row.hash ?? row.id;
                const url = `{{ route('issue.return.create') }}` + `?id=${encodeURIComponent(issueHash)}`;

                return `
                            <a href="${url}" 
                            class="inline-flex justify-center items-center px-3 py-1.5  text-sm  font-medium 
                                    text-white rounded bg-purple-600 hover:bg-purple-700">
                                <i class="fas fa-plus"></i>
                            </a>
                        `;
            }



            function columnsFor(sc) {

                // === RETURN JOBS (dengan kolom ACTION paling kiri) ===
                if (sc === 'returnjobs') {
                    return [
                        dtControlColumn,

                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: (_v, _t, row) => renderReturnPlusButton(row)
                        },
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
                            data: 'issuetype'
                        },
                        {
                            data: 'spbid'
                        },
                        {
                            data: 'cpny_id',
                            className: 'text-center'
                        },
                        {
                            data: 'created_by'
                        },
                        {
                            data: 'status',
                            className: 'text-left',
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
                                return `<span class="w-32 inline-block ${it.c} font-semibold px-3 py-1.5 text-sm text-center rounded">${it.t}</span>`;
                            }
                        },
                    ];
                }

                // === DEFAULT UNTUK SCOPES LAIN ===
                return [dtControlColumn,
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
                        data: 'issuetype'
                    },
                    {
                        data: 'spbid'
                    },
                    {
                        data: 'cpny_id',
                        className: 'text-center'
                    },
                    {
                        data: 'created_by'
                    },
                    {
                        data: 'status',
                        className: 'text-left',
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
                            return `<span class="w-32 inline-block ${it.c} font-semibold px-3 py-1.5 text-sm text-center rounded">${it.t}</span>`;
                        }
                    },
                ];
            }


            function orderFor(sc) {
                // issuedate desc, issueid desc
                return [
                    [1, 'desc'],
                    [0, 'desc']
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
                    pageLength: 10,
                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],



                    dom: '<"dt-toolbar"l B f>rtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'List_IssueList',
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
                            title: 'List_IssueList',
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
                scope = $(this).data('scope') || 'all';
                localStorage.setItem('activeIssueScope', scope);
                updateTitle(scope);
                highlightActive(scope);
                rebuild(scope);
            });
        });
    </script>

</x-app-layout>
