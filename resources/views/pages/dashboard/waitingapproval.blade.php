<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-2 py-2 sm:px-6 lg:px-2">
        <div
            class="mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

            <div x-data="{ tab: 'waitingapp' }">

                <!-- TABS -->
                <div class="flex gap-4 border-b border-gray-100 px-5 py-3 dark:border-white/[0.06]">
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
                <div class="relative overflow-hidden" x-show="tab === 'waitingapp'" x-transition>
                    <table id="agendasTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-10 px-4 py-3"></th>
                                <th class="px-4 py-3 text-left font-medium">DocID</th>
                                <th class="px-4 py-3 text-left font-medium">Date</th>
                                <th class="px-4 py-3 text-left font-medium">Company</th>
                                <th class="px-4 py-3 text-left font-medium">Department</th>
                                <th class="px-4 py-3 text-left font-medium">Info</th>
                                <th class="px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- TAB 2 -->
                <div x-show="tab === 'approval'" x-transition>
                    @include('pages.dashboard.dashapproval')
                </div>

            </div>
        </div>
    </div>

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
                        width: '28px',
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
