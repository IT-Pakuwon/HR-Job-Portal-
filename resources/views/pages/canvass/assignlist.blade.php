<x-app-layout>
    <x-spp-list-styles />
    <x-spp-list-scripts />
    {{-- Select2 CDN --}}
    {{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> --}}

    {{-- Toastr --}}
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script> --}}

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- Header + Filters --}}
        <div class="dt-pretty rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

            <x-list-page-header title="Purchasing Assignment" subtitle="Assign canvass jobs to purchasers or transfer them">
                <div class="sppb-f relative w-44">
                    <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg></span>
                    <select id="docFilter">
                        <option value="">All Doc Type</option>
                        <option value="SPPB">SPPB</option>
                        <option value="SPPJ">SPPJ</option>
                        <option value="SPPK">SPPK</option>
                        <option value="SPPT">SPPT</option>
                    </select>
                </div>
            </x-list-page-header>

            <!-- TABS + ACTION -->
            <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-3 dark:border-white/[0.06] sm:flex-row sm:items-center sm:justify-between">

                <!-- Tabs -->
                <div class="inline-flex rounded-lg bg-gray-100 p-1 dark:bg-white/5">
                    <button type="button" data-tab="assign"
                        class="tab-btn rounded-md px-4 py-1.5 text-sm font-semibold transition">
                        Assign List
                    </button>

                    <button type="button" data-tab="transfer"
                        class="tab-btn rounded-md px-4 py-1.5 text-sm font-semibold transition">
                        Transfer Jobs
                    </button>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <button id="btnAssignPurchasing"
                        class="tab-action assign-action hidden inline-flex h-10 items-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                        <i class="fa-solid fa-plus mr-2 text-xs"></i>Assign Purchasing
                    </button>

                    <button id="btnTransferJobs"
                        class="tab-action transfer-action hidden inline-flex h-10 items-center rounded-lg bg-amber-500 px-5 text-sm font-medium text-white transition hover:bg-amber-600">
                        <i class="fas fa-exchange-alt pr-2"></i>Transfer Jobs
                    </button>
                </div>
            </div>

            {{-- TAB: Assign List --}}
            <div id="tab-assign" class="relative overflow-hidden">
                <table id="canvassTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th class="px-4 py-3 text-left font-medium">
                                DocID</th>
                            <th class="px-4 py-3 text-left font-medium">
                                Assign Purchasing</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Date</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Company</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Created By</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Department</th>
                            <th class="px-4 py-3 text-left font-medium">
                                Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
            </div>

            {{-- TAB: Transfer Jobs --}}
            <div id="tab-transfer" class="relative overflow-hidden">
                <table id="transferTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th class="px-4 py-3 text-left font-medium">
                                DocID</th>
                            <th class="px-4 py-3 text-left font-medium">
                                Assign Purchasing</th>
                            <th class="px-4 py-3 text-left font-medium">
                                Assign Purchasing New</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Date</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Company</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Created By</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Department</th>
                            <th class="px-4 py-3 text-left font-medium">
                                Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            let docTypeFilter = $('#docFilter').val() || '';
            let activeTab = 'assign'; // 'assign' | 'transfer'

            const dtControlColumn = {
                data: null,
                width: '28px',
                className: 'dtr-control',
                orderable: false,
                searchable: false,
                defaultContent: ''
            };

            function docLink(row, text) {
                const map = {
                    SPPB: 'showsppbs',
                    SPPJ: 'showsppjs',
                    SPPK: 'showsppks',
                    SPPT: 'showsppts'
                };
                const base = map[row.doc_type] || '#';
                const url = `/${base}/${row.eid}`;
                return `<a href="${url}" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide tabular-nums text-blue-700 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 group-hover/doc:-rotate-6"><i class="fa-regular fa-file-lines"></i></span><span>${text}</span></a>`;
            }

            // Assign List datatable
            const table = $('#canvassTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 10,
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
                    width: '28px',
                    className: 'dtr-control',
                    orderable: false
                }],
                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'List_Canvas',
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
                        title: 'List_Canvas',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                ajax: {
                    url: "{{ route('assignlist.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.doc = docTypeFilter;
                    }
                },
                order: [
                    [2, 'desc'],
                    [0, 'desc']
                ],
                columns: [
                    dtControlColumn,
                    {
                        data: 'doc_no',
                        className: 'text-left',
                        render: (d, t, row) => docLink(row, d)
                    },
                    {
                        data: 'assignpurchasing',
                        className: 'text-left',
                        render: function(val, type, row) {
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
                    {
                        data: 'doc_date',
                        className: 'text-center',
                        render: d => d ? new Date(d).toLocaleDateString('id-ID') : ''
                    },
                    {
                        data: 'cpny_id',
                        className: 'text-center w-32'
                    },
                    {
                        data: 'created_by_name',
                        className: 'text-center'
                    },
                    {
                        data: 'department_id',
                        className: 'text-center whitespace-normal break-words'
                    },
                    {
                        data: 'keperluan',
                        className: 'text-left'
                    },
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
                                data: params => ({
                                    q: params.term || ''
                                }),
                                processResults: data => ({
                                    results: data.results || []
                                }),
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
                    width: '28px',
                    className: 'dtr-control',
                    orderable: false
                }],
                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'List_TransferJob',
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
                        title: 'List_TransferJob',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                ajax: {
                    url: "{{ route('transferjobs.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.doc = docTypeFilter;
                    }
                },
                order: [
                    [3, 'desc'],
                    [0, 'desc']
                ],
                columns: [
                    dtControlColumn,
                    {
                        data: 'doc_no',
                        className: 'text-left',
                        render: (d, t, row) => docLink(row, d)
                    },
                    {
                        data: 'assignpurchasing',
                        className: 'text-left'
                    },
                    {
                        data: null,
                        className: 'text-left',
                        render: function(_d, _t, row) {
                            return `
                                <select class="transfer-select w-full"
                                    data-src-id="${row.src_id}"
                                    data-doc-type="${row.doc_type}"
                                    data-original="${row.assignpurchasing || ''}">
                                    <option value=""></option>
                                </select>`;
                        }
                    },
                    {
                        data: 'doc_date',
                        className: 'text-center',
                        render: d => d ? new Date(d).toLocaleDateString('id-ID') : ''
                    },
                    {
                        data: 'cpny_id',
                        className: 'text-center w-32'
                    },
                    {
                        data: 'created_by_name',
                        className: 'text-center'
                    },
                    {
                        data: 'department_id',
                        className: 'text-center whitespace-normal break-words'
                    },
                    {
                        data: 'keperluan',
                        className: 'text-left'
                    },
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
                                data: params => ({
                                    q: params.term || ''
                                }),
                                processResults: data => ({
                                    results: data.results || []
                                }),
                                cache: true
                            }
                        });
                    });
                }
            });

            // Filter change -> reload table aktif saja (biar lebih cepat)
            $('#docFilter').select2({ width: '100%', minimumResultsForSearch: Infinity, dropdownCssClass: 'sppb-s2-dropdown' });
            $('#docFilter').on('change', function() {
                docTypeFilter = $(this).val() || '';
                if (activeTab === 'assign') table.ajax.reload(null, true);
                else transferTable.ajax.reload(null, true);
            });

            function switchTab(tab) {
                activeTab = tab;

                // Tabs style
                $('.tab-btn')
                    .removeClass('bg-blue-600 text-white shadow-sm')
                    .addClass('text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white');

                $(`.tab-btn[data-tab="${tab}"]`)
                    .addClass('bg-blue-600 text-white shadow-sm')
                    .removeClass('text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white');

                // Tab content
                $('#tab-assign').toggleClass('hidden', tab !== 'assign');
                $('#tab-transfer').toggleClass('hidden', tab !== 'transfer');

                // Action buttons
                $('.tab-action').addClass('hidden');
                if (tab === 'assign') $('.assign-action').removeClass('hidden');
                if (tab === 'transfer') $('.transfer-action').removeClass('hidden');

                // DataTables adjust (SAFE)
                requestAnimationFrame(() => {
                    if (tab === 'assign' && window.table) {
                        table.columns.adjust().draw(false);
                    }

                    if (tab === 'transfer' && window.transferTable) {
                        transferTable.columns.adjust().draw(false);
                    }
                });
            }

            // Bind click
            $(document).on('click', '.tab-btn', function() {
                switchTab($(this).data('tab'));
            });

            // Default
            switchTab('assign');

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

                if (!items.length) {
                    toastr.info('Tidak ada perubahan untuk di-assign.');
                    return;
                }

                const $btn = $('#btnAssignPurchasing').prop('disabled', true).text('Assigning...');
                $.post("{{ route('assignlist.assign') }}", {
                        items
                    })
                    .done(res => {
                        toastr.success(res.message || 'Assign Purchasing updated.');
                        table.ajax.reload(null, true);
                    })
                    .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Gagal assign.'))
                    .always(() => $btn.prop('disabled', false).html(
                        '<i class="fas fa-check pr-2"></i>Assign Purchasing'));
            });

            // Transfer Jobs bulk
            $('#btnTransferJobs').on('click', function() {
                const items = [];
                $('#transferTable .transfer-select').each(function() {
                    const $s = $(this);
                    const val = $s.val() || '';
                    const orig = $s.data('original') || '';
                    if (val && val !== '0' && val !== orig) {
                        items.push({
                            doc_type: $s.data('doc-type'),
                            src_id: parseInt($s.data('src-id'), 10),
                            assignpurchasing_new: val
                        });
                    }
                });

                if (!items.length) {
                    toastr.info('Tidak ada perubahan untuk transfer.');
                    return;
                }

                const $btn = $('#btnTransferJobs').prop('disabled', true).text('Transferring...');
                $.post("{{ route('transferjobs.update') }}", {
                        items
                    })
                    .done(res => {
                        toastr.success(res.message || 'Transfer Jobs updated.');
                        transferTable.ajax.reload(null, true);
                    })
                    .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Gagal transfer.'))
                    .always(() => $btn.prop('disabled', false).html(
                        '<i class="fas fa-exchange-alt pr-2"></i>Transfer Jobs'));
            });
        });
    </script>
</x-app-layout>
