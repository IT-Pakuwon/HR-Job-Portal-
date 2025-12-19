<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'polist.index' ? 'PO' : '';
    @endphp
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
        #poTable_filter {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }

        #poTable_filter label {
            margin-right: 2px;
        }

        #poTable_filter input {
            width: auto;
            padding: 0.25rem 0.5rem;
            min-width: 80px;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
        }

        /* === Wrapper Width === */
        #poTable_wrapper {
            width: 100%;
        }

        /* === Cell Formatting === */
        #poTable td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding: 10px;
            max-width: 200px;
        }

        #poTable th {
            padding: 10px;
            max-width: 200px;
        }

        /* === Length Section === */
        #poTable_length {
            width: auto;
            display: flex;
            justify-content: flex-start;
        }

        #poTable_length select {
            width: auto;
            padding: 0.25rem 0.5rem;
            min-width: 80px;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
        }

        /* === Info + Pagination === */
        #poTable_info {
            margin: 10px 0;
        }

        .dataTables_paginate {
            margin: 10px 0;
        }

        /* === Hover Effects === */
        #poTable tbody tr {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        #poTable tbody tr:hover {
            background-color: #8f8f8f11;
            cursor: pointer;
        }

        #poTable tbody tr td {
            padding: 8px;
            line-height: 2;
        }

        /* === Column Width Alignment === */
        #poTable th:nth-child(1),
        #poTable td:nth-child(1),
        #poTable th:nth-child(4),
        #poTable td:nth-child(4) {
            width: 120px;
            text-align: center;
        }

        /* === Group Row & Collapse === */
        #poTable tbody tr.collapsed-group-row {
            display: none;
        }

        #poTable tr.group-row {
            background-color: #e6e6e6;
            font-weight: bold;
            cursor: pointer;
            user-select: none;
            color: #333;
        }

        #poTable tr.group-row:hover {
            background-color: #d4d4d4;
        }

        #poTable tr.group-row .fas {
            margin-right: 8px;
            width: 16px;
            text-align: center;
        }

        #poTable tr.group-row td {
            padding: 10px !important;
            border-bottom: 1px solid #ddd;
        }

        #poTable tr.group-row td:first-child {
            border-left: none;
        }

        /* === Custom Switch === */
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

        /* Active / Selected state */
        .scope-filter.active .scope-card {
            transform: scale(1.02);
        }

        /* Hold */
        .scope-filter[data-scope="hold"].active .scope-card {
            background-color: rgb(219 234 254);
            /* blue-100 */
            border-color: rgb(29 78 216);
            /* blue-700 */
            color: rgb(29 78 216);
        }

        /* Purchase */
        .scope-filter[data-scope="purchase"].active .scope-card {
            background-color: rgb(224 231 255);
            /* indigo-100 */
            border-color: rgb(67 56 202);
            /* indigo-700 */
            color: rgb(67 56 202);
        }

        /* Partial */
        .scope-filter[data-scope="partial"].active .scope-card {
            background-color: rgb(254 243 199);
            /* amber-100 */
            border-color: rgb(180 83 9);
            /* amber-700 */
            color: rgb(180 83 9);
        }

        /* Completed */
        .scope-filter[data-scope="completed"].active .scope-card {
            background-color: rgb(220 252 231);
            /* green-100 */
            border-color: rgb(21 128 61);
            /* green-700 */
            color: rgb(21 128 61);
        }

        /* Cancel */
        .scope-filter[data-scope="cancel"].active .scope-card {
            background-color: rgb(254 226 226);
            /* red-100 */
            border-color: rgb(185 28 28);
            /* red-700 */
            color: rgb(185 28 28);
        }

        /* Reuse */
        .scope-filter[data-scope="reuse"].active .scope-card {
            background-color: rgb(243 244 246);
            /* gray-100 */
            border-color: rgb(31 41 55);
            /* gray-700 */
            color: rgb(31 41 55);
        }

        /* All PO */
        .scope-filter[data-scope="all"].active .scope-card {
            background-color: rgb(241 245 249);
            /* slate-100 */
            border-color: rgb(30 41 59);
            /* slate-700 */
            color: rgb(30 41 59);
        }
    </style>

    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid auto-rows-fr grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-7">

            {{-- Hold --}}
            <a href="#" class="scope-filter group block h-full" data-scope="hold">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-md border border-blue-700/60 bg-blue-50/40 p-3 text-blue-700 transition-all duration-300 hover:-translate-y-1 hover:bg-blue-50 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">🧊</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Hold</p>
                    </div>

                    <p class="shrink-0 text-lg font-extrabold">{{ $hold }}</p>
                </div>
            </a>

            {{-- Purchase --}}
            <a href="#" class="scope-filter group block h-full" data-scope="purchase">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-md border border-indigo-700/60 bg-indigo-50/40 p-3 text-indigo-700 transition-all duration-300 hover:-translate-y-1 hover:bg-indigo-50 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">🛒</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Purchase</p>
                    </div>

                    <p class="shrink-0 text-lg font-extrabold">{{ $purchase }}</p>
                </div>
            </a>

            {{-- Partial --}}
            <a href="#" class="scope-filter group block h-full" data-scope="partial">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-md border border-amber-700/60 bg-amber-50/40 p-3 text-amber-700 transition-all duration-300 hover:-translate-y-1 hover:bg-amber-50 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">📦</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Partial</p>
                    </div>

                    <p class="shrink-0 text-lg font-extrabold">{{ $partial }}</p>
                </div>
            </a>

            {{-- Completed --}}
            <a href="#" class="scope-filter group block h-full" data-scope="completed">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-md border border-green-700/60 bg-green-50/40 p-3 text-green-700 transition-all duration-300 hover:-translate-y-1 hover:bg-green-50 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">✅</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Completed</p>
                    </div>

                    <p class="shrink-0 text-lg font-extrabold">{{ $completed }}</p>
                </div>
            </a>

            {{-- Cancel --}}
            <a href="#" class="scope-filter group block h-full" data-scope="cancel">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-md border border-red-700/60 bg-red-50/40 p-3 text-red-700 transition-all duration-300 hover:-translate-y-1 hover:bg-red-50 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">✖️</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Cancel</p>
                    </div>

                    <p class="shrink-0 text-lg font-extrabold">{{ $cancel }}</p>
                </div>
            </a>

            {{-- Reuse --}}
            <a href="#" class="scope-filter group block h-full" data-scope="reuse">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-md border border-gray-700/60 bg-gray-50/40 p-3 text-gray-700 transition-all duration-300 hover:-translate-y-1 hover:bg-gray-50 hover:shadow-md active:scale-95 dark:border-gray-400 dark:bg-gray-700/30 dark:text-gray-200 dark:hover:bg-gray-700/40">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">♻️</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Reuse</p>
                    </div>

                    <p class="shrink-0 text-lg font-extrabold">{{ $reuse }}</p>
                </div>
            </a>

            {{-- All --}}
            <a href="#" class="scope-filter group block h-full" data-scope="all">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-md border border-slate-700/60 bg-slate-50/40 p-3 text-slate-700 transition-all duration-300 hover:-translate-y-1 hover:bg-slate-50 hover:shadow-md active:scale-95 dark:border-white/50 dark:text-white dark:hover:bg-gray-700/40">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">🧾</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">All PO</p>
                    </div>

                    <p class="shrink-0 text-lg font-extrabold">{{ $all }}</p>
                </div>
            </a>

        </div>


        <div class="grid">
            <style>
                table.dataTable {
                    width: 100% !important;
                }

                #poTable_filter {
                    margin-bottom: 16px;
                    display: flex;
                    align-items: center;
                }

                #poTable_filter input {
                    width: auto;
                    padding: .25rem .5rem;
                    border-radius: .5rem;
                    border: 1px solid #d1d5db;
                    background: #f9fafb;
                }

                #poTable td {
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                #poTable th,
                #poTable td {
                    padding: 10px;
                    max-width: 240px;
                }

                #poTable tbody tr:hover {
                    background-color: #8f8f8f11;
                    cursor: pointer;
                }

                #poTable th:nth-child(1),
                #poTable td:nth-child(1) {
                    width: 160px;
                    text-align: left;
                }

                #poTable th:nth-child(2),
                #poTable td:nth-child(2),
                #poTable th:nth-child(4),
                #poTable td:nth-child(4) {
                    text-align: center;
                }

                #poTable th:nth-child(5),
                #poTable td:nth-child(5),
                #poTable th:nth-child(6),
                #poTable td:nth-child(6),
                #poTable th:nth-child(7),
                #poTable td:nth-child(7) {
                    text-align: right;
                }
            </style>

            <div class="mt-6 rounded-2xl bg-white dark:bg-gray-800">
                <div class="flex items-center justify-between gap-4 border-b border-gray-200 p-4 dark:border-gray-700">
                    <h1 class="text-xl font-extrabold text-gray-700 dark:text-white">Purchase Order</h1>
                </div>

                <div class="overflow-x-auto p-6">
                    <table id="poTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    PO Nbr</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    PO Date</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    PO Type
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Vendor</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Delivery Date</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Total</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Tax</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Grand Total</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Created By</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Status
                                </th>

                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>
                </div>
            </div>

            <script>
                $(document).ready(function() {
                    let scope = 'hold'; // default: Purchase Order (P)

                    const $title = $('h1.text-xl.font-extrabold');
                    const titleMap = {
                        hold: 'Purchase Order - Hold',
                        purchase: 'Purchase Order - Purchase',
                        partial: 'Purchase Order - Partial Release',
                        completed: 'Purchase Order - Completed',
                        cancel: 'Purchase Order - Cancel',
                        reuse: 'Purchase Order - Reuse',
                        all: 'Purchase Order - All',
                    };

                    function updateTitle(sc) {
                        $title.text(titleMap[sc] ?? 'Purchase Order');
                    }

                    function highlightActive(sc) {
                        $('.scope-filter').removeClass('#')
                            .each(function() {
                                if ($(this).data('scope') === sc) {
                                    $(this).addClass('#');
                                }
                            });
                    }
                    updateTitle(scope);
                    highlightActive(scope);

                    function fmtDate(v) {
                        if (!v) return '';
                        const d = new Date(v);
                        return Number.isNaN(d.getTime()) ? v : d.toLocaleDateString('id-ID');
                    }

                    function fmtNumber(n) {
                        const x = parseFloat(n ?? 0);
                        if (Number.isNaN(x)) return '0';
                        return new Intl.NumberFormat('id-ID', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0
                        }).format(x);
                    }

                    function renderPONbr(_v, row) {
                        const url = `/showpo/${row.eid}`;
                        const text = row.ponbr || row.eid;
                        return `<a href="${url}" class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-base leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-gray-500 hover:bg-gray-700" rel="noopener">${text}</a>`;
                    }

                    const table = $('#poTable').DataTable({
                        processing: true,
                        serverSide: true,
                        deferRender: true,
                        pageLength: 10,
                        lengthMenu: [10, 25, 50, 100, 250],
                        order: [
                            [1, 'desc'],
                            [0, 'desc']
                        ], // podate desc, lalu ponbr
                        ajax: {
                            url: "{{ route('polist.json') }}",
                            type: "GET",
                            data: function(d) {
                                d.scope = scope;
                            }
                        },
                        columns: [{
                                data: 'ponbr',
                                className: 'text-left',
                                render: (_v, t, row) => renderPONbr(_v, row)
                            },
                            {
                                data: 'podate',
                                className: 'text-center',
                                render: (v) => fmtDate(v)
                            },
                            {
                                data: 'potype',              
                                className: 'text-center'
                            },
                            {
                                data: 'vendorname',
                                className: 'text-left'
                            },
                            {
                                data: 'podeliverydate',
                                className: 'text-center',
                                render: (v) => fmtDate(v)
                            },
                            {
                                data: 'totalamt',
                                className: 'text-right',
                                render: (v) => fmtNumber(v)
                            },
                            {
                                data: 'taxamt',
                                className: 'text-right',
                                render: (v) => fmtNumber(v)
                            },
                            {
                                data: 'grandtotalamt',
                                className: 'text-right',
                                render: (v) => fmtNumber(v)
                            },
                            {
                                data: 'created_by',
                                className: 'text-left'
                            },
                            {
                                data: 'status',
                                className: 'text-left',
                                render: (_v, _t, row) => renderStatusBadge(row)
                            },

                        ],
                        searchDelay: 400,
                        stateSave: true,
                        responsive: true
                    });

                    // Klik kartu → ubah scope, update judul, highlight, reload table
                    $('.scope-filter').on('click', function(e) {
                        e.preventDefault();
                        scope = $(this).data('scope') || 'purchase';
                        updateTitle(scope);
                        highlightActive(scope);
                        table.ajax.reload(null, true);
                    });

                    // Toggle .active class and remember selected scope
                    const poScopes = document.querySelectorAll('.scope-filter');
                    const savedPoScope = localStorage.getItem('activePoScope');

                    if (savedPoScope) {
                        const activeScope = document.querySelector(`.scope-filter[data-scope="${savedPoScope}"]`);
                        if (activeScope) activeScope.classList.add('active');
                    }

                    poScopes.forEach(btn => {
                        btn.addEventListener('click', e => {
                            e.preventDefault();
                            poScopes.forEach(s => s.classList.remove('active'));
                            btn.classList.add('active');
                            localStorage.setItem('activePoScope', btn.dataset.scope);
                        });
                    });

                    function renderStatusBadge(row) {
                        const label = row.status_label ?? row.status ?? '-';
                        const cls = row.status_class ?? 'bg-gray-100 text-gray-700 border-gray-200';
                        return `<span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold ${cls}">${label}</span>`;
                    }


                });
            </script>
        </div>
    </div>
</x-app-layout>
