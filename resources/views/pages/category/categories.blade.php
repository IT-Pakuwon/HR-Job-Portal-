<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-bold text-gray-800 dark:text-white">📂 Ms Category List</h1>
                <button id="addCategoryBtn"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    + Add Category
                </button>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="categoryTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
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
        </div>
        {{-- Modal --}}
        <div id="categoryModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-3xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="categoryModalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">
                    Add Category
                </h2>

                <form id="categoryForm">
                    @csrf
                    <input type="hidden" id="id" name="id">

                    {{-- ROW 1 : Doctype + Category ID --}}
                    <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        {{-- DOCTYPE --}}
                        <div>
                            <label class="mb-1 block text-gray-700 dark:text-white">Doctype</label>
                            <select id="doctype" name="doctype"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                <option value="">choose </option>
                                @foreach ($doctypes as $dt)
                                    <option value="{{ $dt->doctype }}">{{ $dt->doctype }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- CATEGORY ID --}}
                        <div>
                            <label class="mb-1 block text-gray-700 dark:text-white">Category ID</label>
                            <select id="categoryid" name="categoryid"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700 dark:text-white" required>
                                <option value="">Select Category </option>
                                <option value="condition">Condition</option>
                                <option value="type">Type</option>
                                <option value="wotype">WO Type</option>
                                <option value="worequest">WO Request</option>
                            </select>
                        </div>

                    </div>

                    {{-- ROW 2 : Category Name + Groups (SEBELAHAN) --}}
                    <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        {{-- CATEGORY NAME --}}
                        <div>
                            <label class="mb-1 block text-gray-700 dark:text-white">Category Name</label>
                            <input type="text" id="category_name" name="category_name"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        {{-- GROUPS --}}
                        <div>
                            <label class="mb-1 block text-gray-700 dark:text-white">Groups</label>
                            <input type="text" id="groups" name="groups"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                    </div>

                    {{-- ROW 3 : Username + Type --}}
                    <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        {{-- USERNAME --}}
                        <div>
                            <label class="mb-1 block text-gray-700 dark:text-white">Username</label>
                            <select id="username" name="username"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                                <option value="">choose </option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->username }}">
                                        {{ $u->username }} — {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- TYPE --}}
                        <div>
                            <label class="mb-1 block text-gray-700 dark:text-white">Type</label>
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
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {

            // DataTable
            let table = $('#categoryTable').DataTable({
                ajax: {
                    url: "{{ route('categories.json') }}",
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
                        className: 'col-actions',
                        render: function(data, type, row) {
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
                    {
                        data: 'doctype'
                    },
                    {
                        data: 'categoryid'
                    },
                    {
                        data: 'category_name'
                    },
                    {
                        data: 'groups'
                    },
                    {
                        data: 'username'
                    },
                    {
                        data: 'type'
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
            $('#doctype, #username').select2({
                width: '100%',
                dropdownParent: $('#categoryModal')
            });

            // Open modal Add
            $('#addCategoryBtn').click(function() {
                $('#categoryModalTitle').text("Add Category");
                $('#categoryForm')[0].reset();
                $('#id').val('');
                $('#doctype').val('').trigger('change');
                $('#username').val('').trigger('change');
                $('#categoryModal').removeClass('hidden');
            });

            // Close modal
            $('#closeCategoryModal').click(function() {
                $('#categoryModal').addClass('hidden');
            });

            // Edit
            $(document).on('click', '.editCategoryBtn', function() {
                let id = $(this).data('id');

                $('#categoryModalTitle').text("Loading...");
                $('#categoryForm')[0].reset();
                $('#id').val(id);
                $('#categoryModal').removeClass('hidden');

                $.get(`/categories/${id}/edit`, function(data) {
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
            $(document).on('change', '.toggleStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/categories/${id}/toggle-status`,
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
            $('#categoryForm').submit(function(e) {
                e.preventDefault();

                let id = $('#id').val();
                let url = id ? `/categories/${id}` : "{{ route('categories.store') }}";
                let method = 'POST';
                let formData = new FormData(document.getElementById('categoryForm'));

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
                        $('#categoryModal').addClass('hidden');
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        let msg = 'Gagal menyimpan data category';

                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = 'Mohon periksa input:\n';
                            Object.values(xhr.responseJSON.errors).forEach(function(arr) {
                                msg += '- ' + arr.join(', ') + '\n';
                            });
                        }

                        alert(msg);
                    }
                });
            });
        });
    </script>
</x-app-layout>
