<x-app-layout>
    <x-spp-list-styles />
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="dt-pretty rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="Master Training List" subtitle="Manage training courses, sessions and schedules">
                <a href="{{ route('mastertraining.setup') }}"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-5 text-sm font-medium text-gray-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-gray-50 hover:shadow-md active:translate-y-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                    <i class="fa-solid fa-gear text-xs"></i>Setup
                </a>
                <button id="addTrainingBtn" type="button"
                    class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                    <i class="fa-solid fa-plus mr-2 text-xs"></i>Add Training
                </button>
            </x-list-page-header>

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
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-graduation-cap"></i></span>
                        <div>
                        <h2 id="trainingModalTitle" class="text-base font-bold text-gray-900 dark:text-white">
                            Add Training
                        </h2>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            Fill in the training event details below.
                        </p>
                    </div>
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
                                class="w-full rounded-lg border border-gray-300 h-10 px-3 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500/30 dark:border-gray-600 dark:bg-blue-600 dark:text-white dark:focus:border-white dark:focus:ring-white"
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
                            <textarea id="training_description" name="training_description" class="hidden"></textarea>
                            <div id="training_description_editor"
                                class="training-quill overflow-hidden rounded-lg border border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-900"></div>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="mt-6 flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                        <button type="button" id="closeTrainingModal"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 h-10 px-5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 h-10 px-5 text-sm font-semibold text-white transition hover:bg-blue-500 focus:outline-none active:scale-95">
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
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-graduation-cap"></i></span>
                        <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Training Detail</h2>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Read-only summary.</p>
                    </div>
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
                            <div id="view_description" class="mt-0.5 whitespace-pre-line text-sm text-gray-900 dark:text-white"></div>
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
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 h-10 px-5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                        Close
                    </button>
                    <a id="view_manage_sessions_link" href="#"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 h-10 px-5 text-sm font-semibold text-white transition hover:bg-blue-500 focus:outline-none active:scale-95">
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
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

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

        /* Description — Quill editor, styled to match the modal's other inputs */
        .training-quill .ql-toolbar.ql-snow {
            border: none;
            border-bottom: 1px solid rgb(229 231 235);
            background: rgb(249 250 251 / .6);
            padding: 6px 10px;
        }
        .training-quill .ql-container.ql-snow {
            border: none;
            font-family: inherit;
            font-size: .875rem;
        }
        .training-quill .ql-editor {
            min-height: 110px;
            color: rgb(31 41 55);
        }
        .training-quill .ql-editor.ql-blank::before {
            color: rgb(156 163 175);
            font-style: normal;
        }
        .training-quill:focus-within {
            border-color: rgb(17 24 39) !important;
            box-shadow: 0 0 0 1px rgb(17 24 39);
        }
        .dark .training-quill .ql-toolbar.ql-snow {
            background: rgb(255 255 255 / .03);
            border-bottom-color: rgb(255 255 255 / .08);
        }
        .dark .training-quill .ql-container.ql-snow,
        .dark .training-quill .ql-editor {
            color: rgb(248 250 252);
        }
        .dark .training-quill .ql-editor.ql-blank::before {
            color: rgb(100 116 139);
        }
        .dark .training-quill:focus-within {
            border-color: #ffffff !important;
            box-shadow: 0 0 0 1px #ffffff;
        }
        .dark .training-quill .ql-snow .ql-stroke {
            stroke: rgb(148 163 184);
        }
        .dark .training-quill .ql-snow .ql-fill,
        .dark .training-quill .ql-snow .ql-stroke.ql-fill {
            fill: rgb(148 163 184);
        }
        .dark .training-quill .ql-snow .ql-picker-label {
            color: rgb(148 163 184);
        }
        .dark .training-quill .ql-snow button:hover .ql-stroke,
        .dark .training-quill .ql-snow .ql-picker-label:hover .ql-stroke {
            stroke: rgb(248 250 252);
        }
        .dark .training-quill .ql-snow button:hover .ql-fill {
            fill: rgb(248 250 252);
        }
        .dark .training-quill .ql-snow button.ql-active .ql-stroke,
        .dark .training-quill .ql-snow .ql-picker-label.ql-active .ql-stroke {
            stroke: #ffffff;
        }
        .dark .training-quill .ql-picker-options {
            background: #111827;
            border-color: rgb(255 255 255 / .08);
        }
        .dark .training-quill .ql-picker-item {
            color: rgb(226 232 240);
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

        function initTrainingDescrEditor() {
            if (window.trainingDescrQuill) return;

            window.trainingDescrQuill = new Quill('#training_description_editor', {
                theme: 'snow',
                placeholder: 'Optional notes about this training',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['link'],
                        ['clean'],
                    ],
                },
            });
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
                        text: '<i class="fa-solid fa-file-excel"></i> Excel',
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
                        text: '<i class="fa-solid fa-file-csv"></i> CSV',
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
                                        <div class="flex items-center justify-center gap-2">
                                            <label class="switch">
                                                <input type="checkbox" class="toggleStatus"
                                                    data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                                <span class="slider round"></span>
                                            </label>
                                            <button class="editTrainingBtn inline-flex h-8 w-8 items-center justify-center rounded-md border border-blue-100 bg-blue-50 text-blue-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90"
                                                data-id="${data}" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="/mastertraining/${row.eid}/sessions"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-slate-50 text-slate-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-slate-700 hover:bg-slate-700 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 dark:border-white/10 dark:bg-white/5 dark:text-slate-300"
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
                            return `<button type="button" class="viewTrainingBtn group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300"
                                        data-eid="${row.eid}" title="Open"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 group-hover/doc:-rotate-6"><i class="fa-regular fa-file-lines"></i></span><span>${data}</span></button>`;
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
                                '<span class="inline-flex items-center gap-2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Mandatory</span>' :
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
                                ? `<span class="inline-flex items-center gap-2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset bg-slate-50 text-slate-700 ring-slate-200 dark:bg-white/5 dark:text-slate-200 dark:ring-white/10"><i class="fa-regular fa-calendar text-[10px]"></i>${data} schedule${data === 1 ? '' : 's'}</span>`
                                : '<span class="text-gray-400">No schedules</span>';
                            return `<a href="/mastertraining/${row.eid}/sessions" class="hover:underline">${badge}</a>`;
                        }
                    },
                    {
                        data: 'status',
                        className: 'col-status',
                        render: function(data) {
                            return data === 'A' ?
                                '<span class="inline-flex items-center gap-2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset min-w-[96px] justify-center bg-green-50 text-green-700 ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Active</span>' :
                                '<span class="inline-flex items-center gap-2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset min-w-[96px] justify-center bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/30"><span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>Inactive</span>';
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
                initTrainingDescrEditor();
                window.trainingDescrQuill?.setText('');
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
                initTrainingDescrEditor();
                window.trainingDescrQuill?.setText('');
                showLoading();

                $.get(`/mastertraining/${id}/edit`, function(data) {
                    $('#trainingModalTitle').text("Edit Training");

                    $('#training_name').val(data.training_name);

                    let categoryOption = new Option(data.category_id, data.category_id, true, true);
                    $('#category_id').append(categoryOption).trigger('change');

                    setToggleGroup('is_mandatory', data.is_mandatory ? '1' : '0');
                    setToggleGroup('training_type', data.training_type);
                    window.trainingDescrQuill?.clipboard.dangerouslyPasteHTML(data.training_description || '');

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
                    $('#view_description').html(t.training_description || '-');

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

                if (window.trainingDescrQuill) {
                    $('#training_description').val(window.trainingDescrQuill.root.innerHTML);
                }

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
    <style>
        /* ---- shared look: blue focus, 40px fields, blue active pills/steps (same as the SPP forms) ---- */
        #trainingModal .select2-container--default .select2-selection--single { height: 40px; border-color: #e5e7eb; border-radius: 0.5rem; }
        #trainingModal .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; color: #1f2937; }
        #trainingModal .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; }
        #trainingModal .select2-container--default.select2-container--focus .select2-selection--single,
        #trainingModal .select2-container--default.select2-container--open .select2-selection--single { border-color: var(--color-blue-500); box-shadow: 0 0 0 3px rgba(59, 130, 246, .14); }
        #trainingModal .select2-container--default .select2-results__option--highlighted[aria-selected] { background-color: var(--color-blue-600); color: #fff; }
        #trainingModal .select2-dropdown { border-color: #e2e8f0; border-radius: 0.75rem; overflow: hidden; box-shadow: 0 10px 25px rgba(0, 0, 0, .12); }
        #trainingModal .toggle-pill.is-active { background-color: var(--color-blue-600); color: #fff; box-shadow: 0 1px 2px rgba(0, 0, 0, .12); }
        #trainingModal .toggle-pill:hover { background-color: #f1f5f9; }
        #trainingModal .toggle-pill.is-active:hover { background-color: var(--color-blue-600); }
        .dark #trainingModal .toggle-pill.is-active { background-color: var(--color-blue-600); color: #fff; }
        .dark #trainingModal .select2-container--default.select2-container--focus .select2-selection--single,
        .dark #trainingModal .select2-container--default.select2-container--open .select2-selection--single { border-color: #60a5fa; box-shadow: 0 0 0 3px rgba(96, 165, 250, .2); }
        .dark #trainingModal .select2-dropdown { border-color: #374151; }
        
        .training-quill:focus-within { border-color: var(--color-blue-500) !important; box-shadow: 0 0 0 3px rgba(59, 130, 246, .14); }
    </style>
</x-app-layout>
