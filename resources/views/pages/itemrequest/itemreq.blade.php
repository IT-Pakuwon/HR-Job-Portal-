<x-app-layout>
    <x-spp-list-styles />
    <x-spp-list-scripts />
    @php
        $currentPage = Route::currentRouteName() == 'itemreq' ? 'HR' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- STATUS CARDS --}}
        @php
            $statusCards = [
                [['data-status' => ''], 'All', '📦', 'all', $all, false],
                [['data-status' => 'P'], 'On Progress', '⏳', 'yellow', $onProgress, true],
                [['data-status' => 'R'], 'Reject', '⛔', 'red', $reject, false],
                [['data-status' => 'D'], 'Revise / Draft', '✏️', 'blue', $revise, false],
                [['data-status' => 'C'], 'Completed', '✅', 'green', $completed, false],
            ];
            if ($isAdmin ?? false) {
                $statusCards[] = [['data-status' => '', 'data-mode' => 'all'], 'Item Request All List', '📊', 'purple', $allListCount, false];
            }
        @endphp
        <x-status-filter-cards :custom="$statusCards" />

        <div class="dt-pretty mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="Item Request" :create-url="url('/createitemreq')" />

            <div class="relative overflow-hidden">
                <table id="itemReqTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th class="w-32 px-4 py-3 text-left font-medium">IRID</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">Date</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">Company</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">Department</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">Inventory Type</th>
                            <th class="px-4 py-3 text-left font-medium">Description</th>
                            <th class="px-4 py-3 text-left font-medium">PIC</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        var currentUser = "{{ auth()->user()->username }}";

        $(document).ready(function() {
            // default status filter: '' (All)
            let statusFilter = 'P';
            let modeFilter = 'normal';

            const table = $('#itemReqTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,

                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],



                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'List_ItemRequest',
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
                        title: 'List_ItemRequest',
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

                ajax: {
                    url: "{{ route('itemreq.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.status = statusFilter ?? '';
                        d.mode = modeFilter;
                    }
                },

                order: [
                    [1, 'desc']
                ],

                columns: [{
                        data: null,
                        defaultContent: ''
                    },
                    // IRID link + optional tracking button (kalau sudah ada endpoint)
                    {
                        data: 'irid',
                        render: function(data, type, row) {
                            return sppDocCell(row, data || '-', 'itemreq', currentUser, true);
                        }
                    },

                    {
                        data: 'irdate',
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
                        data: 'inventory_type',
                        defaultContent: '-',
                        className: 'text-left whitespace-normal break-words'
                    },

                    {
                        data: 'inventory_descr_req',
                        defaultContent: '-',
                        className: 'text-left'
                    },
                    {
                        data: 'pic_item_req',
                        defaultContent: '-',
                        className: 'text-left'
                    },

                    {
                        data: 'status',
                        className: 'text-left',
                        render: function(data) {
                            return sppStatusBadge(data, { D: 'Revise / Draft' });
                        }
                    }
                ],

                searchDelay: 400,
                stateSave: true,
                responsive: true
            });

            // status cards click
            $('.status-filter').on('click', function(e) {
                e.preventDefault();
                statusFilter = $(this).data('status') || '';
                modeFilter = $(this).data('mode') === 'all' ? 'all' : 'normal';
                table.ajax.reload(null, true);
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
