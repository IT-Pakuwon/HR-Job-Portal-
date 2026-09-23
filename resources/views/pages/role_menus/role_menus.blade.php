<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'role_menus' ? 'Role Menus' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <div>
            {{-- Tab nav --}}
            <div class="flex gap-1 border-b border-gray-200 dark:border-gray-700">
                <button type="button" id="tabBtnList"
                    class="role-menu-tab-btn rounded-t-lg border border-b-0 border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-indigo-600 dark:border-gray-700 dark:bg-gray-800 dark:text-indigo-400">
                    📋 Role Menu List
                </button>
                <button type="button" id="tabBtnAssign"
                    class="role-menu-tab-btn rounded-t-lg border border-b-0 border-gray-200 bg-gray-50 px-5 py-2.5 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                    🔑 Assign Menus
                </button>
            </div>

            {{-- Tab: Role Menu List --}}
            <div id="tabPanelList"
                class="rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
                <div
                    class="flex items-center justify-between border-b border-gray-100 px-5 py-2 dark:border-white/[0.06]">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">📋 Role Menu
                        List</h2>
                    <button id="addRoleMenuBtn"
                        class="inline-flex h-9 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add Role Menu
                    </button>
                </div>

                {{-- Filter Company & Department --}}
                <div class="flex flex-wrap items-end gap-3 px-5 pt-4">
                    <div class="min-w-[200px] flex-1">
                        <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                            Filter Role
                        </label>
                        <select id="filterRole"
                            class="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm dark:bg-gray-700 dark:border-gray-700">
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
                            class="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm dark:bg-gray-700 dark:border-gray-700">
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
                <div class="relative mt-4 overflow-hidden">
                    <table id="roleMenusTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-10 px-4 py-3"></th>
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Role ID</th>
                                <th class="px-4 py-3 text-left font-medium">Menu ID</th>
                                {{-- <th class="px-4 py-3 text-left">Parent Menu</th> --}}
                                <th class="w-32 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            {{-- Tab: Assign Menus (matrix view) --}}
            <div id="tabPanelAssign"
                class="hidden rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                <h1 class="mb-3 text-base font-bold text-gray-800 dark:text-white">🔑 Assign Menus</h1>

                <div class="flex flex-wrap items-end gap-3">
                    <div class="min-w-[240px] flex-1">
                        <select id="matrixRole"
                            class="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm dark:bg-gray-700 dark:border-gray-700">
                            <option value="">-- Select a Role to manage its menus --</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r->role_id }}">{{ $r->role_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="matrixActions" class="hidden flex flex-wrap items-center gap-2">
                        <input id="matrixSearch" type="text" placeholder="Search menu..."
                            class="rounded-lg border border-gray-300 px-2 py-1 text-sm dark:bg-gray-700 dark:border-gray-700">
                        <button type="button" id="matrixSelectAll"
                            class="rounded-lg border px-3 py-1 text-sm text-gray-700 hover:bg-gray-100 dark:border-gray-500 dark:text-gray-200 dark:hover:bg-gray-600">
                            Select All
                        </button>
                        <button type="button" id="matrixSelectNone"
                            class="rounded-lg border px-3 py-1 text-sm text-gray-700 hover:bg-gray-100 dark:border-gray-500 dark:text-gray-200 dark:hover:bg-gray-600">
                            Select None
                        </button>
                        <button type="button" id="matrixSave"
                            class="rounded-lg bg-indigo-600 px-4 py-1 text-sm font-semibold text-white hover:bg-indigo-700">
                            Save Changes
                        </button>
                    </div>
                </div>

                <div id="matrixEmpty" class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                    Pick a role above to see and edit all of its menus in one place.
                </div>

                <div id="matrixGrid" class="mt-3 hidden grid grid-cols-1 gap-x-6 gap-y-2 sm:grid-cols-2 lg:grid-cols-3">
                </div>
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
                        <small class="text-gray-500 dark:text-gray-400">* Hold CTRL untuk memilih banyak menu</small>
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
        <div class="flex items-center gap-3 rounded-xl bg-white px-6 py-4 shadow-lg dark:bg-gray-800">
            <svg class="h-6 w-6 animate-spin text-indigo-600" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10"
                    stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Processing...</span>
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
            // ===== Tabs =====
            function activateTab(tab) {
                const isList = tab === 'list';
                $('#tabPanelList').toggleClass('hidden', !isList);
                $('#tabPanelAssign').toggleClass('hidden', isList);

                $('#tabBtnList')
                    .toggleClass('bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400', isList)
                    .toggleClass('bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400', !isList);
                $('#tabBtnAssign')
                    .toggleClass('bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400', !isList)
                    .toggleClass('bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400', isList);

                if (isList) {
                    table.columns.adjust().draw(false);
                }
            }

            $('#tabBtnList').on('click', function() {
                activateTab('list');
            });
            $('#tabBtnAssign').on('click', function() {
                activateTab('assign');
            });

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

            // ===== Manage by Role: matrix =====
            const allMenus = @json($menus->map(fn($m) => ['menu_id' => $m->menu_id, 'menu_name' => $m->menu_name])->values());

            function renderMatrix(checkedMenuIds) {
                const checkedSet = new Set(checkedMenuIds);
                const html = allMenus.map(m => `
                    <label class="matrix-row flex items-center gap-2 rounded px-2 py-1 text-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                           data-name="${(m.menu_name + ' ' + m.menu_id).toLowerCase()}">
                        <input type="checkbox" class="matrixMenuChk h-4 w-4" value="${m.menu_id}"
                               ${checkedSet.has(m.menu_id) ? 'checked' : ''}>
                        <span>${m.menu_name} <span class="text-gray-400">(${m.menu_id})</span></span>
                    </label>
                `).join('');

                $('#matrixGrid').html(html).removeClass('hidden');
                $('#matrixEmpty').addClass('hidden');
                $('#matrixActions').removeClass('hidden');
            }

            $('#matrixRole').on('change', function() {
                const roleId = $(this).val();

                if (!roleId) {
                    $('#matrixGrid').addClass('hidden').empty();
                    $('#matrixActions').addClass('hidden');
                    $('#matrixEmpty').removeClass('hidden');
                    return;
                }

                showLoading();
                $.get(`/role-menus/by-role/${roleId}`, function(data) {
                    hideLoading();
                    renderMatrix(data.menu_ids || []);
                }).fail(function() {
                    hideLoading();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load menus for this role'
                    });
                });
            });

            $('#matrixSearch').on('input', function() {
                const q = $(this).val().toLowerCase();
                $('.matrix-row').each(function() {
                    $(this).toggle($(this).data('name').includes(q));
                });
            });

            $('#matrixSelectAll').on('click', function() {
                $('.matrix-row:visible .matrixMenuChk').prop('checked', true);
            });

            $('#matrixSelectNone').on('click', function() {
                $('.matrix-row:visible .matrixMenuChk').prop('checked', false);
            });

            $('#matrixSave').on('click', function() {
                const roleId = $('#matrixRole').val();
                if (!roleId) return;

                const menuIds = $('.matrixMenuChk:checked').map(function() {
                    return $(this).val();
                }).get();

                showLoading();
                $.ajax({
                    url: "{{ route('role_menus.save_by_role') }}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        role_id: roleId,
                        menu_id: menuIds
                    },
                    success: function() {
                        hideLoading();
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved',
                            text: 'Menus updated for this role',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        hideLoading();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to save role menus'
                        });
                        console.error(xhr.responseText);
                    }
                });
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
