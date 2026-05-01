<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'imbudgetnonpurch' ? 'HR' : '';
        $hasAllList = auth()->user()->hasRole('COSTCTRLACCESS');

        $xlCols = 5;
        if ($hasAllList) {
            $xlCols++;
        }
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- STATUS CARDS --}}
        <div class="xl:grid-cols-{{ $xlCols }} grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">

            {{-- All --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📄</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">All</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $all }}</p>
                    </div>
                </a>
            </button>

            {{-- On Progress --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="P">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⏳</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">On Progress</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $onProgress }}</p>
                    </div>
                </a>
            </button>

            {{-- Reject --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="R">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⛔️</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Reject</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $reject }}</p>
                    </div>
                </a>
            </button>

            {{-- Revise / Draft --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="D">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✏️</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Revise / Draft</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $revise }}</p>
                    </div>
                </a>
            </button>

            {{-- Completed --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="C">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✅</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Completed</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $completed }}</p>
                    </div>
                </a>
            </button>

            {{-- All List --}}
            @if ($hasAllList)
                <button type="button" class="text-left">
                    <a href="#" class="status-filter group block h-full" data-mode="all">
                        <div
                            class="status-card flex h-full items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-md active:scale-95">
                            <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📊</div>
                            <div class="flex min-w-0 flex-grow flex-col leading-tight">
                                <p class="break-words text-sm font-medium">IM Budget All List</p>
                            </div>
                            <p class="shrink-0 text-base font-bold">{{ $allListCount }}</p>
                        </div>
                    </a>
                </button>
            @endif

        </div>

        {{-- TABLE SECTION --}}
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-center justify-between gap-4 sm:flex-row sm:items-center">
                <h1 id="pageTitle" class="text-base font-extrabold text-gray-700 dark:text-white">
                    IM Budget Non Purchase
                </h1>

                <div class="flex items-center gap-4">

                    {{-- FILTER ONLY ALL MODE --}}
                    <div id="allFilters" class="flex hidden items-center gap-2">

                        <select id="filterStatus"
                            class="rounded-md border px-3 py-1 text-sm dark:border-gray-700 dark:bg-gray-800">
                            <option value="">All Status</option>
                            <option value="D">Revise / Draft</option>
                            <option value="P">On Progress</option>
                            <option value="R">Rejected</option>
                            <option value="C">Completed</option>
                        </select>

                        <select id="filterDepartment"
                            class="rounded-md border px-3 py-1 text-sm dark:border-gray-700 dark:bg-gray-800">
                            <option value="">All Department</option>
                        </select>

                    </div>

                    <a id="createBtn" href="{{ url('/createimbudgetnonpurch') }}"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                        <i class="fas fa-plus pr-2"></i>Create
                    </a>
                </div>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="imbudgetNonPurchTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th scope="col" class="w-40 px-6 py-2 font-medium">DocID</th>
                            <th scope="col" class="w-32 px-6 py-2 font-medium">Date</th>
                            <th scope="col" class="w-32 px-6 py-2 font-medium">Company</th>
                            <th scope="col" class="w-40 px-6 py-2 font-medium">Department</th>
                            <th scope="col" class="w-40 px-6 py-2 font-medium">Requester</th>
                            <th scope="col" class="w-40 px-6 py-2 font-medium">Type</th>
                            <th scope="col" class="w-56 px-6 py-2 font-medium">Description</th>
                            <th scope="col" class="w-40 px-6 py-2 font-medium">Budget From</th>
                            <th scope="col" class="w-40 px-6 py-2 font-medium">Budget To</th>
                            <th scope="col" class="w-40 px-6 py-2 font-medium">Expenditure</th>
                            <th scope="col" class="w-40 px-6 py-2 font-medium">Existing Budget</th>
                            <th scope="col" class="w-40 px-6 py-2 font-medium">Request Budget</th>
                            <th scope="col" class="w-40 px-6 py-2 font-medium">Over Budget</th>
                            <th scope="col" class="w-32 px-6 py-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        var currentUser = "{{ auth()->user()->username }}";

        function formatNumber(val) {
            if (val === null || val === undefined || val === '') return '0.00';

            const num = Number(val);
            if (isNaN(num)) return '0.00';

            return num.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        $(document).ready(function() {
            let statusFilter = 'P';
            let mode = 'normal';
            let deptFilter = '';

            const table = $('#imbudgetNonPurchTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],
                scrollX: true,
                responsive: {
                    details: {
                        type: 'column',
                        target: 0
                    }
                },
                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'IM_Budget_Non_Purchase',
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
                        title: 'IM_Budget_Non_Purchase',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                columnDefs: [{
                    targets: 0,
                    width: '28px',
                    className: 'dtr-control',
                    orderable: false
                }],
                ajax: {
                    url: "{{ route('imbudgetnonpurch.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.status = statusFilter ?? '';
                        d.mode = mode;
                        d.department_extra = deptFilter;
                    },
                    complete: function(xhr) {
                        if (mode === 'all') {
                            const response = xhr.responseJSON;
                            const departments = response?.departments || [];

                            const deptSelect = $('#filterDepartment');

                            deptSelect.empty();
                            deptSelect.append(`<option value="">All Department</option>`);

                            departments.forEach(function(dep) {
                                deptSelect.append(`<option value="${dep}">${dep}</option>`);
                            });

                            if (deptFilter) {
                                deptSelect.val(deptFilter);
                            }
                        }
                    }
                },
                order: [
                    [1, 'desc']
                ],
                columns: [{
                        data: null,
                        defaultContent: ''
                    },
                    {
                        data: 'imnonpurchaseid',
                        render: function(data, type, row) {
                            let showUrl = `/showimbudgetnonpurch/${row.eid}`;
                            let editUrl = `/editimbudgetnonpurch/${row.eid}`;

                            let viewCls =
                                'inline-flex items-center justify-center rounded-full p-2 ' +
                                'text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50';

                            let editCls =
                                'inline-flex justify-center items-center w-[150px] px-3 py-1.5 ' +
                                'text-sm font-semibold text-white rounded transition-colors ' +
                                'bg-yellow-500 hover:bg-yellow-700';

                            let defaultCls =
                                'inline-flex justify-center items-center w-[150px] px-3 py-1.5 ' +
                                'text-sm font-semibold text-white rounded transition-colors ' +
                                'bg-gray-600 hover:bg-gray-700';

                            const text = data || '-';

                            if (row.status === 'D' && row.created_by === currentUser) {
                                return `
                                    <div class="flex items-center gap-2 whitespace-nowrap">
                                        <a href="${editUrl}" class="${editCls}">
                                            ${text}
                                        </a>

                                        <a href="${showUrl}" target="_blank" class="${viewCls}" title="View">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </div>
                                `;
                            }

                            return `
                                <div class="flex items-center gap-2 whitespace-nowrap">
                                    <a href="${showUrl}" target="_blank" class="${defaultCls}">
                                        ${text}
                                    </a>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'imnonpurchasedate',
                        className: 'text-left whitespace-nowrap'
                    },
                    {
                        data: 'cpny_id',
                        className: 'text-center whitespace-nowrap'
                    },
                    {
                        data: 'department_id',
                        className: 'text-center whitespace-normal break-words'
                    },
                    {
                        data: 'user_peminta',
                        defaultContent: '-',
                        className: 'text-left whitespace-nowrap'
                    },
                    {
                        data: 'imnonpurchasetype',
                        defaultContent: '-',
                        className: 'text-left whitespace-nowrap'
                    },
                    {
                        data: 'imbudgetkeperluan',
                        defaultContent: '-',
                        className: 'text-left'
                    },
                    {
                        data: 'budget_from',
                        defaultContent: '-',
                        className: 'text-left whitespace-nowrap'
                    },
                    {
                        data: 'budget_to',
                        defaultContent: '-',
                        className: 'text-left whitespace-nowrap'
                    },
                    {
                        data: 'expenditure_type',
                        defaultContent: '-',
                        className: 'text-left whitespace-nowrap'
                    },
                    {
                        data: 'existing_budget',
                        className: 'text-right whitespace-nowrap',
                        render: function(data) {
                            return formatNumber(data);
                        }
                    },
                    {
                        data: 'request_budget',
                        className: 'text-right whitespace-nowrap',
                        render: function(data) {
                            return formatNumber(data);
                        }
                    },
                    {
                        data: 'over_budget',
                        className: 'text-right whitespace-nowrap',
                        render: function(data) {
                            return formatNumber(data);
                        }
                    },
                    {
                        data: 'status',
                        className: 'text-left whitespace-nowrap',
                        render: function(data) {
                            const map = {
                                'D': {
                                    t: 'Revise',
                                    c: 'bg-amber-200/60 text-amber-800 border border-amber-600/40'
                                },
                                'P': {
                                    t: 'On Progress',
                                    c: 'bg-orange-200/60 text-orange-800 border border-orange-600/40'
                                },
                                'C': {
                                    t: 'Completed',
                                    c: 'bg-green-200/60 text-green-800 border border-green-600/40'
                                },
                                'R': {
                                    t: 'Rejected',
                                    c: 'bg-red-200/60 text-red-800 border border-red-600/40'
                                },
                                'X': {
                                    t: 'Cancel',
                                    c: 'bg-red-200/60 text-red-800 border border-red-600/40'
                                }
                            };

                            const it = map[data] || {
                                t: data || '-',
                                c: 'bg-gray-200/60 text-gray-700 border border-gray-500/40'
                            };

                            return `<span class="w-32 inline-block ${it.c} font-semibold px-3 py-1.5 text-sm text-center rounded">${it.t}</span>`;
                        }
                    }
                ],
                searchDelay: 400,
                stateSave: true
            });

            $(document).off('click', '.status-filter').on('click', '.status-filter', function(e) {
                e.preventDefault();

                const selectedMode = $(this).data('mode') || 'normal';
                const selectedStatus = $(this).data('status');

                if (selectedMode === 'all') {
                    mode = 'all';
                    statusFilter = '';
                    deptFilter = '';

                    $('#pageTitle').text('IM Budget All List');
                    $('#createBtn').hide();
                    $('#allFilters').removeClass('hidden');
                } else {
                    mode = 'normal';
                    statusFilter = selectedStatus ?? '';

                    $('#pageTitle').text('IM Budget Non Purchase');
                    $('#createBtn').show();
                    $('#allFilters').addClass('hidden');
                }

                table.ajax.reload(null, true);
            });

            $('#filterDepartment').on('change', function() {
                deptFilter = this.value;
                table.ajax.reload();
            });

            $('#filterStatus').on('change', function() {
                statusFilter = this.value;
                table.ajax.reload();
            });
        });
    </script>
</x-app-layout>