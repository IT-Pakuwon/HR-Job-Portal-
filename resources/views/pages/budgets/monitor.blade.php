<x-app-layout>
    <style>
        /* Filter selects: compact, icon prefix, custom chevron */
        .bm-select {
            -webkit-appearance: none; appearance: none; width: 100%; height: 40px;
            padding: 0 2.25rem 0 2.5rem; font-size: .875rem; color: #1f2937; cursor: pointer;
            background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: .625rem;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%239ca3af'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z' clip-rule='evenodd'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right .6rem center; background-size: 1.1rem;
            transition: border-color .15s, box-shadow .15s, background-color .15s;
        }
        /* Select2 skin matching the filter style */
        .bm-field .select2-container { width: 100% !important; }
        .bm-field .select2-container--default .select2-selection--single {
            height: 40px; display: flex; align-items: center; background: #f9fafb;
            border: 1px solid #e5e7eb; border-radius: .625rem; transition: border-color .15s, box-shadow .15s, background-color .15s;
        }
        .bm-field .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px; padding-left: 2.5rem; padding-right: 2rem; font-size: .875rem; color: #1f2937; width: 100%;
        }
        .bm-field .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; right: 8px; }
        .bm-field .select2-container--default:hover .select2-selection--single { border-color: #93c5fd; background: #fff; }
        .bm-field .select2-container--default.select2-container--focus .select2-selection--single,
        .bm-field .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #3b82f6; background: #fff; box-shadow: 0 0 0 3px rgb(59 130 246 / .18);
        }
        .bm-s2-dropdown { border: 1px solid #e5e7eb !important; border-radius: .75rem !important; overflow: hidden; box-shadow: 0 10px 25px rgb(0 0 0 / .10); margin-top: 4px; }
        .bm-s2-dropdown .select2-results__option { padding: .5rem .875rem; font-size: .875rem; }
        .bm-s2-dropdown .select2-results__option--highlighted[aria-selected] { background: #eff6ff !important; color: #1d4ed8 !important; }
        .bm-s2-dropdown .select2-results__option[aria-selected=true] { background: #dbeafe; color: #1d4ed8; font-weight: 600; }
        .bm-s2-dropdown .select2-search--dropdown { padding: .5rem; }
        .bm-s2-dropdown .select2-search--dropdown .select2-search__field { border-radius: .5rem; border: 1px solid #d1d5db; padding: .35rem .6rem; }
        .dark .bm-field .select2-container--default .select2-selection--single { background: #111827; border-color: #374151; }
        .dark .bm-field .select2-container--default .select2-selection--single .select2-selection__rendered { color: #f3f4f6; }
        .dark .bm-s2-dropdown { border-color: #374151 !important; }
        .bm-select:hover { border-color: #93c5fd; background-color: #fff; }
        .bm-select:focus { outline: none; background-color: #fff; border-color: #3b82f6; box-shadow: 0 0 0 3px rgb(59 130 246 / .18); }
        .dark .bm-select { background-color: #111827; border-color: #374151; color: #f3f4f6; }
        .dark .bm-select:hover, .dark .bm-select:focus { background-color: #1f2937; }
        /* Budget monitor: polish DataTables controls inside the two panels */
        .bm-panel table.dataTable td.text-right { font-variant-numeric: tabular-nums; }
        .bm-panel table.dataTable tbody tr { transition: background-color .15s; }
        .bm-panel table.dataTable tbody tr:hover { background-color: rgb(239 246 255 / .6); }
        .dark .bm-panel table.dataTable tbody tr:hover { background-color: rgb(55 65 81 / .6); }
        .bm-panel table.dataTable tbody td { border-top: 1px solid #f3f4f6; padding: .65rem 1rem; }
        .dark .bm-panel table.dataTable tbody td { border-top-color: #374151; }
        .bm-panel .dt-toolbar { padding: 0 !important; margin: 0 !important; gap: 10px !important; }
        .bm-panel .dataTables_wrapper { padding: .5rem .75rem !important; }
        .bm-panel .dataTables_info, .bm-panel .dt-info { font-size: .8rem; color: #6b7280; }
    </style>
    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- FILTERS --}}
        <div class="flex flex-col gap-4 rounded-2xl border border-gray-100 bg-white px-5 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 xl:flex-row xl:items-end">
            <div class="flex items-center gap-3 xl:mb-0.5 xl:w-32 xl:shrink-0">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg></span>
                <div>
                    <div class="text-sm font-bold text-gray-800 dark:text-gray-100">Filters</div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400">Narrow the data</div>
                </div>
            </div>
            <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label for="fYear" class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tahun</label>
                    <div class="bm-field relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-10 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></span>
                        <select id="fYear" class="bm-select">
                            <option value="">Select</option>
                        @foreach ($years as $y)
                            <option value="{{ $y }}" {{ $y == ($defaultYear ?? '') ? 'selected' : '' }}>
                                {{ $y }}</option>
                        @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label for="fCompany" class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</label>
                    <div class="bm-field relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-10 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg></span>
                        <select id="fCompany" class="bm-select">
                            <option value="">Select</option>
                        @foreach ($companies as $c)
                            <option value="{{ $c }}" {{ $c == ($defaultCpny ?? '') ? 'selected' : '' }}>
                                {{ $c }}</option>
                        @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label for="fBU" class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Business Unit</label>
                    <div class="bm-field relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-10 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></span>
                        <select id="fBU" class="bm-select">
                            <option value="">Select</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label for="fDept" class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</label>
                    <div class="bm-field relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-10 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg></span>
                        <select id="fDept" class="bm-select">
                            <option value="">Select</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLES --}}
        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">

            {{-- MASTER BUDGET --}}
            <div class="bm-panel overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white px-5 py-3.5 dark:border-gray-700 dark:from-gray-800 dark:to-gray-800">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18M10 3v18M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" /></svg></span>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 dark:text-gray-100">Master Budget</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Budget, additions, reservations and usage per activity</p>
                    </div>
                </div>

                <div class="relative overflow-hidden">
                    <table id="tblMaster" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                                <th class="w-10 px-4 py-3 text-left font-semibold"></th>
                                <th class="w-32 px-4 py-3 text-left font-semibold">COA</th>
                                <th class="w-32 px-4 py-3 text-left font-semibold">Activity</th>
                                <th class="px-4 py-3 text-left font-semibold">Description</th>
                                <th class="w-32 px-4 py-3 text-right font-semibold">Budget</th>
                                <th class="w-32 px-4 py-3 text-right font-semibold">Additional</th>
                                <th class="w-32 px-4 py-3 text-right font-semibold">Reserved</th>
                                <th class="w-32 px-4 py-3 text-right font-semibold">Used</th>
                                <th class="w-32 px-4 py-3 text-right font-semibold">Remaining</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 bg-gray-50 px-4 py-4 dark:border-gray-700 dark:bg-gray-900">
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                        <div class="rounded-xl border border-gray-100 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-800 border-l-4 border-l-gray-400">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Budget</div>
                            <div id="mTotBudg" class="mt-1 text-sm font-bold tabular-nums text-gray-900 dark:text-white">Rp. 0</div>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-800 border-l-4 border-l-blue-500">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Additional</div>
                            <div id="mTotAddi" class="mt-1 text-sm font-bold tabular-nums text-blue-600">Rp. 0</div>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-800 border-l-4 border-l-amber-500">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Reserved</div>
                            <div id="mTotRese" class="mt-1 text-sm font-bold tabular-nums text-amber-600">Rp. 0</div>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-800 border-l-4 border-l-red-500">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Used</div>
                            <div id="mTotUsed" class="mt-1 text-sm font-bold tabular-nums text-red-600">Rp. 0</div>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-800 border-l-4 border-l-emerald-500">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Remaining</div>
                            <div id="mTotRem" class="mt-1 text-sm font-bold tabular-nums text-emerald-600">Rp. 0</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TRX BUDGET --}}
            <div class="bm-panel overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white px-5 py-3.5 dark:border-gray-700 dark:from-gray-800 dark:to-gray-800">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg></span>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 dark:text-gray-100">Trx Budget</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Budget transactions and where they came from</p>
                    </div>
                </div>

                <div class="relative overflow-hidden">
                    <table id="tblTrx" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                                <th class="w-10 px-4 py-3 text-left font-semibold"></th>
                                <th class="px-4 py-3 text-left font-semibold">Ref No</th>
                                <th class="px-4 py-3 text-left font-semibold">Date</th>
                                <th class="px-4 py-3 text-left font-semibold">Account</th>
                                <th class="px-4 py-3 text-left font-semibold">Activity</th>
                                <th class="px-4 py-3 text-left font-semibold">Description</th>
                                <th class="px-4 py-3 text-left font-semibold">Flow</th>
                                <th class="px-4 py-3 text-left font-semibold">Source</th>
                                <th class="px-4 py-3 text-right font-semibold">Amount</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 bg-gray-50 px-4 py-4 dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex justify-end">
                        <div class="min-w-[200px] rounded-xl border border-l-4 border-gray-100 border-l-indigo-500 bg-white p-3 shadow-sm dark:border-gray-700 dark:border-l-indigo-500 dark:bg-gray-800">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Amount</div>
                            <div id="tTotAmount" class="mt-1 text-sm font-bold tabular-nums text-indigo-600">Rp. 0</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    {{-- <script>
        function fmtID(n) {
            return 'Rp. ' + Number(n || 0).toLocaleString('id-ID');
        }

        function buildParams() {
            return {
                year: $('#fYear').val(),
                cpny_id: $('#fCompany').val(),
                business_unit_id: $('#fBU').val(),
                department_fin_id: $('#fDept').val(),
            };
        }

        function reloadBoth() {
            const p = buildParams();
            masterTable.ajax.url("{{ route('budgetmonitor.master.json') }}?" + $.param(p)).load();
            trxTable.ajax.url("{{ route('budgetmonitor.trx.json') }}?" + $.param(p)).load();
        }

        function loadBU() {
            const cpny = $('#fCompany').val();
            $('#fBU').html(`<option value="">Select</option>`);
            $('#fDept').html(`<option value="">Select</option>`);

            $.get("{{ route('budgetmonitor.options.businessUnits') }}", {
                cpny_id: cpny
            }, function(res) {
                (res.data || []).forEach(r => {
                    $('#fBU').append(
                        `<option value="${r.business_unit_id}">${r.business_unit_id}</option>`);
                });
            });
        }

        function loadDept() {
            const cpny = $('#fCompany').val();
            const bu = $('#fBU').val();
            $('#fDept').html(`<option value="">Select</option>`);

            $.get("{{ route('budgetmonitor.options.departments') }}", {
                cpny_id: cpny,
                business_unit_id: bu
            }, function(res) {
                (res.data || []).forEach(r => {
                    $('#fDept').append(
                        `<option value="${r.department_fin_id}">${r.department_fin_id}</option>`);
                });
            });
        }

        let masterTable, trxTable;

        $(document).ready(function() {
            masterTable = $('#tblMaster').DataTable({
                ajax: {
                    url: "{{ route('budgetmonitor.master.json') }}",
                    dataSrc: function(json) {
                        const tot = json.totals || {};
                        $('#mTotBudg').text(fmtID(tot.totalbudget));
                        $('#mTotAddi').text(fmtID(tot.totalbudget_add));
                        $('#mTotRese').text(fmtID(tot.total_reserve));
                        $('#mTotUsed').text(fmtID(tot.total_used));
                        $('#mTotRem').text(fmtID(tot.total_remaining));
                        return json.data || [];
                    }
                },
                processing: true,
                serverSide: false,
                responsive: {
                    details: {
                        type: 'column',
                        target: 0
                    }
                },
                columnDefs: [{
                    targets: 0,
                    width: '28px',
                    className: 'dtr-control',
                    orderable: false
                }],
                order: [
                    [1, 'asc']
                ],
                columns: [{
                        data: null,
                        defaultContent: ''
                    },
                    {
                        data: 'account_id'
                    },
                    {
                        data: 'activity_id'
                    },
                    {
                        data: 'activity_descr'
                    },
                    {
                        data: 'totalbudget',
                        className: 'text-right',
                        render: function(data, type) {
                            const val = Number(data || 0);
                            if (type === 'display') {
                                return fmtID(val);
                            }
                            return val;
                        }
                    },
                    {
                        data: 'totalbudget_add',
                        className: 'text-right',
                        render: function(data, type) {
                            const val = Number(data || 0);
                            if (type === 'display') {
                                return fmtID(val);
                            }
                            return val;
                        }
                    },
                    {
                        data: 'total_reserve',
                        className: 'text-right',
                        render: function(data, type) {
                            const val = Number(data || 0);
                            if (type === 'display') {
                                return fmtID(val);
                            }
                            return val;
                        }
                    },
                    {
                        data: 'total_used',
                        className: 'text-right',
                        render: function(data, type) {
                            const val = Number(data || 0);
                            if (type === 'display') {
                                return fmtID(val);
                            }
                            return val;
                        }
                    },
                    {
                        data: null,
                        className: 'text-right',
                        render: function(data, type, row) {

                            const bud = Number(row.totalbudget || 0);
                            const add = Number(row.totalbudget_add || 0);
                            const rese = Number(row.total_reserve || 0);
                            const used = Number(row.total_used || 0);

                            const remaining = bud + add - rese - used;

                            if (type === 'display') {
                                return fmtID(remaining); // show formatted
                            }

                            return remaining; // return numeric for sorting
                        }
                    },
                ]
            });

            trxTable = $('#tblTrx').DataTable({
                ajax: {
                    url: "{{ route('budgetmonitor.trx.json') }}",
                    dataSrc: function(json) {
                        const tot = json.totals || {};
                        $('#tTotAmount').text(fmtID(tot.budget_amount));
                        return json.data || [];
                    }
                },
                processing: true,
                serverSide: false,
                responsive: {
                    details: {
                        type: 'column',
                        target: 0
                    }
                },
                columnDefs: [{
                    targets: 0,
                    width: '28px',
                    className: 'dtr-control',
                    orderable: false
                }],
                order: [
                    [2, 'desc']
                ],
                columns: [{
                        data: null,
                        defaultContent: ''
                    },
                    {
                        data: 'refnbr'
                    },
                    {
                        data: 'submitdate'
                    },
                    {
                        data: 'account_id'
                    },
                    {
                        data: 'activity_id'
                    },
                    {
                        data: 'activity_descr'
                    },
                    {
                        data: 'budget_flow'
                    },
                    {
                        data: 'transaction_source'
                    },
                    {
                        data: 'budget_amount',
                        className: 'text-right',
                        render: function(data, type) {
                            const val = Number(data || 0);

                            if (type === 'display') {
                                return fmtID(val); // show Rp format
                            }

                            return val; // use number for sorting & filtering
                        }
                    },
                ]
            });

            $('#fYear').on('change', reloadBoth);
            $('#fCompany').on('change', function() {
                loadBU();
                reloadBoth();
            });
            $('#fBU').on('change', function() {
                loadDept();
                reloadBoth();
            });
            $('#fDept').on('change', reloadBoth);
        });
    </script> --}}
    <script>
        function fmtID(n) {
            return 'Rp. ' + Number(n || 0).toLocaleString('id-ID');
        }

        function buildParams() {
            return {
                year: $('#fYear').val(),
                cpny_id: $('#fCompany').val(),
                business_unit_id: $('#fBU').val(),
                department_fin_id: $('#fDept').val(),
            };
        }

        function reloadBoth() {
            if (masterTable) masterTable.ajax.reload(null, true);
            if (trxTable) trxTable.ajax.reload(null, true);
        }

        function loadBU(selectedBU = '') {
            const cpny = $('#fCompany').val();
            $('#fBU').html(`<option value="">Select</option>`);
            $('#fDept').html(`<option value="">Select</option>`);
            $('#fBU, #fDept').trigger('change.select2');

            if (!cpny) return;

            $.get("{{ route('budgetmonitor.options.businessUnits') }}", {
                cpny_id: cpny
            }, function(res) {
                (res.data || []).forEach(r => {
                    const sel = (String(r.business_unit_id) === String(selectedBU)) ? 'selected' : '';
                    $('#fBU').append(
                        `<option value="${r.business_unit_id}" ${sel}>${r.business_unit_id}</option>`);
                });
                $('#fBU').trigger('change.select2');
            });
        }

        function loadDept(selectedDept = '') {
            const cpny = $('#fCompany').val();
            const bu = $('#fBU').val();
            $('#fDept').html(`<option value="">Select</option>`).trigger('change.select2');

            if (!cpny || !bu) return;

            $.get("{{ route('budgetmonitor.options.departments') }}", {
                cpny_id: cpny,
                business_unit_id: bu
            }, function(res) {
                (res.data || []).forEach(r => {
                    const sel = (String(r.department_fin_id) === String(selectedDept)) ? 'selected' : '';
                    $('#fDept').append(
                        `<option value="${r.department_fin_id}" ${sel}>${r.department_fin_id}</option>`);
                });
                $('#fDept').trigger('change.select2');
            });
        }

        let masterTable, trxTable;

        $(document).ready(function() {
            masterTable = $('#tblMaster').DataTable({
                ajax: {
                    url: "{{ route('budgetmonitor.master.json') }}",
                    data: function(d) {
                        // ✅ selalu kirim filter terbaru
                        return Object.assign(d, buildParams());
                    },
                    dataSrc: function(json) {
                        const tot = json.totals || {};
                        $('#mTotBudg').text(fmtID(tot.totalbudget));
                        $('#mTotAddi').text(fmtID(tot.totalbudget_add));
                        $('#mTotRese').text(fmtID(tot.total_reserve));
                        $('#mTotUsed').text(fmtID(tot.total_used));
                        $('#mTotRem').text(fmtID(tot.total_remaining));
                        return json.data || [];
                    }
                },
                processing: true,
                serverSide: false,
                responsive: true,
                // columnDefs: [{
                //     targets: 0,
                //     width: '28px',
                //     className: 'dtr-control',
                //     orderable: false
                // }],
                order: [
                    [1, 'asc']
                ],
                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'Master Budget',
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
                        title: 'Master Budget',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                columns: [{
                        data: null,
                        defaultContent: '',
                        className: 'dtr-control',
                        orderable: false
                    },
                    {
                        data: 'account_id'
                    },
                    {
                        data: 'activity_id'
                    },
                    {
                        data: 'activity_descr'
                    },
                    {
                        data: 'totalbudget',
                        className: 'text-right',
                        render: function(data, type) {
                            const val = Number(data || 0);
                            if (type === 'display') {
                                return fmtID(val);
                            }
                            return val;
                        }
                    },
                    {
                        data: 'totalbudget_add',
                        className: 'text-right',
                        render: function(data, type) {
                            const val = Number(data || 0);
                            if (type === 'display') {
                                return fmtID(val);
                            }
                            return val;
                        }
                    },
                    {
                        data: 'total_reserve',
                        className: 'text-right',
                        render: function(data, type) {
                            const val = Number(data || 0);
                            if (type === 'display') {
                                return fmtID(val);
                            }
                            return val;
                        }
                    },
                    {
                        data: 'total_used',
                        className: 'text-right',
                        render: function(data, type) {
                            const val = Number(data || 0);
                            if (type === 'display') {
                                return fmtID(val);
                            }
                            return val;
                        }
                    },
                    {
                        data: null,
                        className: 'text-right',
                        render: function(data, type, row) {

                            const bud = Number(row.totalbudget || 0);
                            const add = Number(row.totalbudget_add || 0);
                            const rese = Number(row.total_reserve || 0);
                            const used = Number(row.total_used || 0);

                            const remaining = bud + add - rese - used;

                            if (type === 'display') {
                                return fmtID(remaining); // show formatted
                            }

                            return remaining; // return numeric for sorting
                        }
                    },
                ]
            });

            trxTable = $('#tblTrx').DataTable({
                ajax: {
                    url: "{{ route('budgetmonitor.trx.json') }}",
                    data: function(d) {
                        // ✅ selalu kirim filter terbaru
                        return Object.assign(d, buildParams());
                    },
                    dataSrc: function(json) {
                        const tot = json.totals || {};
                        $('#tTotAmount').text(fmtID(tot.budget_amount));
                        return json.data || [];
                    }
                },
                processing: true,
                serverSide: false,
                responsive: true,
                columnDefs: [{
                    targets: 0,
                    width: '28px',
                    className: 'dtr-control',
                    orderable: false
                }],
                order: [
                    [2, 'desc']
                ],
                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'Tr_Budget List',
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
                        title: 'Tr_Budget List',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                columns: [{
                        data: null,
                        defaultContent: '',
                        className: 'dtr-control',
                        orderable: false
                    },
                    {
                        data: 'refnbr',
                        render: function(data, type, row) {
                            if (type !== 'display' || !data) return data || '';

                            const link = document.createElement('a');
                            link.href = row.document_url || @json(url('/budgetmonitor/document')) + '/' + encodeURIComponent(data);
                            link.target = '_blank';
                            link.rel = 'noopener noreferrer';
                            link.className = 'text-indigo-600 underline hover:text-indigo-800 dark:text-indigo-400';
                            link.textContent = data;
                            return link.outerHTML;
                        }
                    },
                    {
                        data: 'submitdate'
                    },
                    {
                        data: 'account_id'
                    },
                    {
                        data: 'activity_id'
                    },
                    {
                        data: 'activity_descr'
                    },
                    {
                        data: 'budget_flow'
                    },
                    {
                        data: 'transaction_source'
                    },
                    {
                        data: 'budget_amount',
                        className: 'text-right',
                        render: function(data, type) {
                            const val = Number(data || 0);

                            if (type === 'display') {
                                return fmtID(val); // show Rp format
                            }

                            return val; // use number for sorting & filtering
                        }
                    },
                ]
            });

            // ✅ init default dropdown dari controller
            const defCpny = @json($defaultCpny ?? '');
            const defBU = @json($defaultBU ?? '');
            const defDept = @json($defaultDept ?? '');

            // Styled dropdowns (Select2) instead of the browser's native list
            $('#fYear, #fCompany, #fBU, #fDept').each(function() {
                $(this).select2({
                    width: '100%',
                    minimumResultsForSearch: this.id === 'fYear' ? Infinity : 8,
                    dropdownCssClass: 'bm-s2-dropdown'
                });
            });

            if (defCpny) {
                $('#fCompany').val(defCpny).trigger('change.select2');
                loadBU(defBU);
                // dept harus nunggu BU ke-set, jadi delay kecil / chain di on change:
                setTimeout(() => {
                    if (defBU) $('#fBU').val(defBU).trigger('change.select2');
                    loadDept(defDept);
                    setTimeout(() => {
                        if (defDept) $('#fDept').val(defDept).trigger('change.select2');
                        reloadBoth();
                    }, 200);
                }, 200);
            } else {
                reloadBoth();
            }

            $('#fYear').on('change', reloadBoth);

            $('#fCompany').on('change', function() {
                loadBU('');
                reloadBoth();
            });

            $('#fBU').on('change', function() {
                loadDept('');
                reloadBoth();
            });

            $('#fDept').on('change', reloadBoth);
        });
    </script>
</x-app-layout>
