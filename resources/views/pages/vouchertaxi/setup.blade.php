<x-app-layout>

    <div class="max-w-9xl mx-auto w-full p-2">

        <div
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">

            <div
                class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">

                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                        Voucher Taxi Category
                    </h2>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Manage voucher taxi categories.
                    </p>
                </div>

                <button type="button" onclick="openCreateCategoryModal()"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">

                    <span>＋</span>

                    <span>
                        Add Category
                    </span>

                </button>

            </div>

            <div class="overflow-x-auto p-5">

                <table id="categoryTable" class="display w-full border-collapse text-sm">

                    <thead>

                        <tr>
                            <th>No</th>
                            <th>Category ID</th>
                            <th>Category Name</th>
                            <th>Group</th>
                            <th>Status</th>
                            <th class="text-right">Action</th>
                        </tr>

                    </thead>

                </table>

            </div>

        </div>

        {{-- CREATE CATEGORY --}}
        <div id="createCategoryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Add Category
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Create voucher taxi category.
                        </p>
                    </div>

                    <button type="button" onclick="closeCreateCategoryModal()"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                <form id="createCategoryForm">

                    @csrf

                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Category ID
                            </label>

                            <input type="text" name="categoryid"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm uppercase shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                required>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Category Name
                            </label>

                            <input type="text" name="category_name"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm uppercase shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                required>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Group
                            </label>

                            <input type="text" name="groups"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm uppercase shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="closeCreateCategoryModal()"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700">

                            Save Category

                        </button>

                    </div>

                </form>

            </div>

        </div>

        {{-- EDIT CATEGORY --}}
        <div id="editCategoryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Edit Category
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Update voucher taxi category.
                        </p>
                    </div>

                    <button type="button" onclick="closeEditCategoryModal()"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                <form id="editCategoryForm">

                    @csrf

                    <input type="hidden" id="edit_category_id">

                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Category ID
                            </label>

                            <input type="text" id="edit_categoryid" name="categoryid"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm uppercase shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                required>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Category Name
                            </label>

                            <input type="text" id="edit_category_name" name="category_name"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm uppercase shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                required>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Group
                            </label>

                            <input type="text" id="edit_groups" name="groups"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm uppercase shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="closeEditCategoryModal()"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-emerald-700">

                            Update Category

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <script>
        let categoryTable;

        $(document).ready(function() {

            categoryTable = $('#categoryTable').DataTable({

                processing: true,
                serverSide: true,
                responsive: true,

                ajax: "{{ route('vouchertaxi.setup.category.json') }}",

                dom: '<"dt-toolbar"l B f>rtip',

                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'Voucher_Taxi_Category_List',
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
                        title: 'Voucher_Taxi_Category_List',
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
                        data: 'DT_RowIndex',
                        searchable: false,
                        orderable: false,
                        width: '5%'
                    },
                    {
                        data: 'categoryid',
                        name: 'categoryid'
                    },
                    {
                        data: 'category_name',
                        name: 'category_name'
                    },
                    {
                        data: 'groups',
                        name: 'groups'
                    },
                    {
                        data: 'status',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'action',
                        searchable: false,
                        orderable: false,
                        className: 'text-right'
                    }
                ],

                pageLength: 10,
                autoWidth: false,

                order: [
                    [1, 'asc']
                ],

                language: {
                    search: '',
                    searchPlaceholder: 'Search category...'
                }

            });

        });

        function openCreateCategoryModal() {

            $('#createCategoryModal')
                .removeClass('hidden')
                .addClass('flex');

        }

        function closeCreateCategoryModal() {

            $('#createCategoryModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#createCategoryForm')[0].reset();

        }

        function closeEditCategoryModal() {

            $('#editCategoryModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#editCategoryForm')[0].reset();

        }

        $('#createCategoryForm').submit(function(e) {

            e.preventDefault();

            $.ajax({

                url: "{{ route('vouchertaxi.setup.category.store') }}",

                type: 'POST',

                data: $(this).serialize(),

                beforeSend: function() {

                    Swal.fire({
                        title: 'Saving...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false
                    });

                    closeCreateCategoryModal();

                    categoryTable.ajax.reload(null, false);

                },

                error: function(xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ??
                            'Something went wrong'
                    });

                }

            });

        });

        function editCategory(id) {

            $.ajax({

                url: `/vouchertaxi/setup/category/find/${id}`,

                type: 'GET',

                success: function(response) {

                    const data = response.data;

                    $('#edit_category_id').val(data.id);
                    $('#edit_categoryid').val(data.categoryid);
                    $('#edit_category_name').val(data.category_name);
                    $('#edit_groups').val(data.groups);

                    $('#editCategoryModal')
                        .removeClass('hidden')
                        .addClass('flex');

                }

            });

        }

        $('#editCategoryForm').submit(function(e) {

            e.preventDefault();

            let id = $('#edit_category_id').val();

            $.ajax({

                url: `/vouchertaxi/setup/category/update/${id}`,

                type: 'POST',

                data: $(this).serialize(),

                beforeSend: function() {

                    Swal.fire({
                        title: 'Updating...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false
                    });

                    closeEditCategoryModal();

                    categoryTable.ajax.reload(null, false);

                },

                error: function(xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ??
                            'Something went wrong'
                    });

                }

            });

        });

        function updateCategoryStatus(id, status, element = null) {

            Swal.fire({

                title: 'Are you sure?',
                text: 'Category status will be updated.',
                icon: 'warning',

                showCancelButton: true,

                confirmButtonText: 'Yes, Update'

            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({

                        url: `/vouchertaxi/setup/category/status/${id}`,

                        type: 'POST',

                        data: {
                            _token: '{{ csrf_token() }}',
                            status: status
                        },

                        beforeSend: function() {

                            Swal.fire({
                                title: 'Updating...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                        },

                        success: function(response) {

                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                timer: 1800,
                                showConfirmButton: false
                            });

                            categoryTable.ajax.reload(null, false);

                        },

                        error: function(xhr) {

                            if (element) {
                                element.checked = !element.checked;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message ??
                                    'Something went wrong'
                            });

                        }

                    });

                } else {

                    if (element) {
                        element.checked = !element.checked;
                    }

                }

            });

        }
    </script>

</x-app-layout>
