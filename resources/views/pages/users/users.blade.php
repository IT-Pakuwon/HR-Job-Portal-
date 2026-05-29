<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'users' ? 'Users' : '';
    @endphp
    <style>
        .select2-container--default .select2-selection--multiple {
            min-height: 44px !important;
            border-radius: 8px !important;
            border: 1px solid #d0d7de !important;
            padding: 4px !important;
        }

        .select2-container--default .select2-selection--single {
            height: 44px !important;
            border-radius: 8px !important;
            border: 1px solid #d0d7de !important;
        }

        .select2-container--default .select2-selection__rendered {
            line-height: 34px !important;
        }

        .select2-container--default .select2-selection__choice {
            background: #f6f8fa !important;
            border: 1px solid #d0d7de !important;
            border-radius: 6px !important;
            padding: 2px 8px !important;
            color: #24292f !important;
        }
    </style>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="mt-4 flex flex-col gap-4 rounded-lg bg-white p-4 dark:bg-gray-800">
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
                            <option value="{{ $c->cpny_id }}">{{ $c->cpny_id }} - {{ $c->cpny_name }}</option>
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
                            <option value="{{ $bu->business_unit_id }}">{{ $bu->business_unit_id }} -
                                {{ $bu->business_unit_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[200px] flex-1">
                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Filter Jabatan
                    </label>
                    <select id="filterJabatan"
                        class="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm dark:bg-gray-700">
                        <option value="">All Jabatan</option>
                        <option value="staff">staff</option>
                        <option value="manager">manager</option>
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
                            <th class="px-4 py-3 text-left font-medium">Jabatan</th>
                            <th class="w-32 px-4 py-3 text-center font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>

        <!-- Modal -->
        <div id="appModal" class="fixed inset-0 z-50 hidden">
            <div class="absolute inset-0 bg-slate-900/50"></div>

            <div class="relative flex h-full items-center justify-center p-4">
                <div
                    class="flex h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-lg bg-white shadow-2xl dark:bg-slate-800">

                    <!-- HEADER -->
                    <div
                        class="flex items-start justify-between border-b border-slate-200 px-8 py-6 dark:border-slate-700">
                        <div>
                            <h2 id="modalTitle" class="text-2xl font-semibold text-slate-900 dark:text-white">
                                Add User
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Create and manage user permissions and access scopes.
                            </p>
                        </div>

                        <button id="closeModal" type="button"
                            class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 dark:hover:bg-slate-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form id="appForm" class="flex min-h-0 flex-1 flex-col">
                        <input type="hidden" id="id">

                        <div class="flex-1 overflow-y-auto overflow-x-visible bg-slate-50/40 p-4">

                            <div class="space-y-6">

                                <!-- USER INFORMATION -->
                                <div
                                    class="rounded-lg border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
                                    <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                                        <h3 class="font-semibold text-slate-900 dark:text-white">
                                            User Information
                                        </h3>
                                        <p class="mt-1 text-sm text-slate-500">
                                            Basic profile information.
                                        </p>
                                    </div>

                                    <div class="grid gap-5 p-6 md:grid-cols-2">

                                        <div>
                                            <label
                                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                                Full Name
                                            </label>
                                            <input id="name" name="name" type="text" placeholder="John Doe"
                                                required
                                                class="h-11 w-full rounded-lg border border-slate-300 px-4 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                                        </div>

                                        <div>
                                            <label
                                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                                Email Address
                                            </label>
                                            <input id="email" name="email" type="email"
                                                placeholder="john.doe@example.com" required
                                                class="h-11 w-full rounded-lg border border-slate-300 px-4 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                                        </div>

                                        <div>
                                            <label
                                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                                NPK
                                            </label>
                                            <input id="npk" name="npk" type="text" placeholder="123456"
                                                class="h-11 w-full rounded-lg border border-slate-300 px-4 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                                        </div>

                                        <div>
                                            <label
                                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                                Position
                                            </label>
                                            <select name="jabatan" required
                                                class="h-11 w-full rounded-lg border border-slate-300 px-4 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                                                <option value="">Select Position</option>
                                                <option value="staff">Staff</option>
                                                <option value="manager">Manager</option>
                                            </select>
                                        </div>

                                    </div>
                                </div>

                                <!-- ACCESS SCOPE -->
                                <div
                                    class="rounded-lg border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
                                    <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                                        <h3 class="font-semibold text-slate-900 dark:text-white">
                                            Access Scope
                                        </h3>
                                        <p class="mt-1 text-sm text-slate-500">
                                            Configure organization access.
                                        </p>
                                    </div>

                                    <div class="grid gap-5 p-6 md:grid-cols-2">

                                        <div>
                                            <label class="mb-2 block text-sm font-medium">Company</label>
                                            <select name="cpny_id[]" class="select2 w-full" multiple
                                                data-placeholder="Search and select company access" required>
                                                <option></option>
                                                @foreach ($company as $c)
                                                    <option value="{{ $c->cpny_id }}">
                                                        {{ $c->cpny_id }} - {{ $c->cpny_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-sm font-medium">Department</label>
                                            <select name="department_id[]" class="select2 w-full" multiple
                                                data-placeholder="Search and select department access" required>
                                                <option></option>
                                                @foreach ($department as $d)
                                                    <option value="{{ $d->department_id }}">
                                                        {{ $d->department_id }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-sm font-medium">Division</label>
                                            <select name="division_id[]" class="select2 w-full" multiple
                                                data-placeholder="Search and select division access" required>
                                                <option></option>
                                                @foreach ($divisions as $d)
                                                    <option value="{{ $d->division_id }}">
                                                        {{ $d->division_id }} - {{ $d->division_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-sm font-medium">Business Unit</label>
                                            <select name="business_unit_id[]" class="select2 w-full" multiple
                                                data-placeholder="Search and select business unit access" required>
                                                <option></option>
                                                @foreach ($businessUnits as $bu)
                                                    <option value="{{ $bu->business_unit_id }}">
                                                        {{ $bu->business_unit_id }} - {{ $bu->business_unit_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                    </div>
                                </div>

                                <!-- SECURITY -->
                                <div
                                    class="rounded-lg border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
                                    <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                                        <h3 class="font-semibold text-slate-900 dark:text-white">
                                            Security & Permissions
                                        </h3>
                                        <p class="mt-1 text-sm text-slate-500">
                                            Configure roles and dashboard access.
                                        </p>
                                    </div>

                                    <div class="grid gap-5 p-6 md:grid-cols-2">

                                        <div>
                                            <label class="mb-2 block text-sm font-medium">
                                                Homepage Dashboard
                                            </label>

                                            <select id="homepage" name="homepage"
                                                class="h-11 w-full rounded-lg border border-slate-300 px-4 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                                                data-placeholder="Search and select homepage">

                                                <option value="">Select Homepage</option>

                                                @foreach ($screens as $screen)
                                                    <option value="{{ $screen->screen_id }}">
                                                        {{ $screen->screen_name }}
                                                    </option>
                                                @endforeach

                                            </select>
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-sm font-medium">
                                                User Type
                                            </label>

                                            <select name="role" required
                                                class="h-11 w-full rounded-lg border border-slate-300 px-4 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                                                <option value="">Select Type</option>
                                                <option value="user">User</option>
                                                <option value="admin">Admin</option>
                                            </select>
                                        </div>

                                        <div class="md:col-span-2">
                                            <label class="mb-2 block text-sm font-medium">
                                                Application Roles
                                            </label>

                                            <select name="role_ids[]" class="select2 w-full" multiple  data-placeholder="Search and assign application roles">
                                                <option></option>
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->role_id }}">
                                                        {{ $role->role_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                    </div>
                                </div>

                            </div>

                        </div>

                        <!-- FOOTER -->
                        <div
                            class="flex items-center justify-end gap-3 border-t border-slate-200 bg-white px-8 py-4 dark:border-slate-700 dark:bg-slate-800">

                            <button id="closeModalFooter" type="button"
                                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                                Cancel
                            </button>

                            <button type="submit"
                                class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white transition hover:bg-slate-800 dark:bg-indigo-600 dark:hover:bg-indigo-700">
                                Save User
                            </button>

                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <div id="saveOverlay" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/40">
        <div class="flex items-center gap-3 rounded-lg bg-white px-5 py-4 shadow-lg dark:bg-gray-800">
            <svg class="h-6 w-6 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Saving user...</span>
        </div>
    </div>

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
                        data: 'jabatan',
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
                    .column(5) // cpny_id
                    .search(val || '', false, false)
                    .draw();
            });

            // ===== Filter Department (kolom 4) =====
            $('#filterDepartment').on('change', function() {
                const val = $(this).val();

                table
                    .column(6) // department_id
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

            // ===== Filter Jabatan (kolom 8) =====
            $('#filterJabatan').on('change', function() {
                const val = $(this).val();
                table
                    .column(8) // jabatan
                    .search(val || '', false, false)
                    .draw();
            });



            // ===== Clear Filter =====
            $('#clearUserFilters').on('click', function() {

                // reset select2 UI + value
                $('#filterCompany').val(null).trigger('change');
                $('#filterDepartment').val(null).trigger('change');
                $('#filterBusinessUnit').val(null).trigger('change');
                $('#filterJabatan').val(null).trigger('change');

                // reset datatable filter untuk kolom yg benar
                table.column(5).search(''); // company
                table.column(6).search(''); // department
                table.column(7).search(''); // business unit
                table.column(8).search(''); // jabatan

                // reset global search juga kalau ada
                table.search('');

                table.draw();
            });



            $('#addAppBtn').click(function() {

                $('#modalTitle').text('Add User');

                $('#appForm')[0].reset();

                $('#id').val('');

                $('#homepage').val(null).trigger('change');

                $('select[name="role"]').val('').trigger('change');
                $('select[name="jabatan"]').val('').trigger('change');

                $('select[name="cpny_id[]"]').val(null).trigger('change');
                $('select[name="department_id[]"]').val(null).trigger('change');
                $('select[name="division_id[]"]').val(null).trigger('change');
                $('select[name="business_unit_id[]"]').val(null).trigger('change');
                $('select[name="role_ids[]"]').val(null).trigger('change');

                const $submitBtn = $('#appForm').find('button[type="submit"]');

                $submitBtn.data('loading', false);
                $submitBtn.prop('disabled', false).text('Save User');

                $('#closeModal').prop('disabled', false);

                $('#appModal').removeClass('hidden');
            });



            $(document).on('click', '.editAppBtn', function() {
                let appId = $(this).data('id');

                const $submitBtn = $('#appForm').find('button[type="submit"]');
                $submitBtn.data('loading', false);
                $submitBtn.prop('disabled', false).text('Save User');

                $('#closeModal').prop('disabled', false);

                $.get(`/users/${appId}/edit`, function(app) {

                    $('#modalTitle').text('Edit User');

                    $('#id').val(app.id);
                    $('#name').val(app.name);
                    $('#email').val(app.email);
                    $('#npk').val(app.npk);

                    $('#homepage').val(app.homepage).trigger('change');

                    $('select[name="jabatan"]').val(app.jabatan).trigger('change');
                    $('select[name="role"]').val(app.role).trigger('change');

                    $('select[name="cpny_id[]"]').val(app.cpny_id).trigger('change');
                    $('select[name="department_id[]"]').val(app.department_id).trigger('change');
                    $('select[name="division_id[]"]').val(app.division_id).trigger('change');
                    $('select[name="business_unit_id[]"]').val(app.business_unit_id).trigger(
                        'change');

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

                const $form = $('#appForm');
                const $submitBtn = $form.find('button[type="submit"]');
                const $closeBtn = $('#closeModal');
                const $overlay = $('#saveOverlay');

                if ($submitBtn.data('loading') === true) {
                    return;
                }

                let appId = $('#id').val();
                let url = appId ? `/users/${appId}` : "{{ route('users.store') }}";
                let method = 'POST';

                let formData = new FormData(document.getElementById('appForm'));

                if (appId) {
                    formData.append('_method', 'PUT');
                }

                $submitBtn.data('loading', true);
                $submitBtn.prop('disabled', true).text('Saving User...');
                $closeBtn.prop('disabled', true);
                $overlay.removeClass('hidden').addClass('flex');

                $.ajax({
                    url: url,
                    type: method,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $('#appModal').addClass('hidden');
                        table.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Successfully!',
                            text: appId ? 'User berhasil diupdate.' :
                                'User berhasil ditambahkan.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        let message = 'Gagal menyimpan data user.';

                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            message = Object.values(xhr.responseJSON.errors)
                                .flat()
                                .join('\n');
                        } else if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Failed!',
                            text: message
                        });
                    },
                    complete: function() {
                        $submitBtn.data('loading', false);
                        $submitBtn.prop('disabled', false).text('Save User');
                        $closeBtn.prop('disabled', false);
                        $overlay.removeClass('flex').addClass('hidden');
                    }
                });
            });

            $('#closeModal, #closeModalFooter').on('click', function() {

                const $submitBtn = $('#appForm').find('button[type="submit"]');

                if ($submitBtn.data('loading') === true) return;

                $('#appModal').addClass('hidden');
            });
        });


        $(document).ready(function() {

            $('.select2').each(function () {
                $(this).select2({
                    width: '100%',
                    allowClear: true,
                    closeOnSelect: false,
                    dropdownParent: $('#appModal'),
                    placeholder: $(this).data('placeholder') || 'Search and select'
                });
            });

            $('#homepage').select2({
                width: '100%',
                allowClear: true,
                dropdownParent: $('#appModal'),
                placeholder: 'Search and select homepage'
            });

            $('#filterCompany').select2({
                placeholder: 'All Company',
                allowClear: true,
                width: '100%'
            });

            $('#filterDepartment').select2({
                placeholder: 'All Department',
                allowClear: true,
                width: '100%'
            });

            $('#filterBusinessUnit').select2({
                placeholder: 'All Business Unit',
                allowClear: true,
                width: '100%'
            });

            $('#filterJabatan').select2({
                placeholder: 'All Position',
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
