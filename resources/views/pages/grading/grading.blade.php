<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'grading' ? 'Grading' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- ── TABS ────────────────────────────────────────────────────────────── --}}
        <div>
            <div class="flex gap-1 border-b border-gray-200 dark:border-gray-700 mb-0">
                <button type="button" id="tab-grade"
                    class="gradingTabBtn px-5 py-2.5 text-sm font-semibold rounded-t-lg border border-b-0 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400"
                    onclick="switchGradingTab('grade')">
                    🎖️ Grading
                </button>
                <button type="button" id="tab-subgrade"
                    class="gradingTabBtn px-5 py-2.5 text-sm font-semibold rounded-t-lg border border-b-0 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                    onclick="switchGradingTab('subgrade')">
                    🏷️ Sub Grading
                </button>
            </div>

            {{-- ── TAB 1: Grading ──────────────────────────────────────────────── --}}
            <div id="panel-grade"
                class="rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
                <div
                    class="flex items-center justify-between border-b border-gray-100 px-5 py-2 dark:border-white/[0.06]">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">🎖️ Grading</h2>
                    <button id="addGradeBtn"
                        class="inline-flex h-9 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add Grade
                    </button>
                </div>

                <div class="relative overflow-hidden">
                    <table id="gradeTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-8 px-2 py-3 text-center"></th> {{-- responsive control --}}
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Grade ID</th>
                                <th class="px-4 py-3 text-left font-medium">Grade Name</th>
                                <th class="px-4 py-3 text-left font-medium">Color</th>
                                <th class="px-4 py-3 text-left font-medium">Company Group</th>
                                <th class="w-32 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            {{-- ── TAB 2: Sub Grading ─────────────────────────────────────────── --}}
            <div id="panel-subgrade"
                class="hidden rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
                <div
                    class="flex items-center justify-between border-b border-gray-100 px-5 py-2 dark:border-white/[0.06]">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">🏷️ Sub
                        Grading</h2>
                    <button id="addSubGradeBtn"
                        class="inline-flex h-9 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add Sub Grade
                    </button>
                </div>

                <div class="relative overflow-hidden">
                    <table id="subGradeTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-8 px-2 py-3 text-center"></th> {{-- responsive control --}}
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Sub Grade ID</th>
                                <th class="px-4 py-3 text-left font-medium">Sub Grade Name</th>
                                <th class="px-4 py-3 text-left font-medium">Grade</th>
                                <th class="px-4 py-3 text-left font-medium">Group Grade</th>
                                <th class="px-4 py-3 text-left font-medium">Color</th>
                                <th class="px-4 py-3 text-left font-medium">Company Group</th>
                                <th class="w-32 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal: Grading -->
        <div id="gradeModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="modalGradeTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add Grade</h2>
                <form id="gradeForm">
                    @csrf
                    <input type="hidden" id="grade_id_hidden" name="id">

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Grade ID</label>
                        <input type="text" id="grade_id_field" name="grade_id"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Grade Name</label>
                        <input type="text" id="grade_name_field" name="grade_name"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Grade Color</label>
                        <input type="color" id="grade_color_code_field" name="grade_color_code"
                            value="#6366f1" class="h-10 w-full rounded-lg border px-1 py-1 dark:bg-gray-700">
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Company Group</label>
                        <select id="grade_group_cpny_id" name="group_cpny_id"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            <option value="">-- Select Company Group --</option>
                            <option value="JKT">JKT</option>
                            <option value="SBY">SBY</option>
                        </select>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeGradeModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Sub Grading -->
        <div id="subGradeModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="modalSubGradeTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add Sub
                    Grade</h2>
                <form id="subGradeForm">
                    @csrf
                    <input type="hidden" id="subgrade_id_hidden" name="id">

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Sub Grade ID</label>
                        <input type="text" id="subgrade_id_field" name="subgrade_id"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Sub Grade Name</label>
                        <input type="text" id="subgrade_name_field" name="subgrade_name"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Grade</label>
                        <select id="subgrade_grade_id" name="grade_id"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                            <option value="">-- Select Grade --</option>
                            @foreach ($grades as $grade)
                                <option value="{{ $grade->grade_id }}">{{ $grade->grade_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Group Grade</label>
                        <input type="text" id="subgrade_group_grade_field" name="group_grade"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Sub Grade Color</label>
                        <input type="color" id="subgrade_color_code_field" name="subgrade_color_code"
                            value="#6366f1" class="h-10 w-full rounded-lg border px-1 py-1 dark:bg-gray-700">
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 dark:text-white">Company Group</label>
                        <select id="subgrade_group_cpny_id" name="group_cpny_id"
                            class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            <option value="">-- Select Company Group --</option>
                            <option value="JKT">JKT</option>
                            <option value="SBY">SBY</option>
                        </select>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeSubGradeModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="loadingOverlay"
        class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/40">
        <div class="flex items-center gap-3 rounded-xl bg-white px-6 py-4 shadow-lg dark:bg-gray-800">
            <svg class="h-6 w-6 animate-spin text-indigo-600" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10"
                    stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Processing...</span>
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

        const initedGradingTabs = { grade: false, subgrade: false };
        const gradingActiveClasses = 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400';
        const gradingInactiveClasses = 'bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400';

        function switchGradingTab(tab) {
            const panels = { grade: '#panel-grade', subgrade: '#panel-subgrade' };
            const btns   = { grade: '#tab-grade',   subgrade: '#tab-subgrade'   };

            Object.keys(panels).forEach(function(key) {
                const isActive = key === tab;
                $(panels[key]).toggleClass('hidden', !isActive);
                $(btns[key])
                    .toggleClass(gradingActiveClasses, isActive)
                    .toggleClass(gradingInactiveClasses, !isActive);
            });

            if (!initedGradingTabs[tab]) {
                initedGradingTabs[tab] = true;
                if (tab === 'grade') initGradeTable();
                if (tab === 'subgrade') initSubGradeTable();
            } else if (tab === 'grade' && window.gradeTable) {
                window.gradeTable.columns.adjust().responsive.recalc();
            } else if (tab === 'subgrade' && window.subGradeTable) {
                window.subGradeTable.columns.adjust().responsive.recalc();
            }
        }

        $(document).ready(function() {

            // =========================================================
            // Grading
            // =========================================================
            window.initGradeTable = function() {
                window.gradeTable = $('#gradeTable').DataTable({
                    ajax: {
                        url: "{{ route('grading.json') }}",
                        type: "GET",
                        dataSrc: 'data',
                        error: function(xhr) {
                            console.error('AJAX Error:', xhr.responseText);
                        }
                    },
                    processing: true,
                    serverSide: false,
                    autoWidth: false,
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
                    columnDefs: [
                        {
                            targets: 0,
                            className: 'dtr-control',
                            orderable: false,
                            searchable: false,
                            width: '28px',
                            defaultContent: ''
                        },
                        {
                            targets: 1,
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        },
                        {
                            targets: 4,
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        },
                        {
                            targets: 6,
                            className: 'text-center'
                        }
                    ],
                    dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'Grading',
                            className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                            exportOptions: {
                                columns: [2, 3, 5, 6]
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            text: '↓ CSV',
                            title: 'Grading',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: [2, 3, 5, 6]
                            }
                        }
                    ],
                    columns: [
                        {
                            data: null,
                            defaultContent: ''
                        },
                        {
                            data: 'id',
                            render: function(data, type, row) {
                                return `
                                    <div class="flex justify-center space-x-2">
                                        <label class="switch">
                                            <input type="checkbox" class="toggleGradeStatus"
                                                data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                            <span class="slider round"></span>
                                        </label>
                                        <button class="editGradeBtn bg-blue-500 text-white px-2 py-1 rounded"
                                            data-id="${data}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        },
                        {
                            data: 'grade_id',
                            className: 'no-pointer'
                        },
                        {
                            data: 'grade_name',
                            className: 'no-pointer'
                        },
                        {
                            data: 'grade_color_code',
                            className: 'no-pointer',
                            render: function(data) {
                                return data
                                    ? `<span class="inline-block h-5 w-5 rounded-full border border-gray-300 align-middle" style="background-color: ${data}" title="${data}"></span>`
                                    : '-';
                            }
                        },
                        {
                            data: 'group_cpny_id',
                            className: 'no-pointer',
                            defaultContent: '-'
                        },
                        {
                            data: 'status',
                            className: 'no-pointer',
                            render: function(data) {
                                return data === 'A'
                                    ? '<span class="inline-block rounded bg-green-300/30 px-4 py-2 font-semibold text-green-600">Active</span>'
                                    : '<span class="inline-block rounded bg-red-300/30 px-4 py-2 font-semibold text-red-600">Inactive</span>';
                            }
                        }
                    ]
                });
            }

            $('#grade_group_cpny_id').select2({
                dropdownParent: $('#gradeModal'),
                placeholder: '-- Select Company Group --',
                width: '100%'
            });

            $('#addGradeBtn').click(function() {
                $('#modalGradeTitle').text("Add Grade");
                $('#gradeForm')[0].reset();
                $('#grade_id_hidden').val('');
                $('#grade_color_code_field').val('#6366f1');
                $('#grade_group_cpny_id').val('').trigger('change');
                $('#gradeModal').removeClass('hidden');
            });

            $(document).on('click', '.editGradeBtn', function() {
                let id = $(this).data('id');

                $('#modalGradeTitle').text("Loading...");
                $('#gradeModal').removeClass('hidden');
                showLoading();

                $.get(`/grading/${id}/edit`, function(c) {
                    $('#modalGradeTitle').text("Edit Grade");
                    $('#grade_id_hidden').val(c.id);
                    $('#grade_id_field').val(c.grade_id);
                    $('#grade_name_field').val(c.grade_name);
                    $('#grade_color_code_field').val(c.grade_color_code || '#6366f1');
                    $('#grade_group_cpny_id').val(c.group_cpny_id).trigger('change');
                    hideLoading();
                }).fail(function(xhr) {
                    hideLoading();
                    $('#gradeModal').addClass('hidden');

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data grade'
                    });

                    console.error(xhr.responseText);
                });
            });

            $(document).on('change', '.toggleGradeStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/grading/${id}/toggle-status`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        status: newStatus
                    },
                    success: function() {
                        window.gradeTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Gagal update status');
                        window.gradeTable.ajax.reload(null, false);
                    }
                });
            });

            $('#gradeForm').submit(function(e) {
                e.preventDefault();

                if ($('#gradeForm button[type="submit"]').prop('disabled')) {
                    return;
                }
                $('#gradeForm button[type="submit"]').prop('disabled', true);

                let id = $('#grade_id_hidden').val();
                let url = id ? `/grading/${id}` : "{{ route('grading.store') }}";
                let formData = new FormData(document.getElementById('gradeForm'));

                if (id) {
                    formData.append('_method', 'PUT');
                }

                showLoading();

                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        hideLoading();
                        $('#gradeForm button[type="submit"]').prop('disabled', false);

                        $('#gradeModal').addClass('hidden');
                        $('#gradeForm')[0].reset();
                        $('#grade_id_hidden').val('');
                        window.gradeTable.ajax.reload(null, false);
                        if (window.subGradeTable) {
                            window.subGradeTable.ajax.reload(null, false);
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Grade saved successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#gradeForm button[type="submit"]').prop('disabled', false);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : 'Gagal menyimpan data grade'
                        });

                        console.error(xhr.responseText);
                    }
                });
            });

            $('#closeGradeModal').click(function() {
                $('#gradeForm')[0].reset();
                $('#grade_id_hidden').val('');
                $('#grade_group_cpny_id').val('').trigger('change');
                $('#gradeModal').addClass('hidden');
            });

            // =========================================================
            // Sub Grading
            // =========================================================
            window.initSubGradeTable = function() {
                window.subGradeTable = $('#subGradeTable').DataTable({
                    ajax: {
                        url: "{{ route('grading.subgrading.json') }}",
                        type: "GET",
                        dataSrc: 'data',
                        error: function(xhr) {
                            console.error('AJAX Error:', xhr.responseText);
                        }
                    },
                    processing: true,
                    serverSide: false,
                    autoWidth: false,
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
                    columnDefs: [
                        {
                            targets: 0,
                            className: 'dtr-control',
                            orderable: false,
                            searchable: false,
                            width: '28px',
                            defaultContent: ''
                        },
                        {
                            targets: 1,
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        },
                        {
                            targets: 6,
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        },
                        {
                            targets: 8,
                            className: 'text-center'
                        }
                    ],
                    dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'Sub Grading',
                            className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                            exportOptions: {
                                columns: [2, 3, 4, 5, 7, 8]
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            text: '↓ CSV',
                            title: 'Sub Grading',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: [2, 3, 4, 5, 7, 8]
                            }
                        }
                    ],
                    columns: [
                        {
                            data: null,
                            defaultContent: ''
                        },
                        {
                            data: 'id',
                            render: function(data, type, row) {
                                return `
                                    <div class="flex justify-center space-x-2">
                                        <label class="switch">
                                            <input type="checkbox" class="toggleSubGradeStatus"
                                                data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                            <span class="slider round"></span>
                                        </label>
                                        <button class="editSubGradeBtn bg-blue-500 text-white px-2 py-1 rounded"
                                            data-id="${data}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        },
                        {
                            data: 'subgrade_id',
                            className: 'no-pointer'
                        },
                        {
                            data: 'subgrade_name',
                            className: 'no-pointer'
                        },
                        {
                            data: 'grade_name',
                            className: 'no-pointer',
                            defaultContent: '-'
                        },
                        {
                            data: 'group_grade',
                            className: 'no-pointer',
                            defaultContent: '-'
                        },
                        {
                            data: 'subgrade_color_code',
                            className: 'no-pointer',
                            render: function(data) {
                                return data
                                    ? `<span class="inline-block h-5 w-5 rounded-full border border-gray-300 align-middle" style="background-color: ${data}" title="${data}"></span>`
                                    : '-';
                            }
                        },
                        {
                            data: 'group_cpny_id',
                            className: 'no-pointer',
                            defaultContent: '-'
                        },
                        {
                            data: 'status',
                            className: 'no-pointer',
                            render: function(data) {
                                return data === 'A'
                                    ? '<span class="inline-block rounded bg-green-300/30 px-4 py-2 font-semibold text-green-600">Active</span>'
                                    : '<span class="inline-block rounded bg-red-300/30 px-4 py-2 font-semibold text-red-600">Inactive</span>';
                            }
                        }
                    ]
                });
            }

            $('#subgrade_grade_id').select2({
                dropdownParent: $('#subGradeModal'),
                placeholder: '-- Select Grade --',
                width: '100%'
            });

            $('#subgrade_group_cpny_id').select2({
                dropdownParent: $('#subGradeModal'),
                placeholder: '-- Select Company Group --',
                width: '100%'
            });

            $('#addSubGradeBtn').click(function() {
                $('#modalSubGradeTitle').text("Add Sub Grade");
                $('#subGradeForm')[0].reset();
                $('#subgrade_id_hidden').val('');
                $('#subgrade_color_code_field').val('#6366f1');
                $('#subgrade_grade_id').val('').trigger('change');
                $('#subgrade_group_cpny_id').val('').trigger('change');
                $('#subGradeModal').removeClass('hidden');
            });

            $(document).on('click', '.editSubGradeBtn', function() {
                let id = $(this).data('id');

                $('#modalSubGradeTitle').text("Loading...");
                $('#subGradeModal').removeClass('hidden');
                showLoading();

                $.get(`/grading/subgrading/${id}/edit`, function(c) {
                    $('#modalSubGradeTitle').text("Edit Sub Grade");
                    $('#subgrade_id_hidden').val(c.id);
                    $('#subgrade_id_field').val(c.subgrade_id);
                    $('#subgrade_name_field').val(c.subgrade_name);
                    $('#subgrade_grade_id').val(c.grade_id).trigger('change');
                    $('#subgrade_group_grade_field').val(c.group_grade);
                    $('#subgrade_color_code_field').val(c.subgrade_color_code || '#6366f1');
                    $('#subgrade_group_cpny_id').val(c.group_cpny_id).trigger('change');
                    hideLoading();
                }).fail(function(xhr) {
                    hideLoading();
                    $('#subGradeModal').addClass('hidden');

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data sub grade'
                    });

                    console.error(xhr.responseText);
                });
            });

            $(document).on('change', '.toggleSubGradeStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/grading/subgrading/${id}/toggle-status`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        status: newStatus
                    },
                    success: function() {
                        window.subGradeTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Gagal update status');
                        window.subGradeTable.ajax.reload(null, false);
                    }
                });
            });

            $('#subGradeForm').submit(function(e) {
                e.preventDefault();

                if ($('#subGradeForm button[type="submit"]').prop('disabled')) {
                    return;
                }
                $('#subGradeForm button[type="submit"]').prop('disabled', true);

                let id = $('#subgrade_id_hidden').val();
                let url = id ? `/grading/subgrading/${id}` : "{{ route('grading.subgrading.store') }}";
                let formData = new FormData(document.getElementById('subGradeForm'));

                if (id) {
                    formData.append('_method', 'PUT');
                }

                showLoading();

                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        hideLoading();
                        $('#subGradeForm button[type="submit"]').prop('disabled', false);

                        $('#subGradeModal').addClass('hidden');
                        $('#subGradeForm')[0].reset();
                        $('#subgrade_id_hidden').val('');
                        window.subGradeTable.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Sub grade saved successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#subGradeForm button[type="submit"]').prop('disabled', false);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : 'Gagal menyimpan data sub grade'
                        });

                        console.error(xhr.responseText);
                    }
                });
            });

            $('#closeSubGradeModal').click(function() {
                $('#subGradeForm')[0].reset();
                $('#subgrade_id_hidden').val('');
                $('#subgrade_grade_id').val('').trigger('change');
                $('#subgrade_group_cpny_id').val('').trigger('change');
                $('#subGradeModal').addClass('hidden');
            });

            // Initialize the first (visible) tab
            initedGradingTabs.grade = true;
            initGradeTable();
        });
    </script>
</x-app-layout>
