<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'department' ? 'Departments' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- ── TABS ────────────────────────────────────────────────────────────── --}}
        <div>
            <div class="flex gap-1 border-b border-gray-200 dark:border-gray-700 mb-0">
                <button type="button" id="tab-master"
                    class="deptTabBtn px-5 py-2.5 text-sm font-semibold rounded-t-lg border border-b-0 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400"
                    onclick="switchDeptTab('master')">
                    🏢 Master Department
                </button>
                <button type="button" id="tab-fin"
                    class="deptTabBtn px-5 py-2.5 text-sm font-semibold rounded-t-lg border border-b-0 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                    onclick="switchDeptTab('fin')">
                    💰 Department Fin
                </button>
                <button type="button" id="tab-hr"
                    class="deptTabBtn px-5 py-2.5 text-sm font-semibold rounded-t-lg border border-b-0 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                    onclick="switchDeptTab('hr')">
                    👥 Department & Division HR
                </button>
            </div>

            {{-- ── TAB 1: Master Department ───────────────────────────────────── --}}
            <div id="panel-master" class="rounded-b-xl rounded-tr-xl bg-white p-4 dark:bg-gray-800 border border-t-0 border-gray-200 dark:border-gray-700">
                <div class="mb-3 flex items-center justify-between">
                    <h1 class="text-base font-bold text-gray-800 dark:text-white">🏢 Master Department</h1>
                    <button id="addDepartmentBtn"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-700">
                        + Add Department
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table id="departmentTable" class="text-body w-full text-left text-sm rtl:text-right">
                        <thead class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                            <tr>
                                <th class="w-8 px-2 py-3 text-center"></th> {{-- responsive control --}}
                                <th class="w-32 px-4 py-3 text-center">Actions</th>
                                <th class="px-4 py-3 text-left">Department ID</th>
                                <th class="px-4 py-3 text-left">Department Name</th>
                                <th class="px-4 py-3 text-left">Department Finance</th>
                                <th class="w-32 px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            {{-- ── TAB 2: Department Fin ──────────────────────────────────────── --}}
            <div id="panel-fin" class="hidden rounded-b-xl rounded-tr-xl bg-white p-4 dark:bg-gray-800 border border-t-0 border-gray-200 dark:border-gray-700">
                <div class="mb-3 flex items-center justify-between">
                    <h1 class="text-base font-bold text-gray-800 dark:text-white">💰 Department Fin</h1>
                    <button id="addDepartmentFinBtn"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-700">
                        + Add Department Fin
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table id="departmentFinTable" class="text-body w-full text-left text-sm rtl:text-right">
                        <thead class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                            <tr>
                                <th class="w-8 px-2 py-3 text-center"></th> {{-- responsive control --}}
                                <th class="w-32 px-4 py-3 text-center">Actions</th>
                                <th class="px-4 py-3 text-left">Department Fin ID</th>
                                <th class="px-4 py-3 text-left">Department Name</th>
                                <th class="px-4 py-3 text-left">Company ID</th>
                                <th class="px-4 py-3 text-left">IFCA Dept Code</th>
                                <th class="px-4 py-3 text-left">Solomon Subaccount Dept</th>
                                <th class="w-32 px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            {{-- ── TAB 3: Department & Division HR ────────────────────────────── --}}
            <div id="panel-hr" class="hidden rounded-b-xl rounded-tr-xl bg-white p-4 dark:bg-gray-800 border border-t-0 border-gray-200 dark:border-gray-700">
                <div class="mb-3 flex items-center justify-between">
                    <h1 class="text-base font-bold text-gray-800 dark:text-white">👥 Department & Division HR</h1>
                    <button id="addDepartmentHrBtn"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-700">
                        + Add Department HR
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table id="departmentHrTable" class="text-body w-full text-left text-sm rtl:text-right">
                        <thead class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                            <tr>
                                <th class="w-8 px-2 py-3 text-center"></th> {{-- responsive control --}}
                                <th class="w-32 px-4 py-3 text-center">Actions</th>
                                <th class="px-4 py-3 text-left">Department ID</th>
                                <th class="px-4 py-3 text-left">Department Name</th>
                                <th class="px-4 py-3 text-left">Division</th>
                                <th class="w-32 px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal: Master Department -->
        <div id="departmentModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="modalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add Department</h2>
                <form id="departmentForm">
                    @csrf
                    <input type="hidden" id="id" name="id">

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Department ID</label>
                        <input type="text" id="department_id" name="department_id"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Department Name</label>
                        <input type="text" id="department_name" name="department_name"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Department Finance ID</label>
                        <input type="text" id="department_fin_id" name="department_fin_id"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Department Fin -->
        <div id="departmentFinModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="modalFinTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add Department Fin</h2>
                <form id="departmentFinForm">
                    @csrf
                    <input type="hidden" id="fin_id" name="id">

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Department Fin ID</label>
                        <input type="text" id="department_fin_id_field" name="department_fin_id"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Department Name</label>
                        <input type="text" id="fin_department_name" name="department_name"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Company ID</label>
                        <input type="text" id="fin_cpny_id" name="cpny_id"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">IFCA Dept Code</label>
                        <input type="text" id="fin_ifca_dept_cd" name="ifca_dept_cd"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Solomon Subaccount Dept</label>
                        <input type="text" id="fin_solomon_subaccount_dept" name="solomon_subaccount_dept"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeFinModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Department & Division HR -->
        <div id="departmentHrModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="modalHrTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add Department HR</h2>
                <form id="departmentHrForm">
                    @csrf
                    <input type="hidden" id="hr_id" name="id">

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Department ID</label>
                        <input type="text" id="hr_department_id" name="department_id"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Department Name</label>
                        <input type="text" id="hr_department_name" name="department_name"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Division</label>
                        <select id="hr_division_id" name="division_id"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                            <option value="">-- Select Division --</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->division_id }}">{{ $division->division_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeHrModal"
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

        const initedTabs = { master: false, fin: false, hr: false };
        const deptActiveClasses = 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400';
        const deptInactiveClasses = 'bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400';

        function switchDeptTab(tab) {
            const panels = { master: '#panel-master', fin: '#panel-fin', hr: '#panel-hr' };
            const btns   = { master: '#tab-master',   fin: '#tab-fin',   hr: '#tab-hr'   };

            Object.keys(panels).forEach(function(key) {
                const isActive = key === tab;
                $(panels[key]).toggleClass('hidden', !isActive);
                $(btns[key])
                    .toggleClass(deptActiveClasses, isActive)
                    .toggleClass(deptInactiveClasses, !isActive);
            });

            if (!initedTabs[tab]) {
                initedTabs[tab] = true;
                if (tab === 'master') initMasterTable();
                if (tab === 'fin') initFinTable();
                if (tab === 'hr') initHrTable();
            } else if (tab === 'master' && window.masterTable) {
                window.masterTable.columns.adjust().responsive.recalc();
            } else if (tab === 'fin' && window.finTable) {
                window.finTable.columns.adjust().responsive.recalc();
            } else if (tab === 'hr' && window.hrTable) {
                window.hrTable.columns.adjust().responsive.recalc();
            }
        }

        $(document).ready(function() {

            // =========================================================
            // Master Department
            // =========================================================
            function initMasterTable() {
                window.masterTable = $('#departmentTable').DataTable({
                    ajax: {
                        url: "{{ route('department.json') }}",
                        type: "GET",
                        dataSrc: 'data',
                        error: function(xhr) {
                            console.error('AJAX Error:', xhr.responseText);
                        }
                    },
                    processing: true,
                    serverSide: false,
                    autoWidth: false,
                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],
                    responsive: {
                        details: {
                            type: 'column',
                            target: 0
                        }
                    },
                    columnDefs: [
                        {
                            targets: 0,
                            className: 'dtr-control',
                            orderable: false,
                            searchable: false,
                            width: '28px',
                            defaultContent: ''
                        },
                        {
                            targets: 1,
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        },
                        {
                            targets: 5,
                            className: 'text-center'
                        }
                    ],
                    dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'Department',
                            className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                            exportOptions: {
                                columns: [2, 3, 4, 5]
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            text: '↓ CSV',
                            title: 'Department',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: [2, 3, 4, 5]
                            }
                        }
                    ],
                    columns: [
                        {
                            data: null,
                            defaultContent: ''
                        },
                        {
                            data: 'id',
                            render: function(data, type, row) {
                                return `
                                    <div class="flex justify-center space-x-2">
                                        <label class="switch">
                                            <input type="checkbox" class="toggleStatus"
                                                data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                            <span class="slider round"></span>
                                        </label>
                                        <button class="editDepartmentBtn bg-blue-500 text-white px-2 py-1 rounded"
                                            data-id="${data}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        },
                        {
                            data: 'department_id',
                            className: 'no-pointer'
                        },
                        {
                            data: 'department_name',
                            className: 'no-pointer'
                        },
                        {
                            data: 'department_fin_id',
                            className: 'no-pointer',
                            defaultContent: '-'
                        },
                        {
                            data: 'status',
                            className: 'no-pointer',
                            render: function(data) {
                                return data === 'A'
                                    ? '<span class="inline-block rounded bg-green-300/30 px-4 py-2 font-semibold text-green-600">Active</span>'
                                    : '<span class="inline-block rounded bg-red-300/30 px-4 py-2 font-semibold text-red-600">Inactive</span>';
                            }
                        }
                    ]
                });
            }

            $('#addDepartmentBtn').click(function() {
                $('#modalTitle').text("Add Department");
                $('#departmentForm')[0].reset();
                $('#id').val('');
                $('#departmentModal').removeClass('hidden');
            });

            $(document).on('click', '.editDepartmentBtn', function() {
                let id = $(this).data('id');

                $('#modalTitle').text("Loading...");
                $('#departmentModal').removeClass('hidden');
                showLoading();

                $.get(`/department/${id}/edit`, function(c) {
                    $('#modalTitle').text("Edit Department");
                    $('#id').val(c.id);
                    $('#department_id').val(c.department_id);
                    $('#department_name').val(c.department_name);
                    $('#department_fin_id').val(c.department_fin_id);
                    hideLoading();
                }).fail(function(xhr) {
                    hideLoading();
                    $('#departmentModal').addClass('hidden');

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data department'
                    });

                    console.error(xhr.responseText);
                });
            });

            $(document).on('change', '.toggleStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/department/${id}/toggle-status`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        status: newStatus
                    },
                    success: function() {
                        window.masterTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Gagal update status');
                        window.masterTable.ajax.reload(null, false);
                    }
                });
            });

            $('#departmentForm').submit(function(e) {
                e.preventDefault();

                let id = $('#id').val();
                let url = id ? `/department/${id}` : "{{ route('department.store') }}";
                let formData = new FormData(document.getElementById('departmentForm'));

                if (id) {
                    formData.append('_method', 'PUT');
                }

                showLoading();
                $('#departmentForm button[type="submit"]').prop('disabled', true);

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
                        hideLoading();
                        $('#departmentForm button[type="submit"]').prop('disabled', false);

                        $('#departmentModal').addClass('hidden');
                        $('#departmentForm')[0].reset();
                        $('#id').val('');
                        window.masterTable.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Department saved successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#departmentForm button[type="submit"]').prop('disabled', false);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal menyimpan data department'
                        });

                        console.error(xhr.responseText);
                    }
                });
            });

            $('#closeModal').click(function() {
                $('#departmentForm')[0].reset();
                $('#id').val('');
                $('#departmentModal').addClass('hidden');
            });

            // =========================================================
            // Department Fin
            // =========================================================
            function initFinTable() {
                window.finTable = $('#departmentFinTable').DataTable({
                    ajax: {
                        url: "{{ route('department.fin.json') }}",
                        type: "GET",
                        dataSrc: 'data',
                        error: function(xhr) {
                            console.error('AJAX Error:', xhr.responseText);
                        }
                    },
                    processing: true,
                    serverSide: false,
                    autoWidth: false,
                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],
                    responsive: {
                        details: {
                            type: 'column',
                            target: 0
                        }
                    },
                    columnDefs: [
                        {
                            targets: 0,
                            className: 'dtr-control',
                            orderable: false,
                            searchable: false,
                            width: '28px',
                            defaultContent: ''
                        },
                        {
                            targets: 1,
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        },
                        {
                            targets: 7,
                            className: 'text-center'
                        }
                    ],
                    dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'Department Fin',
                            className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                            exportOptions: {
                                columns: [2, 3, 4, 5, 6, 7]
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            text: '↓ CSV',
                            title: 'Department Fin',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: [2, 3, 4, 5, 6, 7]
                            }
                        }
                    ],
                    columns: [
                        {
                            data: null,
                            defaultContent: ''
                        },
                        {
                            data: 'id',
                            render: function(data, type, row) {
                                return `
                                    <div class="flex justify-center space-x-2">
                                        <label class="switch">
                                            <input type="checkbox" class="toggleFinStatus"
                                                data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                            <span class="slider round"></span>
                                        </label>
                                        <button class="editDepartmentFinBtn bg-blue-500 text-white px-2 py-1 rounded"
                                            data-id="${data}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        },
                        {
                            data: 'department_fin_id',
                            className: 'no-pointer'
                        },
                        {
                            data: 'department_name',
                            className: 'no-pointer'
                        },
                        {
                            data: 'cpny_id',
                            className: 'no-pointer',
                            defaultContent: '-'
                        },
                        {
                            data: 'ifca_dept_cd',
                            className: 'no-pointer',
                            defaultContent: '-'
                        },
                        {
                            data: 'solomon_subaccount_dept',
                            className: 'no-pointer',
                            defaultContent: '-'
                        },
                        {
                            data: 'status',
                            className: 'no-pointer',
                            render: function(data) {
                                return data === 'A'
                                    ? '<span class="inline-block rounded bg-green-300/30 px-4 py-2 font-semibold text-green-600">Active</span>'
                                    : '<span class="inline-block rounded bg-red-300/30 px-4 py-2 font-semibold text-red-600">Inactive</span>';
                            }
                        }
                    ]
                });
            }

            $('#addDepartmentFinBtn').click(function() {
                $('#modalFinTitle').text("Add Department Fin");
                $('#departmentFinForm')[0].reset();
                $('#fin_id').val('');
                $('#departmentFinModal').removeClass('hidden');
            });

            $(document).on('click', '.editDepartmentFinBtn', function() {
                let id = $(this).data('id');

                $('#modalFinTitle').text("Loading...");
                $('#departmentFinModal').removeClass('hidden');
                showLoading();

                $.get(`/department/fin/${id}/edit`, function(c) {
                    $('#modalFinTitle').text("Edit Department Fin");
                    $('#fin_id').val(c.id);
                    $('#department_fin_id_field').val(c.department_fin_id);
                    $('#fin_department_name').val(c.department_name);
                    $('#fin_cpny_id').val(c.cpny_id);
                    $('#fin_ifca_dept_cd').val(c.ifca_dept_cd);
                    $('#fin_solomon_subaccount_dept').val(c.solomon_subaccount_dept);
                    hideLoading();
                }).fail(function(xhr) {
                    hideLoading();
                    $('#departmentFinModal').addClass('hidden');

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data department finance'
                    });

                    console.error(xhr.responseText);
                });
            });

            $(document).on('change', '.toggleFinStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/department/fin/${id}/toggle-status`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        status: newStatus
                    },
                    success: function() {
                        window.finTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Gagal update status');
                        window.finTable.ajax.reload(null, false);
                    }
                });
            });

            $('#departmentFinForm').submit(function(e) {
                e.preventDefault();

                let id = $('#fin_id').val();
                let url = id ? `/department/fin/${id}` : "{{ route('department.fin.store') }}";
                let formData = new FormData(document.getElementById('departmentFinForm'));

                if (id) {
                    formData.append('_method', 'PUT');
                }

                showLoading();
                $('#departmentFinForm button[type="submit"]').prop('disabled', true);

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
                        hideLoading();
                        $('#departmentFinForm button[type="submit"]').prop('disabled', false);

                        $('#departmentFinModal').addClass('hidden');
                        $('#departmentFinForm')[0].reset();
                        $('#fin_id').val('');
                        window.finTable.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Department finance saved successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#departmentFinForm button[type="submit"]').prop('disabled', false);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal menyimpan data department finance'
                        });

                        console.error(xhr.responseText);
                    }
                });
            });

            $('#closeFinModal').click(function() {
                $('#departmentFinForm')[0].reset();
                $('#fin_id').val('');
                $('#departmentFinModal').addClass('hidden');
            });

            // =========================================================
            // Department & Division HR
            // =========================================================
            function initHrTable() {
                window.hrTable = $('#departmentHrTable').DataTable({
                    ajax: {
                        url: "{{ route('department.hr.json') }}",
                        type: "GET",
                        dataSrc: 'data',
                        error: function(xhr) {
                            console.error('AJAX Error:', xhr.responseText);
                        }
                    },
                    processing: true,
                    serverSide: false,
                    autoWidth: false,
                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],
                    responsive: {
                        details: {
                            type: 'column',
                            target: 0
                        }
                    },
                    columnDefs: [
                        {
                            targets: 0,
                            className: 'dtr-control',
                            orderable: false,
                            searchable: false,
                            width: '28px',
                            defaultContent: ''
                        },
                        {
                            targets: 1,
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        },
                        {
                            targets: 5,
                            className: 'text-center'
                        }
                    ],
                    dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'Department HR',
                            className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                            exportOptions: {
                                columns: [2, 3, 4, 5]
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            text: '↓ CSV',
                            title: 'Department HR',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: [2, 3, 4, 5]
                            }
                        }
                    ],
                    columns: [
                        {
                            data: null,
                            defaultContent: ''
                        },
                        {
                            data: 'id',
                            render: function(data, type, row) {
                                return `
                                    <div class="flex justify-center space-x-2">
                                        <label class="switch">
                                            <input type="checkbox" class="toggleHrStatus"
                                                data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                            <span class="slider round"></span>
                                        </label>
                                        <button class="editDepartmentHrBtn bg-blue-500 text-white px-2 py-1 rounded"
                                            data-id="${data}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        },
                        {
                            data: 'department_id',
                            className: 'no-pointer'
                        },
                        {
                            data: 'department_name',
                            className: 'no-pointer'
                        },
                        {
                            data: 'division_name',
                            className: 'no-pointer',
                            defaultContent: '-'
                        },
                        {
                            data: 'status',
                            className: 'no-pointer',
                            render: function(data) {
                                return data === 'A'
                                    ? '<span class="inline-block rounded bg-green-300/30 px-4 py-2 font-semibold text-green-600">Active</span>'
                                    : '<span class="inline-block rounded bg-red-300/30 px-4 py-2 font-semibold text-red-600">Inactive</span>';
                            }
                        }
                    ]
                });
            }

            $('#addDepartmentHrBtn').click(function() {
                $('#modalHrTitle').text("Add Department HR");
                $('#departmentHrForm')[0].reset();
                $('#hr_id').val('');
                $('#departmentHrModal').removeClass('hidden');
            });

            $(document).on('click', '.editDepartmentHrBtn', function() {
                let id = $(this).data('id');

                $('#modalHrTitle').text("Loading...");
                $('#departmentHrModal').removeClass('hidden');
                showLoading();

                $.get(`/department/hr/${id}/edit`, function(c) {
                    $('#modalHrTitle').text("Edit Department HR");
                    $('#hr_id').val(c.id);
                    $('#hr_department_id').val(c.department_id);
                    $('#hr_department_name').val(c.department_name);
                    $('#hr_division_id').val(c.division_id);
                    hideLoading();
                }).fail(function(xhr) {
                    hideLoading();
                    $('#departmentHrModal').addClass('hidden');

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data department HR'
                    });

                    console.error(xhr.responseText);
                });
            });

            $(document).on('change', '.toggleHrStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/department/hr/${id}/toggle-status`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        status: newStatus
                    },
                    success: function() {
                        window.hrTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Gagal update status');
                        window.hrTable.ajax.reload(null, false);
                    }
                });
            });

            $('#departmentHrForm').submit(function(e) {
                e.preventDefault();

                let id = $('#hr_id').val();
                let url = id ? `/department/hr/${id}` : "{{ route('department.hr.store') }}";
                let formData = new FormData(document.getElementById('departmentHrForm'));

                if (id) {
                    formData.append('_method', 'PUT');
                }

                showLoading();
                $('#departmentHrForm button[type="submit"]').prop('disabled', true);

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
                        hideLoading();
                        $('#departmentHrForm button[type="submit"]').prop('disabled', false);

                        $('#departmentHrModal').addClass('hidden');
                        $('#departmentHrForm')[0].reset();
                        $('#hr_id').val('');
                        window.hrTable.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Department HR saved successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#departmentHrForm button[type="submit"]').prop('disabled', false);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal menyimpan data department HR'
                        });

                        console.error(xhr.responseText);
                    }
                });
            });

            $('#closeHrModal').click(function() {
                $('#departmentHrForm')[0].reset();
                $('#hr_id').val('');
                $('#departmentHrModal').addClass('hidden');
            });

            // Initialize the first (visible) tab
            initMasterTable();
        });
    </script>
</x-app-layout>
