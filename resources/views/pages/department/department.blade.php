<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'department' ? 'Departments' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="mb-8 sm:flex sm:items-center sm:justify-between"></div>

        <div class="grid">
            <style>
                .no-border { border: none !important; }
                .grid { width: 100%; }

                table.dataTable { width: 100% !important; }
                .dataTables_wrapper { width: 100%; }

                #departmentTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    align-items: center;
                }
                #departmentTable_filter label { margin-right: 2px; }
                #departmentTable_filter input {
                    width: auto;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }
                #departmentTable_wrapper {
                    margin-top: 20px;
                    width: 100%;
                }
                #departmentTable td {
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
                #departmentTable th,
                #departmentTable td {
                    padding: 10px;
                    max-width: 200px;
                }
                #departmentTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }
                #departmentTable_length select {
                    width: 80px;
                    padding: 5px;
                }
                #departmentTable_info {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }
                .dataTables_paginate {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }
                #departmentTable tbody tr td {
                    padding: 8px 8px;
                    line-height: 1.6;
                }
                #departmentTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }
                #departmentTable tbody tr:hover {
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
                input:checked + .slider {
                    background-color: #4CAF50;
                }
                input:checked + .slider:before {
                    transform: translateX(18px);
                }

                #departmentTable th:nth-child(1),
                #departmentTable td:nth-child(1) {
                    width: 120px;
                    text-align: center;
                }
                #departmentTable th:nth-child(5),
                #departmentTable td:nth-child(5) {
                    width: 120px;
                    text-align: center;
                }
            </style>

            <div class="mt-6 rounded-xl bg-white p-4 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">🏢 Department List</h2>
                    <button id="addDepartmentBtn" class="rounded-lg bg-indigo-500 px-5 py-2 text-white">
                        + Add Department
                    </button>
                </div>

                <table id="departmentTable" class="w-full table-fixed border-collapse">
                    <thead class="bg-white dark:bg-gray-700">
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
                <div class="relative w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-700">
                    <h2 id="modalTitle" class="mb-4 text-xl font-bold text-gray-800 dark:text-white">Add Department</h2>
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
                            <button type="submit"
                                    class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                        </div>
                    </form>
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
                            error: function(xhr){
                                console.error('AJAX Error:', xhr.responseText);
                            }
                        },
                        processing: true,
                        serverSide: false,
                        columns: [
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
                            { data: 'department_id',   className: 'no-pointer' },
                            { data: 'department_name', className: 'no-pointer' },
                            { data: 'department_fin_id', className: 'no-pointer' },
                            {
                                data: 'status',
                                className: 'no-pointer',
                                render: function(data) {
                                    return data === 'A'
                                        ? '<span class="w-full max-w-25 bg-green-300/30 dark:bg-green-300 text-green-600 font-semibold px-4 py-2 text-center rounded">Active</span>'
                                        : '<span class="w-full max-w-25 bg-red-300/30 dark:bg-red-300 text-red-600 font-semibold px-4 py-2 text-center rounded">Inactive</span>';
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
                            data: { status: newStatus },
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
        </div>
    </div>
</x-app-layout>
