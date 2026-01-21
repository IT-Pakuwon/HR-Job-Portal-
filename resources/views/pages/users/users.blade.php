<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'users' ? 'Users' : '';
    @endphp
    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Users List</h1>
                <button id="addAppBtn"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    + Add User
                </button>
            </div>
            {{-- Filter Company & Department --}}
            <div class="mb-3 flex flex-wrap items-end gap-3">
                <div class="min-w-[200px] flex-1">
                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Filter Company
                    </label>
                    <select id="filterCompany"
                        class="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm dark:bg-gray-700">
                        <option value="">All Company</option>
                        @foreach ($company as $c)
                            <option value="{{ $c->cpny_id }}">{{ $c->cpny_id }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[200px] flex-1">
                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Filter Department
                    </label>
                    <select id="filterDepartment"
                        class="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm dark:bg-gray-700">
                        <option value="">All Department</option>
                        @foreach ($department as $d)
                            <option value="{{ $d->department_id }}">{{ $d->department_id }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[200px] flex-1">
                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Filter Business Unit
                    </label>
                    <select id="filterBusinessUnit"
                        class="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm dark:bg-gray-700">
                        <option value="">All Business Unit</option>
                        @foreach ($businessUnits as $bu)
                            <option value="{{ $bu->business_unit_id }}">{{ $bu->business_unit_id }}</option>
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
            {{-- Table --}}
            <div class="rounded-base relative overflow-x-auto">
                <table id="usersTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th class="w-48 px-4 py-3 font-medium">Actions</th>
                            <th class="px-4 py-3 text-left font-medium">Name</th>
                            <th class="px-4 py-3 text-left font-medium">Username</th>
                            <th class="px-4 py-3 text-left font-medium">Email</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Departement</th>
                            <th class="px-4 py-3 text-left font-medium">BusinessUnit</th>
                            <th class="w-32 px-4 py-3 text-center font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>

        <!-- Modal -->
        <div id="appModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-1/3 rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="modalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add User</h2>
                <form id="appForm">
                    <input type="hidden" id="id">
                    <div class="mb-4 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 dark:text-white">Name</label>
                            <input type="text" id="name" name="name"
                                class="w-full rounded-lg border border-gray-400 px-3 py-2 dark:bg-gray-700" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Email</label>
                            <input type="text" id="email" name="email"
                                class="w-full rounded-lg border border-gray-400 px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                                required>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Company</label>
                            <select name="cpny_id[]" class="select2 w-full rounded-lg border px-3 py-2" multiple
                                required>
                                @foreach ($company as $p)
                                    <option value="{{ $p->cpny_id }}">{{ $p->cpny_id }}</option>
                                @endforeach
                            </select>

                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Departement</label>
                            <select name="department_id[]" class="select2 w-full rounded-lg border px-3 py-2" multiple
                                required>
                                @foreach ($department as $p)
                                    <option value="{{ $p->department_id }}">{{ $p->department_id }}</option>
                                @endforeach
                            </select>

                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Business Unit</label>
                            <select name="business_unit_id[]" class="select2 w-full rounded-lg border px-3 py-2"
                                multiple required>
                                @foreach ($businessUnits as $p)
                                    <option value="{{ $p->business_unit_id }}">{{ $p->business_unit_id }}</option>
                                @endforeach
                            </select>

                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Jabatan</label>
                            <select name="jabatan"
                                class="w-full rounded-lg border border-gray-400 px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                                required>
                                <option value="">Select Option</option>
                                <option value="staff">Staff</option>
                                <option value="manager">Manager</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">Role</label>
                            <select name="role"
                                class="w-full rounded-lg border border-gray-400 px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                                required>
                                <option value="">Select Option</option>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">App Roles </label>
                            <select name="role_ids[]" class="select2 w-full rounded-lg border px-3 py-2" multiple>
                                @foreach ($roles as $r)
                                    <option value="{{ $r->role_id }}">
                                        {{ $r->role_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">NPK</label>
                            <input type="text" name="npk" id="npk"
                                class="w-full rounded-lg border border-gray-400 px-3 py-2 dark:border-gray-600 dark:bg-gray-700">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" id="closeModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            let table = $('#usersTable').DataTable({
                ajax: "{{ route('users.json') }}",
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
                                            <!-- ✅ Toggle Active/Inactive -->
                                            <label class="switch cursor-pointer">
                                                <input type="checkbox" class="toggleStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                                <span class="slider round"></span>
                                            </label>

                                            <!-- ✏️ Edit -->
                                            <button type="button"
                                                    class="editAppBtn bg-blue-500 text-white px-2 py-1 rounded cursor-pointer"
                                                    data-id="${data}" title="Edit User">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <!-- 🔑 Login As -->
                                            <button type="button"
                                                    class="impersonateBtn bg-yellow-500 text-white px-2 py-1 rounded cursor-pointer"
                                                    data-id="${data}" title="Login As">
                                                <i class="fas fa-key"></i>
                                            </button>

                                            <!-- 🔁 Reset Password -->
                                            <button type="button"
                                                    class="resetPwdBtn bg-red-500 text-white px-2 py-1 rounded cursor-pointer"
                                                    data-id="${data}" title="Reset Password">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </div>
                                    `;
                        }
                    },

                    {
                        data: 'name',
                        className: 'no-pointer'
                    },
                    {
                        data: 'username',
                        className: 'no-pointer'
                    },
                    {
                        data: 'email',
                        className: 'no-pointer'
                    },
                    {
                        data: 'cpny_id',
                        className: 'no-pointer'
                    },
                    {
                        data: 'department_id',
                        className: 'no-pointer'
                    },
                    {
                        data: 'business_unit_id',
                        className: 'no-pointer'
                    },
                    {
                        data: 'status',
                        className: 'no-pointer',
                        render: function(data) {
                            return data === 'A' ?
                                '<span class=" w-full max-w-25 bg-green-300/30 dark:bg-green-300 text-green-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Active</span>' :
                                '<span class="  w-full max-w-25 bg-red-300/30 dark:bg-red-300 text-red-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Inactive</span>';
                        }
                    }
                ]
            });

            // ===== Filter Company (kolom 3) =====
            $('#filterCompany').on('change', function() {
                const val = $(this).val();

                table
                    .column(3) // cpny_id
                    .search(val || '', false, false)
                    .draw();
            });

            // ===== Filter Department (kolom 4) =====
            $('#filterDepartment').on('change', function() {
                const val = $(this).val();

                table
                    .column(4) // department_id
                    .search(val || '', false, false)
                    .draw();
            });

            $('#filterBusinessUnit').on('change', function() {
                const val = $(this).val();
                table
                    .column(7) // business_unit_id
                    .search(val || '', false, false)
                    .draw();
            });


            // ===== Clear Filter =====
            $('#clearUserFilters').on('click', function() {
                $('#filterCompany').val('');
                $('#filterDepartment').val('');
                $('#filterBusinessUnit').val('');

                table.column(3).search('');
                table.column(4).search('');
                table.column(7).search('');
                table.draw();
            });


            $('#addAppBtn').click(function() {
                $('#modalTitle').text("Add User");
                $('#appForm')[0].reset();
                $('#id').val('');
                $('.select2').val(null).trigger('change'); // termasuk role_ids[]
                $('#appModal').removeClass('hidden');
            });



            $(document).on('click', '.editAppBtn', function() {
                let appId = $(this).data('id');
                $.get(`/users/${appId}/edit`, function(app) {
                    $('#modalTitle').text("Edit User");
                    $('#id').val(app.id);
                    $('#name').val(app.name);
                    $('#email').val(app.email);
                    $('#npk').val(app.npk);
                    // $('#jabatan').val(app.jabatan);
                    $('select[name="jabatan"]').val(app.jabatan).trigger('change');
                    $('select[name="cpny_id[]"]').val(app.cpny_id).trigger('change');
                    $('select[name="department_id[]"]').val(app.department_id).trigger('change');
                    $('select[name="business_unit_id[]"]').val(app.business_unit_id).trigger(
                        'change');
                    $('select[name="role"]').val(app.role).trigger('change');

                    // ⬇️ Set app roles (sys_user_role)
                    $('select[name="role_ids[]"]').val(app.role_ids).trigger('change');

                    $('#appModal').removeClass('hidden');
                });
            });



            // ✅ Toggle Status (Active <-> Inactive)
            $(document).on('change', '.toggleStatus', function() {
                let appId = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/users/${appId}/toggle-status`,
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

            $('#appForm').submit(function(e) {
                e.preventDefault();
                let appId = $('#id').val();
                let url = appId ? `/users/${appId}` : "{{ route('users.store') }}";
                let method = 'POST'; // <-- selalu POST

                let formData = new FormData(document.getElementById('appForm'));

                if (appId) {
                    formData.append('_method', 'PUT'); // <-- spoof PUT method
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
                        $('#appModal').addClass('hidden');
                        table.ajax.reload();
                    }
                });
            });

            $('#closeModal').click(function() {
                $('#appModal').addClass('hidden');
            });
        });

        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Select Option",
                allowClear: true,
                width: '100%'
            });
        });

        // 🔑 Login As (SweetAlert)
        $(document).on('click', '.impersonateBtn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            let userId = $(this).data('id');

            Swal.fire({
                title: "Login As User?",
                text: "You will be logged in as this user.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, Continue",
                cancelButtonText: "Cancel"
            }).then((result) => {

                if (result.isConfirmed) {
                    $.ajax({
                        url: `/users/${userId}/impersonate`,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function(res) {

                            Swal.fire({
                                title: "Sucessfully!",
                                text: res.message || "Login Sucessfully.",
                                icon: "success",
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = res.redirect ?? window.location
                                    .href;
                            });

                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: "Failed!",
                                text: xhr.responseJSON?.message ||
                                    'Failed to log in as user.',
                                icon: "error"
                            });
                        }
                    });
                }

            });
        });
        // 🔁 Reset Password ke default: pakuwon1234#               
        $(document).on('click', '.resetPwdBtn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            let userId = $(this).data('id');

            Swal.fire({
                title: "Reset Password?",
                text: "Password user will be reset to default: pakuwon1234#",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, Reset",
                cancelButtonText: "Cancel"
            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({
                        url: `/users/${userId}/reset-password`,
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function(res) {

                            Swal.fire({
                                title: "Successfully!",
                                text: res.message || "Password reset successfully.",
                                icon: "success",
                                timer: 1500,
                                showConfirmButton: false
                            });

                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: "Failed!",
                                text: xhr.responseJSON?.message ||
                                    "Reset password failed.",
                                icon: "error"
                            });
                        }
                    });

                }

            });
        });
    </script>
</x-app-layout>
