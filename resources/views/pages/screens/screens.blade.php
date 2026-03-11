<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'screens' ? 'Screens' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-bold text-gray-800 dark:text-white">🪟 Screen List</h1>
                <button id="addScreenBtn"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    + Add Screen
                </button>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="screensTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th class="w-32 px-4 py-3 text-center">Actions</th>
                            <th class="px-4 py-3 text-left">Screen ID</th>
                            <th class="px-4 py-3 text-left">Screen Name</th>
                            <th class="px-4 py-3 text-left">Application ID</th>
                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>

        {{-- Modal --}}
        <div id="screenModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-lg rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="screenModalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">
                    Add Screen
                </h2>
                <form id="screenForm">
                    @csrf
                    <input type="hidden" id="id">

                    <div class="mb-4 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 dark:text-white">Screen ID</label>
                            <input type="text" id="screen_id" name="screen_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">Screen Name</label>
                            <input type="text" id="screen_name" name="screen_name"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-white">Application</label>
                        <select id="application_id" name="application_id"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                            <option value="">Select Application </option>
                            @foreach ($applications as $app)
                                <option value="{{ $app->application_id }}">
                                    {{ $app->application_id }} - {{ $app->application_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" id="closeScreenModal"
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
            let table = $('#screensTable').DataTable({
                ajax: {
                    url: "{{ route('screens.json') }}",
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
                                            <button class="editScreenBtn bg-blue-500 text-white px-2 py-1 rounded"
                                                data-id="${data}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    `;
                        }
                    },
                    {
                        data: 'screen_id'
                    },
                    {
                        data: 'screen_name'
                    },
                    {
                        data: 'application_id'
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

            // Add
            $('#addScreenBtn').click(function() {
                $('#screenModalTitle').text("Add Screen");
                $('#screenForm')[0].reset();
                $('#id').val('');
                $('#screenModal').removeClass('hidden');
            });

            // Edit
            $(document).on('click', '.editScreenBtn', function() {
                let id = $(this).data('id');

                $('#screenModalTitle').text("Loading...");
                $('#screenModal').removeClass('hidden');
                showLoading();

                $.get(`/screens/${id}/edit`, function(data) {
                    $('#screenModalTitle').text("Edit Screen");
                    $('#id').val(data.id);
                    $('#screen_id').val(data.screen_id);
                    $('#screen_name').val(data.screen_name);
                    $('#application_id').val(data.application_id);
                    hideLoading();
                }).fail(function(xhr) {
                    hideLoading();
                    $('#screenModal').addClass('hidden');

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load screen data'
                    });

                    console.error(xhr.responseText);
                });
            });

            // Toggle status
            $(document).on('change', '.toggleStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/screens/${id}/toggle-status`,
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

            // Submit
            $('#screenForm').submit(function(e) {
                e.preventDefault();

                let id = $('#id').val();
                let url = id ? `/screens/${id}` : "{{ route('screens.store') }}";
                let method = 'POST';
                let formData = new FormData(document.getElementById('screenForm'));

                if (id) {
                    formData.append('_method', 'PUT');
                }

                showLoading();
                $('#screenForm button[type="submit"]').prop('disabled', true);

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
                        $('#screenForm button[type="submit"]').prop('disabled', false);

                        $('#screenModal').addClass('hidden');
                        table.ajax.reload();

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Screen saved successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#screenForm button[type="submit"]').prop('disabled', false);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal menyimpan screen'
                        });

                        console.error(xhr.responseText);
                    }
                });
            });

            $('#closeScreenModal').click(function() {
                $('#screenForm')[0].reset();
                $('#id').val('');
                $('#screenModal').addClass('hidden');
            });
        });
    </script>

</x-app-layout>
