<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'jobpostings' ? 'HR' : '';
    @endphp
    <div class="max-w-9xl mx-auto px-8 py-4 sm:px-8 lg:px-8">
        
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div
                class="flex flex-col items-start justify-between gap-4 border-b border-gray-200 sm:flex-row sm:items-center dark:border-gray-700">
                {{-- Changed text-lg to text-base --}}
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Self Register Applicant</h1>
                {{-- <a"
                        class="inline-flex items-center rounded-xl bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        List Job Posting
                        </a> --}}
            </div>
            {{-- Padding applied here instead of outer container --}}

            <div id="applicantsFilters" class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-5 lg:grid-cols-11">
                <!-- filters will be injected here -->
                <button id="btnResetFilters"
                    class="rounded-md border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">
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
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
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
                { index: 1, type: 'text', placeholder: 'DocID' },
                { index: 2, type: 'text', placeholder: 'Apply Date' },
                { index: 3, type: 'text', placeholder: 'Full Name' },
                { index: 4, type: 'text', placeholder: 'Education' },
                { index: 5, type: 'text', placeholder: 'Religion' },
                { index: 6, type: 'text', placeholder: 'Height' },
                { index: 7, type: 'text', placeholder: 'Weight' },
                { index: 8, type: 'text', placeholder: 'Company' },
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
                responsive: { details: { type: 'column', target: 0 } },

                columnDefs: [
                    { targets: 0, className: 'dtr-control', orderable: false, searchable: false, responsivePriority: 1 },
                    { targets: [1,2], responsivePriority: 2 },
                    { targets: [3,4,5,6,7,8], responsivePriority: 100 }
                ],

                processing: true,
                serverSide: true,
                searching: true,
                paging: true,
                info: true,

                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'Applicant_List',
                        className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                        exportOptions: { columns: ':visible', modifier: { page: 'current' } }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '↓ CSV',
                        title: 'Applicant_List',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: { columns: ':visible', modifier: { page: 'current' } }
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
                order: [[1, 'desc']],

                columns: [
                    { // 0 responsive control
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
                                class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm font-semibold text-white rounded bg-gray-500 hover:bg-gray-700">
                                ${data}
                            </a>`;
                        }
                    },
                    { data: 'apply_date', name: 'apply_date' },          // 2
                    { data: 'fullname', name: 'fullname' },              // 3
                    { data: 'education_name', name: 'education_name' },  // 4
                    { data: 'religion', name: 'religion' },              // 5
                    { data: 'height', name: 'height', className:'small-col' }, // 6
                    { data: 'weight', name: 'weight', className:'small-col' }, // 7
                    { data: 'company_name', name: 'company_name' },      // 8
                ],

                rowCallback: function(row, data) {
                    $(row).css('color', '');
                    if (data.status === 'R') $(row).css('color', '#dc2626');                    
                    else $(row).css('color', 'black');
                }
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
        });
    </script>


    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


</x-app-layout>
