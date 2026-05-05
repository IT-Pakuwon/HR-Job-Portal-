<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'jobpostings' ? 'HR' : '';
    @endphp

    <style>
        .select2-container {
            width: 100% !important;
            max-width: 100% !important;
        }

        .select2-selection__rendered {
            display: block !important;
            max-width: 100% !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
        }
    </style>
    <div class="max-w-9xl mx-auto p-2">

        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div
                class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center dark:border-gray-700">
                {{-- Changed text-lg to text-base --}}
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Self Register Applicant</h1>
                {{-- <a"
                        class="inline-flex items-center rounded-xl bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        List Job Posting
                        </a> --}}
            </div>
            {{-- Padding applied here instead of outer container --}}

            <div id="applicantsFilters" class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-5 lg:grid-cols-9">
                <!-- filters will be injected here -->
                <button id="btnResetFilters" class="rounded-md border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">
                    Reset
                </button>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="applicantsTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th scope="col" class="w-32 px-4 py-3 text-center">
                                DocID
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-left">
                                Date
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-left">
                                Name
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-left">
                                Divisi
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-left">
                                Department
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-left">
                                Education
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-left">
                                Religion
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-center">
                                Height
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-center">
                                Weight
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-center">
                                Last Working
                            </th>
                            <th scope="col" class="w-32 px-4 py-3 text-center">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Mapping Modal -->
        <div id="mappingModal"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">

            <div
                class="w-full max-w-2xl transform rounded-2xl bg-white p-8 shadow-2xl transition-all duration-300 scale-95 opacity-0"
                id="mappingModalContent">

                <!-- Header -->
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">
                            Mapping Applicant
                        </h2>
                        <p class="text-sm text-gray-500">
                            Assign candidate to job posting
                        </p>
                    </div>

                    <button id="closeMappingModal"
                        class="text-gray-400 hover:text-gray-600 text-lg">
                        ✕
                    </button>
                </div>

                <!-- Hidden -->
                <input type="hidden" id="mapApplicantId">

                <!-- Mapping Row -->
              <div class="mb-8 flex items-center gap-4 w-full">

                    <!-- DOC ID -->
                    <div
                        class="min-w-[200px] rounded-xl bg-gray-100 px-5 py-3 text-center text-base font-semibold text-gray-700 shadow-inner">
                        <span id="mapDocId">DOCID</span>
                    </div>

                    <!-- Arrow -->
                    <div class="text-gray-400 text-2xl">→</div>

                    <!-- Select -->
                    <div class="flex-1 min-w-0">
                        <select id="jobPostingSelect"
                            class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm
                                focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500
                                hover:border-gray-400 transition">
                        </select>
                    </div>

                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-2">
                    <button id="closeMappingModalBtn"
                        class="rounded-lg px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700">
                        Cancel
                    </button>

                    <button id="saveMapping"
                        class="rounded-xl px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow">
                        Save Mapping
                    </button>
                </div>
            </div>
        </div>

    </div>


    <script>
        var currentUser = "{{ auth()->user()->username }}";
    </script>




    <script>
        $(document).ready(function() {
            let currentStatus = '';

            // Filter input per kolom (index harus sesuai kolom DataTables)
            const columnFilters = [
                {
                    index: 1,
                    type: 'text',
                    placeholder: 'DocID'
                },
                {
                    index: 2,
                    type: 'text',
                    placeholder: 'Apply Date'
                },
                {
                    index: 3,
                    type: 'text',
                    placeholder: 'Full Name'
                },
                {
                    index: 4,
                    type: 'text',
                    placeholder: 'Division' // 🔥 NEW
                },
                {
                    index: 5,
                    type: 'text',
                    placeholder: 'Department' // 🔥 NEW
                },
                {
                    index: 6,
                    type: 'text',
                    placeholder: 'Education'
                },
                {
                    index: 7,
                    type: 'text',
                    placeholder: 'Religion'
                },
                {
                    index: 8,
                    type: 'text',
                    placeholder: 'Height'
                },
                {
                    index: 9,
                    type: 'text',
                    placeholder: 'Weight'
                },
                {
                    index: 10,
                    type: 'text',
                    placeholder: 'Company'
                }
            ];

            const $filters = $('#applicantsFilters');

            columnFilters.forEach(col => {
                const $el = $(`
                    <input type="text"
                        class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm
                            focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        placeholder="Search ${col.placeholder}">
                `);

                let debounce;
                $el.on('input', function() {
                    clearTimeout(debounce);
                    const val = this.value;
                    debounce = setTimeout(() => {
                        applicantTable.column(col.index).search(val).draw();
                    }, 300);
                });

                $filters.append($el);
            });

            const applicantTable = $('#applicantsTable').DataTable({
                responsive: {
                    details: {
                        type: 'column',
                        target: 0
                    }
                },

                columnDefs: [{
                        targets: 0,
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false,
                        searchable: false,
                        responsivePriority: 1
                    },
                    {
                        targets: [1, 2],
                        responsivePriority: 2
                    },
                    {
                        targets: [3, 4, 5, 6, 7, 8],
                        responsivePriority: 100
                    }
                ],

                processing: true,
                serverSide: true,
                searching: true,
                paging: true,
                info: true,

                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'Applicant_List',
                        className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '↓ CSV',
                        title: 'Applicant_List',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],

                pageLength: 10,

                ajax: {
                    url: "{{ route('selfregister.json') }}",
                    type: 'GET',
                    data: function(d) {
                        d.status = currentStatus;
                    }
                },

                // sort by DocID (kolom index 1)
                order: [
                    [1, 'desc']
                ],

                columns: [{ // 0 responsive control
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false,
                        searchable: false,
                        data: null,
                        defaultContent: ''
                    },
                    { // 1 docid
                        data: 'docid',
                        name: 'docid',
                        render: function(data, type, row) {
                            return `<a href="/showselfregister/${row.eid}" target="_blank"
                                class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm font-semibold text-white rounded  bg-gray-600 hover:bg-gray-700 ">
                                ${data}
                            </a>`;
                        }
                    },
                    {
                        data: 'apply_date',
                        name: 'apply_date'
                    }, // 2
                    {
                        data: 'fullname',
                        name: 'fullname'
                    },
                    {
                        data: 'division_name',
                        name: 'division_name'
                    },
                    {
                        data: 'department_name',
                        name: 'department_name'
                    },// 3
                    {
                        data: 'education_name',
                        name: 'education_name'
                    }, // 4
                    {
                        data: 'religion',
                        name: 'religion'
                    }, // 5
                    {
                        data: 'height',
                        name: 'height',
                        className: 'small-col'
                    }, // 6
                    {
                        data: 'weight',
                        name: 'weight',
                        className: 'small-col'
                    }, // 7
                    {
                        data: 'company_name',
                        name: 'company_name'
                    }, // 8
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {

                            // ✅ SUDAH MAPPED
                            if (row.jobposting_docid) {
                                return `
                                    <div class="flex flex-col items-center gap-3 py-2">

                                        <!-- STATUS -->
                                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-700">
                                            ✓ Mapped
                                        </span>

                                        <!-- JOB INFO -->
                                        <div class="text-center leading-tight">
                                            <div class="text-sm font-semibold text-gray-800">
                                                ${row.job_name || '-'}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                ${row.jobposting_docid}
                                            </div>
                                        </div>

                                        <!-- ACTION -->
                                        <button
                                            class="rollback-btn text-sm font-medium text-red-500 hover:text-red-600 hover:underline transition"
                                            data-id="${row.eid}" data-job="${row.jobposting_docid}">
                                            Undo Mapping
                                        </button>
                                    </div>
                                `;
                            }

                            // ❌ BELUM MAPPED
                            return `
                                <div class="flex justify-center py-3">
                                    <button
                                        class="map-btn px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow hover:bg-indigo-700 hover:shadow-md transition"

                                        data-id="${row.eid}"
                                        data-docid="${row.docid}">
                                        + Map Candidate
                                    </button>
                                </div>
                            `;
                        }
                    },
                ],

                rowCallback: function(row, data) {
                    $(row).css('color', '');
                    if (data.status === 'R') $(row).css('color', '#dc2626');
                    else $(row).css('color', 'black');
                }
            });

            $(document).on('click', '.rollback-btn', function () {

                let applicantId = $(this).data('id');
                let jobId = $(this).data('job'); // 🔥 TAMBAH

                if (!confirm('Undo mapping?')) return;

                $.ajax({
                    url: "{{ route('applicant.mapping.rollback') }}",
                    type: "POST",
                    data: {
                        applicant_id: applicantId,
                        jobposting_docid: jobId, // 🔥 WAJIB
                        _token: "{{ csrf_token() }}"
                    },
                    success: function () {
                        alert('Mapping removed!');
                        $('#applicantsTable').DataTable().ajax.reload();
                    },
                    error: function (err) {
                        console.log(err);
                        alert('Rollback failed!');
                    }
                });
            });

            // reset filters
            $('#btnResetFilters').on('click', function() {
                $('#applicantsFilters input').val('');
                applicantTable.search('').columns().search('').draw();
            });

            // filter status card
            $('.status-filter').on('click', function(e) {
                e.preventDefault();
                $('.status-filter').removeClass('active');
                $(this).addClass('active');
                currentStatus = $(this).data('status') || '';
                applicantTable.ajax.reload();
            });

            // ==============================
            // OPEN MODAL
            // ==============================
            $(document).on('click', '.map-btn', function () {
                let applicantId = $(this).data('id');
                let docId = $(this).data('docid');

                $('#mapApplicantId').val(applicantId);
                $('#mapDocId').text(docId);

                loadJobPostings();

                $('#mappingModal').removeClass('hidden').addClass('flex');

                setTimeout(() => {
                    $('#mappingModalContent')
                        .removeClass('scale-95 opacity-0')
                        .addClass('scale-100 opacity-100');
                }, 10);
            });


            // ==============================
            // CLOSE MODAL (FIXED ❗)
            // ==============================
            function closeModal() {
                $('#mappingModalContent')
                    .removeClass('scale-100 opacity-100')
                    .addClass('scale-95 opacity-0');

                setTimeout(() => {
                    $('#mappingModal').addClass('hidden').removeClass('flex');
                }, 200);
            }

            $('#closeMappingModal, #closeMappingModalBtn').on('click', closeModal);


            // ==============================
            // LOAD JOB POSTING
            // ==============================
            function loadJobPostings() {
                $.ajax({
                    url: "{{ route('jobposting.list') }}",
                    type: "GET",
                    success: function (res) {
                        let $select = $('#jobPostingSelect');

                        $select.empty().append(`<option value="">Select Job Posting</option>`);

                        res.forEach(item => {
                            $select.append(`
                                <option value="${item.docid}">
                                    ${item.docid} - ${item.job_name}
                                </option>
                            `);
                        });

                        // 🔥 INIT HERE
                        initSelect2();
                    }
                });
            }

            function initSelect2() {
                $('#jobPostingSelect').select2({
                    dropdownParent: $('#mappingModal'),
                    placeholder: "🔍 Search Job Posting...",
                    width: '100%',
                    allowClear: true,
                    templateResult: formatJob,
                    templateSelection: formatJobSelection
                });
            }

            function formatJob(data) {
                if (!data.id) return data.text;

                let text = data.text;

                // split docid + info
                let [doc, info] = text.split(' - ');

                return $(`
                    <div class="py-2 px-1">
                        <div style="font-size:14px; font-weight:600; color:#111827;">
                            ${info || '-'}
                        </div>
                        <div style="font-size:12px; color:#6b7280; margin-top:2px;">
                            ${doc}
                        </div>
                    </div>
                `);
            }

            function formatJobSelection(data) {
                if (!data.id) return data.text;

                let [doc, info] = data.text.split(' - ');

                return `${doc} - ${info}`;
            }

            $('#jobPostingSelect').select2({
                dropdownParent: $('#mappingModal'),
                width: '100%',
                placeholder: "🔍 Search job...",
            });
            // ==============================
            // SAVE MAPPING
            // ==============================
            $('#saveMapping').on('click', function () {
                let applicantId = $('#mapApplicantId').val();
                let jobPostingId = $('#jobPostingSelect').val();

                if (!jobPostingId) {
                    alert('Please select job posting');
                    return;
                }

                $.ajax({
                    url: "{{ route('applicant.mapping.store') }}",
                    type: "POST",
                    data: {
                        applicant_id: applicantId,
                       jobposting_docid: jobPostingId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function () {
                        alert('Mapping saved!');

                        closeModal();

                        $('#applicantsTable').DataTable().ajax.reload();
                    },
                    error: function () {
                        alert('Failed to save mapping');
                    }
                });
            });
        });
    </script>


    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


</x-app-layout>
