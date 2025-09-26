<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'careers' ? 'HR' : '';
    @endphp
    <div class="max-w-9xl mx-auto w-full px-2 py-0 sm:px-6 lg:px-2">
        {{-- <div class="px-4 sm:px-6 lg:px-8 w-full max-w-9xl mx-auto"> --}}
        <!-- Dashboard actions -->
        <div class="mb-8 sm:flex sm:items-center sm:justify-between"></div>
        <div class="mb-4 flex items-center justify-end">
            {{-- <label for="cpnyidFilter" class="mr-2 font-semibold text-gray-700"></label> --}}
            <select id="cpnyidFilter" class="rounded border px-3 py-1">
                <option value="">All</option>
                <option value="AW">AW</option>
                <option value="EP">EP</option>
                <option value="PSA">PSA</option>
                <option value="GPS">GPS</option>
            </select>
        </div>

        <!-- Breadcrumb dengan Dropdown -->
        <div class="flex items-center justify-between sm:mb-0">
            <!-- Title Page -->
            <div class="grid grid-rows-5 gap-6 xl:grid-cols-5 xl:grid-rows-1">
                <button>
                    <a href="#" class="status-filter" data-status_app="H">
                        <div
                            class="flex items-center gap-4 rounded-lg border border-orange-700 bg-orange-200/20 p-4 text-orange-600 shadow-white">
                            <span class="text-4xl">📄</span>
                            <div>
                                <p class="text-lg font-medium">Incompleted Profile</p>
                                {{-- <p class="text-3xl font-extrabold">{{ $incompletedprofile }}</p> --}}
                                <p class="text-3xl font-extrabold" id="incompletedprofile">{{ $incompletedprofile }}</p>
                            </div>
                        </div>
                    </a>
                </button>
                <button>
                    <a href="#" class="status-filter" data-status_app="P">
                        <div
                            class="flex items-center gap-4 rounded-lg border border-blue-700 bg-blue-200/20 p-4 text-blue-600 shadow-white">
                            <span class="text-4xl">✅</span>
                            <div>
                                <p class="text-lg font-medium">Completed Profile</p>
                                {{-- <p class="text-3xl text-left font-extrabold">{{ $completedprofile }}</p> --}}
                                <p class="text-3xl font-extrabold" id="completedprofile">{{ $completedprofile }}</p>
                            </div>
                        </div>
                    </a>
                </button>
                <button>
                    <a href="#" class="status-filter" data-status="H">
                        <div
                            class="flex items-center gap-4 rounded-lg border border-orange-700 bg-orange-200/20 p-4 text-orange-600 shadow-white">
                            <span class="text-4xl">📄</span>
                            <div>
                                <p class="text-lg font-medium">Applicant</p>
                                {{-- <p class="text-3xl font-extrabold">{{ $nocandidate }}</p> --}}
                                <p class="text-3xl font-extrabold" id="nocandidate">{{ $nocandidate }}</p>
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
                                <p class="text-lg font-medium">Candidate</p>
                                {{-- <p class="text-3xl text-left font-extrabold">{{ $candidate }}</p> --}}
                                <p class="text-3xl font-extrabold" id="candidate">{{ $candidate }}</p>
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
                                <p class="text-lg font-medium">Completed Join</p>
                                {{-- <p class="text-3xl  text-left font-extrabold">{{ $join }}</p> --}}
                                <p class="text-3xl font-extrabold" id="join">{{ $join }}</p>

                            </div>
                        </div>
                    </a>
                </button>

            </div>
        </div>
        <div class="grid">
            <style>
                #cpnyidFilter {
                    min-width: 100px;
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
                }

                .dataTables_wrapper {
                    width: 100%;
                }

                @media (max-width: 600px) {
                    .dataTables_wrapper {
                        padding: 0 10px;
                    }
                }

                #careersTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    /* Aligns items to the left */
                    align-items: center;
                    /* Vertically aligns items */
                }

                #careersTable_filter label {
                    margin-right: 2px;
                }

                #careersTable_filter input {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }


                #careersTable_wrapper {
                    margin-top: 20px;
                    width: 100%;
                }

                /* Prevent text from wrapping */
                #careersTable td {
                    white-space: nowrap;
                    /* Prevent text from wrapping */
                    overflow: hidden;
                    /* Hide overflow content */
                    text-overflow: ellipsis;
                    /* Display ellipsis ("...") for overflowing content */
                }

                /* Optional: Adjust the width for table cells */
                #careersTable th,
                #careersTable td {
                    padding: 10px;
                    /* Adjust padding for better appearance */
                    max-width: 200px;
                    /* You can set a maximum width to control overflow */
                }


                #careersTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }

                #careersTable_length select {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }

                #careersTable_length select option {
                    padding: 5px;
                    /* Mengatur jarak antar opsi */
                }

                #careersTable_info {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                .dataTables_paginate {
                    margin-top: 10px;
                    margin-bottom: 10px;

                }

                #careersTable tbody tr td {
                    padding: 8px 8px;
                    /* Adjust padding for uniform height */
                    line-height: 2;
                    /* Optional, for better text alignment */
                }

                #careersTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }

                #careersTable tbody tr:hover {
                    background-color: #8f8f8f11;
                    opacity: 100%;
                    cursor: pointer;
                }

                #careersTable tbody tr:hover td {
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
                #careersTable th:nth-child(1),
                #careersTable td:nth-child(1) {
                    width: 120px;
                    text-align: center;
                }

                #careersTable th:nth-child(4),
                #careersTable td:nth-child(4) {
                    width: 120px;
                    text-align: center;
                }
            </style>
            <div class="mt-6 overflow-y-auto rounded-xl bg-white p-4 dark:bg-gray-800">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <h1 class="align-middle text-2xl font-bold dark:text-white">List Job Applicant</h1>
                </div>
                <div class="rounded-lg bg-white dark:bg-gray-800">
                    <table id="careersTable" class="mt-5 min-w-full rounded">
                        <thead class="bg-white-200 dark:text-white">
                            <tr>
                                <th class="w-32 px-4 py-3 text-left">DocID</th>
                                <th class="px-4 py-3 text-center">Name</th>
                                <th class="px-4 py-3 text-center">Date</th>
                                <th class="px-4 py-3 text-center">Job Title</th>
                                <th class="px-4 py-3 text-center">Step</th>
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
                    let table = $('#careersTable').DataTable({
                        ajax: buildDataUrl(),
                        processing: true,
                        serverSide: false,
                        responsive: true,
                        order: [
                            [0, 'desc']
                        ],
                        columns: [{
                                data: 'id',
                                render: function(data, type, row) {
                                    let url = `/showcareers/${row.id}`;
                                    let buttonClass =
                                        'px-4 py-2.5 bg-indigo-500 text-white rounded hover:bg-indigo-700';
                                    let buttonText = row.docid;

                                    if (row.status === 'D' && row.created_user === currentUser) {
                                        url = `/editcareers/${row.id}`;
                                        buttonClass =
                                            'px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-700';
                                    }

                                    return `<a href="${url}" class="px-3 py-1 ${buttonClass} text-white rounded">${buttonText}</a>`;
                                }
                            },
                            {
                                data: 'fullname',
                                className: 'no-pointer'
                            },
                            {
                                data: 'apply_date',
                                className: 'no-pointer'
                            },
                            {
                                data: 'job_title',
                                className: 'no-pointer'
                            },
                            {
                                data: 'apply_step',
                                className: 'no-pointer',
                                render: function(data) {
                                    let labelMap = {
                                        'JOAPP': 'Job Apply',
                                        'WIHC': 'Waiting Interview HC',
                                        'IHC': 'Interview HC',
                                        'WIU': 'Waiting Interview User',
                                        'IU': 'Interview User',
                                        'WPT': 'Waiting Psycho Test',
                                        'PT': 'Psycho Test',
                                        'OFF': 'Offering',
                                        'JOIN': 'Join'
                                    };
                                    return `<span class="w-32 bg-blue-300/30 dark:bg-blue-300 text-blue-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">${labelMap[data] || data}</span>`;
                                }
                            },
                        ]
                    });

                    // Refilter when company changed
                    $('#cpnyidFilter').on('change', function() {
                        const url = buildDataUrl();
                        console.log('Filter CPNYID:', url);
                        table.ajax.url(url).load();
                        fetchStats();
                    });

                    // Refilter when status-filter clicked
                    $('.status-filter').on('click', function(e) {
                        e.preventDefault();
                        $('.status-filter').removeClass('active');
                        $(this).addClass('active');
                        const url = buildDataUrl();
                        console.log('Filter status or step:', url);
                        table.ajax.url(url).load();
                        fetchStats();
                    });
                });
            </script>
            <script>
                function buildDataUrl() {
                    let baseUrl = "{{ route('careers.json') }}";
                    let params = [];

                    const activeStatusApp = $('.status-filter[data-status_app].active').data('status_app');
                    const activeStatus = $('.status-filter[data-status].active').data('status');
                    const cpnyid = $('#cpnyidFilter').val();

                    if (activeStatusApp) params.push("status_app=" + encodeURIComponent(activeStatusApp));
                    if (activeStatus) params.push("status=" + encodeURIComponent(activeStatus));
                    if (cpnyid) params.push("cpnyid=" + encodeURIComponent(cpnyid));

                    return baseUrl + (params.length ? '?' + params.join('&') : '');
                }
            </script>
            <script>
                function fetchStats() {
                    $.ajax({
                        url: "{{ route('careers.stats') }}",
                        type: 'GET',
                        data: {
                            cpnyid: $('#cpnyidFilter').val()
                        },
                        success: function(data) {
                            $('#incompletedprofile').text(data.incompletedprofile);
                            $('#completedprofile').text(data.completedprofile);
                            $('#nocandidate').text(data.nocandidate);
                            $('#candidate').text(data.candidate);
                            $('#join').text(data.join);
                        },
                        error: function(xhr) {
                            console.error('Failed to fetch stats:', xhr.responseText);
                        }
                    });
                }
            </script>
        </div>
    </div>
</x-app-layout>
