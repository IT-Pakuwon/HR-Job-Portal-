<x-app-layout>
    <x-spp-list-styles />
    <x-spp-list-scripts />

    <div class="max-w-9xl mx-auto w-full p-2">
        @php
            $statusCards = [
                [['data-scope' => 'bastjobs'], 'Bast Jobs', '📦', 'blue', $bastjobs, false, null, 'scope-filter'],
                [['data-scope' => 'onprogress'], 'On Progress', '⏳', 'yellow', $onProgress, false, null, 'scope-filter'],
                [['data-scope' => 'rejected'], 'Rejected', '❌', 'red', $rejected, false, null, 'scope-filter'],
                [['data-scope' => 'revise'], 'Revise', '🛠️', 'blue', $revise, false, null, 'scope-filter'],
                [['data-scope' => 'completed'], 'Completed', '✅', 'green', $completed, false, null, 'scope-filter'],
                [['data-scope' => 'all'], 'All', '🧾', 'all', $all, false, null, 'scope-filter'],
                ...(auth()->user()->isAdmin() ? [[['data-scope' => 'allactive'], 'BAST All', '📊', 'purple', $allActive, false, null, 'scope-filter']] : []),
            ];
        @endphp
        <x-status-filter-cards :custom="$statusCards" />

        <div
            class="dt-pretty mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="Bast" subtitle="Handover jobs and BAST documents" title-id="pageTitle">
                <div class="sppb-f relative w-56"><span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" /></svg></span><select id="filter_vendor"><option value="">All Vendor</option></select></div>
                <div class="sppb-f relative w-52"><span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" /></svg></span><select id="filter_terms"><option value="">All Terms</option></select></div>
                <div class="relative w-40">
                    <input type="date" id="filter_start" aria-label="Start date" title="Start date" class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm font-medium text-gray-700 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                </div>
                <div class="relative w-40">
                    <input type="date" id="filter_end" aria-label="End date" title="End date" class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm font-medium text-gray-700 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                </div>
                <button type="button" id="reset_filters" class="inline-flex h-10 items-center rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-gray-50 hover:shadow-md active:translate-y-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"><i class="fas fa-rotate-right mr-2 text-xs"></i>Reset</button>
            </x-list-page-header>

            <div class="relative overflow-hidden">
                <table id="bastTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead
                        class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                        <tr id="thead-row"></tr>
                    </thead>
                    <tbody>
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>



    </div>
    <script>
        const currentUser = @json(auth()->user()->username ?? '');
        const dtControlColumn = {
            data: null,
            className: 'dtr-control',
            orderable: false,
            searchable: false,
            defaultContent: ''
        };


        $(function() {
            let scope = 'bastjobs';
            const $title = $('#pageTitle');
            const $thead = $('#bastTable thead');
            let table;

            const titleMap = {
                bastjobs: 'Bast - Jobs',
                onprogress: 'Bast - On Progress',
                completed: 'Bast - Completed',
                rejected: 'Bast - Rejected',
                revise: 'Bast - Revise',
                all: 'Bast - All',
                allactive: 'Bast List',
            };

            function headerFor(sc) {
                if (sc === 'bastjobs') {
                    // Disesuaikan ke TrPOterm
                    return `
                    <th>    </th>
                                <th class="px-4 py-3 text-left font-medium">Action</th>
                                <th class="px-4 py-3 text-left font-medium">PO Nbr</th>
                                <th class="px-4 py-3 text-left font-medium">Company</th>
                                <th class="px-4 py-3 text-left font-medium">Vendor</th>
                                <th class="px-4 py-3 text-left font-medium">SPK Start</th>
                                <th class="px-4 py-3 text-left font-medium">SPK End</th>
                                <th class="px-4 py-3 text-left font-medium">Terms</th>
                                <th class="px-4 py-3 text-left font-medium">Progress %</th>
                                <th class="px-4 py-3 text-left font-medium">Payment %</th>
                                <th class="px-4 py-3 text-left font-medium">Created By</th>
                                <th class="px-4 py-3 text-left font-medium">Status</th>
                            `;
                }
                // TrBast scopes
                return `
                <th></th>
                            <th class="px-4 py-3 text-left font-medium">Bast ID</th>
                            <th class="px-4 py-3 text-left font-medium">Bast Date</th>
                            <th class="px-4 py-3 text-left font-medium">PO Nbr</th>
                            <th class="px-4 py-3 text-left font-medium">SPPB/J/K/T</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Vendor</th>
                            <th class="px-4 py-3 text-left font-medium">Terms</th>
                            <th class="px-4 py-3 text-left font-medium">Created By</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                        `;
            }

            function columnsFor(sc) {
                // 🔹 responsive control column
                if (sc === 'bastjobs') {
                    return [dtControlColumn, {
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: (_v, t, row) => renderPlusCreate(row)
                        },
                        {
                            data: 'ponbr',
                            render: (_v, _t, row) => renderPoLink(row)
                        },
                        {
                            data: 'cpny_id',
                            className: 'text-left'
                        },
                        {
                            data: 'vendorname'
                        },
                        {
                            data: 'spkstartworkingdate_fmt',
                            defaultContent: '',
                            className: 'text-left',
                            render: (_v, _t, row) => row.spkstartworkingdate_fmt ?? ''
                        },
                        {
                            data: 'spkendtworkingdate_fmt',
                            defaultContent: '',
                            className: 'text-left',
                            render: (_v, _t, row) => row.spkendtworkingdate_fmt ?? ''
                        },
                        {
                            data: 'terms_name'
                        },
                        {
                            data: 'progress_pct',
                            className: 'text-left'
                        },
                        {
                            data: 'payment_pct',
                            className: 'text-left'
                        },
                        {
                            data: 'created_by'
                        },
                        {
                            data: 'status',
                            orderable: false,
                            searchable: false,
                            className: 'text-left',
                            render: (_v, _t, row) => renderStatusBadge(row)
                        },


                    ];
                }
                // TrBast scopes
                // return [
                //     { data: 'bastid',  render: (_v,_t,row)=>renderBastLink(row) },
                //     { data: 'bastdate', render: (_v,_t,row)=>row.bastdate_fmt ?? '', className:'text-center' },
                //     { data: 'ponbr', render: (_v,_t,row)=>renderPoLink(row) },
                //     { data: 'sppbjktid', render: (_v,_t,row)=>renderSppbLink(row) },
                //     { data: 'cpny_id', className:'text-center' },
                //     { data: 'created_by' },
                // ];
                return [dtControlColumn, {
                        data: 'bastid',
                        render: (_v, _t, row) => renderBastLink(row)
                    },
                    {
                        data: 'bastdate',
                        render: (_v, _t, row) => row.bastdate_fmt ?? '',
                        className: 'text-left'
                    },
                    {
                        data: 'ponbr',
                        className: 'text-left'
                    },
                    {
                        data: 'sppbjktid',
                        className: 'text-left'
                    },
                    {
                        data: 'cpny_id',
                        className: 'text-left'
                    },
                    {
                        data: 'vendorname',
                        className: 'text-left'
                    },
                    {
                        data: 'terms_name',
                        defaultContent: '-',
                        className: 'text-left'
                    },
                    {
                        data: 'created_by'
                    },
                    {
                        data: 'status',
                        orderable: true,
                        className: 'text-left',
                        render: (_v, _t, row) => renderStatusBadge(row)
                    },

                ];
            }

            function orderFor(sc) {
                if (sc === 'bastjobs') return [
                    [1, 'desc']
                ]; // sort by PONBR
                return [
                    [1, 'desc'],
                    [0, 'desc']
                ];
            }

            function updateTitle(sc) {
                $title.text(titleMap[sc] ?? 'Bast');
            }

            function resetThead(sc) {
                const $table = $('#bastTable');

                // hapus thead lama (yang mungkin sisa clone DataTables)
                $table.find('thead').remove();

                // buat ulang thead + tr
                const theadHtml = `
                        <thead
                        class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <tr id="thead-row">${headerFor(sc)}</tr>
                            </thead>`;
                $table.prepend(theadHtml);

                // pastikan tbody ada
                if ($table.find('tbody').length === 0) {
                    $table.append(`<tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    `);
                }
            }

            function rebuild(sc) {
                if ($.fn.DataTable.isDataTable('#bastTable')) {
                    $('#bastTable').DataTable().clear().destroy();
                }
                resetThead(sc);

                table = $('#bastTable').DataTable({
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
                            text: '<i class="fa-solid fa-file-excel"></i> Excel',
                            title: 'List_BAST',
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
                            text: '<i class="fa-solid fa-file-csv"></i> CSV',
                            title: 'List_BAST',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        }
                    ],
                    order: orderFor(sc),
                    ajax: {
                        url: "{{ route('bastlist.json') }}",
                        type: "GET",
                        data: function(d) {
                            d.scope = sc;
                            d.vendor = $('#filter_vendor').val();
                            d.terms = $('#filter_terms').val();
                            d.start_date = $('#filter_start').val();
                            d.end_date = $('#filter_end').val();
                        }
                    },
                    columns: columnsFor(sc),
                    searchDelay: 400,
                    stateSave: false,
                    responsive: true
                });

                table.on('xhr', function() {
                    const json = table.ajax.json();
                    if (!json) return;

                    populateFilterOptions($('#filter_vendor'), json.vendorOptions, 'All Vendor');
                    populateFilterOptions($('#filter_terms'), json.termsOptions, 'All Terms');
                });

            }

            function initSelect2($el, placeholder) {
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }
                $el.select2({
                    placeholder: placeholder,
                    allowClear: true,
                    dropdownCssClass: 'sppb-s2-dropdown',
                    width: '100%'
                });
            }

            function populateFilterOptions($el, options, allLabel) {
                const current = $el.val();
                $el.empty().append(`<option value="">${allLabel}</option>`);

                (options || []).forEach(v => {
                    $el.append(`<option value="${v}">${v}</option>`);
                });

                if (current && options && options.includes(current)) {
                    $el.val(current);
                }

                initSelect2($el, allLabel);
            }

            initSelect2($('#filter_vendor'), 'All Vendor');
            initSelect2($('#filter_terms'), 'All Terms');

            $('#filter_vendor, #filter_terms, #filter_start, #filter_end')
                .on('change keyup', function() {
                    table.ajax.reload();
                });


            function resetFilters() {
                $('#filter_vendor').val(null).trigger('change.select2');
                $('#filter_terms').val(null).trigger('change.select2');
                $('#filter_start').val('');
                $('#filter_end').val('');

                table.ajax.reload();
            }

            $('#reset_filters').on('click', resetFilters);

            function renderPlusCreate(row) {
                // create BAST → kirim hash id PO (hasil mapping di controller)
                const url = `{{ route('bast.create') }}` + `?term=${encodeURIComponent(row.term_eid ?? '')}`;
                return `<a href="${url}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-blue-100 bg-blue-50 text-sm font-bold text-blue-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90" title="Create BAST" aria-label="Create BAST">+</a>`;
            }

            function renderPoLink(row) {
                const text = row.ponbr ?? '';
                if (row.ponbr_eid) {
                    const url = `/showpo/${encodeURIComponent(row.ponbr_eid)}`;
                    return `<a href="${url}" target="_blank" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 group-hover/doc:-rotate-6"><i class="fa-regular fa-file-lines"></i></span><span>${text}</span></a>`;
                }
                return text;
            }

            function renderSppbLink(row) {
                const text = row.sppbjktid ?? '';
                if (row.sppb_route && row.sppb_eid) {
                    const url = `/${row.sppb_route}/${encodeURIComponent(row.sppb_eid)}`;
                    return `<a href="${url}" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 group-hover/doc:-rotate-6"><i class="fa-regular fa-file-lines"></i></span><span>${text}</span></a>`;
                }
                return text;
            }

            function renderBastLink(row) {
                const label = row.bastid ?? '';
                const hash = row.bastid_eid || row.eid || row.hash || row.id;

                if (!label) return '';
                if (!hash) {
                    return `<span class="inline-flex min-w-[140px] items-center rounded-md border border-slate-200 bg-slate-50 px-3 py-1 text-[13px] font-semibold leading-6 text-slate-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-400">${label}</span>`;
                }

                const statusRaw = (row.status ?? '').toString().trim().toUpperCase();
                const creator = (row.created_by ?? '').toString();
                const isRevise = statusRaw === 'D';
                const isOwner = creator === (currentUser ?? '');

                if (isRevise && isOwner) {
                    const url = `/editbasts/${encodeURIComponent(hash)}`;
                    return `<a href="${url}" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-amber-800 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-amber-500 hover:bg-amber-500 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300" title="Edit (Revise)"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 group-hover/doc:rotate-12"><i class="fa-solid fa-pen text-[10px]"></i></span><span>${label}</span></a>`;
                }

                const url = `/showbast/${encodeURIComponent(hash)}`;
                return `<a href="${url}" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 group-hover/doc:-rotate-6"><i class="fa-regular fa-file-lines"></i></span><span>${label}</span></a>`;
            }

            function renderStatusBadge(row) {
                const label = row.status_label ?? row.status ?? 'Unknown';
                const L = String(label).toLowerCase();
                let c = 'bg-slate-50 text-slate-600 ring-slate-200 dark:bg-white/5 dark:text-slate-300 dark:ring-white/10', d = 'bg-slate-400';
                if (/pending|progress|waiting/.test(L)) { c = 'bg-yellow-50 text-yellow-700 ring-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:ring-yellow-500/30'; d = 'bg-yellow-500'; }
                else if (/complete|received|done|closed/.test(L)) { c = 'bg-green-50 text-green-700 ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30'; d = 'bg-green-500'; }
                else if (/reject|cancel/.test(L)) { c = 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/30'; d = 'bg-rose-500'; }
                else if (/revise|partial/.test(L)) { c = 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/30'; d = 'bg-blue-500'; }
                return `<span class="inline-flex min-w-[116px] items-center justify-center gap-2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset ${c}"><span class="h-1.5 w-1.5 rounded-full ${d}"></span>${label}</span>`;
            }


            // init
            updateTitle(scope);
            rebuild(scope);

            // ganti scope
            $('.scope-filter').on('click', function(e) {
                e.preventDefault();
                scope = $(this).data('scope') || 'bastjobs';
                updateTitle(scope);
                rebuild(scope);

                // active state + save
                $('.scope-filter').removeClass('active');
                $(this).addClass('active');
                localStorage.setItem('activeBastScope', scope);
            });

            // restore scope terakhir
            const savedBastScope = localStorage.getItem('activeBastScope');
            if (savedBastScope) {
                scope = savedBastScope;
                updateTitle(scope);
                rebuild(scope);
                $('.scope-filter').removeClass('active');
                $(`.scope-filter[data-scope="${scope}"]`).addClass('active');
            } else {
                $(`.scope-filter[data-scope="bastjobs"]`).addClass('active');
            }
        });
    </script>
</x-app-layout>
