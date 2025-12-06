<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'role_menus' ? 'Role Menus' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="mb-8 sm:flex sm:items-center sm:justify-between"></div>

        <div class="grid">
            <style>
                table.dataTable { width: 100% !important; }
                #roleMenusTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    align-items: center;
                }
                #roleMenusTable_filter input {
                    width: auto;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
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
                    top: 0; left: 0; right: 0; bottom: 0;
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
                input:checked + .slider { background-color: #4CAF50; }
                input:checked + .slider:before { transform: translateX(18px); }
            </style>

            <div class="mt-6 rounded-xl bg-white p-4 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">🧩 Role Menu Mapping</h2>
                    <button id="addRoleMenuBtn" class="rounded-lg bg-indigo-500 px-5 py-2 text-white">
                        + Add Role Menu
                    </button>
                </div>

                <table id="roleMenusTable" class="w-full border-collapse">
                    <thead class="bg-white dark:bg-gray-700">
                        <tr>
                            <th class="w-32 px-4 py-3 text-center">Actions</th>
                            <th class="px-4 py-3 text-left">Role ID</th>
                            <th class="px-4 py-3 text-left">Menu ID</th>
                            <th class="px-4 py-3 text-left">Parent Menu</th>
                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            {{-- Modal --}}
            <div id="roleMenuModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
                <div class="relative w-full max-w-3xl rounded-lg bg-white p-6 dark:bg-gray-700">
                    <h2 id="roleMenuModalTitle" class="mb-4 text-xl font-bold text-gray-800 dark:text-white">
                        Add Role Menu
                    </h2>
                    <form id="roleMenuForm">
                        @csrf
                        <input type="hidden" id="id" name="id">

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Role</label>
                            <select id="role_id" name="role_id"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                <option value="">-- Select Role --</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->role_id }}">{{ $r->role_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Menu</label>
                            <select id="menu_id" name="menu_id[]" 
                                multiple
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                @foreach($menus as $m)
                                    <option value="{{ $m->menu_id }}">
                                        {{ $m->menu_name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-gray-500">* Hold CTRL untuk memilih banyak menu</small>
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Parent Menu ID (optional)</label>
                            {{-- <input type="text" id="parent_menu_id" name="parent_menu_id"
                                   class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700"> --}}
                            <select id="parent_menu_id" name="parent_menu_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                                <option value="">-- None --</option>
                                @foreach($parentMenus as $pm)
                                    <option value="{{ $pm }}">{{ $pm }}</option>
                                @endforeach
                            </select>

                        </div>

                        <div class="mt-4 flex justify-end space-x-2">
                            <button type="button" id="closeRoleMenuModal"
                                    class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                            <button type="submit"
                                    class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                $(document).ready(function () {
                    let table = $('#roleMenusTable').DataTable({
                        ajax: {
                            url: "{{ route('role_menus.json') }}",
                            type: "GET",
                            dataSrc: 'data'
                        },
                        processing: true,
                        serverSide: false,
                        columns: [
                            {
                                data: 'id',
                                render: function (data, type, row) {
                                    return `
                                        <div class="flex justify-center space-x-2">
                                            <label class="switch">
                                                <input type="checkbox" class="toggleStatus"
                                                    data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                                <span class="slider round"></span>
                                            </label>
                                            <button class="editRoleMenuBtn bg-blue-500 text-white px-2 py-1 rounded"
                                                    data-id="${data}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    `;
                                }
                            },
                            { data: 'role_id' },
                            { data: 'menu_id' },
                            { data: 'parent_menu_id' },
                            {
                                data: 'status',
                                className: 'text-center',
                                render: function (data) {
                                    return data === 'A'
                                        ? '<span class="bg-green-300/30 text-green-600 font-semibold px-4 py-1 rounded">Active</span>'
                                        : '<span class="bg-red-300/30 text-red-600 font-semibold px-4 py-1 rounded">Inactive</span>';
                                }
                            }
                        ]
                    });

                    // Add
                    $('#addRoleMenuBtn').click(function () {
                        $('#roleMenuModalTitle').text("Add Role Menu");
                        $('#roleMenuForm')[0].reset();
                        $('#id').val('');
                        $('#menu_id').val([]).change(); // clear multiselect
                        $('#roleMenuModal').removeClass('hidden');
                    });


                    // Edit
                    // Edit
                    $(document).on('click', '.editRoleMenuBtn', function () {
                        let id = $(this).data('id'); // ← ini id row, bukan role_id

                        $('#roleMenuModalTitle').text("Loading...");
                        $('#roleMenuModal').removeClass('hidden');

                        $.get(`/role-menus/${id}/edit`, function (data) {
                            $('#roleMenuModalTitle').text("Edit Role Menu");
                            $('#id').val(data.id);                // id row utk URL PUT
                            $('#role_id').val(data.role_id);      // set role
                            $('#parent_menu_id').val(data.parent_menu_id);

                            // Set multiple menu selection
                            $('#menu_id').val(data.menu_ids).change();
                        });
                    });


                    // Toggle status
                    $(document).on('change', '.toggleStatus', function () {
                        let id = $(this).data('id');
                        let newStatus = $(this).is(':checked') ? 'A' : 'X';

                        $.ajax({
                            url: `/role-menus/${id}/toggle-status`,
                            type: 'PUT',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: { status: newStatus },
                            success: function () {
                                table.ajax.reload(null, false);
                            }
                        });
                    });

                    // Submit (create/update)
                    $('#roleMenuForm').submit(function (e) {
                        e.preventDefault();

                        let id = $('#id').val();
                        let url = id ? `/role-menus/${id}` : "{{ route('role_menus.store') }}";
                        let method = 'POST';
                        let formData = new FormData(document.getElementById('roleMenuForm'));

                        if (id) {
                            formData.append('_method', 'PUT');
                        }

                        $.ajax({
                            url: url,
                            type: method,
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function () {
                                $('#roleMenuModal').addClass('hidden');
                                table.ajax.reload();
                            },
                            error: function (xhr) {
                                console.error(xhr.responseText);
                                alert('Gagal menyimpan data role menu');
                            }
                        });
                    });

                    $('#closeRoleMenuModal').click(function () {
                        $('#roleMenuModal').addClass('hidden');
                    });
                });
            </script>
        </div>
    </div>
</x-app-layout>
