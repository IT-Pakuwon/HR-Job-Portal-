<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'jobpostings' ? 'HR' : '';
    @endphp
    <div class="max-w-9xl mx-auto px-8 py-4 sm:px-8 lg:px-8">
        <div class="grid-col-1 grid gap-6 xl:grid-cols-5 xl:grid-rows-1">
            {{-- All Status --}}
            <button>
                <a href="#" class="status-filter" data-status="">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600">
                        <span class="text-base">📄</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-sm font-medium">All</p>
                            <p class="text-right text-base font-extrabold">{{ $all }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- On Progress Status --}}
            <button>
                <a href="#" class="status-filter" data-status="P">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600">
                        <span class="text-base">⏳</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-sm font-medium">On Progress</p>
                            <p class="text-right text-base font-extrabold">{{ $onProgress }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- Reject Status --}}
            <button>
                <a href="#" class="status-filter" data-status="R">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600">
                        <span class="text-base">⛔️</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-sm font-medium">Reject</p>
                            <p class="text-right text-base font-extrabold">{{ $reject }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- Revise / Draft Status --}}
            <button>
                <a href="#" class="status-filter" data-status="D">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 dark:border-white dark:text-white">
                        <span class="text-base">✏️</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-sm font-medium">Revise / Draft</p>
                            <p class="text-right text-base font-extrabold">{{ $revise }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- Completed Status --}}
            <button>
                <a href="#" class="status-filter" data-status="C">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600">
                        <span class="text-base">✅</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-sm font-medium">Completed</p>
                            <p class="text-right text-base font-extrabold">{{ $completed }}</p>
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

                #applicantsTable tr.row-checked td { color: #000000; }
                #applicantsTable tr.row-unchecked td { color: #2563eb; }
                #applicantsTable tr.row-reject td { color: #dc2626; }

                .dark #applicantsTable tr.row-checked td { color: #ffffff; }
                .dark #applicantsTable tr.row-unchecked td { color: #22d3ee; }
                .dark #applicantsTable tr.row-reject td { color: #f87171; }
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


                #applicantsContainer_wrapper {
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
            <div id="container" class="mt-2 grid grid-cols-1 gap-4 xl:grid-cols-1">
                <!-- TABEL KIRI: Job Posting -->
                <div id="jobpostingTableWrapper"
                    class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
                    <div
                        class="flex flex-col gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">List Job
                            Posting</h2>
                        <select id="cpnyidFilter" class="rounded border px-3 py-1">
                            <option value="">All</option>
                            <option value="AW">AW</option>
                            <option value="EP">EP</option>
                            <option value="PSA">PSA</option>
                            <option value="GPS">GPS</option>
                        </select>
                    </div>
                    <div class="relative overflow-hidden">
                        <table id="jobpostingsTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                            <thead>
                                <tr
                                    class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                    <th class="px-4 py-3 text-left font-medium">DocID</th>
                                    <th class="px-4 py-3 text-left font-medium">Date</th>
                                    <th class="px-4 py-3 text-left font-medium">Company</th>
                                    <th class="px-4 py-3 text-left font-medium">Departement</th>
                                    <th class="px-4 py-3 text-left font-medium">Title</th>
                                    <th class="px-4 py-3 text-left font-medium">Level</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>


                <!-- TABEL KANAN: Applicant -->
                <div id="applicantsContainer"
                    class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]"
                    style="display:none;">

                    <div
                        class="flex items-center justify-between border-b border-gray-100 px-5 py-2 dark:border-white/[0.06]">
                        <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">Applicants</h2>
                        <div class="flex flex-row-reverse items-center justify-end gap-4 text-base">
                            <button id="detailApplicantsBtn" class="font-semibold text-blue-500 hover:text-blue-700">See
                                Detail</button>
                            <button id="closeApplicantsBtn"
                                class="font-semibold text-red-500 hover:text-red-700">Close</button>

                        </div>

                    </div>
                    <div id="applicantsTableWrapper" class="relative overflow-hidden">
                        <table id="applicantsTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                            <thead>
                                <tr
                                    class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                    <th class="px-4 py-3 text-left font-medium">Docid</th>
                                    <th class="px-4 py-3 text-left font-medium">Date</th>
                                    <th class="px-4 py-3 text-left font-medium">Name</th>
                                    <th class="px-4 py-3 text-left font-medium">Education</th>
                                    <th class="px-4 py-3 text-left font-medium">Religion</th>
                                    <th class="px-4 py-3 text-left font-medium">Height</th>
                                    <th class="px-4 py-3 text-left font-medium">Weight</th>
                                    <th class="px-4 py-3 text-left font-medium">Last Working</th>
                                    <th class="px-4 py-3 text-left font-medium">Score</th>
                                    <th class="px-4 py-3 text-left font-medium">Step</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
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
                                        'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-gray-500 hover:bg-gray-700';
                                    let buttonText = row.docid;

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
                        responsive: true, // penting!
                        processing: true,
                        searching: true,
                        paging: true,
                        info: true,
                        lengthChange: true,
                        pageLength: 10,
                        data: [],
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
                                data: 'education_name',
                                // className: 'edu-col hidden'
                            },
                            {
                                data: 'religion',
                                // className: 'edu-col hidden'
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
                                data: 'company_name',
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
                                    return `<span class="w-32 bg-blue-300/30 text-blue-600 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-semibold px-4 py-2 text-center rounded">${labelMap[data] || data}</span>`;
                                }
                            }
                        ],

                        rowCallback: function(row, data, index) {
                            $(row).removeClass('row-checked row-unchecked row-reject');

                            if (data.status === 'R') {
                                $(row).addClass('row-reject');
                            } else if (data.is_read === 'N') {
                                $(row).addClass('row-unchecked');
                            } else {
                                $(row).addClass('row-checked');
                            }
                        }
                    });


                    $('#applicantsTable thead th').eq(5).addClass('small-col'); // Height
                    $('#applicantsTable thead th').eq(6).addClass('small-col'); // Weight
                    $('#applicantsTable thead th').eq(8).addClass('small-col'); // Score

                    // On clicking a Job Posting row, show applicants
                    $('#jobpostingsTable tbody').on('click', 'tr', function() {
                        let data = jobTable.row(this).data();

                        if (data && data.docid) {
                            let jobDocId = data.docid;

                            $('#applicantsContainer').show();
                            $('#container').removeClass('xl:grid-cols-1').addClass('xl:grid-cols-2');

                            jobTable.columns.adjust();
                            if (jobTable.responsive) {
                                jobTable.responsive.recalc();
                            }

                            applicantTable.columns.adjust();
                            if (applicantTable.responsive) {
                                applicantTable.responsive.recalc();
                            }

                            $(window).trigger('resize');

                            $.ajax({
                                url: `/jobapplicant/applicants/${jobDocId}`,
                                type: 'GET',
                                success: function(res) {
                                    applicantTable.clear().rows.add(res.data).draw();
                                    applicantTable.columns.adjust().draw();


                                },
                                error: function() {
                                    // alert('Failed to load applicants.');
                                }
                            });
                        }
                    });


                    // Close Applicants panel button click
                    // $('#closeApplicantsBtn').on('click', function() {
                    //     $('#applicantsContainer').hide();
                    //     $('#container').removeClass('xl:grid-cols-2').addClass('xl:grid-cols-1');
                    // });

                    $('#closeApplicantsBtn').on('click', function() {
                        $('#jobpostingTableWrapper').show(); // Tampilkan tabel kiri
                        // $('#applicantsContainer').show(); // Tampilkan tabel kanan
                        $('#applicantsContainer').hide();
                        // Reset layout jadi 2 kolom
                        $('#container').removeClass('xl:grid-cols-2').addClass('xl:grid-cols-1');

                        // Reset kolom applicants agar tidak span 2 kolom
                        $('#applicantsContainer').removeClass('xl:col-span-2');
                        // $('#applicantsTable thead th.edu-col, #applicantsTable tbody td.edu-col').addClass('hidden');
                    });


                    // Tombol Detail: perbesar applicantsTable dan sembunyikan jobpostingsTable
                    $('#detailApplicantsBtn').on('click', function() {
                        $('#jobpostingTableWrapper').hide(); // sembunyikan tabel kiri
                        $('#applicantsContainer').removeClass('xl:grid-cols-2').addClass(
                            'xl:col-span-2'); // optional: membuat lebar penuh
                        // $('#applicantsTable thead th.edu-col, #applicantsTable tbody td.edu-col').removeClass('hidden');
                        $('#detailApplicantsBtn').addClass('hidden');

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
