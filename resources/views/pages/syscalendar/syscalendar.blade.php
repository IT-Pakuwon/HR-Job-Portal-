<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'sys-calendar' ? 'Calendar Exception' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-bold text-gray-800 dark:text-white">📅 Calendar Exception List</h1>
                <button id="addCalendarBtn"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    + Add Calendar
                </button>
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-white">Month</label>
                    <select id="filterMonth" class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        <option value="">All Month</option>
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-white">Year</label>
                    <select id="filterYear" class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        <option value="">All Year</option>
                        @for ($y = date('Y') + 2; $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button id="btnFilter"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Filter
                    </button>

                    <button id="btnResetFilter"
                        class="rounded-lg bg-gray-500 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-600">
                        Reset
                    </button>
                </div>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="calendarTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th class="w-32 px-4 py-3 text-center">Actions</th>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Perpost</th>
                            <th class="px-4 py-3 text-left">Description</th>
                            <th class="px-4 py-3 text-left">Type</th>
                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <div id="calendarModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 px-4">
            <div class="relative w-full max-w-2xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="modalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add Calendar Exception</h2>

                <form id="calendarForm">
                    @csrf
                    <input type="hidden" id="id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Date Calendar</label>
                            <input type="date" id="date_calendar" name="date_calendar"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Type</label>
                            <select id="date_calendar_type" name="date_calendar_type"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                <option value="">-- Select Type --</option>
                                <option value="CUTI_BERSAMA">CUTI_BERSAMA</option>
                                <option value="LIBUR_NASIONAL">LIBUR_NASIONAL</option>
                            </select>
                        </div>

                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Description</label>
                            <input type="text" id="date_calendar_descr" name="date_calendar_descr"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="loadingOverlay"
        class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/40">
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
            $('#loadingOverlay').removeClass('hidden');
        }

        function hideLoading() {
            $('#loadingOverlay').addClass('hidden');
        }
        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#calendarTable')) {
                $('#calendarTable').DataTable().clear().destroy();
            }

            let table = $('#calendarTable').DataTable({
                ajax: {
                    url: "{{ route('sys-calendar.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.month = $('#filterMonth').val();
                        d.year = $('#filterYear').val();
                    },
                    dataSrc: "data",
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
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false,
                        searchable: false
                    },
                    {
                        targets: 1,
                        orderable: false,
                        searchable: false
                    }
                ],
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'Calendar Exception',
                        className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                        exportOptions: {
                            columns: [2, 3, 4, 5, 6]
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '↓ CSV',
                        title: 'Calendar Exception',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: [2, 3, 4, 5, 6]
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
                                        <input type="checkbox" class="toggleStatus"
                                            data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                    <button class="editCalendarBtn bg-blue-500 text-white px-2 py-1 rounded"
                                        data-id="${data}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'date_calendar',
                        className: 'no-pointer'
                    },
                    {
                        data: 'perpost_year',
                        className: 'no-pointer'
                    },
                    {
                        data: 'date_calendar_descr',
                        className: 'no-pointer'
                    },
                    {
                        data: 'date_calendar_type',
                        className: 'no-pointer'
                    },
                    {
                        data: 'status',
                        className: 'no-pointer text-center',
                        render: function(data) {
                            return data === 'A'
                                ? '<span class="inline-block rounded bg-green-300/30 px-4 py-2 font-semibold text-green-600">Active</span>'
                                : '<span class="inline-block rounded bg-red-300/30 px-4 py-2 font-semibold text-red-600">Inactive</span>';
                        }
                    }
                ]
            });

            $('#btnFilter').click(function() {
                showLoading();
                table.ajax.reload(function() {
                    hideLoading();
                });
            });

            $('#btnResetFilter').click(function() {
                $('#filterMonth').val('');
                $('#filterYear').val('');
                showLoading();
                table.ajax.reload(function() {
                    hideLoading();
                });
            });

            $('#addCalendarBtn').click(function() {
                $('#modalTitle').text("Add Calendar Exception");
                $('#calendarForm')[0].reset();
                $('#id').val('');
                $('#calendarModal').removeClass('hidden');
            });

            $(document).on('click', '.editCalendarBtn', function() {
                let id = $(this).data('id');

                $.get(`/sys-calendar/${id}/edit`, function(c) {
                    $('#modalTitle').text("Edit Calendar Exception");
                    $('#id').val(c.id);
                    $('#date_calendar').val(c.date_calendar);
                    $('#date_calendar_descr').val(c.date_calendar_descr);
                    $('#date_calendar_type').val(c.date_calendar_type);
                    $('#calendarModal').removeClass('hidden');
                }).fail(function(xhr) {
                    console.error(xhr.responseText);
                    alert('Gagal mengambil data calendar');
                });
            });

            $(document).on('change', '.toggleStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/sys-calendar/${id}/toggle-status`,
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
                        console.error(xhr.responseText);
                        alert('Gagal update status');
                        table.ajax.reload(null, false);
                    }
                });
            });

            $('#calendarForm').submit(function(e) {
                e.preventDefault();

                let id = $('#id').val();
                let url = id ? `/sys-calendar/${id}` : "{{ route('sys-calendar.store') }}";
                let formData = new FormData(document.getElementById('calendarForm'));

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
                        $('#calendarModal').addClass('hidden');
                        $('#calendarForm')[0].reset();
                        $('#id').val('');
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Gagal menyimpan data calendar');
                    }
                });
            });

            $('#closeModal').click(function() {
                $('#calendarModal').addClass('hidden');
            });
        });
    </script>
</x-app-layout>