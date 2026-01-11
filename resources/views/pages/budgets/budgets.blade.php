<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'budgets' ? 'HR' : '';
    @endphp
    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">

            {{-- All Status --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="ALL">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">📄</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-base font-medium">All</p>
                        </div>

                        <p class="shrink-0 text-xl font-bold">{{ $all }}</p>
                    </div>
                </a>
            </button>

            {{-- On Progress Status --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="P">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">⏳</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-base font-medium">On Progress</p>
                        </div>

                        <p class="shrink-0 text-xl font-bold">{{ $onProgress }}</p>
                    </div>
                </a>
            </button>

            {{-- Reject Status --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="R">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">⛔️</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-base font-medium">Reject</p>
                        </div>

                        <p class="shrink-0 text-xl font-bold">{{ $reject }}</p>
                    </div>
                </a>
            </button>

            {{-- Revise / Draft Status --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="D">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">✏️</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-base font-medium">Revise / Draft</p>
                        </div>

                        <p class="shrink-0 text-xl font-bold">{{ $revise }}</p>
                    </div>
                </a>
            </button>

            {{-- Completed Status --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="C">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">✅</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-base font-medium">Completed</p>
                        </div>

                        <p class="shrink-0 text-xl font-bold">{{ $completed }}</p>
                    </div>
                </a>
            </button>

        </div>

        <div class="mt-6 flex flex-col gap-6 rounded-xl bg-white p-6 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-xl font-extrabold text-gray-700 dark:text-white">Budget</h1>
                <a href="{{ url('/createbudgets') }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-base font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    <i class="fas fa-plus pr-2"></i>Import Budget</a>
            </div>
            <div class="rounded-base relative overflow-x-auto">
                <table id="budgetsTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th class="w-8"></th>
                            <th scope="col" class="w-32 px-6 py-3 font-medium">
                                DocID</th>
                            <th scope="col" class="w-32 px-6 py-3 font-medium">
                                Date</th>
                            <th scope="col" class="px-6 py-3 font-medium">
                                Perpost</th>
                            <th scope="col" class="px-6 py-3 font-medium">
                                Company</th>
                            <th scope="col" class="px-6 py-3 font-medium">
                                Business Unit</th>
                            <th scope="col" class="px-6 py-3 font-medium">
                                Departement</th>
                            <th scope="col" class="px-6 py-3 font-medium">
                                Total Budget</th>
                            <th scope="col" class="px-6 py-3 font-medium">
                                Status</th>
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
            let table = $('#budgetsTable').DataTable({
                ajax: "{{ route('budgets.json') }}?status=P",
                processing: true,
                serverSide: false,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],
                responsive: {
                    details: {
                        type: 'column',
                        target: 0 // 👈 this is REQUIRED
                    }
                },

                columnDefs: [{
                    targets: 0,
                    className: 'dtr-control',
                    orderable: false
                }],


                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'List Budget',
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
                        title: 'List Budget',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                responsive: {
                    details: {
                        type: 'column',
                        target: 0 // 👈 this is REQUIRED
                    }
                },

                columnDefs: [{
                    targets: 0,
                    className: 'dtr-control',
                    orderable: false
                }],

                order: [1, 'asc'],
                columns: [{
                        data: null,
                        defaultContent: ''
                    },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            // let url = `/showbudgets/${row.id}`;
                            let url = `/showbudgets/${row.eid}`;
                            let buttonClass =
                                'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-base leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-gray-500 hover:bg-gray-700';
                            let buttonText = row.budget_id;

                            // **Cek apakah user yang login sama dengan created_user dan status = D**
                            if (row.status === 'D' && row.created_by === currentUser) {
                                // url = `/editbudgets/${row.id}`;
                                url = `/editbudgets/${row.eid}`;
                                buttonClass =
                                    'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-base leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-yellow-500 hover:bg-yellow-700';
                            }

                            return `<a href="${url}" class="px-3 py-1 ${buttonClass} text-white rounded">${buttonText}</a>`;
                        }
                    },
                    {
                        data: 'budget_date',
                        className: 'no-pointer'
                    },
                    {
                        data: 'perpost',
                        className: 'no-pointer'
                    },
                    {
                        data: 'cpny_id',
                        className: 'no-pointer'
                    },
                    {
                        data: 'business_unit_name',
                        className: 'no-pointer'
                    },
                    {
                        data: 'department_name',
                        className: 'no-pointer'
                    },
                    {
                        data: 'totalbudget',
                        className: 'no-pointer text-middle',
                        render: function(data) {
                            return Number(data).toLocaleString('id-ID');
                        }
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

            $('.status-filter').on('click', function(e) {
                e.preventDefault();

                let selectedStatus = $(this).data('status') || 'ALL';

                // URL baru dengan query param status
                let newUrl = "{{ route('budgets.json') }}";
                newUrl += "?status=" + encodeURIComponent(selectedStatus ?? '');

                console.log("Loading DataTable with URL:", newUrl); // for debug

                table.ajax.url(newUrl).load();
            });

            document.querySelectorAll('.status-filter').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelectorAll('.status-filter').forEach(b => b.classList.remove(
                        'active'));
                    this.classList.add('active');
                });
            });

        });
    </script>
</x-app-layout>
