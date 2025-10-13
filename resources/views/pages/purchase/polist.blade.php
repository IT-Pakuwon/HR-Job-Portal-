<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'polist.index' ? 'PO' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">

        {{-- Mini cards 7 status --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-7">
            <a href="#" class="scope-filter group" data-scope="hold">
                <div
                    class="flex items-center gap-2 rounded-md border border-blue-700/60 bg-blue-50/40 p-2 text-blue-700 hover:bg-blue-50">
                    <span class="text-base">🧊</span>
                    <div class="flex w-full items-center justify-between">
                        <p class="text-sm font-medium">Hold</p>
                        <p class="text-right text-lg font-extrabold">{{ $hold }}</p>
                    </div>
                </div>
            </a>

            <a href="#" class="scope-filter group" data-scope="purchase">
                <div
                    class="flex items-center gap-2 rounded-md border border-indigo-700/60 bg-indigo-50/40 p-2 text-indigo-700 hover:bg-indigo-50">
                    <span class="text-base">🛒</span>
                    <div class="flex w-full items-center justify-between">
                        <p class="text-sm font-medium">Purchase</p>
                        <p class="text-right text-lg font-extrabold">{{ $purchase }}</p>
                    </div>
                </div>
            </a>

            <a href="#" class="scope-filter group" data-scope="partial">
                <div
                    class="flex items-center gap-2 rounded-md border border-amber-700/60 bg-amber-50/40 p-2 text-amber-700 hover:bg-amber-50">
                    <span class="text-base">📦</span>
                    <div class="flex w-full items-center justify-between">
                        <p class="text-sm font-medium">Partial</p>
                        <p class="text-right text-lg font-extrabold">{{ $partial }}</p>
                    </div>
                </div>
            </a>

            <a href="#" class="scope-filter group" data-scope="completed">
                <div
                    class="flex items-center gap-2 rounded-md border border-green-700/60 bg-green-50/40 p-2 text-green-700 hover:bg-green-50">
                    <span class="text-base">✅</span>
                    <div class="flex w-full items-center justify-between">
                        <p class="text-sm font-medium">Completed</p>
                        <p class="text-right text-lg font-extrabold">{{ $completed }}</p>
                    </div>
                </div>
            </a>

            <a href="#" class="scope-filter group" data-scope="cancel">
                <div
                    class="flex items-center gap-2 rounded-md border border-red-700/60 bg-red-50/40 p-2 text-red-700 hover:bg-red-50">
                    <span class="text-base">✖️</span>
                    <div class="flex w-full items-center justify-between">
                        <p class="text-sm font-medium">Cancel</p>
                        <p class="text-right text-lg font-extrabold">{{ $cancel }}</p>
                    </div>
                </div>
            </a>

            <a href="#" class="scope-filter group" data-scope="reuse">
                <div
                    class="flex items-center gap-2 rounded-md border border-gray-700/60 bg-gray-50/40 p-2 text-gray-700 hover:bg-gray-50 dark:border-gray-400 dark:bg-gray-700/30 dark:text-gray-200">
                    <span class="text-base">♻️</span>
                    <div class="flex w-full items-center justify-between">
                        <p class="text-sm font-medium">Reuse</p>
                        <p class="text-right text-lg font-extrabold">{{ $reuse }}</p>
                    </div>
                </div>
            </a>

            <a href="#" class="scope-filter group" data-scope="all">
                <div
                    class="flex items-center gap-2 rounded-md border border-slate-700/60 bg-slate-50/40 p-2 text-slate-700 hover:bg-slate-50 dark:border-white/50 dark:text-white">
                    <span class="text-base">🧾</span>
                    <div class="flex w-full items-center justify-between">
                        <p class="text-sm font-medium">All PO</p>
                        <p class="text-right text-lg font-extrabold">{{ $all }}</p>
                    </div>
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
                        return `<a href="${url}" class="px-3 py-1.5 rounded bg-indigo-600 text-white hover:bg-indigo-700 text-sm font-semibold" rel="noopener">${text}</a>`;
                    }

                    const table = $('#poTable').DataTable({
                        processing: true,
                        serverSide: true,
                        deferRender: true,
                        pageLength: 25,
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
                });
            </script>
        </div>
    </div>
</x-app-layout>
