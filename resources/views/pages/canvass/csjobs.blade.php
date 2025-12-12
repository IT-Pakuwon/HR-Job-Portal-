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

        /* === Filter Section === */
        #tblMine_filter,
        #tblEntryCS_filter,
        #tblRevision_filter,
        #tblAll_filter,
        #tblSppbjkt_filter {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }

        #tblMine_filter label,
        #tblEntryCS_filter label,
        #tblRevision_filter label,
        #tblAll_filter label,
        #tblSppbjkt_filter label {
            margin-right: 2px;
        }

        #tblMine_filter input,
        #tblEntryCS_filter input,
        #tblRevision_filter input,
        #tblAll_filter input,
        #tblSppbjkt_filter input {
            width: auto;
            padding: 0.25rem 0.5rem;
            min-width: 80px;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
        }

        /* === Wrapper Width === */
        #tblMine_wrapper,
        #tblEntryCS_wrapper,
        #tblRevision_wrapper,
        #tblAll_wrapper,
        #tblSppbjkt_wrapper {
            width: 100%;
        }

        /* === Cell Formatting === */
        #tblMine td,
        #tblEntryCS td,
        #tblRevision td,
        #tblAll td,
        #tblSppbjkt td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #tblMine th,
        #tblEntryCS th,
        #tblRevision th,
        #tblAll th,
        #tblSppbjkt th,
        #tblMine td,
        #tblEntryCS td,
        #tblRevision td,
        #tblAll td,
        #tblSppbjkt td {
            padding: 10px;
            max-width: 200px;
        }

        /* === Length Section === */
        #tblMine_length,
        #tblEntryCS_length,
        #tblRevision_length,
        #tblAll_length,
        #tblSppbjkt_length {
            width: auto;
            display: flex;
            justify-content: flex-start;
        }

        #tblMine_length select,
        #tblEntryCS_length select,
        #tblRevision_length select,
        #tblAll_length select,
        #tblSppbjkt_length select {
            width: auto;
            padding: 0.25rem 0.5rem;
            min-width: 80px;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
        }

        /* === Info + Pagination === */
        #tblMine_info,
        #tblEntryCS_info,
        #tblRevision_info,
        #tblAll_info,
        #tblSppbjkt_info {
            margin: 10px 0;
        }

        .dataTables_paginate {
            margin: 10px 0;
        }

        /* === Hover Effects === */
        #tblMine tbody tr,
        #tblEntryCS tbody tr,
        #tblRevision tbody tr,
        #tblAll tbody tr,
        #tblSppbjkt tbody tr {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        #tblMine tbody tr:hover,
        #tblEntryCS tbody tr:hover,
        #tblRevision tbody tr:hover,
        #tblAll tbody tr:hover,
        #tblSppbjkt tbody tr:hover {
            background-color: #8f8f8f11;
            cursor: pointer;
        }

        #tblMine tbody tr td,
        #tblEntryCS tbody tr td,
        #tblRevision tbody tr td,
        #tblAll tbody tr td,
        #tblSppbjkt tbody tr td {
            padding: 8px;
            line-height: 2;
        }

        /* === Column Width Alignment === */
        #tblMine th:nth-child(1),
        #tblMine td:nth-child(1),
        #tblEntryCS th:nth-child(1),
        #tblEntryCS td:nth-child(1),
        #tblRevision th:nth-child(1),
        #tblRevision td:nth-child(1),
        #tblAll th:nth-child(1),
        #tblAll td:nth-child(1),
        #tblSppbjkt th:nth-child(1),
        #tblSppbjkt td:nth-child(1) {
            width: 120px;
            text-align: center;
        }

        #tblMine th:nth-child(4),
        #tblMine td:nth-child(4),
        #tblEntryCS th:nth-child(4),
        #tblEntryCS td:nth-child(4),
        #tblRevision th:nth-child(4),
        #tblRevision td:nth-child(4),
        #tblAll th:nth-child(4),
        #tblAll td:nth-child(4),
        #tblSppbjkt th:nth-child(4),
        #tblSppbjkt td:nth-child(4) {
            width: 120px;
            text-align: center;
        }

        /* === Group Row & Collapse === */
        #tblMine tbody tr.collapsed-group-row,
        #tblEntryCS tbody tr.collapsed-group-row,
        #tblRevision tbody tr.collapsed-group-row,
        #tblAll tbody tr.collapsed-group-row,
        #tblSppbjkt tbody tr.collapsed-group-row {
            display: none;
        }

        #tblMine tr.group-row,
        #tblEntryCS tr.group-row,
        #tblRevision tr.group-row,
        #tblAll tr.group-row,
        #tblSppbjkt tr.group-row {
            background-color: #e6e6e6;
            font-weight: bold;
            cursor: pointer;
            user-select: none;
            color: #333;
        }

        #tblMine tr.group-row:hover,
        #tblEntryCS tr.group-row:hover,
        #tblRevision tr.group-row:hover,
        #tblAll tr.group-row:hover,
        #tblSppbjkt tr.group-row:hover {
            background-color: #d4d4d4;
        }

        #tblMine tr.group-row .fas,
        #tblEntryCS tr.group-row .fas,
        #tblRevision tr.group-row .fas,
        #tblAll tr.group-row .fas,
        #tblSppbjkt tr.group-row .fas {
            margin-right: 8px;
            width: 16px;
            text-align: center;
        }

        #tblMine tr.group-row td,
        #tblEntryCS tr.group-row td,
        #tblRevision tr.group-row td,
        #tblAll tr.group-row td,
        #tblSppbjkt tr.group-row td {
            padding: 10px !important;
            border-bottom: 1px solid #ddd;
        }

        #tblMine tr.group-row td:first-child,
        #tblEntryCS tr.group-row td:first-child,
        #tblRevision tr.group-row td:first-child,
        #tblAll tr.group-row td:first-child,
        #tblSppbjkt tr.group-row td:first-child {
            border-left: none;
        }

        /* === Custom Switch === */
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


    {{-- Select2 & Toastr (biarkan seperti sebelumnya) --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <style>
        /* Active / Selected state */
        .filter-card.active {
            transform: scale(1.02);
        }

        /* CS Jobs */
        #btn-mine.active {
            background-color: rgb(199 210 254);
            /* indigo-200 */
            border-color: rgb(67 56 202);
            /* indigo-700 */
        }

        /* CS Revision */
        #btn-revision.active {
            background-color: rgb(253 230 138);
            /* amber-200 */
            border-color: rgb(180 83 9);
            /* amber-700 */
        }

        /* All CS Jobs */
        #btn-all.active {
            background-color: rgb(229 231 235);
            /* gray-200 */
            border-color: rgb(31 41 55);
            /* gray-700 */
        }

        /* SPPBJKT IN Progress */
        #btn-sppbjkt.active {
            background-color: rgb(187 247 208);
            /* green-200 */
            border-color: rgb(21 128 61);
            /* green-700 */
        }
    </style>
    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid auto-rows-fr grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4">

            {{-- CS Jobs --}}
            <button type="button" class="w-full text-left">
                <div id="btn-mine"
                    class="filter-card flex h-full items-center gap-3 rounded-lg border border-indigo-700 bg-indigo-200/20 p-3 text-indigo-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-indigo-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">🗂️</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-base font-medium">CS Jobs</p>
                    </div>

                    <p id="count-mine" class="shrink-0 text-xl font-bold">{{ $mine }}</p>
                </div>
            </button>

            {{-- CS Revision --}}
            <button type="button" class="w-full text-left">
                <div id="btn-revision"
                    class="filter-card flex h-full items-center gap-3 rounded-lg border border-amber-700 bg-amber-200/20 p-3 text-amber-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-amber-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">📝</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-base font-medium">CS Revision</p>
                    </div>

                    <p id="count-revision" class="shrink-0 text-xl font-bold">{{ $revision }}</p>
                </div>
            </button>

            {{-- All CS Jobs --}}
            <button type="button" class="w-full text-left">
                <div id="btn-all"
                    class="filter-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">🌐</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-base font-medium">All CS Jobs</p>
                    </div>

                    <p id="count-all" class="shrink-0 text-xl font-bold">{{ $all }}</p>
                </div>
            </button>

            {{-- SPPBJKT IN Progress --}}
            <button type="button" class="w-full text-left">
                <div id="btn-sppbjkt"
                    class="filter-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">🚦</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-base font-medium">SPPBJKT IN Progress</p>
                    </div>

                    <p id="count-sppbjkt" class="shrink-0 text-xl font-bold">{{ $sppbjkt }}</p>
                </div>
            </button>

        </div>



        {{-- ====== PANES (tanpa tab) ====== --}}
        <div class="grid">
            <div class="mt-6 rounded-2xl bg-white p-6 dark:bg-gray-800">

                {{-- === PANE: CS Jobs + Entry CS (dua tabel) === --}}
                <div id="pane-mine">
                    <div>
                        <h2 class="mb-2 text-xl font-semibold">CS Jobs</h2>
                        <table id="tblMine" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="w-32 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                        Action</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">DocID
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Assign
                                        Date</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Company
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Assign
                                        Purchasing</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Assign
                                        By</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                        Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            </tbody>
                        </table>

                    </div>

                    <div class="mt-10">
                        <h2 class="mb-2 text-xl font-semibold">Entry CS (My CS)</h2>
                        <table id="tblEntryCS" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">CSID
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">SPPBJKT ID
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Date</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Company</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">User
                                        Peminta</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Note
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- === PANE: CS Jobs (with internal tab for Entry CS) === --}}
                {{-- <div id="pane-mine">
                    <!-- Internal Tab Buttons -->
                    <div class="mb-4 flex gap-3">
                        <button id="subtab-mine"
                            class="subtab-btn active rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            CS Jobs
                        </button>
                        <button id="subtab-entrycs"
                            class="subtab-btn rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                            Entry CS (My CS)
                        </button>
                    </div>

                    <!-- === Sub-pane: CS Jobs === -->
                    <div id="subpane-mine">
                        <h2 class="mb-2 text-xl font-semibold">CS Jobs</h2>
                        <table id="tblMine" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="w-32 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                        Action</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">DocID
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Assign Date</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Date</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Company</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Name</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Assign Purchasing</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Assign By</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                        Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            </tbody>
                        </table>
                    </div>

                    <!-- === Sub-pane: Entry CS === -->
                    <div id="subpane-entrycs" class="hidden">
                        <h2 class="mb-2 text-xl font-semibold">Entry CS (My CS)</h2>
                        <table id="tblEntryCS" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">CSID
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Date</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Company</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                        Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">User
                                        Peminta</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Note
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            </tbody>
                        </table>
                    </div>
                </div> --}}


                {{-- === PANE: My Revision === --}}
                {{-- === PANE: My Revision (TrPO Reuse) === --}}
                <div id="pane-revision" class="hidden">
                    <h2 class="mb-2 text-xl font-semibold">My Revision</h2>
                    <table id="tblRevision" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="w-2 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                    Action
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                    PO Number
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                    PO Date
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                    CSID
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                    SPPBJKT
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                    Company
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                    Department
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                    Vendor
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>
                </div>


                {{-- === PANE: All Jobs === --}}
                <div id="pane-all" class="hidden">
                    <h2 class="mb-2 text-xl font-semibold">All Jobs</h2>
                    <table id="tblAll" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">DocID
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Assign
                                    Date</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Date
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                    Company
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Name
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Assign
                                    Purchasing</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Assign
                                    By</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                    Department</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                    Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>
                </div>

                {{-- === PANE: SPPBJKT IN Progress === --}}
                <div id="pane-sppbjkt" class="hidden">
                    <h2 class="mb-2 text-xl font-semibold">SPPBJKT IN Progress</h2>
                    <table id="tblSppbjkt" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">DocID
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Assign
                                    Date</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Date
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                    Company</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Name
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Assign
                                    Purchasing</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Assign
                                    By</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">
                                    Department</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                    Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <script>
        $(function() {
            // ===== renderer util (sama seperti sebelumnya) =====
            const mapShowUrl = {
                SPPB: 'showsppbs',
                SPPJ: 'showsppjs',
                SPPK: 'showsppks',
                SPPT: 'showsppts'
            };

            function buildCreateUrl(row) {
                return `/createcs/${row.doc_type}/${row.eid}`;
            }

            function renderDocBtn(row) {
                const base = mapShowUrl[row.doc_type] || '#';
                return `<a href="/${base}/${row.eid}" class='inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-base leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-gray-500 hover:bg-gray-700'>${row.doc_no}</a>`;
            }

            function colSetWithoutCreate() {
                return [{
                        data: null,
                        className: 'text-left',
                        render: (_d, _t, row) => renderDocBtn(row)
                    },
                    {
                        data: 'assigndate',
                        className: 'text-center',
                        render: v => v ? (isNaN(new Date(v)) ? v : new Date(v).toLocaleDateString('id-ID')) : ''
                    },
                    {
                        data: 'doc_date',
                        className: 'text-left',
                        render: v => v ? (isNaN(new Date(v)) ? v : new Date(v).toLocaleDateString('id-ID')) : ''
                    },
                    {
                        data: 'cpny_id',
                        className: 'text-left'
                    },
                    {
                        data: 'created_by_name',
                        className: 'text-left',
                        defaultContent: '-'
                    },
                    {
                        data: 'assignpurchasing',
                        className: 'text-left',
                        defaultContent: ''
                    },
                    {
                        data: 'assignby',
                        className: 'text-left',
                        defaultContent: ''
                    },
                    {
                        data: 'department_id',
                        className: 'text-left'
                    },
                    {
                        data: 'keperluan',
                        className: 'text-left'
                    },
                ];
            }

            
            function colSetWithCreate() {
                const actionCol = {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-left',
                    render: (_d, _t, row) => {
                        const createUrl = `/createcs/${row.doc_type}/${row.eid}`;
                        // tombol + (buat CS) & X (complete sisaan)
                        return `
                    <div class="inline-flex gap-2">
                    <a href="${createUrl}"
                        class="inline-flex justify-center items-center px-3 py-1.5 text-sm font-medium text-white rounded bg-blue-500 hover:bg-blue-700"
                        title="Create CS">
                        <i class="fas fa-plus"></i>
                    </a>
                    <button type="button"
                        class="btn-complete-open inline-flex justify-center items-center px-3 py-1.5 text-sm font-medium text-white rounded bg-red-500 hover:bg-red-700"
                        data-doc="${row.doc_type}" data-eid="${row.eid}" title="Complete sisa yang tidak jadi diorder">
                        <i class="fas fa-times"></i>
                    </button>
                    </div>`;
                    }
                };
                return [actionCol, ...colSetWithoutCreate()];
            }


            // ===== Datatables init (tanpa parameter docType) =====
            const tblMine = $('#tblMine').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100, 250],
                ajax: {
                    url: "{{ route('csjobs.mine.json') }}",
                    type: "GET"
                },
                order: [
                    [3, 'desc'],
                    [1, 'desc']
                ],
                columns: colSetWithCreate(),
                searchDelay: 400,
                stateSave: true,
                responsive: true
            });

            const tblEntryCS = $('#tblEntryCS').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100, 250],
                ajax: {
                    url: "{{ route('csjobs.entry.json') }}",
                    type: "GET"
                },
                order: [
                    [1, 'desc'],
                    [0, 'desc']
                ],
                columns: [
                    {
                        data: 'csid',
                        className: 'text-left',
                        render: (v, _t, row) =>
                            `<a href="/editcs/${row.eid}" 
                            class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 
                                    text-base leading-tight font-semibold text-white rounded text-center 
                                    transition-colors duration-200 bg-yellow-500 hover:bg-yellow-700">
                                ${v}
                            </a>`
                    },
                    {
                        data: 'sppbjktid',
                        className: 'text-left',
                        render: (v, _t, row) => {
                            if (!row.sppbjkt_eid) return '-';

                            const mapShowUrl = {
                                SPPB: 'showsppbs',
                                SPPJ: 'showsppjs',
                                SPPK: 'showsppks',
                                SPPT: 'showsppts',
                            };

                            const base = mapShowUrl[row.sppbjkt_doc_type] || '#';

                            return `
                                <a href="/${base}/${row.sppbjkt_eid}"
                                class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-base leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-gray-500 hover:bg-gray-700">
                                    ${v}
                                </a>
                            `;
                        }
                    },
                    {
                        data: 'csdate',
                        className: 'text-center',
                        render: v => v ? (isNaN(new Date(v)) ? v : new Date(v).toLocaleDateString('id-ID')) : ''
                    },
                    {
                        data: 'cpny_id',
                        className: 'text-center'
                    },
                    {
                        data: 'department_id',
                        className: 'text-center'
                    },
                    {
                        data: 'user_peminta',
                        className: 'text-left',
                        defaultContent: '-'
                    },
                    {
                        data: 'csnote',
                        className: 'text-left',
                        defaultContent: '-'
                    },
                ],

                searchDelay: 400,
                stateSave: true,
                responsive: true
            });

            const tblAll = $('#tblAll').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100, 250],
                ajax: {
                    url: "{{ route('csjobs.all.json') }}",
                    type: "GET"
                },
                order: [
                    [2, 'desc'],
                    [0, 'desc']
                ],
                columns: colSetWithoutCreate(),
                searchDelay: 400,
                stateSave: true,
                responsive: true
            });

            const tblRevision = $('#tblRevision').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100, 250],
                ajax: {
                    url: "{{ route('csjobs.revision.json') }}",
                    type: "GET"
                },
                order: [
                    [2, 'desc'], // sort by PO Date
                    [1, 'desc']  // then by PO Number
                ],
                columns: colSetRevision(),
                searchDelay: 400,
                stateSave: true,
                responsive: true
            });

            const tblSppbjkt = $('#tblSppbjkt').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100, 250],
                ajax: {
                    url: "{{ route('csjobs.sppbjkt.progress.json') }}",
                    type: "GET"
                },
                order: [
                    [2, 'desc'],
                    [0, 'desc']
                ],
                columns: colSetWithoutCreate(),
                searchDelay: 400,
                stateSave: true,
                responsive: true
            });

            // ===== Switching panes via cards =====
            function showPane(key) {
                $('#pane-mine, #pane-revision, #pane-all, #pane-sppbjkt').addClass('hidden');
                $(`#pane-${key}`).removeClass('hidden');

                // tampilkan Entry CS hanya di CS Jobs
                if (key === 'mine') {
                    $('#pane-mine').find('#tblMine').DataTable().columns.adjust();
                    $('#pane-mine').find('#tblEntryCS').DataTable().columns.adjust();
                } else if (key === 'revision') {
                    tblRevision.columns.adjust();
                } else if (key === 'all') {
                    tblAll.columns.adjust();
                } else if (key === 'sppbjkt') {
                    tblSppbjkt.columns.adjust();
                }

                // highlight kartu aktif
                $('.filter-card').removeClass('active');
                $(`#btn-${key}`).addClass('active');
            }

            // default ke CS Jobs (mine)
            showPane('mine');

            $('#btn-mine').on('click', () => showPane('mine'));
            $('#btn-revision').on('click', () => showPane('revision'));
            $('#btn-all').on('click', () => showPane('all'));
            $('#btn-sppbjkt').on('click', () => showPane('sppbjkt'));

            // (Opsional) refresh counts
            function refreshCounts() {
                $.get("{{ route('csjobs.dataset.counts') }}")
                    .done(res => {
                        $('#count-mine').text(res.mine);
                        $('#count-revision').text(res.revision);
                        $('#count-all').text(res.all);
                        $('#count-sppbjkt').text(res.sppbjkt);
                    });
            }
            // refreshCounts(); // panggil bila diperlukan

            // Toggle .active class and remember selection
            const filters = document.querySelectorAll('.filter-card');
            const savedFilter = localStorage.getItem('activeFilter');

            if (savedFilter) {
                const activeFilter = document.querySelector(`#${savedFilter}`);
                if (activeFilter) activeFilter.classList.add('active');
            }

            filters.forEach(card => {
                card.addEventListener('click', e => {
                    e.preventDefault();
                    filters.forEach(c => c.classList.remove('active'));
                    card.classList.add('active');
                    localStorage.setItem('activeFilter', card.id);
                });
            });
        });
    </script>
    <script>
        function colSetRevision() {
        // kolom Action (Create CS untuk PO)
        const actionCol = {
            data: null,
            orderable: false,
            searchable: false,
            className: 'text-left',
            render: (_d, _t, row) => {
                // backend kirim doc_type = 'PO' dan eid = hashids(ponbr)
                const createUrl = `/createcs/${row.doc_type}/${row.eid}`;
                return `
                    <div class="inline-flex gap-2">
                        <a href="${createUrl}"
                            class="inline-flex justify-center items-center px-3 py-1.5 text-sm font-medium text-white rounded bg-blue-500 hover:bg-blue-700"
                            title="Create CS dari PO">
                            <i class="fas fa-plus"></i>
                        </a>
                    </div>`;
            }
        };

        return [
            actionCol,
            {
                data: 'ponbr',
                className: 'text-left',                
                render: (v, _t, row) =>
                            `<a href="/showpo/${row.eid}" class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-base leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-gray-500 hover:bg-gray-700">${v}</a>`
            },          

            {
                data: 'podate',
                className: 'text-center',
                render: v =>
                    v ? (isNaN(new Date(v)) ? v : new Date(v).toLocaleDateString('id-ID')) : ''
            },
            {
                data: 'csid',
                className: 'text-center',
                defaultContent: '-'
            },
            {
                data: 'sppbjktid',
                className: 'text-center',
                defaultContent: '-'
            },
            {
                data: 'cpny_id',
                className: 'text-center',
                defaultContent: '-'
            },
            {
                data: 'department_id',
                className: 'text-center',
                defaultContent: '-'
            },
            {
                data: 'vendorname',
                className: 'text-left',
                defaultContent: '-'
            },
        ];
    }

    </script>
    <Script>
        // Klik tombol X untuk complete sisa openordered
        $(document).on('click', '.btn-complete-open', function() {
            const doc = $(this).data('doc'); // SPPB | SPPJ | SPPK | SPPT
            const eid = $(this).data('eid'); // hashids dari src_id

            Swal.fire({
                title: 'Complete Sisa Order?',
                html: `
                    <div style="text-align:left;">
                        <p>Dokumen: <b>${doc}</b></p>
                        <p>Aksi ini akan menandai <b>semua sisa (open qty)</b> sebagai <b>Completed</b>.</p>                       
                        <p style="color:red; font-weight:bold;">Yakin ingin melanjutkan?</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Complete Sekarang',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true,
            }).then((result) => {
                if (!result.isConfirmed) return;

                const $btn = $(this).prop('disabled', true);

                $.ajax({
                        url: `/csjobs/complete/${doc}/${eid}`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                    })
                    .done(res => {
                        if (res.ok) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message || 'Sisa qty telah di-completed-kan.',
                                timer: 2000,
                                showConfirmButton: false
                            });

                            // Refresh semua tabel yang mungkin terpengaruh
                            try {
                                $('#tblMine').DataTable().ajax.reload(null, false);
                            } catch (e) {}
                            try {
                                $('#tblAll').DataTable().ajax.reload(null, false);
                            } catch (e) {}
                            try {
                                $('#tblRevision').DataTable().ajax.reload(null, false);
                            } catch (e) {}
                            try {
                                $('#tblSppbjkt').DataTable().ajax.reload(null, false);
                            } catch (e) {}
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: res.message || 'Gagal memproses aksi.',
                            });
                        }
                    })
                    .fail(xhr => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan pada server.',
                        });
                    })
                    .always(() => $btn.prop('disabled', false));
            });
        });
    </Script>
    
</x-app-layout>
