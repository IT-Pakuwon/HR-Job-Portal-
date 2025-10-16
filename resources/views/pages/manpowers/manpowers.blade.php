<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'manpowers' ? 'HR' : '';
    @endphp
    <div class="max-w-9xl mx-auto w-full px-2 py-0 sm:px-6 lg:px-2">
        {{-- <div class="px-4 sm:px-6 lg:px-8 w-full max-w-9xl mx-auto"> --}}
        <!-- Dashboard actions -->
        <div class="mb-8 sm:flex sm:items-center sm:justify-between"></div>
        <!-- Breadcrumb dengan Dropdown -->
        <div class="flex items-center justify-between sm:mb-0">
            <!-- Title Page -->
            <div class="grid grid-rows-5 gap-6 xl:grid-cols-5 xl:grid-rows-1">
                <button>
                    <a href="#" class="status-filter" data-status="">
                        <div
                            class="flex items-center gap-4 rounded-lg border border-orange-700 bg-orange-200/20 p-4 text-orange-600 shadow-white">
                            <span class="text-4xl">📄</span>
                            <div>
                                <p class="text-lg font-medium">All</p>
                                <p class="text-3xl font-extrabold">{{ $all }}</p>
                            </div>
                        </div>
                    </a>
                </button>
                <button>
                    <a href="#" class="status-filter" data-status="P">
                        <div
                            class="flex items-center gap-4 rounded-lg border border-blue-700 bg-blue-200/20 p-4 text-blue-600 shadow-white">
                            <span class="text-4xl">⏳</span>
                            <div>
                                <p class="text-lg font-medium">On Progress</p>
                                <p class="text-left text-3xl font-extrabold">{{ $onProgress }}</p>
                            </div>
                        </div>
                    </a>
                </button>
                <button>
                    <a href="#" class="status-filter" data-status="R">
                        <div
                            class="flex items-center gap-4 rounded-lg border border-red-700 bg-red-200/20 p-4 text-red-600 shadow-white">
                            <span class="text-4xl">⛔️</span>
                            <div>
                                <p class="text-lg font-medium">Reject</p>
                                <p class="text-left text-3xl font-extrabold">{{ $reject }}</p>
                            </div>
                        </div>
                    </a>
                </button>
                <button>
                    <a href="#" class="status-filter" data-status="D">
                        <div
                            class="flex items-center gap-4 rounded-lg border border-gray-700 bg-gray-200/20 p-4 text-gray-600 shadow-white">
                            <span class="text-4xl">✏️</span>
                            <div>
                                <p class="text-lg font-medium">Revise</p>
                                <p class="f text-left text-3xl font-extrabold">{{ $revise }}</p>
                            </div>
                        </div>
                    </a>
                </button>
                <button>
                    <a href="#" class="status-filter" data-status="C">
                        <div
                            class="flex items-center gap-4 rounded-lg border border-green-700 bg-green-200/20 p-4 text-green-600 shadow-white">
                            <span class="text-4xl">✅</span>
                            <div>
                                <p class="text-lg font-medium">Completed</p>
                                <p class="text-left text-3xl font-extrabold">{{ $completed }}</p>
                            </div>
                        </div>
                    </a>
                </button>
            </div>
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

                #manpowersTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    /* Aligns items to the left */
                    align-items: center;
                    /* Vertically aligns items */
                }

                #manpowersTable_filter label {
                    margin-right: 2px;
                }

                #manpowersTable_filter input {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }


                #manpowersTable_wrapper {
                    margin-top: 20px;
                    width: 100%;
                }

                /* Prevent text from wrapping */
                #manpowersTable td {
                    white-space: nowrap;
                    /* Prevent text from wrapping */
                    overflow: hidden;
                    /* Hide overflow content */
                    text-overflow: ellipsis;
                    /* Display ellipsis ("...") for overflowing content */
                }

                /* Optional: Adjust the width for table cells */
                #manpowersTable th,
                #manpowersTable td {
                    padding: 10px;
                    /* Adjust padding for better appearance */
                    max-width: 200px;
                    /* You can set a maximum width to control overflow */
                }


                #manpowersTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }

                #manpowersTable_length select {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }

                #manpowersTable_length select option {
                    padding: 5px;
                    /* Mengatur jarak antar opsi */
                }

                #manpowersTable_info {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                .dataTables_paginate {
                    margin-top: 10px;
                    margin-bottom: 10px;

                }

                #manpowersTable tbody tr td {
                    padding: 8px 8px;
                    /* Adjust padding for uniform height */
                    line-height: 2;
                    /* Optional, for better text alignment */
                }

                #manpowersTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }

                #manpowersTable tbody tr:hover {
                    background-color: #8f8f8f11;
                    opacity: 100%;
                    cursor: pointer;
                }

                #manpowersTable tbody tr:hover td {
                    color: black;
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
                #manpowersTable th:nth-child(1),
                #manpowersTable td:nth-child(1) {
                    width: 120px;
                    text-align: center;
                }

                #manpowersTable th:nth-child(4),
                #manpowersTable td:nth-child(4) {
                    width: 120px;
                    text-align: center;
                }
            </style>
            <div class="mt-6 overflow-y-auto rounded-xl bg-white p-4 dark:bg-gray-800">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <h1 class="align-middle text-2xl font-bold dark:text-white">Manpower Planning</h1>
                    <a href="{{ url('/createmanpowers') }}" class="rounded-lg bg-indigo-500 px-5 py-2 text-white">
                        <i class="fas fa-plus pr-2"></i>Create Manpower</a>
                </div>
                <div class="rounded-lg bg-white dark:bg-gray-800">
                    <table id="manpowersTable" class="mt-5 min-w-full rounded">
                        <thead class="bg-white-200 dark:text-white">
                            <tr>
                                <th class="w-32 px-4 py-3 text-left">DocID</th>
                                <th class="px-4 py-3 text-center">Company</th>
                                <th class="px-4 py-3 text-center">Departement</th>
                                <th class="px-4 py-3 text-center">Periode</th>
                                <th class="px-4 py-3 text-center">Required</th>
                                <th class="px-4 py-3 text-center">Actual</th>
                                <th class="px-4 py-3 text-center">Total</th>
                                <th class="w-32 px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <script>
                var currentUser = "{{ auth()->user()->username }}";
            </script>


            <script>
                $(document).ready(function() {
                    let table = $('#manpowersTable').DataTable({
                        ajax: "{{ route('manpowers.json') }}?status=P",
                        processing: true,
                        serverSide: false,
                        responsive: true,
                        order: [
                            [0, 'desc']
                        ],
                        columns: [{
                                data: 'id',
                                render: function(data, type, row) {
                                    let url = `/showmanpowers/${row.id}`;
                                    let buttonClass =
                                        'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-medium text-white rounded text-center transition-colors duration-200 bg-gray-500 hover:bg-gray-700';
                                    let buttonText = row.docid;

                                    // **Cek apakah user yang login sama dengan created_user dan status = D**
                                    if (row.status === 'D' && row.created_user === currentUser) {
                                        url = `/editmanpowers/${row.id}`;
                                        buttonClass =
                                            'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-medium text-white rounded text-center transition-colors duration-200 bg-yellow-500 hover:bg-yellow-700';
                                    }

                                    return `<a href="${url}" class="px-3 py-1 ${buttonClass} text-white rounded">${buttonText}</a>`;
                                }
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
                                data: 'periodyear',
                                className: 'no-pointer'
                            },
                            {
                                data: 'required',
                                className: 'no-pointer'
                            },
                            {
                                data: 'actual',
                                className: 'no-pointer'
                            },
                            {
                                data: 'total_actual',
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
                                        statusClass =
                                            "  w-full max-w-32 bg-gray-300/30  bg-gray-300  text-gray-600 flex justify-items-center  focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                                    }
                                    return `<span class="${badgeClass}">${statusText}</span>`;
                                }

                            }
                        ]
                    });

                    $('.status-filter').on('click', function(e) {
                        e.preventDefault();

                        let selectedStatus = $(this).data('status');

                        // URL baru dengan query param status
                        let newUrl = "{{ route('manpowers.json') }}";
                        newUrl += "?status=" + encodeURIComponent(selectedStatus ?? '');

                        console.log("Loading DataTable with URL:", newUrl); // for debug

                        table.ajax.url(newUrl).load();
                    });




                });
            </script>
        </div>
    </div>
</x-app-layout>
