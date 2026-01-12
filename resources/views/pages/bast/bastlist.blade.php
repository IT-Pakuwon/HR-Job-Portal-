<x-app-layout>


    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid auto-rows-fr grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6">

            {{-- Bast Jobs --}}
            <button type="button" class="scope-filter group block h-full" data-scope="bastjobs">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-xl">📦</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-base font-medium leading-tight">Bast Jobs</p>
                    </div>

                    <p class="shrink-0 text-xl font-bold">{{ $bastjobs }}</p>
                </div>
            </button>

            {{-- On Progress --}}
            <button type="button" class="scope-filter group block h-full" data-scope="onprogress">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-xl">⏳</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-base font-medium leading-tight">On Progress</p>
                    </div>

                    <p class="shrink-0 text-xl font-bold">{{ $onProgress }}</p>
                </div>
            </button>

            {{-- Rejected --}}
            <button type="button" class="scope-filter group block h-full" data-scope="rejected">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-xl">❌</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-base font-medium leading-tight">Rejected</p>
                    </div>

                    <p class="shrink-0 text-xl font-bold">{{ $rejected }}</p>
                </div>
            </button>

            {{-- Revise --}}
            <button type="button" class="scope-filter group block h-full" data-scope="revise">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-yellow-700 bg-yellow-200/20 p-3 text-yellow-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-yellow-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-xl">🛠️</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-base font-medium leading-tight">Revise</p>
                    </div>

                    <p class="shrink-0 text-xl font-bold">{{ $revise }}</p>
                </div>
            </button>

            {{-- Completed --}}
            <button type="button" class="scope-filter group block h-full" data-scope="completed">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-xl">✅</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-base font-medium leading-tight">Completed</p>
                    </div>

                    <p class="shrink-0 text-xl font-bold">{{ $completed }}</p>
                </div>
            </button>

            {{-- All --}}
            <button type="button" class="scope-filter group block h-full" data-scope="all">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-xl">🧾</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-base font-medium leading-tight">All</p>
                    </div>

                    <p class="shrink-0 text-xl font-bold">{{ $all }}</p>
                </div>
            </button>

        </div>
        <div class="mt-6 flex flex-col gap-6 rounded-xl bg-white p-6 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-xl font-extrabold text-gray-700 dark:text-white">Bast</h1>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="bastTable" class="text-body w-full text-left text-sm rtl:text-right">
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
            let scope = 'bastjobs';
            const $title = $('h1.text-xl.font-extrabold');
            const $thead = $('#bastTable thead');
            let table;

            const titleMap = {
                bastjobs: 'Bast - Jobs',
                onprogress: 'Bast - On Progress',
                completed: 'Bast - Completed',
                rejected: 'Bast - Rejected',
                revise: 'Bast - Revise',
                all: 'Bast - All',
            };

            function headerFor(sc) {
                if (sc === 'bastjobs') {
                    // Disesuaikan ke TrPOterm
                    return `
                    <th>    </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">PO Nbr</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Company</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Vendor</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Terms</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Progress %</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Payment %</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Created By</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                            `;
                }
                // TrBast scopes
                return `
                <th></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Bast ID</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Bast Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">PO Nbr</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">SPPB/J/K/T</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Company</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Created By</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                        `;
            }

            function columnsFor(sc) {
                // 🔹 responsive control column
                if (sc === 'bastjobs') {
                    return [dtControlColumn, {
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: (_v, t, row) => renderPlusCreate(row)
                        },
                        {
                            data: 'ponbr',
                            render: (_v, _t, row) => renderPoLink(row)
                        },
                        {
                            data: 'cpny_id',
                            className: 'text-left'
                        },
                        {
                            data: 'vendorname'
                        },
                        {
                            data: 'terms_name'
                        },
                        {
                            data: 'progress_pct',
                            className: 'text-left'
                        },
                        {
                            data: 'payment_pct',
                            className: 'text-left'
                        },
                        {
                            data: 'created_by'
                        },
                        {
                            data: 'status',
                            orderable: false,
                            searchable: false,
                            className: 'text-left',
                            render: (_v, _t, row) => renderStatusBadge(row)
                        },


                    ];
                }
                // TrBast scopes
                // return [
                //     { data: 'bastid',  render: (_v,_t,row)=>renderBastLink(row) },
                //     { data: 'bastdate', render: (_v,_t,row)=>row.bastdate_fmt ?? '', className:'text-center' },
                //     { data: 'ponbr', render: (_v,_t,row)=>renderPoLink(row) },
                //     { data: 'sppbjktid', render: (_v,_t,row)=>renderSppbLink(row) },
                //     { data: 'cpny_id', className:'text-center' },
                //     { data: 'created_by' },
                // ];
                return [dtControlColumn, {
                        data: 'bastid',
                        render: (_v, _t, row) => renderBastLink(row)
                    },
                    {
                        data: 'bastdate',
                        render: (_v, _t, row) => row.bastdate_fmt ?? '',
                        className: 'text-left'
                    },
                    {
                        data: 'ponbr',
                        className: 'text-left'
                    },
                    {
                        data: 'sppbjktid',
                        className: 'text-left'
                    },
                    {
                        data: 'cpny_id',
                        className: 'text-left'
                    },
                    {
                        data: 'created_by'
                    },
                    {
                        data: 'status',
                        orderable: true,
                        className: 'text-left',
                        render: (_v, _t, row) => renderStatusBadge(row)
                    },

                ];
            }

            function orderFor(sc) {
                if (sc === 'bastjobs') return [
                    [1, 'desc']
                ]; // sort by PONBR
                return [
                    [1, 'desc'],
                    [0, 'desc']
                ];
            }

            function updateTitle(sc) {
                $title.text(titleMap[sc] ?? 'Bast');
            }

            function resetThead(sc) {
                const $table = $('#bastTable');

                // hapus thead lama (yang mungkin sisa clone DataTables)
                $table.find('thead').remove();

                // buat ulang thead + tr
                const theadHtml = `
                        <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                            <tr id="thead-row">${headerFor(sc)}</tr>
                            </thead>`;
                $table.prepend(theadHtml);

                // pastikan tbody ada
                if ($table.find('tbody').length === 0) {
                    $table.append(`<tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    `);
                }
            }

            function rebuild(sc) {
                if ($.fn.DataTable.isDataTable('#bastTable')) {
                    $('#bastTable').DataTable().clear().destroy();
                }
                resetThead(sc);

                table = $('#bastTable').DataTable({
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

                    dom: '<"dt-toolbar"l B f>rtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'List_BAST',
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
                            title: 'List_BAST',
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
                        url: "{{ route('bastlist.json') }}",
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

            function renderPlusCreate(row) {
                // create BAST → kirim hash id PO (hasil mapping di controller)
                const url = `{{ route('bast.create') }}` + `?term=${encodeURIComponent(row.term_eid ?? '')}`;
                return `<a href="${url}" class="inline-flex justify-center items-center px-4 py-2 text-sm leading-tight font-medium text-white rounded text-center transition-colors duration-200 bg-blue-500 hover:bg-blue-700">
                            <i class="fas fa-plus"></i></a>`;
            }

            function renderPoLink(row) {
                const text = row.ponbr ?? '';
                if (row.ponbr_eid) {
                    const url = `/showpo/${encodeURIComponent(row.ponbr_eid)}`;
                    return `<a href="${url}" class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-base leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-gray-500 hover:bg-gray-700">${text}</a>`;
                }
                return text;
            }

            function renderSppbLink(row) {
                const text = row.sppbjktid ?? '';
                if (row.sppb_route && row.sppb_eid) {
                    const url = `/${row.sppb_route}/${encodeURIComponent(row.sppb_eid)}`;
                    return `<a href="${url}" class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-base leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-gray-500 hover:bg-gray-700">${text}</a>`;
                }
                return text;
            }

            function renderBastLink(row) {
                const label = row.bastid ?? '';
                const hash = row.bastid_eid || row.eid || row.hash || row.id;

                if (!label) return '';
                if (!hash) {
                    return `<span class="inline-flex items-center px-3 py-1.5 text-sm font-semibold rounded bg-gray-400 text-white">${label}</span>`;
                }

                const statusRaw = (row.status ?? '').toString().trim().toUpperCase();
                const creator = (row.created_by ?? '').toString();
                const isRevise = statusRaw === 'D';
                const isOwner = creator === (currentUser ?? '');

                if (isRevise && isOwner) {
                    const url = `/editbasts/${encodeURIComponent(hash)}`;
                    return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-semibold rounded bg-amber-600 text-white hover:bg-amber-700" title="Edit (Revise)">${label}</a>`;
                }

                const url = `/showbast/${encodeURIComponent(hash)}`;
                return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-semibold rounded bg-gray-600 text-white hover:bg-gray-700">${label}</a>`;
            }

            function renderStatusBadge(row) {
                const label = row.status_label ?? row.status ?? '-';
                const cls = row.status_class ?? 'bg-gray-100 text-gray-700 border-gray-200';
                return `<span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold ${cls}">${label}</span>`;
            }


            // init
            updateTitle(scope);
            rebuild(scope);

            // ganti scope
            $('.scope-filter').on('click', function(e) {
                e.preventDefault();
                scope = $(this).data('scope') || 'bastjobs';
                updateTitle(scope);
                rebuild(scope);

                // active state + save
                $('.scope-filter').removeClass('active');
                $(this).addClass('active');
                localStorage.setItem('activeBastScope', scope);
            });

            // restore scope terakhir
            const savedBastScope = localStorage.getItem('activeBastScope');
            if (savedBastScope) {
                scope = savedBastScope;
                updateTitle(scope);
                rebuild(scope);
                $('.scope-filter').removeClass('active');
                $(`.scope-filter[data-scope="${scope}"]`).addClass('active');
            } else {
                $(`.scope-filter[data-scope="bastjobs"]`).addClass('active');
            }
        });
    </script>
</x-app-layout>
