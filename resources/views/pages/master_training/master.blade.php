<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div
            class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div
                class="flex flex-row items-start justify-between gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] sm:flex-row sm:items-center">
                <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">🎓 Master
                    Training List</h2>
                <div class="flex items-center gap-2">
                    <a href="{{ route('mastertraining.setup') }}"
                        class="inline-flex h-10 items-center justify-center gap-1.5 rounded-lg bg-gray-900 px-5 text-sm font-medium text-white transition hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
                        ⚙️ Setup
                    </a>
                    <button id="addTrainingBtn"
                        class="inline-flex h-10 items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add Training
                    </button>
                </div>
            </div>

            <div class="relative overflow-hidden">
                <table id="trainingTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th class="col-actions px-4 py-3 text-left font-medium">Actions</th>
                            <th class="px-4 py-3 text-left font-medium">Training ID</th>
                            <th class="px-4 py-3 text-left font-medium">Training Name</th>
                            <th class="px-4 py-3 text-left font-medium">Category</th>
                            <th class="px-4 py-3 text-left font-medium">Mandatory</th>
                            <th class="px-4 py-3 text-left font-medium">Type</th>
                            <th class="px-4 py-3 text-left font-medium">Schedules</th>
                            <th class="col-status px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        {{-- Modal --}}
        <div id="trainingModal"
            class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl dark:bg-gray-800">

                {{-- Header --}}
                <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <div>
                        <h2 id="trainingModalTitle" class="text-base font-bold text-gray-900 dark:text-white">
                            Add Training
                        </h2>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            Fill in the training event details below.
                        </p>
                    </div>
                    <button type="button" id="closeTrainingModalX"
                        class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="trainingForm" class="px-6 py-5">
                    @csrf
                    <input type="hidden" id="id" name="id">

                    <div class="space-y-5">
                        {{-- Training Name --}}
                        <div>
                            <label for="training_name"
                                class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Training Name
                            </label>
                            <input type="text" id="training_name" name="training_name"
                                placeholder="e.g. Whistle Blower System - Pakta Integritas"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white"
                                required>
                        </div>

                        {{-- Category --}}
                        <div>
                            <label for="category_id"
                                class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Category
                            </label>
                            <select id="category_id" name="category_id" class="w-full" required></select>
                        </div>

                        {{-- Mandatory + Training Type --}}
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Mandatory
                                </label>
                                <div class="inline-flex w-full rounded-lg border border-gray-300 p-1 dark:border-gray-600"
                                    data-toggle-group="is_mandatory">
                                    <button type="button" data-value="1"
                                        class="toggle-pill flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition">
                                        Mandatory
                                    </button>
                                    <button type="button" data-value="0"
                                        class="toggle-pill flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition">
                                        Not Mandatory
                                    </button>
                                </div>
                                <input type="hidden" id="is_mandatory" name="is_mandatory" required>
                            </div>

                            <div>
                                <label
                                    class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Training Type
                                </label>
                                <div class="inline-flex w-full rounded-lg border border-gray-300 p-1 dark:border-gray-600"
                                    data-toggle-group="training_type">
                                    <button type="button" data-value="INTERNAL"
                                        class="toggle-pill flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition">
                                        Internal
                                    </button>
                                    <button type="button" data-value="EXTERNAL"
                                        class="toggle-pill flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition">
                                        External
                                    </button>
                                </div>
                                <input type="hidden" id="training_type" name="training_type" required>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="training_description"
                                class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Description
                            </label>
                            <textarea id="training_description" name="training_description" rows="3"
                                placeholder="Optional notes about this training"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-white dark:focus:ring-white"></textarea>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="mt-6 flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                        <button type="button" id="closeTrainingModal"
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

        {{-- View (read-only) Modal --}}
        <div id="trainingViewModal"
            class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-2xl dark:bg-gray-800">

                <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Training Detail</h2>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Read-only summary.</p>
                    </div>
                    <button type="button" id="closeTrainingViewModalX"
                        class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5">
                    <div id="viewLoading" class="py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                        Loading...
                    </div>

                    <div id="viewContent" class="hidden space-y-5">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Training ID</div>
                                <div id="view_training_id" class="mt-0.5 text-sm text-gray-900 dark:text-white"></div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Category</div>
                                <div id="view_category_id" class="mt-0.5 text-sm text-gray-900 dark:text-white"></div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Mandatory</div>
                                <div id="view_is_mandatory" class="mt-0.5 text-sm text-gray-900 dark:text-white"></div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Type</div>
                                <div id="view_training_type" class="mt-0.5 text-sm text-gray-900 dark:text-white"></div>
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Training Name</div>
                            <div id="view_training_name" class="mt-0.5 text-sm text-gray-900 dark:text-white"></div>
                        </div>

                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Description</div>
                            <div id="view_description" class="mt-0.5 text-sm text-gray-900 dark:text-white"></div>
                        </div>

                        <div class="border-t border-gray-100 pt-4 dark:border-gray-700">
                            <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Levels &amp; Schedules
                            </div>
                            <div id="view_levels" class="space-y-2"></div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-gray-100 px-6 py-4 dark:border-gray-700">
                    <button type="button" id="closeTrainingViewModal"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                        Close
                    </button>
                    <a id="view_manage_sessions_link" href="#"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
                        Manage Sessions
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div id="loadingOverlay"
        class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/40">
        <div class="flex items-center gap-3 rounded-xl bg-white px-6 py-4 shadow-lg dark:bg-gray-800">
            <svg class="h-6 w-6 animate-spin text-gray-900 dark:text-white" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10"
                    stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Processing...</span>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Monochrome (black/gray) theme for select2 inside this modal — no indigo/blue */
        #trainingModal .select2-container--default .select2-selection--single {
            height: 42px;
            border-color: #d1d5db;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
        }

        #trainingModal .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 42px;
            padding-left: 12px;
            color: #111827;
        }

        #trainingModal .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }

        #trainingModal .select2-container--default.select2-container--focus .select2-selection--single,
        #trainingModal .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #111827;
            box-shadow: 0 0 0 1px #111827;
        }

        #trainingModal .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #111827;
        }

        #trainingModal .select2-dropdown {
            border-color: #111827;
        }

        .dark #trainingModal .select2-container--default .select2-selection--single {
            background-color: #111827;
            border-color: #4b5563;
        }

        .dark #trainingModal .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #ffffff;
        }

        .dark #trainingModal .select2-container--default.select2-container--focus .select2-selection--single,
        .dark #trainingModal .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #ffffff;
            box-shadow: 0 0 0 1px #ffffff;
        }

        .dark #trainingModal .select2-dropdown {
            background-color: #111827;
            border-color: #ffffff;
            color: #ffffff;
        }

        .dark #trainingModal .select2-results__option {
            color: #ffffff;
        }

        /* Toggle pill group (Mandatory / Training Type) */
        #trainingModal .toggle-pill {
            color: #4b5563;
        }

        #trainingModal .toggle-pill:hover {
            background-color: #f3f4f6;
        }

        #trainingModal .toggle-pill.is-active {
            background-color: #111827;
            color: #ffffff;
        }

        .dark #trainingModal .toggle-pill {
            color: #d1d5db;
        }

        .dark #trainingModal .toggle-pill:hover {
            background-color: #374151;
        }

        .dark #trainingModal .toggle-pill.is-active {
            background-color: #ffffff;
            color: #111827;
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

            // DataTable
            let table = $('#trainingTable').DataTable({
                ajax: {
                    url: "{{ route('mastertraining.json') }}",
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
                        title: 'Master Training',
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
                        title: 'Master Training',
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
                                            <button class="editTrainingBtn bg-blue-500 text-white px-2 py-1 rounded"
                                                data-id="${data}" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="/mastertraining/${row.eid}/sessions"
                                                class="bg-gray-900 text-white px-2 py-1 rounded dark:bg-white dark:text-gray-900"
                                                title="Manage Sessions">
                                                <i class="fas fa-calendar-alt"></i>
                                            </a>
                                        </div>
                                    `;
                        }
                    },
                    {
                        data: 'training_id',
                        render: function(data, type, row) {
                            return `<button type="button" class="viewTrainingBtn rounded bg-gray-700 px-2 py-1 font-semibold text-white hover:bg-gray-600"
                                        data-eid="${row.eid}">${data}</button>`;
                        }
                    },
                    {
                        data: 'training_name'
                    },
                    {
                        data: 'category_id'
                    },
                    {
                        data: 'is_mandatory',
                        render: function(data) {
                            return data ?
                                '<span class="bg-amber-300/30 text-amber-700 font-semibold px-3 py-1 rounded">Mandatory</span>' :
                                '<span class="text-gray-500 dark:text-gray-400">-</span>';
                        }
                    },
                    {
                        data: 'training_type'
                    },
                    {
                        data: 'schedule_count',
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (type !== 'display') return data;
                            let badge = data > 0
                                ? `<span class="bg-gray-900/10 text-gray-800 dark:bg-white/10 dark:text-white font-semibold px-3 py-1 rounded">${data} schedule${data === 1 ? '' : 's'}</span>`
                                : '<span class="text-gray-400">No schedules</span>';
                            return `<a href="/mastertraining/${row.eid}/sessions" class="hover:underline">${badge}</a>`;
                        }
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

            // Select2 - Category (AJAX)
            $('#category_id').select2({
                width: '100%',
                dropdownParent: $('#trainingModal'),
                placeholder: 'Choose Category',
                allowClear: true,
                ajax: {
                    url: "{{ route('mastertraining.category-search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                }
            });

            // Toggle pill groups (Mandatory / Training Type)
            $(document).on('click', '.toggle-pill', function() {
                let group = $(this).closest('[data-toggle-group]').data('toggle-group');
                let value = $(this).data('value');
                setToggleGroup(group, value);
            });

            function resetTrainingForm() {
                $('#trainingForm')[0].reset();
                $('#id').val('');
                $('#category_id').val(null).trigger('change');
                setToggleGroup('is_mandatory', '0');
                setToggleGroup('training_type', 'INTERNAL');
            }

            // Open modal Add
            $('#addTrainingBtn').click(function() {
                $('#trainingModalTitle').text("Add Training");
                resetTrainingForm();
                $('#trainingModal').removeClass('hidden');
            });

            // Close modal
            $('#closeTrainingModal, #closeTrainingModalX').click(function() {
                resetTrainingForm();
                $('#trainingModal').addClass('hidden');
            });

            // Edit
            $(document).on('click', '.editTrainingBtn', function() {
                let id = $(this).data('id');

                $('#trainingModalTitle').text("Loading...");
                $('#trainingForm')[0].reset();
                $('#id').val(id);
                $('#trainingModal').removeClass('hidden');
                showLoading();

                $.get(`/mastertraining/${id}/edit`, function(data) {
                    $('#trainingModalTitle').text("Edit Training");

                    $('#training_name').val(data.training_name);

                    let categoryOption = new Option(data.category_id, data.category_id, true, true);
                    $('#category_id').append(categoryOption).trigger('change');

                    setToggleGroup('is_mandatory', data.is_mandatory ? '1' : '0');
                    setToggleGroup('training_type', data.training_type);
                    $('#training_description').val(data.training_description);

                    hideLoading();
                }).fail(function(xhr) {
                    hideLoading();
                    $('#trainingModal').addClass('hidden');

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data training'
                    });

                    console.error(xhr.responseText);
                });
            });

            // View (read-only)
            function openTrainingView(eid, updateUrl = true) {
                if (updateUrl && window.location.pathname !== `/mastertraining/${eid}`) {
                    history.pushState({ eid: eid }, '', `/mastertraining/${eid}`);
                }

                $('#viewContent').addClass('hidden');
                $('#viewLoading').removeClass('hidden').text('Loading...');
                $('#trainingViewModal').removeClass('hidden');
                $('#view_manage_sessions_link').attr('href', `/mastertraining/${eid}/sessions`);

                $.get(`/mastertraining/${eid}/show`, function(res) {
                    let t = res.training;

                    $('#view_training_id').text(t.training_id);
                    $('#view_category_id').text(t.category_id);
                    $('#view_is_mandatory').text(t.is_mandatory ? 'Mandatory' : 'Not Mandatory');
                    $('#view_training_type').text(t.training_type);
                    $('#view_training_name').text(t.training_name);
                    $('#view_description').text(t.training_description || '-');

                    let $levels = $('#view_levels').empty();

                    if (!res.levels || res.levels.length === 0) {
                        $levels.append(
                            '<div class="rounded-lg border border-dashed border-gray-300 p-3 text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">No levels/schedules yet.</div>'
                        );
                    } else {
                        res.levels.forEach(function(level) {
                            let counts = Object.entries(level.status_counts || {})
                                .map(([status, count]) => `${status}: ${count}`)
                                .join(' · ');

                            $levels.append(`
                                <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">${level.grade_name}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">${level.schedule_count} schedule(s)</span>
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">${counts}</div>
                                </div>
                            `);
                        });
                    }

                    $('#viewLoading').addClass('hidden');
                    $('#viewContent').removeClass('hidden');
                }).fail(function(xhr) {
                    $('#viewLoading').text('Gagal mengambil data training');
                    console.error(xhr.responseText);
                });
            }

            $(document).on('click', '.viewTrainingBtn', function() {
                openTrainingView($(this).data('eid'));
            });

            function closeTrainingView(updateUrl = true) {
                $('#trainingViewModal').addClass('hidden');

                if (updateUrl && window.location.pathname !== '/mastertraining') {
                    history.pushState({}, '', '/mastertraining');
                }
            }

            $('#closeTrainingViewModal, #closeTrainingViewModalX').click(function() {
                closeTrainingView();
            });

            window.addEventListener('popstate', function() {
                let match = window.location.pathname.match(/^\/mastertraining\/([A-Za-z0-9]+)$/);
                if (match) {
                    openTrainingView(match[1], false);
                } else {
                    closeTrainingView(false);
                }
            });

            // Deep link: /mastertraining/{eid} opens the view modal on load
            (function() {
                let match = window.location.pathname.match(/^\/mastertraining\/([A-Za-z0-9]+)$/);
                if (match) {
                    openTrainingView(match[1], false);
                }
            })();

            // Toggle status
            $(document).on('change', '.toggleStatus', function() {
                let $checkbox = $(this);
                let id = $checkbox.data('id');
                let newStatus = $checkbox.is(':checked') ? 'A' : 'X';

                function submitToggle() {
                    $.ajax({
                        url: `/mastertraining/${id}/toggle-status`,
                        type: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                            status: newStatus
                        },
                        success: function() {
                            table.ajax.reload(null, false);
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: newStatus === 'A' ? 'Training diaktifkan' : 'Training dinonaktifkan',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true
                            });
                        },
                        error: function(xhr) {
                            $checkbox.prop('checked', newStatus !== 'A');
                            Swal.fire({
                                icon: 'error',
                                title: 'Tidak dapat mengubah status',
                                text: xhr.responseJSON?.message || 'Gagal mengubah status training'
                            });
                        }
                    });
                }

                if (newStatus === 'X') {
                    $checkbox.prop('checked', true);
                    Swal.fire({
                        title: 'Nonaktifkan training ini?',
                        text: 'Training yang masih memiliki schedule aktif tidak dapat dinonaktifkan.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, nonaktifkan',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (!result.isConfirmed) return;
                        $checkbox.prop('checked', false);
                        submitToggle();
                    });
                } else {
                    submitToggle();
                }
            });

            // Submit (create / update)
            $('#trainingForm').submit(function(e) {
                e.preventDefault();

                let id = $('#id').val();
                let url = id ? `/mastertraining/${id}` : "{{ route('mastertraining.store') }}";
                let method = 'POST';
                let formData = new FormData(document.getElementById('trainingForm'));

                if (id) {
                    formData.append('_method', 'PUT');
                }

                showLoading();
                $('#trainingForm button[type="submit"]').prop('disabled', true);

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
                        $('#trainingForm button[type="submit"]').prop('disabled', false);

                        $('#trainingModal').addClass('hidden');
                        resetTrainingForm();
                        table.ajax.reload();

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Training saved successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#trainingForm button[type="submit"]').prop('disabled', false);

                        let msg = 'Gagal menyimpan data training';

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
