<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'polist.index' ? 'PO' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-8 py-4 sm:px-6 lg:px-8">
        <div class="grid auto-rows-fr grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-7">

            {{-- UNSEND --}}
            <a href="#" class="scope-filter group block h-full" data-scope="hold">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-md border border-blue-700/60 bg-blue-50/40 p-3 text-blue-700 transition-all duration-300 hover:-translate-y-1 hover:bg-blue-50 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🧊</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">UNSEND</p>
                    </div>

                    <p class="shrink-0 text-sm font-extrabold">{{ $hold }}</p>
                </div>
            </a>

            {{-- Purchase --}}
            <a href="#" class="scope-filter group block h-full" data-scope="purchase">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-md border border-indigo-700/60 bg-indigo-50/40 p-3 text-indigo-700 transition-all duration-300 hover:-translate-y-1 hover:bg-indigo-50 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🛒</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Purchase</p>
                    </div>

                    <p class="shrink-0 text-sm font-extrabold">{{ $purchase }}</p>
                </div>
            </a>

            {{-- Partial --}}
            <a href="#" class="scope-filter group block h-full" data-scope="partial">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-md border border-amber-700/60 bg-amber-50/40 p-3 text-amber-700 transition-all duration-300 hover:-translate-y-1 hover:bg-amber-50 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📦</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Partial</p>
                    </div>

                    <p class="shrink-0 text-sm font-extrabold">{{ $partial }}</p>
                </div>
            </a>

            {{-- Completed --}}
            <a href="#" class="scope-filter group block h-full" data-scope="completed">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-md border border-green-700/60 bg-green-50/40 p-3 text-green-700 transition-all duration-300 hover:-translate-y-1 hover:bg-green-50 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✅</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Completed</p>
                    </div>

                    <p class="shrink-0 text-sm font-extrabold">{{ $completed }}</p>
                </div>
            </a>

            {{-- Cancel --}}
            <a href="#" class="scope-filter group block h-full" data-scope="cancel">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-md border border-red-700/60 bg-red-50/40 p-3 text-red-700 transition-all duration-300 hover:-translate-y-1 hover:bg-red-50 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✖️</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Cancel</p>
                    </div>

                    <p class="shrink-0 text-sm font-extrabold">{{ $cancel }}</p>
                </div>
            </a>

            {{-- Reuse --}}
            <a href="#" class="scope-filter group block h-full" data-scope="reuse">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-md border border-gray-700/60 bg-gray-50/40 p-3 text-gray-700 transition-all duration-300 hover:-translate-y-1 hover:bg-gray-50 hover:shadow-md active:scale-95 dark:border-gray-400 dark:bg-gray-700/30 dark:text-gray-200 dark:hover:bg-gray-700/40">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">♻️</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Reuse</p>
                    </div>

                    <p class="shrink-0 text-sm font-extrabold">{{ $reuse }}</p>
                </div>
            </a>

            {{-- All --}}
            <a href="#" class="scope-filter group block h-full" data-scope="all">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-md border border-slate-700/60 bg-slate-50/40 p-3 text-slate-700 transition-all duration-300 hover:-translate-y-1 hover:bg-slate-50 hover:shadow-md active:scale-95 dark:border-white/50 dark:text-white dark:hover:bg-gray-700/40">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🧾</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">All PO</p>
                    </div>

                    <p class="shrink-0 text-sm font-extrabold">{{ $all }}</p>
                </div>
            </a>

        </div>


        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Purchase Order</h1>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="poTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr class="transition-colors hover:bg-gray-100 dark:hover:bg-gray-700">
                            <th class="dtr-control"></th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                PO Nbr</th>
                            <th class="w-32 px-6 py-2 font-medium">
                                PO Date</th>
                            <th class="w-32 px-6 py-2 font-medium">
                                Company
                            </th>
                            <th class="w-32 px-6 py-2 font-medium">
                                PO Type
                            </th>
                            <th class="w-32 px-6 py-2 font-medium">
                                Vendor</th>
                            <th class="w-32 px-6 py-2 font-medium">
                                Delivery Date</th>
                            <th class="w-32 px-6 py-2 font-medium">
                                Total</th>
                            <th class="w-32 px-6 py-2 font-medium">
                                Tax</th>
                            <th class="w-32 px-6 py-2 font-medium">
                                Grand Total</th>
                            <th class="w-32 px-6 py-2 font-medium">
                                Created By</th>
                            <th class="w-32 px-6 py-2 font-medium">
                                Status
                            </th>

                        </tr>
                    </thead>
                    <tbody>
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <script>
        $(document).ready(function() {
            let scope = 'hold'; // default: Purchase Order (P)

            const $title = $('h1.text-base.font-extrabold');
            const titleMap = {
                hold: 'Purchase Order - UNSEND',
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
                return `<a href="${url}" class="inline-flex min-w-[90px] justify-center rounded bg-gray-500 px-2 py-1  text-sm  font-semibold text-white hover:bg-gray-700" rel="noopener">${text}</a>`;
            }

            const table = $('#poTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,

                autoWidth: false,
                // scrollX: true,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],

                columnDefs: [{
                        targets: 0,
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false,
                        searchable: false,
                        width: '32px'
                    },
                    {
                        targets: 1,
                        width: "110px"
                    },
                    {
                        targets: 2,
                        width: "90px"
                    },
                    {
                        targets: 3,
                        width: "90px"
                    },
                    {
                        targets: 4,
                        width: "320px",
                        className: "col-wrap"
                    },
                    {
                        targets: 5,
                        width: "120px"
                    },
                    {
                        targets: 6,
                        width: "140px"
                    },
                    {
                        targets: 7,
                        width: "120px"
                    },
                    {
                        targets: 8,
                        width: "160px"
                    },
                    {
                        targets: 9,
                        width: "130px"
                    },
                    {
                        targets: 10,
                        width: "120px"
                    },
                    {
                        targets: 11,
                        width: "120px"
                    }
                ],


                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'List_PO',
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
                        title: 'List_PO',
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

                order: [
                    [2, 'desc'],
                    [1, 'desc']
                ], // podate desc, lalu ponbr
                ajax: {
                    url: "{{ route('polist.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.scope = scope;
                    }
                },
                columns: [{
                        data: null,
                        defaultContent: '',
                        width: '28px',
                        className: 'dtr-control',
                        width: '32px'
                    }, {
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
                        data: 'cpny_id',
                        className: 'text-center'
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
            });

            // Klik kartu → ubah scope, update judul, highlight, reload table
            $(document).on('click', '.scope-filter', function(e) {
                e.preventDefault();

                scope = $(this).data('scope');
                localStorage.setItem('activePoScope', scope);

                updateTitle(scope);
                highlightActive(scope);

                // 🔥 CLEAR DATATABLE STATE
                table.state.clear();

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
                return `<span class="inline-flex items-center rounded-full border px-3 py-1  text-sm  font-semibold ${cls}">${label}</span>`;
            }


        });
    </script>
</x-app-layout>
