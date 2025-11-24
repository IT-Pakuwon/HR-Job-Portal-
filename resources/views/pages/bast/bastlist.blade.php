<x-app-layout>
    <style>
        /* Active / Selected state */
        .scope-filter.active .scope-card {
            transform: scale(1.02);
        }

        /* Receipt Jobs */
        .scope-filter[data-scope="bastjobs"].active .scope-card {
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
        #bastTable_filter {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }

        #bastTable_filter label {
            margin-right: 2px;
        }

        #bastTable_filter input {
            width: auto;
            padding: 0.25rem 0.5rem;
            min-width: 80px;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
        }

        /* === Wrapper Width === */
        #bastTable_wrapper {
            width: 100%;
        }

        /* === Cell Formatting === */
        #bastTable td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding: 10px;
            max-width: 200px;
        }

        #bastTable th {
            padding: 10px;
            max-width: 200px;
        }

        /* === Length Section === */
        #bastTable_length {
            width: auto;
            display: flex;
            justify-content: flex-start;
        }

        #bastTable_length select {
            width: auto;
            padding: 0.25rem 0.5rem;
            min-width: 80px;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
        }

        /* === Info + Pagination === */
        #bastTable_info {
            margin: 10px 0;
        }

        .dataTables_paginate {
            margin: 10px 0;
        }

        /* === Hover Effects === */
        #bastTable tbody tr {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        #bastTable tbody tr:hover {
            background-color: #8f8f8f11;
            cursor: pointer;
        }

        #bastTable tbody tr td {
            padding: 8px;
            line-height: 2;
        }

        /* === Column Width Alignment === */
        #bastTable th:nth-child(1),
        #bastTable td:nth-child(1),
        #bastTable th:nth-child(4),
        #bastTable td:nth-child(4) {
            width: 120px;
            text-align: center;
        }

        /* === Group Row & Collapse === */
        #bastTable tbody tr.collapsed-group-row {
            display: none;
        }

        #bastTable tr.group-row {
            background-color: #e6e6e6;
            font-weight: bold;
            cursor: pointer;
            user-select: none;
            color: #333;
        }

        #bastTable tr.group-row:hover {
            background-color: #d4d4d4;
        }

        #bastTable tr.group-row .fas {
            margin-right: 8px;
            width: 16px;
            text-align: center;
        }

        #bastTable tr.group-row td {
            padding: 10px !important;
            border-bottom: 1px solid #ddd;
        }

        #bastTable tr.group-row td:first-child {
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

    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid auto-rows-fr grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6">

            {{-- Bast Jobs --}}
            <button type="button" class="scope-filter group block h-full" data-scope="bastjobs">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-xl">📦</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-base font-medium leading-tight">Bast Jobs</p>
                    </div>

                    <p class="shrink-0 text-xl font-bold">{{ $bastjobs }}</p>
                </div>
            </button>

            {{-- On Progress --}}
            <button type="button" class="scope-filter group block h-full" data-scope="onprogress">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-xl">⏳</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-base font-medium leading-tight">On Progress</p>
                    </div>

                    <p class="shrink-0 text-xl font-bold">{{ $onProgress }}</p>
                </div>
            </button>

            {{-- Rejected --}}
            <button type="button" class="scope-filter group block h-full" data-scope="rejected">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-xl">❌</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-base font-medium leading-tight">Rejected</p>
                    </div>

                    <p class="shrink-0 text-xl font-bold">{{ $rejected }}</p>
                </div>
            </button>

            {{-- Revise --}}
            <button type="button" class="scope-filter group block h-full" data-scope="revise">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-yellow-700 bg-yellow-200/20 p-3 text-yellow-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-yellow-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-xl">🛠️</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-base font-medium leading-tight">Revise</p>
                    </div>

                    <p class="shrink-0 text-xl font-bold">{{ $revise }}</p>
                </div>
            </button>

            {{-- Completed --}}
            <button type="button" class="scope-filter group block h-full" data-scope="completed">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-xl">✅</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-base font-medium leading-tight">Completed</p>
                    </div>

                    <p class="shrink-0 text-xl font-bold">{{ $completed }}</p>
                </div>
            </button>

            {{-- All --}}
            <button type="button" class="scope-filter group block h-full" data-scope="all">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-xl">🧾</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-base font-medium leading-tight">All</p>
                    </div>

                    <p class="shrink-0 text-xl font-bold">{{ $all }}</p>
                </div>
            </button>

        </div>


        <div class="grid">
            <div class="mt-6 rounded-2xl bg-white dark:bg-gray-800">
                <div
                    class="flex flex-col items-start justify-between gap-4 border-b border-gray-200 p-4 sm:flex-row sm:items-center dark:border-gray-700">
                    <h1 class="text-xl font-extrabold text-gray-700 dark:text-white">Bast</h1>
                </div>

                <div class="overflow-x-auto p-6">
                    <table id="bastTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
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
                    let scope = 'bastjobs';
                    const $title = $('h1.text-xl.font-extrabold');
                    const $thead = $('#bastTable thead');
                    let table;

                    const titleMap = {
                        bastjobs: 'Bast - Jobs',
                        onprogress: 'Bast - On Progress',
                        completed: 'Bast - Completed',
                        rejected: 'Bast - Rejected',
                        revise: 'Bast - Revise',
                        all: 'Bast - All',
                    };

                    function headerFor(sc) {
                        if (sc === 'bastjobs') {
                            // Disesuaikan ke TrPOterm
                            return `
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">PO Nbr</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Company</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Vendor</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Terms</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Progress %</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Payment %</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Created By</th>
                            `;
                        }
                        // TrBast scopes
                        return `
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Bast ID</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Bast Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">PO Nbr</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">SPPB/J/K/T</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Company</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Created By</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                        `;
                    }

                    function columnsFor(sc) {
                        if (sc === 'bastjobs') {
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
                                    data: 'cpny_id',
                                    className: 'text-left'
                                },
                                {
                                    data: 'vendorname'
                                },
                                {
                                    data: 'terms_name'
                                },
                                {
                                    data: 'progress_pct',
                                    className: 'text-left'
                                },
                                {
                                    data: 'payment_pct',
                                    className: 'text-left'
                                },
                                {
                                    data: 'created_by'
                                },
                                
                            ];
                        }
                        // TrBast scopes
                        // return [
                        //     { data: 'bastid',  render: (_v,_t,row)=>renderBastLink(row) },
                        //     { data: 'bastdate', render: (_v,_t,row)=>row.bastdate_fmt ?? '', className:'text-center' },
                        //     { data: 'ponbr', render: (_v,_t,row)=>renderPoLink(row) },
                        //     { data: 'sppbjktid', render: (_v,_t,row)=>renderSppbLink(row) },
                        //     { data: 'cpny_id', className:'text-center' },
                        //     { data: 'created_by' },
                        // ];
                        return [{
                                data: 'bastid',
                                render: (_v, _t, row) => renderBastLink(row)
                            },
                            {
                                data: 'bastdate',
                                render: (_v, _t, row) => row.bastdate_fmt ?? '',
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
                                className: 'text-left',
                                render: function(data) {
                                    const map = {
                                        'D': {
                                            t: 'Revise',
                                            c: 'bg-gray-300/30 text-gray-600'
                                        },
                                        'P': {
                                            t: 'On Progress',
                                            c: 'bg-blue-300/30 text-blue-600'
                                        },
                                        'C': {
                                            t: 'Completed',
                                            c: 'bg-green-300/30 text-green-600'
                                        },
                                        'X': {
                                            t: 'Cancel',
                                            c: 'bg-red-300/30 text-red-600'
                                        },
                                        'R': {
                                            t: 'Rejected',
                                            c: 'bg-red-300/30 text-red-600'
                                        },
                                    };
                                    const it = map[data] || {
                                        t: data || '-',
                                        c: 'bg-gray-300/30 text-gray-600'
                                    };
                                    return `<span class="w-32 inline-block ${it.c} font-semibold px-3 py-1.5 text-base text-center rounded">${it.t}</span>`;
                                }
                            },
                        ];
                    }

                    function orderFor(sc) {
                        if (sc === 'bastjobs') return [
                            [1, 'desc']
                        ]; // sort by PONBR
                        return [
                            [1, 'desc'],
                            [0, 'desc']
                        ];
                    }

                    function updateTitle(sc) {
                        $title.text(titleMap[sc] ?? 'Bast');
                    }

                    function resetThead(sc) {
                        const $table = $('#bastTable');

                        // hapus thead lama (yang mungkin sisa clone DataTables)
                        $table.find('thead').remove();

                        // buat ulang thead + tr
                        const theadHtml = `
                            <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr id="thead-row">${headerFor(sc)}</tr>
                            </thead>`;
                        $table.prepend(theadHtml);

                        // pastikan tbody ada
                        if ($table.find('tbody').length === 0) {
                            $table.append(
                                '<tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>'
                            );
                        }
                    }

                    function rebuild(sc) {
                        if ($.fn.DataTable.isDataTable('#bastTable')) {
                            $('#bastTable').DataTable().clear().destroy();
                        }
                        resetThead(sc);

                        table = $('#bastTable').DataTable({
                            processing: true,
                            serverSide: true,
                            deferRender: true,
                            pageLength: 25,
                            lengthMenu: [10, 25, 50, 100, 250],
                            order: orderFor(sc),
                            ajax: {
                                url: "{{ route('bastlist.json') }}",
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
                        // create BAST → kirim hash id PO (hasil mapping di controller)
                        const url = `{{ route('bast.create') }}` + `?term=${encodeURIComponent(row.term_eid ?? '')}`;
                        return `<a href="${url}" class="inline-flex justify-center items-center px-4 py-2 text-sm leading-tight font-medium text-white rounded text-center transition-colors duration-200 bg-blue-500 hover:bg-blue-700">
                            <i class="fas fa-plus"></i></a>`;
                    }

                    function renderPoLink(row) {
                        const text = row.ponbr ?? '';
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

                    function renderBastLink(row) {
                        const label = row.bastid ?? '';
                        const hash = row.bastid_eid || row.eid || row.hash || row.id;

                        if (!label) return '';
                        if (!hash) {
                            return `<span class="inline-flex items-center px-3 py-1.5 text-sm font-semibold rounded bg-gray-400 text-white">${label}</span>`;
                        }

                        const statusRaw = (row.status ?? '').toString().trim().toUpperCase();
                        const creator = (row.created_by ?? '').toString();
                        const isRevise = statusRaw === 'D';
                        const isOwner = creator === (currentUser ?? '');

                        if (isRevise && isOwner) {
                            const url = `/editbasts/${encodeURIComponent(hash)}`;
                            return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-semibold rounded bg-amber-600 text-white hover:bg-amber-700" title="Edit (Revise)">${label}</a>`;
                        }

                        const url = `/showbast/${encodeURIComponent(hash)}`;
                        return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-semibold rounded bg-gray-600 text-white hover:bg-gray-700">${label}</a>`;
                    }

                    // init
                    updateTitle(scope);
                    rebuild(scope);

                    // ganti scope
                    $('.scope-filter').on('click', function(e) {
                        e.preventDefault();
                        scope = $(this).data('scope') || 'bastjobs';
                        updateTitle(scope);
                        rebuild(scope);

                        // active state + save
                        $('.scope-filter').removeClass('active');
                        $(this).addClass('active');
                        localStorage.setItem('activeBastScope', scope);
                    });

                    // restore scope terakhir
                    const savedBastScope = localStorage.getItem('activeBastScope');
                    if (savedBastScope) {
                        scope = savedBastScope;
                        updateTitle(scope);
                        rebuild(scope);
                        $('.scope-filter').removeClass('active');
                        $(`.scope-filter[data-scope="${scope}"]`).addClass('active');
                    } else {
                        $(`.scope-filter[data-scope="bastjobs"]`).addClass('active');
                    }
                });
            </script>
        </div>
    </div>
</x-app-layout>
