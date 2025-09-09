<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'jobpostings' ? 'HR' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-2 py-2 sm:px-6 lg:px-2">
        <!-- Filter Dropdown -->
        <div class="mb-6 flex justify-end">
            <select id="cpnyidFilter"
                class="w-full rounded-md border border-gray-300 px-4 py-2 text-gray-700 transition hover:border-gray-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Companies</option>
                <option value="AW">AW</option>
                <option value="EP">EP</option>
                <option value="PSA">PSA</option>
                <option value="GPS">GPS</option>
            </select>
        </div>
        <!-- Status Filter Buttons -->
        <div class="mb-6 flex items-center justify-between sm:mb-0">
            <div class="grid w-full grid-rows-5 gap-6 xl:grid-cols-5 xl:grid-rows-1">
                @php
                    $statuses = [
                        ['label' => 'All', 'status' => '', 'count' => $all, 'color' => 'orange', 'emoji' => '📄'],
                        [
                            'label' => 'On Progress',
                            'status' => 'P',
                            'count' => $onProgress,
                            'color' => 'blue',
                            'emoji' => '⏳',
                        ],
                        ['label' => 'Reject', 'status' => 'R', 'count' => $reject, 'color' => 'red', 'emoji' => '⛔️'],
                        ['label' => 'Revise', 'status' => 'D', 'count' => $revise, 'color' => 'gray', 'emoji' => '✏️'],
                        [
                            'label' => 'Completed',
                            'status' => 'C',
                            'count' => $completed,
                            'color' => 'green',
                            'emoji' => '✅',
                        ],
                    ];
                @endphp

                @foreach ($statuses as $status)
                    <button>
                        <a href="#" class="status-filter" data-status="{{ $status['status'] }}">
                            <div
                                class="border-{{ $status['color'] }}-700 bg-{{ $status['color'] }}-200/20 text-{{ $status['color'] }}-600 flex items-center gap-4 rounded-lg border p-4   shadow-white">
                                <span class="text-4xl">{{ $status['emoji'] }}</span>
                                <div>
                                    <p class="text-lg font-medium">{{ $status['label'] }}</p>
                                    <p class="text-3xl font-extrabold">{{ $status['count'] }}</p>
                                </div>
                            </div>
                        </a>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Tables -->
        <div id="container" class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-1">
            <!-- Job Posting Table -->
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

            <div id="applicantsContainer" class="overflow-x-auto rounded-xl bg-white p-4" style="display:none;">
                <div class="mb-4 flex items-center justify-between">
                    <h1 class="text-2xl font-bold">Applicants</h1>
                    <button id="closeApplicantsBtn" class="font-semibold text-red-500 hover:text-red-700">Close
                        ✖️</button>
                </div>
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

    </div>

    <script>
        $(document).ready(function() {
            var currentUser = "{{ auth()->user()->username }}";

            // Initialize Job Posting DataTable
            let jobTable = $('#jobpostingsTable').DataTable({
                ajax: "{{ route('jobapplicant.json') }}?status=P",
                processing: true,
                serverSide: false,
                responsive: true,
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'docid',
                        render: function(data, type, row) {
                            let url = `/showjobpostings/${row.id}`;
                            let buttonClass =
                                'px-4 py-2.5 bg-indigo-500 text-white rounded hover:bg-indigo-700';
                            return `<a href="${url}" class="${buttonClass}">${data}</a>`;
                        }
                    },
                    {
                        data: 'date'
                    },
                    {
                        data: 'cpnyid'
                    },
                    {
                        data: 'departementid'
                    },
                    {
                        data: 'job_title'
                    },
                    {
                        data: 'job_level'
                    }
                ]
            });

            // Initialize Applicants DataTable with empty data
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
                        render: function(data, type, row) {
                            return `<a href="/showcareers/${row.id}" target="_blank" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-700">${data}</a>`;
                        }
                    },
                    {
                        data: 'apply_date'
                    },
                    {
                        data: 'fullname'
                    },
                    {
                        data: 'apply_step',
                        render: function(data) {
                            const labelMap = {
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

            // On clicking a Job Posting row, show applicants
            $('#jobpostingsTable tbody').on('click', 'tr', function() {
                let data = jobTable.row(this).data();

                if (data && data.docid) {
                    let jobDocId = data.docid;

                    // Show Applicants container
                    $('#applicantsContainer').show();
                    $('#container').removeClass('xl:grid-cols-1').addClass('xl:grid-cols-2');

                    // Load applicants data via AJAX by docid
                    $.ajax({
                        url: `/jobapplicant/applicants/${jobDocId}`,
                        type: 'GET',
                        success: function(res) {
                            applicantTable.clear().rows.add(res.data).draw();
                        },
                        error: function() {
                            alert('Failed to load applicants.');
                        }
                    });
                }
            });

            // Close Applicants panel button click
            $('#closeApplicantsBtn').on('click', function() {
                $('#applicantsContainer').hide();
                $('#container').removeClass('xl:grid-cols-2').addClass('xl:grid-cols-1');
            });

            // Optional: Filter job postings by company ID
            $('#cpnyidFilter').on('change', function() {
                let val = $(this).val();
                let ajaxUrl = "{{ route('jobapplicant.json') }}" + (val ? "?cpnyid=" + val : "");
                jobTable.ajax.url(ajaxUrl).load();
            });

            // Optional: Filter job postings by status from buttons
            $('.status-filter').on('click', function(e) {
                e.preventDefault();
                let status = $(this).data('status');
                let ajaxUrl = "{{ route('jobapplicant.json') }}" + (status ? "?status=" + status : "");
                jobTable.ajax.url(ajaxUrl).load();

                // Hide applicants when status filter changes
                $('#applicantsContainer').hide();
                applicantTable.clear().draw();
            });
        });
    </script>

    <style>
        /* Your existing styles here, for brevity not repeated */
        /* Add the custom styles you had in your original code */
    </style>
</x-app-layout>
