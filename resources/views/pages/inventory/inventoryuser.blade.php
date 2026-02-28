<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'inventories' ? 'Inventories' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-bold text-gray-800 dark:text-white">📦 Inventory List</h1>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-gray-700 dark:text-white">Filter:</span>

                <button type="button"
                    class="typeFilterBtn rounded-lg bg-gray-300 px-4 py-2 text-sm font-semibold text-gray-800"
                    data-type="">
                    All
                </button>

                <button type="button"
                    class="typeFilterBtn rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700"
                    data-type="STOCK">
                    Stock
                </button>

                <button type="button"
                    class="typeFilterBtn rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700"
                    data-type="NONSTOCK">
                    NonStock
                </button>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="inventoriesTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th class="px-4 py-3 text-left">Inventory ID</th>
                            <th class="px-4 py-3 text-left">Description</th>
                            <th class="px-4 py-3 text-left">Item Type</th>
                            <th class="px-4 py-3 text-left">Item Class</th>
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
            let currentFilter = '';

            let table = $('#inventoriesTable').DataTable({
                ajax: {
                    url: "{{ route('inventories-user.json') }}",
                    data: function(d) {
                        d.type_filter = currentFilter;
                    }
                },
                processing: true,
                serverSide: false,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
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
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'User',
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
                        title: 'User',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                columns: [{
                        data: 'inventoryid',
                        className: 'no-pointer'
                    },
                    {
                        data: 'inventory_descr',
                        className: 'no-pointer'
                    },
                    {
                        data: 'item_type',
                        className: 'no-pointer'
                    },
                    {
                        data: 'item_class',
                        className: 'no-pointer'
                    },
                    {
                        data: 'status',
                        className: 'no-pointer',
                        render: function(data) {
                            return data === 'A' ?
                                '<span class="w-full max-w-25 bg-green-300/30 dark:bg-green-300 text-green-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Active</span>' :
                                '<span class="w-full max-w-25 bg-red-300/30 dark:bg-red-300 text-red-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Inactive</span>';
                        }
                    }
                ]
            });



            $('.typeFilterBtn').on('click', function() {

                // ambil value filter
                currentFilter = $(this).data('type');

                // reset semua button style
                $('.typeFilterBtn')
                    .removeClass('bg-indigo-600 text-white')
                    .addClass('bg-gray-100 text-gray-700');

                // aktifkan button yg diklik
                $(this)
                    .removeClass('bg-gray-100 text-gray-700 bg-gray-300')
                    .addClass('bg-indigo-600 text-white');

                table.ajax.reload();
            });

            $('#closeInventoryModal').click(function() {
                $('#inventoryModal').addClass('hidden');
            });
        });
    </script>
</x-app-layout>
