<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'business-units' ? 'Business Units' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-bold text-gray-800 dark:text-white">🏢 Business Unit List</h1>
                <button id="addBusinessUnitBtn"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    + Add Business Unit
                </button>
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-white">Company</label>
                    <select id="filterCompany" class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        <option value="">All Company</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->cpny_id }}">
                                {{ $company->cpny_id }} - {{ $company->cpny_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="button" id="btnFilter"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Filter
                    </button>

                    <button type="button" id="btnResetFilter"
                        class="rounded-lg bg-gray-500 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-600">
                        Reset
                    </button>
                </div>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="businessUnitTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th class="w-32 px-4 py-3 text-center">Actions</th>
                            <th class="px-4 py-3 text-left">Business Unit ID</th>
                            <th class="px-4 py-3 text-left">Company</th>
                            <th class="px-4 py-3 text-left">Business Unit Name</th>
                            <th class="px-4 py-3 text-left">IFCA Entity</th>
                            <th class="px-4 py-3 text-left">Solomon Company</th>
                            <th class="px-4 py-3 text-left">Solomon Allocation</th>
                            <th class="px-4 py-3 text-left">Integration Type</th>
                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <div id="businessUnitModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
            <div class="relative w-full max-w-3xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="modalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add Business Unit</h2>

                <form id="businessUnitForm">
                    @csrf
                    <input type="hidden" id="id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Business Unit ID</label>
                            <input type="text" id="business_unit_id" name="business_unit_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Company</label>
                            <select id="cpny_id" name="cpny_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                <option value="">-- Select Company --</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->cpny_id }}">
                                        {{ $company->cpny_id }} - {{ $company->cpny_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Business Unit Name</label>
                            <input type="text" id="business_unit_name" name="business_unit_name"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">IFCA Entity Code</label>
                            <input type="text" id="ifca_entity_cd" name="ifca_entity_cd"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Integration Type</label>
                            <select id="integration_type" name="integration_type"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                                <option value="">-- Select Type --</option>
                                <option value="IFCA">IFCA</option>
                                <option value="SOLOMON">SOLOMON</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Solomon Company ID</label>
                            <input type="text" id="solomon_cpny_id" name="solomon_cpny_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Solomon Allocation Code</label>
                            <input type="text" id="solomon_allocation_cd" name="solomon_allocation_cd"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
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
            if ($.fn.DataTable.isDataTable('#businessUnitTable')) {
                $('#businessUnitTable').DataTable().clear().destroy();
            }

            let table = $('#businessUnitTable').DataTable({
                ajax: {
                    url: "{{ route('business-units.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.cpny_id = $('#filterCompany').val();
                    },
                    dataSrc: "data",
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
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false,
                        searchable: false
                    },
                    {
                        targets: 1,
                        orderable: false,
                        searchable: false
                    }
                ],
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'Business Unit',
                        className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                        exportOptions: {
                            columns: [2, 3, 4, 5, 6, 7, 8, 9]
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '↓ CSV',
                        title: 'Business Unit',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: [2, 3, 4, 5, 6, 7, 8, 9]
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
                                    <button class="editBusinessUnitBtn bg-blue-500 text-white px-2 py-1 rounded"
                                        data-id="${data}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'business_unit_id',
                        className: 'no-pointer'
                    },
                    {
                        data: null,
                        className: 'no-pointer',
                        render: function(data, type, row) {
                            return `${row.cpny_id ?? '-'}${row.cpny_name ? ' - ' + row.cpny_name : ''}`;
                        }
                    },
                    {
                        data: 'business_unit_name',
                        className: 'no-pointer'
                    },
                    {
                        data: 'ifca_entity_cd',
                        className: 'no-pointer',
                        defaultContent: '-'
                    },
                    {
                        data: 'solomon_cpny_id',
                        className: 'no-pointer',
                        defaultContent: '-'
                    },
                    {
                        data: 'solomon_allocation_cd',
                        className: 'no-pointer',
                        defaultContent: '-'
                    },
                    {
                        data: 'integration_type',
                        className: 'no-pointer',
                        defaultContent: '-'
                    },
                    {
                        data: 'status',
                        className: 'no-pointer text-center',
                        render: function(data) {
                            return data === 'A'
                                ? '<span class="inline-block rounded bg-green-300/30 px-4 py-2 font-semibold text-green-600">Active</span>'
                                : '<span class="inline-block rounded bg-red-300/30 px-4 py-2 font-semibold text-red-600">Inactive</span>';
                        }
                    }
                ]
            });

            $('#btnFilter').click(function() {
                showLoading();
                table.ajax.reload(function() {
                    hideLoading();
                }, true);
            });

            $('#btnResetFilter').click(function() {
                $('#filterCompany').val('');
                showLoading();
                table.ajax.reload(function() {
                    hideLoading();
                }, true);
            });

            $('#addBusinessUnitBtn').click(function() {
                $('#modalTitle').text("Add Business Unit");
                $('#businessUnitForm')[0].reset();
                $('#id').val('');
                $('#businessUnitModal').removeClass('hidden').addClass('flex');
            });

            $(document).on('click', '.editBusinessUnitBtn', function() {
                let id = $(this).data('id');

                showLoading();

                $.get(`/business-units/${id}/edit`, function(c) {
                    $('#modalTitle').text("Edit Business Unit");
                    $('#id').val(c.id);
                    $('#business_unit_id').val(c.business_unit_id);
                    $('#cpny_id').val(c.cpny_id);
                    $('#business_unit_name').val(c.business_unit_name);
                    $('#ifca_entity_cd').val(c.ifca_entity_cd);
                    $('#solomon_cpny_id').val(c.solomon_cpny_id);
                    $('#solomon_allocation_cd').val(c.solomon_allocation_cd);
                    $('#integration_type').val(c.integration_type);

                    $('#businessUnitModal').removeClass('hidden').addClass('flex');
                    hideLoading();
                }).fail(function(xhr) {
                    hideLoading();

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data business unit'
                    });

                    console.error(xhr.responseText);
                });
            });

            $(document).on('change', '.toggleStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/business-units/${id}/toggle-status`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        status: newStatus
                    },
                    success: function() {
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Gagal update status');
                        table.ajax.reload(null, false);
                    }
                });
            });

            $('#businessUnitForm').submit(function(e) {
                e.preventDefault();

                let id = $('#id').val();
                let url = id ? `/business-units/${id}` : "{{ route('business-units.store') }}";
                let formData = new FormData(document.getElementById('businessUnitForm'));

                if (id) {
                    formData.append('_method', 'PUT');
                }

                showLoading();
                $('#businessUnitForm button[type="submit"]').prop('disabled', true);

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
                        $('#businessUnitForm button[type="submit"]').prop('disabled', false);

                        $('#businessUnitModal').addClass('hidden').removeClass('flex');
                        $('#businessUnitForm')[0].reset();
                        $('#id').val('');
                        table.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Business unit saved successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#businessUnitForm button[type="submit"]').prop('disabled', false);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal menyimpan data business unit'
                        });

                        console.error(xhr.responseText);
                    }
                });
            });

            $('#closeModal').click(function() {
                $('#businessUnitForm')[0].reset();
                $('#id').val('');
                $('#businessUnitModal').addClass('hidden').removeClass('flex');
            });
        });
    </script>
</x-app-layout>