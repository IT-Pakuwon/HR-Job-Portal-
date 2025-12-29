<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'roles' ? 'Roles' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="mb-8 sm:flex sm:items-center sm:justify-between"></div>

        <div class="grid">
            <style>
                .grid {
                    width: 100%;
                }

                table.dataTable {
                    width: 100% !important;
                }

                .dataTables_wrapper {
                    width: 100%;
                }

                #rolesTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    align-items: center;
                }

                #rolesTable_filter input {
                    width: auto;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }

                #rolesTable_length {
                    display: flex;
                    justify-content: flex-start;
                }

                #rolesTable_length select {
                    width: 80px;
                    padding: 5px;
                }

                #rolesTable tbody tr:hover {
                    background-color: #8f8f8f11;
                    cursor: pointer;
                }

                /* switch status */
                .switch {
                    position: relative;
                    display: inline-block;
                    width: 40px;
                    height: 22px;
                }

                .switch input {
                    opacity: 0;
                    width: 0;
                    height: 0;
                }

                .slider {
                    position: absolute;
                    cursor: pointer;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background-color: #ccc;
                    transition: .4s;
                    border-radius: 34px;
                }

                .slider:before {
                    position: absolute;
                    content: "";
                    height: 16px;
                    width: 16px;
                    left: 3px;
                    bottom: 3px;
                    background-color: white;
                    transition: .4s;
                    border-radius: 50%;
                }

                input:checked+.slider {
                    background-color: #4CAF50;
                }

                input:checked+.slider:before {
                    transform: translateX(18px);
                }
            </style>

            <div class="mt-6 rounded-xl bg-white p-4 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">🔑 Sys Role List</h2>
                    <button id="addRoleBtn" class="rounded-lg bg-indigo-500 px-5 py-2 text-white">
                        + Add Role
                    </button>
                </div>

                <table id="rolesTable" class="w-full table-fixed border-collapse">
                    <thead class="bg-white dark:bg-gray-700">
                        <tr>
                            <th class="w-32 px-4 py-3 text-center">Actions</th>
                            <th class="px-4 py-3 text-left">Role ID</th>
                            <th class="px-4 py-3 text-left">Role Name</th>
                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            {{-- Modal --}}
            <div id="roleModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
                <div class="relative w-full max-w-lg rounded-lg bg-white p-6 dark:bg-gray-700">
                    <h2 id="roleModalTitle" class="mb-4 text-xl font-bold text-gray-800 dark:text-white">
                        Add Role
                    </h2>
                    <form id="roleForm">
                        @csrf
                        <input type="hidden" id="id" name="id">
                        <div class="mb-4 grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 dark:text-white">Role ID</label>
                                <input type="text" id="role_id" name="role_id"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                            </div>

                            <div>
                                <label class="block text-gray-700 dark:text-white">Role Name</label>
                                <input type="text" id="role_name" name="role_name"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" id="closeRoleModal"
                                class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                            <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                $(document).ready(function() {
                    let table = $('#rolesTable').DataTable({
                        ajax: {
                            url: "{{ route('roles.json') }}",
                            type: "GET",
                            dataSrc: 'data'
                        },
                        processing: true,
                        serverSide: false,
                        columns: [{
                                data: 'id',
                                render: function(data, type, row) {
                                    return `
                                        <div class="flex justify-center space-x-2">
                                            <label class="switch">
                                                <input type="checkbox" class="toggleStatus"
                                                    data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                                <span class="slider round"></span>
                                            </label>
                                            <button class="editRoleBtn bg-blue-500 text-white px-2 py-1 rounded"
                                                data-id="${data}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    `;
                                }
                            },
                            {
                                data: 'role_id'
                            },
                            {
                                data: 'role_name'
                            },
                            {
                                data: 'status',
                                className: 'text-center',
                                render: function(data) {
                                    return data === 'A' ?
                                        '<span class="bg-green-300/30 text-green-600 font-semibold px-4 py-1 rounded">Active</span>' :
                                        '<span class="bg-red-300/30 text-red-600 font-semibold px-4 py-1 rounded">Inactive</span>';
                                }
                            }
                        ]
                    });

                    // Add
                    $('#addRoleBtn').click(function() {
                        $('#roleModalTitle').text("Add Role");
                        $('#roleForm')[0].reset();
                        $('#id').val('');
                        $('#roleModal').removeClass('hidden');
                    });

                    // Edit
                    $(document).on('click', '.editRoleBtn', function() {
                        let id = $(this).data('id');

                        $('#roleModalTitle').text("Loading...");
                        $('#roleModal').removeClass('hidden');

                        $.get(`/roles/${id}/edit`, function(data) {
                            $('#roleModalTitle').text("Edit Role");
                            $('#id').val(data.id);
                            $('#role_id').val(data.role_id);
                            $('#role_name').val(data.role_name);
                        });
                    });

                    // Toggle status
                    $(document).on('change', '.toggleStatus', function() {
                        let id = $(this).data('id');
                        let newStatus = $(this).is(':checked') ? 'A' : 'X';

                        $.ajax({
                            url: `/roles/${id}/toggle-status`,
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
                    $('#roleForm').submit(function(e) {
                        e.preventDefault();

                        let id = $('#id').val();
                        let url = id ? `/roles/${id}` : "{{ route('roles.store') }}";
                        let method = 'POST';
                        let formData = new FormData(document.getElementById('roleForm'));

                        if (id) {
                            formData.append('_method', 'PUT');
                        }

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
                                $('#roleModal').addClass('hidden');
                                table.ajax.reload();
                            },
                            error: function(xhr) {
                                console.error(xhr.responseText);
                                alert('Gagal menyimpan data role');
                            }
                        });
                    });

                    $('#closeRoleModal').click(function() {
                        $('#roleModal').addClass('hidden');
                    });
                });
            </script>
        </div>
    </div>
</x-app-layout>
