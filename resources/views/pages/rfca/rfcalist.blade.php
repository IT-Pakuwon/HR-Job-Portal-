<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid auto-rows-fr grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">

            {{-- Rfca Jobs --}}
            <button type="button" class="scope-filter group block h-full" data-scope="rfcajobs">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">📦</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">Rfca Jobs</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $rfcajobs }}</p>
                </div>
            </button>

            {{-- Finance Received --}}
            <button type="button" class="scope-filter group block h-full" data-scope="financereceived">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">💰</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">Finance Received</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $financeReceived }}</p>
                </div>
            </button>

            {{-- Treasury Payment --}}
            <button type="button" class="scope-filter group block h-full" data-scope="treasurypayment">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-yellow-700 bg-yellow-200/20 p-3 text-yellow-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-yellow-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">🏦</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">Treasury Payment</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $treasuryPayment }}</p>
                </div>
            </button>

            {{-- Rfca Completed --}}
            <button type="button" class="scope-filter group block h-full" data-scope="completed">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">✅</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">Rfca Completed</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $completed }}</p>
                </div>
            </button>

            {{-- All Rfca --}}
            <button type="button" class="scope-filter group block h-full" data-scope="all">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">🧾</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">All Rfca</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $all }}</p>
                </div>
            </button>

        </div>

        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Rfca</h1>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="rfcaTable" class="text-body w-full text-left text-sm rtl:text-right">
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
            let scope = 'rfcajobs';
            const $title = $('h1.text-base.font-extrabold');
            const $thead = $('#rfcaTable thead');
            let table;

            const titleMap = {
                rfcajobs: 'Rfca - Jobs',
                financereceived: 'Rfca - Finance Received',
                treasurypayment: 'Rfca - Treasury Payment',
                completed: 'Rfca - Completed',
                all: 'Rfca - All',
            };


            function headerFor(sc) {
                return `
                <th></th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Rfca ID</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Rfca Date</th>                            
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">PO Nbr</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">SPPB/J/K/T</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Company</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Created By</th>
                            <th class="px-6 py-3 text-left  text-sm  font-semibold uppercase tracking-wider">Rfca Status</th>
                        `;
            }



            function columnsFor(sc) {
                return [
                    dtControlColumn,
                    {
                        data: 'rfcaid',
                        render: (_v, _t, row) => renderRfcaLink(row)
                    },
                    {
                        data: 'rfcadate',
                        render: (_v, _t, row) => row.rfcadate_fmt ?? '',
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
                        data: 'current_step_id',
                        className: 'text-left',
                        render: (v) => {
                            const map = {
                                'PS': 'RFCA Jobs',
                                'FR': 'Finance Received',
                                'TP': 'Treasury Payment',
                                'PC': 'RFCA Completed'
                            };
                            return map[v] ?? '-';
                        }
                    },
                ];
            }


            function orderFor(sc) {
                return [
                    [1, 'desc'], // rfcadate
                    [0, 'desc'], // rfcaid
                ];
            }


            function updateTitle(sc) {
                $title.text(titleMap[sc] ?? 'Rfca');
            }

            function resetThead(sc) {
                const $table = $('#rfcaTable');

                // hapus thead lama (yang mungkin sisa clone DataTables)
                $table.find('thead').remove();

                // buat ulang thead + tr
                const theadHtml = `
                        <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b  text-sm ">
                            <tr id="thead-row">${headerFor(sc)}</tr>
                            </thead>`;
                $table.prepend(theadHtml);

                // pastikan tbody ada
                if ($table.find('tbody').length === 0) {
                    $table.append(
                        `<tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>`
                    );
                }
            }

            function rebuild(sc) {
                if ($.fn.DataTable.isDataTable('#rfcaTable')) {
                    $('#rfcaTable').DataTable().clear().destroy();
                }
                resetThead(sc);

                table = $('#rfcaTable').DataTable({
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
                            title: 'List_RFCA',
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
                            title: 'List_RFCA',
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
                        url: "{{ route('rfcalist.json') }}",
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


            function renderPoLink(row) {
                const text = row.ponbr ?? '';
                if (row.ponbr_eid) {
                    const url = `/showpo/${encodeURIComponent(row.ponbr_eid)}`;
                    return `<a href="${url}" class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200  bg-gray-600 hover:bg-gray-700 ">${text}</a>`;
                }
                return text;
            }

            function renderSppbLink(row) {
                const text = row.sppbjktid ?? '';
                if (row.sppb_route && row.sppb_eid) {
                    const url = `/${row.sppb_route}/${encodeURIComponent(row.sppb_eid)}`;
                    return `<a href="${url}" class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200  bg-gray-600 hover:bg-gray-700 ">${text}</a>`;
                }
                return text;
            }

            function renderRfcaLink(row) {
                const label = row.rfcaid ?? '';
                const hash = row.rfcaid_eid || row.eid || row.hash || row.id;

                if (!label) return '';
                if (!hash) {
                    return `<span class="inline-flex items-center px-3 py-1.5  text-sm  font-semibold rounded bg-gray-400 text-white">${label}</span>`;
                }

                const statusRaw = (row.status ?? '').toString().trim().toUpperCase();
                const creator = (row.created_by ?? '').toString();
                const isRevise = statusRaw === 'D';
                const isOwner = creator === (currentUser ?? '');

                if (isRevise && isOwner) {
                    const url = `/editrfcas/${encodeURIComponent(hash)}`;
                    return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5  text-sm  font-semibold rounded bg-amber-600 text-white hover:bg-amber-700" title="Edit (Revise)">${label}</a>`;
                }

                const url = `/showrfca/${encodeURIComponent(hash)}`;
                return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5  text-sm  font-semibold rounded bg-gray-600 text-white hover:bg-gray-700">${label}</a>`;
            }

            // init
            updateTitle(scope);
            rebuild(scope);

            // ganti scope
            $('.scope-filter').on('click', function(e) {
                e.preventDefault();
                scope = $(this).data('scope') || 'rfcajobs';
                updateTitle(scope);
                rebuild(scope);

                $('.scope-filter').removeClass('active');
                $(this).addClass('active');
                localStorage.setItem('activeRfcaScope', scope);
            });

            // restore scope terakhir
            const savedRfcaScope = localStorage.getItem('activeRfcaScope');
            if (savedRfcaScope) {
                scope = savedRfcaScope;
                updateTitle(scope);
                rebuild(scope);
                $('.scope-filter').removeClass('active');
                $(`.scope-filter[data-scope="${scope}"]`).addClass('active');
            } else {
                $(`.scope-filter[data-scope="rfcajobs"]`).addClass('active');
            }
        });
    </script>
</x-app-layout>
