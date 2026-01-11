<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'changestos' ? 'HR' : '';
    @endphp
    <style>
        /* Active / Selected state */
        .status-filter.active .status-card {
            transform: scale(1.02);
        }

        /* All */
        .status-filter[data-status=""].active .status-card {
            background-color: rgb(254 215 170);
            /* orange-200 */
            border-color: rgb(194 65 12);
            /* orange-700 */
            color: rgb(194 65 12);
        }

        /* On Progress */
        .status-filter[data-status="P"].active .status-card {
            background-color: rgb(191 219 254);
            /* blue-200 */
            border-color: rgb(29 78 216);
            /* blue-700 */
            color: rgb(29 78 216);
        }

        /* Reject */
        .status-filter[data-status="R"].active .status-card {
            background-color: rgb(254 202 202);
            /* red-200 */
            border-color: rgb(185 28 28);
            /* red-700 */
            color: rgb(185 28 28);
        }

        /* Revise */
        .status-filter[data-status="D"].active .status-card {
            background-color: rgb(229 231 235);
            /* gray-200 */
            border-color: rgb(31 41 55);
            /* gray-700 */
            color: rgb(31 41 55);
        }

        /* Completed */
        .status-filter[data-status="C"].active .status-card {
            background-color: rgb(187 247 208);
            /* green-200 */
            border-color: rgb(21 128 61);
            /* green-700 */
            color: rgb(21 128 61);
        }
    </style>
    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid-col-1 grid gap-6 xl:grid-cols-5 xl:grid-rows-1">
            {{-- All Status --}}
            <button>
                <a href="#" class="status-filter group block" data-status="">
                    <div
                        class="status-card flex items-center gap-4 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">
                        <span class="text-xl group-hover:animate-pulse">📄</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">All</p>
                            <p class="text-right text-xl font-extrabold">{{ $all }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- On Progress Status --}}
            <button>
                <a href="#" class="status-filter group block" data-status="P">
                    <div
                        class="status-card flex items-center gap-4 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">
                        <span class="text-xl group-hover:animate-pulse">⏳</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">On Progress</p>
                            <p class="text-right text-xl font-extrabold">{{ $onProgress }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- Reject Status --}}
            <button>
                <a href="#" class="status-filter group block" data-status="R">
                    <div
                        class="status-card flex items-center gap-4 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">
                        <span class="text-xl group-hover:animate-pulse">⛔️</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">Reject</p>
                            <p class="text-right text-xl font-extrabold">{{ $reject }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- Revise / Draft Status --}}
            <button>
                <a href="#" class="status-filter group block" data-status="D">
                    <div
                        class="status-card flex items-center gap-4 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">
                        <span class="text-xl group-hover:animate-pulse">✏️</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">Revise</p>
                            <p class="text-right text-xl font-extrabold">{{ $revise }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- Completed Status --}}
            <button>
                <a href="#" class="status-filter group block" data-status="C">
                    <div
                        class="status-card flex items-center gap-4 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">
                        <span class="text-xl group-hover:animate-pulse">✅</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">Completed</p>
                            <p class="text-right text-xl font-extrabold">{{ $completed }}</p>
                        </div>
                    </div>
                </a>
            </button>
        </div>

        <div class="grid">
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

                /* ChangeSto Table Specific Styles */
                #changestosTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    align-items: center;
                }

                #changestosTable_filter label {
                    margin-right: 2px;
                }

                #changestosTable_filter input {
                    width: 200px;
                }

                #changestosTable_wrapper {
                    width: 100%;
                }

                #changestosTable td {
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                #changestosTable th,
                #changestosTable td {
                    padding: 10px;
                    max-width: 200px;
                }

                #changestosTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }

                #changestosTable_length select {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                }

                #changestosTable_length select option {
                    padding: 5px;
                }

                #changestosTable_info {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                .dataTables_paginate {
                    /* This class is for all DataTables paginations */
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                #changestosTable tbody tr td {
                    padding: 8px 8px;
                    line-height: 2;
                }

                #changestosTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }

                #changestosTable tbody tr:hover {
                    background-color: #8f8f8f11;
                    opacity: 100%;
                    cursor: pointer;
                }

                #changestosTable tbody tr:hover td {
                    /* color: black; */
                }

                #changestosTable th:nth-child(1),
                #changestosTable td:nth-child(1) {
                    width: 120px;
                    text-align: center;
                }

                #changestosTable th:nth-child(4),
                #changestosTable td:nth-child(4) {
                    width: 120px;
                    text-align: center;
                }

                /* --- Custom Styles for RowGroup Collapse/Expand (Applied to changestosTable) --- */
                /* Initially hide rows in collapsed groups */
                #changestosTable tbody tr.collapsed-group-row {
                    display: none;
                }

                /* Style for group rows */
                #changestosTable tr.group-row {
                    background-color: #e6e6e6;
                    /* Light gray background for group headers */
                    font-weight: bold;
                    cursor: pointer;
                    user-select: none;
                    /* Prevent text selection on click */
                    color: #333;
                    /* Darker text for group headers */
                }

                #changestosTable tr.group-row:hover {
                    background-color: #d4d4d4;
                    /* Slightly darker on hover */
                }

                /* Icon styling */
                #changestosTable tr.group-row .fas {
                    margin-right: 8px;
                    width: 16px;
                    /* Ensure consistent icon width */
                    text-align: center;
                }

                /* Adjust padding for group rows to look consistent with other cells */
                #changestosTable tr.group-row td {
                    padding: 10px !important;
                    border-bottom: 1px solid #ddd;
                    /* Separator for groups */
                }

                /* Remove border from the first td in group row to match the colspan */
                #changestosTable tr.group-row td:first-child {
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
            <div class="mt-6 flex flex-col gap-6 rounded-xl bg-white p-6 dark:bg-gray-800">
                <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                    {{-- Changed text-3xl to text-xl --}}
                    <h1 class="text-xl font-extrabold text-gray-700 dark:text-white">Request Additional</h1>
                    <a href="{{ url('/createchangestos') }}"
                        class="inline-flex items-center rounded-xl bg-indigo-600 px-6 py-2 text-base font-semibold text-white transition-colors duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <i class="fas fa-plus pr-2"></i>Create
                    </a>
                </div>

                <div class="rounded-base relative overflow-x-auto"> {{-- Padding applied here instead of outer container --}}
                    <table id="changestosTable" class="text-body w-full text-left text-sm rtl:text-right">
                        <thead
                            class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                            <tr>
                                <th scope="col" class="w-32 px-6 py-3 font-medium">
                                    DocID
                                </th>
                                <th scope="col" class="w-32 px-6 py-3 font-medium">
                                    Date
                                </th>
                                <th scope="col" class="w-32 px-6 py-3 font-medium">
                                    Company
                                </th>
                                <th scope="col" class="w-32 px-6 py-3 font-medium">
                                    Department
                                </th>
                                <th scope="col" class="w-32 px-6 py-3 font-medium">
                                    Sub Department
                                </th>
                                <th scope="col" class="w-32 px-6 py-3 font-medium">
                                    Sub Gradename
                                </th>
                                <th scope="col" class="w-32 px-6 py-3 font-medium">
                                    Note
                                </th>
                                <th scope="col" class="w-32 px-6 py-3 font-medium">
                                    Status
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


            <script>
                $(document).ready(function() {
                    // Hanya inisialisasi tabel changestosTable
                    let changestosTable = $('#changestosTable').DataTable({
                        ajax: "{{ route('changestos.json') }}?status=P",
                        processing: true,
                        serverSide: false,
                        responsive: true,
                        order: [
                            [2, 'asc'], // Urutkan berdasarkan 'Company' (index 2) untuk pengelompokan
                            [0, 'desc'] // Kemudian berdasarkan DocID (index 0)
                        ],
                        rowGroup: {
                            dataSrc: 'cpnyid', // Kelompokkan berdasarkan kolom 'cpnyid'
                            startRender: function(rows, group) {
                                // Cek apakah semua baris dalam grup saat ini tersembunyi (collapsed)
                                let isCollapsed = rows.nodes().to$().filter('.collapsed-group-row').length ===
                                    rows.count();
                                let icon = isCollapsed ? '<i class="fas fa-plus-circle"></i>' :
                                    '<i class="fas fa-minus-circle"></i>';

                                // Mengembalikan baris grup dengan ikon dan jumlah catatan
                                return $('<tr/>')
                                    .append('<td colspan="' + rows.columns().count() + '">' + icon + ' ' +
                                        group + ' (' + rows.count() + ' records)</td>')
                                    .attr('data-group', group)
                                    .addClass('group-row');
                            }
                        },
                        columns: [{
                                data: null,
                                defaultContent: ''
                            }, {
                                data: 'eid',
                                render: function(data, type, row) {
                                    let url = `/showchangestos/${row.eid}`;
                                    let buttonClass =
                                        'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-base leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-gray-500 hover:bg-gray-700';
                                    let buttonText = row
                                        .changerequest_id; // Menggunakan row.changerequest_id untuk teks tombol

                                    // Cek apakah user yang login sama dengan created_user dan status = D (Revise/Draft)
                                    if (row.status === 'D' && row.created_user === currentUser) {
                                        url = `/editchangestos/${row.eid}`;
                                        buttonClass =
                                            'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-base leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-yellow-500 hover:bg-yellow-700';
                                    }

                                    return `<a href="${url}" class="px-3 py-1 ${buttonClass} text-white rounded">${buttonText}</a>`;
                                }
                            },
                            {
                                data: 'changerequest_date',
                                className: 'no-pointer'
                            },
                            {
                                data: 'cpnyid',
                                className: 'no-pointer'
                            },
                            {
                                data: 'departementid',
                                className: 'no-pointer'
                            },
                            {
                                data: 'departement_name',
                                className: 'no-pointer'
                            },
                            {
                                data: 'subgrade_name',
                                className: 'no-pointer'
                            },
                            {
                                data: 'changerequest_note',
                                className: 'no-pointer'
                            },
                            {
                                data: 'status',
                                className: 'no-pointer',
                                render: function(data) {
                                    let statusText = "";
                                    let badgeClass = "";

                                    if (data === 'D') {
                                        statusText = "Revise";
                                        badgeClass =
                                            "w-32 bg-gray-300/30 dark:bg-gray-300 text-gray-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                                    } else if (data === 'P') {
                                        statusText = "On Progress";
                                        badgeClass =
                                            "w-32 bg-blue-300/30 dark:bg-blue-300 text-blue-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                                    } else if (data === 'C') {
                                        statusText = "Completed";
                                        badgeClass =
                                            "w-32 bg-green-300/30 dark:bg-green-300 text-green-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                                    } else if (data === 'X') {
                                        statusText = "Cancel";
                                        badgeClass =
                                            "w-32 bg-red-300/30 dark:bg-red-300 text-red-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                                    } else if (data === 'R') {
                                        statusText = "Rejected";
                                        badgeClass =
                                            "w-32 bg-red-300/30 dark:bg-red-300 text-red-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                                    } else {
                                        badgeClass =
                                            "  w-full max-w-32 bg-gray-300/30  bg-gray-300  text-gray-600 flex justify-items-center  focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                                    }
                                    return `<span class="${badgeClass}">${statusText}</span>`;
                                }
                            }
                        ]
                    });

                    // Event listener untuk klik pada baris grup (collapse/expand) untuk changestosTable
                    $('#changestosTable tbody').on('click', 'tr.group-row', function() {
                        let groupName = $(this).data('group');
                        let iconElement = $(this).find('i');

                        changestosTable.rows().every(function() {
                            if (this.data().cpnyid ===
                                groupName
                            ) { // Sesuaikan dengan nama properti data yang digunakan untuk grouping
                                $(this.node()).toggleClass('collapsed-group-row');
                            }
                        });

                        // Mengganti ikon plus/minus
                        if (iconElement.hasClass('fa-plus-circle')) {
                            iconElement.removeClass('fa-plus-circle').addClass('fa-minus-circle');
                        } else {
                            iconElement.removeClass('fa-minus-circle').addClass('fa-plus-circle');
                        }
                    });


                    // Filter status akan memfilter data di changestosTable
                    $('.status-filter').on('click', function(e) {
                        e.preventDefault();
                        let selectedStatus = $(this).data('status');
                        let newUrl = "{{ route('changestos.json') }}";
                        newUrl += "?status=" + encodeURIComponent(selectedStatus ?? '');
                        console.log("Loading changestosTable with URL:", newUrl);
                        changestosTable.ajax.url(newUrl).load();
                    });
                });
                // Toggle .active class and remember selected status
                const statusFilters = document.querySelectorAll('.status-filter');
                const savedStatus = localStorage.getItem('activeStatus');

                if (savedStatus) {
                    const activeStatus = document.querySelector(`.status-filter[data-status="${savedStatus}"]`);
                    if (activeStatus) activeStatus.classList.add('active');
                }

                statusFilters.forEach(btn => {
                    btn.addEventListener('click', e => {
                        e.preventDefault();
                        statusFilters.forEach(s => s.classList.remove('active'));
                        btn.classList.add('active');
                        localStorage.setItem('activeStatus', btn.dataset.status);
                    });
                });
            </script>
        </div>
    </div>
</x-app-layout>
