<x-app-layout>
    <x-spp-list-styles />
    <x-spp-list-scripts />
    @php
        $currentPage = Route::currentRouteName() == 'budgets' ? 'HR' : '';
    @endphp
    <div class="max-w-9xl mx-auto w-full p-2">
        {{-- STATUS CARDS (On Progress is the default filter, see currentStatus below) --}}
        @php
            $budgetIcon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>';
            $statusCards = [
                [['data-status' => 'ALL'], 'All', '📦', 'all', $all, false],
                [['data-status' => 'P'], 'On Progress', '⏳', 'yellow', $onProgress, true],
                [['data-status' => 'R'], 'Reject', '⛔', 'red', $reject, false],
                [['data-status' => 'D'], 'Revise / Draft', '✏️', 'blue', $revise, false],
                [['data-status' => 'C'], 'Completed', '✅', 'green', $completed, false],
            ];
        @endphp
        <x-status-filter-cards :custom="$statusCards" />

        <div class="dt-pretty mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="Budget" subtitle="Import, track and manage your budgets" :icon="$budgetIcon">
                <div class="sppb-f relative w-56">
                    <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></span>
                    <select id="filterBusinessUnit">
                        <option value="">All Business Unit</option>
                        @foreach ($businessUnits as $bu)
                            <option value="{{ $bu->business_unit_id }}">{{ $bu->business_unit_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sppb-f relative w-56">
                    <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg></span>
                    <select id="filterDepartment">
                        <option value="">All Department</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->department_fin_id }}">{{ $dept->department_name }}</option>
                        @endforeach
                    </select>
                </div>

                <a href="{{ url('/createbudgets') }}"
                    class="inline-flex h-10 items-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                    <i class="fas fa-plus pr-2"></i>Import Budget
                </a>
            </x-list-page-header>

            <div class="relative overflow-x-hidden">
                <table id="budgetsTable" class="w-full min-w-full border-separate border-spacing-0 text-left text-sm rtl:text-right">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th scope="col" class="w-32 px-4 py-3 text-left font-medium">
                                DocID</th>
                            <th scope="col" class="w-32 px-4 py-3 text-left font-medium">
                                Date</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">
                                Perpost</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">
                                Company</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">
                                Business Unit</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">
                                Departement</th>
                            <th scope="col" class="px-4 py-3 text-right font-medium">
                                Total Budget</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">
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
            let currentStatus = 'P';
            let table = $('#budgetsTable').DataTable({
                ajax: {
                    url: "{{ route('budgets.json') }}",
                    data: function(d) {
                        d.status = currentStatus;
                        d.business_unit = $('#filterBusinessUnit').val();
                        d.department = $('#filterDepartment').val();
                    }
                },
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
                    width: '28px',
                    className: 'dtr-control',
                    orderable: false
                }],


                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fa-solid fa-file-excel"></i> Excel',
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
                        text: '<i class="fa-solid fa-file-csv"></i> CSV',
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
                    width: '28px',
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
                            return sppDocCell(row, row.budget_id, 'budgets', currentUser, true, ['D']);
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
                        className: 'no-pointer text-right tabular-nums',
                        render: function(data) {
                            return Number(data).toLocaleString('id-ID');
                        }
                    },
                    {
                        data: 'status',
                        className: 'no-pointer',
                        render: function(data) {
                            return sppStatusBadge(data);
                        }
                    }
                ]
            });

            $('.status-filter').on('click', function(e) {
                e.preventDefault();

                currentStatus = $(this).data('status') || 'ALL';

                table.ajax.reload();
            });

            $('#filterBusinessUnit, #filterDepartment').select2({
                width: '100%',
                minimumResultsForSearch: 8,
                dropdownCssClass: 'sppb-s2-dropdown'
            });

            $('#filterBusinessUnit, #filterDepartment').on('change', function() {
                table.ajax.reload();
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
