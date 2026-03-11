<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'role_menus' ? 'Role Menus' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-bold text-gray-800 dark:text-white">Role Menu Mapping</h1>
                <button id="addRoleMenuBtn"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    + Add Role Menu
                </button>
            </div>

            {{-- Filter Company & Department --}}
            <div class="mb-3 flex flex-wrap items-end gap-3">
                <div class="min-w-[200px] flex-1">
                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Filter Role
                    </label>
                    <select id="filterRole"
                        class="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm dark:bg-gray-700">
                        <option value="">All Role</option>
                        @foreach ($roles as $r)
                            <option value="{{ $r->role_id }}">{{ $r->role_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[200px] flex-1">
                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Filter Menu
                    </label>
                    <select id="filterMenu"
                        class="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm dark:bg-gray-700">
                        <option value="">All Menu</option>
                        @foreach ($menus as $m)
                            <option value="{{ $m->menu_id }}">{{ $m->menu_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-6">
                    <button id="clearUserFilters" type="button"
                        class="rounded-lg border px-3 py-1 text-sm text-gray-700 hover:bg-gray-100 dark:border-gray-500 dark:text-gray-200 dark:hover:bg-gray-600">
                        Clear Filter
                    </button>
                </div>
            </div>
            <div class="rounded-base relative overflow-x-auto">
                <table id="roleMenusTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th class="w-32 px-4 py-3 text-center">Actions</th>
                            <th class="px-4 py-3 text-left">Role ID</th>
                            <th class="px-4 py-3 text-left">Menu ID</th>
                            {{-- <th class="px-4 py-3 text-left">Parent Menu</th> --}}
                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        {{-- Modal --}}
        <div id="roleMenuModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-3xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="roleMenuModalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">
                    Add Role Menu
                </h2>
                <form id="roleMenuForm">
                    @csrf
                    <input type="hidden" id="id" name="id">

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Role</label>
                        <select id="role_id" name="role_id"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                            <option value="">Select Role </option>
                            @foreach ($roles as $r)
                                <option value="{{ $r->role_id }}">{{ $r->role_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Menu</label>
                        <select id="menu_id" name="menu_id[]" multiple size="12"
                            class="h-64 w-full overflow-y-auto rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                            @foreach ($menus as $m)
                                <option value="{{ $m->menu_id }}">
                                    {{ $m->menu_name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-gray-500">* Hold CTRL untuk memilih banyak menu</small>
                    </div>


                    {{-- <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Parent Menu ID (optional)</label>
                            <select id="parent_menu_id" name="parent_menu_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                                <option value="">None </option>
                                @foreach ($parentMenus as $pm)
                                    <option value="{{ $pm }}">{{ $pm }}</option>
                                @endforeach
                            </select>
                        </div> --}}

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeRoleMenuModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="loadingOverlay"
        class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/40">
        <div class="flex items-center gap-3 rounded-xl bg-white px-6 py-4 shadow-lg">
            <svg class="h-6 w-6 animate-spin text-indigo-600" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10"
                    stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span class="text-sm font-semibold text-gray-700">Processing...</span>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function showLoading() {
            $('#loadingOverlay').removeClass('hidden');
        }

        function hideLoading() {
            $('#loadingOverlay').addClass('hidden');
        }

        $(document).ready(function() {
            let table = $('#roleMenusTable').DataTable({
                ajax: {
                    url: "{{ route('role_menus.json') }}",
                    type: "GET",
                    dataSrc: 'data'
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
                        data: null,
                        defaultContent: ''
                    }, {
                        data: 'id',
                        render: function(data, type, row) {
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
                    {
                        data: 'role_id'
                    },
                    {
                        data: 'menu_id'
                    },
                    // { data: 'parent_menu_id' },
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

            // ===== Filter Role =====
            $('#filterRole').on('change', function() {
                const val = $(this).val();

                table
                    .column(2) // role_id
                    .search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false)
                    .draw();
            });

            // ===== Filter Menu =====
            $('#filterMenu').on('change', function() {
                const val = $(this).val();

                table
                    .column(3) // menu_id
                    .search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false)
                    .draw();
            });

            // ===== Clear Filter =====
            $('#clearUserFilters').on('click', function() {
                $('#filterRole').val('');
                $('#filterMenu').val('');

                table.column(2).search('');
                table.column(3).search('');
                table.draw();
            });


            // Add
            $('#addRoleMenuBtn').click(function() {
                $('#roleMenuModalTitle').text("Add Role Menu");
                $('#roleMenuForm')[0].reset();
                $('#id').val('');
                $('#menu_id').val([]).change(); // clear multiselect
                $('#roleMenuModal').removeClass('hidden');
            });


            // Edit
            // Edit
           $(document).on('click', '.editRoleMenuBtn', function() {
                let id = $(this).data('id');

                $('#roleMenuModalTitle').text("Loading...");
                $('#roleMenuModal').removeClass('hidden');
                showLoading();

                $.get(`/role-menus/${id}/edit`, function(data) {
                    $('#roleMenuModalTitle').text("Edit Role Menu");
                    $('#id').val(data.id);
                    $('#role_id').val(data.role_id);

                    if ($('#parent_menu_id').length) {
                        $('#parent_menu_id').val(data.parent_menu_id);
                    }

                    $('#menu_id').val(data.menu_ids).change();

                    hideLoading();
                }).fail(function(xhr) {
                    hideLoading();
                    $('#roleMenuModal').addClass('hidden');

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load role menu data'
                    });

                    console.error(xhr.responseText);
                });
            });


            // Toggle status
            $(document).on('change', '.toggleStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/role-menus/${id}/toggle-status`,
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

            // Submit (create/update)
            $('#roleMenuForm').submit(function(e) {
                e.preventDefault();

                let id = $('#id').val();
                let url = id ? `/role-menus/${id}` : "{{ route('role_menus.store') }}";
                let method = 'POST';
                let formData = new FormData(document.getElementById('roleMenuForm'));

                if (id) {
                    formData.append('_method', 'PUT');
                }

                showLoading();
                $('#roleMenuForm button[type="submit"]').prop('disabled', true);

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
                        hideLoading();
                        $('#roleMenuForm button[type="submit"]').prop('disabled', false);

                        $('#roleMenuModal').addClass('hidden');
                        table.ajax.reload();

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Role menu saved successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#roleMenuForm button[type="submit"]').prop('disabled', false);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal menyimpan data role menu'
                        });

                        console.error(xhr.responseText);
                    }
                });
            });

            $('#closeRoleMenuModal').click(function() {
                $('#roleMenuForm')[0].reset();
                $('#id').val('');
                $('#menu_id').val([]).change();
                $('#roleMenuModal').addClass('hidden');
            });
        });
    </script>
</x-app-layout>
