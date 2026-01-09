<x-app-layout>
    <style>
        /* Active / Selected state */
        .scope-filter.active .scope-card {
            transform: scale(1.02);
        }

        /* Receipt Jobs */
        .scope-filter[data-scope="receiptjobs"].active .scope-card {
            background-color: rgb(254 215 170);
            /* orange-200 */
            border-color: rgb(194 65 12);
            /* orange-700 */
            color: rgb(194 65 12);
        }

        /* On Progress */
        .scope-filter[data-scope="onprogress"].active .scope-card {
            background-color: rgb(191 219 254);
            /* blue-200 */
            border-color: rgb(29 78 216);
            /* blue-700 */
            color: rgb(29 78 216);
        }

        /* Completed */
        .scope-filter[data-scope="completed"].active .scope-card {
            background-color: rgb(187 247 208);
            /* green-200 */
            border-color: rgb(21 128 61);
            /* green-700 */
            color: rgb(21 128 61);
        }

        /* All */
        .scope-filter[data-scope="all"].active .scope-card {
            background-color: rgb(229 231 235);
            /* gray-200 */
            border-color: rgb(31 41 55);
            /* gray-700 */
            color: rgb(31 41 55);
        }

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
        #receiptTable_filter {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }

        #receiptTable_filter label {
            margin-right: 2px;
        }

        #receiptTable_filter input {
            width: auto;
            padding: 0.25rem 0.5rem;
            min-width: 80px;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
        }

        /* === Wrapper Width === */
        #receiptTable_wrapper {
            width: 100%;
        }

        /* === Cell Formatting === */
        #receiptTable td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding: 10px;
            max-width: 200px;
        }

        #receiptTable th {
            padding: 10px;
            max-width: 200px;
        }

        /* === Length Section === */
        #receiptTable_length {
            width: auto;
            display: flex;
            justify-content: flex-start;
        }

        #receiptTable_length select {
            width: auto;
            padding: 0.25rem 0.5rem;
            min-width: 80px;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
        }

        /* === Info + Pagination === */
        #receiptTable_info {
            margin: 10px 0;
        }

        .dataTables_paginate {
            margin: 10px 0;
        }

        /* === Hover Effects === */
        #receiptTable tbody tr {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        #receiptTable tbody tr:hover {
            background-color: #8f8f8f11;
            cursor: pointer;
        }

        #receiptTable tbody tr td {
            padding: 8px;
            line-height: 2;
        }

        /* === Column Width Alignment === */
        #receiptTable th:nth-child(1),
        #receiptTable td:nth-child(1),
        #receiptTable th:nth-child(4),
        #receiptTable td:nth-child(4) {
            width: 120px;
            text-align: center;
        }

        /* === Group Row & Collapse === */
        #receiptTable tbody tr.collapsed-group-row {
            display: none;
        }

        #receiptTable tr.group-row {
            background-color: #e6e6e6;
            font-weight: bold;
            cursor: pointer;
            user-select: none;
            color: #333;
        }

        #receiptTable tr.group-row:hover {
            background-color: #d4d4d4;
        }

        #receiptTable tr.group-row .fas {
            margin-right: 8px;
            width: 16px;
            text-align: center;
        }

        #receiptTable tr.group-row td {
            padding: 10px !important;
            border-bottom: 1px solid #ddd;
        }

        #receiptTable tr.group-row td:first-child {
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


    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid auto-rows-fr grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-7">

            {{-- Receipt Jobs --}}
            <a href="#" class="scope-filter group block h-full" data-scope="receiptjobs">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-xl">📦</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-base font-medium">Receipt Jobs</p>
                    </div>

                    <p class="shrink-0 text-xl font-extrabold">{{ $receiptjobs }}</p>
                </div>
            </a>

            {{-- Return Jobs --}}
            <a href="#" class="scope-filter group block h-full" data-scope="returnjobs">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-xl">↩️</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-base font-medium">Return Jobs</p>
                    </div>

                    <p class="shrink-0 text-xl font-extrabold">{{ $returnjobs }}</p>
                </div>
            </a>

            {{-- On Progress --}}
            <a href="#" class="scope-filter group block h-full" data-scope="onprogress">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-xl">⏳</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-base font-medium">On Progress</p>
                    </div>

                    <p class="shrink-0 text-xl font-extrabold">{{ $onProgress }}</p>
                </div>
            </a>

            {{-- Rejected --}}
            <a href="#" class="scope-filter group block h-full" data-scope="rejected">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-xl">❌</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-base font-medium">Rejected</p>
                    </div>

                    <p class="shrink-0 text-xl font-extrabold">{{ $rejected }}</p>
                </div>
            </a>

            {{-- Revise --}}
            <a href="#" class="scope-filter group block h-full" data-scope="revise">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-yellow-700 bg-yellow-200/20 p-3 text-yellow-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-yellow-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-xl">🛠️</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-base font-medium">Revise</p>
                    </div>

                    <p class="shrink-0 text-xl font-extrabold">{{ $revise }}</p>
                </div>
            </a>

            {{-- Completed --}}
            <a href="#" class="scope-filter group block h-full" data-scope="completed">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-xl">✅</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-base font-medium">Completed</p>
                    </div>

                    <p class="shrink-0 text-xl font-extrabold">{{ $completed }}</p>
                </div>
            </a>

            {{-- All --}}
            <a href="#" class="scope-filter group block h-full" data-scope="all">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-xl">🧾</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-base font-medium">All</p>
                    </div>

                    <p class="shrink-0 text-xl font-extrabold">{{ $all }}</p>
                </div>
            </a>

        </div>


        <div class="grid">
            <div class="mt-6 rounded-2xl bg-white dark:bg-gray-800">
                <div
                    class="flex flex-col items-start justify-between gap-4 border-b border-gray-200 p-4 sm:flex-row sm:items-center dark:border-gray-700">
                    <h1 class="text-xl font-extrabold text-gray-700 dark:text-white">Receipt</h1>
                </div>

                <div class="overflow-x-auto p-6">
                    <table id="receiptTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr id="thead-row"></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>
                </div>
            </div>

            <script>
                const currentUser = @json(auth()->user()->username ?? '');

                $(function() {
                    let scope = 'receiptjobs';
                    const $title = $('h1.text-xl.font-extrabold');
                    const $thead = $('#receiptTable thead');
                    const $theadRow = $('#thead-row');
                    let table;

                    const titleMap = {
                        receiptjobs: 'Receipt - Jobs',
                        returnjobs: 'Receipt - Return Jobs',
                        onprogress: 'Receipt - On Progress',
                        completed: 'Receipt - Completed',
                        rejected: 'Receipt - Rejected',
                        revise: 'Receipt - Revise',
                        all: 'Receipt - All',
                    };


                    function headerFor(sc) {
                        if (sc === 'receiptjobs') {
                            return `
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">PO Nbr</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">PO Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Company</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Vendor</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Delivery Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Created By</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                            `;
                        }
                        if (sc === 'returnjobs') {
                            return `
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Receipt Nbr</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Receipt Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">PO Nbr</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">SPPB/J/K/T</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Company</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Created By</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                            `;
                        }
                        // TrReceipt scopes (tanpa kolom "+")
                        return `
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Receipt Nbr</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Receipt Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Receipt Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">PO Nbr</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">SPPB/J/K/T</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Company</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Created By</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                        `;
                    }

                    function columnsFor(sc) {
                        let buttonClass =
                            'inline-flex items-center justify-center w-[100px] rounded bg-gray-500 py-1.5 text-white hover:bg-gray-700';
                        if (sc === 'receiptjobs') {
                            return [{
                                    data: null,
                                    orderable: false,
                                    searchable: false,
                                    render: (_v, t, row) => renderPlusCreate(row)
                                },
                                {
                                    data: 'ponbr',
                                    render: (_v, _t, row) => renderPoLink(row)
                                },
                                {
                                    data: 'podate',
                                    render: (_v, _t, row) => row.podate_fmt ?? '',
                                    className: 'text-left'
                                },
                                {
                                    data: 'cpny_id',
                                    className: 'text-left'
                                },
                                {
                                    data: 'vendorname'
                                },
                                {
                                    data: 'podeliverydate',
                                    render: (_v, _t, row) => row.podelivery_fmt ?? '',
                                    className: 'text-left'
                                },
                                {
                                    data: 'created_by'
                                },
                                {
                                    data: 'status',
                                    orderable: false,
                                    searchable: false,
                                    render: (_v, _t, row) => renderStatusBadge(row),
                                    className: 'text-left'
                                },

                            ];
                        }
                        if (sc === 'returnjobs') {
                            return [{
                                    data: null,
                                    orderable: false,
                                    searchable: false,
                                    render: (_v, t, row) => renderPlusReturn(row)
                                },
                                {
                                    data: 'receiptnbr',
                                    render: (_v, _t, row) => renderReceiptLink(row)
                                },
                                {
                                    data: 'receiptdate',
                                    render: (_v, _t, row) => row.receiptdate_fmt ?? '',
                                    className: 'text-left'
                                },
                                {
                                    data: 'ponbr',
                                    className: 'text-left'
                                },
                                {
                                    data: 'sppbjktid',
                                    className: 'text-left'
                                },
                                {
                                    data: 'cpny_id',
                                    className: 'text-left'
                                },
                                {
                                    data: 'created_by'
                                },
                                {
                                    data: 'status',
                                    orderable: false,
                                    searchable: false,
                                    render: (_v, _t, row) => renderStatusBadge(row),
                                    className: 'text-left'
                                },
                            ];
                        }
                        // TrReceipt scopes: tanpa kolom "+"
                        return [{
                                data: 'receiptnbr',
                                render: (_v, _t, row) => renderReceiptLink(row)
                            },
                            {
                                data: 'receiptdate',
                                render: (_v, _t, row) => row.receiptdate_fmt ?? '',
                                className: 'text-left'
                            },
                            {
                                data: 'receipttype'
                            },
                            {
                                data: 'ponbr',
                                className: 'text-left'
                                // render: (_v, _t, row) => renderPoLink(row)
                            },
                            {
                                data: 'sppbjktid',
                                className: 'text-left'
                                // render: (_v, _t, row) => renderSppbLink(row)
                            },
                            {
                                data: 'cpny_id',
                                className: 'text-left'
                            },
                            {
                                data: 'created_by'
                            },
                            {
                                data: 'status',
                                orderable: false,
                                searchable: false,
                                render: (_v, _t, row) => renderStatusBadge(row),
                                className: 'text-left'
                            },
                        ];
                    }

                    function orderFor(sc) {
                        if (sc === 'receiptjobs') return [
                            [2, 'desc'],
                            [1, 'desc']
                        ];
                        if (sc === 'returnjobs') return [
                            [2, 'desc'],
                            [1, 'desc']
                        ];
                        return [
                            [1, 'desc'],
                            [0, 'desc']
                        ];
                    }


                    function updateTitle(sc) {
                        $title.text(titleMap[sc] ?? 'Receipt');
                    }

                    function highlightActive(sc) {
                        $('.scope-filter').removeClass('#')
                            .each(function() {
                                if ($(this).data('scope') === sc) $(this).addClass(
                                    '#');
                            });
                    }

                    function resetThead(sc) {
                        $thead.empty().append('<tr id="thead-row"></tr>');
                        $('#thead-row').html(headerFor(sc));
                    }

                    function rebuild(sc) {
                        if ($.fn.DataTable.isDataTable('#receiptTable')) {
                            $('#receiptTable').DataTable().clear().destroy();
                        }
                        resetThead(sc);

                        table = $('#receiptTable').DataTable({
                            processing: true,
                            serverSide: true,
                            deferRender: true,
                            pageLength: 10,
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
                            order: orderFor(sc),
                            ajax: {
                                url: "{{ route('receiptlist.json') }}",
                                type: "GET",
                                data: function(d) {
                                    d.scope = sc;
                                }
                            },
                            columns: columnsFor(sc),
                            searchDelay: 400,
                            stateSave: false,
                            responsive: true
                        });
                    }

                    function renderPlusCreate(row) {
                        const url = `{{ route('receipt.create') }}` + `?ponbr=${encodeURIComponent(row.ponbr_eid ?? '')}`;
                        return `<a href="${url}" class="inline-flex justify-center items-center px-4 py-2 text-sm leading-tight font-medium text-white rounded text-center transition-colors duration-200 bg-blue-500 hover:bg-blue-700">
                    <i class="fas fa-plus"></i></a>`;
                    }

                    function renderPlusReturn(row) {
                        const url = `{{ route('receipt.return.create') }}` +
                            `?rcp=${encodeURIComponent(row.receiptnbr_eid ?? '')}`;
                        return `<a href="${url}" class="inline-flex items-center justify-center rounded bg-indigo-600 px-2 py-1 text-white text-sm font-bold hover:bg-indigo-700">+</a>`;
                    }


                    function renderPoLink(row) {
                        const text = row.ponbr ?? '';
                        // gunakan hash id jika tersedia
                        if (row.ponbr_eid) {
                            const url = `/showpo/${encodeURIComponent(row.ponbr_eid)}`;
                            return `<a href="${url}" class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-base leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-gray-500 hover:bg-gray-700">${text}</a>`;
                        }
                        return text;
                    }

                    function renderSppbLink(row) {
                        const text = row.sppbjktid ?? '';
                        if (row.sppb_route && row.sppb_eid) {
                            const url = `/${row.sppb_route}/${encodeURIComponent(row.sppb_eid)}`;
                            return `<a href="${url}" class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-base leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-gray-500 hover:bg-gray-700">${text}</a>`;
                        }
                        return text;
                    }

                    // function renderReceiptLink(row) {
                    //     const url = `/showreceipt/${encodeURIComponent(row.receiptnbr_eid ?? '')}`;
                    //     const text = row.receiptnbr ?? '';
                    //     return `<a href="${url}" class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-base leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-gray-500 hover:bg-gray-700">${text}</a>`;
                    // }

                    function renderReceiptLink(row) {
                        const label = row.receiptnbr ?? '';
                        const hash = row.receiptnbr_eid || row.eid || row.hash || row.id; // prioritaskan receiptnbr_eid

                        if (!label) return '';
                        if (!hash) {
                            // fallback bila hash tidak ada
                            return `<span class="inline-flex items-center px-3 py-1.5 text-sm font-semibold rounded bg-gray-400 text-white">${label}</span>`;
                        }

                        // baca status & creator
                        const statusRaw = (row.status ?? row.xstatus ?? '').toString().trim().toUpperCase();
                        const creator = (row.created_by ?? row.createdby ?? '').toString();
                        const isRevise = statusRaw === 'D';
                        const isOwner = creator === (currentUser ?? '');

                        // Jika Revise dan pemilik dokumen = user sekarang → arahkan ke EDIT
                        if (isRevise && isOwner) {
                            const url = `/editreceipts/${encodeURIComponent(hash)}`;
                            return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-semibold rounded bg-amber-600 text-white hover:bg-amber-700" title="Edit (Revise)">${label}</a>`;
                        }

                        // default → SHOW
                        const url = `/showreceipt/${encodeURIComponent(hash)}`;
                        return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-semibold rounded bg-gray-600 text-white hover:bg-gray-700">${label}</a>`;
                    }

                    function renderStatusBadge(row) {
                        const label = row.status_label ?? row.status ?? 'Unknown';
                        const cls = row.status_class ?? 'bg-gray-100 text-gray-700 border-gray-200';

                        return `
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold ${cls}">
                            ${label}
                        </span>
                        `;
                    }


                    // init awal
                    updateTitle(scope);
                    highlightActive(scope);
                    rebuild(scope);

                    // ganti scope
                    $('.scope-filter').on('click', function(e) {
                        e.preventDefault();
                        scope = $(this).data('scope') || 'receiptjobs';
                        updateTitle(scope);
                        highlightActive(scope);
                        rebuild(scope);
                    });

                    // Toggle .active class and remember selected scope
                    const receiptScopes = document.querySelectorAll('.scope-filter');
                    const savedReceiptScope = localStorage.getItem('activeReceiptScope');

                    if (savedReceiptScope) {
                        const activeScope = document.querySelector(`.scope-filter[data-scope="${savedReceiptScope}"]`);
                        if (activeScope) activeScope.classList.add('active');
                    }

                    receiptScopes.forEach(btn => {
                        btn.addEventListener('click', e => {
                            e.preventDefault();
                            receiptScopes.forEach(s => s.classList.remove('active'));
                            btn.classList.add('active');
                            localStorage.setItem('activeReceiptScope', btn.dataset.scope);
                        });
                    });
                });
            </script>

        </div>
    </div>
</x-app-layout>
