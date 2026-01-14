<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'tasks' ? 'HR' : '';
    @endphp
    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <!-- Dashboard actions -->
        <div class="mb-8 sm:flex sm:items-center sm:justify-between"></div>
        <!-- Breadcrumb dengan Dropdown -->
        <div class="mb-4 flex items-center justify-end sm:mb-0">
            <!-- Title Page -->
            {{-- <h1 class="text-lg md:text-lg text-gray-800 dark:text-gray-100 font-bold">{{ $currentPage }}</h1> --}}
            <!-- Breadcrumb -->
            <nav class="flex items-center text-gray-600 dark:text-gray-300">
                <a href="#" class="hover:text-gray-900 dark:hover:text-white">Settings</a>
                <span class="mx-2">/</span>

                <!-- Dropdown untuk Master -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center font-bold text-gray-800 dark:text-gray-100">
                        Master <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Dropdown -->
                    <ul x-show="open" @click.away="open = false"
                        class="absolute left-0 z-10 mt-2 w-48 rounded border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-800">
                        <li><a href="{{ route('account') }}"
                                class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">My Account</a></li>
                        <li><a href="{{ route('screens') }}"
                                class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">Master Screen</a></li>
                        <li><a href="{{ route('applications') }}"
                                class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">Master Application</a>
                        </li>
                        <li><a href="{{ route('groups') }}"
                                class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">Master Group</a></li>
                        <li><a href="{{ route('mastercard') }}"
                                class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">Master Card</a></li>
                    </ul>
                </div>

                <span class="mx-2">/</span>
                <span class="font-bold text-gray-800 dark:text-gray-100">{{ $currentPage }}</span>
            </nav>
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

                #tasksTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    /* Aligns items to the left */
                    align-items: center;
                    /* Vertically aligns items */
                }

                #tasksTable_filter label {
                    margin-right: 2px;
                }

                #tasksTable_filter input {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }


                #tasksTable_wrapper {
                    margin-top: 20px;
                    width: 100%;
                }

                /* Prevent text from wrapping */
                #tasksTable td {
                    white-space: nowrap;
                    /* Prevent text from wrapping */
                    overflow: hidden;
                    /* Hide overflow content */
                    text-overflow: ellipsis;
                    /* Display ellipsis ("...") for overflowing content */
                }

                /* Optional: Adjust the width for table cells */
                #tasksTable th,
                #tasksTable td {
                    padding: 10px;
                    /* Adjust padding for better appearance */
                    max-width: 200px;
                    /* You can set a maximum width to control overflow */
                }


                #tasksTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }

                #tasksTable_length select {
                    width: 80px;
                    /* Lebar otomatis untuk select dropdown */
                    padding: 5px;
                    Menambahkan padding agar lebih nyaman min-width: 0px;
                    /* Lebar minimal untuk memastikan angka tidak tertutup */
                }

                #tasksTable_length select option {
                    padding: 5px;
                    /* Mengatur jarak antar opsi */
                }

                #tasksTable_info {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                .dataTables_paginate {
                    margin-top: 10px;
                    margin-bottom: 10px;

                }

                #tasksTable tbody tr td {
                    padding: 8px 8px;
                    /* Adjust padding for uniform height */
                    line-height: 1.6;
                    /* Optional, for better text alignment */
                }

                #tasksTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }

                #tasksTable tbody tr:hover {
                    background-color: #8f8f8f11;
                    opacity: 100%;
                    cursor: pointer;
                }

                #tasksTable tbody tr:hover td {
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
                #tasksTable th:nth-child(1),
                #tasksTable td:nth-child(1) {
                    width: 120px;
                    text-align: center;
                }

                #tasksTable th:nth-child(4),
                #tasksTable td:nth-child(4) {
                    width: 120px;
                    text-align: center;
                }
            </style>
            <div class="mt-6 rounded-xl bg-white p-4 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base font-bold text-gray-800 dark:text-white">Task Management</h2>
                    {{-- <button id="addAppBtn" class="px-5 py-2 bg-indigo-500 text-white rounded-lg">
                        + Create PRF
                    </button> --}}
                    <a href="{{ url('/createtasks') }}" class="rounded-lg bg-indigo-500 px-5 py-2 text-white">+ Create
                        Tasks</a>
                </div>

                <table id="tasksTable" class="w-full table-fixed border-collapse">
                    <thead class="bg-white dark:bg-gray-700">
                        <tr>
                            <th class="w-32 px-4 py-3 text-center">DocID</th>
                            <th class="px-4 py-3 text-left">Company</th>
                            <th class="px-4 py-3 text-left">Departement</th>
                            <th class="px-4 py-3 text-left">Summary</th>
                            <th class="px-4 py-3 text-left">Priority</th>
                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <script>
                $(document).ready(function() {
                    let table = $('#tasksTable').DataTable({
                        ajax: "{{ route('tasks.json') }}",
                        processing: true,
                        serverSide: false,
                        responsive: true,
                        order: [
                            [0, 'desc']
                        ],
                        columns: [{
                                data: 'id',
                                className: 'text-center',
                                render: function(data, type, row) {
                                    return `<a href="/showtasks/${row.id}" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-700">${row.taskid}</a>`;
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
                                data: 'summary',
                                className: 'no-pointer'
                            },
                            {
                                data: 'taskpriority',
                                className: 'no-pointer',
                                render: function(data) {
                                    let priorityText = "";
                                    let badgeClass = "";

                                    if (data === 'Highest') {
                                        priorityText = "Highest";
                                        badgeClass =
                                            "bg-red-300/30 dark:bg-red-300 text-red-500 dark:text-red-800 font-semibold focus:outline-none pointer-events-none border-none px-4 py-2 rounded-full";
                                    } else if (data === 'High') {
                                        priorityText = "High";
                                        badgeClass =
                                            "bg-red-300/30 dark:bg-red-300 text-red-500 dark:text-red-800 font-semibold focus:outline-none pointer-events-none border-none px-4 py-2 rounded-full";
                                    } else if (data === 'Medium') {
                                        priorityText = "Medium";
                                        badgeClass =
                                            "bg-yellow-300/30  dark:bg-yellow-300 text-yellow-600 dark:text-yellow-800 font-semibold  focus:outline-none pointer-events-none border-none px-4 py-2 rounded-full";
                                    } else if (data === 'Low') {
                                        priorityText = "Low";
                                        badgeClass =
                                            "bg-blue-300/30  dark:bg-bluee-300 text-blue-600 dark:text-blue-800 font-semibold  focus:outline-none pointer-events-none border-none px-4 py-2 rounded-full";
                                    } else if (data === 'Lowest') {
                                        priorityText = "Lowest";
                                        badgeClass =
                                            "bg-green-300/30  dark:bg-green-300 text-green-600 dark:text-green-800 font-semibold  focus:outline-none pointer-events-none border-none px-4 py-2 rounded-full";
                                    }

                                    return `<span class="${badgeClass}">${priorityText}</span>`;
                                }
                            },
                            {
                                data: 'status',
                                className: 'no-pointer',
                                render: function(data) {
                                    let statusText = "";
                                    let badgeClass = "";

                                    if (data === 'H') {
                                        statusText = "Hold";
                                        badgeClass =
                                            "bg-gray-300/30 dark:bg-gray-300 text-gray-600  focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded ";
                                    } else if (data === 'P') {
                                        statusText = "On Progress";
                                        badgeClass =
                                            " bg-blue-300/30 dark:bg-blue-300 text-blue-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center  rounded";
                                    } else if (data === 'C') {
                                        statusText = "Completed";
                                        badgeClass =
                                            "bg-green-300/30 dark:bg-green-300 text-green-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                                    } else if (data === 'X') {
                                        statusText === 'Cancel';
                                        statusClass =
                                            "bg-red-300/30 dark:bg-red-300 text-red-600  flex justify-items-center  focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                                    } else if (data === 'R') {
                                        statusText === 'Rejected';
                                        statusClass =
                                            "bg-red-300/30 dark:bg-red-300 text-red-600  flex justify-items-center  focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                                    } else {
                                        statusClass =
                                            "  w-full max-w-32 bg-gray-300/30  bg-gray-300  text-gray-600 flex justify-items-center  focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                                    }
                                    return `<span class="${badgeClass}">${statusText}</span>`;
                                }

                            }
                        ]
                    });

                    $('#appForm').submit(function(e) {
                        e.preventDefault();
                        let appId = $('#task_id').val();
                        let url = appId ? `/tasks/${appId}` : "{{ route('tasks.store') }}";
                        let method = appId ? 'PUT' : 'POST';

                        $.ajax({
                            url: url,
                            type: method,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            data: {
                                task_code: $('#task_code').val(),
                                task_name: $('#task_name').val(),
                            },
                            success: function() {
                                $('#appModal').addClass('hidden');
                                table.ajax.reload();
                            }
                        });
                    });


                });
            </script>

            <script>
                document.getElementById('addAppBtn').addEventListener('click', function() {
                    window.open('/createtasks');
                });
            </script>
        </div>
    </div>
</x-app-layout>
