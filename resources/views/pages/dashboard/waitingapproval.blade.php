<x-app-layout>  
    <div class="max-w-9xl mx-auto w-full px-2 py-2 sm:px-6 lg:px-2">        
        <script src="//unpkg.com/alpinejs" defer></script>
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

                #agendasTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    /* Aligns items to the left */
                    align-items: center;
                    /* Vertically aligns items */
                }

                #agendasTable_filter label {
                    margin-right: 2px;
                }

                #agendasTable_filter input {
                    width: 200px;
                    /* Adjust the width of the input box */
                }


                #agendasTable_wrapper {
                    margin-top: 20px;
                    width: 100%;
                }

                /* Prevent text from wrapping */
                #agendasTable td {
                    white-space: nowrap;
                    /* Prevent text from wrapping */
                    overflow: hidden;
                    /* Hide overflow content */
                    text-overflow: ellipsis;
                    /* Display ellipsis ("...") for overflowing content */
                }

                /* Optional: Adjust the width for table cells */
                #agendasTable th,
                #agendasTable td {
                    padding: 10px;
                    /* Adjust padding for better appearance */
                    max-width: 200px;
                    /* You can set a maximum width to control overflow */
                }


                #agendasTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }

                #agendasTable_length select {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                }

                #agendasTable_length select option {
                    padding: 5px;
                    /* Mengatur jarak antar opsi */
                }

                #agendasTable_info {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                .dataTables_paginate {
                    margin-top: 10px;
                    margin-bottom: 10px;

                }

                #agendasTable tbody tr td {
                    padding: 8px 8px;
                    /* Adjust padding for uniform height */
                    line-height: 2;
                    /* Optional, for better text alignment */
                }

                #agendasTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }

                #agendasTable tbody tr:hover {
                    background-color: #8f8f8f11;
                    opacity: 100%;
                    cursor: pointer;
                }

                #agendasTable tbody tr:hover td {
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
                #agendasTable th:nth-child(1),
                #agendasTable td:nth-child(1) {
                    width: 120px;
                    text-align: center;
                }

                #agendasTable th:nth-child(4),
                #agendasTable td:nth-child(4) {
                    width: 120px;
                    text-align: center;
                }
            </style>
            <div class="mt-2 overflow-y-auto rounded-xl bg-white p-4 dark:bg-gray-800">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <h1 class="align-middle text-2xl font-bold dark:text-white"></h1>
                    
                </div>
                <div x-data="{ tab: 'waitingapp' }" class="mt-4">
                    <div class="mb-4 flex space-x-4">
                        <button @click="tab = 'waitingapp'"
                            :class="tab === 'waitingapp' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="rounded px-4 py-2 font-semibold">
                            📄 Waiting Approval
                        </button>
                        <button @click="tab = 'approval'"
                            :class="tab === 'approval' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="rounded px-4 py-2 font-semibold">
                            📅 Approval
                        </button>
                    </div>
                    <div class="grid" x-show="tab === 'waitingapp'">
                        <div class="rounded-lg bg-white dark:bg-gray-800">
                            <table id="agendasTable" class="mt-5 min-w-full rounded">
                                <thead class="bg-white-200 dark:text-white">
                                    <tr>
                                        <th class="w-32 px-4 py-3 text-left">DocID</th>
                                        <th class="px-4 py-3 text-center">Date</th>
                                        <th class="px-4 py-3 text-center">Company</th>
                                        <th class="px-4 py-3 text-center">Departement</th>
                                        <th class="px-4 py-3 text-center">Info</th>                                       
                                        <th class="w-32 px-4 py-3 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 2: Calendar -->
                    <div x-show="tab === 'approval'">
                        @include('pages.dashboard.dashapproval')
                        {{-- @include('pages.agendas.calendar') --}}
                    </div>
                </div>
            </div>
            <script>
                var currentUser = "{{ auth()->user()->username }}";
            </script>         
            <script>
                $(document).ready(function() {
                    let table = $('#agendasTable').DataTable({
                        ajax: "{{ route('waitingapproval.json') }}",
                        processing: true,
                        serverSide: false,
                        responsive: true,
                        order: [
                            [0, 'desc']
                        ],
                        columns: [{
                                data: 'id',
                                render: function(data, type, row) {
                                    let url = `${window.location.origin}${row.url}/${row.id}`;
                                    let buttonClass =
                                        'px-4 py-2.5 bg-indigo-500 text-white rounded hover:bg-indigo-700';
                                    let buttonText = row.docid;


                                    return `<a href="${url}" class="px-3 py-1 ${buttonClass} text-white rounded">${buttonText}</a>`;
                                }
                            },
                            {
                                data: 'docdate',
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
                                data: 'infohd',
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

                });
            </script>
        </div>
    </div>
</x-app-layout>
