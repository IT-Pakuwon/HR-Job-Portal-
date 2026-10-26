<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'attachments-master' ? 'Attachments Master' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <div
            class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div
                class="flex flex-row items-start justify-between gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] sm:flex-row sm:items-center">
                <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">📎 Attachment
                    List</h2>
            </div>

            <div class="grid grid-cols-1 gap-3 px-5 pt-4 md:grid-cols-5">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-white">Company</label>
                    <select id="filterCompany" class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" style="width:100%">
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
                    <select id="filterDoctype" class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700"
                        style="width:100%">
                        <option value="">All Doc Type</option>
                        @foreach ($doctypes as $doctype)
                            <option value="{{ $doctype }}">{{ $doctype }}</option>
                        @endforeach
                    </select>
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

            <div class="relative mt-4 overflow-hidden">
                <table id="attachmentTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th class="w-24 px-4 py-3 text-left font-medium">Actions</th>
                            <th class="px-4 py-3 text-left font-medium">Ref Nbr</th>
                            <th class="px-4 py-3 text-left font-medium">Doc Type</th>
                            <th class="px-4 py-3 text-left font-medium">Date</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Department</th>
                            <th class="px-4 py-3 text-left font-medium">Attachment Name</th>
                            <th class="px-4 py-3 text-left font-medium">Folder</th>
                            <th class="px-4 py-3 text-left font-medium">Filename</th>
                            <th class="px-4 py-3 text-left font-medium">Filesize</th>
                            <th class="px-4 py-3 text-left font-medium">Extension</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#filterDoctype').select2({
                width: '100%',
                placeholder: 'All Doc Type',
                allowClear: true
            });

            $('#filterCompany').select2({
                width: '100%',
                placeholder: 'All Company',
                allowClear: true
            });

            if ($.fn.DataTable.isDataTable('#attachmentTable')) {
                $('#attachmentTable').DataTable().clear().destroy();
            }


            // With serverSide, export buttons would only export the current page: fetch everything first.
            function exportAll(type) {
                return function(e, dt, button, config) {
                    const self = this;
                    const oldStart = dt.settings()[0]._iDisplayStart;
                    dt.one('preXhr', function(e, s, data) {
                        data.start = 0;
                        data.length = -1;
                        dt.one('preDraw', function(e, settings) {
                            $.fn.dataTable.ext.buttons[type].action.call(self, e, dt, button, config);
                            dt.one('preXhr', function(e, s, data) {
                                settings._iDisplayStart = oldStart;
                                data.start = oldStart;
                            });
                            setTimeout(dt.ajax.reload, 0);
                            return false;
                        });
                    });
                    dt.ajax.reload();
                };
            }
            let table = $('#attachmentTable').DataTable({
                ajax: {
                    url: "{{ route('attachments-master.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.cpny_id = $('#filterCompany').val();
                        d.doctype = $('#filterDoctype').val();
                        d.q = $('#filterSearch').val();
                    },
                    dataSrc: "data",
                    error: function(xhr) {
                        console.error('AJAX Error:', xhr.responseText);
                    }
                },
                processing: true,
                serverSide: true,
                searchDelay: 500,
                order: [],
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
                        action: exportAll('excelHtml5'),
                        text: '↓ Excel',
                        title: 'Attachment List',
                        className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                        exportOptions: {
                            columns: [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        action: exportAll('csvHtml5'),
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
                            const open = row.can_open ?
                                `<a href="/attachments-master/legacy/${row.id}/view" target="_blank" rel="noopener" title="Open file" class="ml-2 text-indigo-600 hover:text-indigo-800">&#128065;</a>` :
                                '';
                            return `
                                <div class="flex items-center justify-center">
                                    <label class="switch">
                                        <input type="checkbox" class="toggleStatus"
                                            data-id="${row.id}" data-source="${row.source}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                    ${open}
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
                        defaultContent: '-',
                        render: function(data, type, row) {
                            if (type !== 'display') return data;
                            return row.source === 'legacy' ?
                                `${data} <span class="ml-1 rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700">Legacy</span>` :
                                data;
                        }
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
                $('#filterCompany').val(null).trigger('change');
                $('#filterDoctype').val(null).trigger('change');
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
                        status: newStatus,
                        source: $(this).data('source')
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
