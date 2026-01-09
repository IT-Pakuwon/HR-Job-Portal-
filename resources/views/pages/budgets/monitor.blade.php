<x-app-layout>
    <style>
        .no-border {
            border: none !important;
        }

        .grid {
            width: 100%;
        }

        select,
        textarea,
        input {
            width: 100%;
            /* Make all input elements take full width */
        }

        table.dataTable {
            width: 100% !important;
        }

        .dataTables_wrapper {
            width: 100%;
        }

        @media (max-width: 600px) {
            .dataTables_wrapper {
                padding: 0 10px;
            }
        }

        /* Wo Table Specific Styles */
        #tblMaster_filter {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }

        #wtblMaster_filter label {
            margin-right: 2px;
        }

        #tblMaster_filter input {
            width: auto;
            padding: 5px;
            min-width: 80px;
            padding: 0.25rem 0.5rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
        }

        #tblMaster_wrapper {
            width: 100%;
        }

        #tblMaster td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #tblMaster th,
        #tblMaster td {
            padding: 10px;
            max-width: 200px;
        }

        #tblMaster_length {
            width: auto;
            display: flex;
            justify-content: flex-start;
        }

        #tblMaster_length select {
            width: auto;
            padding: 5px;
            min-width: 80px;
            padding: 0.25rem 0.5rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
        }

        #tblMaster_length select option {
            padding: 5px;
        }

        #tblMaster_info {
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .dataTables_paginate {
            /* This class is for all DataTables paginations */
            margin-top: 10px;
            margin-bottom: 10px;
        }

        #tblMaster tbody tr td {
            padding: 8px 8px;
            line-height: 2;
        }

        #tblMaster tbody tr {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        #tblMaster tbody tr:hover {
            background-color: #8f8f8f11;
            opacity: 100%;
            cursor: pointer;
        }

        #tblMaster tbody tr:hover td {
            /* color: black; */
        }

        #tblMaster th:nth-child(1),
        #tblMaster td:nth-child(1) {
            width: 120px;
            text-align: center;
        }

        #tblMaster th:nth-child(4),
        #tblMaster td:nth-child(4) {
            width: 120px;
            text-align: center;
        }

        /* --- Custom Styles for RowGroup Collapse/Expand (Applied to tblMaster) --- */
        /* Initially hide rows in collapsed groups */
        #tblMaster tbody tr.collapsed-group-row {
            display: none;
        }

        /* Style for group rows */
        #tblMaster tr.group-row {
            background-color: #e6e6e6;
            /* Light gray background for group headers */
            font-weight: bold;
            cursor: pointer;
            user-select: none;
            /* Prevent text selection on click */
            color: #333;
            /* Darker text for group headers */
        }

        #tblMaster tr.group-row:hover {
            background-color: #d4d4d4;
            /* Slightly darker on hover */
        }

        /* Icon styling */
        #tblMaster tr.group-row .fas {
            margin-right: 8px;
            width: 16px;
            /* Ensure consistent icon width */
            text-align: center;
        }

        /* Adjust padding for group rows to look consistent with other cells */
        #tblMaster tr.group-row td {
            padding: 10px !important;
            border-bottom: 1px solid #ddd;
            /* Separator for groups */
        }

        /* Remove border from the first td in group row to match the colspan */
        #tblMaster tr.group-row td:first-child {
            border-left: none;
        }

        /* ✅ Custom Switch Button (Global, if used elsewhere) */
        .switch {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 22px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #4CAF50;
        }

        input:checked+.slider:before {
            transform: translateX(18px);
        }

        /* === DataTables Export Buttons (Cute Style) === */
        .dt-buttons {
            display: flex;
            gap: 8px;
            margin-right: 12px;
        }

        .dt-button {
            display: inline-flex !important;
            align-items: center;
            gap: 6px;
            padding: 6px 12px !important;
            border-radius: 9999px !important;
            border: 1px solid transparent !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            line-height: 1 !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
            transition: all .2s ease-in-out;
        }

        /* Excel */
        .dt-button.buttons-excel {
            background-color: #dcfce7 !important;
            /* green-100 */
            color: #166534 !important;
            /* green-800 */
            border-color: #86efac !important;
        }

        .dt-button.buttons-excel:hover {
            background-color: #bbf7d0 !important;
        }

        /* CSV */
        .dt-button.buttons-csv {
            background-color: #e0f2fe !important;
            /* sky-100 */
            color: #075985 !important;
            /* sky-800 */
            border-color: #7dd3fc !important;
        }

        .dt-button.buttons-csv:hover {
            background-color: #bae6fd !important;
        }

        /* Remove default DataTables button styles */
        .dt-button:focus,
        .dt-button:active {
            outline: none !important;
            box-shadow: none !important;
        }

        /* === Fix spacing between Length & Export buttons === */

        /* Make toolbar items flex-aligned */
        .dataTables_length,
        .dt-buttons,
        .dataTables_filter {
            display: flex;
            align-items: center;
        }


        /* ✅ Control gap manually */
        .dt-buttons {
            margin-left: 12px !important;
            /* ← adjust: 4–8px is perfect */
            margin-right: 0 !important;
        }
    </style>

    <style>
        .no-border {
            border: none !important;
        }

        .grid {
            width: 100%;
        }

        select,
        textarea,
        input {
            width: 100%;
            /* Make all input elements take full width */
        }

        table.dataTable {
            width: 100% !important;
        }

        .dataTables_wrapper {
            width: 100%;
        }

        @media (max-width: 600px) {
            .dataTables_wrapper {
                padding: 0 10px;
            }
        }

        /* Wo Table Specific Styles */
        #tblTrx_filter {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }

        #tblTrx_filter label {
            margin-right: 2px;
        }

        #tblTrx_filter input {
            width: auto;
            padding: 5px;
            min-width: 80px;
            padding: 0.25rem 0.5rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
        }

        #tblTrx_wrapper {
            width: 100%;
        }

        #tblTrx td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #tblTrx th,
        #tblTrx td {
            padding: 10px;
            max-width: 200px;
        }

        #tblTrx_length {
            width: auto;
            display: flex;
            justify-content: flex-start;
        }

        #tblTrx_length select {
            width: auto;
            padding: 5px;
            min-width: 80px;
            padding: 0.25rem 0.5rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
        }

        #tblTrx_length select option {
            padding: 5px;
        }

        #tblTrx_info {
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .dataTables_paginate {
            /* This class is for all DataTables paginations */
            margin-top: 10px;
            margin-bottom: 10px;
        }

        #tblTrx tbody tr td {
            padding: 8px 8px;
            line-height: 2;
        }

        #tblTrx tbody tr {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        #tblTrx tbody tr:hover {
            background-color: #8f8f8f11;
            opacity: 100%;
            cursor: pointer;
        }

        #tblTrx tbody tr:hover td {
            /* color: black; */
        }

        #tblTrx th:nth-child(1),
        #tblTrx td:nth-child(1) {
            width: 120px;
            text-align: center;
        }

        #tblTrx th:nth-child(4),
        #tblTrx td:nth-child(4) {
            width: 120px;
            text-align: center;
        }

        /* --- Custom Styles for RowGroup Collapse/Expand (Applied to wosTable) --- */
        /* Initially hide rows in collapsed groups */
        #tblTrx tbody tr.collapsed-group-row {
            display: none;
        }

        /* Style for group rows */
        #tblTrx tr.group-row {
            background-color: #e6e6e6;
            /* Light gray background for group headers */
            font-weight: bold;
            cursor: pointer;
            user-select: none;
            /* Prevent text selection on click */
            color: #333;
            /* Darker text for group headers */
        }

        #tblTrx tr.group-row:hover {
            background-color: #d4d4d4;
            /* Slightly darker on hover */
        }

        /* Icon styling */
        #tblTrx tr.group-row .fas {
            margin-right: 8px;
            width: 16px;
            /* Ensure consistent icon width */
            text-align: center;
        }

        /* Adjust padding for group rows to look consistent with other cells */
        #tblTrx tr.group-row td {
            padding: 10px !important;
            border-bottom: 1px solid #ddd;
            /* Separator for groups */
        }

        /* Remove border from the first td in group row to match the colspan */
        #tblTrx tr.group-row td:first-child {
            border-left: none;
        }

        /* ✅ Custom Switch Button (Global, if used elsewhere) */
        .switch {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 22px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #4CAF50;
        }

        input:checked+.slider:before {
            transform: translateX(18px);
        }
    </style>
    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">

        {{-- FILTER BAR --}}
        <div class="mb-4 rounded-2xl bg-white p-4 dark:bg-gray-800">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Tahun</label>
                    <select id="fYear"
                        class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white">
                        <option value="">Select</option>
                        @foreach ($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Company</label>
                    <select id="fCompany"
                        class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white">
                        <option value="">Select</option>
                        @foreach ($companies as $c)
                            <option value="{{ $c }}">{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Business Unit</label>
                    <select id="fBU"
                        class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white">
                        <option value="">Select</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Department</label>
                    <select id="fDept"
                        class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white">
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
                <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">

                    <!-- TABLE -->
                    <div class="overflow-x-auto">
                        <table id="tblMaster" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-100 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                        COA
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                        Activity
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                        Description
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                        Budget
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                        Additional
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                        Reserved
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                        Used
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-800">
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
                                <div id="mTotBudg" class="mt-1 text-lg font-extrabold text-gray-900 dark:text-white">
                                    Rp. 0
                                </div>
                            </div>

                            <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                                <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Total Additional
                                </div>
                                <div id="mTotAddi" class="mt-1 text-lg font-extrabold text-blue-600">
                                    Rp. 0
                                </div>
                            </div>

                            <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                                <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Total Reserved
                                </div>
                                <div id="mTotRese" class="mt-1 text-lg font-extrabold text-amber-600">
                                    Rp. 0
                                </div>
                            </div>

                            <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                                <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Total Used
                                </div>
                                <div id="mTotUsed" class="mt-1 text-lg font-extrabold text-red-600">
                                    Rp. 0
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- <div class="border-t border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900">
                        <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm sm:grid-cols-4">

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

            </div>

            {{-- TRX BUDGET --}}
            <div class="rounded-2xl bg-white dark:bg-gray-800">
                <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                    <h2 class="text-lg font-extrabold text-gray-700 dark:text-white">Trx Budget</h2>
                </div>
                <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">

                    <!-- TABLE -->
                    <div class="overflow-x-auto">
                        <table id="tblTrx" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-100 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                        Ref No
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                        Date
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                        Account
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                        Activity
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                        Description
                                    </th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                        Flow
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                        Source
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                        Amount
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-800">
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
                                <div id="tTotAmount" class="mt-1 text-lg font-extrabold text-indigo-600">
                                    Rp. 0
                                </div>
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


                // 🔥 ADD THIS
                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'Purchase_Order',
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
                        title: 'Purchase_Order',
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

                order: [
                    [0, 'asc']
                ],
                columns: [{
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
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],


                // 🔥 ADD THIS
                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'Purchase_Order',
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
                        title: 'Purchase_Order',
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

                order: [
                    [1, 'desc']
                ],
                columns: [{
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
