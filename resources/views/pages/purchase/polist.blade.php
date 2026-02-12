<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'polist.index' ? 'PO' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- ===== Tabs ===== --}}
        <div class="mb-4 flex items-center gap-2">
            @if (!$isFinanceAccess)
                <button type="button" id="tabMy" class="po-tab rounded-lg border px-4 py-2 text-sm font-semibold"
                    data-tab="my">
                    My PO
                </button>
            @endif

            <button type="button" id="tabAll" class="po-tab rounded-lg border px-4 py-2 text-sm font-semibold"
                data-tab="all">
                All PO
            </button>
        </div>

        <div class="mt-2 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white" id="poTitle">Purchase Order</h1>

                {{-- ===== Filters ===== --}}
                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">

                    {{-- Company --}}
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-300">Company</label>
                        <select id="filterCompany"
                            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            <option value="">All</option>
                            @foreach ($companies as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>


                    {{-- Status (HANYA My PO) --}}
                    <div class="flex items-center gap-2" id="wrapStatus" style="display:none;">
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-300">Status</label>
                        <select id="filterStatus"
                            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            <option value="">All</option>
                            <option value="H">Unsend</option>
                            <option value="P">Purchase</option>
                            <option value="O">Partial</option>
                            <option value="C">Completed</option>
                            <option value="X">Canceled</option>
                            <option value="D">Reuse</option>
                        </select>
                    </div>

                    <button type="button" id="btnReset"
                        class="rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600">
                        Reset
                    </button>
                </div>
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
                            <th class="w-32 px-6 py-2 font-medium">PO Date</th>
                            <th class="w-32 px-6 py-2 font-medium">Company</th>
                            <th class="w-32 px-6 py-2 font-medium">PO Type</th>
                            <th class="w-32 px-6 py-2 font-medium">Vendor</th>
                            <th class="w-32 px-6 py-2 font-medium">Delivery Date</th>
                            <th class="w-32 px-6 py-2 font-medium">Purpose</th>
                            {{-- <th class="w-32 px-6 py-2 font-medium">Total</th>
                            <th class="w-32 px-6 py-2 font-medium">Tax</th> --}}
                            <th class="w-32 px-6 py-2 font-medium">Grand Total</th>
                            <th class="w-32 px-6 py-2 font-medium">Created By</th>
                            <th class="w-32 px-6 py-2 font-medium">Status</th>
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

                $('.po-tab').removeClass('bg-indigo-600 text-white border-indigo-600')
                    .addClass(
                        'bg-white text-gray-700 border-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600'
                    );

                $(`.po-tab[data-tab="${tab}"]`)
                    .addClass('bg-indigo-600 text-white border-indigo-600')
                    .removeClass(
                        'bg-white text-gray-700 border-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600'
                    );

                if (tab === 'my') {
                    $('#wrapStatus').show();
                    $title.text('Purchase Order - My PO');
                } else {
                    $('#wrapStatus').hide();
                    $('#filterStatus').val('');
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
                return `<a href="${url}" class="inline-flex min-w-[90px] justify-center rounded bg-gray-500 px-2 py-1 text-sm font-semibold text-white hover:bg-gray-700" rel="noopener">${text}</a>`;
            }

            function renderStatusBadge(row) {
                const label = row.status_label ?? row.status ?? '-';
                const cls = row.status_class ?? 'bg-gray-100 text-gray-700 border-gray-200';
                return `<span class="inline-flex items-center rounded-full border px-3 py-1 text-sm font-semibold ${cls}">${label}</span>`;
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
                        target: 0
                    }
                },
                order: [
                    [2, 'desc'],
                    [1, 'desc']
                ],
                ajax: {
                    url: "{{ route('polist.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.tab = activeTab; // my / all
                        d.company = ($('#filterCompany').val() || ''); // optional
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

            function reloadAndResetState() {
                table.state.clear(); // biar gak nyangkut state tab lama
                table.ajax.reload(null, true);
            }

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
            $('#filterCompany, #filterStatus').on('change', function() {
                reloadAndResetState();
            });

            $('#filterCreator').on('input', _.debounce(function() {
                reloadAndResetState();
            }, 450));

            // Reset
            $('#btnReset').on('click', function() {
                $('#filterCompany').val('');
                $('#filterStatus').val('');
                @if ($isFinanceAccess)
                    $('#filterCreator').val('');
                @endif
                reloadAndResetState();
            });

        });
    </script>

    {{-- lodash debounce (kalau belum ada) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"></script>
</x-app-layout>
