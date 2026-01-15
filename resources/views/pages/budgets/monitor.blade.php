<x-app-layout>

    <div class="max-w-9xl mx-auto w-full px-8 py-4 sm:px-6 lg:px-8">
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
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

            {{-- MASTER BUDGET --}}
            <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
                <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Master Budget</h1>
                </div>
                <!-- TABLE -->
                <div class="rounded-base relative overflow-x-auto">
                    <table id="tblMaster"class="text-body w-full text-left text-xs rtl:text-right">
                        <thead
                            class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-xs">
                            <tr>
                                <th class="w-8"></th>
                                <th class="w-32 px-6 py-2 font-medium">
                                    COA
                                </th>
                                <th class="w-32 px-6 py-2 font-medium">
                                    Activity
                                </th>
                                <th class="px-6 py-3 font-medium">
                                    Description
                                </th>
                                <th class="w-32 px-6 py-2 font-medium">
                                    Budget
                                </th>
                                <th class="w-32 px-6 py-2 font-medium">
                                    Additional
                                </th>
                                <th class="w-32 px-6 py-2 font-medium">
                                    Reserved
                                </th>
                                <th class="w-32 px-6 py-2 font-medium">
                                    Used
                                </th>
                            </tr>
                        </thead>

                        <tbody></tbody>
                        <!-- rows injected by DataTables -->
                        </tbody>
                    </table>
                </div>

                <!-- TOTAL SUMMARY -->
                <div class="border-t border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-700 dark:bg-gray-900">
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">

                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Total Budget
                            </div>
                            <div id="mTotBudg" class="mt-1 text-sm font-extrabold text-gray-900 dark:text-white">
                                Rp. 0
                            </div>
                        </div>

                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Total Additional
                            </div>
                            <div id="mTotAddi" class="mt-1 text-sm font-extrabold text-blue-600">
                                Rp. 0
                            </div>
                        </div>

                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Total Reserved
                            </div>
                            <div id="mTotRese" class="mt-1 text-sm font-extrabold text-amber-600">
                                Rp. 0
                            </div>
                        </div>

                        <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Total Used
                            </div>
                            <div id="mTotUsed" class="mt-1 text-sm font-extrabold text-red-600">
                                Rp. 0
                            </div>
                        </div>

                    </div>
                </div>

                {{-- <div class="border-t border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900">
                        <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-xs sm:grid-cols-4">

                            <div class="flex justify-between sm:block">
                                <span class="text-gray-500 dark:text-gray-400">Total Budget</span>
                                <span id="mTotBudg" class="font-bold text-gray-800 dark:text-gray-100">0</span>
                            </div>

                            <div class="flex justify-between sm:block">
                                <span class="text-gray-500 dark:text-gray-400">Total Additional</span>
                                <span id="mTotAddi" class="font-bold text-blue-600">0</span>
                            </div>

                            <div class="flex justify-between sm:block">
                                <span class="text-gray-500 dark:text-gray-400">Total Reserved</span>
                                <span id="mTotRese" class="font-bold text-amber-600">0</span>
                            </div>

                            <div class="flex justify-between sm:block">
                                <span class="text-gray-500 dark:text-gray-400">Total Used</span>
                                <span id="mTotUsed" class="font-bold text-red-600">0</span>
                            </div>



                        </div>
                    </div> --}}

            </div>

            {{-- TRX BUDGET --}}
            <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
                <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Trx Budget</h1>
                </div>

                <!-- TABLE -->
                <div class="rounded-base relative overflow-x-auto">
                    <table id="tblTrx" class="text-body w-full text-left text-xs rtl:text-right">
                        <thead
                            class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-xs">
                            <tr>
                                <th class="w-8"></th>
                                <th class="px-6 py-3 font-medium">
                                    Ref No
                                </th>
                                <th class="px-6 py-3 font-medium">
                                    Date
                                </th>
                                <th class="px-6 py-3 font-medium">
                                    Account
                                </th>
                                <th class="px-6 py-3 font-medium">
                                    Activity
                                </th>
                                <th class="px-6 py-3 font-medium">
                                    Description
                                </th>
                                <th class="px-6 py-3 font-medium">
                                    Flow
                                </th>
                                <th class="px-6 py-3 font-medium">
                                    Source
                                </th>
                                <th class="px-6 py-3 font-medium">
                                    Amount
                                </th>
                            </tr>
                        </thead>

                        <tbody></tbody>
                        <!-- rows injected by DataTables -->
                        </tbody>
                    </table>
                </div>

                <!-- TOTAL -->
                <div class="border-t border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex justify-end">
                        <div class="rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-gray-800">
                            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Total Amount
                            </div>
                            <div id="tTotAmount" class="mt-1 text-sm font-extrabold text-indigo-600">
                                Rp. 0
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function fmtID(n) {
            return Number(n || 0).toLocaleString('id-ID');
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

            const urlMaster = "{{ route('budgetmonitor.master.json') }}?" + $.param(p);
            const urlTrx = "{{ route('budgetmonitor.trx.json') }}?" + $.param(p);

            masterTable.ajax.url(urlMaster).load();
            trxTable.ajax.url(urlTrx).load();
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
                        return json.data || [];
                    }
                },
                processing: true,
                serverSide: false,
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],



                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'Master_Budget',
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
                        title: 'Master_Budget',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                // 🔥 END ADD

                responsive: {
                    details: {
                        type: 'column',
                        target: 0 // 👈 this is REQUIRED
                    }
                },

                columnDefs: [{
                    targets: 0,
                    className: 'dtr-control',
                    orderable: false
                }],

                order: [0, 'asc'],
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
                        render: d => fmtID(d)
                    },
                    {
                        data: 'totalbudget_add',
                        className: 'text-right',
                        render: d => fmtID(d)
                    },
                    {
                        data: 'total_reserve',
                        className: 'text-right',
                        render: d => fmtID(d)
                    },
                    {
                        data: 'total_used',
                        className: 'text-right',
                        render: d => fmtID(d)
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
                        type: 'column', // use a column as the toggle
                        target: 0 // first column
                    }
                },


                columnDefs: [{
                        targets: 0,
                        className: 'dtr-control',
                        orderable: false,
                        responsivePriority: 1
                    },
                    {
                        targets: [3, 4, 5, 6, 7],
                        responsivePriority: 1
                    }
                ],
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],



                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'Budget Monitor',
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
                        title: 'Budget Monitor',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                // 🔥 END ADD
                responsive: {
                    details: {
                        type: 'column',
                        target: 0 // 👈 this is REQUIRED
                    }
                },

                columnDefs: [{
                    targets: 0,
                    className: 'dtr-control',
                    orderable: false
                }],

                order: [1, 'asc'],
                columns: [{
                        data: null,
                        defaultContent: ''
                    }, {
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
                        render: d => fmtID(d)
                    },
                ]
            });

            // Filter change -> reload table
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
    </script>
</x-app-layout>
