<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'companies' ? 'Companies' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="mb-8 sm:flex sm:items-center sm:justify-between"></div>

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

                #companiesTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    align-items: center;
                }

                #companiesTable_filter label {
                    margin-right: 2px;
                }

                #companiesTable_filter input {
                    width: auto;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }

                #companiesTable_wrapper {
                    margin-top: 20px;
                    width: 100%;
                }

                #companiesTable td {
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                #companiesTable th,
                #companiesTable td {
                    padding: 10px;
                    max-width: 200px;
                }

                #companiesTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }

                #companiesTable_length select {
                    width: 80px;
                    padding: 5px;
                }

                #companiesTable_info {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                .dataTables_paginate {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                #companiesTable tbody tr td {
                    padding: 8px 8px;
                    line-height: 1.6;
                }

                #companiesTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }

                #companiesTable tbody tr:hover {
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

                input:checked+.slider {
                    background-color: #4CAF50;
                }

                input:checked+.slider:before {
                    transform: translateX(18px);
                }

                #companiesTable th:nth-child(1),
                #companiesTable td:nth-child(1) {
                    width: 120px;
                    text-align: center;
                }

                #companiesTable th:nth-child(6),
                #companiesTable td:nth-child(6) {
                    width: 120px;
                    text-align: center;
                }
            </style>

            <div class="mt-6 flex flex-col gap-6 rounded-xl bg-white p-6 dark:bg-gray-800">
                <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">🏢 Company List</h2>
                    <button id="addCompanyBtn"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-base font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                        + Add Company
                    </button>
                </div>

                <table id="companiesTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th class="w-32 px-4 py-3 text-center">Actions</th>
                            <th class="px-4 py-3 text-left">Company ID</th>
                            <th class="px-4 py-3 text-left">Company Name</th>
                            <th class="px-4 py-3 text-left">City</th>
                            <th class="px-4 py-3 text-left">Province</th>
                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Modal -->
            <div id="companyModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
                <div class="relative w-full max-w-xl rounded-lg bg-white p-6 dark:bg-gray-700">
                    <h2 id="modalTitle" class="mb-4 text-xl font-bold text-gray-800 dark:text-white">Add Company</h2>
                    <form id="companyForm">
                        <input type="hidden" id="id" name="id">

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Company ID</label>
                                <input type="text" id="cpny_id" name="cpny_id"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                            </div>
                            <div class="mb-3 md:col-span-1">
                                <label class="block text-gray-700 dark:text-white">Company Name</label>
                                <input type="text" id="cpny_name" name="cpny_name"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                            </div>

                            <div class="mb-3 md:col-span-2">
                                <label class="block text-gray-700 dark:text-white">Address Line 1</label>
                                <input type="text" id="address_line1" name="address_line1"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>
                            <div class="mb-3 md:col-span-2">
                                <label class="block text-gray-700 dark:text-white">Address Line 2</label>
                                <input type="text" id="address_line2" name="address_line2"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>

                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">City</label>
                                <input type="text" id="city" name="city"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>
                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Province</label>
                                <input type="text" id="province" name="province"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>

                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Postal Code</label>
                                <input type="text" id="postalcode" name="postalcode"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>
                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Phone</label>
                                <input type="text" id="phone" name="phone"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>

                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Fax</label>
                                <input type="text" id="fax" name="fax"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>
                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Tax Registration</label>
                                <input type="text" id="tax_registration" name="tax_registration"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>

                            <div class="mb-3 md:col-span-2">
                                <label class="block text-gray-700 dark:text-white">Tax Address</label>
                                <input type="text" id="tax_address_line" name="tax_address_line"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>

                            <div class="mb-3 md:col-span-2">
                                <label class="block text-gray-700 dark:text-white">Warehouse Note</label>
                                <textarea id="warehouse_note" name="warehouse_note" class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700"
                                    rows="2"></textarea>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end space-x-2">
                            <button type="button" id="closeModal"
                                class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                            <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                $(document).ready(function() {
                    let table = $('#companiesTable').DataTable({
                        ajax: "{{ route('companies.json') }}",
                        processing: true,
                        serverSide: false,
                        columns: [{
                                data: null,
                                defaultContent: ''
                            }, {
                                data: 'id',
                                render: function(data, type, row) {
                                    return `
                                        <div class="flex justify-center space-x-2">
                                            <label class="switch">
                                                <input type="checkbox" class="toggleStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                                <span class="slider round"></span>
                                            </label>
                                            <button class="editCompanyBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    `;
                                }
                            },
                            {
                                data: 'cpny_id',
                                className: 'no-pointer'
                            },
                            {
                                data: 'cpny_name',
                                className: 'no-pointer'
                            },
                            {
                                data: 'city',
                                className: 'no-pointer'
                            },
                            {
                                data: 'province',
                                className: 'no-pointer'
                            },
                            {
                                data: 'status',
                                className: 'no-pointer',
                                render: function(data) {
                                    return data === 'A' ?
                                        '<span class="w-full max-w-25 bg-green-300/30 dark:bg-green-300 text-green-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Active</span>' :
                                        '<span class="w-full max-w-25 bg-red-300/30 dark:bg-red-300 text-red-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Inactive</span>';
                                }
                            }
                        ]
                    });

                    // Add
                    $('#addCompanyBtn').click(function() {
                        $('#modalTitle').text("Add Company");
                        $('#companyForm')[0].reset();
                        $('#id').val('');
                        $('#companyModal').removeClass('hidden');
                    });

                    // Edit
                    $(document).on('click', '.editCompanyBtn', function() {
                        let id = $(this).data('id');
                        $.get(`/companies/${id}/edit`, function(c) {
                            $('#modalTitle').text("Edit Company");
                            $('#id').val(c.id);
                            $('#cpny_id').val(c.cpny_id);
                            $('#cpny_name').val(c.cpny_name);
                            $('#address_line1').val(c.address_line1);
                            $('#address_line2').val(c.address_line2);
                            $('#city').val(c.city);
                            $('#province').val(c.province);
                            $('#postalcode').val(c.postalcode);
                            $('#phone').val(c.phone);
                            $('#fax').val(c.fax);
                            $('#tax_registration').val(c.tax_registration);
                            $('#tax_address_line').val(c.tax_address_line);
                            $('#warehouse_note').val(c.warehouse_note);

                            $('#companyModal').removeClass('hidden');
                        });
                    });

                    // Toggle status
                    $(document).on('change', '.toggleStatus', function() {
                        let id = $(this).data('id');
                        let newStatus = $(this).is(':checked') ? 'A' : 'X';

                        $.ajax({
                            url: `/companies/${id}/toggle-status`,
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
                    $('#companyForm').submit(function(e) {
                        e.preventDefault();
                        let id = $('#id').val();
                        let url = id ? `/companies/${id}` : "{{ route('companies.store') }}";
                        let method = 'POST';
                        let formData = new FormData(document.getElementById('companyForm'));

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
                                $('#companyModal').addClass('hidden');
                                table.ajax.reload();
                            },
                            error: function(xhr) {
                                console.error(xhr.responseText);
                                alert('Gagal menyimpan data company');
                            }
                        });
                    });

                    $('#closeModal').click(function() {
                        $('#companyModal').addClass('hidden');
                    });
                });
            </script>
        </div>
    </div>
</x-app-layout>
