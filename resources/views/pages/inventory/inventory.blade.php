<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'inventories' ? 'Inventories' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="mb-8 sm:flex sm:items-center sm:justify-between"></div>

        <div class="grid">
            <style>
                .no-border { border: none !important; }
                .grid { width: 100%; }
                select, textarea, input { width: 100%; }
                table.dataTable { width: 100% !important; }
                .dataTables_wrapper { width: 100%; }
                @media (max-width: 600px) { .dataTables_wrapper { padding: 0 10px; } }

                #inventoriesTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    align-items: center;
                }
                #inventoriesTable_filter label { margin-right: 2px; }
                #inventoriesTable_filter input {
                    width: auto;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }

                #inventoriesTable_wrapper { margin-top: 20px; width: 100%; }
                #inventoriesTable td { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
                #inventoriesTable th, #inventoriesTable td { padding: 10px; max-width: 240px; }
                #inventoriesTable_length { width: auto; display: flex; justify-content: flex-start; }
                #inventoriesTable_length select { width: 80px; padding: 5px; }
                #inventoriesTable_info { margin-top: 10px; margin-bottom: 10px; }
                .dataTables_paginate { margin-top: 10px; margin-bottom: 10px; }
                #inventoriesTable tbody tr td { padding: 8px 8px; line-height: 1.6; }
                #inventoriesTable tbody tr:hover { background-color: #8f8f8f11; cursor: pointer; }

                /* switch */
                .switch { position: relative; display: inline-block; width: 40px; height: 22px; }
                .switch input { opacity: 0; width: 0; height: 0; }
                .slider {
                    position: absolute; cursor: pointer;
                    top: 0; left: 0; right: 0; bottom: 0;
                    background-color: #ccc; transition: .4s; border-radius: 34px;
                }
                .slider:before {
                    position: absolute; content: "";
                    height: 16px; width: 16px; left: 3px; bottom: 3px;
                    background-color: white; transition: .4s; border-radius: 50%;
                }
                input:checked + .slider { background-color: #4CAF50; }
                input:checked + .slider:before { transform: translateX(18px); }

                #inventoriesTable th:nth-child(1), #inventoriesTable td:nth-child(1) { width: 120px; text-align:center; }
                #inventoriesTable th:nth-child(6), #inventoriesTable td:nth-child(6) { width: 120px; text-align:center; }
            </style>

            <div class="mt-6 rounded-xl bg-white p-4 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">📦 Inventory List</h2>
                    <button id="addInventoryBtn" class="rounded-lg bg-indigo-500 px-5 py-2 text-white">
                        + Add Inventory
                    </button>
                </div>

                <table id="inventoriesTable" class="w-full table-fixed border-collapse">
                    <thead class="bg-white dark:bg-gray-700">
                        <tr>
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

            <!-- Modal -->
            <div id="inventoryModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
                <div class="relative w-full max-w-3xl rounded-lg bg-white p-6 dark:bg-gray-700">
                    <h2 id="inventoryModalTitle" class="mb-4 text-xl font-bold text-gray-800 dark:text-white">Add Inventory</h2>

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
                            <button type="submit"
                                    class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                $(document).ready(function() {
                    let table = $('#inventoriesTable').DataTable({
                        ajax: "{{ route('inventories.json') }}",
                        processing: true,
                        serverSide: false,
                        columns: [
                            {
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
                            { data: 'inventoryid',     className: 'no-pointer' },
                            { data: 'inventory_descr', className: 'no-pointer' },
                            { data: 'item_type',       className: 'no-pointer' },
                            { data: 'item_class',      className: 'no-pointer' },
                            {
                                data: 'status',
                                className: 'no-pointer',
                                render: function(data) {
                                    return data === 'A'
                                        ? '<span class="w-full max-w-25 bg-green-300/30 dark:bg-green-300 text-green-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Active</span>'
                                        : '<span class="w-full max-w-25 bg-red-300/30 dark:bg-red-300 text-red-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Inactive</span>';
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
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: { status: newStatus },
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
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
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
        </div>
    </div>
</x-app-layout>
