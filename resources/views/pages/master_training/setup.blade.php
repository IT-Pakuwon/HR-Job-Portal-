<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div
            class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

            {{-- Header --}}
            <div
                class="flex flex-col gap-4 border-b border-gray-100 px-5 py-3 dark:border-white/[0.06] sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('mastertraining') }}"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white"
                            title="Back to Master Training List">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </a>
                        <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">⚙️ Training
                            Setup</h2>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Manage training places and training categories (doctype = TE).
                    </p>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="flex items-center gap-1 border-b border-gray-100 px-5 pt-3 dark:border-white/[0.06]">
                <button type="button" data-setup-tab="places"
                    class="setup-tab rounded-t-lg border-b-2 border-transparent px-4 py-2.5 text-sm font-semibold text-gray-500 transition hover:text-gray-800 dark:text-gray-400 dark:hover:text-white">
                    Training Places
                </button>
                <button type="button" data-setup-tab="categories"
                    class="setup-tab rounded-t-lg border-b-2 border-transparent px-4 py-2.5 text-sm font-semibold text-gray-500 transition hover:text-gray-800 dark:text-gray-400 dark:hover:text-white">
                    Training Categories
                </button>
            </div>

            {{-- TAB: Places --}}
            <div id="tab-places" class="setup-panel p-5">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Master list of training locations used by training schedules.
                    </p>
                    <button type="button" id="addPlaceBtn"
                        class="inline-flex h-10 items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-4 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add Place
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table id="placesTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="px-4 py-3 text-left font-medium">No</th>
                                <th class="px-4 py-3 text-left font-medium">Places ID</th>
                                <th class="px-4 py-3 text-left font-medium">Places Name</th>
                                <th class="px-4 py-3 text-left font-medium">Address</th>
                                <th class="px-4 py-3 text-left font-medium">Status</th>
                                <th class="px-4 py-3 text-left font-medium">Created At</th>
                                <th class="px-4 py-3 text-right font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            {{-- TAB: Categories --}}
            <div id="tab-categories" class="setup-panel hidden p-5">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Training categories. All records are forced to doctype = TE.
                    </p>
                    <button type="button" id="addCategoryBtn"
                        class="inline-flex h-10 items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-4 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add Category
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table id="categoriesTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="px-4 py-3 text-left font-medium">No</th>
                                <th class="px-4 py-3 text-left font-medium">Category ID</th>
                                <th class="px-4 py-3 text-left font-medium">Category Name</th>
                                <th class="px-4 py-3 text-left font-medium">Status</th>
                                <th class="px-4 py-3 text-left font-medium">Created At</th>
                                <th class="px-4 py-3 text-right font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ============ PLACE MODAL (create / edit) ============ --}}
        <div id="placeModal"
            class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-2xl dark:bg-gray-800">
                <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <div>
                        <h2 id="placeModalTitle" class="text-base font-bold text-gray-900 dark:text-white">
                            Add Training Place
                        </h2>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            Fill in the training place details below.
                        </p>
                    </div>
                    <button type="button" id="closePlaceModalX"
                        class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="placeForm" class="px-6 py-5">
                    @csrf
                    <input type="hidden" id="place_id" name="id">

                    <div class="space-y-5">
                        <div>
                            <label for="places_name"
                                class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Places Name
                            </label>
                            <input type="text" id="places_name" name="places_name"
                                placeholder="e.g. Wisma Barunajaya Auditorium"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white"
                                required>
                        </div>

                        <div>
                            <label for="places_address"
                                class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Address
                            </label>
                            <textarea id="places_address" name="places_address" rows="3"
                                placeholder="Optional address of the training place"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white"></textarea>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Status
                            </label>
                            <div class="inline-flex w-full rounded-lg border border-gray-300 p-1 dark:border-gray-600"
                                data-toggle-group="place_status">
                                <button type="button" data-value="A"
                                    class="toggle-pill flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition">
                                    Active
                                </button>
                                <button type="button" data-value="X"
                                    class="toggle-pill flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition">
                                    Inactive
                                </button>
                            </div>
                            <input type="hidden" id="place_status" name="status" required>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                        <button type="button" id="closePlaceModal"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============ CATEGORY MODAL (create / edit) ============ --}}
        <div id="categoryModal"
            class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-2xl dark:bg-gray-800">
                <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <div>
                        <h2 id="categoryModalTitle" class="text-base font-bold text-gray-900 dark:text-white">
                            Add Training Category
                        </h2>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            New category will be attached with doctype = TE automatically.
                        </p>
                    </div>
                    <button type="button" id="closeCategoryModalX"
                        class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="categoryForm" class="px-6 py-5">
                    @csrf
                    <input type="hidden" id="category_id" name="id">
                    <input type="hidden" name="doctype" value="TE">

                    <div class="space-y-5">
                        <div>
                            <label for="categoryid"
                                class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Category ID (Code)
                            </label>
                            <input type="text" id="categoryid" name="categoryid"
                                placeholder="e.g. leadership_program"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white"
                                required>
                        </div>

                        <div>
                            <label for="category_name"
                                class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Category Name
                            </label>
                            <input type="text" id="category_name" name="category_name"
                                placeholder="e.g. Leadership Program"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white"
                                required>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Status
                            </label>
                            <div class="inline-flex w-full rounded-lg border border-gray-300 p-1 dark:border-gray-600"
                                data-toggle-group="category_status">
                                <button type="button" data-value="A"
                                    class="toggle-pill flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition">
                                    Active
                                </button>
                                <button type="button" data-value="X"
                                    class="toggle-pill flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition">
                                    Inactive
                                </button>
                            </div>
                            <input type="hidden" id="category_status" name="status" required>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                        <button type="button" id="closeCategoryModal"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="loadingOverlay"
        class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/40">
        <div class="flex items-center gap-3 rounded-xl bg-white px-6 py-4 shadow-lg dark:bg-gray-800">
            <svg class="h-6 w-6 animate-spin text-gray-900 dark:text-white" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Processing...</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Toggle pill group */
        .toggle-pill {
            color: #4b5563;
        }

        .toggle-pill:hover {
            background-color: #f3f4f6;
        }

        .toggle-pill.is-active {
            background-color: #111827;
            color: #ffffff;
        }

        .dark .toggle-pill {
            color: #d1d5db;
        }

        .dark .toggle-pill:hover {
            background-color: #374151;
        }

        .dark .toggle-pill.is-active {
            background-color: #ffffff;
            color: #111827;
        }

        /* Active tab styling */
        .setup-tab.is-active {
            border-bottom-color: #2563eb;
            color: #2563eb;
        }

        .dark .setup-tab.is-active {
            border-bottom-color: #60a5fa;
            color: #60a5fa;
        }
    </style>

    <script>
        function showLoading() {
            $('#loadingOverlay').removeClass('hidden');
        }

        function hideLoading() {
            $('#loadingOverlay').addClass('hidden');
        }

        function setToggleGroup(group, value) {
            $(`[data-toggle-group="${group}"] .toggle-pill`).each(function() {
                $(this).toggleClass('is-active', $(this).data('value') == value);
            });
            $(`#${group}`).val(value);
        }

        $(document).ready(function() {

            let placesTable, categoriesTable;

            // ---------- TABS ----------
            $('.setup-tab').on('click', function() {
                let tab = $(this).data('setup-tab');
                $('.setup-tab').removeClass('is-active');
                $(this).addClass('is-active');
                $('.setup-panel').addClass('hidden');
                $(`#tab-${tab}`).removeClass('hidden');
            });

            // Initialize: activate places tab by default
            $('.setup-tab[data-setup-tab="places"]').addClass('is-active');

            // ---------- Toggle pills ----------
            $(document).on('click', '.toggle-pill', function() {
                let group = $(this).closest('[data-toggle-group]').data('toggle-group');
                setToggleGroup(group, $(this).data('value'));
            });

            // ---------- PLACES DataTable ----------
            placesTable = $('#placesTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('mastertraining.setup.places') }}",
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
                pageLength: 10,
                autoWidth: false,
                order: [
                    [1, 'desc']
                ],
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false,
                        orderable: false,
                        width: '5%'
                    },
                    {
                        data: 'places_id',
                        name: 'places_id'
                    },
                    {
                        data: 'places_name',
                        name: 'places_name'
                    },
                    {
                        data: 'places_address',
                        name: 'places_address',
                        render: function(data) {
                            return data || '-';
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        searchable: false,
                        orderable: false,
                        className: 'text-right'
                    }
                ],
                language: {
                    search: '',
                    searchPlaceholder: 'Search place...'
                }
            });

            // ---------- CATEGORIES DataTable ----------
            categoriesTable = $('#categoriesTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('mastertraining.setup.categories') }}",
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
                pageLength: 10,
                autoWidth: false,
                order: [
                    [4, 'desc']
                ],
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
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
                        data: 'status',
                        name: 'status',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        searchable: false,
                        orderable: false,
                        className: 'text-right'
                    }
                ],
                language: {
                    search: '',
                    searchPlaceholder: 'Search category...'
                }
            });

            // ==================== PLACES ====================
            function resetPlaceForm() {
                $('#placeForm')[0].reset();
                $('#place_id').val('');
                setToggleGroup('place_status', 'A');
            }

            $('#addPlaceBtn').click(function() {
                $('#placeModalTitle').text('Add Training Place');
                resetPlaceForm();
                $('#placeModal').removeClass('hidden');
            });

            $('#closePlaceModal, #closePlaceModalX').click(function() {
                $('#placeModal').addClass('hidden');
                resetPlaceForm();
            });

            window.editPlace = function(id) {
                showLoading();
                $.get(`/mastertraining/setup/places/${id}`, function(res) {
                    let d = res.data;
                    $('#placeModalTitle').text('Edit Training Place');
                    $('#place_id').val(d.id);
                    $('#places_name').val(d.places_name);
                    $('#places_address').val(d.places_address || '');
                    setToggleGroup('place_status', d.status || 'A');
                    hideLoading();
                    $('#placeModal').removeClass('hidden');
                }).fail(function(xhr) {
                    hideLoading();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data training place'
                    });
                });
            };

            window.deletePlace = function(id, name) {
                Swal.fire({
                    title: 'Delete training place?',
                    text: `"${name}" will be soft-deleted.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: `/mastertraining/setup/places/${id}`,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                            _method: 'DELETE'
                        },
                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            placesTable.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || 'Gagal menghapus training place'
                            });
                        }
                    });
                });
            };

            $('#placeForm').submit(function(e) {
                e.preventDefault();
                let id = $('#place_id').val();
                let url = id ? `/mastertraining/setup/places/${id}` : "{{ route('mastertraining.setup.places.store') }}";
                let formData = new FormData(document.getElementById('placeForm'));
                if (id) {
                    formData.append('_method', 'PUT');
                }

                showLoading();
                $('#placeForm button[type="submit"]').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        hideLoading();
                        $('#placeForm button[type="submit"]').prop('disabled', false);
                        $('#placeModal').addClass('hidden');
                        resetPlaceForm();
                        placesTable.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#placeForm button[type="submit"]').prop('disabled', false);
                        let msg = 'Gagal menyimpan training place';
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors)
                                .map(arr => arr.join(', '))
                                .join('\n');
                        } else if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });
                    }
                });
            });

            // ==================== CATEGORIES ====================
            function resetCategoryForm() {
                $('#categoryForm')[0].reset();
                $('#category_id').val('');
                setToggleGroup('category_status', 'A');
            }

            $('#addCategoryBtn').click(function() {
                $('#categoryModalTitle').text('Add Training Category');
                resetCategoryForm();
                $('#categoryModal').removeClass('hidden');
            });

            $('#closeCategoryModal, #closeCategoryModalX').click(function() {
                $('#categoryModal').addClass('hidden');
                resetCategoryForm();
            });

            window.editCategory = function(id) {
                showLoading();
                $.get(`/mastertraining/setup/categories/${id}`, function(res) {
                    let d = res.data;
                    $('#categoryModalTitle').text('Edit Training Category');
                    $('#category_id').val(d.id);
                    $('#categoryid').val(d.categoryid);
                    $('#category_name').val(d.category_name);
                    setToggleGroup('category_status', d.status || 'A');
                    hideLoading();
                    $('#categoryModal').removeClass('hidden');
                }).fail(function(xhr) {
                    hideLoading();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data training category'
                    });
                });
            };

            window.deleteCategory = function(id, name) {
                Swal.fire({
                    title: 'Delete training category?',
                    text: `"${name}" will be soft-deleted.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: `/mastertraining/setup/categories/${id}`,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                            _method: 'DELETE'
                        },
                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            categoriesTable.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || 'Gagal menghapus training category'
                            });
                        }
                    });
                });
            };

            $('#categoryForm').submit(function(e) {
                e.preventDefault();
                let id = $('#category_id').val();
                let url = id ? `/mastertraining/setup/categories/${id}` : "{{ route('mastertraining.setup.categories.store') }}";
                let formData = new FormData(document.getElementById('categoryForm'));
                if (id) {
                    formData.append('_method', 'PUT');
                }

                showLoading();
                $('#categoryForm button[type="submit"]').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        hideLoading();
                        $('#categoryForm button[type="submit"]').prop('disabled', false);
                        $('#categoryModal').addClass('hidden');
                        resetCategoryForm();
                        categoriesTable.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#categoryForm button[type="submit"]').prop('disabled', false);
                        let msg = 'Gagal menyimpan training category';
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors)
                                .map(arr => arr.join(', '))
                                .join('\n');
                        } else if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });
                    }
                });
            });
        });
    </script>
</x-app-layout>
