<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid auto-rows-fr grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-7">

            {{-- Receipt Jobs --}}
            <a href="#" class="scope-filter group block h-full" data-scope="receiptjobs">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">📦</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Purchase Receipt</p>
                    </div>

                    <p class="shrink-0 text-base font-extrabold">{{ $receiptjobs }}</p>
                </div>
            </a>

            {{-- Return Jobs --}}
            <a href="#" class="scope-filter group block h-full" data-scope="returnjobs">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">↩️</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Return STTB</p>
                    </div>

                    <p class="shrink-0 text-base font-extrabold">{{ $returnjobs }}</p>
                </div>
            </a>

            {{-- On Progress --}}
            <a href="#" class="scope-filter group block h-full" data-scope="onprogress">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">⏳</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">On Progress</p>
                    </div>

                    <p class="shrink-0 text-base font-extrabold">{{ $onProgress }}</p>
                </div>
            </a>

            {{-- Rejected --}}
            <a href="#" class="scope-filter group block h-full" data-scope="rejected">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">❌</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Rejected</p>
                    </div>

                    <p class="shrink-0 text-base font-extrabold">{{ $rejected }}</p>
                </div>
            </a>

            {{-- Revise --}}
            <a href="#" class="scope-filter group block h-full" data-scope="revise">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-yellow-700 bg-yellow-200/20 p-3 text-yellow-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-yellow-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">🛠️</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Revise</p>
                    </div>

                    <p class="shrink-0 text-base font-extrabold">{{ $revise }}</p>
                </div>
            </a>

            {{-- Completed --}}
            <a href="#" class="scope-filter group block h-full" data-scope="completed">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">✅</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Completed</p>
                    </div>

                    <p class="shrink-0 text-base font-extrabold">{{ $completed }}</p>
                </div>
            </a>

            {{-- All --}}
            <a href="#" class="scope-filter group block h-full" data-scope="all">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">🧾</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">All</p>
                    </div>

                    <p class="shrink-0 text-base font-extrabold">{{ $all }}</p>
                </div>
            </a>

        </div>


        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Receipt</h1>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="receiptTable" class="text-body w-full text-left text-sm rtl:text-right">
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
            let scope = 'receiptjobs';
            const $title = $('h1.text-base.font-extrabold');
            const $thead = $('#receiptTable thead');
            const $theadRow = $('#thead-row');
            let table;

            const titleMap = {
                receiptjobs: 'Receipt - Jobs',
                returnjobs: 'Receipt - Return Jobs',
                onprogress: 'Receipt - On Progress',
                completed: 'Receipt - Completed',
                rejected: 'Receipt - Rejected',
                revise: 'Receipt - Revise',
                all: 'Receipt - All',
            };


            function headerFor(sc) {
                if (sc === 'receiptjobs') {

                   return `
                    <th></th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Action</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">PO Nbr</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">PO Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Company</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Vendor</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Delivery Date</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold uppercase tracking-wider">Total Qty</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold uppercase tracking-wider">Total Qty Received</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Created By</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Status</th>
                    `;
                }
                if (sc === 'returnjobs') {
                    return `
                    <th></th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Action</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Receipt Nbr</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Receipt Date</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">PO Nbr</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">SPPB/J/K/T</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Company</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Created By</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Status</th>
                            `;
                }
                // TrReceipt scopes (tanpa kolom "+")
                return `
                <th></th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Receipt Nbr</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Receipt Date</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Receipt Type</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">PO Nbr</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">SPPB/J/K/T</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Company</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Created By</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Status</th>
                        `;
            }

            function columnsFor(sc) {
                let buttonClass =
                    'inline-flex items-center justify-center w-[100px] rounded bg-gray-500 py-1.5 text-white hover:bg-gray-700';
                if (sc === 'receiptjobs') {
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
                            data: 'podate',
                            render: (_v, _t, row) => row.podate_fmt ?? '',
                            className: 'text-left'
                        },
                        {
                            data: 'cpny_id',
                            className: 'text-left'
                        },
                        {
                            data: 'vendorname'
                        },
                        {
                            data: 'podeliverydate',
                            render: (_v, _t, row) => row.podelivery_fmt ?? '',
                            className: 'text-left'
                        },
                        {
                            data: 'totalqty',
                            render: (_v, _t, row) => row.totalqty_fmt ?? '0.00',
                            className: 'text-right'
                        },
                        {
                            data: 'totalqtyreceived',
                            render: (_v, _t, row) => row.totalqtyreceived_fmt ?? '0.00',
                            className: 'text-right'
                        },
                        {
                            data: 'created_by'
                        },
                        {
                            data: 'status',
                            orderable: false,
                            searchable: false,
                            render: (_v, _t, row) => renderStatusBadge(row),
                            className: 'text-left'
                        },
                    ];
                }
                if (sc === 'returnjobs') {
                    return [dtControlColumn, {
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: (_v, t, row) => renderPlusReturn(row)
                        },
                        {
                            data: 'receiptnbr',
                            render: (_v, _t, row) => renderReceiptLink(row)
                        },
                        {
                            data: 'receiptdate',
                            render: (_v, _t, row) => row.receiptdate_fmt ?? '',
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
                            orderable: false,
                            searchable: false,
                            render: (_v, _t, row) => renderStatusBadge(row),
                            className: 'text-left'
                        },
                    ];
                }
                // TrReceipt scopes: tanpa kolom "+"
                return [dtControlColumn, {
                        data: 'receiptnbr',
                        render: (_v, _t, row) => renderReceiptLink(row)
                    },
                    {
                        data: 'receiptdate',
                        render: (_v, _t, row) => row.receiptdate_fmt ?? '',
                        className: 'text-left'
                    },
                    {
                        data: 'receipttype'
                    },
                    {
                        data: 'ponbr',
                        className: 'text-left'
                        // render: (_v, _t, row) => renderPoLink(row)
                    },
                    {
                        data: 'sppbjktid',
                        className: 'text-left'
                        // render: (_v, _t, row) => renderSppbLink(row)
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
                        orderable: false,
                        searchable: false,
                        render: (_v, _t, row) => renderStatusBadge(row),
                        className: 'text-left'
                    },
                ];
            }

            function orderFor(sc) {
                if (sc === 'receiptjobs') return [
                    [3, 'desc'],
                    [2, 'desc']
                ];
                if (sc === 'returnjobs') return [
                    [3, 'desc'],
                    [2, 'desc']
                ];
                return [
                    [2, 'desc'],
                    [1, 'desc']
                ];
            }


            function updateTitle(sc) {
                $title.text(titleMap[sc] ?? 'Receipt');
            }

            function highlightActive(sc) {
                $('.scope-filter').removeClass('#')
                    .each(function() {
                        if ($(this).data('scope') === sc) $(this).addClass(
                            '#');
                    });
            }

            function resetThead(sc) {
                $thead.empty().append('<tr id="thead-row"></tr>');
                $('#thead-row').html(headerFor(sc));
            }

            function rebuild(sc) {
                if ($.fn.DataTable.isDataTable('#receiptTable')) {
                    $('#receiptTable').DataTable().clear().destroy();
                }
                resetThead(sc);

                table = $('#receiptTable').DataTable({
                    processing: true,
                    serverSide: true,
                    deferRender: true,
                    pageLength: 10,
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

                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],

                    dom: '<"dt-toolbar"l B f>rtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'List_Receipt',
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
                            title: 'List_Receipt',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        }
                    ],
                    // responsive: {
                    //     details: {
                    //         type: 'column',
                    //         target: 0 // 👈 this is REQUIRED
                    //     }
                    // },

                    // columnDefs: [{
                    //     targets: 0,
                    //     width: '28px',
                    className: 'dtr-control',
                    //     orderable: false
                    // }],
                    order: orderFor(sc),
                    ajax: {
                        url: "{{ route('receiptlist.json') }}",
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

            // function renderPlusCreate(row) {
            //     const url = `{{ route('receipt.create') }}` + `?ponbr=${encodeURIComponent(row.ponbr_eid ?? '')}`;
            //     return `<a href="${url}" class="inline-flex justify-center items-center px-4 py-2  text-sm  leading-tight font-medium text-white rounded text-center transition-colors duration-200 bg-blue-500 hover:bg-blue-700">
        //         <i class="fas fa-plus"></i></a>`;
            // }

            function renderPlusCreate(row) {
                const base = `{{ route('receipt.create') }}`;
                const ponbr = encodeURIComponent(row.ponbr_eid ?? '');
                const cpny = encodeURIComponent(row.cpny_id ?? '');

                const url = `${base}?ponbr=${ponbr}&cpny_id=${cpny}`;

                return `<a href="${url}" class="inline-flex justify-center items-center px-4 py-2 text-sm leading-tight font-medium text-white rounded text-center transition-colors duration-200 bg-blue-500 hover:bg-blue-700">
                    <i class="fas fa-plus"></i></a>`;
            }


            function renderPlusReturn(row) {
                const url = `{{ route('receipt.return.create') }}` +
                    `?rcp=${encodeURIComponent(row.receiptnbr_eid ?? '')}`;
                return `<a href="${url}" class="inline-flex items-center justify-center rounded bg-indigo-600 px-2 py-1 text-white  text-sm  font-bold hover:bg-indigo-700">+</a>`;
            }


            function renderPoLink(row) {
                const text = row.ponbr ?? '';
                // gunakan hash id jika tersedia
                if (row.ponbr_eid) {
                    const url = `/showpo/${encodeURIComponent(row.ponbr_eid)}`;
                    return `<a href="${url}" target="_blank" class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200  bg-gray-600 hover:bg-gray-700 ">${text}</a>`;
                }
                return text;
            }

            function renderSppbLink(row) {
                const text = row.sppbjktid ?? '';
                if (row.sppb_route && row.sppb_eid) {
                    const url = `/${row.sppb_route}/${encodeURIComponent(row.sppb_eid)}`;
                    return `<a href="${url}" target="_blank" class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200  bg-gray-600 hover:bg-gray-700 ">${text}</a>`;
                }
                return text;
            }

            // function renderReceiptLink(row) {
            //     const url = `/showreceipt/${encodeURIComponent(row.receiptnbr_eid ?? '')}`;
            //     const text = row.receiptnbr ?? '';
            //     return `<a href="${url}" class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200  bg-gray-600 hover:bg-gray-700 ">${text}</a>`;
            // }

            function renderReceiptLink(row) {
                const label = row.receiptnbr ?? '';
                const hash = row.receiptnbr_eid || row.eid || row.hash || row.id; // prioritaskan receiptnbr_eid

                if (!label) return '';
                if (!hash) {
                    // fallback bila hash tidak ada
                    return `<span class="inline-flex items-center px-3 py-1.5  text-sm  font-semibold rounded bg-gray-400 text-white">${label}</span>`;
                }

                // baca status & creator
                const statusRaw = (row.status ?? row.xstatus ?? '').toString().trim().toUpperCase();
                const creator = (row.created_by ?? row.createdby ?? '').toString();
                const isRevise = statusRaw === 'D';
                const isOwner = creator === (currentUser ?? '');

                // Jika Revise dan pemilik dokumen = user sekarang → arahkan ke EDIT
                if (isRevise && isOwner) {
                    const url = `/editreceipts/${encodeURIComponent(hash)}`;
                    return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5  text-sm  font-semibold rounded bg-amber-600 text-white hover:bg-amber-700" title="Edit (Revise)">${label}</a>`;
                }

                // default → SHOW
                const url = `/showreceipt/${encodeURIComponent(hash)}`;
                return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5  text-sm  font-semibold rounded bg-gray-600 text-white hover:bg-gray-700">${label}</a>`;
            }

            function renderStatusBadge(row) {
                const label = row.status_label ?? row.status ?? 'Unknown';
                const cls = row.status_class ?? 'bg-gray-200/60 text-gray-700 border border-gray-500/40'

                return `
                        <span class="inline-flex items-center rounded-full border px-3 py-1  text-sm  font-semibold ${cls}">
                            ${label}
                        </span>
                        `;
            }


            // init awal
            updateTitle(scope);
            highlightActive(scope);
            rebuild(scope);

            // ganti scope
            $('.scope-filter').on('click', function(e) {
                e.preventDefault();
                scope = $(this).data('scope') || 'receiptjobs';
                updateTitle(scope);
                highlightActive(scope);
                rebuild(scope);
            });

            // Toggle .active class and remember selected scope
            const receiptScopes = document.querySelectorAll('.scope-filter');
            const savedReceiptScope = localStorage.getItem('activeReceiptScope');

            if (savedReceiptScope) {
                const activeScope = document.querySelector(`.scope-filter[data-scope="${savedReceiptScope}"]`);
                if (activeScope) activeScope.classList.add('active');
            }

            receiptScopes.forEach(btn => {
                btn.addEventListener('click', e => {
                    e.preventDefault();
                    receiptScopes.forEach(s => s.classList.remove('active'));
                    btn.classList.add('active');
                    localStorage.setItem('activeReceiptScope', btn.dataset.scope);
                });
            });
        });
    </script>

</x-app-layout>
