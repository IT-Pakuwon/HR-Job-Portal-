<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'tenants' ? 'Tenants' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="mt-6 flex flex-col gap-6 rounded-xl bg-white p-6 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">🏬 Tenant List</h1>
                <button id="addTenantBtn"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-base font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    + Add Tenant
                </button>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="tenantsTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th class="w-32 px-4 py-3 text-center">Actions</th>
                            <th class="px-4 py-3 text-left">Unit ID</th>
                            <th class="px-4 py-3 text-left">Company</th>
                            <th class="px-4 py-3 text-left">Store Name</th>
                            <th class="px-4 py-3 text-left">Floor</th>
                            <th class="px-4 py-3 text-left">Store No</th>
                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <div id="tenantModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-2xl rounded-lg bg-white p-6 dark:bg-gray-700">
                <h2 id="modalTitle" class="mb-4 text-xl font-bold text-gray-800 dark:text-white">Add Tenant</h2>
                <form id="tenantForm">
                    <input type="hidden" id="id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-gray-700 dark:text-white">Unit ID</label>
                            <input type="text" id="unit_id" name="unit_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Company ID</label>
                            <input type="text" id="cpny_id" name="cpny_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Store Name</label>
                            <input type="text" id="store_name" name="store_name"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">Floor ID</label>
                            <input type="text" id="floor_id" name="floor_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Store No</label>
                            <input type="text" id="store_no" name="store_no"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            let table = $('#tenantsTable').DataTable({
                ajax: "{{ route('tenants.json') }}",
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
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                        <div class="flex justify-center space-x-2">
                                            <label class="switch">
                                                <input type="checkbox" class="toggleStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                                <span class="slider round"></span>
                                            </label>
                                            <button class="editTenantBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    `;
                        }
                    },
                    {
                        data: 'unit_id'
                    },
                    {
                        data: 'cpny_id'
                    },
                    {
                        data: 'store_name'
                    },
                    {
                        data: 'floor_id',
                        render: d => d ?? '-'
                    },
                    {
                        data: 'store_no',
                        render: d => d ?? '-'
                    },
                    {
                        data: 'status',
                        render: function(data) {
                            return data === 'A' ?
                                '<span class="bg-green-300/30 text-green-600 font-semibold px-4 py-2 text-center rounded">Active</span>' :
                                '<span class="bg-red-300/30 text-red-600 font-semibold px-4 py-2 text-center rounded">Inactive</span>';
                        }
                    }
                ]
            });

            // Add
            $('#addTenantBtn').click(function() {
                $('#modalTitle').text("Add Tenant");
                $('#tenantForm')[0].reset();
                $('#id').val('');
                $('#tenantModal').removeClass('hidden').addClass('flex');
            });

            // Edit
            $(document).on('click', '.editTenantBtn', function() {
                let id = $(this).data('id');
                $.get(`/tenants/${id}/edit`, function(t) {
                    $('#modalTitle').text("Edit Tenant");
                    $('#id').val(t.id);
                    $('#unit_id').val(t.unit_id);
                    $('#cpny_id').val(t.cpny_id);
                    $('#store_name').val(t.store_name);
                    $('#floor_id').val(t.floor_id);
                    $('#store_no').val(t.store_no);

                    $('#tenantModal').removeClass('hidden').addClass('flex');
                });
            });

            // Toggle status
            $(document).on('change', '.toggleStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/tenants/${id}/toggle-status`,
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
            $('#tenantForm').submit(function(e) {
                e.preventDefault();
                let id = $('#id').val();
                let url = id ? `/tenants/${id}` : "{{ route('tenants.store') }}";
                let formData = new FormData(document.getElementById('tenantForm'));

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
                        $('#tenantModal').addClass('hidden').removeClass('flex');
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Gagal menyimpan data tenant');
                    }
                });
            });

            $('#closeModal').click(function() {
                $('#tenantModal').addClass('hidden').removeClass('flex');
            });
        });
    </script>
</x-app-layout>
