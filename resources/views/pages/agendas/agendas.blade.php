<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'agendas' ? 'HR' : '';
    @endphp
    <div class="max-w-9xl mx-auto w-full px-2 py-2 sm:px-6 lg:px-2">
        {{-- <div class="px-4 sm:px-6 lg:px-8 w-full max-w-9xl mx-auto"> --}}
        <div class="grid grid-rows-5 gap-6 xl:grid-cols-5 xl:grid-rows-1">
            <button>
                <a href="#" class="status-filter" data-status="">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-orange-700 bg-orange-200/20 p-4 text-orange-600 shadow-white">
                        <span class="text-lg">📄</span>
                        <div>
                            <p class="text-sm font-medium">All</p>
                            <p class="text-lg font-extrabold">{{ $all }}</p>
                        </div>
                    </div>
                </a>
            </button>
            <button>
                <a href="#" class="status-filter" data-status="P">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-blue-700 bg-blue-200/20 p-4 text-blue-600 shadow-white">
                        <span class="text-lg">⏳</span>
                        <div>
                            <p class="text-sm font-medium">On Progress</p>
                            <p class="text-left text-lg font-extrabold">{{ $onProgress }}</p>
                        </div>
                    </div>
                </a>
            </button>
            <button>
                <a href="#" class="status-filter" data-status="R">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-red-700 bg-red-200/20 p-4 text-red-600 shadow-white">
                        <span class="text-lg">⛔️</span>
                        <div>
                            <p class="text-sm font-medium">Reject</p>
                            <p class="text-left text-lg font-extrabold">{{ $reject }}</p>
                        </div>
                    </div>
                </a>
            </button>
            <button>
                <a href="#" class="status-filter" data-status="D">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-gray-700 bg-gray-200/20 p-4 text-gray-600 shadow-white dark:text-gray-400">
                        <span class="text-lg">✏️</span>
                        <div>
                            <p class="text-sm font-medium">Revise</p>
                            <p class="f text-left text-lg font-extrabold">{{ $revise }}</p>
                        </div>
                    </div>
                </a>
            </button>
            <button>
                <a href="#" class="status-filter" data-status="C">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-green-700 bg-green-200/20 p-4 text-green-600 shadow-white">
                        <span class="text-lg">✅</span>
                        <div>
                            <p class="text-sm font-medium">Completed</p>
                            <p class="text-left text-lg font-extrabold">{{ $completed }}</p>
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
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
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
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
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
            <div
                class="mt-2 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
                <div
                    class="flex flex-col gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">Agenda</h2>
                    <a href="{{ url('/createagendas') }}"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                        <i class="fas fa-plus pr-2"></i>Create Agenda</a>
                </div>
                <div x-data="{ tab: 'table' }">
                    <div class="flex gap-4 px-5 py-3">
                        <button @click="tab = 'table'"
                            :class="tab === 'table' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="rounded px-4 py-2 font-semibold">
                            📄 Agenda Table
                        </button>
                        <button @click="tab = 'calendar'"
                            :class="tab === 'calendar' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="rounded px-4 py-2 font-semibold">
                            📅 Schedule Calendar
                        </button>
                    </div>
                    <div class="relative overflow-hidden" x-show="tab === 'table'">
                        <table id="agendasTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                            <thead>
                                <tr
                                    class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                    <th class="w-32 px-4 py-3 text-left font-medium">DocID</th>
                                    <th class="px-4 py-3 text-left font-medium">Title</th>
                                    <th class="px-4 py-3 text-left font-medium">Description</th>
                                    <th class="px-4 py-3 text-left font-medium">StartDate</th>
                                    <th class="px-4 py-3 text-left font-medium">EndDate</th>
                                    <th class="px-4 py-3 text-left font-medium">Participant</th>
                                    <th class="w-32 px-4 py-3 text-left font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <!-- Tab 2: Calendar -->
                    <div class="p-5" x-show="tab === 'calendar'">
                        @include('pages.agendas.calendar')
                    </div>
                </div>

            </div>
            <script>
                var currentUser = "{{ auth()->user()->username }}";
            </script>


            <script>
                $(document).ready(function() {
                    let table = $('#agendasTable').DataTable({
                        ajax: "{{ route('agendas.json') }}",
                        processing: true,
                        serverSide: false,
                        responsive: true,
                        order: [
                            [0, 'desc']
                        ],
                        columns: [{
                                data: null,
                                defaultContent: ''
                            }, {
                                data: 'id',
                                render: function(data, type, row) {
                                    let url = `/showagendas/${row.id}`;
                                    let buttonClass =
                                        'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200  bg-gray-600 hover:bg-gray-700 ';
                                    let buttonText = row.docid;

                                    // **Cek apakah user yang login sama dengan created_user dan status = D**
                                    if (row.status === 'D' && row.created_user === currentUser) {
                                        url = `/editagendas/${row.id}`;
                                        buttonClass =
                                            'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-yellow-500 hover:bg-yellow-700';
                                    }

                                    return `<a href="${url}" class="px-3 py-1 ${buttonClass} text-white rounded">${buttonText}</a>`;
                                }
                            },
                            {
                                data: 'title',
                                className: 'no-pointer'
                            },
                            {
                                data: 'description',
                                className: 'no-pointer'
                            },
                            {
                                data: 'startdate',
                                className: 'no-pointer'
                            },
                            {
                                data: 'enddate',
                                className: 'no-pointer'
                            },
                            {
                                data: 'participant',
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
                                            "w-32 bg-amber-200/60 text-amber-800 dark:bg-amber-300/40 dark:text-amber-900 focus:outline-none pointer-events-none border border-amber-600/40 font-semibold px-4 py-2 text-center rounded";
                                    } else if (data === 'P') {
                                        statusText = "On Progress";
                                        badgeClass =
                                            "w-32 bg-orange-200/60 text-orange-800 dark:bg-orange-300/40 dark:text-orange-900 focus:outline-none pointer-events-none border border-orange-600/40 font-semibold px-4 py-2 text-center rounded";
                                    }
                                } else if (data === 'C') {
                                    statusText = "Completed";
                                    badgeClass =
                                        "w-32 bg-green-200/60 text-green-800 dark:bg-green-300/40 dark:text-green-900 focus:outline-none pointer-events-none border border-green-600/40 font-semibold px-4 py-2 text-center rounded";
                                } else if (data === 'X') {
                                    statusText = "Cancel";
                                    badgeClass =
                                        "w-32 bg-red-200/60 text-red-800 dark:bg-red-300/40 dark:text-red-900 focus:outline-none pointer-events-none border border-red-600/40 font-semibold px-4 py-2 text-center rounded";
                                } else if (data === 'R') {
                                    statusText = "Rejected";
                                    badgeClass =
                                        "w-32 bg-red-200/60 text-red-800 dark:bg-red-300/40 dark:text-red-900 focus:outline-none pointer-events-none border border-red-600/40 font-semibold px-4 py-2 text-center rounded";
                                } else {
                                    statusClass =
                                        "w-32 bg-gray-200/60 text-gray-700 dark:bg-gray-300/40 dark:text-gray-900 focus:outline-none pointer-events-none border border-gray-500/40 font-semibold px-4 py-2 text-center rounded";
                                }
                            }
                            return `<span class="${badgeClass}">${statusText}</span>`;
                        }

                    }]
                });

                $('.status-filter').on('click', function(e) {
                e.preventDefault();

                let selectedStatus = $(this).data('status');

                // URL baru dengan query param status
                let newUrl = "{{ route('agendas.json') }}";
                newUrl += "?status=" + encodeURIComponent(selectedStatus ?? '');

                console.log("Loading DataTable with URL:", newUrl); // for debug

                table.ajax.url(newUrl).load();
                });


                });
            </script>
        </div>
    </div>
</x-app-layout>
