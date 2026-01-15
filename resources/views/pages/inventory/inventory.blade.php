<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'inventories' ? 'Inventories' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-bold text-gray-800 dark:text-white">📦 Inventory List</h1>
                <button id="addInventoryBtn"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    + Add Inventory
                </button>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="inventoriesTable" class="text-body w-full text-left text-xs rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-xs">
                        <tr>
                            <th></th>
                            <th class="w-32 px-4 py-3 text-center">Actions</th>
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

        <!-- Modal -->
        <div id="inventoryModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-3xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="inventoryModalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add
                    Inventory</h2>

                <form id="inventoryForm">
                    <input type="hidden" id="id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Inventory ID</label>
                            <input type="text" id="inventoryid" name="inventoryid"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Stock Unit</label>
                            <input type="text" id="stock_unit" name="stock_unit"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Description</label>
                            <input type="text" id="inventory_descr" name="inventory_descr"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Purchase Unit</label>
                            <input type="text" id="purchase_unit" name="purchase_unit"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Item Type</label>
                            <input type="text" id="item_type" name="item_type"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Item Sub Type</label>
                            <input type="text" id="item_sub_type" name="item_sub_type"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Item Class</label>
                            <input type="text" id="item_class" name="item_class"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Item Sub Class</label>
                            <input type="text" id="item_sub_class" name="item_sub_class"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Item Category</label>
                            <input type="text" id="item_category" name="item_category"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeInventoryModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            let table = $('#inventoriesTable').DataTable({
                ajax: "{{ route('inventories.json') }}",
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
                        data: null,
                        defaultContent: ''
                    }, {
                        data: 'id',
                        render: function(data, type, row) {
                            return `
                                        <div class="flex justify-center space-x-2">
                                            <label class="switch">
                                                <input type="checkbox" class="toggleStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                                <span class="slider round"></span>
                                            </label>
                                            <button class="editInventoryBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    `;
                        }
                    },
                    {
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

            // Add
            $('#addInventoryBtn').click(function() {
                $('#inventoryModalTitle').text("Add Inventory");
                $('#inventoryForm')[0].reset();
                $('#id').val('');
                $('#inventoryModal').removeClass('hidden');
            });

            // Edit
            $(document).on('click', '.editInventoryBtn', function() {
                let id = $(this).data('id');
                $.get(`/inventories/${id}/edit`, function(i) {
                    $('#inventoryModalTitle').text("Edit Inventory");
                    $('#id').val(i.id);
                    $('#inventoryid').val(i.inventoryid);
                    $('#inventory_descr').val(i.inventory_descr);
                    $('#item_type').val(i.item_type);
                    $('#item_sub_type').val(i.item_sub_type);
                    $('#item_class').val(i.item_class);
                    $('#item_sub_class').val(i.item_sub_class);
                    $('#item_category').val(i.item_category);
                    $('#stock_unit').val(i.stock_unit);
                    $('#purchase_unit').val(i.purchase_unit);

                    $('#inventoryModal').removeClass('hidden');
                });
            });

            // Toggle status
            $(document).on('change', '.toggleStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/inventories/${id}/toggle-status`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        status: newStatus
                    },
                    success: function() {
                        table.ajax.reload(null, false);
                    }
                });
            });

            // Submit (create / update)
            $('#inventoryForm').submit(function(e) {
                e.preventDefault();
                let id = $('#id').val();
                let url = id ? `/inventories/${id}` : "{{ route('inventories.store') }}";
                let formData = new FormData(document.getElementById('inventoryForm'));

                if (id) formData.append('_method', 'PUT');

                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        $('#inventoryModal').addClass('hidden');
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Gagal menyimpan data inventory');
                    }
                });
            });

            $('#closeInventoryModal').click(function() {
                $('#inventoryModal').addClass('hidden');
            });
        });
    </script>
</x-app-layout>
