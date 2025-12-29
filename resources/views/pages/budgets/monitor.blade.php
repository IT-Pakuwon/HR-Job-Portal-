<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">

        {{-- FILTER BAR --}}
        <div class="mb-4 rounded-2xl bg-white p-4 dark:bg-gray-800">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Tahun</label>
                    <select id="fYear" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white">
                        <option value="">Select</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Company</label>
                    <select id="fCompany" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white">
                        <option value="">Select</option>
                        @foreach($companies as $c)
                            <option value="{{ $c }}">{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Business Unit</label>
                    <select id="fBU" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white">
                        <option value="">Select</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Department</label>
                    <select id="fDept" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white">
                        <option value="">Select</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- TABLES --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

            {{-- MASTER BUDGET --}}
            <div class="rounded-2xl bg-white dark:bg-gray-800">
                <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                    <h2 class="text-lg font-extrabold text-gray-700 dark:text-white">Master Budget</h2>
                </div>
                <div class="overflow-x-auto p-4">
                    <table id="tblMaster" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">COA</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Activity</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Deskripsi</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Total Budg</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Total Addi</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Total Rese</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Total Used</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>

                    <div class="mt-3 flex justify-end gap-6 text-sm font-bold text-gray-700 dark:text-gray-200">
                        <div>Total Budg: <span id="mTotBudg">0</span></div>
                        <div>Total Addi: <span id="mTotAddi">0</span></div>
                        <div>Total Rese: <span id="mTotRese">0</span></div>
                        <div>Total Used: <span id="mTotUsed">0</span></div>
                    </div>
                </div>
            </div>

            {{-- TRX BUDGET --}}
            <div class="rounded-2xl bg-white dark:bg-gray-800">
                <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                    <h2 class="text-lg font-extrabold text-gray-700 dark:text-white">Trx Budget</h2>
                </div>
                <div class="overflow-x-auto p-4">
                    <table id="tblTrx" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Ref Nbr</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Date</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Account ID</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Activity ID</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Descr</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Flow</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Source</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>

                    <div class="mt-3 flex justify-end text-sm font-bold text-gray-700 dark:text-gray-200">
                        <div>Total Amount: <span id="tTotAmount">0</span></div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function fmtID(n){
            return Number(n || 0).toLocaleString('id-ID');
        }

        function buildParams(){
            return {
                year: $('#fYear').val(),
                cpny_id: $('#fCompany').val(),
                business_unit_id: $('#fBU').val(),
                department_fin_id: $('#fDept').val(),
            };
        }

        function reloadBoth(){
            const p = buildParams();

            const urlMaster = "{{ route('budgetmonitor.master.json') }}?" + $.param(p);
            const urlTrx    = "{{ route('budgetmonitor.trx.json') }}?" + $.param(p);

            masterTable.ajax.url(urlMaster).load();
            trxTable.ajax.url(urlTrx).load();
        }

        function loadBU(){
            const cpny = $('#fCompany').val();
            $('#fBU').html(`<option value="">Select</option>`);
            $('#fDept').html(`<option value="">Select</option>`);

            $.get("{{ route('budgetmonitor.options.businessUnits') }}", { cpny_id: cpny }, function(res){
                (res.data || []).forEach(r => {
                    $('#fBU').append(`<option value="${r.business_unit_id}">${r.business_unit_id}</option>`);
                });
            });
        }

        function loadDept(){
            const cpny = $('#fCompany').val();
            const bu   = $('#fBU').val();
            $('#fDept').html(`<option value="">Select</option>`);

            $.get("{{ route('budgetmonitor.options.departments') }}", { cpny_id: cpny, business_unit_id: bu }, function(res){
                (res.data || []).forEach(r => {
                    $('#fDept').append(`<option value="${r.department_fin_id}">${r.department_fin_id}</option>`);
                });
            });
        }

        let masterTable, trxTable;

        $(document).ready(function(){

            masterTable = $('#tblMaster').DataTable({
                ajax: {
                    url: "{{ route('budgetmonitor.master.json') }}",
                    dataSrc: function(json){
                        const tot = json.totals || {};
                        $('#mTotBudg').text(fmtID(tot.totalbudget));
                        $('#mTotAddi').text(fmtID(tot.totalbudget_add));
                        $('#mTotRese').text(fmtID(tot.total_reserve));
                        $('#mTotUsed').text(fmtID(tot.total_used));
                        return json.data || [];
                    }
                },
                processing: true,
                serverSide: false,
                responsive: true,
                order: [[0,'asc']],
                columns: [
                    { data: 'account_id' },
                    { data: 'activity_id' },
                    { data: 'activity_descr' },
                    { data: 'totalbudget', className:'text-right', render: d => fmtID(d) },
                    { data: 'totalbudget_add', className:'text-right', render: d => fmtID(d) },
                    { data: 'total_reserve', className:'text-right', render: d => fmtID(d) },
                    { data: 'total_used', className:'text-right', render: d => fmtID(d) },
                ]
            });

            trxTable = $('#tblTrx').DataTable({
                ajax: {
                    url: "{{ route('budgetmonitor.trx.json') }}",
                    dataSrc: function(json){
                        const tot = json.totals || {};
                        $('#tTotAmount').text(fmtID(tot.budget_amount));
                        return json.data || [];
                    }
                },
                processing: true,
                serverSide: false,
                responsive: true,
                order: [[1,'desc']],
                columns: [
                    { data: 'refnbr' },
                    { data: 'submitdate' },
                    { data: 'account_id' },
                    { data: 'activity_id' },
                    { data: 'activity_descr' },
                    { data: 'budget_flow' },
                    { data: 'transaction_source' },
                    { data: 'budget_amount', className:'text-right', render: d => fmtID(d) },
                ]
            });

            // Filter change -> reload table
            $('#fYear').on('change', reloadBoth);

            $('#fCompany').on('change', function(){
                loadBU();
                reloadBoth();
            });

            $('#fBU').on('change', function(){
                loadDept();
                reloadBoth();
            });

            $('#fDept').on('change', reloadBoth);
        });
    </script>
</x-app-layout>
