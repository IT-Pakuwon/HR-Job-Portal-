<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'personnels' ? 'HR' : '';
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
                <a href="#" class="hover:text-gray-900 dark:hover:text-white">HR</a>
                <span class="mx-2">/</span>

                <!-- Dropdown untuk Master -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center font-bold text-gray-800 dark:text-gray-100">
                        PRF <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Dropdown -->
                    <ul x-show="open" @click.away="open = false"
                        class="absolute right-0 z-10 mt-2 w-48 rounded border border-gray-300 bg-white shadow-md dark:border-gray-700 dark:bg-gray-800">
                        {{-- <li><a href="{{ route('account') }}" class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">My Account</a></li>
                        <li><a href="{{ route('screens') }}" class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">Master Screen</a></li>
                        <li><a href="{{ route('applications') }}" class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">Master Application</a></li>
                        <li><a href="{{ route('groups') }}" class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">Master Group</a></li>
                        <li><a href="{{ route('mastercard') }}" class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">Master Card</a></li> --}}
                    </ul>
                </div>
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

                #personnelsTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    /* Aligns items to the left */
                    align-items: center;
                    /* Vertically aligns items */
                }

                #personnelsTable_filter label {
                    margin-right: 2px;
                }

                #personnelsTable_filter input {
                    width: 200px;
                    /* Adjust the width of the input box */
                }


                #personnelsTable_wrapper {
                    margin-top: 20px;
                    width: 100%;
                }

                /* Prevent text from wrapping */
                #personnelsTable td {
                    white-space: nowrap;
                    /* Prevent text from wrapping */
                    overflow: hidden;
                    /* Hide overflow content */
                    text-overflow: ellipsis;
                    /* Display ellipsis ("...") for overflowing content */
                }

                /* Optional: Adjust the width for table cells */
                #personnelsTable th,
                #personnelsTable td {
                    padding: 10px;
                    /* Adjust padding for better appearance */
                    max-width: 200px;
                    /* You can set a maximum width to control overflow */
                }


                #personnelsTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }

                #personnelsTable_length select {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                }

                #personnelsTable_length select option {
                    padding: 5px;
                    /* Mengatur jarak antar opsi */
                }

                #personnelsTable_info {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                .dataTables_paginate {
                    margin-top: 10px;
                    margin-bottom: 10px;

                }

                #personnelsTable tbody tr td {
                    padding: 8px 8px;
                    /* Adjust padding for uniform height */
                    line-height: 2;
                    /* Optional, for better text alignment */
                }

                #personnelsTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }

                #personnelsTable tbody tr:hover {
                    background-color: #8f8f8f11;
                    opacity: 100%;
                    cursor: pointer;
                }

                #personnelsTable tbody tr:hover td {
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
                #personnelsTable th:nth-child(1),
                #personnelsTable td:nth-child(1) {
                    width: 120px;
                    text-align: center;
                }

                #personnelsTable th:nth-child(4),
                #personnelsTable td:nth-child(4) {
                    width: 120px;
                    text-align: center;
                }
            </style>
            <div class="mt-2 overflow-y-auto rounded-xl bg-white p-4 dark:bg-gray-800">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <h1 class="align-middle text-lg font-bold dark:text-white">Personnel Requisition Form (PRF)</h1>
                    {{-- <button id="addAppBtn" class="px-5 py-2 bg-indigo-500 text-white rounded-lg">+ Create PRF</button> --}}
                    <a href="{{ url('/createpersonnels') }}" class="rounded-lg bg-indigo-500 px-5 py-2 text-white">
                        <i class="fas fa-plus pr-2"></i>Create PRF</a>
                </div>
                <div class="rounded-lg bg-white dark:bg-gray-800">
                    <table id="personnelsTable" class="mt-5 min-w-full rounded">
                        <thead class="bg-white-200 dark:text-white">
                            <tr>
                                <th class="w-32 px-4 py-3 text-center">DocID</th>
                                <th class="px-4 py-3 text-left">Company</th>
                                <th class="px-4 py-3 text-left">Departement</th>
                                <th class="px-4 py-3 text-left">Title</th>
                                <th class="px-4 py-3 text-left">Level</th>
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
                    let table = $('#personnelsTable').DataTable({
                        ajax: "{{ route('personnels.json') }}",
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
                                    let url = `/showpersonnels/${row.id}`;
                                    let buttonClass =
                                        'px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-700';
                                    let buttonText = row.docid;

                                    // **Cek apakah user yang login sama dengan created_user dan status = D**
                                    if (row.status === 'D' && row.created_user === currentUser) {
                                        url = `/editpersonnels/${row.id}`;
                                        buttonClass =
                                            'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-yellow-500 hover:bg-yellow-700';
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
                                data: 'job_title',
                                className: 'no-pointer'
                            },
                            {
                                data: 'job_level',
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
                        let appId = $('#personnel_id').val();
                        let url = appId ? `/personnels/${appId}` : "{{ route('personnels.store') }}";
                        let method = appId ? 'PUT' : 'POST';

                        $.ajax({
                            url: url,
                            type: method,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            data: {
                                personnel_code: $('#personnel_code').val(),
                                personnel_name: $('#personnel_name').val(),
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
                    window.open('/createpersonnels');
                });
            </script>
        </div>
    </div>
</x-app-layout>
