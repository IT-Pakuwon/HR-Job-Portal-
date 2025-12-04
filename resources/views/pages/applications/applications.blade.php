<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'applications' ? 'Applications' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="mb-8 sm:flex sm:items-center sm:justify-between"></div>

        <div class="grid">
            <style>
                table.dataTable { width: 100% !important; }
                #applicationsTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    align-items: center;
                }
                #applicationsTable_filter input {
                    width: auto;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }
                .switch {
                    position: relative;
                    display: inline-block;
                    width: 40px;
                    height: 22px;
                }
                .switch input { opacity: 0; width: 0; height: 0; }
                .slider {
                    position: absolute;
                    cursor: pointer;
                    top: 0; left: 0; right: 0; bottom: 0;
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
                input:checked + .slider { background-color: #4CAF50; }
                input:checked + .slider:before { transform: translateX(18px); }
            </style>

            <div class="mt-6 rounded-xl bg-white p-4 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">🧩 Application List</h2>
                    <button id="addAppBtn" class="rounded-lg bg-indigo-500 px-5 py-2 text-white">
                        + Add Application
                    </button>
                </div>

                <table id="applicationsTable" class="w-full border-collapse">
                    <thead class="bg-white dark:bg-gray-700">
                        <tr>
                            <th class="w-32 px-4 py-3 text-center">Actions</th>
                            <th class="px-4 py-3 text-left">Application ID</th>
                            <th class="px-4 py-3 text-left">Application Name</th>
                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            {{-- Modal --}}
            <div id="appModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
                <div class="relative w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-700">
                    <h2 id="appModalTitle" class="mb-4 text-xl font-bold text-gray-800 dark:text-white">
                        Add Application
                    </h2>
                    <form id="appForm">
                        @csrf
                        <input type="hidden" id="id">

                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-white">Application ID</label>
                            <input type="text" id="application_id" name="application_id"
                                   class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-white">Application Name</label>
                            <input type="text" id="application_name" name="application_name"
                                   class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="flex justify-end space-x-2">
                            <button type="button" id="closeAppModal"
                                    class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                            <button type="submit"
                                    class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                $(document).ready(function () {
                    let table = $('#applicationsTable').DataTable({
                        ajax: {
                            url: "{{ route('applications.json') }}",
                            type: "GET",
                            dataSrc: 'data'
                        },
                        processing: true,
                        serverSide: false,
                        columns: [
                            {
                                data: 'id',
                                render: function (data, type, row) {
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
                            { data: 'application_id' },
                            { data: 'application_name' },
                            {
                                data: 'status',
                                className: 'text-center',
                                render: function (data) {
                                    return data === 'A'
                                        ? '<span class="bg-green-300/30 text-green-600 font-semibold px-4 py-1 rounded">Active</span>'
                                        : '<span class="bg-red-300/30 text-red-600 font-semibold px-4 py-1 rounded">Inactive</span>';
                                }
                            }
                        ]
                    });

                    // Add
                    $('#addAppBtn').click(function () {
                        $('#appModalTitle').text("Add Application");
                        $('#appForm')[0].reset();
                        $('#id').val('');
                        $('#appModal').removeClass('hidden');
                    });

                    // Edit
                    $(document).on('click', '.editAppBtn', function () {
                        let id = $(this).data('id');

                        $('#appModalTitle').text("Loading...");
                        $('#appModal').removeClass('hidden');

                        $.get(`/applications/${id}/edit`, function (data) {
                            $('#appModalTitle').text("Edit Application");
                            $('#id').val(data.id);
                            $('#application_id').val(data.application_id);
                            $('#application_name').val(data.application_name);
                        });
                    });

                    // Toggle status
                    $(document).on('change', '.toggleStatus', function () {
                        let id = $(this).data('id');
                        let newStatus = $(this).is(':checked') ? 'A' : 'X';

                        $.ajax({
                            url: `/applications/${id}/toggle-status`,
                            type: 'PUT',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: { status: newStatus },
                            success: function () {
                                table.ajax.reload(null, false);
                            }
                        });
                    });

                    // Submit form
                    $('#appForm').submit(function (e) {
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
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function () {
                                $('#appModal').addClass('hidden');
                                table.ajax.reload();
                            },
                            error: function (xhr) {
                                console.error(xhr.responseText);
                                alert('Gagal menyimpan application');
                            }
                        });
                    });

                    $('#closeAppModal').click(function () {
                        $('#appModal').addClass('hidden');
                    });
                });
            </script>
        </div>
    </div>
</x-app-layout>
