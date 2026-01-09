<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'itemreq' ? 'HR' : '';
    @endphp

    <style>
        /* Active / Selected state */
        .status-filter.active .status-card {
            transform: scale(1.02);
        }

        .status-filter[data-status=""].active .status-card {
            background-color: rgb(254 215 170);
            border-color: rgb(194 65 12);
        }

        .status-filter[data-status="P"].active .status-card {
            background-color: rgb(191 219 254);
            border-color: rgb(29 78 216);
        }

        .status-filter[data-status="R"].active .status-card {
            background-color: rgb(254 202 202);
            border-color: rgb(185 28 28);
        }

        .status-filter[data-status="D"].active .status-card {
            background-color: rgb(229 231 235);
            border-color: rgb(31 41 55);
        }

        .status-filter[data-status="C"].active .status-card {
            background-color: rgb(187 247 208);
            border-color: rgb(21 128 61);
        }

        /* === DataTables Export Buttons (Cute Style) === */
        .dt-buttons {
            display: flex;
            gap: 8px;
            margin-right: 12px;
        }

        .dt-button {
            display: inline-flex !important;
            align-items: center;
            gap: 6px;
            padding: 6px 12px !important;
            border-radius: 9999px !important;
            border: 1px solid transparent !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            line-height: 1 !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
            transition: all .2s ease-in-out;
        }

        /* Excel */
        .dt-button.buttons-excel {
            background-color: #dcfce7 !important;
            /* green-100 */
            color: #166534 !important;
            /* green-800 */
            border-color: #86efac !important;
        }

        .dt-button.buttons-excel:hover {
            background-color: #bbf7d0 !important;
        }

        /* CSV */
        .dt-button.buttons-csv {
            background-color: #e0f2fe !important;
            /* sky-100 */
            color: #075985 !important;
            /* sky-800 */
            border-color: #7dd3fc !important;
        }

        .dt-button.buttons-csv:hover {
            background-color: #bae6fd !important;
        }

        /* Remove default DataTables button styles */
        .dt-button:focus,
        .dt-button:active {
            outline: none !important;
            box-shadow: none !important;
        }

        /* === Fix spacing between Length & Export buttons === */

        /* Make toolbar items flex-aligned */
        .dataTables_length,
        .dt-buttons,
        .dataTables_filter {
            display: flex;
            align-items: center;
        }


        /* ✅ Control gap manually */
        .dt-buttons {
            margin-left: 12px !important;
            /* ← adjust: 4–8px is perfect */
            margin-right: 0 !important;
        }
    </style>

    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">

        {{-- STATUS CARDS --}}
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">
            {{-- All --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter active group block h-full" data-status="">
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

            {{-- On Progress --}}
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

            {{-- Reject --}}
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

            {{-- Revise / Draft --}}
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

            {{-- Completed --}}
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

        {{-- TABLE --}}
        <div class="mt-6 grid">
            <style>
                table.dataTable {
                    width: 100% !important;
                }

                .dataTables_wrapper {
                    width: 100%;
                }

                #itemReqTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    align-items: center;
                }

                #itemReqTable_filter input {
                    width: auto;
                    min-width: 120px;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }

                #itemReqTable td {
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                #itemReqTable th,
                #itemReqTable td {
                    padding: 10px;
                    max-width: 280px;
                }

                #itemReqTable tbody tr:hover {
                    background-color: #8f8f8f11;
                    cursor: pointer;
                }
            </style>

            <div class="rounded-2xl bg-white dark:bg-gray-800">
                <div
                    class="flex flex-col items-start justify-between gap-4 border-b border-gray-200 p-4 sm:flex-row sm:items-center dark:border-gray-700">
                    <h1 class="text-xl font-extrabold text-gray-700 dark:text-white">Item Request</h1>

                    {{-- sesuaikan URL/route create --}}
                    <a href="{{ url('/createitemreq') }}"
                        class="inline-flex items-center rounded-xl bg-blue-600 px-6 py-2 text-base font-semibold text-white transition-colors duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <i class="fas fa-plus pr-2"></i>Create
                    </a>
                </div>

                <div class="overflow-x-auto p-6">
                    <table id="itemReqTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="w-32 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    IRID</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Date</th>
                                <th
                                    class="w-32 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Company</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Department</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Description</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    PIC</th>
                                <th
                                    class="w-32 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>
                </div>
            </div>

            <script>
                var currentUser = "{{ auth()->user()->username }}";

                $(document).ready(function() {
                    // default status filter: '' (All)
                    let statusFilter = '';

                    const table = $('#itemReqTable').DataTable({
                        processing: true,
                        serverSide: true,
                        deferRender: true,

                        pageLength: 10,
                        lengthMenu: [
                            [10, 25, 50, 100, 250, -1],
                            [10, 25, 50, 100, 250, 'All']
                        ],


                        // 🔥 ADD THIS
                        dom: '<"dt-toolbar"l B f>rtip',
                        buttons: [{
                                extend: 'excelHtml5',
                                text: '↓ Excel',
                                title: 'Purchase_Order',
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
                                title: 'Purchase_Order',
                                className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                                exportOptions: {
                                    columns: ':visible',
                                    modifier: {
                                        page: 'current'
                                    }
                                }
                            }
                        ],
                        // 🔥 END ADD

                        ajax: {
                            url: "{{ route('itemreq.json') }}",
                            type: "GET",
                            data: function(d) {
                                d.status = statusFilter ?? '';
                            }
                        },

                        order: [
                            [0, 'desc']
                        ],

                        columns: [
                            // IRID link + optional tracking button (kalau sudah ada endpoint)
                            {
                                data: 'irid',
                                render: function(data, type, row) {
                                    let url = `/showitemreq/${row.eid}`;
                                    let cls =
                                        'inline-flex justify-center items-center w-[140px] px-3 py-1.5 text-base leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-gray-500 hover:bg-gray-700';

                                    const text = data || '-';

                                    // jika status Draft (D) & milik current user -> edit
                                    if (row.status === 'D' && row.created_by === currentUser) {
                                        url = `/edititemreq/${row.eid}`;
                                        cls =
                                            'inline-flex justify-center items-center w-[140px] px-3 py-1.5 text-base leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-yellow-500 hover:bg-yellow-700';
                                    }

                                    return `
                                        <div class="flex items-center gap-2 whitespace-nowrap">
                                            <a href="${url}" class="${cls}">${text}</a>
                                            <!-- Aktifkan jika sudah ada tracking route untuk item request -->
                                            <!--
                                            <button type="button"
                                                class="tracking-btn inline-flex items-center justify-center rounded-full p-2 text-red-600 hover:text-red-700 hover:bg-red-50"
                                                data-id="${row.eid}" data-doc="${text}" aria-label="Tracking" title="Tracking">
                                                <i class="fa-solid fa-paper-plane"></i>
                                            </button>
                                            -->
                                        </div>
                                    `;
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
                                    const map = {
                                        'D': {
                                            t: 'Revise / Draft',
                                            c: 'bg-gray-300/30 text-gray-600'
                                        },
                                        'P': {
                                            t: 'On Progress',
                                            c: 'bg-blue-300/30 text-blue-600'
                                        },
                                        'C': {
                                            t: 'Completed',
                                            c: 'bg-green-300/30 text-green-600'
                                        },
                                        'R': {
                                            t: 'Rejected',
                                            c: 'bg-red-300/30 text-red-600'
                                        },
                                    };
                                    const it = map[data] || {
                                        t: data || '-',
                                        c: 'bg-gray-300/30 text-gray-600'
                                    };
                                    return `<span class="w-36 inline-block ${it.c} font-semibold px-3 py-1.5 text-base text-center rounded">${it.t}</span>`;
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
        </div>
    </div>
</x-app-layout>
