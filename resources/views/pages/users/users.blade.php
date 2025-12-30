<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'users' ? 'Users' : '';
    @endphp
    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <!-- Dashboard actions -->
        <div class="mb-8 sm:flex sm:items-center sm:justify-between"></div>
        <!-- Breadcrumb dengan Dropdown -->

        <div class="grid">
            <style>
                .no-border {
                    border: none !important;
                }

                .grid {
                    width: 100%;
                }

                select,
                textarea,
                input {
                    width: 100%;
                    /* Make all input elements take full width */
                }

                table.dataTable {
                    width: 100% !important;
                }

                .dataTables_wrapper {
                    width: 100%;
                }

                @media (max-width: 600px) {
                    .dataTables_wrapper {
                        padding: 0 10px;
                    }
                }

                #usersTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    /* Aligns items to the left */
                    align-items: center;
                    /* Vertically aligns items */
                }

                #usersTable_filter label {
                    margin-right: 2px;
                }

                #usersTable_filter input {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }


                #usersTable_wrapper {
                    margin-top: 20px;
                    width: 100%;
                }

                /* Prevent text from wrapping */
                #usersTable td {
                    white-space: nowrap;
                    /* Prevent text from wrapping */
                    overflow: hidden;
                    /* Hide overflow content */
                    text-overflow: ellipsis;
                    /* Display ellipsis ("...") for overflowing content */
                }

                /* Optional: Adjust the width for table cells */
                #usersTable th,
                #usersTable td {
                    padding: 10px;
                    /* Adjust padding for better appearance */
                    max-width: 200px;
                    /* You can set a maximum width to control overflow */
                }


                #usersTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }

                #usersTable_length select {
                    width: 80px;
                    /* Lebar otomatis untuk select dropdown */
                    padding: 5px;
                    Menambahkan padding agar lebih nyaman min-width: 0px;
                    /* Lebar minimal untuk memastikan angka tidak tertutup */
                }

                #usersTable_length select option {
                    padding: 5px;
                    /* Mengatur jarak antar opsi */
                }

                #usersTable_info {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                .dataTables_paginate {
                    margin-top: 10px;
                    margin-bottom: 10px;

                }

                #usersTable tbody tr td {
                    padding: 8px 8px;
                    /* Adjust padding for uniform height */
                    line-height: 1.6;
                    /* Optional, for better text alignment */
                }

                #usersTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }

                #usersTable tbody tr:hover {
                    background-color: #8f8f8f11;
                    opacity: 100%;
                    cursor: pointer;
                }

                #usersTable tbody tr:hover td {
                    /* color: black; */
                }
            </style>
            <style>
                /* ✅ Custom Switch Button */
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

                /* ✅ Memperkecil Lebar Kolom Actions */
                /* #usersTable th:nth-child(1),
                #usersTable td:nth-child(1) {
                    width: 120px;
                    text-align: center;
                }

                #usersTable th:nth-child(4),
                #usersTable td:nth-child(4) {
                    width: 120px;
                    text-align: center;
                } */

                /* ✅ Memperkecil Lebar Kolom Actions */
                #usersTable th:nth-child(1),
                #usersTable td:nth-child(1) {
                    width: 180px;
                    /* sebelumnya 120px, kita besarin dikit */
                    text-align: center;
                }
            </style>
            <div class="mt-6 rounded-xl bg-white p-4 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">📌 Users List</h2>
                    <button id="addAppBtn" class="rounded-lg bg-indigo-500 px-5 py-2 text-white">
                        + Add User
                    </button>
                </div>

                {{-- Filter Company & Department --}}
                <div class="mb-3 flex flex-wrap items-center gap-3">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                            Filter Company
                        </label>
                        <select id="filterCompany" class="rounded-lg border px-2 py-1 text-sm dark:bg-gray-700">
                            <option value="">-- All --</option>
                            @foreach ($company as $c)
                                <option value="{{ $c->cpny_id }}">{{ $c->cpny_id }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                            Filter Department
                        </label>
                        <select id="filterDepartment" class="rounded-lg border px-2 py-1 text-sm dark:bg-gray-700">
                            <option value="">-- All --</option>
                            @foreach ($department as $d)
                                <option value="{{ $d->department_id }}">{{ $d->department_id }}</option>
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


                <table id="usersTable" class="w-full table-fixed border-collapse">
                    <thead class="bg-white dark:bg-gray-700">
                        <tr>
                            <th class="w-48 px-4 py-3 text-center">Actions</th>
                            <th class="px-4 py-3 text-left">Name</th>
                            <th class="px-4 py-3 text-left">Username</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-left">Company</th>
                            <th class="px-4 py-3 text-left">Departement</th>
                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Modal -->
            <div id="appModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
                <div class="relative w-1/3 rounded-lg bg-white p-6 dark:bg-gray-700">
                    <h2 id="modalTitle" class="mb-4 text-xl font-bold text-gray-800 dark:text-white">Add User</h2>
                    <form id="appForm">
                        <input type="hidden" id="id">
                        <div class="mb-4 grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 dark:text-white">Name</label>
                                <input type="text" id="name" name="name"
                                    class="w-full rounded-lg border border-gray-400 px-3 py-2 dark:bg-gray-700"
                                    required>
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
                                <select name="department_id[]" class="select2 w-full rounded-lg border px-3 py-2"
                                    multiple required>
                                    @foreach ($department as $p)
                                        <option value="{{ $p->department_id }}">{{ $p->department_id }}</option>
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

            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

            <script>
                $(document).ready(function() {
                    let table = $('#usersTable').DataTable({
                        ajax: "{{ route('users.json') }}",
                        processing: true,
                        serverSide: false,
                        columns: [{
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

                    // ===== Clear Filter =====
                    $('#clearUserFilters').on('click', function() {
                        $('#filterCompany').val('');
                        $('#filterDepartment').val('');

                        table.column(3).search('');
                        table.column(4).search('');
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
            </script>

            <script>
                $(document).ready(function() {
                    $('.select2').select2({
                        placeholder: "Select Option",
                        allowClear: true,
                        width: '100%'
                    });
                });
            </script>

            <script>
                // 🔑 Login As (SweetAlert)
                $(document).on('click', '.impersonateBtn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    let userId = $(this).data('id');

                    Swal.fire({
                        title: "Login As User?",
                        text: "Anda akan login sebagai user ini.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Ya, Lanjutkan",
                        cancelButtonText: "Batal"
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
                                        title: "Berhasil!",
                                        text: res.message || "Login as berhasil.",
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
                                        title: "Gagal!",
                                        text: xhr.responseJSON?.message ||
                                            'Gagal login sebagai user.',
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
                        text: "Password user akan di-reset ke default: pakuwon1234#",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Ya, Reset",
                        cancelButtonText: "Batal"
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
                                        title: "Sukses!",
                                        text: res.message || "Password berhasil di-reset.",
                                        icon: "success",
                                        timer: 1500,
                                        showConfirmButton: false
                                    });

                                },
                                error: function(xhr) {
                                    Swal.fire({
                                        title: "Gagal!",
                                        text: xhr.responseJSON?.message ||
                                            "Reset password gagal.",
                                        icon: "error"
                                    });
                                }
                            });

                        }

                    });
                });
            </script>

        </div>
    </div>
</x-app-layout>
