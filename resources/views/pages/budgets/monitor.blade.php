<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- FILTERS --}}
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <label class="text-xs font-semibold text-gray-700 dark:text-gray-200">Tahun</label>
                <select id="fYear" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white">
                    <option value="">Select</option>
                    @foreach ($years as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-700 dark:text-gray-200">Company</label>
                <select id="fCompany" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white">
                    <option value="">Select</option>
                    @foreach ($companies as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-700 dark:text-gray-200">Business Unit</label>
                <select id="fBU" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white">
                    <option value="">Select</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-700 dark:text-gray-200">Department</label>
                <select id="fDept" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white">
                    <option value="">Select</option>
                </select>
            </div>
        </div>

        {{-- TABLES --}}
        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">

            {{-- MASTER BUDGET --}}
            <div class="flex flex-col gap-4 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Master Budget</h1>
                </div>

                <div class="relative overflow-x-auto">
                    <table id="tblMaster" class="w-full text-left text-xs rtl:text-right">
                        <thead class="border-b bg-gray-50 text-sm dark:bg-gray-900">
                            <tr>
                                <th></th>
                                <th class="w-32 px-6 py-2 font-medium">COA</th>
                                <th class="w-32 px-6 py-2 font-medium">Activity</th>
                                <th class="px-6 py-3 font-medium">Description</th>
                                <th class="w-32 px-6 py-2 font-medium">Budget</th>
                                <th class="w-32 px-6 py-2 font-medium">Additional</th>
                                <th class="w-32 px-6 py-2 font-medium">Reserved</th>
                                <th class="w-32 px-6 py-2 font-medium">Used</th>
                                <th class="w-32 px-6 py-2 font-medium">Remaining</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-700 dark:bg-gray-900">
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Budget</div>
                            <div id="mTotBudg" class="mt-1 text-xs font-extrabold text-gray-900 dark:text-white">Rp. 0</div>
                        </div>
                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Additional</div>
                            <div id="mTotAddi" class="mt-1 text-xs font-extrabold text-blue-600">Rp. 0</div>
                        </div>
                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Reserved</div>
                            <div id="mTotRese" class="mt-1 text-xs font-extrabold text-amber-600">Rp. 0</div>
                        </div>
                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Used</div>
                            <div id="mTotUsed" class="mt-1 text-xs font-extrabold text-red-600">Rp. 0</div>
                        </div>

                        <!-- ✅ NEW -->
                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Remaining</div>
                            <div id="mTotRem" class="mt-1 text-xs font-extrabold text-emerald-600">Rp. 0</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TRX BUDGET --}}
            <div class="flex flex-col gap-4 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Trx Budget</h1>
                </div>

                <div class="relative overflow-x-auto">
                    <table id="tblTrx" class="w-full text-left text-xs rtl:text-right">
                        <thead class="border-b bg-gray-50 text-sm dark:bg-gray-900">
                            <tr>
                                <th></th>
                                <th class="px-6 py-3 font-medium">Ref No</th>
                                <th class="px-6 py-3 font-medium">Date</th>
                                <th class="px-6 py-3 font-medium">Account</th>
                                <th class="px-6 py-3 font-medium">Activity</th>
                                <th class="px-6 py-3 font-medium">Description</th>
                                <th class="px-6 py-3 font-medium">Flow</th>
                                <th class="px-6 py-3 font-medium">Source</th>
                                <th class="px-6 py-3 font-medium">Amount</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex justify-end">
                        <div class="rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-gray-800">
                            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Amount</div>
                            <div id="tTotAmount" class="mt-1 text-xs font-extrabold text-indigo-600">Rp. 0</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

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
            const p = buildParams();
            masterTable.ajax.url("{{ route('budgetmonitor.master.json') }}?" + $.param(p)).load();
            trxTable.ajax.url("{{ route('budgetmonitor.trx.json') }}?" + $.param(p)).load();
        }

        function loadBU() {
            const cpny = $('#fCompany').val();
            $('#fBU').html(`<option value="">Select</option>`);
            $('#fDept').html(`<option value="">Select</option>`);

            $.get("{{ route('budgetmonitor.options.businessUnits') }}", { cpny_id: cpny }, function(res) {
                (res.data || []).forEach(r => {
                    $('#fBU').append(`<option value="${r.business_unit_id}">${r.business_unit_id}</option>`);
                });
            });
        }

        function loadDept() {
            const cpny = $('#fCompany').val();
            const bu = $('#fBU').val();
            $('#fDept').html(`<option value="">Select</option>`);

            $.get("{{ route('budgetmonitor.options.departments') }}", { cpny_id: cpny, business_unit_id: bu }, function(res) {
                (res.data || []).forEach(r => {
                    $('#fDept').append(`<option value="${r.department_fin_id}">${r.department_fin_id}</option>`);
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
                responsive: { details: { type: 'column', target: 0 } },
                columnDefs: [{ targets: 0, width: '28px', className: 'dtr-control', orderable: false }],
                order: [[1,'asc']],
                columns: [
                    { data: null, defaultContent: '' },
                    { data: 'account_id' },
                    { data: 'activity_id' },
                    { data: 'activity_descr' },
                    { data: 'totalbudget', className: 'text-right', render: d => fmtID(d) },
                    { data: 'totalbudget_add', className: 'text-right', render: d => fmtID(d) },
                    { data: 'total_reserve', className: 'text-right', render: d => fmtID(d) },
                    { data: 'total_used', className: 'text-right', render: d => fmtID(d) },
                    {
                        data: null,
                        className: 'text-right',
                        render: function(d, t, row) {
                            const bud  = Number(row.totalbudget || 0);
                            const add  = Number(row.totalbudget_add || 0);
                            const rese = Number(row.total_reserve || 0);
                            const used = Number(row.total_used || 0);
                            return fmtID(bud + add - rese - used);
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
                responsive: { details: { type: 'column', target: 0 } },
                columnDefs: [{ targets: 0, width: '28px', className: 'dtr-control', orderable: false }],
                order: [[2,'desc']],
                columns: [
                    { data: null, defaultContent: '' },
                    { data: 'refnbr' },
                    { data: 'submitdate' },
                    { data: 'account_id' },
                    { data: 'activity_id' },
                    { data: 'activity_descr' },
                    { data: 'budget_flow' },
                    { data: 'transaction_source' },
                    { data: 'budget_amount', className: 'text-right', render: d => fmtID(d) },
                ]
            });

            $('#fYear').on('change', reloadBoth);
            $('#fCompany').on('change', function(){ loadBU(); reloadBoth(); });
            $('#fBU').on('change', function(){ loadDept(); reloadBoth(); });
            $('#fDept').on('change', reloadBoth);
        });
    </script>
</x-app-layout>