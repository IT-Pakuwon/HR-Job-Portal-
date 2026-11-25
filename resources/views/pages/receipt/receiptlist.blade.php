<x-app-layout>
    <x-spp-list-styles />
    <x-spp-list-scripts />
    @php
        $xlCols = 7;

        if ($hasReceiptAllAccess) {
            $xlCols++; // Receipt All
        }
    @endphp
    <div class="max-w-9xl mx-auto w-full p-2">
        @php
            $statusCards = [
                [['data-scope' => 'receiptjobs'], 'Purchase Receipt', '📦', 'blue', $receiptjobs, false, null, 'scope-filter'],
                [['data-scope' => 'returnjobs'], 'Return STTB', '↩️', 'purple', $returnjobs, false, null, 'scope-filter'],
                [['data-scope' => 'onprogress'], 'On Progress', '⏳', 'yellow', $onProgress, false, null, 'scope-filter'],
                [['data-scope' => 'rejected'], 'Rejected', '❌', 'red', $rejected, false, null, 'scope-filter'],
                [['data-scope' => 'revise'], 'Revise', '🛠️', 'blue', $revise, false, null, 'scope-filter'],
                [['data-scope' => 'completed'], 'Completed', '✅', 'green', $completed, false, null, 'scope-filter'],
                [['data-scope' => 'all'], 'All', '🧾', 'all', $all, false, null, 'scope-filter'],
                ...($hasReceiptAllAccess ? [[['data-scope' => 'receiptall'], 'Receipt All', '📊', 'purple', $receiptAll, false, null, 'scope-filter']] : []),
            ];
        @endphp
        <x-status-filter-cards :custom="$statusCards" />


        <div
            class="dt-pretty mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="Receipt" subtitle="Purchase receipts and return STTB" title-id="pageTitle">
                <div class="relative w-44">
                    <i class="fa-solid fa-hashtag pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                    <input type="text" id="filter_ponbr" placeholder="PO Number" class="h-10 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm font-medium text-gray-700 shadow-sm placeholder:font-normal placeholder:text-gray-400 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                </div>
                <div class="sppb-f relative w-56"><span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" /></svg></span><select id="filter_vendor"><option value="">All Vendor</option></select></div>
                <div class="relative w-44">
                    <input type="date" id="filter_delivery" class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm font-medium text-gray-700 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                </div>
                <button onclick="resetFilters()" type="button" class="inline-flex h-10 items-center rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-gray-50 hover:shadow-md active:translate-y-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"><i class="fas fa-rotate-right mr-2 text-xs"></i>Reset</button>
            </x-list-page-header>

            

            <div class="relative overflow-hidden">
                <table id="receiptTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
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
            let scope = 'receiptjobs';
            const $title = $('#pageTitle');
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
                receiptall: 'Receipt - All (Active)',
            };


            function headerFor(sc) {
                if (sc === 'receiptjobs') {

                    return `
                    <th></th>
                    <th class="px-4 py-3 text-left font-medium">Action</th>
                    <th class="px-4 py-3 text-left font-medium">PO Nbr</th>
                    <th class="px-4 py-3 text-left font-medium">PO Date</th>
                    <th class="px-4 py-3 text-left font-medium">Company</th>
                    <th class="px-4 py-3 text-left font-medium">Vendor</th>
                    <th class="px-4 py-3 text-left font-medium">Delivery Date</th>
                    <th class="px-4 py-3 text-right font-medium">Total Qty</th>
                    <th class="px-4 py-3 text-right font-medium">Total Qty Received</th>
                    <th class="px-4 py-3 text-left font-medium">Created By</th>
                    <th class="px-4 py-3 text-left font-medium">Status</th>
                    `;
                }
                if (sc === 'returnjobs') {
                    return `
                    <th></th>
                            <th class="px-4 py-3 text-left font-medium">Action</th>
                            <th class="px-4 py-3 text-left font-medium">Receipt Nbr</th>
                            <th class="px-4 py-3 text-left font-medium">Receipt Date</th>
                            <th class="px-4 py-3 text-left font-medium">PO Nbr</th>
                            <th class="px-4 py-3 text-left font-medium">SPPB/J/K/T</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Created By</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                            `;
                }
                // TrReceipt scopes (tanpa kolom "+")
                return `
                <th></th>
                            <th class="px-4 py-3 text-left font-medium">Receipt Nbr</th>
                            <th class="px-4 py-3 text-left font-medium">Receipt Date</th>
                            <th class="px-4 py-3 text-left font-medium">Receipt Type</th>
                            <th class="px-4 py-3 text-left font-medium">PO Nbr</th>
                            <th class="px-4 py-3 text-left font-medium">SPPB/J/K/T</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Created By</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
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
                    [2, 'desc']
                ];
                return [
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
                            text: '<i class="fa-solid fa-file-excel"></i> Excel',
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
                            text: '<i class="fa-solid fa-file-csv"></i> CSV',
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
                            d.ponbr = $('#filter_ponbr').val();
                            d.vendor = $('#filter_vendor').val();
                            d.delivery_date = $('#filter_delivery').val();
                        }
                    },
                    columns: columnsFor(sc),
                    searchDelay: 400,
                    stateSave: false,
                    responsive: true
                });

                table.on('xhr', function() {
                    const json = table.ajax.json();
                    if (!json || !json.data) return;

                    const vendors = new Set();

                    json.data.forEach(row => {
                        if (row.vendorname) vendors.add(row.vendorname);
                    });

                    const $vendor = $('#filter_vendor');

                    if ($vendor.children().length <= 1) {
                        vendors.forEach(v => {
                            $vendor.append(`<option value="${v}">${v}</option>`);
                        });
                    }
                });
            }

            $('#filter_vendor').select2({ width: '100%', minimumResultsForSearch: 8, dropdownCssClass: 'sppb-s2-dropdown' });
            $('#filter_ponbr, #filter_vendor, #filter_delivery')
                .on('keyup change', function() {
                    table.ajax.reload();
                });

            function resetFilters() {
                $('#filter_ponbr').val('');
                $('#filter_vendor').val('').trigger('change.select2');
                $('#filter_delivery').val('');

                table.ajax.reload();
            }

            // function renderPlusCreate(row) {
            //     const url = `{{ route('receipt.create') }}` + `?ponbr=${encodeURIComponent(row.ponbr_eid ?? '')}`;
            //     return `<a href="${url}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-blue-100 bg-blue-50 text-sm font-bold text-blue-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90">
        //         <i class="fas fa-plus"></i></a>`;
            // }

            function renderPlusCreate(row) {
                const base = `{{ route('receipt.create') }}`;
                const ponbr = encodeURIComponent(row.ponbr_eid ?? '');
                const cpny = encodeURIComponent(row.cpny_id ?? '');

                const url = `${base}?ponbr=${ponbr}&cpny_id=${cpny}`;

                return `<a href="${url}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-blue-100 bg-blue-50 text-sm font-bold text-blue-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90">
                    <i class="fas fa-plus"></i></a>`;
            }


            function renderPlusReturn(row) {
                const url = `{{ route('receipt.return.create') }}` +
                    `?rcp=${encodeURIComponent(row.receiptnbr_eid ?? '')}`;
                return `<a href="${url}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-blue-100 bg-blue-50 text-sm font-bold text-blue-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90">+</a>`;
            }


            function renderPoLink(row) {
                const text = row.ponbr ?? '';
                // gunakan hash id jika tersedia
                if (row.ponbr_eid) {
                    const url = `/showpo/${encodeURIComponent(row.ponbr_eid)}`;
                    return `<a href="${url}" target="_blank" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:-rotate-6 group-hover/doc:scale-125 group-hover/doc:opacity-100"><i class="fa-regular fa-file-lines"></i></span><span>${text}</span></a>`;
                }
                return text;
            }

            function renderSppbLink(row) {
                const text = row.sppbjktid ?? '';
                if (row.sppb_route && row.sppb_eid) {
                    const url = `/${row.sppb_route}/${encodeURIComponent(row.sppb_eid)}`;
                    return `<a href="${url}" target="_blank" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:-rotate-6 group-hover/doc:scale-125 group-hover/doc:opacity-100"><i class="fa-regular fa-file-lines"></i></span><span>${text}</span></a>`;
                }
                return text;
            }

            // function renderReceiptLink(row) {
            //     const url = `/showreceipt/${encodeURIComponent(row.receiptnbr_eid ?? '')}`;
            //     const text = row.receiptnbr ?? '';
            //     return `<a href="${url}" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:-rotate-6 group-hover/doc:scale-125 group-hover/doc:opacity-100"><i class="fa-regular fa-file-lines"></i></span><span>${text}</span></a>`;
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
                    return `<a href="${url}" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-amber-800 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-amber-500 hover:bg-amber-500 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300" title="Edit (Revise)"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:-rotate-6 group-hover/doc:scale-125 group-hover/doc:opacity-100"><i class="fa-solid fa-pen text-[10px]"></i></span><span>${label}</span></a>`;
                }

                // default → SHOW
                const url = `/showreceipt/${encodeURIComponent(hash)}`;
                return `<a href="${url}" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:-rotate-6 group-hover/doc:scale-125 group-hover/doc:opacity-100"><i class="fa-regular fa-file-lines"></i></span><span>${label}</span></a>`;
            }

            function renderStatusBadge(row) {
                const label = row.status_label ?? row.status ?? 'Unknown';
                const L = String(label).toLowerCase();
                let c = 'bg-slate-50 text-slate-600 ring-slate-200 dark:bg-white/5 dark:text-slate-300 dark:ring-white/10', d = 'bg-slate-400';
                if (/pending|progress|waiting/.test(L)) { c = 'bg-yellow-50 text-yellow-700 ring-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:ring-yellow-500/30'; d = 'bg-yellow-500'; }
                else if (/complete|received|done|closed/.test(L)) { c = 'bg-green-50 text-green-700 ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30'; d = 'bg-green-500'; }
                else if (/reject|cancel/.test(L)) { c = 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/30'; d = 'bg-rose-500'; }
                else if (/revise|partial/.test(L)) { c = 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/30'; d = 'bg-blue-500'; }
                return `<span class="inline-flex min-w-[116px] items-center justify-center gap-2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset ${c}"><span class="h-1.5 w-1.5 rounded-full ${d}"></span>${label}</span>`;
            }

            function renderStatusBadgeOld(row) {
                const label = row.status_label ?? row.status ?? 'Unknown';
                const cls = row.status_class ?? 'bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-500/30'

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
