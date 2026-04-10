<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'rfp' ? 'RFP' : '';
        $user = auth()->user();
        $hasRfpAllAccess = $user->user_role === 'admin';

        $xlCols = 5;
        if ($hasRfpAllAccess) {
            $xlCols++;
        }
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="xl:grid-cols-{{ $xlCols }} grid auto-rows-fr grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">

            <a href="#" class="status-filter group block h-full" data-status="">
                <div class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">📄</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">All</p>
                    </div>
                    <p class="shrink-0 text-base font-extrabold">{{ $all }}</p>
                </div>
            </a>

            <a href="#" class="status-filter group block h-full" data-status="P">
                <div class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">⏳</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">On Progress</p>
                    </div>
                    <p class="shrink-0 text-base font-extrabold">{{ $onProgress }}</p>
                </div>
            </a>

            <a href="#" class="status-filter group block h-full" data-status="R">
                <div class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">⛔️</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Reject</p>
                    </div>
                    <p class="shrink-0 text-base font-extrabold">{{ $reject }}</p>
                </div>
            </a>

            <a href="#" class="status-filter group block h-full" data-status="D">
                <div class="status-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">✏️</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Revise / Draft</p>
                    </div>
                    <p class="shrink-0 text-base font-extrabold">{{ $revise }}</p>
                </div>
            </a>

            <a href="#" class="status-filter group block h-full" data-status="C">
                <div class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">✅</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Completed</p>
                    </div>
                    <p class="shrink-0 text-base font-extrabold">{{ $completed }}</p>
                </div>
            </a>

            @if ($hasRfpAllAccess)
                <a href="#" class="status-filter group block h-full" data-scope="rfp_all">
                    <div class="status-card flex h-full items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-md active:scale-95">
                        <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">🌐</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">RFP All</p>
                        </div>
                        <p class="shrink-0 text-base font-extrabold">{{ $rfpAll ?? 0 }}</p>
                    </div>
                </a>
            @endif
        </div>

        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Request For Payment</h1>               
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="rfpTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th class="w-32 px-6 py-2 font-medium">RFP ID</th>
                            <th class="w-32 px-6 py-2 font-medium">Date</th>
                            <th class="w-32 px-6 py-2 font-medium">Company</th>
                            <th class="w-32 px-6 py-2 font-medium">Department</th>
                            <th class="w-32 px-6 py-2 font-medium">Vendor</th>
                            <th class="w-32 px-6 py-2 font-medium">Type PO</th>
                            <th class="w-32 px-6 py-2 font-medium">Description</th>
                            <th class="w-32 px-6 py-2 font-medium">Amount</th>
                            <th class="w-32 px-6 py-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let scopeFilter = '';
        var currentUser = "{{ auth()->user()->username }}";

        $(document).ready(function() {
            let statusFilter = 'P';

            const table = $('#rfpTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],
                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'List_RFP',
                        className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: { page: 'current' }
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '↓ CSV',
                        title: 'List_RFP',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: { page: 'current' }
                        }
                    }
                ],
                responsive: {
                    details: {
                        type: 'column',
                        target: 0
                    }
                },
                columnDefs: [
                    {
                        targets: 0,
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false
                    }
                ],
                ajax: {
                    url: "{{ route('rfp.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.status = statusFilter ?? '';
                        d.scope = scopeFilter ?? '';
                    }
                },
                order: [[2, 'desc']],
                columns: [
                    {
                        data: null,
                        defaultContent: ''
                    },
                    {
                        data: 'rfp_id',
                        render: function(data, type, row) {
                            let url = `/showrfp/${row.eid}`;
                            let cls = 'shrink-0 px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-700 text-sm';
                            const text = data || row.eid;

                            if (row.status === 'D' && row.created_by === currentUser) {
                                url = `/editrfp/${row.eid}`;
                                cls = 'shrink-0 px-3 py-1.5 bg-yellow-500 text-white rounded hover:bg-yellow-700 text-sm';
                            }

                            return `<a href="${url}" class="${cls}">${text}</a>`;
                        }
                    },
                    {
                        data: 'rfp_date',
                        className: 'text-left'
                    },
                    {
                        data: 'cpny_id',
                        className: 'text-center w-32'
                    },
                    {
                        data: 'department_id',
                        className: 'text-center whitespace-normal break-words'
                    },
                    {
                        data: 'vendor_name',
                        defaultContent: '-',
                        className: 'text-left'
                    },
                    {
                        data: 'type_po',
                        defaultContent: '-',
                        className: 'text-left'
                    },
                    {
                        data: 'keperluan',
                        defaultContent: '-',
                        className: 'text-left'
                    },
                    {
                        data: 'rfp_amount',
                        defaultContent: '0',
                        className: 'text-right',
                        render: function(data) {
                            let num = parseFloat(data || 0);
                            return num.toLocaleString('id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'status',
                        className: 'text-left',
                        render: function(data) {
                            const map = {
                                'D': { t: 'Revise / Draft', c: 'bg-amber-200/60 text-amber-800 border border-amber-600/40' },
                                'P': { t: 'On Progress', c: 'bg-orange-200/60 text-orange-800 border border-orange-600/40' },
                                'C': { t: 'Completed', c: 'bg-green-200/60 text-green-800 border border-green-600/40' },
                                'X': { t: 'Cancel', c: 'bg-red-200/60 text-red-800 border border-red-600/40' },
                                'R': { t: 'Rejected', c: 'bg-red-200/60 text-red-800 border border-red-600/40' },
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
                stateSave: true,
                responsive: true
            });

            $('.status-filter').on('click', function(e) {
                e.preventDefault();

                const status = $(this).data('status');
                const scope = $(this).data('scope');

                if (scope) {
                    scopeFilter = scope;
                    statusFilter = '';
                } else {
                    statusFilter = status ?? '';
                    scopeFilter = '';
                }

                table.ajax.reload(null, true);
            });

            document.querySelectorAll('.status-filter').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelectorAll('.status-filter').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });
    </script>
</x-app-layout>