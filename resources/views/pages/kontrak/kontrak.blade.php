<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'kontrak.index' ? 'KONTRAK' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-8 py-4 sm:px-6 lg:px-8">

        {{-- ===== Tabs ===== --}}
        <div class="mb-4 flex items-center gap-2">
            @if(!$isFinanceAccess)
                <button type="button" id="tabMy"
                    class="kontrak-tab rounded-lg border px-4 py-2 text-sm font-semibold"
                    data-tab="my">
                    My Kontrak
                </button>
            @endif

            <button type="button" id="tabAll"
                class="kontrak-tab rounded-lg border px-4 py-2 text-sm font-semibold"
                data-tab="all">
                All Kontrak
            </button>
        </div>

        <div class="mt-2 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white" id="kontrakTitle">Kontrak</h1>

                {{-- ===== Filters ===== --}}
                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">

                    {{-- Company --}}
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-300">Company</label>
                        <select id="filterCompany"
                            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            <option value="">All</option>
                            @foreach($companies as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status (HANYA My) --}}
                    <div class="flex items-center gap-2" id="wrapStatus" style="display:none;">
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-300">Status</label>
                        <select id="filterStatus"
                            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            <option value="">All</option>
                            <option value="H">Unsend</option>
                            <option value="P">On Progress</option>
                            <option value="C">Completed</option>                           
                        </select>
                    </div>

                    <button type="button" id="btnReset"
                        class="rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600">
                        Reset
                    </button>
                </div>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="kontrakTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr class="transition-colors hover:bg-gray-100 dark:hover:bg-gray-700">
                            <th class="dtr-control"></th>
                            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Kontrak ID</th>
                            <th class="w-32 px-6 py-2 font-medium">Kontrak Date</th>
                            <th class="w-24 px-6 py-2 font-medium">Company</th>
                            <th class="w-24 px-6 py-2 font-medium">Type</th>
                            <th class="w-28 px-6 py-2 font-medium">Category</th>
                            <th class="px-6 py-2 font-medium">Vendor</th>
                            <th class="w-32 px-6 py-2 font-medium">Start Date</th>
                            <th class="w-32 px-6 py-2 font-medium">End Date</th>
                            <th class="w-32 px-6 py-2 font-medium">Created By</th>
                            <th class="w-28 px-6 py-2 font-medium">Status</th>
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

            // default tab:
            // - FINACCESS: all
            // - non-fin: my
            let activeTab = localStorage.getItem('kontrakActiveTab') || (isFinanceAccess ? 'all' : 'my');

            if (isFinanceAccess && activeTab === 'my') activeTab = 'all';
            if (!document.querySelector('.kontrak-tab[data-tab="my"]') && activeTab === 'my') activeTab = 'all';

            const $title = $('#kontrakTitle');

            function setTabUI(tab) {
                if (!document.querySelector('.kontrak-tab[data-tab="my"]') && tab === 'my') {
                    tab = 'all';
                    activeTab = 'all';
                    localStorage.setItem('kontrakActiveTab', 'all');
                }

                $('.kontrak-tab').removeClass('bg-indigo-600 text-white border-indigo-600')
                    .addClass('bg-white text-gray-700 border-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600');

                $(`.kontrak-tab[data-tab="${tab}"]`)
                    .addClass('bg-indigo-600 text-white border-indigo-600')
                    .removeClass('bg-white text-gray-700 border-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600');

                if (tab === 'my') {
                    $('#wrapStatus').show();
                    $title.text('Kontrak - My Kontrak');
                } else {
                    $('#wrapStatus').hide();
                    $('#filterStatus').val('');
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
                const isHold  = st === 'H';
                const isOwner = !!row.is_owner;

                // ===============================
                // RULE AKHIR
                // ===============================
                // - TAB my  : HOLD atau owner => edit
                // - TAB all : SELALU show (termasuk HOLD)
                let canEdit = false;

                if (activeTab === 'my') {
                    canEdit = isHold || isOwner;
                }

                const url = canEdit
                    ? `/createkontrak/${encodeURIComponent(row.eid)}`
                    : `/showkontrak/${encodeURIComponent(row.eid)}`;

                const text = row.kontrakid || row.eid;

                const cls = canEdit
                    ? 'bg-amber-600 hover:bg-amber-700'
                    : 'bg-gray-600 hover:bg-gray-700';

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
                const cls = row.status_class ?? 'bg-gray-100 text-gray-700 border-gray-200';
                return `<span class="inline-flex items-center rounded-full border px-3 py-1 text-sm font-semibold ${cls}">${label}</span>`;
            }

            const table = $('#kontrakTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [[10,25,50,100,250,-1],[10,25,50,100,250,'All']],
                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'List_Kontrak',
                        className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                        exportOptions: { columns: ':visible', modifier: { page: 'current' } }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '↓ CSV',
                        title: 'List_Kontrak',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: { columns: ':visible', modifier: { page: 'current' } }
                    }
                ],
                responsive: { details: { type: 'column', target: 0 } },
                order: [[2,'desc'],[1,'desc']],
                ajax: {
                    url: "{{ route('kontrak.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.tab     = activeTab;
                        d.company = ($('#filterCompany').val() || '');
                        d.creator = ($('#filterCreator').val() || '');
                        d.status  = ($('#filterStatus').val() || '');
                    }
                },
                columns: [
                    { data: null, defaultContent: '', className: 'dtr-control', orderable:false, searchable:false, width:'32px' },
                    { data: 'kontrakid', className: 'text-left', render: (_v, _t, row) => renderKontrakId(_v, row) },
                    { data: 'kontrakdate', className: 'text-center', render: (v) => fmtDate(v) },
                    { data: 'cpny_id', className: 'text-center' },
                    { data: 'kontraktype', className: 'text-center' },
                    { data: 'kontrakcategory', className: 'text-center' },
                    { data: 'vendorname', className: 'text-left' },
                    { data: 'startdate', className: 'text-center', render: (v) => fmtDate(v) },
                    { data: 'enddate', className: 'text-center', render: (v) => fmtDate(v) },
                    { data: 'created_by', className: 'text-left' },
                    { data: 'status', className: 'text-left', render: (_v, _t, row) => renderStatusBadge(row) },
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

            $('#filterCompany, #filterStatus').on('change', function() {
                reloadAndResetState();
            });

            // kalau kamu mau aktifkan creator filter untuk FINACCESS, tinggal tambahkan inputnya di view + debounce ini
            $('#filterCreator').on('input', _.debounce(function() {
                reloadAndResetState();
            }, 450));

            $('#btnReset').on('click', function() {
                $('#filterCompany').val('');
                $('#filterStatus').val('');
                @if($isFinanceAccess)
                    $('#filterCreator').val('');
                @endif
                reloadAndResetState();
            });
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"></script>
</x-app-layout>
