<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'applications' ? 'Applications' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-bold text-gray-800 dark:text-white">Application List</h1>
                <button id="addAppBtn"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    + Add Application
                </button>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="applicationsTable"class="text-body w-full text-left  text-sm  rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th class="w-32 px-4 py-3 text-center">Actions</th>
                            <th class="px-4 py-3 text-left">Application ID</th>
                            <th class="px-4 py-3 text-left">Application Name</th>
                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        {{-- Modal --}}
        <div id="appModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="appModalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">
                    Add Application
                </h2>
                <form id="appForm">
                    @csrf
                    <input type="hidden" id="id">

                    <div class="mb-4 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 dark:text-white">Application ID</label>
                            <input type="text" id="application_id" name="application_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">Application Name</label>
                            <input type="text" id="application_name" name="application_name"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" id="closeAppModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            let table = $('#applicationsTable').DataTable({
                ajax: {
                    url: "{{ route('applications.json') }}",
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
                        title: 'Application',
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
                        title: 'Application',
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
                                            <button class="editAppBtn bg-blue-500 text-white px-2 py-1 rounded"
                                                data-id="${data}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    `;
                        }
                    },
                    {
                        data: 'application_id'
                    },
                    {
                        data: 'application_name'
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
            $('#addAppBtn').click(function() {
                $('#appModalTitle').text("Add Application");
                $('#appForm')[0].reset();
                $('#id').val('');
                $('#appModal').removeClass('hidden');
            });

            // Edit
            $(document).on('click', '.editAppBtn', function() {
                let id = $(this).data('id');

                $('#appModalTitle').text("Loading...");
                $('#appModal').removeClass('hidden');

                $.get(`/applications/${id}/edit`, function(data) {
                    $('#appModalTitle').text("Edit Application");
                    $('#id').val(data.id);
                    $('#application_id').val(data.application_id);
                    $('#application_name').val(data.application_name);
                });
            });

            // Toggle status
            $(document).on('change', '.toggleStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/applications/${id}/toggle-status`,
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

            // Submit form
            $('#appForm').submit(function(e) {
                e.preventDefault();

                let id = $('#id').val();
                let url = id ? `/applications/${id}` : "{{ route('applications.store') }}";
                let method = 'POST';
                let formData = new FormData(document.getElementById('appForm'));

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
                        $('#appModal').addClass('hidden');
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Gagal menyimpan application');
                    }
                });
            });

            $('#closeAppModal').click(function() {
                $('#appModal').addClass('hidden');
            });
        });
    </script>
</x-app-layout>
