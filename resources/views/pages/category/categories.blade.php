<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="mb-8 sm:flex sm:items-center sm:justify-between"></div>

        <div class="grid">
            <style>
                .grid { width: 100%; }
                table.dataTable { width: 100% !important; }
                .dataTables_wrapper { width: 100%; }

                #categoryTable th,
                #categoryTable td {
                    white-space: normal !important;
                    word-wrap: break-word;
                    word-break: break-word;
                    vertical-align: top;
                }

                #categoryTable th.col-actions,
                #categoryTable td.col-actions {
                    width: 80px;
                    text-align: center;
                }

                #categoryTable th.col-status,
                #categoryTable td.col-status {
                    width: 80px;
                    text-align: center;
                }

                #categoryTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    align-items: center;
                }
                #categoryTable_filter input {
                    width: auto;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }

                #categoryTable_length {
                    display: flex;
                    justify-content: flex-start;
                }
                #categoryTable_length select {
                    width: 80px;
                    padding: 5px;
                }

                #categoryTable tbody tr:hover {
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
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">📂 Ms Category List</h2>
                    <button id="addCategoryBtn" class="rounded-lg bg-indigo-500 px-5 py-2 text-white">
                        + Add Category
                    </button>
                </div>

                <table id="categoryTable" class="w-full table-fixed border-collapse">
                    <thead class="bg-white dark:bg-gray-700">
                        <tr>
                            <th class="col-actions px-4 py-3">Actions</th>
                            <th class="px-4 py-3 text-left">Doctype</th>
                            <th class="px-4 py-3 text-left">Category ID</th>
                            <th class="px-4 py-3 text-left">Category Name</th>
                            <th class="px-4 py-3 text-left">Groups</th>
                            <th class="px-4 py-3 text-left">Username</th>
                            <th class="px-4 py-3 text-left">Type</th>
                            <th class="col-status px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            {{-- Modal --}}
            {{-- Modal --}}
            <div id="categoryModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
                <div class="relative w-full max-w-3xl rounded-lg bg-white p-6 dark:bg-gray-700">
                    <h2 id="categoryModalTitle" class="mb-4 text-xl font-bold text-gray-800 dark:text-white">
                        Add Category
                    </h2>

                    <form id="categoryForm">
                        @csrf
                        <input type="hidden" id="id" name="id">

                        {{-- ROW 1 : Doctype + Category ID --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            {{-- DOCTYPE --}}
                            <div>
                                <label class="block text-gray-700 dark:text-white mb-1">Doctype</label>
                                <select id="doctype" name="doctype"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                    <option value="">-- choose --</option>
                                    @foreach($doctypes as $dt)
                                        <option value="{{ $dt->doctype }}">{{ $dt->doctype }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- CATEGORY ID --}}
                            <div>
                                <label class="block text-gray-700 dark:text-white mb-1">Category ID</label>
                                <input type="text" id="categoryid" name="categoryid"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                            </div>
                        </div>

                        {{-- ROW 2 : Category Name + Groups (SEBELAHAN) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            {{-- CATEGORY NAME --}}
                            <div>
                                <label class="block text-gray-700 dark:text-white mb-1">Category Name</label>
                                <input type="text" id="category_name" name="category_name"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700"
                                    required>
                            </div>

                            {{-- GROUPS --}}
                            <div>
                                <label class="block text-gray-700 dark:text-white mb-1">Groups</label>
                                <input type="text" id="groups" name="groups"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            </div>
                        </div>

                        {{-- ROW 3 : Username + Type --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            {{-- USERNAME --}}
                            <div>
                                <label class="block text-gray-700 dark:text-white mb-1">Username</label>
                                <select id="username" name="username"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                                    <option value="">-- choose --</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->username }}">
                                            {{ $u->username }} — {{ $u->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- TYPE --}}
                            <div>
                                <label class="block text-gray-700 dark:text-white mb-1">Type</label>
                                <input type="text" id="type" name="type"
                                    class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700"
                                    placeholder="mis: Header, Detail, dll">
                            </div>
                        </div>

                        {{-- BUTTONS --}}
                        <div class="mt-4 flex justify-end space-x-2">
                            <button type="button" id="closeCategoryModal"
                                class="rounded-lg bg-red-500 px-4 py-2 text-white">
                                Cancel
                            </button>
                            <button type="submit"
                                class="rounded-lg bg-blue-500 px-4 py-2 text-white">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>


            {{-- Select2 --}}
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

            <script>
                $(document).ready(function () {

                    // DataTable
                    let table = $('#categoryTable').DataTable({
                        ajax: {
                            url: "{{ route('categories.json') }}",
                            type: "GET",
                            dataSrc: 'data'
                        },
                        processing: true,
                        serverSide: false,
                        columns: [
                            {
                                data: 'id',
                                className: 'col-actions',
                                render: function (data, type, row) {
                                    return `
                                        <div class="flex justify-center space-x-2">
                                            <label class="switch">
                                                <input type="checkbox" class="toggleStatus"
                                                    data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                                <span class="slider round"></span>
                                            </label>
                                            <button class="editCategoryBtn bg-blue-500 text-white px-2 py-1 rounded"
                                                data-id="${data}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    `;
                                }
                            },
                            { data: 'doctype' },
                            { data: 'categoryid' },
                            { data: 'category_name' },
                            { data: 'groups' },
                            { data: 'username' },
                            { data: 'type' },
                            {
                                data: 'status',
                                className: 'col-status',
                                render: function (data) {
                                    return data === 'A'
                                        ? '<span class="bg-green-300/30 text-green-600 font-semibold px-4 py-1 rounded">Active</span>'
                                        : '<span class="bg-red-300/30 text-red-600 font-semibold px-4 py-1 rounded">Inactive</span>';
                                }
                            }
                        ]
                    });

                    // Select2 untuk dropdown di modal
                    $('#doctype, #username').select2({
                        width: '100%',
                        dropdownParent: $('#categoryModal')
                    });

                    // Open modal Add
                    $('#addCategoryBtn').click(function () {
                        $('#categoryModalTitle').text("Add Category");
                        $('#categoryForm')[0].reset();
                        $('#id').val('');
                        $('#doctype').val('').trigger('change');
                        $('#username').val('').trigger('change');
                        $('#categoryModal').removeClass('hidden');
                    });

                    // Close modal
                    $('#closeCategoryModal').click(function () {
                        $('#categoryModal').addClass('hidden');
                    });

                    // Edit
                    $(document).on('click', '.editCategoryBtn', function () {
                        let id = $(this).data('id');

                        $('#categoryModalTitle').text("Loading...");
                        $('#categoryForm')[0].reset();
                        $('#id').val(id);
                        $('#categoryModal').removeClass('hidden');

                        $.get(`/categories/${id}/edit`, function (data) {
                            $('#categoryModalTitle').text("Edit Category");

                            $('#doctype').val(data.doctype).trigger('change');
                            $('#categoryid').val(data.categoryid);
                            $('#category_name').val(data.category_name);
                            $('#groups').val(data.groups);
                            $('#username').val(data.username).trigger('change');
                            $('#type').val(data.type);
                        });
                    });

                    // Toggle status
                    $(document).on('change', '.toggleStatus', function () {
                        let id = $(this).data('id');
                        let newStatus = $(this).is(':checked') ? 'A' : 'X';

                        $.ajax({
                            url: `/categories/${id}/toggle-status`,
                            type: 'PUT',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: { status: newStatus },
                            success: function () {
                                table.ajax.reload(null, false);
                            }
                        });
                    });

                    // Submit (create / update)
                    $('#categoryForm').submit(function (e) {
                        e.preventDefault();

                        let id     = $('#id').val();
                        let url    = id ? `/categories/${id}` : "{{ route('categories.store') }}";
                        let method = 'POST';
                        let formData = new FormData(document.getElementById('categoryForm'));

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
                                $('#categoryModal').addClass('hidden');
                                table.ajax.reload();
                            },
                            error: function (xhr) {
                                console.error(xhr.responseText);
                                let msg = 'Gagal menyimpan data category';

                                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                                    msg = 'Mohon periksa input:\n';
                                    Object.values(xhr.responseJSON.errors).forEach(function (arr) {
                                        msg += '- ' + arr.join(', ') + '\n';
                                    });
                                }

                                alert(msg);
                            }
                        });
                    });
                });
            </script>
        </div>
    </div>
</x-app-layout>
