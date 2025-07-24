<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'jobpostings' ? 'HR' : '';
    @endphp
    <div class="max-w-9xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid-col-1 grid gap-6 xl:grid-cols-5 xl:grid-rows-1">
            {{-- All Status --}}
            <button>
                <a href="#" class="status-filter" data-status="">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600">
                        <span class="text-xl">📄</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">All</p>
                            <p class="text-right text-xl font-extrabold">{{ $all }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- Unchecked Status --}}
            <button>
                <a href="#" class="status-filter" data-status="is_read_N">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600">
                        <span class="text-xl">⏳</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">Unchecked</p>
                            <p class="text-right text-xl font-extrabold">{{ $unchecked }}</p>
                        </div>
                    </div>
                </a>
            </button>          

            {{-- Checked Status --}}
            <button>
                <a href="#" class="status-filter" data-status="is_read_Y">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 dark:border-white dark:text-white">
                        <span class="text-xl">✏️</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">Checked</p>
                            <p class="text-right text-xl font-extrabold">{{ $checked }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- Reject Status --}}
            <button>
                <a href="#" class="status-filter" data-status="R">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600">
                        <span class="text-xl">⛔️</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">Reject</p>
                            <p class="text-right text-xl font-extrabold">{{ $reject }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- Approved Status --}}
            <button>
                <a href="#" class="status-filter" data-status="C">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600">
                        <span class="text-xl">✅</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">Approved</p>
                            <p class="text-right text-xl font-extrabold">{{ $approved }}</p>
                        </div>
                    </div>
                </a>
            </button>
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
            </style>
            <div class="mt-6 rounded-2xl bg-white dark:bg-gray-800">
                <div
                    class="flex flex-col items-start justify-between gap-4 border-b border-gray-200 p-4 sm:flex-row sm:items-center dark:border-gray-700">
                    {{-- Changed text-3xl to text-xl --}}
                    <h1 class="text-xl font-extrabold text-gray-700 dark:text-white">Applicant List</h1>
                    <a"
                        class="inline-flex items-center rounded-xl bg-indigo-600 px-6 py-2 text-base font-semibold text-white transition-colors duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        List Job Posting
                        </a>
                </div>

                <div class="overflow-x-auto p-6"> {{-- Padding applied here instead of outer container --}}
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


            <script>
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
            </script>

        </div>
    </div>
</x-app-layout>
