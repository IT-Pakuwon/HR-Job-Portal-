<x-app-layout>
    {{-- Select2 CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- Toastr --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <style>
        table.dataTable { width: 100% !important; }
        .dataTables_wrapper { width: 100%; }

        /* samakan style kedua tabel */
        #canvassTable_filter, #transferTable_filter {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }
        #canvassTable_filter input, #transferTable_filter input { width: 200px; }
        #canvassTable td, #transferTable td {
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        #canvassTable th, #canvassTable td,
        #transferTable th, #transferTable td { padding: 10px; max-width: 200px; }
        #canvassTable tbody tr:hover, #transferTable tbody tr:hover {
            background-color: #8f8f8f11;
            cursor: pointer;
        }
        #transferTable th:nth-child(3), #transferTable td:nth-child(3) { min-width: 220px; }
    </style>

    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">

        {{-- Header + Filters --}}
        <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="flex flex-col gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <h1 class="text-xl font-extrabold text-gray-700 dark:text-white">Purchasing Assignment</h1>

                    {{-- Filter doc_type --}}
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-semibold text-gray-600 dark:text-gray-300">Doc Type:</label>
                        <select id="docFilter"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:bg-gray-700 dark:text-white">
                            <option value="">All</option>
                            <option value="SPPB">SPPB</option>
                            <option value="SPPJ">SPPJ</option>
                            <option value="SPPK">SPPK</option>
                            <option value="SPPT">SPPT</option>
                        </select>
                    </div>
                </div>

                {{-- Tabs --}}
                <div class="flex gap-2">
                    <button type="button" data-tab="assign"
                        class="tab-btn rounded-xl px-4 py-2 text-sm font-semibold border border-gray-300 dark:border-gray-600 bg-gray-900 text-white">
                        Assign List
                    </button>
                    <button type="button" data-tab="transfer"
                        class="tab-btn rounded-xl px-4 py-2 text-sm font-semibold border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-white">
                        Transfer Jobs
                    </button>
                </div>
            </div>

            {{-- TAB: Assign List --}}
            <div id="tab-assign" class="p-4">
                <div class="flex justify-end pb-3">
                    <button type="button" id="btnAssignPurchasing"
                        class="inline-flex items-center rounded-xl bg-blue-600 px-6 py-2 text-base font-semibold text-white hover:bg-blue-700">
                        <i class="fas fa-check pr-2"></i>Assign Purchasing
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table id="canvassTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">DocID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Assign Purchasing</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Date</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Company</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Created By</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>
                </div>
            </div>

            {{-- TAB: Transfer Jobs --}}
            <div id="tab-transfer" class="p-4 hidden">
                <div class="flex justify-end pb-3">
                    <button type="button" id="btnTransferJobs"
                        class="inline-flex items-center rounded-xl bg-amber-600 px-6 py-2 text-base font-semibold text-white hover:bg-amber-700">
                        <i class="fas fa-exchange-alt pr-2"></i>Transfer Jobs
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table id="transferTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">DocID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Assign Purchasing</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Assign Purchasing New</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Date</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Company</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Created By</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script>
        $(function() {
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });

            let docTypeFilter = $('#docFilter').val() || '';
            let activeTab = 'assign'; // 'assign' | 'transfer'

            function docLink(row, text) {
                const map = { SPPB:'showsppbs', SPPJ:'showsppjs', SPPK:'showsppks', SPPT:'showsppts' };
                const base = map[row.doc_type] || '#';
                const url = `/${base}/${row.eid}`;
                return `<a href="${url}" class="inline-flex items-center rounded px-3 py-1.5 bg-blue-600 text-white hover:bg-blue-700 text-sm font-semibold">${text}</a>`;
            }

            // Assign List datatable
            const table = $('#canvassTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100, 250],
                ajax: {
                    url: "{{ route('assignlist.json') }}",
                    type: "GET",
                    data: function(d) { d.doc = docTypeFilter; }
                },
                order: [[2,'desc'],[0,'desc']],
                columns: [
                    { data:'doc_no', className:'text-left', render: (d,t,row)=>docLink(row,d) },
                    {
                        data:'assignpurchasing',
                        className:'text-left',
                        render: function(val,type,row){
                            const v = (val && val !== '0') ? String(val) : '';
                            return `
                                <select class="assign-select w-full"
                                    data-src-id="${row.src_id}"
                                    data-doc-type="${row.doc_type}"
                                    data-original="${v}">
                                    <option value=""></option>
                                    ${v ? `<option value="${v}" selected>${v}</option>` : ''}
                                </select>`;
                        }
                    },
                    { data:'doc_date', className:'text-center', render: d => d ? new Date(d).toLocaleDateString('id-ID') : '' },
                    { data:'cpny_id', className:'text-center w-32' },
                    { data:'created_by_name', className:'text-center' },
                    { data:'department_id', className:'text-center whitespace-normal break-words' },
                    { data:'keperluan', className:'text-left' },
                ],
                drawCallback: function() {
                    $('#canvassTable .assign-select').each(function() {
                        const $sel = $(this);
                        if ($sel.hasClass('select2-hidden-accessible')) return;

                        $sel.select2({
                            placeholder: '— pilih purchaser —',
                            allowClear: true,
                            width: 'resolve',
                            minimumInputLength: 2,
                            ajax: {
                                url: "{{ route('assignlist.users') }}",
                                dataType: 'json',
                                delay: 250,
                                data: params => ({ q: params.term || '' }),
                                processResults: data => ({ results: data.results || [] }),
                                cache: true
                            }
                        });
                    });
                }
            });

            // Transfer Jobs datatable
            const transferTable = $('#transferTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100, 250],
                ajax: {
                    url: "{{ route('transferjobs.json') }}",
                    type: "GET",
                    data: function(d) { d.doc = docTypeFilter; }
                },
                order: [[3,'desc'],[0,'desc']],
                columns: [
                    { data:'doc_no', className:'text-left', render: (d,t,row)=>docLink(row,d) },
                    { data:'assignpurchasing', className:'text-left' },
                    {
                        data:null,
                        className:'text-left',
                        render: function(_d,_t,row){
                            return `
                                <select class="transfer-select w-full"
                                    data-src-id="${row.src_id}"
                                    data-doc-type="${row.doc_type}"
                                    data-original="${row.assignpurchasing || ''}">
                                    <option value=""></option>
                                </select>`;
                        }
                    },
                    { data:'doc_date', className:'text-center', render: d => d ? new Date(d).toLocaleDateString('id-ID') : '' },
                    { data:'cpny_id', className:'text-center w-32' },
                    { data:'created_by_name', className:'text-center' },
                    { data:'department_id', className:'text-center whitespace-normal break-words' },
                    { data:'keperluan', className:'text-left' },
                ],
                drawCallback: function() {
                    $('#transferTable .transfer-select').each(function() {
                        const $sel = $(this);
                        if ($sel.hasClass('select2-hidden-accessible')) return;

                        $sel.select2({
                            placeholder: '— pilih purchaser baru —',
                            allowClear: true,
                            width: 'resolve',
                            minimumInputLength: 2,
                            ajax: {
                                url: "{{ route('assignlist.users') }}",
                                dataType: 'json',
                                delay: 250,
                                data: params => ({ q: params.term || '' }),
                                processResults: data => ({ results: data.results || [] }),
                                cache: true
                            }
                        });
                    });
                }
            });

            // Filter change -> reload table aktif saja (biar lebih cepat)
            $('#docFilter').on('change', function() {
                docTypeFilter = $(this).val() || '';
                if (activeTab === 'assign') table.ajax.reload(null, true);
                else transferTable.ajax.reload(null, true);
            });

            // Tabs switch
            $('.tab-btn').on('click', function() {
                const tab = $(this).data('tab');
                activeTab = tab;

                $('.tab-btn').removeClass('bg-gray-900 text-white').addClass('bg-white dark:bg-gray-800 text-gray-700 dark:text-white');
                $(this).addClass('bg-gray-900 text-white').removeClass('bg-white dark:bg-gray-800 text-gray-700 dark:text-white');

                $('#tab-assign').toggleClass('hidden', tab !== 'assign');
                $('#tab-transfer').toggleClass('hidden', tab !== 'transfer');

                // force redraw columns when showing hidden DataTable
                setTimeout(() => {
                    if (tab === 'assign') table.columns.adjust().draw(false);
                    else transferTable.columns.adjust().draw(false);
                }, 50);
            });

            // Assign Purchasing bulk
            $('#btnAssignPurchasing').on('click', function() {
                const items = [];
                $('#canvassTable .assign-select').each(function() {
                    const $s = $(this);
                    const val = $s.val() || '';
                    const orig = $s.data('original') || '';
                    if (val && val !== '0' && val !== orig) {
                        items.push({
                            doc_type: $s.data('doc-type'),
                            src_id: parseInt($s.data('src-id'), 10),
                            assignpurchasing: val
                        });
                    }
                });

                if (!items.length) { toastr.info('Tidak ada perubahan untuk di-assign.'); return; }

                const $btn = $('#btnAssignPurchasing').prop('disabled', true).text('Assigning...');
                $.post("{{ route('assignlist.assign') }}", { items })
                    .done(res => {
                        toastr.success(res.message || 'Assign Purchasing updated.');
                        table.ajax.reload(null, true);
                    })
                    .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Gagal assign.'))
                    .always(() => $btn.prop('disabled', false).html('<i class="fas fa-check pr-2"></i>Assign Purchasing'));
            });

            // Transfer Jobs bulk
            $('#btnTransferJobs').on('click', function() {
                const items = [];
                $('#transferTable .transfer-select').each(function() {
                    const $s = $(this);
                    const val  = $s.val() || '';
                    const orig = $s.data('original') || '';
                    if (val && val !== '0' && val !== orig) {
                        items.push({
                            doc_type: $s.data('doc-type'),
                            src_id: parseInt($s.data('src-id'), 10),
                            assignpurchasing_new: val
                        });
                    }
                });

                if (!items.length) { toastr.info('Tidak ada perubahan untuk transfer.'); return; }

                const $btn = $('#btnTransferJobs').prop('disabled', true).text('Transferring...');
                $.post("{{ route('transferjobs.update') }}", { items })
                    .done(res => {
                        toastr.success(res.message || 'Transfer Jobs updated.');
                        transferTable.ajax.reload(null, true);
                    })
                    .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Gagal transfer.'))
                    .always(() => $btn.prop('disabled', false).html('<i class="fas fa-exchange-alt pr-2"></i>Transfer Jobs'));
            });
        });
    </script>
</x-app-layout>
