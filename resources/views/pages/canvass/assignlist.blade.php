<x-app-layout>
    {{-- Select2 CDN --}}
    {{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> --}}

    {{-- Toastr --}}
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script> --}}

    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">

        {{-- Header + Filters --}}
        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">

            <!-- TOP HEADER -->
            <div
                class="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700">

                <!-- Title -->
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">
                    Purchasing Assignment
                </h1>

                <!-- Filter -->
                <div class="flex items-center gap-2">
                    <label for="docFilter"
                        class="whitespace-nowrap text-xs font-semibold text-gray-600 dark:text-gray-300">
                        Doc Type:
                    </label>
                    <select id="docFilter"
                        class="min-w-[140px] rounded-lg border border-gray-300 px-3 py-2 text-xs dark:bg-gray-700 dark:text-white">
                        <option value="">All</option>
                        <option value="SPPB">SPPB</option>
                        <option value="SPPJ">SPPJ</option>
                        <option value="SPPK">SPPK</option>
                        <option value="SPPT">SPPT</option>
                    </select>
                </div>
            </div>

            <!-- TABS + ACTION -->
            <div class="flex flex-col gap-3 px-6 py-3 sm:flex-row sm:items-center sm:justify-between">

                <!-- Tabs -->
                <div class="flex gap-2">
                    <button type="button" data-tab="assign"
                        class="tab-btn rounded-xl border border-gray-300 bg-gray-900 px-4 py-2 text-xs font-semibold text-white">
                        Assign List
                    </button>

                    <button type="button" data-tab="transfer"
                        class="tab-btn rounded-xl border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-white">
                        Transfer Jobs
                    </button>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <button id="btnAssignPurchasing"
                        class="tab-action assign-action hidden rounded-xl bg-blue-600 px-5 py-2 text-xs font-semibold text-white hover:bg-blue-700">
                        Assign Purchasing
                    </button>

                    <button id="btnTransferJobs"
                        class="tab-action transfer-action hidden rounded-xl bg-amber-600 px-5 py-2 text-xs font-semibold text-white hover:bg-amber-700">
                        <i class="fas fa-exchange-alt pr-2"></i>Transfer Jobs
                    </button>
                </div>
            </div>



            {{-- TAB: Assign List --}}
            <div id="tab-assign" class="rounded-base relative overflow-x-auto p-4">
                <table id="canvassTable" class="text-body w-full text-left text-xs rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-xs">
                        <tr>
                            <th></th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                DocID</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Assign Purchasing</th>
                            <th class="w-32 px-6 py-3 font-medium">
                                Date</th>
                            <th class="w-32 px-6 py-3 font-medium">
                                Company</th>
                            <th class="w-32 px-6 py-3 font-medium">
                                Created By</th>
                            <th class="w-32 px-6 py-3 font-medium">
                                Department</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
            </div>

            {{-- TAB: Transfer Jobs --}}
            <div id="tab-transfer"class="rounded-base relative overflow-x-auto p-4">
                <table id="transferTable" class="text-body w-full text-left text-xs rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-xs">
                        <tr>
                            <th></th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                DocID</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Assign Purchasing</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Assign Purchasing New</th>
                            <th class="w-32 px-6 py-3 font-medium">
                                Date</th>
                            <th class="w-32 px-6 py-3 font-medium">
                                Company</th>
                            <th class="w-32 px-6 py-3 font-medium">
                                Created By</th>
                            <th class="w-32 px-6 py-3 font-medium">
                                Department</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
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
                return `<a href="${url}" class="inline-flex items-center rounded px-3 py-1.5 bg-blue-600 text-white hover:bg-blue-700 text-xs font-semibold">${text}</a>`;
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
            $('#docFilter').on('change', function() {
                docTypeFilter = $(this).val() || '';
                if (activeTab === 'assign') table.ajax.reload(null, true);
                else transferTable.ajax.reload(null, true);
            });

            function switchTab(tab) {
                activeTab = tab;

                // Tabs style
                $('.tab-btn')
                    .removeClass('bg-gray-900 text-white')
                    .addClass('bg-white dark:bg-gray-800 text-gray-700 dark:text-white');

                $(`.tab-btn[data-tab="${tab}"]`)
                    .addClass('bg-gray-900 text-white')
                    .removeClass('bg-white dark:bg-gray-800 text-gray-700 dark:text-white');

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
