<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'applicants' ? 'HR' : '';
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
                    margin-top: 20px;
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

                #applicantsTable_info {
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
                #applicantsTable th:nth-child(1),
                #applicantsTable td:nth-child(1) {
                    width: 120px;
                    text-align: center;
                }

                #applicantsTable th:nth-child(4),
                #applicantsTable td:nth-child(4) {
                    width: 120px;
                    text-align: center;
                }
            </style>
            <div class="mt-6 overflow-y-auto rounded-xl bg-white p-4 dark:bg-gray-800">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <h1 class="align-middle text-2xl font-bold dark:text-white">List Applicant</h1>
                </div>
                <div class="rounded-lg bg-white dark:bg-gray-800">
                    <table id="applicantsTable" class="mt-5 min-w-full rounded">
                        <thead class="bg-white-200 dark:text-white">
                            <tr>
                                <th class="w-32 px-4 py-3 text-left">DocID</th>
                                <th class="px-4 py-3 text-center">Full Name</th>
                                <th class="px-4 py-3 text-center">Age</th>
                                <th class="px-4 py-3 text-center">Gender</th>
                                <th class="px-4 py-3 text-center">Phone</th>
                                <th class="px-4 py-3 text-center">Email</th>
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
                    let table = $('#applicantsTable').DataTable({
                        ajax: "{{ route('applicants.json') }}?status=P",
                        processing: true,
                        serverSide: false,
                        responsive: true,
                        order: [
                            [0, 'desc']
                        ],
                        columns: [{
                                data: 'id',
                                render: function(data, type, row) {
                                    let url = `/showapplicants/${row.id}`;
                                    let buttonClass =
                                        'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-base leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-gray-500 hover:bg-gray-700';
                                    let buttonText = row.applicant_id;

                                    // // **Cek apakah user yang login sama dengan created_user dan status = D**
                                    // if (row.status === 'D' && row.created_user === currentUser) {
                                    //     url = `/editapplicants/${row.id}`;
                                    //     buttonClass = 'inline-flex justify-center items-center w-[120px] p-2 bg-yellow-500 text-white rounded hover:bg-yellow-700 text-center';
                                    // }

                                    return `<a href="${url}" class="px-3 py-1 ${buttonClass} text-white rounded">${buttonText}</a>`;
                                }
                            },
                            {
                                data: 'full_name',
                                className: 'no-pointer'
                            },
                            {
                                data: 'age',
                                className: 'no-pointer'
                            },
                            {
                                data: 'gender',
                                className: 'no-pointer'
                            },
                            {
                                data: 'phone_number',
                                className: 'no-pointer'
                            },
                            {
                                data: 'email_address',
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
                        let newUrl = "{{ route('applicants.json') }}";
                        newUrl += "?status=" + encodeURIComponent(selectedStatus ?? '');

                        console.log("Loading DataTable with URL:", newUrl); // for debug

                        table.ajax.url(newUrl).load();
                    });




                });
            </script>
        </div>
    </div>
</x-app-layout>
