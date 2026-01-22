<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'department' ? 'Departments' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-bold text-gray-800 dark:text-white">🏢 Department List</h1>
                <button id="addDepartmentBtn"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    + Add Department
                </button>
            </div>

            <table id="departmentTable" class="text-body w-full text-left text-sm rtl:text-right">
                <thead
                    class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                    <tr>
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

        <!-- Modal -->
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
    </div>
    <script>
        $(document).ready(function() {

            // Kalau sebelumnya sudah pernah di-init, destroy dulu
            if ($.fn.DataTable.isDataTable('#departmentTable')) {
                $('#departmentTable').DataTable().clear().destroy();
            }

            let table = $('#departmentTable').DataTable({
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
                        className: 'no-pointer'
                    },
                    {
                        data: 'status',
                        className: 'no-pointer',
                        render: function(data) {
                            return data === 'A' ?
                                '<span class="w-full max-w-25 bg-green-300/30 dark:bg-green-300 text-green-600 font-semibold px-4 py-2 text-center rounded">Active</span>' :
                                '<span class="w-full max-w-25 bg-red-300/30 dark:bg-red-300 text-red-600 font-semibold px-4 py-2 text-center rounded">Inactive</span>';
                        }
                    }
                ]
            });

            // Add
            $('#addDepartmentBtn').click(function() {
                $('#modalTitle').text("Add Department");
                $('#departmentForm')[0].reset();
                $('#id').val('');
                $('#departmentModal').removeClass('hidden');
            });

            // Edit
            $(document).on('click', '.editDepartmentBtn', function() {
                let id = $(this).data('id');

                $('#modalTitle').text("Loading...");
                $('#departmentModal').removeClass('hidden');

                $.get(`/department/${id}/edit`, function(c) {
                    $('#modalTitle').text("Edit Department");
                    $('#id').val(c.id);
                    $('#department_id').val(c.department_id);
                    $('#department_name').val(c.department_name);
                    $('#department_fin_id').val(c.department_fin_id);
                });
            });

            // Toggle status
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
                        table.ajax.reload(null, false);
                    }
                });
            });

            // Submit form (create / update)
            $('#departmentForm').submit(function(e) {
                e.preventDefault();
                let id = $('#id').val();
                let url = id ? `/department/${id}` : "{{ route('department.store') }}";
                let method = 'POST';
                let formData = new FormData(document.getElementById('departmentForm'));

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
                        $('#departmentModal').addClass('hidden');
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Gagal menyimpan data department');
                    }
                });
            });

            $('#closeModal').click(function() {
                $('#departmentModal').addClass('hidden');
            });
        });
    </script>
</x-app-layout>
