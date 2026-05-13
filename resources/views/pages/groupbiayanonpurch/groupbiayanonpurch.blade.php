<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-bold text-gray-800 dark:text-white">
                    💰 Group Biaya Non Purchase List
                </h1>

                <button id="addGroupBiayaBtn"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    + Add Group Biaya
                </button>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="groupBiayaTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th class="w-32 px-4 py-3 text-center">Actions</th>
                            <th class="px-4 py-3 text-left">Group Biaya ID</th>
                            <th class="px-4 py-3 text-left">Description</th>
                            <th class="w-32 px-4 py-3 text-center">Is Budget</th>
                            <th class="w-32 px-4 py-3 text-center">Is Deposit</th>
                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        {{-- Modal --}}
        <div id="groupBiayaModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-4xl rounded-lg bg-white p-6 dark:bg-gray-700">
                <h2 id="modalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">
                    Add Group Biaya
                </h2>

                <form id="groupBiayaForm">
                    @csrf
                    <input type="hidden" id="id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Group Biaya ID</label>
                            <input type="text" id="groupbiaya_id" name="groupbiaya_id"
                                value="{{ $nextGroupbiayaId ?? '' }}"
                                class="w-full cursor-not-allowed rounded-lg border bg-gray-100 px-3 py-2 text-gray-600 dark:bg-gray-600 dark:text-gray-300"
                                readonly required>
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Description</label>
                            <input type="text" id="groupbiayadescr" name="groupbiayadescr"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Is Budget</label>
                            <select id="is_budget" name="is_budget"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                                <option value="false">No</option>
                                <option value="true">Yes</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Is Deposit</label>
                            <select id="is_deposit" name="is_deposit"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                                <option value="false">No</option>
                                <option value="true">Yes</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeModal"
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

    {{-- Loading --}}
    <div id="loadingOverlay"
        class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/40">
        <div class="flex items-center gap-3 rounded-xl bg-white px-6 py-4 shadow-lg">
            <svg class="h-6 w-6 animate-spin text-indigo-600" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10"
                    stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span class="text-sm font-semibold text-gray-700">Processing...</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function showLoading() {
            $('#loadingOverlay').removeClass('hidden').addClass('flex');
        }

        function hideLoading() {
            $('#loadingOverlay').removeClass('flex').addClass('hidden');
        }

        function ynBadge(value) {
            const v = String(value ?? '').toLowerCase();

            if (
                value === true ||
                value === 1 ||
                v === '1' ||
                v === 'true' ||
                v === 't' ||
                v === 'y'
            ) {
                return '<span class="bg-green-300/30 text-green-600 font-semibold px-4 py-1 rounded">Yes</span>';
            }

            return '<span class="bg-gray-300/30 text-gray-600 font-semibold px-4 py-1 rounded">No</span>';
        }

        function normalizeBool(value) {
            if (value === true || value === 1 || value === '1' || value === 'true' || value === 't' || value === 'Y') {
                return 'true';
            }

            return 'false';
        }

        $(document).ready(function() {
            let nextGroupbiayaId = @json($nextGroupbiayaId ?? '');
            let table = $('#groupBiayaTable').DataTable({
                ajax: {
                    url: "{{ route('groupbiayanonpurch.json') }}",
                    type: "GET",
                    cache: false,
                    data: function(d) {
                        d._t = new Date().getTime();
                    },
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
                        title: 'Group_Biaya_Non_Purchase',
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
                        title: 'Group_Biaya_Non_Purchase',
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
                    },
                    {
                        data: 'id',
                        className: 'text-center',
                        render: function(data, type, row) {
                            return `
                                <div class="flex justify-center space-x-2">
                                    <label class="switch">
                                        <input type="checkbox" class="toggleStatus"
                                            data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>

                                    <button class="editGroupBiayaBtn bg-blue-500 text-white px-2 py-1 rounded"
                                        data-id="${data}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'groupbiaya_id'
                    },
                    {
                        data: 'groupbiayadescr'
                    },
                    {
                        data: 'is_budget',
                        className: 'text-center',
                        render: function(data) {
                            return ynBadge(data);
                        }
                    },
                    {
                        data: 'is_deposit',
                        className: 'text-center',
                        render: function(data) {
                            return ynBadge(data);
                        }
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

            $('#addGroupBiayaBtn').click(function() {
                $('#modalTitle').text("Add Group Biaya");
                $('#groupBiayaForm')[0].reset();
                $('#id').val('');

                $('#groupbiaya_id')
                    .val(nextGroupbiayaId)
                    .prop('readonly', true)
                    .addClass('cursor-not-allowed bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300');

                $('#is_budget').val('false');
                $('#is_deposit').val('false');

                $('#groupBiayaModal').removeClass('hidden');
            });

            $(document).on('click', '.editGroupBiayaBtn', function() {
                let id = $(this).data('id');

                showLoading();

                $.get(`/groupbiaya-nonpurch/${id}/edit`, function(row) {
                    $('#modalTitle').text("Edit Group Biaya");
                    $('#id').val(row.id);

                    $('#groupbiaya_id')
                        .val(row.groupbiaya_id)
                        .prop('readonly', true)
                        .addClass('cursor-not-allowed bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300');

                    $('#groupbiayadescr').val(row.groupbiayadescr);

                    $('#is_budget').val(normalizeBool(row.is_budget)).trigger('change');
                    $('#is_deposit').val(normalizeBool(row.is_deposit)).trigger('change');

                    $('#groupBiayaModal').removeClass('hidden');
                    hideLoading();
                }).fail(function(xhr) {
                    hideLoading();

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load group biaya data'
                    });

                    console.error(xhr.responseText);
                });
            });

            $(document).on('change', '.toggleStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/groupbiaya-nonpurch/${id}/toggle-status`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        status: newStatus
                    },
                    success: function() {
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        table.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Failed to update status'
                        });
                    }
                });
            });

            $('#groupBiayaForm').submit(function(e) {
                e.preventDefault();

                let id = $('#id').val();
                let url = id ? `/groupbiaya-nonpurch/${id}` : "{{ route('groupbiayanonpurch.store') }}";
                let method = 'POST';
                let formData = new FormData(document.getElementById('groupBiayaForm'));

                if (id) {
                    formData.append('_method', 'PUT');
                }

                showLoading();
                $('#groupBiayaForm button[type="submit"]').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: method,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        hideLoading();
                        $('#groupBiayaForm button[type="submit"]').prop('disabled', false);

                        if (!res || res.success === false) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: res?.message || 'Failed to save data'
                            });
                            return;
                        }

                        if (res.groupbiaya_id) {
                            const match = String(res.groupbiaya_id).match(/^GB(\d+)$/);

                            if (match) {
                                const nextNum = parseInt(match[1], 10) + 1;
                                nextGroupbiayaId = 'GB' + String(nextNum).padStart(3, '0');
                            }
                        }

                        $('#groupBiayaModal').addClass('hidden');
                        $('#groupBiayaForm')[0].reset();
                        $('#id').val('');

                        table.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: res.message || 'Group Biaya saved successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#groupBiayaForm button[type="submit"]').prop('disabled', false);

                        let msg = 'Gagal menyimpan data Group Biaya';

                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
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

                        console.error(xhr.responseText);
                    }
                });
            });

            $('#closeModal').click(function() {
                $('#groupBiayaForm')[0].reset();
                $('#id').val('');
                $('#groupBiayaModal').addClass('hidden');
            });
        });
    </script>
</x-app-layout>