<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'kontrak.index' ? 'KONTRAK' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- ===== Tabs ===== --}}
        <div class="mb-4 flex items-center gap-2">
            @if (!$isFinanceAccess)
                <button type="button" id="tabMy" class="kontrak-tab rounded-lg border px-4 py-2 text-sm font-semibold"
                    data-tab="my">
                    My Kontrak
                </button>
            @endif

            <button type="button" id="tabAll" class="kontrak-tab rounded-lg border px-4 py-2 text-sm font-semibold"
                data-tab="all">
                All Kontrak
            </button>

            @if ($hasCostCtrlAccess)
                <button type="button" id="tabFinance" class="kontrak-tab rounded-lg border px-4 py-2 text-sm font-semibold"
                    data-tab="finance">
                    Kontrak Finance
                </button>
            @endif
        </div>

        <div
            class="mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div
                class="flex flex-col gap-3 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] lg:flex-row lg:items-center lg:justify-between">
                <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100" id="kontrakTitle">
                    Kontrak</h2>

                {{-- ===== Filters ===== --}}
                <div class="flex flex-wrap items-center gap-3">

                    {{-- Company --}}
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-2.5 flex items-center text-gray-400 dark:text-gray-500">
                            <i class="fas fa-building text-xs"></i>
                        </span>
                        <select id="filterCompany"
                            class="rounded-lg border border-gray-200 bg-white py-2 pl-8 pr-6 text-sm font-medium text-gray-700 shadow-sm transition-colors focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            <option value="">All Company</option>
                            @foreach ($companies as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status (HANYA My) --}}
                    <div class="relative" id="wrapStatus" style="display:none;">
                        <span class="pointer-events-none absolute inset-y-0 left-2.5 flex items-center text-gray-400 dark:text-gray-500">
                            <i class="fas fa-check-circle text-xs"></i>
                        </span>
                        <select id="filterStatus"
                            class="rounded-lg border border-gray-200 bg-white py-2 pl-8 pr-6 text-sm font-medium text-gray-700 shadow-sm transition-colors focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            <option value="">All Status</option>
                            <option value="H">Unsend</option>
                            <option value="P">On Progress</option>
                            <option value="C">Completed</option>
                        </select>
                    </div>

                    <div class="relative" id="wrapBudgetStatus" style="display:none;">
                        <span class="pointer-events-none absolute inset-y-0 left-2.5 flex items-center text-gray-400 dark:text-gray-500">
                            <i class="fas fa-wallet text-xs"></i>
                        </span>
                        <select id="filterBudgetStatus"
                            class="rounded-lg border border-gray-200 bg-white py-2 pl-8 pr-6 text-sm font-medium text-gray-700 shadow-sm transition-colors focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            <option value="need">Need Budget</option>
                            <option value="done">Done Budget</option>
                        </select>
                    </div>

                    <button type="button" id="btnReset"
                        class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-600 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                        <i class="fas fa-rotate-right pr-1.5 text-xs"></i>Reset
                    </button>
                </div>
            </div>

            <div class="relative overflow-hidden">
                <table id="kontrakTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="dtr-control w-10 px-4 py-3"></th>
                            <th class="px-4 py-3 text-left font-medium">Kontrak ID</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">Kontrak Date</th>
                            <th class="w-24 px-4 py-3 text-left font-medium">Company</th>
                            <th class="w-24 px-4 py-3 text-left font-medium">Type</th>
                            <th class="w-28 px-4 py-3 text-left font-medium">Category</th>
                            <th class="px-4 py-3 text-left font-medium">Vendor</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">Start Date</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">End Date</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">Created By</th>
                            <th class="w-28 px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>
    </div>

    <script>
        $(document).ready(function() {
            const isFinanceAccess = @json($isFinanceAccess);
            const hasCostCtrlAccess = @json($hasCostCtrlAccess);

            // default tab:
            // - FINACCESS: all
            // - non-fin: my
            let activeTab = localStorage.getItem('kontrakActiveTab') || (isFinanceAccess ? 'all' : 'my');

            if (isFinanceAccess && activeTab === 'my') activeTab = 'all';
            if (!document.querySelector('.kontrak-tab[data-tab="my"]') && activeTab === 'my') activeTab = 'all';
            if (!hasCostCtrlAccess && activeTab === 'finance') activeTab = isFinanceAccess ? 'all' : 'my';

            const $title = $('#kontrakTitle');

            function setTabUI(tab) {
                if (!document.querySelector('.kontrak-tab[data-tab="my"]') && tab === 'my') {
                    tab = 'all';
                    activeTab = 'all';
                    localStorage.setItem('kontrakActiveTab', 'all');
                }

                $('.kontrak-tab').removeClass('bg-indigo-600 text-white border-indigo-600')
                    .addClass(
                        'bg-white text-gray-700 border-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600'
                    );

                $(`.kontrak-tab[data-tab="${tab}"]`)
                    .addClass('bg-indigo-600 text-white border-indigo-600')
                    .removeClass(
                        'bg-white text-gray-700 border-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600'
                    );

                if (tab === 'my') {
                    $('#wrapStatus').show();
                    $('#wrapBudgetStatus').hide();
                    $('#filterBudgetStatus').val('need');
                    $title.text('Kontrak - My Kontrak');
                } else if (tab === 'finance') {
                    $('#wrapStatus').hide();
                    $('#filterStatus').val('');
                    $('#wrapBudgetStatus').show();
                    $title.text('Kontrak - Kontrak Finance');
                } else {
                    $('#wrapStatus').hide();
                    $('#filterStatus').val('');
                    $('#wrapBudgetStatus').hide();
                    $('#filterBudgetStatus').val('need');
                    $title.text('Kontrak - All Kontrak');
                }
            }

            function fmtDate(v) {
                if (!v) return '';
                const d = new Date(v);
                return Number.isNaN(d.getTime()) ? v : d.toLocaleDateString('id-ID');
            }

            function renderKontrakId(_v, row) {
                const st = (row.status || '').toString().toUpperCase();
                const isHold = st === 'H';

                let url = '';
                let cls = '';

                // ===============================
                // RULE FINAL
                // ===============================
                if (activeTab === 'my') {
                    // MY TAB
                    if (isHold) {
                        url = `/createkontrak/${encodeURIComponent(row.eid)}`;
                        cls = 'bg-amber-600 hover:bg-amber-700';
                    } else {
                        url = `/showkontrak/${encodeURIComponent(row.eid)}`;
                        cls = 'bg-gray-600 hover:bg-gray-700';
                    }
                } else {
                    // ALL TAB (tidak diubah)
                    url = `/showkontrak/${encodeURIComponent(row.eid)}`;
                    cls = 'bg-gray-600 hover:bg-gray-700';
                }

                const text = row.kontrakid || row.eid;

                return `
                    <a href="${url}"
                    class="inline-flex min-w-[110px] justify-center rounded px-2 py-1 text-sm font-semibold text-white ${cls}"
                    rel="noopener">
                        ${text}
                    </a>
                `;
            }




            function renderStatusBadge(row) {
                const label = row.status_label ?? row.status ?? '-';
                const cls = row.status_class ?? 'bg-gray-200/60 text-gray-700 border border-gray-500/40'
                return `<span class="inline-flex items-center rounded border px-3 py-1.5 text-sm font-semibold ${cls}">${label}</span>`;
            }

            const table = $('#kontrakTable').DataTable({
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
                        title: 'List_Kontrak',
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
                        title: 'List_Kontrak',
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
                    url: "{{ route('kontrak.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.tab = activeTab;
                        d.company = ($('#filterCompany').val() || '');
                        d.creator = ($('#filterCreator').val() || '');
                        d.status = ($('#filterStatus').val() || '');
                        d.budget_status = ($('#filterBudgetStatus').val() || 'need');
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
                        data: 'kontrakid',
                        className: 'text-left',
                        render: (_v, _t, row) => renderKontrakId(_v, row)
                    },
                    {
                        data: 'kontrakdate',
                        className: 'text-center',
                        render: (v) => fmtDate(v)
                    },
                    {
                        data: 'cpny_id',
                        className: 'text-center'
                    },
                    {
                        data: 'kontraktype',
                        className: 'text-center'
                    },
                    {
                        data: 'kontrakcategory',
                        className: 'text-center'
                    },
                    {
                        data: 'vendorname',
                        className: 'text-left'
                    },
                    {
                        data: 'startdate',
                        className: 'text-center',
                        render: (v) => fmtDate(v)
                    },
                    {
                        data: 'enddate',
                        className: 'text-center',
                        render: (v) => fmtDate(v)
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
                table.state.clear();
                table.ajax.reload(null, true);
            }

            setTabUI(activeTab);

            $(document).on('click', '.kontrak-tab', function() {
                activeTab = $(this).data('tab');
                localStorage.setItem('kontrakActiveTab', activeTab);
                setTabUI(activeTab);
                reloadAndResetState();
            });

            $('#filterCompany, #filterStatus, #filterBudgetStatus').on('change', function() {
                reloadAndResetState();
            });

            // kalau kamu mau aktifkan creator filter untuk FINACCESS, tinggal tambahkan inputnya di view + debounce ini
            $('#filterCreator').on('input', _.debounce(function() {
                reloadAndResetState();
            }, 450));

            $('#btnReset').on('click', function() {
                $('#filterCompany').val('');
                $('#filterStatus').val('');
                $('#filterBudgetStatus').val('need');
                @if ($isFinanceAccess)
                    $('#filterCreator').val('');
                @endif
                reloadAndResetState();
            });
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"></script>
</x-app-layout>
