<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-bold text-gray-800 dark:text-white">🔐 Group Access Specific List</h1>
                <button id="addGroupAccBtn"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    + Add Group Access
                </button>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="groupAccTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th class="col-actions px-4 py-3">Actions</th>
                            <th class="px-4 py-3 text-left">Group Access ID</th>
                            <th class="px-4 py-3 text-left">Group Access Name</th>
                            <th class="px-4 py-3 text-left">Username</th>
                            <th class="px-4 py-3 text-left">Department</th>
                            <th class="px-4 py-3 text-left">Parameter Access ID</th>
                            <th class="col-status px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        {{-- Modal --}}
        <div id="groupAccModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-3xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="groupAccModalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">
                    Add Group Access
                </h2>

                <form id="groupAccForm">
                    @csrf
                    <input type="hidden" id="id" name="id">

                    {{-- ROW 1 : Group Access ID + Group Access Name --}}
                    <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        {{-- GROUP ACCESS ID --}}
                        <div>
                            <label class="mb-1 block text-gray-700 dark:text-white">Group Access ID</label>
                            <select id="group_access_id" name="group_access_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                <option value="">choose </option>
                                @foreach ($groupAccessOptions as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- GROUP ACCESS NAME --}}
                        <div>
                            <label class="mb-1 block text-gray-700 dark:text-white">Group Access Name</label>
                            <input type="text" id="group_access_name" name="group_access_name"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                    </div>

                    {{-- ROW 2 : Username + Department --}}
                    <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        {{-- USERNAME --}}
                        <div>
                            <label class="mb-1 block text-gray-700 dark:text-white">Username</label>
                            <select id="username" name="username"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                <option value="">choose </option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->username }}">
                                        {{ $u->username }} — {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- DEPARTMENT --}}
                        <div>
                            <label class="mb-1 block text-gray-700 dark:text-white">Department</label>
                            <select id="department_id" name="department_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                                <option value="">choose </option>
                                @foreach ($departments as $d)
                                    <option value="{{ $d->department_id }}">
                                        {{ $d->department_id }} — {{ $d->department_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- ROW 3 : Parameter Access ID --}}
                    <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-gray-700 dark:text-white">Parameter Access ID</label>
                            <input type="text" id="parameter_access_id" name="parameter_access_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                    </div>

                    {{-- BUTTONS --}}
                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeGroupAccModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">
                            Save
                        </button>
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function showLoading() {
            $('#loadingOverlay').removeClass('hidden');
        }

        function hideLoading() {
            $('#loadingOverlay').addClass('hidden');
        }
        $(document).ready(function() {

            // DataTable
            let table = $('#groupAccTable').DataTable({
                ajax: {
                    url: "{{ route('group_acc_specific.json') }}",
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
                        target: 0
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
                        title: 'Group Access Specific',
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
                        title: 'Group Access Specific',
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
                        className: 'col-actions',
                        render: function(data, type, row) {
                            return `
                                        <div class="flex justify-center space-x-2">
                                            <label class="switch">
                                                <input type="checkbox" class="toggleStatus"
                                                    data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                                <span class="slider round"></span>
                                            </label>
                                            <button class="editGroupAccBtn bg-blue-500 text-white px-2 py-1 rounded"
                                                data-id="${data}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    `;
                        }
                    },
                    {
                        data: 'group_access_id'
                    },
                    {
                        data: 'group_access_name'
                    },
                    {
                        data: 'username'
                    },
                    {
                        data: 'department_id'
                    },
                    {
                        data: 'parameter_access_id'
                    },
                    {
                        data: 'status',
                        className: 'col-status',
                        render: function(data) {
                            return data === 'A' ?
                                '<span class="bg-green-300/30 text-green-600 font-semibold px-4 py-1 rounded">Active</span>' :
                                '<span class="bg-red-300/30 text-red-600 font-semibold px-4 py-1 rounded">Inactive</span>';
                        }
                    }
                ]
            });

            // Select2 untuk dropdown di modal
            $('#group_access_id, #username, #department_id').select2({
                width: '100%',
                dropdownParent: $('#groupAccModal')
            });

            // Open modal Add
            $('#addGroupAccBtn').click(function() {
                $('#groupAccModalTitle').text("Add Group Access");
                $('#groupAccForm')[0].reset();
                $('#id').val('');
                $('#group_access_id').val('').trigger('change');
                $('#username').val('').trigger('change');
                $('#department_id').val('').trigger('change');
                $('#groupAccModal').removeClass('hidden');
            });

            // Close modal
            $('#closeGroupAccModal').click(function() {
                $('#groupAccForm')[0].reset();
                $('#id').val('');
                $('#group_access_id').val('').trigger('change');
                $('#username').val('').trigger('change');
                $('#department_id').val('').trigger('change');
                $('#groupAccModal').addClass('hidden');
            });

            // Edit
            $(document).on('click', '.editGroupAccBtn', function() {
                let id = $(this).data('id');

                $('#groupAccModalTitle').text("Loading...");
                $('#groupAccForm')[0].reset();
                $('#id').val(id);
                $('#groupAccModal').removeClass('hidden');
                showLoading();

                $.get(`/group-acc-specific/${id}/edit`, function(data) {
                    $('#groupAccModalTitle').text("Edit Group Access");

                    $('#group_access_id').val(data.group_access_id).trigger('change');
                    $('#group_access_name').val(data.group_access_name);
                    $('#username').val(data.username).trigger('change');
                    $('#department_id').val(data.department_id).trigger('change');
                    $('#parameter_access_id').val(data.parameter_access_id);

                    hideLoading();
                }).fail(function(xhr) {
                    hideLoading();
                    $('#groupAccModal').addClass('hidden');

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data group access'
                    });

                    console.error(xhr.responseText);
                });
            });

            // Toggle status
            $(document).on('change', '.toggleStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/group-acc-specific/${id}/toggle-status`,
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

            // Submit (create / update)
            $('#groupAccForm').submit(function(e) {
                e.preventDefault();

                let id = $('#id').val();
                let url = id ? `/group-acc-specific/${id}` : "{{ route('group_acc_specific.store') }}";
                let method = 'POST';
                let formData = new FormData(document.getElementById('groupAccForm'));

                if (id) {
                    formData.append('_method', 'PUT');
                }

                showLoading();
                $('#groupAccForm button[type="submit"]').prop('disabled', true);

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
                        $('#groupAccForm button[type="submit"]').prop('disabled', false);

                        $('#groupAccModal').addClass('hidden');
                        $('#groupAccForm')[0].reset();
                        $('#id').val('');
                        $('#group_access_id').val('').trigger('change');
                        $('#username').val('').trigger('change');
                        $('#department_id').val('').trigger('change');
                        table.ajax.reload();

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Group Access Specific saved successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#groupAccForm button[type="submit"]').prop('disabled', false);

                        let msg = 'Gagal menyimpan data group access';

                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors)
                                .map(arr => arr.join(', '))
                                .join('\n');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });

                        console.error(xhr.responseText);
                    }
                });
            });
        });
    </script>
</x-app-layout>
