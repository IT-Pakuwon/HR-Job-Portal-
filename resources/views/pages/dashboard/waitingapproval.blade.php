<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-2 py-2 sm:px-6 lg:px-2">
        <div class="mt-2 rounded-xl bg-white p-4 dark:bg-gray-800">

            <div x-data="{ tab: 'waitingapp' }" class="mt-4">

                <!-- TABS -->
                <div class="mb-4 flex space-x-4">
                    <button @click="tab = 'waitingapp'"
                        :class="tab === 'waitingapp'
                            ?
                            'bg-indigo-600 text-white' :
                            'bg-gray-200 text-gray-700'"
                        class="rounded px-4 py-2 font-semibold">
                        📄 Waiting Approval
                    </button>

                    <button @click="tab = 'approval'"
                        :class="tab === 'approval'
                            ?
                            'bg-indigo-600 text-white' :
                            'bg-gray-200 text-gray-700'"
                        class="rounded px-4 py-2 font-semibold">
                        📅 Approval
                    </button>
                </div>

                <!-- TAB 1 -->
                <div x-show="tab === 'waitingapp'" x-transition>
                    <div class="overflow-x-auto">
                        <table id="agendasTable" class="w-full text-left text-xs">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>DocID</th>
                                    <th>Date</th>
                                    <th>Company</th>
                                    <th>Department</th>
                                    <th>Info</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2 -->
                <div x-show="tab === 'approval'" x-transition>
                    @include('pages.dashboard.dashapproval')
                </div>

            </div>
        </div>
    </div>

    <!-- ===================== -->
    <!-- DEPENDENCIES (WAJIB) -->
    <!-- ===================== -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables core -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    <!-- 🔥 RESPONSIVE EXTENSION (WAJIB FOR DTR) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <!-- Alpine -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <!-- ===================== -->
    <!-- DATATABLE INIT -->
    <!-- ===================== -->
    <script>
        let agendaTable;

        $(document).ready(function() {

            agendaTable = $('#agendasTable').DataTable({
                ajax: "{{ route('waitingapproval.json') }}",
                processing: true,
                serverSide: false,

                responsive: {
                    details: {
                        type: 'column',
                        target: 0
                    }
                },

                columnDefs: [{
                        targets: 0,
                        className: 'dtr-control',
                        orderable: false,
                        responsivePriority: 1
                    },
                    {
                        responsivePriority: 2,
                        targets: 1
                    },
                    {
                        responsivePriority: 3,
                        targets: -1
                    }
                ],

                order: [
                    [1, 'desc']
                ],

                columns: [{
                        data: null,
                        defaultContent: ''
                    },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            const url = `${window.location.origin}${row.url}/${row.id}`;
                            return `
                                <a href="${url}"
                                   class="inline-flex w-[120px] justify-center rounded bg-gray-600 px-3 py-1.5 text-white hover:bg-gray-800">
                                   ${row.docid}
                                </a>`;
                        }
                    },
                    {
                        data: 'docdate'
                    },
                    {
                        data: 'cpnyid'
                    },
                    {
                        data: 'departementid'
                    },
                    {
                        data: 'infohd'
                    },
                    {
                        data: 'status',
                        render: function(v) {
                            const map = {
                                D: ['Revise', 'gray'],
                                P: ['On Progress', 'blue'],
                                C: ['Completed', 'green'],
                                X: ['Cancel', 'red'],
                                R: ['Rejected', 'red'],
                            };
                            const [text, color] = map[v] || ['Unknown', 'gray'];
                            return `
                                <span class="inline-block w-28 rounded bg-${color}-300/30 px-3 py-1.5 font-semibold text-${color}-600">
                                    ${text}
                                </span>`;
                        }
                    }
                ]
            });
        });

        /* ===============================
           ALPINE x-show → RESPONSIVE FIX
        ================================ */
        document.addEventListener('alpine:init', () => {
            Alpine.effect(() => {
                const el = document.getElementById('agendasTable');
                if (!el) return;

                // table visible?
                if (el.offsetParent !== null && agendaTable) {
                    setTimeout(() => {
                        agendaTable.columns.adjust();
                        agendaTable.responsive.recalc();
                    }, 200);
                }
            });
        });
    </script>
</x-app-layout>
