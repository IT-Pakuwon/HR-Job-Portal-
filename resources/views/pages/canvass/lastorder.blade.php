<x-app-layout>
    {{-- Toastr --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Last Order</h1>

                    <div class="flex gap-2">
                        <button type="button" data-tab="inv"
                            class="tab-btn rounded-xl border border-gray-300 bg-gray-900 px-4 py-2 text-sm font-semibold text-white dark:border-gray-600">
                            Last Order Inventory
                        </button>
                        <button type="button" data-tab="bq"
                            class="tab-btn rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            Last Order BQ
                        </button>
                    </div>
                </div>
            </div>

            {{-- TAB Inventory --}}
            <div id="tab-inv" class="rounded-base relative overflow-x-auto p-4">
                <table id="invTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                PONbr</th>
                            <th class="w-32 px-6 py-2 font-medium">PO Date</th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                CSID</th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Vendor</th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Inventory ID</th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Description</th>
                            <th
                                class="px-6 py-3 text-right text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Unit Cost</th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Purchaser</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            {{-- TAB BQ --}}
            <div id="tab-bq" class="rounded-base relative hidden overflow-x-auto p-4">
                <table id="bqTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                BQID</th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                CSID</th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Vendor</th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Description</th>
                            <th
                                class="px-6 py-3 text-right text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Product Price</th>
                            <th
                                class="px-6 py-3 text-right text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Jasa Price</th>
                            <th
                                class="px-6 py-3 text-right text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Total</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>
    </div>

    <script>
        $(function() {
            function fmtDate(d) {
                if (!d) return '';
                const dt = new Date(d);
                return isNaN(dt) ? d : dt.toLocaleDateString('id-ID');
            }

            function fmtMoney(v) {
                if (v === null || v === undefined || v === '') return '';
                const n = Number(v);
                if (isNaN(n)) return v;
                return n.toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function poUrl(row) {
                return row.po_eid ? `/showpo/${row.po_eid}` : '#';
            }

            function csUrl(row) {
                return row.cs_eid ? `/showcs/${row.cs_eid}` : '#';
            }

            function bqUrl(row) {
                return row.bq_eid ? `/showbqcs/${row.bq_eid}` : '#';
            }



            const dtControlColumn = {
                data: null,
                width: '28px',
                className: 'dtr-control',
                orderable: false,
                searchable: false,
                defaultContent: ''
            };

            // ===== COLUMNS INVENTORY =====
            function invColumnsDef() {
                return [
                    dtControlColumn,
                    {
                        data: 'ponbr',
                        render: function(data, type, row) {
                            const url = poUrl(row);
                            if (url === '#') return data ?? '';
                            return `<a href="${url}" target="_blank" class="link-underline">${data ?? ''}</a>`;
                        }
                    },
                    {
                        data: 'podate',
                        className: 'text-center',
                        render: (d) => fmtDate(d)
                    },
                    {
                        data: 'csid',
                        render: function(data, type, row) {
                            const url = csUrl(row);
                            if (url === '#') return data ?? '';
                            return `<a href="${url}" target="_blank" class="link-underline">${data ?? ''}</a>`;
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
                            return `<div class="whitespace-normal break-words">${data}</div>`;
                        }
                    },
                    {
                        data: 'unitcost',
                        className: 'text-right',
                        render: (v) => fmtMoney(v)
                    },
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

            // ===== COLUMNS BQ =====
            function bqColumnsDef() {
                return [
                    dtControlColumn,
                    // { data: 'bqid', className: 'text-left' },
                    {
                        data: 'bqid',
                        render: function(data, type, row) {
                            const url = bqUrl(row);
                            if (url === '#') return data ?? '';
                            return `<a href="${url}" target="_blank" class="link-underline">${data ?? ''}</a>`;
                        }
                    },

                    {
                        data: 'csid',
                        render: function(data, type, row) {
                            const url = csUrl(row);
                            if (url === '#') return data ?? '';
                            return `<a href="${url}" target="_blank" class="link-underline">${data ?? ''}</a>`;
                        }
                    },
                    {
                        data: 'vendor_name',
                        className: 'text-left'
                    },
                    {
                        data: 'bq_descr',
                        className: 'text-left',
                        render: function(data) {
                            if (!data) return '';
                            return `<div class="whitespace-normal break-words">${data}</div>`;
                        }
                    },
                    {
                        data: 'product_price',
                        className: 'text-right',
                        render: (v) => fmtMoney(v)
                    },
                    {
                        data: 'jasa_price',
                        className: 'text-right',
                        render: (v) => fmtMoney(v)
                    },
                    {
                        data: 'total_price',
                        className: 'text-right',
                        render: (v) => fmtMoney(v)
                    },
                ];
            }

            // ===== INIT TABLES =====
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
                        title: 'LastOrder_Inventory',
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
                        title: 'LastOrder_Inventory',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    },
                ],
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
                    orderable: false
                }],
                ajax: {
                    url: "{{ route('lastorder.inventory.json') }}",
                    type: "GET"
                },
                order: [
                    [2, 'desc']
                ], // podate
                columns: invColumnsDef(),
                searchDelay: 400,
                stateSave: true
            });

            const bqTable = $('#bqTable').DataTable({
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
                        title: 'LastOrder_BQ',
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
                        title: 'LastOrder_BQ',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    },
                ],
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
                    orderable: false
                }],
                ajax: {
                    url: "{{ route('lastorder.bq.json') }}",
                    type: "GET"
                },
                order: [
                    [1, 'desc']
                ], // bqid
                columns: bqColumnsDef(),
                searchDelay: 400,
                stateSave: true
            });

            // ===== Tabs =====
            $('.tab-btn').on('click', function() {
                const tab = $(this).data('tab');

                $('.tab-btn').removeClass('bg-gray-900 text-white')
                    .addClass('bg-white dark:bg-gray-800 text-gray-700 dark:text-white');
                $(this).addClass('bg-gray-900 text-white')
                    .removeClass('bg-white dark:bg-gray-800 text-gray-700 dark:text-white');

                $('#tab-inv').toggleClass('hidden', tab !== 'inv');
                $('#tab-bq').toggleClass('hidden', tab !== 'bq');

                setTimeout(() => {
                    if (tab === 'inv') invTable.columns.adjust().draw(false);
                    else bqTable.columns.adjust().draw(false);
                }, 50);
            });
        });
    </script>
</x-app-layout>
