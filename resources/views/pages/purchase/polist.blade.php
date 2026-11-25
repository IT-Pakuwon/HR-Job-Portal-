<x-app-layout>
    <x-spp-list-styles />
    <x-spp-list-scripts />
    @php
        $currentPage = Route::currentRouteName() == 'polist.index' ? 'PO' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- ===== Tabs ===== --}}
        <div class="mb-4 flex items-center gap-2">
            @if (!$isFinanceAccess)
                <button type="button" id="tabMy" class="po-tab h-10 rounded-lg border px-5 text-sm font-semibold shadow-sm transition-colors"
                    data-tab="my">
                    My PO
                </button>
            @endif

            <button type="button" id="tabAll" class="po-tab h-10 rounded-lg border px-5 text-sm font-semibold shadow-sm transition-colors"
                data-tab="all">
                All PO
            </button>
        </div>

        <div
            class="dt-pretty mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="Purchase Order" subtitle="Track and manage your purchase orders" title-id="poTitle">
                    <div class="sppb-f relative w-44">
                        <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg></span>
                        <select id="filterCompany">
                            <option value="">All Company</option>
                            @foreach ($companies as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sppb-f relative w-44">
                        <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" /></svg></span>
                        <select id="filterPoType">
                            <option value="">All PO Types</option>
                            @foreach ($poTypes as $poType)
                                <option value="{{ $poType }}">{{ $poType }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sppb-f relative w-56" id="wrapStatus" style="display:none;">
                        <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>
                        <select id="filterStatus">
                            <option value="">All Status</option>
                            <option value="H">Unsend</option>

                            <option value="P_T">Purchase</option>
                            <option value="P_F">Purchase - Unsend Email</option>

                            <option value="O">Partial</option>
                            <option value="C">Completed</option>
                            <option value="X">Canceled</option>
                            <option value="D">Reuse</option>
                        </select>
                    </div>
                    <button type="button" id="btnReset"
                        class="inline-flex h-10 items-center rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-gray-50 hover:shadow-md active:translate-y-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                        <i class="fas fa-rotate-right mr-2 text-xs"></i>Reset
                    </button>
            </x-list-page-header>

            <div class="relative overflow-hidden">
                <table id="poTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="dtr-control w-10 px-4 py-3"></th>
                            <th class="px-4 py-3 text-left font-medium">PO Nbr</th>

                            <th class="w-32 px-4 py-3 text-left font-medium">CS Number</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">SPPBJKT Number</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">PO Date</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">Company</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">PO Type</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">Vendor</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">Delivery Date</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">Purpose</th>
                            {{-- <th class="w-32 px-6 py-2 font-medium">Total</th>
                            <th class="w-32 px-6 py-2 font-medium">Tax</th> --}}
                            <th class="w-32 px-4 py-3 text-left font-medium">Grand Total</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">Created By</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>

    </div>

    <script>
        $(document).ready(function() {
            // ===== state tab =====
            // let activeTab = localStorage.getItem('poActiveTab') || 'my'; // default My PO
            const isFinanceAccess = @json($isFinanceAccess);

            // default tab:
            // - FINACCESS: all
            // - non-fin: my
            let activeTab = localStorage.getItem('poActiveTab') || (isFinanceAccess ? 'all' : 'my');

            // kalau FINACCESS tapi localStorage nyangkut "my", paksa ke "all"
            if (isFinanceAccess && activeTab === 'my') activeTab = 'all';

            // kalau non-fin tapi tombol my tidak ada (jaga-jaga), fallback ke all
            if (!document.querySelector('.po-tab[data-tab="my"]') && activeTab === 'my') activeTab = 'all';

            const $title = $('#poTitle');

            function setTabUI(tab) {
                // guard: kalau my tab tidak ada (FINACCESS), paksa all
                if (!document.querySelector('.po-tab[data-tab="my"]') && tab === 'my') {
                    tab = 'all';
                    activeTab = 'all';
                    localStorage.setItem('poActiveTab', 'all');
                }

                $('.po-tab').removeClass('bg-blue-600 text-white border-blue-600')
                    .addClass(
                        'bg-white text-gray-700 border-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600'
                    );

                $(`.po-tab[data-tab="${tab}"]`)
                    .addClass('bg-blue-600 text-white border-blue-600')
                    .removeClass(
                        'bg-white text-gray-700 border-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600'
                    );

                // if (tab === 'my') {
                //     $('#wrapStatus').show();
                //     $title.text('Purchase Order - My PO');
                // } else {
                //     $('#wrapStatus').hide();
                //     $('#filterStatus').val('');
                //     $title.text('Purchase Order - All PO');
                // }

                $('#wrapStatus').show(); // always show

                if (tab === 'my') {
                    $title.text('Purchase Order - My PO');
                } else {
                    $title.text('Purchase Order - All PO');
                }
            }



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
                return `<a href="${url}" target="_blank" rel="noopener" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white" title="Open"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:-rotate-6 group-hover/doc:scale-125 group-hover/doc:opacity-100"><i class="fa-regular fa-file-lines"></i></span><span>${text}</span></a>`;
            }

            function renderStatusBadge(row) {
                const label = row.status_label ?? row.status ?? '-';
                const L = String(label).toLowerCase();
                let c = 'bg-slate-50 text-slate-600 ring-slate-200 dark:bg-white/5 dark:text-slate-300 dark:ring-white/10', d = 'bg-slate-400';
                if (L.includes('unsend email')) { c = 'bg-orange-50 text-orange-700 ring-orange-200 dark:bg-orange-500/10 dark:text-orange-300 dark:ring-orange-500/30'; d = 'bg-orange-500'; }
                else if (L === 'unsend') { c = 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/30'; d = 'bg-blue-500'; }
                else if (L === 'purchase' || L === 'on progress') { c = 'bg-yellow-50 text-yellow-700 ring-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:ring-yellow-500/30'; d = 'bg-yellow-500'; }
                else if (L === 'partial') { c = 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30'; d = 'bg-amber-500'; }
                else if (L === 'completed') { c = 'bg-green-50 text-green-700 ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30'; d = 'bg-green-500'; }
                else if (L === 'canceled') { c = 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/30'; d = 'bg-rose-500'; }
                return `<span class="inline-flex min-w-[116px] items-center justify-center gap-2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset ${c}"><span class="h-1.5 w-1.5 rounded-full ${d}"></span>${label}</span>`;
            }

            // ===== DataTable =====
            const table = $('#poTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],
                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fa-solid fa-file-excel"></i> Excel',
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
                        text: '<i class="fa-solid fa-file-csv"></i> CSV',
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
                        target: 0
                    }
                },
                order: [
                    [12, 'asc'],
                    [4, 'desc']
                ],
                ajax: {
                    url: "{{ route('polist.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.tab = activeTab; // my / all
                        d.company = ($('#filterCompany').val() || ''); // optional
                        d.potype = ($('#filterPoType').val() || '');
                        d.creator = ($('#filterCreator').val() || ''); // optional / hidden for non-fin
                        d.status = ($('#filterStatus').val() || ''); // only My PO (All PO will send '')
                    }
                },
                columns: [{
                        data: null,
                        defaultContent: '',
                        className: 'dtr-control',
                        orderable: false,
                        searchable: false,
                        width: '32px'
                    },
                    {
                        data: 'ponbr',
                        className: 'text-left',
                        width: '42px',
                        render: (_v, t, row) => renderPONbr(_v, row),
                    },
                    {
                        data: 'csid',
                        className: 'text-left',
                        render: (v) => v ?? '-'
                    },
                    {
                        data: 'sppbjktid',
                        className: 'text-left',
                        render: (v) => v ?? '-'
                    },
                    {
                        data: 'podate',
                        className: 'text-center',
                        width: '42px',
                        render: (v) => fmtDate(v)
                    },
                    {
                        data: 'cpny_id',
                        width: '42px',
                        className: 'text-center'
                    },
                    {
                        data: 'potype',
                        width: '42px',
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
                        data: 'keperluan',
                        className: 'text-left',
                        render: (v) => v ?? '-'
                    },

                    // {
                    //     data: 'totalamt',
                    //     className: 'text-right',
                    //     render: (v) => fmtNumber(v)
                    // },
                    // {
                    //     data: 'taxamt',
                    //     className: 'text-right',
                    //     render: (v) => fmtNumber(v)
                    // },
                    {
                        data: 'grandtotalamt',
                        className: 'text-right',
                        render: function(data, type) {

                            // EXPORT / SORT = raw numeric
                            if (type === 'export' || type === 'sort') {
                                return parseFloat(data ?? 0);
                            }

                            // DISPLAY
                            return new Intl.NumberFormat('id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }).format(parseFloat(data ?? 0));
                        }
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

            function reloadAndResetState() {
                table.state.clear(); // biar gak nyangkut state tab lama
                table.ajax.reload(null, true);
            }

            // styled dropdowns (Select2, same skin as the SPP lists)
            sppInitFilters();

            // ===== init tab UI =====
            setTabUI(activeTab);

            // ===== tab click =====
            $(document).on('click', '.po-tab', function() {
                activeTab = $(this).data('tab');
                localStorage.setItem('poActiveTab', activeTab);

                setTabUI(activeTab);
                reloadAndResetState();
            });

            // ===== filter change =====
            $('#filterCompany, #filterStatus, #filterPoType').on('change', function() {
                reloadAndResetState();
            });

            $('#filterCreator').on('input', _.debounce(function() {
                reloadAndResetState();
            }, 450));

            // Reset
            $('#btnReset').on('click', function() {
                $('#filterCompany').val('');
                $('#filterPoType').val('');
                $('#filterStatus').val('');
                @if ($isFinanceAccess)
                    $('#filterCreator').val('');
                @endif
                $('#filterCompany, #filterPoType, #filterStatus, #filterBudgetStatus').trigger('change.select2');
                reloadAndResetState();
            });

        });
    </script>

    {{-- lodash debounce (kalau belum ada) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"></script>
</x-app-layout>
