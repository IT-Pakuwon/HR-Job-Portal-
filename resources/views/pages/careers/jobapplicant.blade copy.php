<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'jobpostings' ? 'HR' : '';
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
                    <a href="#" class="status-filter" data-status="">
                        <div
                            class="flex items-center gap-4 rounded-lg border border-orange-700 bg-orange-200/20 p-4 text-orange-600 shadow-md shadow-white">
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
                            class="flex items-center gap-4 rounded-lg border border-blue-700 bg-blue-200/20 p-4 text-blue-600 shadow-md shadow-white">
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
                            class="flex items-center gap-4 rounded-lg border border-red-700 bg-red-200/20 p-4 text-red-600 shadow-md shadow-white">
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
                            class="flex items-center gap-4 rounded-lg border border-gray-700 bg-gray-200/20 p-4 text-gray-600 shadow-md shadow-white">
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
                            class="flex items-center gap-4 rounded-lg border border-green-700 bg-green-200/20 p-4 text-green-600 shadow-md shadow-white">
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

                #jobpostingsTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    /* Aligns items to the left */
                    align-items: center;
                    /* Vertically aligns items */
                }

                #jobpostingsTable_filter label {
                    margin-right: 2px;
                }

                #jobpostingsTable_filter input {
                    width: 200px;
                    /* Adjust the width of the input box */
                }


                #jobpostingsTable_wrapper {
                    margin-top: 20px;
                    width: 100%;
                }

                /* Prevent text from wrapping */
                #jobpostingsTable td {
                    white-space: nowrap;
                    /* Prevent text from wrapping */
                    overflow: hidden;
                    /* Hide overflow content */
                    text-overflow: ellipsis;
                    /* Display ellipsis ("...") for overflowing content */
                }

                /* Optional: Adjust the width for table cells */
                #jobpostingsTable th,
                #jobpostingsTable td {
                    padding: 10px;
                    /* Adjust padding for better appearance */
                    max-width: 200px;
                    /* You can set a maximum width to control overflow */
                }


                #jobpostingsTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }

                #jobpostingsTable_length select {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                }

                #jobpostingsTable_length select option {
                    padding: 5px;
                    /* Mengatur jarak antar opsi */
                }

                #jobpostingsTable_info {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                .dataTables_paginate {
                    margin-top: 10px;
                    margin-bottom: 10px;

                }

                #jobpostingsTable tbody tr td {
                    padding: 8px 8px;
                    /* Adjust padding for uniform height */
                    line-height: 2;
                    /* Optional, for better text alignment */
                }

                #jobpostingsTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }

                #jobpostingsTable tbody tr:hover {
                    background-color: #8f8f8f11;
                    opacity: 100%;
                    cursor: pointer;
                }

                #jobpostingsTable tbody tr:hover td {
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
            </style>
            {{-- <div class="mt-6  overflow-y-auto bg-white  dark:bg-gray-800 p-4 rounded-xl">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <h1 class=" align-middle text-2xl font-bold  dark:text-white">List Job Posting</h1>     
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg">
                    <table id="jobpostingsTable" class="min-w-full rounded mt-5">
                        <thead class="bg-white-200 dark:text-white">
                            <tr>
                                <th class="px-4 py-3 text-left w-32">DocID</th>
                                <th class="px-4 py-3 text-center">Date</th>
                                <th class="px-4 py-3 text-center">Company</th>
                                <th class="px-4 py-3 text-center">Departement</th>
                                <th class="px-4 py-3 text-center">Title</th>
                                <th class="px-4 py-3 text-center">Level</th>
                                <th class="px-4 py-3 text-center w-32">Status</th>  
                            </tr>
                        </thead>
                            <tbody></tbody>
                    </table>
                </div>   
            </div>   --}}
            <div class="mt-6 grid grid-cols-1 gap-4 xl:grid-cols-2">
                <!-- TABEL KIRI: Job Posting -->
                <!-- TABEL KIRI: Job Posting -->
                <div class="overflow-x-auto rounded-xl bg-white p-4">
                    <h1 class="mb-4 text-2xl font-bold">List Job Posting</h1>
                    <table id="jobpostingsTable" class="min-w-full rounded">
                        <thead>
                            <tr>
                                <th>DocID</th>
                                <th>Date</th>
                                <th>Company</th>
                                <th>Departement</th>
                                <th>Title</th>
                                <th>Level</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- TABEL KANAN: Applicant (DISABLE/SEMBUNYIKAN AWALNYA) -->
                <div id="applicantsContainer" class="overflow-x-auto rounded-xl bg-white p-4" style="display:none;">
                    <h1 class="mb-4 text-2xl font-bold">Applicants</h1>
                    <table id="applicantsTable" class="min-w-full rounded">
                        <thead>
                            <tr>
                                <th>Docid</th>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Step</th>
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
                    let jobTable = $('#jobpostingsTable').DataTable({
                        ajax: "{{ route('jobapplicant.json') }}?status=P",
                        processing: true,
                        serverSide: false,
                        responsive: true,
                        order: [
                            [0, 'desc']
                        ],
                        columns: [{
                                data: 'id',
                                render: function(data, type, row) {
                                    let url = `/showjobpostings/${row.id}`;
                                    let buttonClass =
                                        'px-4 py-2.5 bg-indigo-500 text-white rounded hover:bg-indigo-700';
                                    let buttonText = row.docid;

                                    // // **Cek apakah user yang login sama dengan created_user dan status = D**
                                    // if (row.status === 'D' && row.created_user === currentUser) {
                                    //     url = `/editjobpostings/${row.id}`;
                                    //     buttonClass = 'px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-700';
                                    // }

                                    return `<a href="${url}" class="px-3 py-1 ${buttonClass} text-white rounded">${buttonText}</a>`;
                                }
                            },
                            {
                                data: 'date',
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
                                data: 'job_title',
                                className: 'no-pointer'
                            },
                            {
                                data: 'job_level',
                                className: 'no-pointer'
                            },
                        ]
                    });

                    let applicantTable = $('#applicantsTable').DataTable({
                        processing: true,
                        responsive: true,
                        searching: true,
                        paging: true,
                        info: true,
                        lengthChange: true,
                        pageLength: 10,
                        data: [],
                        columns: [{
                                data: 'docid',
                                width: '120px',
                                render: function(data, type, row) {
                                    return `<a href="/showcareers/${row.id}" target="_blank" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-700">${row.docid}</a>`;
                                }
                            },
                            {
                                data: 'apply_date',
                                width: '100px'
                            },
                            {
                                data: 'fullname',
                                width: '200px'
                            },
                            {
                                data: 'apply_step',
                                width: '180px',
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
                                    return `<span class="w-32 bg-blue-300/30 text-blue-600 font-semibold px-4 py-2 text-center rounded">${labelMap[data] || data}</span>`;
                                }
                            }
                        ]
                    });


                    $('#jobpostingsTable tbody').on('click', 'tr', function() {
                        let data = jobTable.row(this).data();
                        if (data && data.id) {
                            let jobId = data.docid;

                            $.ajax({
                                url: `/jobapplicant/applicants/${jobId}`,
                                type: 'GET',
                                success: function(res) {
                                    applicantTable.clear().rows.add(res.data).draw();
                                },
                                error: function() {
                                    // alert('Failed to load applicants.');
                                }
                            });
                        }
                    });

                    $('#cpnyidFilter').on('change', function() {
                        let selectedCpnyid = $(this).val();
                        let selectedStatus = $('.status-filter.active').data('status') || 'P';

                        let newUrl = "{{ route('jobapplicant.json') }}?status=" + encodeURIComponent(
                            selectedStatus);
                        if (selectedCpnyid) {
                            newUrl += "&cpnyid=" + encodeURIComponent(selectedCpnyid);
                        }

                        jobTable.ajax.url(newUrl).load();

                        updateCounts(selectedCpnyid); // <-- ini penting
                    });



                    // $('.status-filter').on('click', function (e) {
                    //     e.preventDefault();

                    //     let selectedStatus = $(this).data('status');

                    //     // URL baru dengan query param status
                    //     let newUrl = "{{ route('jobpostings.json') }}";
                    //     newUrl += "?status=" + encodeURIComponent(selectedStatus ?? '');

                    //     console.log("Loading DataTable with URL:", newUrl); // for debug

                    //     jobTable.ajax.url(newUrl).load();
                    // });
                    $('.status-filter').on('click', function(e) {
                        e.preventDefault();

                        $('.status-filter').removeClass('active');
                        $(this).addClass('active');

                        let selectedStatus = $(this).data('status');
                        let selectedCpnyid = $('#cpnyidFilter').val();

                        let newUrl = "{{ route('jobapplicant.json') }}?status=" + encodeURIComponent(
                            selectedStatus);
                        if (selectedCpnyid) {
                            newUrl += "&cpnyid=" + encodeURIComponent(selectedCpnyid);
                        }

                        jobTable.ajax.url(newUrl).load();
                    });

                    function updateCounts(cpnyid = '') {
                        let url = "{{ route('jobapplicant.counts') }}";
                        if (cpnyid) {
                            url += '?cpnyid=' + encodeURIComponent(cpnyid);
                        }

                        $.ajax({
                            url: url,
                            type: 'GET',
                            success: function(data) {
                                $('[data-status=""] .font-extrabold').text(data.all);
                                $('[data-status="P"] .font-extrabold').text(data.onProgress);
                                $('[data-status="R"] .font-extrabold').text(data.reject);
                                $('[data-status="D"] .font-extrabold').text(data.revise);
                                $('[data-status="C"] .font-extrabold').text(data.completed);
                            },
                            error: function() {
                                console.error('Failed to load status counts');
                            }
                        });
                    }






                });
            </script>

        </div>
    </div>
</x-app-layout>
