<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'jobpostings' ? 'HR' : '';
    @endphp
    <style>
        /* === Shared Style for All Status Filters === */
        .status-filter.active .status-card {
            transform: scale(1.02);
        }

        /* === Status-Specific Active Colors === */
        .status-filter[data-status=""].active .status-card {
            background-color: rgb(254 215 170);
            /* orange-200 */
            border-color: rgb(194 65 12);
            /* orange-700 */
            color: rgb(194 65 12);
        }

        .status-filter[data-status="is_read_N"].active .status-card,
        .status-filter[data-status="P"].active .status-card {
            background-color: rgb(191 219 254);
            /* blue-200 */
            border-color: rgb(29 78 216);
            /* blue-700 */
            color: rgb(29 78 216);
        }

        .status-filter[data-status="is_read_Y"].active .status-card,
        .status-filter[data-status="D"].active .status-card {
            background-color: rgb(229 231 235);
            /* gray-200 */
            border-color: rgb(31 41 55);
            /* gray-700 */
            color: rgb(31 41 55);
        }

        .status-filter[data-status="R"].active .status-card {
            background-color: rgb(254 202 202);
            /* red-200 */
            border-color: rgb(185 28 28);
            /* red-700 */
            color: rgb(185 28 28);
        }

        .status-filter[data-status="C"].active .status-card {
            background-color: rgb(187 247 208);
            /* green-200 */
            border-color: rgb(21 128 61);
            /* green-700 */
            color: rgb(21 128 61);
        }
    </style>
    <div class="max-w-9xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
        <div class="mt-4 grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">

            {{-- All Status --}}
            <a href="#" class="status-filter group block h-full" data-status="">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">📄</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-base font-medium">All</p>
                    </div>

                    <p class="shrink-0 text-xl font-bold">{{ $all }}</p>
                </div>
            </a>

            {{-- Unchecked --}}
            <a href="#" class="status-filter group block h-full" data-status="is_read_N">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">⏳</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-base font-medium">Unchecked</p>
                    </div>

                    <p class="shrink-0 text-xl font-bold">{{ $unchecked }}</p>
                </div>
            </a>

            {{-- Checked --}}
            <a href="#" class="status-filter group block h-full" data-status="is_read_Y">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">✏️</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-base font-medium">Checked</p>
                    </div>

                    <p class="shrink-0 text-xl font-bold">{{ $checked }}</p>
                </div>
            </a>

            {{-- Reject --}}
            <a href="#" class="status-filter group block h-full" data-status="R">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">⛔️</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-base font-medium">Reject</p>
                    </div>

                    <p class="shrink-0 text-xl font-bold">{{ $reject }}</p>
                </div>
            </a>

            {{-- Approved --}}
            <a href="#" class="status-filter group block h-full" data-status="C">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">✅</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-base font-medium">Approved</p>
                    </div>

                    <p class="shrink-0 text-xl font-bold">{{ $approved }}</p>
                </div>
            </a>

        </div>

        <div class="grid">
            <style>
                /* Kolom kecil */
                table#applicantsTable td.small-col,
                table#applicantsTable th.small-col {
                    width: 60px !important;
                    max-width: 60px !important;
                    text-align: center;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
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
                    min-width: 0 !important;
                    table-layout: fixed;

                }

                table.dataTable th,
                table.dataTable td {
                    white-space: normal;
                    word-wrap: break-word;
                }

                .dataTables_wrapper {
                    width: 100%;
                }

                @media (max-width: 600px) {
                    .dataTables_wrapper {
                        padding: 0 10px;
                    }
                }


                /* Applicant Table */
                #applicantsTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    /* Aligns items to the left */
                    align-items: center;
                    /* Vertically aligns items */
                }

                #applicantsTable_filter label {
                    margin-right: 2px;
                }

                #applicantsTable_filter input {
                    width: 200px;
                    /* Adjust the width of the input box */
                }


                #applicantsTable_wrapper {
                    width: 100%;
                }

                /* Prevent text from wrapping */
                #applicantsTable td {
                    white-space: nowrap;
                    /* Prevent text from wrapping */
                    overflow: hidden;
                    /* Hide overflow content */
                    text-overflow: ellipsis;
                    /* Display ellipsis ("...") for overflowing content */
                }

                /* Optional: Adjust the width for table cells */
                #applicantsTable th,
                #applicantsTable td {
                    padding: 10px;
                    /* Adjust padding for better appearance */
                    max-width: 200px;
                    /* You can set a maximum width to control overflow */
                }


                #applicantsTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }

                #applicantsTable_length select {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                }

                #applicantsTable_length select option {
                    padding: 5px;
                    /* Mengatur jarak antar opsi */
                }

                #applicantsTableinfo {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                .dataTables_paginate {
                    margin-top: 10px;
                    margin-bottom: 10px;

                }

                #applicantsTable tbody tr td {
                    padding: 8px 8px;
                    /* Adjust padding for uniform height */
                    line-height: 2;
                    /* Optional, for better text alignment */
                }

                #applicantsTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }

                #applicantsTable tbody tr:hover {
                    background-color: #8f8f8f11;
                    opacity: 100%;
                    cursor: pointer;
                }

                #applicantsTable tbody tr:hover td {
                    /* color: black; */
                }
            </style>
            <style>
                /* ✅ Custom Switch Button */
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

                /* ✅ Memperkecil Lebar Kolom Actions */
                #jobpostingsTable th:nth-child(1),
                #jobpostingsTable td:nth-child(1) {
                    width: 120px;
                    text-align: center;
                }

                #jobpostingsTable th:nth-child(4),
                #jobpostingsTable td:nth-child(4) {
                    width: 120px;
                    text-align: center;
                }

                #w-full {
                    width: 100% !important;
                }

                .edu-col.hidden {
                    display: none;
                }

                /* Header row filter */
                #applicantsTable thead tr.filters th {
                    padding: 6px 8px;
                }

                #applicantsTable thead .col-filter {
                    width: 100%;
                    box-sizing: border-box;
                }

                #applicantsTable thead .input-filter {
                    padding: 6px 8px;
                    border: 1px solid #d1d5db;
                    border-radius: 6px;
                    font-size: 12px;
                }

                #applicantsTable thead .select-filter {
                    padding: 6px 8px;
                    border: 1px solid #d1d5db;
                    border-radius: 6px;
                    font-size: 12px;
                    background: white;
                }

                .dark #applicantsTable thead .input-filter,
                .dark #applicantsTable thead .select-filter {
                    background: #374151;
                    color: #e5e7eb;
                    border-color: #4b5563;
                }
            </style>
            <div class="mt-6 rounded-2xl bg-white dark:bg-gray-800">
                <div
                    class="flex flex-col items-start justify-between gap-4 border-b border-gray-200 p-4 sm:flex-row sm:items-center dark:border-gray-700">
                    {{-- Changed text-3xl to text-xl --}}
                    <h1 class="text-xl font-extrabold text-gray-700 dark:text-white">Applicant List</h1>
                    {{-- <a"
                        class="inline-flex items-center rounded-xl bg-indigo-600 px-6 py-2 text-base font-semibold text-white transition-colors duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        List Job Posting
                        </a> --}}
                </div>
                <div class="overflow-x-auto p-6"> {{-- Padding applied here instead of outer container --}}

                    <div class="mb-4 flex items-center gap-3">
                        <select id="filterJobTL"></select>
                        <button id="btnResetFilters" class="rounded-md border px-3 py-2 text-sm hover:bg-gray-50">
                            Reset
                        </button>
                    </div>

                    <table id="applicantsTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col"
                                    class="w-32 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    DocID
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Date
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Name
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Education
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Religion
                                </th>
                                <th scope="col"
                                    class="w-32 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Height
                                </th>
                                <th scope="col"
                                    class="w-32 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Weight
                                </th>
                                <th scope="col"
                                    class="w-32 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Last Working
                                </th>
                                <th scope="col"
                                    class="w-32 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Score
                                </th>
                                <th scope="col"
                                    class="w-32 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Step
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            {{-- Table rows will be populated here by JavaScript/DataTables --}}
                        </tbody>
                    </table>
                </div>
            </div>


            <script>
                var currentUser = "{{ auth()->user()->username }}";
            </script>


            {{-- <script>
                $(document).ready(function() {
                    let currentStatus = '';
                    let applicantTable = $('#applicantsTable').DataTable({
                        responsive: true,
                        processing: true,
                        serverSide: true,
                        searching: true,
                        paging: true,
                        info: true,
                        lengthChange: true,
                        pageLength: 10,
                        ajax: {
                            url: "{{ route('jobapplicant.json') }}",
                            type: 'GET',
                            data: function(d) {
                                d.status = currentStatus;
                            }
                        },
                        order: [
                            [8, 'desc']
                        ],
                        columns: [{
                                data: 'docid',
                                render: function(data, type, row) {
                                    return `<a href="/showcareers/${row.id}" target="_blank" class="px-4 py-2.5 bg-indigo-500 text-white rounded hover:bg-indigo-700">${data}</a>`;
                                }
                            },
                            {
                                data: 'apply_date'
                            },
                            {
                                data: 'fullname'
                            },
                            {
                                data: 'education_name'
                            },
                            {
                                data: 'religion'
                            },
                            {
                                data: 'height',
                                className: 'small-col'
                            },
                            {
                                data: 'weight',
                                className: 'small-col'
                            },
                            {
                                data: 'company_name'
                            },
                            {
                                data: 'match_score_percentage',
                                className: 'small-col'
                            },
                            {
                                data: 'prev_apply_step',
                                render: function(data) {
                                    const labelMap = {
                                        'JOAPHC': 'Job Apply HC',
                                        'JOAPUS': 'Job Apply User',
                                        'WIHC': 'Create Schedule Interview HC',
                                        'IHC': 'Interview HC',
                                        'WIU': 'Create Schedule Interview User',
                                        'IU': 'Interview User',
                                        'WPT': 'Waiting Psycho Test',
                                        'PT': 'Psycho Test',
                                        'OFF': 'Offering',
                                        'JOIN': 'Join'
                                    };
                                    return `<span class=\"w-32 bg-blue-300/30 text-blue-600 text-base font-semibold px-4 py-2 text-center rounded\">${labelMap[data] || data}</span>`;
                                }
                            }
                        ],
                        rowCallback: function(row, data, index) {
                            if (data.is_read === 'N') {
                                $(row).css('color', 'blue');
                            } else {
                                $(row).css('color', 'black');
                            }
                        }
                    });
                    $('#applicantsTable thead th').eq(5).addClass('small-col');
                    $('#applicantsTable thead th').eq(6).addClass('small-col');
                    $('#applicantsTable thead th').eq(8).addClass('small-col');

                    // Event handler status-filter
                    $('.status-filter').on('click', function(e) {
                        e.preventDefault();
                        $('.status-filter').removeClass('active');
                        $(this).addClass('active');
                        currentStatus = $(this).data('status');
                        applicantTable.ajax.reload();
                    });
                });
            </script> --}}


            <script>
                $(document).ready(function() {
                    let currentStatus = '';

                    // Definisi kolom (data + name HARUS diisi untuk server-side)
                    const colDefs = [{
                            data: 'docid',
                            name: 'docid',
                            type: 'text',
                            title: 'DocID'
                        },
                        {
                            data: 'apply_date',
                            name: 'apply_date',
                            type: 'text',
                            title: 'Date'
                        },
                        {
                            data: 'fullname',
                            name: 'fullname',
                            type: 'text',
                            title: 'Name'
                        },
                        {
                            data: 'education_name',
                            name: 'education_name',
                            type: 'text',
                            title: 'Education'
                        },
                        {
                            data: 'religion',
                            name: 'religion',
                            type: 'text',
                            title: 'Religion'
                        },
                        {
                            data: 'height',
                            name: 'height',
                            type: 'text',
                            title: 'Height'
                        },
                        {
                            data: 'weight',
                            name: 'weight',
                            type: 'text',
                            title: 'Weight'
                        },
                        {
                            data: 'company_name',
                            name: 'company_name',
                            type: 'text',
                            title: 'Last Working'
                        },
                        {
                            data: 'match_score_percentage',
                            name: 'match_score_percentage',
                            type: 'text',
                            title: 'Score'
                        },
                        {
                            data: 'prev_apply_step',
                            name: 'prev_apply_step',
                            type: 'select',
                            title: 'Step'
                        },
                    ];

                    const stepLabelMap = {
                        'JOAPHC': 'Job Apply HC',
                        'JOAPUS': 'Job Apply User',
                        'WIHC': 'Create Schedule Interview HC',
                        'IHC': 'Interview HC',
                        'WIU': 'Create Schedule Interview User',
                        'IU': 'Interview User',
                        'WPT': 'Waiting Psycho Test',
                        'PT': 'Psycho Test',
                        'OFF': 'Offering',
                        'JOIN': 'Join'
                    };

                    // ===== Tambah baris filter kedua di THEAD, berbasis nama kolom =====
                    const $thead = $('#applicantsTable thead');
                    const $filterRow = $('<tr class="filters"></tr>');
                    colDefs.forEach(def => {
                        let ctl = '';
                        if (def.type === 'select' && def.name === 'prev_apply_step') {
                            ctl = `
                        <select class="col-filter select-filter" data-colname="${def.name}">
                        <option value="">All</option>
                        ${Object.entries(stepLabelMap).map(([k,v]) => `<option value="${k}">${v}</option>`).join('')}
                        </select>`;
                        } else {
                            ctl =
                                `<input type="text" class="col-filter input-filter" data-colname="${def.name}" placeholder="Search ${def.title}">`;
                        }
                        $filterRow.append($('<th>').html(ctl));
                    });
                    $thead.append($filterRow);

                    // ===== Init DataTable =====
                    const applicantTable = $('#applicantsTable').DataTable({
                        responsive: true,
                        processing: true,
                        serverSide: true,
                        searching: true, // global search tetap bisa
                        paging: true,
                        info: true,
                        lengthChange: true,
                        pageLength: 10,
                        orderCellsTop: true, // penting utk 2 baris thead
                        ajax: {
                            url: "{{ route('jobapplicant.json') }}",
                            type: 'GET',
                            data: function(d) {
                                d.status = currentStatus;
                                d.job_tl_exact = $('#filterJobTL').val() || '';
                            }
                        },
                        order: [
                            [8, 'desc']
                        ],
                        columns: [{
                                data: 'docid',
                                name: 'docid',
                                render: function(data, type, row) {
                                    return `<a href="/showcareers/${row.eid}" target="_blank" class= 'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-base leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-gray-500 hover:bg-gray-700'>${data}</a>`;
                                }
                            },
                            {
                                data: 'apply_date',
                                name: 'apply_date'
                            },
                            {
                                data: 'fullname',
                                name: 'fullname'
                            },
                            {
                                data: 'education_name',
                                name: 'education_name'
                            },
                            {
                                data: 'religion',
                                name: 'religion'
                            },
                            {
                                data: 'height',
                                name: 'height',
                                className: 'small-col'
                            },
                            {
                                data: 'weight',
                                name: 'weight',
                                className: 'small-col'
                            },
                            {
                                data: 'company_name',
                                name: 'company_name'
                            },
                            {
                                data: 'match_score_percentage',
                                name: 'match_score_percentage',
                                className: 'small-col'
                            },
                            {
                                data: 'prev_apply_step',
                                name: 'prev_apply_step',
                                render: function(data) {
                                    const label = stepLabelMap[data] || data;
                                    return `<span class="inline-flex justify-center items-center w-[120px] bg-blue-300/30 text-blue-600 text-base font-semibold px-3 py-1.5 text-center rounded whitespace-normal break-words">
    ${label}
</span>`;

                                }
                            }
                        ],
                        rowCallback: function(row, data) {
                            // reset dulu
                            $(row).css('color', '');

                            if (data.status === 'R') {
                                // merah (Tailwind red-600)
                                $(row).css('color', '#dc2626');
                            } else if (data.is_read === 'N') {
                                // biru (Tailwind blue-600)
                                $(row).css('color', '#2563eb');
                            } else {
                                $(row).css('color', 'black');
                            }
                        },
                        initComplete: function() {
                            const api = this.api();

                            // Input text → debounce
                            let debounce;
                            $('#applicantsTable thead').on('input', 'input.col-filter', function() {
                                const colName = $(this).data('colname');
                                const val = this.value;
                                clearTimeout(debounce);
                                debounce = setTimeout(function() {
                                    api.column(colName + ':name').search(val)
                                        .draw(); // <-- pakai selector :name
                                }, 300);
                            });

                            // Select (Step)
                            $('#applicantsTable thead').on('change', 'select.col-filter', function() {
                                const colName = $(this).data('colname');
                                api.column(colName + ':name').search(this.value)
                                    .draw(); // <-- pakai selector :name
                            });
                        }
                    });

                    // kecilkan tiga header kolom numerik
                    $('#applicantsTable thead tr:eq(0) th').eq(5).addClass('small-col');
                    $('#applicantsTable thead tr:eq(0) th').eq(6).addClass('small-col');
                    $('#applicantsTable thead tr:eq(0) th').eq(8).addClass('small-col');

                    $('#filterJobTL').select2({
                        placeholder: 'Filter by Job Title — Job Level',
                        allowClear: true,
                        width: 'resolve',
                        ajax: {
                            url: "{{ route('jobfilters.tl') }}", // endpoint gabungan
                            dataType: 'json',
                            delay: 200,
                            data: params => ({
                                q: params.term || ''
                            }), // pencarian server (opsional)
                            processResults: data => ({
                                // server sudah kirim {id:'Title|||Level', text:'Title — Level'}
                                results: data
                            }),
                            cache: true
                        }
                    });

                    // reload tabel saat filter berubah
                    $('#filterJobTL').on('change', function() {
                        applicantTable.ajax.reload();
                    });

                    // reset
                    $('#btnResetFilters').on('click', function() {
                        $('#filterJobTL').val(null).trigger('change');
                        applicantTable.ajax.reload();
                    });


                    // Filter tombol status (All/Unchecked/Checked/Reject/Approved)
                    $('.status-filter').on('click', function(e) {
                        e.preventDefault();
                        $('.status-filter').removeClass('active');
                        $(this).addClass('active');
                        currentStatus = $(this).data('status') || '';
                        applicantTable.ajax.reload();
                    });
                });
                // Make each row of .status-filter independent
                document.querySelectorAll('.grid-col-1').forEach(grid => {
                    const filters = grid.querySelectorAll('.status-filter');

                    filters.forEach(btn => {
                        btn.addEventListener('click', e => {
                            e.preventDefault();
                            filters.forEach(s => s.classList.remove('active'));
                            btn.classList.add('active');
                        });
                    });
                });
            </script>

            <!-- Select2 CSS -->
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

            <!-- Select2 JS -->
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


        </div>
    </div>
</x-app-layout>
