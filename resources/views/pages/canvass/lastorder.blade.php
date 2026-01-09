<x-app-layout>
    {{-- Toastr --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <style>
        table.dataTable { width: 100% !important; }
        .dataTables_wrapper { width: 100%; }

        #invTable_filter, #bqTable_filter {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }
        #invTable_filter input, #bqTable_filter input { width: 220px; }

        #invTable td, #bqTable td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding: 10px;
            max-width: 240px;
        }
        #invTable tbody tr:hover, #bqTable tbody tr:hover {
            background-color: #8f8f8f11;
            cursor: pointer;
        }
        .link-underline {
            text-decoration: underline;
            color: #2563eb; /* blue-600 */
        }
        .link-underline:hover { color: #1d4ed8; }
    </style>
    <style>
        /* Default DataTables sering nowrap. Kita biarkan nowrap utk kolom lain */
        #invTable td, #bqTable td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: top;
        }

        /* ✅ Vendor (kolom ke-4) dan Description (kolom ke-6) dibuat WRAP */
        #invTable td:nth-child(4),
        #invTable td:nth-child(6),
        #bqTable td:nth-child(4),
        #bqTable td:nth-child(6) {
            white-space: normal !important;   /* boleh turun baris */
            overflow: visible !important;     /* jangan potong */
            text-overflow: unset !important;  /* hilangkan ... */
            word-break: break-word;           /* pecah kata panjang */
            overflow-wrap: anywhere;          /* lebih aman utk string tanpa spasi */
            line-height: 1.35;
        }

        /* Optional: kasih lebar minimum biar terlihat rapi */
        #invTable th:nth-child(4), #invTable td:nth-child(4),
        #bqTable th:nth-child(4),  #bqTable td:nth-child(4) {
            min-width: 220px;
        }

        #invTable th:nth-child(6), #invTable td:nth-child(6),
        #bqTable th:nth-child(6),  #bqTable td:nth-child(6) {
            min-width: 280px;
        }
    </style>


    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <h1 class="text-xl font-extrabold text-gray-700 dark:text-white">Last Order</h1>

                    <div class="flex gap-2">
                        <button type="button" data-tab="inv"
                            class="tab-btn rounded-xl px-4 py-2 text-sm font-semibold border border-gray-300 dark:border-gray-600 bg-gray-900 text-white">
                            Last Order Inventory
                        </button>
                        <button type="button" data-tab="bq"
                            class="tab-btn rounded-xl px-4 py-2 text-sm font-semibold border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-white">
                            Last Order BQ
                        </button>
                    </div>
                </div>
            </div>

            {{-- TAB Inventory --}}
            <div id="tab-inv" class="p-4">
                <div class="overflow-x-auto">
                    <table id="invTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">PONbr</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">PO Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">CSID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Vendor</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Inventory ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Description</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Unit Cost</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Purchaser</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>
                </div>
            </div>

            {{-- TAB BQ --}}
            <div id="tab-bq" class="p-4 hidden">
                <div class="overflow-x-auto">
                    <table id="bqTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">PONbr</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">PO Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">CSID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Vendor</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Inventory ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Description</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Unit Cost</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Purchaser</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function() {
            let activeTab = 'inv';

            // NOTE: ganti route show sesuai route Anda yang sudah ada
            function poUrl(row) {
                // jika po_eid null, fallback '#' (tidak error)
                return row.po_eid ? `/showpo/${row.po_eid}` : '#';
            }
            function csUrl(row) {
                return row.cs_eid ? `/showcs/${row.cs_eid}` : '#';
            }

            function columnsDef() {
                return [
                    {
                        data: 'ponbr',
                        render: function(data, type, row) {
                            const url = poUrl(row);
                            if (url === '#') return data ?? '';
                            return `<a href="${url}" class="link-underline">${data ?? ''}</a>`;
                        }
                    },
                    {
                        data: 'podate',
                        className: 'text-center',
                        render: function(d) {
                            if (!d) return '';
                            const dt = new Date(d);
                            return isNaN(dt) ? d : dt.toLocaleDateString('id-ID');
                        }
                    },
                    {
                        data: 'csid',
                        render: function(data, type, row) {
                            const url = csUrl(row);
                            if (url === '#') return data ?? '';
                            return `<a href="${url}" class="link-underline">${data ?? ''}</a>`;
                        }
                    },
                    { data: 'vendorname' },
                    { data: 'inventoryid' },
                    { data: 'inventory_descr' },
                    {
                        data: 'unitcost',
                        className: 'text-right',
                        render: function(v) {
                            if (v === null || v === undefined || v === '') return '';
                            const n = Number(v);
                            if (isNaN(n)) return v;
                            return n.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }
                    },
                    // { data: 'purchaser' },
                    {
                        data: 'purchaser',
                        className: 'text-center',
                        render: function (data) {
                            if (!data) return '';
                            return String(data).toUpperCase();
                        }
                    },

                ];
            }

            const invTable = $('#invTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100, 250],
                ajax: { url: "{{ route('lastorder.inventory.json') }}", type: "GET" },
                order: [[1,'desc']],
                columns: columnsDef(),
                searchDelay: 400,
                responsive: true,
                stateSave: true
            });

            const bqTable = $('#bqTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100, 250],
                ajax: { url: "{{ route('lastorder.bq.json') }}", type: "GET" },
                order: [[1,'desc']],
                columns: columnsDef(),
                searchDelay: 400,
                responsive: true,
                stateSave: true
            });

            // Tabs
            $('.tab-btn').on('click', function() {
                const tab = $(this).data('tab');
                activeTab = tab;

                $('.tab-btn').removeClass('bg-gray-900 text-white')
                    .addClass('bg-white dark:bg-gray-800 text-gray-700 dark:text-white');

                $(this).addClass('bg-gray-900 text-white')
                    .removeClass('bg-white dark:bg-gray-800 text-gray-700 dark:text-white');

                $('#tab-inv').toggleClass('hidden', tab !== 'inv');
                $('#tab-bq').toggleClass('hidden', tab !== 'bq');

                // adjust columns (datatable in hidden tab often needs this)
                setTimeout(() => {
                    if (tab === 'inv') invTable.columns.adjust().draw(false);
                    else bqTable.columns.adjust().draw(false);
                }, 50);
            });
        });
    </script>
</x-app-layout>
