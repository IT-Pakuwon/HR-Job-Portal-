<x-app-layout>
    <x-spp-list-styles />
    <x-spp-list-scripts />

    @php
        $hasIssueAllAccess = auth()->user()->isAdmin();

        $xlCols = 6;

        if ($hasIssueAllAccess) {
            $xlCols++; // Issue All
        }
    @endphp
    <div class="max-w-9xl mx-auto w-full p-2">
        @php
            $statusCards = [
                [['data-scope' => 'returnjobs'], 'Return Issue', '↩️', 'purple', $returnjobs, false, null, 'scope-filter'],
                [['data-scope' => 'all'], 'All', '🧾', 'all', $all, false, null, 'scope-filter'],
                [['data-scope' => 'onprogress'], 'On Progress', '⏳', 'yellow', $onProgress, false, null, 'scope-filter'],
                [['data-scope' => 'revise'], 'Revise', '🛠️', 'blue', $revise, false, null, 'scope-filter'],
                [['data-scope' => 'rejected'], 'Rejected', '❌', 'red', $rejected, false, null, 'scope-filter'],
                [['data-scope' => 'completed'], 'Completed', '✅', 'green', $completed, false, null, 'scope-filter'],
                ...($hasIssueAllAccess ? [[['data-scope' => 'issueall'], 'Issue All', '📊', 'purple', $issueAll, false, null, 'scope-filter']] : []),
            ];
        @endphp
        <x-status-filter-cards :custom="$statusCards" />
        <div
            class="dt-pretty mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="Issue" subtitle="Warehouse issues and return issues" title-id="pageTitle"></x-list-page-header>

            <div class="relative overflow-hidden">
                <table id="issueTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead
                        class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
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
            const $title = $('#pageTitle');
            const $thead = $('#issueTable thead');

            const titleMap = {
                returnjobs: 'Issue - Return Jobs',
                onprogress: 'Issue - On Progress',
                rejected: 'Issue - Rejected',
                revise: 'Issue - Revise',
                completed: 'Issue - Completed',
                all: 'Issue - All',
                issueall: 'Issue - All (Active)',
            };


            function headerFor(sc) {

                if (sc === 'returnjobs') {
                    return `
                    <th></th>
                                <th class="px-4 py-3 text-left font-medium">Action</th>
                                <th class="px-4 py-3 text-left font-medium">Issue ID</th>
                                <th class="px-4 py-3 text-center font-medium">Issue Date</th>
                                <th class="px-4 py-3 text-left font-medium">Issue Type</th>
                                <th class="px-4 py-3 text-left font-medium">SPB ID</th>
                                <th class="px-4 py-3 text-center font-medium">Company</th>
                                <th class="px-4 py-3 text-left font-medium">Created By</th>
                                <th class="px-4 py-3 text-left font-medium">Status</th>
                            `;
                }

                // Default scopes
                return `
                <th></th>
                            <th class="px-4 py-3 text-left font-medium">Issue ID</th>
                            <th class="px-4 py-3 text-center font-medium">Issue Date</th>
                            <th class="px-4 py-3 text-left font-medium">Issue Type</th>
                            <th class="px-4 py-3 text-left font-medium">SPB ID</th>
                            <th class="px-4 py-3 text-center font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Created By</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
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
                    return `<a href="${url}" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-amber-800 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-amber-500 hover:bg-amber-500 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:-rotate-6 group-hover/doc:scale-125 group-hover/doc:opacity-100"><i class="fa-solid fa-pen text-[10px]"></i></span><span>${label}</span></a>`;
                }
                const url = `/showissue/${encodeURIComponent(hash)}`;
                return `<a href="${url}" target="_blank" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:-rotate-6 group-hover/doc:scale-125 group-hover/doc:opacity-100"><i class="fa-regular fa-file-lines"></i></span><span>${label}</span></a>`;
            }

            function renderReturnPlusButton(row) {
                const issueHash = row.issue_eid ?? row.eid ?? row.hash ?? row.id;
                const url = `{{ route('issue.return.create') }}` + `?id=${encodeURIComponent(issueHash)}`;

                return `
                            <a href="${url}"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-purple-100 bg-purple-50 text-sm font-bold text-purple-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-purple-600 hover:bg-purple-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90">
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
                                        c: 'bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-500/30'
                                    },
                                    'P': {
                                        t: 'On Progress',
                                        c: 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:ring-yellow-500/30'
                                    },
                                    'C': {
                                        t: 'Completed',
                                        c: 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30'
                                    },
                                    'X': {
                                        t: 'Cancel',
                                        c: 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30'
                                    },
                                    'R': {
                                        t: 'Rejected',
                                        c: 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30'
                                    },
                                };
                                const it = map[data] || {
                                    t: data || '-',
                                    c: 'bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-500/30'
                                };
                                return `<span class="inline-flex min-w-[116px] items-center justify-center whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ${it.c}">${it.t}</span>`;
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
                                    c: 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30'
                                },
                                'P': {
                                    t: 'On Progress',
                                    c: 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:ring-yellow-500/30'
                                },
                                'C': {
                                    t: 'Completed',
                                    c: 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30'
                                },
                                'X': {
                                    t: 'Cancel',
                                    c: 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30'
                                },
                                'R': {
                                    t: 'Rejected',
                                    c: 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30'
                                },
                            };
                            const it = map[data] || {
                                t: data || '-',
                                c: 'bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-500/30'
                            };
                            return `<span class="inline-flex min-w-[116px] items-center justify-center whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ${it.c}">${it.t}</span>`;
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
                            text: '<i class="fa-solid fa-file-excel"></i> Excel',
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
                            text: '<i class="fa-solid fa-file-csv"></i> CSV',
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
