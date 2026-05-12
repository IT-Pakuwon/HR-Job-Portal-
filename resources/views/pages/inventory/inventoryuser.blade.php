<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'inventories' ? 'Inventories' : '';
    @endphp

    <style>
        .dt-toolbar {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .dt-toolbar .dataTables_length,
        .dt-toolbar .dataTables_filter {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dt-toolbar .dataTables_filter input {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 6px 10px;
        }

        .dt-toolbar .dataTables_length select {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 6px 10px;
        }
    </style>

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row flex-wrap items-center justify-between gap-4">

                <!-- LEFT: TITLE -->
                <h1 class="whitespace-nowrap text-base font-bold text-gray-800 dark:text-white">
                    📦 Inventory List
                </h1>

                <!-- RIGHT: ALL CONTROLS -->
                <div class="flex flex-wrap items-center gap-3">

                    <!-- TYPE FILTER -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-gray-700 dark:text-white">Filter:</span>

                        <button type="button"
                            class="typeFilterBtn whitespace-nowrap rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"
                            data-type="STOCK">
                            Stock
                        </button>

                        <button type="button"
                            class="typeFilterBtn whitespace-nowrap rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700"
                            data-type="NONSTOCK">
                            NonStock
                        </button>
                    </div>

                    <!-- COMPANY -->
                    <select id="filter_cpny" class="rounded-lg border px-3 py-2 text-sm">
                        <option value="">Select Company</option>
                        @foreach ($cpnyIds as $cpny)
                            <option value="{{ $cpny }}">{{ $cpny }}</option>
                        @endforeach
                    </select>

                    <!-- BU -->
                    <select id="filter_bu" class="rounded-lg border px-3 py-2 text-sm">
                        <option value="">Select Business Unit</option>
                        @foreach ($buList as $bu)
                            <option value="{{ $bu->business_unit_id }}">{{ $bu->business_unit_id }}</option>
                        @endforeach
                    </select>

                    <!-- HINT -->
                    <span id="hintStockOnly" class="hidden whitespace-nowrap text-xs text-gray-500">
                        Filter Company/BU hanya dipakai saat tab Stock.
                    </span>

                </div>
            </div>
            <div class="rounded-base relative overflow-x-auto">
                <table id="inventoriesTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th class="px-4 py-3 text-left">Inventory ID</th>
                            <th class="px-4 py-3 text-left">Description</th>
                            <th class="px-4 py-3 text-left">UOM</th>
                            <th class="px-4 py-3 text-left">Stock</th>
                            {{-- <th class="px-4 py-3 text-left">Item Type</th> --}}
                            <th class="px-4 py-3 text-left">Item Class</th>
                            {{-- kolom stock tetap ada di DOM, nanti disembunyikan via JS saat NONSTOCK --}}

                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>
    </div>

    <script>
        $(document).ready(function() {
            let currentFilter = 'STOCK'; // default STOCK (karena ALL dihapus)

            const $cpny = $('#filter_cpny');
            const $bu = $('#filter_bu');

            function isStockMode() {
                return currentFilter === 'STOCK';
            }

            let table = $('#inventoriesTable').DataTable({
                ajax: {
                    url: "{{ route('inventories-user.json') }}",
                    data: function(d) {
                        d.type_filter = currentFilter;

                        // kirim cpny/bu hanya saat STOCK
                        if (isStockMode()) {
                            d.cpny_id = $cpny.val();
                            d.business_unit_id = $bu.val();
                        } else {
                            d.cpny_id = '';
                            d.business_unit_id = '';
                        }
                    }
                },
                processing: true,
                serverSide: false,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],
                dom: '<"dt-toolbar"lBf>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'Inventory',
                        className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700'
                    },
                    {
                        extend: 'csvHtml5',
                        text: '↓ CSV',
                        title: 'Inventory',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700'
                    }
                ],
                columns: [{
                        data: 'inventoryid'
                    },
                    {
                        data: 'inventory_descr'
                    },
                    // ✅ Stock column
                    {
                        data: 'stock_unit',

                    },

                    // ✅ Stock column
                    {
                        data: 'stock',
                        render: function(v) {

                            if (v === null || v === undefined || v === '') {
                                return '0';
                            }

                            const n = Number(v);

                            if (Number.isNaN(n)) {
                                return v;
                            }

                           return n.toLocaleString('id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },

                    // {
                    //     data: 'item_type'
                    // },
                    {
                        data: 'item_class'
                    },


                    {
                        data: 'status',
                        className: 'text-center',
                        render: function(s) {
                            return String(s || '').toUpperCase() === 'A' ?
                                '<span class="w-full max-w-25 bg-green-300/30 text-green-600 pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Active</span>' :
                                '<span class="w-full max-w-25 bg-red-300/30 text-red-600 pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Inactive</span>';
                        }
                    }
                ],
                initComplete: function() {
                    applyStockColumnVisibility();
                }
            });

            function applyStockColumnVisibility() {
                // index kolom stock = 4 (0-based)
                const show = isStockMode();
                table.column(4).visible(show);

                // hint + disable filter cpny/bu kalau nonstock
                $('#hintStockOnly').toggleClass('hidden', show);
                $cpny.prop('disabled', !show);
                $bu.prop('disabled', !show);
            }

            // tombol filter
            $('.typeFilterBtn').on('click', function() {
                currentFilter = $(this).data('type');

                $('.typeFilterBtn')
                    .removeClass('bg-indigo-600 text-white')
                    .addClass('bg-gray-100 text-gray-700');

                $(this)
                    .removeClass('bg-gray-100 text-gray-700')
                    .addClass('bg-indigo-600 text-white');

                applyStockColumnVisibility();
                table.ajax.reload();
            });

            // reload kalau cpny/bu berubah (hanya relevan saat stock)
            $('#filter_cpny, #filter_bu').on('change', function() {
                if (isStockMode()) table.ajax.reload();
            });
        });
    </script>
</x-app-layout>
