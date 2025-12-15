<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'vendors' ? 'Vendors' : '';
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

                #vendorsTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    align-items: center;
                }
                #vendorsTable_filter label { margin-right: 2px; }
                #vendorsTable_filter input {
                    width: auto;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }
                #vendorsTable_wrapper { margin-top: 20px; width: 100%; }
                #vendorsTable td { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
                #vendorsTable th, #vendorsTable td { padding: 10px; max-width: 240px; }
                #vendorsTable_length { width: auto; display: flex; justify-content: flex-start; }
                #vendorsTable_length select { width: 80px; padding: 5px; }
                #vendorsTable_info { margin-top: 10px; margin-bottom: 10px; }
                .dataTables_paginate { margin-top: 10px; margin-bottom: 10px; }
                #vendorsTable tbody tr td { padding: 8px 8px; line-height: 1.6; }
                #vendorsTable tbody tr { transition: background-color 0.3s ease, color 0.3s ease; }
                #vendorsTable tbody tr:hover { background-color: #8f8f8f11; cursor: pointer; }

                /* switch status */
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

                #vendorsTable th:nth-child(1), #vendorsTable td:nth-child(1) { width: 120px; text-align: center; }
                #vendorsTable th:nth-child(7), #vendorsTable td:nth-child(7) { width: 120px; text-align: center; }
            </style>

            <div class="mt-6 rounded-xl bg-white p-4 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">🏷️ Vendor List</h2>
                    <button id="addVendorBtn" class="rounded-lg bg-indigo-500 px-5 py-2 text-white">
                        + Add Vendor
                    </button>
                </div>

                <table id="vendorsTable" class="w-full table-fixed border-collapse">
                    <thead class="bg-white dark:bg-gray-700">
                        <tr>
                            <th class="w-32 px-4 py-3 text-center">Actions</th>
                            <th class="px-4 py-3 text-left">Vendor ID</th>
                            <th class="px-4 py-3 text-left">Vendor Name</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-left">Contact Person</th>
                            <th class="px-4 py-3 text-left">Phone</th>
                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Modal -->
            <div id="vendorModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
                <div class="relative w-full max-w-2xl rounded-lg bg-white p-6 dark:bg-gray-700">
                    <h2 id="vendorModalTitle" class="mb-4 text-xl font-bold text-gray-800 dark:text-white">Add Vendor</h2>
                    <form id="vendorForm">
                        <input type="hidden" id="id" name="id">

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Vendor ID</label>
                                <input type="text" id="vendor_id" name="vendor_id"
                                       class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                            </div>

                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Vendor Name</label>
                                <input type="text" id="vendor_name" name="vendor_name"
                                       class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                            </div>

                            <div class="mb-3 md:col-span-2">
                                <label class="block text-gray-700 dark:text-white">Address Line 1</label>
                                <input type="text" id="vendor_addr1" name="vendor_addr1"
                                       class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>

                            <div class="mb-3 md:col-span-2">
                                <label class="block text-gray-700 dark:text-white">Address Line 2</label>
                                <input type="text" id="vendor_addr2" name="vendor_addr2"
                                       class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>

                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Email</label>
                                <input type="email" id="email" name="email"
                                       class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>

                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Contact Person</label>
                                <input type="text" id="contact_person" name="contact_person"
                                       class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>

                            <div class="mb-3 md:col-span-2">
                                <label class="block text-gray-700 dark:text-white">Phone Number</label>
                                <input type="text" id="phone_number" name="phone_number"
                                       class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end space-x-2">
                            <button type="button" id="closeVendorModal"
                                    class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                            <button type="submit"
                                    class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                $(document).ready(function() {
                    let table = $('#vendorsTable').DataTable({
                        ajax: "{{ route('vendors.json') }}",
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
                                            <button class="editVendorBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    `;
                                }
                            },
                            { data: 'vendor_id',      className: 'no-pointer' },
                            { data: 'vendor_name',    className: 'no-pointer' },
                            { data: 'email',          className: 'no-pointer' },
                            { data: 'contact_person', className: 'no-pointer' },
                            { data: 'phone_number',   className: 'no-pointer' },
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
                    $('#addVendorBtn').click(function() {
                        $('#vendorModalTitle').text("Add Vendor");
                        $('#vendorForm')[0].reset();
                        $('#id').val('');
                        $('#vendorModal').removeClass('hidden');
                    });

                    // Edit
                    $(document).on('click', '.editVendorBtn', function() {
                        let id = $(this).data('id');
                        $.get(`/vendors/${id}/edit`, function(v) {
                            $('#vendorModalTitle').text("Edit Vendor");
                            $('#id').val(v.id);
                            $('#vendor_id').val(v.vendor_id);
                            $('#vendor_name').val(v.vendor_name);
                            $('#vendor_addr1').val(v.vendor_addr1);
                            $('#vendor_addr2').val(v.vendor_addr2);
                            $('#email').val(v.email);
                            $('#contact_person').val(v.contact_person);
                            $('#phone_number').val(v.phone_number);
                            $('#vendorModal').removeClass('hidden');
                        });
                    });

                    // Toggle status
                    $(document).on('change', '.toggleStatus', function() {
                        let id = $(this).data('id');
                        let newStatus = $(this).is(':checked') ? 'A' : 'X';

                        $.ajax({
                            url: `/vendors/${id}/toggle-status`,
                            type: 'PUT',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: { status: newStatus },
                            success: function() {
                                table.ajax.reload(null, false);
                            }
                        });
                    });

                    // Submit form (create / update)
                    $('#vendorForm').submit(function(e) {
                        e.preventDefault();
                        let id = $('#id').val();
                        let url = id ? `/vendors/${id}` : "{{ route('vendors.store') }}";
                        let method = 'POST';
                        let formData = new FormData(document.getElementById('vendorForm'));

                        if (id) formData.append('_method', 'PUT');

                        $.ajax({
                            url: url,
                            type: method,
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function() {
                                $('#vendorModal').addClass('hidden');
                                table.ajax.reload();
                            },
                            error: function(xhr) {
                                console.error(xhr.responseText);
                                alert('Gagal menyimpan data vendor');
                            }
                        });
                    });

                    $('#closeVendorModal').click(function() {
                        $('#vendorModal').addClass('hidden');
                    });
                });
            </script>
        </div>
    </div>
</x-app-layout>
