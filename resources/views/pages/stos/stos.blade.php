<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'stos' ? 'HR' : '';
    @endphp
    <style>
        /* Shared behavior */
        .status-filter.active .status-card {
            transform: scale(1.02);
        }

        /* Color mapping for active states */
        .status-filter[data-status=""].active .status-card {
            background-color: rgb(254 215 170);
            /* orange-200 */
            border-color: rgb(194 65 12);
            /* orange-700 */
            color: rgb(194 65 12);
        }

        .status-filter[data-status="P"].active .status-card {
            background-color: rgb(191 219 254);
            /* blue-200 */
            border-color: rgb(29 78 216);
            /* blue-700 */
            color: rgb(29 78 216);
        }

        .status-filter[data-status="R"].active .status-card {
            background-color: rgb(254 202 202);
            /* red-200 */
            border-color: rgb(185 28 28);
            /* red-700 */
            color: rgb(185 28 28);
        }

        .status-filter[data-status="D"].active .status-card {
            background-color: rgb(229 231 235);
            /* gray-200 */
            border-color: rgb(31 41 55);
            /* gray-700 */
            color: rgb(31 41 55);
        }

        .status-filter[data-status="C"].active .status-card {
            background-color: rgb(187 247 208);
            /* green-200 */
            border-color: rgb(21 128 61);
            /* green-700 */
            color: rgb(21 128 61);
        }
    </style>
    <div class="max-w-9xl mx-auto w-full px-8 py-4 sm:px-6 lg:px-8">
        <div class="grid-col-1 grid gap-6 xl:grid-cols-5 xl:grid-rows-1">
            {{-- All Status --}}
            <a href="#" class="status-filter group block" data-status="">
                <div
                    class="status-card flex items-center gap-4 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">
                    <span class="text-base group-hover:animate-pulse">📄</span>
                    <div class="flex flex-grow items-center justify-between">
                        <p class="text-sm font-medium">All</p>
                        <p class="text-right text-base font-extrabold">{{ $all }}</p>
                    </div>
                </div>
            </a>

            {{-- On Progress --}}
            <a href="#" class="status-filter group block" data-status="P">
                <div
                    class="status-card flex items-center gap-4 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">
                    <span class="text-base group-hover:animate-pulse">⏳</span>
                    <div class="flex flex-grow items-center justify-between">
                        <p class="text-sm font-medium">On Progress</p>
                        <p class="text-right text-base font-extrabold">{{ $onProgress }}</p>
                    </div>
                </div>
            </a>

            {{-- Reject --}}
            <a href="#" class="status-filter group block" data-status="R">
                <div
                    class="status-card flex items-center gap-4 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">
                    <span class="text-base group-hover:animate-pulse">⛔️</span>
                    <div class="flex flex-grow items-center justify-between">
                        <p class="text-sm font-medium">Reject</p>
                        <p class="text-right text-base font-extrabold">{{ $reject }}</p>
                    </div>
                </div>
            </a>

            {{-- Revise / Draft --}}
            <a href="#" class="status-filter group block" data-status="D">
                <div
                    class="status-card flex items-center gap-4 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">
                    <span class="text-base group-hover:animate-pulse">✏️</span>
                    <div class="flex flex-grow items-center justify-between">
                        <p class="text-sm font-medium">Revise / Draft</p>
                        <p class="text-right text-base font-extrabold">{{ $revise }}</p>
                    </div>
                </div>
            </a>

            {{-- Completed --}}
            <a href="#" class="status-filter group block" data-status="C">
                <div
                    class="status-card flex items-center gap-4 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">
                    <span class="text-base group-hover:animate-pulse">✅</span>
                    <div class="flex flex-grow items-center justify-between">
                        <p class="text-sm font-medium">Completed</p>
                        <p class="text-right text-base font-extrabold">{{ $completed }}</p>
                    </div>
                </div>
            </a>
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

                #stosTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    /* Aligns items to the left */
                    align-items: center;
                    /* Vertically aligns items */
                }

                #stosTable_filter label {
                    margin-right: 2px;
                }

                #stosTable_filter input {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }


                #stosTable_wrapper {
                    width: 100%;
                }

                /* Prevent text from wrapping */
                #stosTable td {
                    white-space: nowrap;
                    /* Prevent text from wrapping */
                    overflow: hidden;
                    /* Hide overflow content */
                    text-overflow: ellipsis;
                    /* Display ellipsis ("...") for overflowing content */
                }

                /* Optional: Adjust the width for table cells */
                #stosTable th,
                #stosTable td {
                    padding: 10px;
                    /* Adjust padding for better appearance */
                    max-width: 200px;
                    /* You can set a maximum width to control overflow */
                }


                #stosTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }

                #stosTable_length select {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }

                #stosTable_length select option {
                    padding: 5px;
                    /* Mengatur jarak antar opsi */
                }

                #stosTable_info {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                .dataTables_paginate {
                    margin-top: 10px;
                    margin-bottom: 10px;

                }

                #stosTable tbody tr td {
                    padding: 8px 8px;
                    /* Adjust padding for uniform height */
                    line-height: 2;
                    /* Optional, for better text alignment */
                }

                #stosTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }

                #stosTable tbody tr:hover {
                    background-color: #8f8f8f11;
                    opacity: 100%;
                    cursor: pointer;
                }

                #stosTable tbody tr:hover td {
                    /* color: black; */
                }

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
                #stosTable th:nth-child(1),
                #stosTable td:nth-child(1) {
                    width: 120px;
                    text-align: center;
                }

                #stosTable th:nth-child(4),
                #stosTable td:nth-child(4) {
                    width: 120px;
                    text-align: center;
                }
            </style>
            <div class="mt-6 rounded-xl bg-white dark:bg-gray-800">
                <div
                    class="flex flex-col items-start justify-between gap-4 border-b border-gray-200 p-4 sm:flex-row sm:items-center dark:border-gray-700">
                    {{-- Changed text-lg to text-base --}}
                    <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Organization Structure</h1>
                    <div class="flex gap-2">
                        <a href="{{ url('/createstos') }}"
                            class="inline-flex items-center rounded-xl bg-blue-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <i class="fas fa-plus pr-2"></i>Create
                        </a>
                        <a href="{{ url('/stoall') }}"
                            class="inline-flex items-center rounded-xl bg-blue-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">All
                            Org Chart
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto p-4"> {{-- Padding applied here instead of outer container --}}
                    <table id="stosTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col"
                                    class="w-32 px-6 py-2 text-left text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    DocID
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Date
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Company
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Department
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    User
                                </th>
                                <th scope="col"
                                    class="w-32 px-6 py-2 text-center text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
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
                    // Set default status filter to 'P' (On Progress)
                    $('.status-filter[data-status="P"]').addClass('active');

                    let table = $('#stosTable').DataTable({
                        ajax: {
                            url: "{{ route('stos.json') }}",
                            type: 'GET',
                            data: function(d) {
                                d.status = $('.status-filter.active').data('status') || '';
                            }
                        },
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        order: [
                            [0, 'desc']
                        ],
                        columns: [{
                                data: 'eid',
                                render: function(data, type, row) {
                                    let url = `/showstos/${row.eid}`;
                                    let buttonClass =
                                        'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-gray-500 hover:bg-gray-700';
                                    let buttonText = row.sto_id;

                                    if (row.status === 'D' && row.created_user === currentUser) {
                                        url = `/editstos/${row.eid}`;
                                        buttonClass =
                                            'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-yellow-500 hover:bg-yellow-700';
                                    }
                                    // Jika status H, arahkan ke /createstos?id=...
                                    if (row.status === 'H' && row.created_user === currentUser) {
                                        url = `/createstos?id=${row.eid}`;
                                        buttonClass =
                                            'px-4 py-2 bg-green-500 text-white rounded hover:bg-green-700';
                                    }

                                    return `<a href="${url}" class="px-3 py-1 ${buttonClass} text-white rounded">${buttonText}</a>`;
                                }
                            },
                            {
                                data: 'sto_date',
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
                                data: 'user',
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
                                    } else if (data === 'H') {
                                        statusText = "Draft";
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
                                        statusClass =
                                            " w-full max-w-32 bg-gray-300/30 bg-gray-300 text-gray-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                                    }
                                    return `<span class="${badgeClass}">${statusText}</span>`;
                                }

                            }
                        ]
                    });

                    $('.status-filter').on('click', function(e) {
                        e.preventDefault();
                        $('.status-filter').removeClass('active');
                        $(this).addClass('active');
                        table.ajax.reload();
                    });
                });

                // Make each .grid-col-1 set independent
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
        </div>
    </div>
</x-app-layout>
