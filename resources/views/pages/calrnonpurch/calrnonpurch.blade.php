<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid auto-rows-fr grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6">

            {{-- CALR Jobs --}}
            <button type="button" class="scope-filter group block h-full" data-scope="calrjobs">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">📦</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">CALR Jobs</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $calrjobs }}</p>
                </div>
            </button>

            {{-- On Progress --}}
            <button type="button" class="scope-filter group block h-full" data-scope="onprogress">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">⏳</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">On Progress</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $onProgress }}</p>
                </div>
            </button>

            {{-- Rejected --}}
            <button type="button" class="scope-filter group block h-full" data-scope="rejected">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">❌</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">Rejected</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $rejected }}</p>
                </div>
            </button>

            {{-- Revise --}}
            <button type="button" class="scope-filter group block h-full" data-scope="revise">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-yellow-700 bg-yellow-200/20 p-3 text-yellow-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-yellow-100 hover:shadow-md active:scale-95">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">🛠️</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">Revise</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $revise }}</p>
                </div>
            </button>

            {{-- Completed --}}
            <button type="button" class="scope-filter group block h-full" data-scope="completed">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">✅</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">Completed</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $completed }}</p>
                </div>
            </button>

            {{-- All --}}
            <button type="button" class="scope-filter group block h-full" data-scope="all">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">🧾</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">All</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $all }}</p>
                </div>
            </button>
        </div>

        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">CALR Non Purchase</h1>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="calrTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr id="thead-row"></tr>
                    </thead>
                    <tbody>
                        {{-- DataTables --}}
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
            let scope = 'calrjobs';
            const $title = $('h1.text-base.font-extrabold');
            let table;

            const titleMap = {
                calrjobs: 'CALR Non Purchase - Jobs',
                onprogress: 'CALR Non Purchase - On Progress',
                completed: 'CALR Non Purchase - Completed',
                rejected: 'CALR Non Purchase - Rejected',
                revise: 'CALR Non Purchase - Revise',
                all: 'CALR Non Purchase - All',
            };

            function headerFor(sc) {
                if (sc === 'calrjobs') {
                    return `
                        <th></th>
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Document ID</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Date</th>                  
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Company</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Please Pay To</th>
                        <th class="px-6 py-3 text-right text-sm font-semibold uppercase tracking-wider">Amount RCA</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Created By</th>
                    `;
                }

                return `
                    <th></th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">CALR Non Purchase ID</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">CALR Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Document ID</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Company</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Department</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold uppercase tracking-wider">Amount RCA</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold uppercase tracking-wider">Settlement</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold uppercase tracking-wider">Diff</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Created By</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Status</th>
                `;
            }

            function columnsFor(sc) {
                if (sc === 'calrjobs') {
                    return [
                        dtControlColumn,
                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: (_v, _t, row) => renderPlusCreate(row)
                        },
                        {
                            data: 'rfpnonpurchaseid',
                            render: (_v, _t, row) => renderRfpNonPurchLink(row),
                            className: 'text-left'
                        },
                        {
                            data: 'rfpnonpurchasedate',
                            render: (_v, _t, row) => row.rfpnonpurchasedate_fmt ?? '',
                            className: 'text-left'
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
                            data: 'pleasepayto',
                            className: 'text-left'
                        },
                        {
                            data: 'amountrequestpayment',
                            render: (_v, _t, row) => row.amountrequestpayment_fmt ?? formatMoney(row.amountrequestpayment),
                            className: 'text-right'
                        },
                        {
                            data: 'created_by',
                            className: 'text-left'
                        },
                    ];
                }

                return [
                    dtControlColumn,
                    {
                        data: 'calrnonpurchaseid',
                        render: (_v, _t, row) => renderCalrNonPurchLink(row),
                        className: 'text-left'
                    },
                    {
                        data: 'calrnonpurchasedate',
                        render: (_v, _t, row) => row.calrnonpurchasedate_fmt ?? '',
                        className: 'text-left'
                    },
                    {
                        data: 'rfpnonpurchaseid',
                        render: (_v, _t, row) => renderRfpNonPurchPlainLink(row),
                        className: 'text-left'
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
                        data: 'amountrfp',
                        render: (_v, _t, row) => row.amountrfp_fmt ?? formatMoney(row.amountrfp),
                        className: 'text-right'
                    },
                    {
                        data: 'amountsettlement',
                        render: (_v, _t, row) => row.amountsettlement_fmt ?? formatMoney(row.amountsettlement),
                        className: 'text-right'
                    },
                    {
                        data: 'amountdiff',
                        render: (_v, _t, row) => row.amountdiff_fmt ?? formatMoney(row.amountdiff),
                        className: 'text-right'
                    },
                    {
                        data: 'created_by',
                        className: 'text-left'
                    },
                    {
                        data: 'status',
                        className: 'text-left',
                        render: renderStatusBadge
                    },
                ];
            }

            function orderFor(sc) {
                if (sc === 'calrjobs') {
                    return [
                        [2, 'desc']
                    ];
                }

                return [
                    [1, 'desc']
                ];
            }

            function updateTitle(sc) {
                $title.text(titleMap[sc] ?? 'CALR Non Purchase');
            }

            function resetThead(sc) {
                const $table = $('#calrTable');

                $table.find('thead').remove();

                const theadHtml = `
                    <thead class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr id="thead-row">${headerFor(sc)}</tr>
                    </thead>
                `;

                $table.prepend(theadHtml);

                if ($table.find('tbody').length === 0) {
                    $table.append(`
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    `);
                }
            }

            function rebuild(sc) {
                if ($.fn.DataTable.isDataTable('#calrTable')) {
                    $('#calrTable').DataTable().clear().destroy();
                }

                resetThead(sc);

                table = $('#calrTable').DataTable({
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
                            target: 0
                        }
                    },
                    columnDefs: [{
                        targets: 0,
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false
                    }],
                    dom: '<"dt-toolbar"l B f>rtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'List_CALR_Non_Purchase',
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
                            title: 'List_CALR_Non_Purchase',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        }
                    ],
                    order: orderFor(sc),
                    ajax: {
                        url: "{{ route('calrnonpurch.json') }}",
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

            function renderPlusCreate(row) {
                const rfpHash = row.rfpnonpurchase_eid ?? '';

                const url = `{{ route('calrnonpurch.create') }}` +
                    `?rfpnonpurchase=${encodeURIComponent(rfpHash)}`;

                return `
                    <a href="${url}"
                        class="inline-flex items-center justify-center rounded bg-blue-500 px-4 py-2 text-center text-sm font-medium leading-tight text-white transition-colors duration-200 hover:bg-blue-700">
                        <i class="fas fa-plus"></i>
                    </a>
                `;
            }

            function renderRfpNonPurchLink(row) {
                const label = row.rfpnonpurchaseid ?? '';
                const hash = row.rfpnonpurchase_eid || row.eid || row.hash || row.id;

                if (!label) return '';

                if (!hash) {
                    return `
                        <span class="inline-flex items-center rounded bg-gray-400 px-3 py-1.5 text-sm font-semibold text-white">
                            ${escapeHtml(label)}
                        </span>
                    `;
                }

                const url = `/showcalrnonpurch/${encodeURIComponent(hash)}`;

                return `
                    <a href="${url}" target="_blank"
                        class="inline-flex items-center justify-center rounded bg-gray-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-gray-700">
                        ${escapeHtml(label)}
                    </a>
                `;
            }

            function renderRfpNonPurchPlainLink(row) {
                const label = row.rfpnonpurchaseid ?? '';

                if (!label) return '';

                return `
                    <span class="inline-flex items-center justify-center rounded bg-gray-500 px-3 py-1.5 text-sm font-semibold text-white">
                        ${escapeHtml(label)}
                    </span>
                `;
            }

            function renderCalrNonPurchLink(row) {
                const label = row.calrnonpurchaseid ?? '';
                const hash = row.calrnonpurchase_eid || row.eid || row.hash || row.id;

                if (!label) return '';

                if (!hash) {
                    return `
                        <span class="inline-flex items-center rounded bg-gray-400 px-3 py-1.5 text-sm font-semibold text-white">
                            ${escapeHtml(label)}
                        </span>
                    `;
                }

                const statusRaw = (row.status ?? '').toString().trim().toUpperCase();
                const creator = (row.created_by ?? '').toString();
                const isRevise = statusRaw === 'D';
                const isOwner = creator === (currentUser ?? '');

                if (isRevise && isOwner) {
                    const url = `/editcalrnonpurch/${encodeURIComponent(hash)}`;

                    return `
                        <a href="${url}"
                            class="inline-flex items-center justify-center rounded bg-amber-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-amber-700"
                            title="Edit Revise">
                            ${escapeHtml(label)}
                        </a>
                    `;
                }

                const url = `/showcalrnonpurch/${encodeURIComponent(hash)}`;

                return `
                    <a href="${url}"
                        class="inline-flex items-center justify-center rounded bg-gray-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-gray-700">
                        ${escapeHtml(label)}
                    </a>
                `;
            }

            function renderStatusBadge(data) {
                const map = {
                    'D': {
                        t: 'Revise',
                        c: 'bg-amber-200/60 text-amber-800 border border-amber-600/40'
                    },
                    'P': {
                        t: 'On Progress',
                        c: 'bg-orange-200/60 text-orange-800 border border-orange-600/40'
                    },
                    'C': {
                        t: 'Completed',
                        c: 'bg-green-200/60 text-green-800 border border-green-600/40'
                    },
                    'X': {
                        t: 'Cancel',
                        c: 'bg-red-200/60 text-red-800 border border-red-600/40'
                    },
                    'R': {
                        t: 'Rejected',
                        c: 'bg-red-200/60 text-red-800 border border-red-600/40'
                    },
                };

                const it = map[data] || {
                    t: data || '-',
                    c: 'bg-gray-200/60 text-gray-700 border border-gray-500/40'
                };

                return `
                    <span class="inline-block w-32 rounded px-3 py-1.5 text-center text-sm font-semibold ${it.c}">
                        ${it.t}
                    </span>
                `;
            }

            function formatMoney(value) {
                const num = Number(value || 0);

                return num.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            updateTitle(scope);
            rebuild(scope);

            $('.scope-filter').on('click', function(e) {
                e.preventDefault();

                scope = $(this).data('scope') || 'calrjobs';

                updateTitle(scope);
                rebuild(scope);

                $('.scope-filter').removeClass('active');
                $(this).addClass('active');

                localStorage.setItem('activeCalrNonPurchScope', scope);
            });

            const savedCalrScope = localStorage.getItem('activeCalrNonPurchScope');

            if (savedCalrScope) {
                scope = savedCalrScope;

                updateTitle(scope);
                rebuild(scope);

                $('.scope-filter').removeClass('active');
                $(`.scope-filter[data-scope="${scope}"]`).addClass('active');
            } else {
                $(`.scope-filter[data-scope="calrjobs"]`).addClass('active');
            }
        });
    </script>
</x-app-layout>