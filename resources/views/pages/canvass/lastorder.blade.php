<x-app-layout>
    {{-- Toastr --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <style>
        a.link-underline {
            color: #2563eb;
            /* biru (Tailwind indigo-600) */
            text-decoration-line: underline;
            text-decoration-color: #2563eb;
            /* warna garis underline */
            text-underline-offset: 3px;
            text-decoration-thickness: 1.5px;
        }

        a.link-underline:hover {
            color: #1d4ed8;
            /* biru lebih gelap (indigo-700) */
            text-decoration-color: #1d4ed8;
        }
    </style>
    <style>
        /* Paksa table mengikuti lebar container */
        table.dataTable {
            width: 100% !important;
        }

        /* Kolom Description (index ke-6 kalau dihitung dari 0 termasuk control column) */
        #invTable td:nth-child(7),
        #invTable th:nth-child(7),
        #bqTable td:nth-child(7),
        #bqTable th:nth-child(7) {
            white-space: normal !important;
            /* wrap */
            word-break: break-word !important;
            /* pecah kata panjang */
            overflow-wrap: anywhere !important;
            /* aman untuk string panjang */
            max-width: 420px;
            /* batasi supaya tidak melebar */
        }
    </style>




    <div class="max-w-9xl mx-auto w-full px-8 py-4 sm:px-6 lg:px-8">
        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Last Order</h1>

                    <div class="flex gap-2">
                        <button type="button" data-tab="inv"
                            class="tab-btn rounded-xl border border-gray-300 bg-gray-900 px-4 py-2 text-xs font-semibold text-white dark:border-gray-600">
                            Last Order Inventory
                        </button>
                        <button type="button" data-tab="bq"
                            class="tab-btn rounded-xl border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            Last Order BQ
                        </button>
                    </div>
                </div>
            </div>

            {{-- TAB Inventory --}}
            <div id="tab-inv" class="rounded-base relative overflow-x-auto p-4">
                <table id="invTable" class="text-body w-full text-left text-xs rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-xs">
                        <tr>
                            <th></th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                PONbr</th>
                            <th class="w-32 px-6 py-2 font-medium">
                                PO Date</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                CSID</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Vendor</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Inventory ID</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Description</th>
                            <th
                                class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Unit Cost</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Purchaser</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
            </div>

            {{-- TAB BQ --}}
            <div id="tab-bq" class="rounded-base relative hidden overflow-x-auto p-4">
                <table id="bqTable" class="text-body w-full text-left text-xs rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-xs">
                        <tr>
                            <th></th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                PONbr</th>
                            <th class="w-32 px-6 py-2 font-medium">
                                PO Date</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                CSID</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Vendor</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Inventory ID</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Description</th>
                            <th
                                class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Unit Cost</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Purchaser</th>
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

            const dtControlColumn = {
                data: null,
                className: 'dtr-control',
                orderable: false,
                searchable: false,
                defaultContent: ''
            };

            function columnsDef() {
                return [
                    dtControlColumn, {
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
                    {
                        data: 'vendorname'
                    },
                    {
                        data: 'inventoryid'
                    },
                    {
                        data: 'inventory_descr',
                        className: 'text-left',
                        render: function(data) {
                            if (!data) return '';
                            // bungkus supaya wrap lebih "nempel" dan aman dari CSS DataTables
                            return `<div class="whitespace-normal break-words">${data}</div>`;
                        }
                    },

                    {
                        data: 'unitcost',
                        className: 'text-right',
                        render: function(v) {
                            if (v === null || v === undefined || v === '') return '';
                            const n = Number(v);
                            if (isNaN(n)) return v;
                            return n.toLocaleString('id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    // { data: 'purchaser' },
                    {
                        data: 'purchaser',
                        className: 'text-center',
                        render: function(data) {
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
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],

                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'List_CS',
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
                        title: 'List_CS',
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

                ajax: {
                    url: "{{ route('lastorder.inventory.json') }}",
                    type: "GET"
                },
                order: [
                    [1, 'desc']
                ],
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
                ajax: {
                    url: "{{ route('lastorder.bq.json') }}",
                    type: "GET"
                },
                order: [
                    [1, 'desc']
                ],
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
