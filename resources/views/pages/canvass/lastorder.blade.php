<x-app-layout>
    <x-spp-list-styles />
    {{-- Toastr --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="dt-pretty rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="Last Order" subtitle="Most recent purchase price per item">
                <div class="inline-flex rounded-lg bg-gray-100 p-1 dark:bg-white/5">
                    <button type="button" data-tab="inv"
                        class="tab-btn rounded-md px-4 py-1.5 text-sm font-semibold transition">
                        Last Order Inventory
                    </button>
                    <button type="button" data-tab="bq"
                        class="tab-btn rounded-md px-4 py-1.5 text-sm font-semibold transition">
                        Last Order BQ
                    </button>
                </div>
            </x-list-page-header>

            {{-- TAB Inventory --}}
            <div id="tab-inv" class="relative overflow-hidden">
                <table id="invTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th class="px-4 py-3 text-left font-medium">
                                PONbr</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">PO Date</th>
                            <th class="px-4 py-3 text-left font-medium">
                                CSID</th>
                            <th class="px-4 py-3 text-left font-medium">
                                Vendor</th>
                            <th class="px-4 py-3 text-left font-medium">
                                Inventory ID</th>
                            <th class="px-4 py-3 text-left font-medium">
                                Description</th>
                            <th class="px-4 py-3 text-left font-medium">
                                Unit Cost</th>
                            <th class="px-4 py-3 text-left font-medium">
                                Purchaser</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            {{-- TAB BQ --}}
            <div id="tab-bq" class="relative hidden overflow-hidden">
                <div id="bqLoadError" role="alert" class="hidden mx-4 mt-3 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                    <span id="bqLoadErrorText"></span>
                    <button id="bqRetry" type="button" class="ml-2 font-semibold underline">Coba lagi</button>
                </div>
                <table id="bqTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th class="px-4 py-3 text-left font-medium">
                                BQID</th>
                            <th class="px-4 py-3 text-left font-medium">
                                CSID</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">PO Number</th>
                            <th class="px-4 py-3 text-left font-medium">PO Date</th>
                            <th class="px-4 py-3 text-left font-medium">
                                Vendor</th>
                            <th class="px-4 py-3 text-left font-medium">
                                Description</th>
                            <th class="px-4 py-3 text-left font-medium">
                                Product Price</th>
                            <th class="px-4 py-3 text-left font-medium">
                                Jasa Price</th>
                            <th class="px-4 py-3 text-left font-medium">
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
                            return `<div class="w-full whitespace-normal break-words text-left">${data}</div>`;
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
                        data: 'cpny_id',
                        className: 'text-left'
                    },
                    {
                        data: 'ponbr',
                        render: function(data, type, row) {
                            if (type !== 'display') return data ?? '';
                            const text = $('<span>').text(data ?? '').html();
                            const url = poUrl(row);
                            return url === '#' ? text : `<a href="${url}" target="_blank" rel="noopener" class="link-underline">${text}</a>`;
                        }
                    },
                    {
                        data: 'podate',
                        className: 'text-center',
                        render: (data, type) => type === 'display' ? fmtDate(data) : (data ?? '')
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
                            return `<div class="w-full whitespace-normal break-words text-left">${data}</div>`;
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
                    type: "GET",
                    timeout: 60000,
                    beforeSend: function() {
                        $('#bqLoadError').addClass('hidden');
                    },
                    error: function(xhr, status) {
                        $('#bqTable_processing').hide();
                        if (status === 'abort') return;
                        const message = status === 'timeout'
                            ? 'Data Last Order BQ belum selesai dimuat. Permintaan melewati batas waktu.'
                            : 'Data Last Order BQ gagal dimuat' + (xhr.status ? ' (HTTP ' + xhr.status + ')' : '') + '.';
                        $('#bqLoadErrorText').text(message);
                        $('#bqLoadError').removeClass('hidden');
                    }
                },
                order: [
                    [1, 'desc']
                ], // bqid
                columns: bqColumnsDef(),
                searchDelay: 400,
                stateSave: true
            });

            $('#bqRetry').on('click', function() {
                bqTable.ajax.reload(null, false);
            });

            // initial look of the tabs
            $('.tab-btn').addClass('text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white');
            $('.tab-btn[data-tab="inv"]').addClass('bg-blue-600 text-white shadow-sm').removeClass('text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white');

            // ===== Tabs =====
            $('.tab-btn').on('click', function() {
                const tab = $(this).data('tab');

                $('.tab-btn').removeClass('bg-blue-600 text-white shadow-sm')
                    .addClass('text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white');
                $(this).addClass('bg-blue-600 text-white shadow-sm')
                    .removeClass('text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white');

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
