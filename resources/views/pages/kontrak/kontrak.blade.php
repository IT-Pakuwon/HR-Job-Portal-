<x-app-layout>
    <x-spp-list-styles />
    <x-spp-list-scripts />
    @php
        $currentPage = Route::currentRouteName() == 'kontrak.index' ? 'KONTRAK' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- ===== Tabs ===== --}}
        <div class="mb-4 flex flex-wrap items-center gap-2">
            @if (!$isFinanceAccess)
                <button type="button" id="tabMy" class="kontrak-tab h-10 rounded-lg border px-5 text-sm font-semibold shadow-sm transition-colors"
                    data-tab="my">
                    My Kontrak
                </button>
            @endif

            <button type="button" id="tabAll" class="kontrak-tab h-10 rounded-lg border px-5 text-sm font-semibold shadow-sm transition-colors"
                data-tab="all">
                All Kontrak
            </button>

            @if ($hasCostCtrlAccess)
                <button type="button" id="tabFinance" class="kontrak-tab h-10 rounded-lg border px-5 text-sm font-semibold shadow-sm transition-colors"
                    data-tab="finance">
                    Kontrak Finance
                </button>
            @endif
            @if (auth()->user()->hasRole('PURCHACCESS'))
                <a href="{{ route('kontrak.master-bq') }}" class="inline-flex h-10 items-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">Master Kontrak</a>
            @endif
        </div>

        <div
            class="dt-pretty mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="Kontrak" subtitle="Track and manage your contracts" title-id="kontrakTitle">
                    <div class="sppb-f relative w-44">
                        <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg></span>
                        <select id="filterCompany">
                            <option value="">All Company</option>
                            @foreach ($companies as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sppb-f relative w-44" id="wrapStatus" style="display:none;">
                        <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>
                        <select id="filterStatus">
                            <option value="">All Status</option>
                            <option value="H">Unsend</option>
                            <option value="P">On Progress</option>
                            <option value="C">Completed</option>
                        </select>
                    </div>
                    <div class="sppb-f relative w-44" id="wrapBudgetStatus" style="display:none;">
                        <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg></span>
                        <select id="filterBudgetStatus">
                            <option value="need">Need Budget</option>
                            <option value="done">Done Budget</option>
                        </select>
                    </div>
                    <button type="button" id="btnReset"
                        class="inline-flex h-10 items-center rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-gray-50 hover:shadow-md active:translate-y-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                        <i class="fas fa-rotate-right mr-2 text-xs"></i>Reset
                    </button>
            </x-list-page-header>

            <div class="relative overflow-hidden">
                <table id="kontrakTable" class="w-full min-w-full table-fixed border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="dtr-control w-10 px-4 py-3"></th>
                            <th class="w-44 px-4 py-3 text-left font-medium">Kontrak ID</th>
                            <th class="w-36 px-4 py-3 text-center! font-medium">Kontrak Date</th>
                            <th class="w-24 px-4 py-3 text-center! font-medium">Company</th>
                            <th class="w-24 px-4 py-3 text-center! font-medium">Type</th>
                            <th class="w-28 px-4 py-3 text-center! font-medium">Category</th>
                            <th class="min-w-[240px] px-4 py-3 text-left font-medium">Vendor</th>
                            <th class="w-32 px-4 py-3 text-center! font-medium">Start Date</th>
                            <th class="w-32 px-4 py-3 text-center! font-medium">End Date</th>
                            <th class="w-44 px-4 py-3 text-left font-medium">Created By</th>
                            <th class="w-36 px-4 py-3 text-center! font-medium">Status</th>
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

                $('.kontrak-tab').removeClass('bg-blue-600 text-white border-blue-600')
                    .addClass(
                        'bg-white text-gray-700 border-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600'
                    );

                $(`.kontrak-tab[data-tab="${tab}"]`)
                    .addClass('bg-blue-600 text-white border-blue-600')
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
                refreshS2();
            }

            // programmatic value changes need a Select2 refresh
            const refreshS2 = () => $('#filterStatus, #filterBudgetStatus').trigger('change.select2');

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
                const isEdit = url.indexOf('/createkontrak/') === 0;
                const tone = isEdit
                    ? 'border-amber-200 bg-amber-50 text-amber-800 hover:border-amber-500 hover:bg-amber-500 hover:text-white dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500 dark:hover:text-white'
                    : 'border-blue-100 bg-blue-50 text-blue-700 hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white';
                const icon = isEdit ? 'fa-solid fa-pen text-[10px]' : 'fa-regular fa-file-lines';

                return `<a href="${url}" rel="noopener" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 active:scale-95 ${tone}" title="${isEdit ? 'Edit draft' : 'Open'}"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100"><i class="${icon}"></i></span><span>${text}</span></a>`;
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
                        text: '<i class="fa-solid fa-file-excel"></i> Excel',
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
                        text: '<i class="fa-solid fa-file-csv"></i> CSV',
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
                        className: 'text-center',
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

            // styled dropdowns (Select2, same skin as the SPP lists)
            sppInitFilters();

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
                $('#filterCompany, #filterPoType, #filterStatus, #filterBudgetStatus').trigger('change.select2');
                reloadAndResetState();
            });
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"></script>
</x-app-layout>
