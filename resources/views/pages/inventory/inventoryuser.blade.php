<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'inventories' ? 'Inventories' : '';
    @endphp

    <x-spp-list-styles />

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="dt-pretty rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="Inventory List" subtitle="Browse stock and non-stock inventory items">
                {{-- TYPE FILTER (segmented) --}}
                <div class="inline-flex rounded-lg bg-gray-100 p-1 dark:bg-white/5">
                    <button type="button" data-type="STOCK"
                        class="typeFilterBtn whitespace-nowrap rounded-md bg-blue-600 px-4 py-1.5 text-sm font-semibold text-white shadow-sm transition">Stock</button>
                    <button type="button" data-type="NONSTOCK"
                        class="typeFilterBtn whitespace-nowrap rounded-md px-4 py-1.5 text-sm font-semibold text-gray-600 transition hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">NonStock</button>
                </div>

                {{-- COMPANY --}}
                <div class="sppb-f relative w-44">
                    <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg></span>
                    <select id="filter_cpny">
                        <option value="">Select Company</option>
                        @foreach ($cpnyIds as $cpny)
                            <option value="{{ $cpny }}">{{ $cpny }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- BU --}}
                <div class="sppb-f relative w-52">
                    <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg></span>
                    <select id="filter_bu">
                        <option value="">Select Business Unit</option>
                        @foreach ($buList as $bu)
                            <option value="{{ $bu->business_unit_id }}">{{ $bu->business_unit_id }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- HINT --}}
                <span id="hintStockOnly" class="hidden whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                    Company/BU filters only apply to Stock.
                </span>
            </x-list-page-header>

            <div class="relative overflow-hidden">
                <table id="inventoriesTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="px-4 py-3 text-left font-medium">Inventory ID</th>
                            <th class="px-4 py-3 text-left font-medium">Description</th>
                            <th class="px-4 py-3 text-left font-medium">UOM</th>
                            <th class="px-4 py-3 text-left font-medium">Stock</th>
                            {{-- <th class="px-4 py-3 text-left">Item Type</th> --}}
                            <th class="px-4 py-3 text-left font-medium">Item Class</th>
                            {{-- kolom stock tetap ada di DOM, nanti disembunyikan via JS saat NONSTOCK --}}

                            <th class="w-32 px-4 py-3 text-left font-medium">Status</th>
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

            $cpny.add($bu).select2({
                width: '100%',
                minimumResultsForSearch: 8,
                dropdownCssClass: 'sppb-s2-dropdown'
            });

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

                            let value = v;

                            if (typeof value === 'string') {
                                value = value.replace(/\./g, '').replace(',', '.');
                            }

                            const n = Number(value);

                            if (Number.isNaN(n)) {
                                return v;
                            }

                            return n.toLocaleString('id-ID', {
                                minimumFractionDigits: 0,
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
                        className: 'text-left',
                        render: function(s) {
                            return String(s || '').toUpperCase() === 'A' ?
                                '<span class="inline-flex min-w-[96px] items-center justify-center gap-2 rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Active</span>' :
                                '<span class="inline-flex min-w-[96px] items-center justify-center gap-2 rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30"><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Inactive</span>';
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
                    .removeClass('bg-blue-600 text-white shadow-sm')
                    .addClass('text-gray-600 dark:text-gray-300');

                $(this)
                    .removeClass('text-gray-600 dark:text-gray-300')
                    .addClass('bg-blue-600 text-white shadow-sm');

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
