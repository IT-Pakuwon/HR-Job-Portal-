<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">

            {{-- 1. Issue New Jobs (TrSPB) --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="issuejobsnew">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🆕</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium leading-tight">
                                Issue New Jobs
                            </p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $issuejobsnew }}</p>
                    </div>
                </a>
            </button>

            {{-- 2. Issue Jobs (TrSPB) --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="issuejobs">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📦</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium leading-tight">
                                Issue Jobs
                            </p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $issuejobs }}</p>
                    </div>
                </a>
            </button>

            {{-- 3. SPPB Jobs (TrSPB) --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="onprogress">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📑</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium leading-tight">
                                SPPB Jobs
                            </p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $sppbjobs }}</p>
                    </div>
                </a>
            </button>

            {{-- 4. Issue On Progress (TrIssue) --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="issueprogress">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⏳</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium leading-tight">
                                Issue On Progress
                            </p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $issueprogress }}</p>
                    </div>
                </a>
            </button>

            {{-- 5. SPPB On Progress (TrIssue) --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="sppbprogress">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-yellow-700 bg-yellow-200/20 p-3 text-yellow-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-yellow-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📌</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium leading-tight">
                                SPPB On Progress
                            </p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $sppbprogress }}</p>
                    </div>
                </a>
            </button>

        </div>
        <div class="mt-6 rounded-xl bg-white dark:bg-gray-800">
            <div
                class="flex flex-col items-start justify-between gap-4 border-b border-gray-200 p-4 sm:flex-row sm:items-center dark:border-gray-700">
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Issue</h1>
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
    </div>


    <script>
        const currentUser = @json(auth()->user()->username ?? '');

        $(function() {
            let scope = 'issuejobsnew';
            const $title = $('h1.text-base.font-extrabold');
            const $thead = $('#issueTable thead');
            const dtControlColumn = {
                data: null,
                className: 'dtr-control',
                orderable: false,
                searchable: false,
                defaultContent: ''
            };
            const titleMap = {
                issuejobsnew: 'Issue - New Jobs',
                issuejobs: 'Issue - Jobs',
                onprogress: 'SPPB - Jobs',
                issueprogress: 'Issue - On Progress',
                sppbprogress: 'SPPB - On Progress',
            };

            const spbScopes = ['issuejobsnew', 'issuejobs', 'onprogress'];
            const issueScopes = ['issueprogress'];
            const sppbScopes = ['sppbprogress'];
            const allowedScopes = [...spbScopes, ...issueScopes, ...sppbScopes];

            function scopeType(sc) {
                if (spbScopes.includes(sc)) return 'spb';
                if (issueScopes.includes(sc)) return 'issue';
                if (sppbScopes.includes(sc)) return 'sppb';
                return 'spb';
            }

            function headerFor(sc) {
                const type = scopeType(sc);
                if (type === 'spb') {
                    const isSppbJobs = (sc === 'onprogress');
                    return `
                    <th></th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">SPB ID</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">SPB Date</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Company</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Keperluan</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Created By</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                ${isSppbJobs ? 'Status SPPB' : 'Status Issue'}
                                </th>
                            `;
                }

                if (type === 'issue') {
                    // TrIssue header
                    return `
                    <th></th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Issue ID</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Issue Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Issue Type</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">SPB ID</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Company</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Created By</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                                `;

                }
                // SPPB (TrSPPB) header
                return `
                <th></th>
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
                return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold rounded bg-gray-600 text-white hover:bg-gray-700">${label}</a>`;
            }

            function renderSppbLink(row) {
                const label = row.sppbid ?? '';
                const hash = row.eid || row.sppb_hash || row.hash || row.id;
                if (!label) return '';
                const url = `/showsppbs/${encodeURIComponent(hash ?? '')}`; // sesuaikan dengan route detail SPPB-mu
                return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold rounded bg-gray-600 text-white hover:bg-gray-700">${label}</a>`;
            }

            function renderIssueLinkCell(_value, _type, row) {
                const label = row.issueid ?? '';
                const hash = row.eid || row.issue_eid || row.issue_hash || row.hash || row.id;

                if (!label) return '';
                if (!hash) {
                    return `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded bg-gray-400 text-white">${label}</span>`;
                }

                const statusRaw = (row.status ?? row.xstatus ?? '').toString().trim().toUpperCase();
                const creator = (row.created_by ?? row.createdby ?? '').toString();

                const isRevise = statusRaw === 'D';
                const isOwner = creator === (currentUser ?? '');
                if (isRevise && isOwner) {
                    const url = `/editissues/${encodeURIComponent(hash)}`;
                    return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold rounded bg-amber-600 text-white hover:bg-amber-700">${label}</a>`;
                }
                const url = `/showissue/${encodeURIComponent(hash)}`;
                return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold rounded bg-gray-600 text-white hover:bg-gray-700">${label}</a>`;
            }

            // function renderPlusCreate(row) {
            //     const url = `{{ route('issue.create') }}` + `?spbid=${encodeURIComponent(row.spb_eid ?? '')}`;
            //     return `<a href="${url}" class="inline-flex justify-center items-center px-3 py-1.5 text-xs font-medium text-white rounded bg-blue-600 hover:bg-blue-700">
        //         <i class="fas fa-plus"></i>
        //     </a>`;
            // }
            function renderIssueCreate(row) {
                const url = `{{ route('issue.create') }}` + `?spbid=${encodeURIComponent(row.spb_eid ?? '')}`;
                return `<a href="${url}" class="inline-flex justify-center items-center px-3 py-1.5 text-xs font-medium text-white rounded bg-blue-600 hover:bg-blue-700">
                            <i class="fas fa-plus"></i>
                        </a>`;
            }

            function renderSppbCreate(row) {
                const url = `{{ route('sppb.create') }}` + `?spbid=${encodeURIComponent(row.spb_eid ?? '')}`;
                return `<a href="${url}" class="inline-flex justify-center items-center px-3 py-1.5 text-xs font-medium text-white rounded bg-amber-600 hover:bg-amber-700">
                            <i class="fas fa-plus"></i>
                        </a>`;
            }


            function columnsFor(sc) {
                const type = scopeType(sc);
                if (type === 'spb') {
                    const isSppbJobs = (sc === 'onprogress'); // scope SPPB Jobs

                    return [dtControlColumn,
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
                        {
                            data: null,
                            defaultContent: '',
                            render: (_v, _t, row) => {
                                const isSppbJobs = (sc === 'onprogress');
                                const val = isSppbJobs ? (row.status_sppb ?? '-') : (row.status_issue ??
                                    '-');

                                const map = {
                                    'Open': 'bg-gray-200/50 text-gray-700',
                                    'Partial': 'bg-amber-200/50 text-amber-700',
                                    'Completed': 'bg-green-200/50 text-green-700',
                                    'Full': 'bg-green-200/50 text-green-700',
                                };
                                const cls = map[val] || 'bg-gray-200/50 text-gray-700';
                                return `<span class="inline-block ${cls} font-semibold px-3 py-1.5 text-xs text-center rounded">${val}</span>`;
                            }
                        }

                    ];
                }

                if (type === 'issue') {
                    return [dtControlColumn, {
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
                        {
                            data: 'status',
                            defaultContent: '',
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
                                    t: (data || '-'),
                                    c: 'bg-gray-300/30 text-gray-600'
                                };
                                return `<span class="w-32 inline-block ${it.c} font-semibold px-3 py-1.5 text-sm text-center rounded">${it.t}</span>`;
                            }
                        }

                    ];
                }
                // SPPB (sppbprogress)
                return [dtControlColumn, {
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
                const type = scopeType(sc);
                if (type === 'spb') {
                    return [
                        [3, 'desc'], // spbdate
                        [2, 'desc'] // spbid
                    ];
                }
                if (type === 'issue') {
                    return [
                        [2, 'desc'], // issuedate
                        [1, 'desc'] // issueid
                    ];
                }
                // sppb
                return [
                    [2, 'desc'], // sppbdate
                    [1, 'desc'] // sppbid
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
                    responsive: {
                        details: {
                            type: 'column',
                            target: 0 // 👈 this is REQUIRED
                        }
                    },

                    columnDefs: [{
                        targets: 0,
                        className: 'dtr-control',
                        orderable: false
                    }],

                    // 🔥 ADD THIS
                    dom: '<"dt-toolbar"l B f>rtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'Purchase_Order',
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
                            title: 'Purchase_Order',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        }
                    ],
                    // 🔥 END ADD
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
</x-app-layout>
