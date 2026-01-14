<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'access_rights' ? 'Access Rights' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="mt-6 flex flex-col gap-6 rounded-xl bg-white p-6 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-bold text-gray-800 dark:text-white">Access Rights</h1>
                <button id="addAccessRightBtn"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    + Add Access Right
                </button>
            </div>

            <div class="mb-3 flex flex-wrap items-end gap-3">
                <div class="min-w-[200px] flex-1">
                    <label class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-200">
                        Filter Role
                    </label>
                    <select id="filterRole"
                        class="w-full rounded-lg border border-gray-300 px-2 py-1 text-xs dark:bg-gray-700">
                        <option value="">All Role</option>
                        @foreach ($roles as $r)
                            <option value="{{ $r->role_id }}">{{ $r->role_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[200px] flex-1">
                    <label class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-200">
                        Filter Screen
                    </label>
                    <select id="filterScreen"
                        class="w-full rounded-lg border border-gray-300 px-2 py-1 text-xs dark:bg-gray-700">
                        <option value="">All Screen</option>
                        @foreach ($screens as $s)
                            <option value="{{ $s->menu_id }}">{{ $s->menu_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-6">
                    <button id="clearUserFilters" type="button"
                        class="rounded-lg border px-3 py-1 text-xs text-gray-700 hover:bg-gray-100 dark:border-gray-500 dark:text-gray-200 dark:hover:bg-gray-600">
                        Clear Filter
                    </button>
                </div>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="accessRightsTable" class="text-body w-full text-left text-xs rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-xs">
                        <tr>
                            <th></th>
                            <th class="w-32 px-4 py-3 text-center">Actions</th>
                            <th class="px-4 py-3 text-left">Role</th>
                            <th class="px-4 py-3 text-left">Screen ID</th>
                            <th class="px-4 py-3 text-left">App ID</th>
                            <th class="px-4 py-3 text-left">Access Name</th>
                            <th class="px-4 py-3 text-left">Allowed?</th>
                            <th class="px-4 py-3 text-left">Type</th>
                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        {{-- Modal --}}
        <div id="accessRightModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            {{-- 🔥 Modal dilebarkan: max-w-2xl --}}
            <div class="relative w-full max-w-2xl rounded-lg bg-white p-6 dark:bg-gray-700">
                <h2 id="accessRightModalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">
                    Add Access Right
                </h2>
                <form id="accessRightForm">
                    @csrf
                    <input type="hidden" id="id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Role</label>
                            <select id="role_id" name="role_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                <option value="">-- Select Role --</option>
                                @foreach ($roles as $r)
                                    <option value="{{ $r->role_id }}">{{ $r->role_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Screen</label>
                            <select id="screen_id" name="screen_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                <option value="">-- Select Screen --</option>
                                @foreach ($screens as $s)
                                    <option value="{{ $s->menu_id }}">{{ $s->menu_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Application ID</label>
                            <select id="application_id" name="application_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                <option value="">-- Application --</option>
                                @foreach ($applications as $app)
                                    <option value="{{ $app->application_id }}">
                                        {{ $app->application_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Access Type (optional)</label>
                            <input type="text" id="access_type" name="access_type" value="NORMAL"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700"
                                placeholder="e.g. NORMAL, SPECIAL">
                        </div>

                        {{-- Checkbox VIEW / CREATE / EDIT / DELETE --}}
                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Access Rights</label>
                            <div class="mt-1 flex justify-between gap-4">
                                <label class="inline-flex items-center space-x-1">
                                    <input type="checkbox" name="access_names[]" value="VIEW" id="chk_view"
                                        class="h-4 w-4">
                                    <span>VIEW</span>
                                </label>
                                <label class="inline-flex items-center space-x-1">
                                    <input type="checkbox" name="access_names[]" value="CREATE" id="chk_create"
                                        class="h-4 w-4">
                                    <span>CREATE</span>
                                </label>
                                <label class="inline-flex items-center space-x-1">
                                    <input type="checkbox" name="access_names[]" value="EDIT" id="chk_edit"
                                        class="h-4 w-4">
                                    <span>EDIT</span>
                                </label>
                                <label class="inline-flex items-center space-x-1">
                                    <input type="checkbox" name="access_names[]" value="DELETE" id="chk_delete"
                                        class="h-4 w-4">
                                    <span>DELETE</span>
                                </label>
                            </div>
                        </div>

                        {{-- 🔥 Other Access Names: input + tombol ADD + checkbox dinamis --}}
                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">
                                Other Access Names
                            </label>
                            <div class="mt-1 flex flex-wrap gap-2">
                                <input type="text" id="new_access_name"
                                    class="flex-1 rounded-lg border px-3 py-2 dark:bg-gray-700"
                                    placeholder="Contoh: APPROVE, REJECT">
                                <button type="button" id="btnAddAccessName"
                                    class="rounded-lg bg-emerald-500 px-4 py-2 text-white">
                                    ADD
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Ketik satu nama lalu klik ADD. Untuk beberapa nama, klik ADD berkali-kali.
                            </p>
                            <div id="customAccessContainer" class="mt-2 flex flex-wrap gap-3">
                                {{-- checkbox akses custom akan muncul di sini --}}
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeAccessRightModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            let table = $('#accessRightsTable').DataTable({
                ajax: {
                    url: "{{ route('access_rights.json') }}",
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
                    className: 'dtr-control',
                    orderable: false
                }],
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'Access Rights',
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
                        title: 'Access Rights',
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
                                            <button class="editAccessRightBtn bg-blue-500 text-white px-2 py-1 rounded"
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
                        data: 'screen_id'
                    },
                    {
                        data: 'application_id'
                    },
                    {
                        data: 'access_name'
                    },
                    {
                        data: 'access_right',
                        render: function(data) {
                            return data ?
                                '<span class="text-green-600 font-semibold">YES</span>' :
                                '<span class="text-red-600 font-semibold">NO</span>';
                        }
                    },
                    {
                        data: 'access_type'
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

            // ===== Filter ROLE (kolom index 1) =====
            $('#filterRole').on('change', function() {
                const val = $(this).val();

                table
                    .column(1) // role_id
                    .search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false)
                    .draw();
            });

            // ===== Filter SCREEN ID (kolom index 2) =====
            $('#filterScreen').on('change', function() {
                const val = $(this).val();

                table
                    .column(2) // screen_id
                    .search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false)
                    .draw();
            });

            // ===== Clear Filter =====
            $('#clearFilters').on('click', function() {
                $('#filterRole').val('');
                $('#filterScreen').val('');

                table.column(1).search('');
                table.column(2).search('');
                table.draw();
            });


            function resetAccessModal() {
                $('#accessRightForm')[0].reset();
                $('#id').val('');
                $('#chk_view').prop('checked', false);
                $('#chk_create').prop('checked', false);
                $('#chk_edit').prop('checked', false);
                $('#chk_delete').prop('checked', false);
                $('#new_access_name').val('');
                $('#customAccessContainer').empty();
            }

            // helper: tambah checkbox custom secara dinamis
            function addCustomCheckbox(label, checked = true) {
                if (!label) return;
                let val = label.toString().trim();
                if (!val) return;
                let up = val.toUpperCase();

                // kalau sudah ada, tinggal ceklis
                let existing = $('#customAccessContainer input[type="checkbox"][value="' + up + '"]');
                if (existing.length > 0) {
                    existing.prop('checked', true);
                    return;
                }

                let html = `
                            <label class="inline-flex items-center space-x-1">
                                <input type="checkbox" name="access_names[]" value="${up}"
                                       class="h-4 w-4 custom-access">
                                <span>${up}</span>
                            </label>
                        `;
                $('#customAccessContainer').append(html);

                if (checked) {
                    $('#customAccessContainer input[type="checkbox"][value="' + up + '"]').prop('checked', true);
                }
            }

            // klik tombol ADD untuk Other Access Names
            $('#btnAddAccessName').on('click', function() {
                let val = $('#new_access_name').val();
                addCustomCheckbox(val, true);
                $('#new_access_name').val('');
                $('#new_access_name').focus();
            });

            // Enter di input Other Access Names juga jadi ADD
            $('#new_access_name').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#btnAddAccessName').click();
                }
            });

            // Add
            $('#addAccessRightBtn').click(function() {
                $('#accessRightModalTitle').text("Add Access Right");
                resetAccessModal();
                $('#accessRightModal').removeClass('hidden');
            });

            // Edit
            $(document).on('click', '.editAccessRightBtn', function() {
                let id = $(this).data('id');

                $('#accessRightModalTitle').text("Loading...");
                resetAccessModal();
                $('#accessRightModal').removeClass('hidden');

                $.get(`/access-rights/${id}/edit`, function(data) {
                    $('#accessRightModalTitle').text("Edit Access Right");
                    $('#id').val(data.id);
                    $('#role_id').val(data.role_id);
                    $('#screen_id').val(data.screen_id);
                    $('#application_id').val(data.application_id);
                    $('#access_type').val(data.access_type);

                    // access_names = array dari controller
                    let names = data.access_names || [];
                    names.forEach(function(n) {
                        let up = (n || '').toUpperCase();
                        if (up === 'VIEW') {
                            $('#chk_view').prop('checked', true);
                        } else if (up === 'CREATE') {
                            $('#chk_create').prop('checked', true);
                        } else if (up === 'EDIT') {
                            $('#chk_edit').prop('checked', true);
                        } else if (up === 'DELETE') {
                            $('#chk_delete').prop('checked', true);
                        } else if (up) {
                            // nama lain masuk ke checkbox custom
                            addCustomCheckbox(up, true);
                        }
                    });
                });
            });

            // Toggle status
            $(document).on('change', '.toggleStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/access-rights/${id}/toggle-status`,
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
            $('#accessRightForm').submit(function(e) {
                e.preventDefault();

                let id = $('#id').val();
                let url = id ? `/access-rights/${id}` : "{{ route('access_rights.store') }}";
                let method = 'POST';
                let formData = new FormData(document.getElementById('accessRightForm'));

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
                        $('#accessRightModal').addClass('hidden');
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Gagal menyimpan access right');
                    }
                });
            });

            $('#closeAccessRightModal').click(function() {
                $('#accessRightModal').addClass('hidden');
            });
        });
    </script>
</x-app-layout>
