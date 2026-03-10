<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'attachments-master' ? 'Attachments Master' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-bold text-gray-800 dark:text-white">📎 Attachment List</h1>
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-5">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-white">Company</label>
                    <select id="filterCompany" class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        <option value="">All Company</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->cpny_id }}">
                                {{ $company->cpny_id }} - {{ $company->cpny_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-white">Doc Type</label>
                    <input type="text" id="filterDoctype"
                        class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700"
                        placeholder="PB / PJ / PO / CS">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-white">Search</label>
                    <input type="text" id="filterSearch"
                        class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700"
                        placeholder="Refnbr / name / filename / folder">
                </div>

                <div class="flex items-end gap-2 md:col-span-2">
                    <button type="button" id="btnFilter"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Filter
                    </button>

                    <button type="button" id="btnResetFilter"
                        class="rounded-lg bg-gray-500 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-600">
                        Reset
                    </button>
                </div>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="attachmentTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th class="w-24 px-4 py-3 text-center">Actions</th>
                            <th class="px-4 py-3 text-left">Ref Nbr</th>
                            <th class="px-4 py-3 text-left">Doc Type</th>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Company</th>
                            <th class="px-4 py-3 text-left">Department</th>
                            <th class="px-4 py-3 text-left">Attachment Name</th>
                            <th class="px-4 py-3 text-left">Folder</th>
                            <th class="px-4 py-3 text-left">Filename</th>
                            <th class="px-4 py-3 text-left">Filesize</th>
                            <th class="px-4 py-3 text-left">Extension</th>
                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#attachmentTable')) {
                $('#attachmentTable').DataTable().clear().destroy();
            }

            let table = $('#attachmentTable').DataTable({
                ajax: {
                    url: "{{ route('attachments-master.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.cpny_id = $('#filterCompany').val();
                        d.doctype = $('#filterDoctype').val();
                        d.search = $('#filterSearch').val();
                    },
                    dataSrc: "data",
                    error: function(xhr) {
                        console.error('AJAX Error:', xhr.responseText);
                    }
                },
                processing: true,
                serverSide: false,
                autoWidth: false,
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
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'Attachment List',
                        className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                        exportOptions: {
                            columns: [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '↓ CSV',
                        title: 'Attachment List',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
                        }
                    }
                ],
                columns: [{
                        data: null,
                        defaultContent: ''
                    },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            return `
                                <div class="flex justify-center">
                                    <label class="switch">
                                        <input type="checkbox" class="toggleStatus"
                                            data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'refnbr',
                        defaultContent: '-'
                    },
                    {
                        data: 'doctype',
                        defaultContent: '-'
                    },
                    {
                        data: 'attachment_date',
                        defaultContent: '-'
                    },
                    {
                        data: 'cpny_id',
                        defaultContent: '-'
                    },
                    {
                        data: 'department_id',
                        defaultContent: '-'
                    },
                    {
                        data: 'attachment_name',
                        defaultContent: '-'
                    },
                    {
                        data: 'folder',
                        defaultContent: '-'
                    },
                    {
                        data: 'filename',
                        defaultContent: '-'
                    },
                    {
                        data: 'filesize',
                        defaultContent: '-'
                    },
                    {
                        data: 'extention',
                        defaultContent: '-'
                    },
                    {
                        data: 'status',
                        className: 'text-center',
                        render: function(data) {
                            return data === 'A' ?
                                '<span class="inline-block rounded bg-green-300/30 px-4 py-2 font-semibold text-green-600">Active</span>' :
                                '<span class="inline-block rounded bg-red-300/30 px-4 py-2 font-semibold text-red-600">Inactive</span>';
                        }
                    }
                ]
            });

            $('#btnFilter').click(function() {
                table.ajax.reload(null, true);
            });

            $('#btnResetFilter').click(function() {
                $('#filterCompany').val('');
                $('#filterDoctype').val('');
                $('#filterSearch').val('');
                table.ajax.reload(null, true);
            });

            $('#filterSearch').on('keypress', function(e) {
                if (e.which === 13) {
                    table.ajax.reload(null, true);
                }
            });

            $(document).on('change', '.toggleStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/attachments-master/${id}/toggle-status`,
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
        });
    </script>
</x-app-layout>