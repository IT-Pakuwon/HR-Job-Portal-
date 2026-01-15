<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            {{-- header + tombol add --}}
            <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <h1 class="text-base font-bold text-gray-800 dark:text-white">✅ Ms Approval List</h1>
                <button id="addApprovalBtn"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    + Add Approval
                </button>
            </div>
            <div class="mb-3 flex flex-wrap items-end gap-3">
                <div class="min-w-[200px] flex-1">
                    <label class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-200">
                        Filter Doc Type
                    </label>
                    <select id="filterDoctype"
                        class="w-full rounded-lg border border-gray-300 px-2 py-1 text-xs dark:bg-gray-700">
                        <option value="">All Document Type</option>
                        @foreach ($doctypes as $dt)
                            <option value="{{ $dt->doctype }}">{{ $dt->doctype }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[200px] flex-1">
                    <label class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-200">
                        Filter Company
                    </label>
                    <select id="filterCompany"
                        class="w-full rounded-lg border border-gray-300 px-2 py-1 text-xs dark:bg-gray-700">
                        <option value="">All Company</option>
                        @foreach ($companies as $c)
                            <option value="{{ $c->cpny_id }}">{{ $c->cpny_id }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[200px] flex-1">
                    <label class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-200">
                        Filter Department
                    </label>
                    <select id="filterDept"
                        class="w-full rounded-lg border border-gray-300 px-2 py-1 text-xs dark:bg-gray-700">
                        <option value="">All Department</option>
                        @foreach ($departments as $d)
                            <option value="{{ $d->department_id }}">{{ $d->department_id }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-6">
                    <button id="clearUserFilters" type="button"
                        class="rounded-lg border px-3 py-1 text-xs text-gray-700 hover:bg-gray-100 dark:border-gray-500 dark:text-gray-200 dark:hover:bg-gray-600">
                        Clear Filter
                    </button>
                </div>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="approvalTable" class="text-body w-full text-left text-xs rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-xs">
                        <tr>
                            <th></th>
                            <th class="col-actions w-24 px-2 py-3 text-center">Actions</th>
                            <th class="col-level w-16 px-2 py-3 text-center">Level</th>
                            <th class="col-doctype px-2 py-3 text-left">Doc Type</th>
                            <th class="px-2 py-3 text-left">Company</th>
                            <th class="px-2 py-3 text-left">Department</th>
                            <th class="col-name px-2 py-3 text-left">Name</th>
                            <th class="px-2 py-3 text-left">Type</th>
                            <th class="px-2 py-3 text-left">Condition</th>
                            <th class="col-start w-24 px-2 py-3 text-right">Start Nom</th>
                            <th class="col-end w-24 px-2 py-3 text-right">End Nom</th>
                            <th class="col-status w-24 px-2 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        {{-- Modal --}}
        <div id="approvalModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-6xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="approvalModalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">
                    Add Approval
                </h2>
                <form id="approvalForm">
                    @csrf
                    <input type="hidden" id="id" name="id">

                    {{-- Baris atas: Doctype, Company, Department --}}
                    <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                        {{-- DOCTYPE --}}
                        <div>
                            <label class="mb-1 block text-gray-700 dark:text-white">Doctype</label>
                            <select id="aprv_doctype" name="aprv_doctype"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                <option value="">-- choose --</option>
                                @foreach ($doctypes as $dt)
                                    <option value="{{ $dt->doctype }}">{{ $dt->doctype }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- COMPANY --}}
                        <div>
                            <label class="mb-1 block text-gray-700 dark:text-white">Company</label>
                            <select id="aprv_cpnyid_select" name="aprv_cpnyid"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                <option value="">-- choose --</option>
                                @foreach ($companies as $c)
                                    <option value="{{ $c->cpny_id }}">
                                        {{ $c->cpny_id }} — {{ $c->cpny_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- DEPARTMENT --}}
                        <div>
                            <label class="mb-1 block text-gray-700 dark:text-white">Department</label>
                            <select id="aprv_departementid" name="aprv_departementid"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                <option value="">-- choose --</option>
                                @foreach ($departments as $d)
                                    <option value="{{ $d->department_id }}">
                                        {{ $d->department_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Approval Lines --}}
                    <div class="rounded-lg border bg-gray-50 p-3 dark:border-gray-600 dark:bg-gray-800">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-800 dark:text-gray-100">
                                Approval Lines
                            </span>
                            <button type="button" id="addLineBtn"
                                class="rounded bg-indigo-500 px-3 py-1 text-xs font-semibold text-white">
                                ADD
                            </button>
                        </div>

                        <div
                            class="mb-1 hidden grid-cols-6 gap-2 text-xs font-semibold text-gray-600 md:grid dark:text-gray-300">
                            <div>Level</div>
                            <div>Name</div>
                            <div>Type</div>
                            <div>Condition</div>
                            <div>Start Nominal</div>
                            <div>End Nominal</div>
                        </div>

                        <div id="linesContainer" class="space-y-2">
                            {{-- baris dynamic via JS --}}
                        </div>
                    </div>

                    {{-- template options username (hidden) --}}
                    <select id="usernameOptionsTemplate" class="hidden">
                        <option value="">-- choose --</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->username }}">{{ $u->name }}</option>
                        @endforeach
                    </select>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeApprovalModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Select2 CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {

            // ===== DataTable =====
            let table = $('#approvalTable').DataTable({
                ajax: {
                    url: "{{ route('approvals.json') }}",
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
                        className: 'text-center col-actions',
                        render: function(data, type, row) {
                            return `
                                        <div class="flex justify-center space-x-2">
                                            <label class="switch">
                                                <input type="checkbox" class="toggleStatus"
                                                    data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                                <span class="slider round"></span>
                                            </label>
                                            <button class="editApprovalBtn bg-blue-500 text-white px-2 py-1 rounded"
                                                data-id="${data}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    `;
                        }
                    },
                    {
                        data: 'aprv_leveling',
                        className: 'text-center col-level'
                    },
                    {
                        data: 'aprv_doctype',
                        className: 'col-doctype'
                    },
                    {
                        data: 'aprv_cpnyid'
                    },
                    {
                        data: 'aprv_departementid'
                    },
                    {
                        data: 'aprv_name',
                        className: 'col-name'
                    },
                    {
                        data: 'aprv_type'
                    },
                    {
                        data: 'aprv_condition'
                    },
                    {
                        data: 'aprv_start_nominal',
                        className: 'text-right col-start',
                        render: function(data) {
                            return data ? parseFloat(data).toLocaleString('id-ID') : '';
                        }
                    },
                    {
                        data: 'aprv_end_nominal',
                        className: 'text-right col-end',
                        render: function(data) {
                            return data ? parseFloat(data).toLocaleString('id-ID') : '';
                        }
                    },
                    {
                        data: 'status',
                        className: 'text-center col-status',
                        render: function(data) {
                            return data === 'A' ?
                                '<span class="bg-green-300/30 text-green-600 font-semibold px-4 py-1 rounded">Active</span>' :
                                '<span class="bg-red-300/30 text-red-600 font-semibold px-4 py-1 rounded">Inactive</span>';
                        }
                    }
                ]
            });

            // ===== INIT select2 (header + filter) =====
            // ==== Select2 untuk field di dalam modal ====
            $('#aprv_doctype, #aprv_departementid, #aprv_cpnyid_select').select2({
                width: '100%',
                dropdownParent: $('#approvalModal') // ini oke karena memang di dalam modal
            });

            // ==== Select2 untuk FILTER di atas tabel (di luar modal) ====
            $('#filterDoctype, #filterCompany, #filterDept').select2({
                width: '100%' // TANPA dropdownParent
            });


            function applyColumnFilter(selectId, colIndex) {
                $(selectId).on('change', function() {
                    const val = $(this).val();
                    if (val) {
                        const regex = '^' + $.fn.dataTable.util.escapeRegex(val) + '$';
                        table.column(colIndex).search(regex, true, false).draw();
                    } else {
                        table.column(colIndex).search('', false, false).draw();
                    }
                });
            }

            // kolom: 2 = doctype, 3 = company, 4 = department
            applyColumnFilter('#filterDoctype', 3);
            applyColumnFilter('#filterCompany', 4);
            applyColumnFilter('#filterDept', 5);

            $('#clearUserFilters').on('click', function() {
                $('#filterDoctype').val('').trigger('change');
                $('#filterCompany').val('').trigger('change');
                $('#filterDept').val('').trigger('change');

                table.search('').columns().search('').draw();
            });

            // ===== Helper baris approval line =====
            function lineRowTemplate(idx, data) {
                const level = data && data.aprv_leveling ? data.aprv_leveling : '';
                const typeVal = data && data.aprv_type ? data.aprv_type : '';
                const condVal = data && data.aprv_condition ? data.aprv_condition : '';
                const startNom = data && data.aprv_start_nominal ? data.aprv_start_nominal : '';
                const endNom = data && data.aprv_end_nominal ? data.aprv_end_nominal : '';

                return `
                            <div class="grid grid-cols-1 items-start gap-2 md:grid-cols-6 line-row" data-row="${idx}">
                                <div>
                                    <label class="md:hidden text-xs text-gray-500 dark:text-gray-300">Level</label>
                                    <input type="text"
                                        name="aprv_leveling[]"
                                        class="level-input w-full rounded-lg border px-2 py-1 text-xs dark:bg-gray-700"
                                        value="${level}"
                                        placeholder="0.00"
                                        inputmode="decimal"
                                        autocomplete="off"
                                        required>
                                </div>

                                <div>
                                    <label class="md:hidden text-xs text-gray-500 dark:text-gray-300">Name</label>
                                    <select name="aprv_username[${idx}][]"
                                            class="w-full rounded-lg border px-2 py-1 text-xs sel-username dark:bg-gray-700"
                                            multiple
                                            required>
                                    </select>
                                </div>

                                <div>
                                    <label class="md:hidden text-xs text-gray-500 dark:text-gray-300">Type</label>
                                    <select name="aprv_type[]"
                                            class="w-full rounded-lg border px-2 py-1 text-xs sel-type dark:bg-gray-700">
                                        <option value=""></option>
                                        @foreach ($type as $t)
                                            <option value="{{ $t->category_name }}"
                                                \${typeVal === '{{ $t->category_name }}' ? 'selected' : ''}>
                                                {{ $t->category_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="md:hidden text-xs text-gray-500 dark:text-gray-300">Condition</label>
                                    <select name="aprv_condition[]"
                                            class="w-full rounded-lg border px-2 py-1 text-xs sel-condition dark:bg-gray-700">
                                        <option value=""></option>
                                        @foreach ($condition as $c)
                                            <option value="{{ $c->category_name }}"
                                                \${condVal === '{{ $c->category_name }}' ? 'selected' : ''}>
                                                {{ $c->category_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="md:hidden text-xs text-gray-500 dark:text-gray-300">Start Nominal</label>
                                    <input type="text"
                                        name="aprv_start_nominal[]"
                                        class="nominal-input w-full rounded-lg border px-2 py-1 text-xs dark:bg-gray-700"
                                        value="${startNom}"
                                        inputmode="decimal"
                                        autocomplete="off">
                                </div>

                                <div class="flex gap-2">
                                    <div class="flex-1">
                                        <label class="md:hidden text-xs text-gray-500 dark:text-gray-300">End Nominal</label>
                                        <input type="text"
                                            name="aprv_end_nominal[]"
                                            class="nominal-input w-full rounded-lg border px-2 py-1 text-xs dark:bg-gray-700"
                                            value="${endNom}"
                                            inputmode="decimal"
                                            autocomplete="off">
                                    </div>
                                    <button type="button"
                                            class="removeLineBtn self-center rounded bg-red-500 px-2 py-1 text-xs font-semibold text-white">
                                        ✕
                                    </button>
                                </div>
                            </div>
                        `;
            }


            let lineIdxCounter = 0;

            function addLineRow(data) {
                const idx = lineIdxCounter++;
                $('#linesContainer').append(lineRowTemplate(idx, data || {}));

                const $row = $('#linesContainer').find(`.line-row[data-row="${idx}"]`);

                // isi option username dari template
                const optionsHtml = $('#usernameOptionsTemplate').html();
                const $usernameSelect = $row.find('.sel-username');
                $usernameSelect.html(optionsHtml);

                // init select2 multiple (dropdownParent modal)
                $usernameSelect.select2({
                    width: '100%',
                    dropdownParent: $('#approvalModal')
                });

                // kalau edit, pre-select username
                if (data && data.aprv_username) {
                    let selected = data.aprv_username;
                    if (typeof selected === 'string') {
                        selected = selected.split(',').map(s => s.trim()).filter(Boolean);
                    }
                    $usernameSelect.val(selected).trigger('change');
                }
            }

            // ADD line
            $('#addLineBtn').on('click', function() {
                addLineRow();
            });

            // Hapus line (minimal 1)
            $(document).on('click', '.removeLineBtn', function() {
                const total = $('#linesContainer .line-row').length;
                if (total <= 1) return;
                $(this).closest('.line-row').remove();
            });

            // ADD Approval
            $('#addApprovalBtn').click(function() {
                $('#approvalModalTitle').text("Add Approval");
                $('#approvalForm')[0].reset();
                $('#id').val('');
                $('#addLineBtn').removeClass('hidden');
                $('#linesContainer').empty();

                $('#aprv_doctype').val('').trigger('change');
                $('#aprv_departementid').val('').trigger('change');
                $('#aprv_cpnyid_select').val('').trigger('change');

                lineIdxCounter = 0;
                addLineRow();
                $('#approvalModal').removeClass('hidden');
            });

            // EDIT Approval
            $(document).on('click', '.editApprovalBtn', function() {
                let id = $(this).data('id');

                $('#approvalModalTitle').text("Loading...");
                $('#approvalForm')[0].reset();
                $('#id').val(id);
                $('#linesContainer').empty();
                $('#addLineBtn').addClass('hidden');

                lineIdxCounter = 0;

                $('#approvalModal').removeClass('hidden');

                $.get(`/approvals/${id}/edit`, function(data) {
                    $('#approvalModalTitle').text("Edit Approval");

                    $('#aprv_doctype').val(data.aprv_doctype).trigger('change');
                    $('#aprv_departementid').val(data.aprv_departementid).trigger('change');
                    $('#aprv_cpnyid_select').val(data.aprv_cpnyid).trigger('change');

                    addLineRow({
                        aprv_leveling: data.aprv_leveling,
                        aprv_username: data.aprv_username,
                        aprv_type: data.aprv_type,
                        aprv_condition: data.aprv_condition,
                        aprv_start_nominal: data.aprv_start_nominal,
                        aprv_end_nominal: data.aprv_end_nominal,
                    });
                });
            });

            // Toggle status
            $(document).on('change', '.toggleStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/approvals/${id}/toggle-status`,
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
            $('#approvalForm').submit(function(e) {
                e.preventDefault();

                const dt = $('#aprv_doctype').val();
                const cp = $('#aprv_cpnyid_select').val();
                const dep = $('#aprv_departementid').val();

                if (!dt || !cp || !dep) {
                    alert('Doctype, Company, dan Department wajib diisi.');
                    return;
                }

                let errorMsg = null;

                $('.level-input').each(function() {
                    const v = $(this).val().trim();
                    if (!isValidDecimal2(v)) {
                        errorMsg = 'Level harus angka dengan maksimal 2 angka di belakang koma.';
                        $(this).focus();
                        return false;
                    }
                });
                if (errorMsg) {
                    alert(errorMsg);
                    return;
                }

                $('.nominal-input').each(function() {
                    const v = $(this).val().trim();
                    if (!isValidNumeric(v)) {
                        errorMsg = 'Nominal hanya boleh angka (boleh desimal).';
                        $(this).focus();
                        return false;
                    }
                });
                if (errorMsg) {
                    alert(errorMsg);
                    return;
                }

                let id = $('#id').val();
                let url = id ? `/approvals/${id}` : "{{ route('approvals.store') }}";
                let method = 'POST';
                let formEl = document.getElementById('approvalForm');
                let formData = new FormData(formEl);

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
                        $('#approvalModal').addClass('hidden');
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        let msg = 'Gagal menyimpan data approval';
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

            $('#closeApprovalModal').click(function() {
                $('#approvalModal').addClass('hidden');
            });
        });

        function isValidDecimal2(val) {
            if (val === '') return true;
            return /^\d+(\.\d{1,2})?$/.test(val);
        }

        function isValidNumeric(val) {
            if (val === '') return true;
            return /^-?\d*(\.\d+)?$/.test(val);
        }

        $(document).on('input', '.level-input', function() {
            let v = $(this).val();
            v = v.replace(/[^0-9.]/g, '');
            let parts = v.split('.');
            if (parts.length > 2) {
                v = parts[0] + '.' + parts.slice(1).join('');
                parts = v.split('.');
            }
            if (parts.length === 2 && parts[1].length > 2) {
                v = parts[0] + '.' + parts[1].slice(0, 2);
            }
            $(this).val(v);
        });

        $(document).on('input', '.nominal-input', function() {
            let v = $(this).val();
            v = v.replace(/[^0-9.]/g, '');
            let parts = v.split('.');
            if (parts.length > 2) {
                v = parts[0] + '.' + parts.slice(1).join('');
            }
            $(this).val(v);
        });
    </script>

</x-app-layout>
