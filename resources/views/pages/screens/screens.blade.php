<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'screens' ? 'Screens' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="mb-8 sm:flex sm:items-center sm:justify-between"></div>

        <div class="grid">
            <style>
                table.dataTable {
                    width: 100% !important;
                }

                #screensTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    align-items: center;
                }

                #screensTable_filter input {
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
            </style>

            <div class="mt-6 rounded-xl bg-white p-4 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">🪟 Screen List</h2>
                    <button id="addScreenBtn" class="rounded-lg bg-indigo-500 px-5 py-2 text-white">
                        + Add Screen
                    </button>
                </div>

                <table id="screensTable" class="w-full border-collapse">
                    <thead class="bg-white dark:bg-gray-700">
                        <tr>
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

            {{-- Modal --}}
            <div id="screenModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
                <div class="relative w-full max-w-lg rounded-lg bg-white p-6 dark:bg-gray-700">
                    <h2 id="screenModalTitle" class="mb-4 text-xl font-bold text-gray-800 dark:text-white">
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
                                <option value="">-- Select Application --</option>
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

            <script>
                $(document).ready(function() {
                    let table = $('#screensTable').DataTable({
                        ajax: {
                            url: "{{ route('screens.json') }}",
                            type: "GET",
                            dataSrc: 'data'
                        },
                        processing: true,
                        serverSide: false,
                        columns: [{
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

                        $.get(`/screens/${id}/edit`, function(data) {
                            $('#screenModalTitle').text("Edit Screen");
                            $('#id').val(data.id);
                            $('#screen_id').val(data.screen_id);
                            $('#screen_name').val(data.screen_name);
                            $('#application_id').val(data.application_id);
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
                                $('#screenModal').addClass('hidden');
                                table.ajax.reload();
                            },
                            error: function(xhr) {
                                console.error(xhr.responseText);
                                alert('Gagal menyimpan screen');
                            }
                        });
                    });

                    $('#closeScreenModal').click(function() {
                        $('#screenModal').addClass('hidden');
                    });
                });
            </script>
        </div>
    </div>
</x-app-layout>
