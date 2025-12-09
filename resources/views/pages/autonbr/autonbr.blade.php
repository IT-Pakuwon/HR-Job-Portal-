<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="mb-8 sm:flex sm:items-center sm:justify-between"></div>

        <div class="grid">
            <style>
                .grid { width: 100%; }
                table.dataTable { width: 100% !important; }
                .dataTables_wrapper { width: 100%; }

                #autonbrTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    align-items: center;
                }
                #autonbrTable_filter input {
                    width: auto;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }

                #autonbrTable_length {
                    display: flex;
                    justify-content: flex-start;
                }
                #autonbrTable_length select {
                    width: 80px;
                    padding: 5px;
                }

                #autonbrTable tbody tr:hover {
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
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">🔢 Autonumber Setup</h2>
                    <button id="addAutonbrBtn" class="rounded-lg bg-indigo-500 px-5 py-2 text-white">
                        + Add Autonbr
                    </button>
                </div>

                <table id="autonbrTable" class="w-full table-fixed border-collapse">
                    <thead class="bg-white dark:bg-gray-700">
                        <tr>
                            <th class="w-32 px-4 py-3 text-center">Actions</th>
                            <th class="px-4 py-3 text-left">Doctype</th>
                            <th class="w-24 px-4 py-3 text-right">Year</th>
                            <th class="w-24 px-4 py-3 text-right">Month</th>
                            <th class="w-32 px-4 py-3 text-right">Number</th>
                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            {{-- Modal --}}
            <div id="autonbrModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
                <div class="relative w-full max-w-xl rounded-lg bg-white p-6 dark:bg-gray-700">
                    <h2 id="autonbrModalTitle" class="mb-4 text-xl font-bold text-gray-800 dark:text-white">
                        Add Autonbr
                    </h2>

                    <form id="autonbrForm">
                        @csrf
                        <input type="hidden" id="id" name="id">

                        {{-- Doctype + Year --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700 dark:text-white mb-1">Doctype</label>
                                <input type="text" id="doctype" name="doctype"
                                       class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700"
                                       placeholder="mis: SPPB, CS, PO"
                                       required>
                            </div>
                            <div>
                                <label class="block text-gray-700 dark:text-white mb-1">Year</label>
                                <input type="number" id="year" name="year"
                                       class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700"
                                       min="2000" max="2100" required>
                            </div>
                        </div>

                        {{-- Month + Number --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700 dark:text-white mb-1">Month</label>
                                <select id="month" name="month"
                                        class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700"
                                        required>
                                    <option value="">-- choose --</option>
                                    @for($m=1; $m<=12; $m++)
                                        <option value="{{ $m }}">{{ sprintf('%02d', $m) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-700 dark:text-white mb-1">Number</label>
                                <input type="number" id="number" name="number" value="0"
                                       class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700"
                                       min="0" required>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end space-x-2">
                            <button type="button" id="closeAutonbrModal"
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

            <script>
                $(document).ready(function () {

                    // ===== DataTable =====
                    let table = $('#autonbrTable').DataTable({
                        ajax: {
                            url: "{{ route('autonbrs.json') }}",
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
                                            <button class="editAutonbrBtn bg-blue-500 text-white px-2 py-1 rounded"
                                                data-id="${data}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    `;
                                }
                            },
                            { data: 'doctype' },
                            {
                                data: 'year',
                                className: 'text-right'
                            },
                            {
                                data: 'month',
                                className: 'text-right',
                                render: function (data) {
                                    if (!data) return '';
                                    let m = parseInt(data, 10);
                                    if (isNaN(m)) return data;
                                    return m.toString().padStart(2, '0');
                                }
                            },
                            {
                                data: 'number',
                                className: 'text-right',
                                render: function (data) {
                                    return data ? parseInt(data, 10).toLocaleString('id-ID') : '0';
                                }
                            },
                            {
                                data: 'status',
                                className: 'text-center',
                                render: function (data) {
                                    return data === 'A'
                                        ? '<span class="bg-green-300/30 text-green-600 font-semibold px-3 py-1 rounded">Active</span>'
                                        : '<span class="bg-red-300/30 text-red-600 font-semibold px-3 py-1 rounded">Inactive</span>';
                                }
                            }
                        ]
                    });

                    // ===== Add =====
                    $('#addAutonbrBtn').click(function () {
                        $('#autonbrModalTitle').text("Add Autonbr");
                        $('#autonbrForm')[0].reset();
                        $('#id').val('');
                        $('#autonbrModal').removeClass('hidden');
                    });

                    // ===== Edit =====
                    $(document).on('click', '.editAutonbrBtn', function () {
                        let id = $(this).data('id');

                        $('#autonbrModalTitle').text("Loading...");
                        $('#autonbrForm')[0].reset();
                        $('#id').val(id);
                        $('#autonbrModal').removeClass('hidden');

                        $.get(`/autonbrs/${id}/edit`, function (data) {
                            $('#autonbrModalTitle').text("Edit Autonbr");

                            $('#doctype').val(data.doctype);
                            $('#year').val(data.year);
                            $('#month').val(data.month);
                            $('#number').val(data.number);
                        });
                    });

                    // ===== Toggle Status =====
                    $(document).on('change', '.toggleStatus', function () {
                        let id = $(this).data('id');
                        let newStatus = $(this).is(':checked') ? 'A' : 'X';

                        $.ajax({
                            url: `/autonbrs/${id}/toggle-status`,
                            type: 'PUT',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: { status: newStatus },
                            success: function () {
                                table.ajax.reload(null, false);
                            }
                        });
                    });

                    // ===== Submit (create / update) =====
                    $('#autonbrForm').submit(function (e) {
                        e.preventDefault();

                        let id      = $('#id').val();
                        let url     = id ? `/autonbrs/${id}` : "{{ route('autonbrs.store') }}";
                        let method  = 'POST';
                        let formEl  = document.getElementById('autonbrForm');
                        let formData = new FormData(formEl);

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
                                $('#autonbrModal').addClass('hidden');
                                table.ajax.reload();
                            },
                            error: function (xhr) {
                                console.error(xhr.responseText);

                                let msg = 'Gagal menyimpan autonbr';

                                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                                    msg = 'Mohon periksa input:\n';
                                    Object.values(xhr.responseJSON.errors).forEach(function (arr) {
                                        msg += '- ' + arr.join(', ') + '\n';
                                    });
                                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }

                                alert(msg);
                            }
                        });
                    });

                    $('#closeAutonbrModal').click(function () {
                        $('#autonbrModal').addClass('hidden');
                    });
                });
            </script>
        </div>
    </div>
</x-app-layout>
