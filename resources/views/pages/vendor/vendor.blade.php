<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'vendors' ? 'Vendors' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h2 class="text-base font-bold text-gray-800 dark:text-white">🏷️ Vendor List</h2>
                <button id="addVendorBtn"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    + Add Vendor
                </button>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="vendorsTable" class="text-body w-full text-left text-xs rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-xs">
                        <tr>
                            <th></th>
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
        </div>

        <!-- Modal -->
        <div id="vendorModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-2xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="vendorModalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add Vendor
                </h2>
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
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            let table = $('#vendorsTable').DataTable({
                ajax: "{{ route('vendors.json') }}",
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
                    },
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
                    {
                        data: 'vendor_id',
                        className: 'no-pointer'
                    },
                    {
                        data: 'vendor_name',
                        className: 'no-pointer'
                    },
                    {
                        data: 'email',
                        className: 'no-pointer'
                    },
                    {
                        data: 'contact_person',
                        className: 'no-pointer'
                    },
                    {
                        data: 'phone_number',
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
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
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
</x-app-layout>
