<x-app-layout>
    <x-spp-list-styles />
    <x-spp-list-scripts />
    <div class="max-w-9xl mx-auto w-full p-2">
        @php
            $statusCards = [
                [['data-scope' => 'calrjobs'], 'Calr Jobs', '📦', 'blue', $calrjobs, false, null, 'scope-filter'],
                [['data-scope' => 'onprogress'], 'On Progress', '⏳', 'yellow', $onProgress, false, null, 'scope-filter'],
                [['data-scope' => 'rejected'], 'Rejected', '❌', 'red', $rejected, false, null, 'scope-filter'],
                [['data-scope' => 'revise'], 'Revise', '🛠️', 'blue', $revise, false, null, 'scope-filter'],
                [['data-scope' => 'completed'], 'Completed', '✅', 'green', $completed, false, null, 'scope-filter'],
                [['data-scope' => 'all'], 'All', '🧾', 'all', $all, false, null, 'scope-filter'],
            ];
        @endphp
        <x-status-filter-cards :custom="$statusCards" />


        <div
            class="dt-pretty mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="Calr" subtitle="Calr jobs, progress and completed documents" title-id="pageTitle"></x-list-page-header>

            <div class="relative overflow-hidden">
                <table id="calrTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
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
            let scope = 'calrjobs';
            const $title = $('#pageTitle');
            const $thead = $('#calrTable thead');
            let table;

            const titleMap = {
                calrjobs: 'Calr - Jobs',
                onprogress: 'Calr - On Progress',
                completed: 'Calr - Completed',
                rejected: 'Calr - Rejected',
                revise: 'Calr - Revise',
                all: 'Calr - All',
            };

            function headerFor(sc) {
                if (sc === 'calrjobs') {
                    // Jobs dari TrRfca + TrRfcaStep
                    return `
                    <th></th>
                                <th class="px-4 py-3 text-left font-medium">Action</th>
                                <th class="px-4 py-3 text-left font-medium">RFCA ID</th>
                                <th class="px-4 py-3 text-left font-medium">PO Nbr</th>
                                <th class="px-4 py-3 text-left font-medium">Company</th>
                                <th class="px-4 py-3 text-left font-medium">Vendor</th>
                                <th class="px-4 py-3 text-left font-medium">Keperluan</th>
                                <th class="px-4 py-3 text-left font-medium">RFCA Step</th>
                                <th class="px-4 py-3 text-left font-medium">RFCA Type</th>
                                <th class="px-4 py-3 text-left font-medium">Created By</th>
                            `;
                }
                // TrCalr scopes
                return `
                <th></th>
                            <th class="px-4 py-3 text-left font-medium">Calr ID</th>
                            <th class="px-4 py-3 text-left font-medium">Calr Date</th>
                            <th class="px-4 py-3 text-left font-medium">RFCA ID</th>
                            <th class="px-4 py-3 text-left font-medium">CS ID</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Vendor</th>
                            <th class="px-4 py-3 text-left font-medium">Keperluan</th>
                            <th class="px-4 py-3 text-left font-medium">Created By</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                        `;
            }


            function columnsFor(sc) {
                if (sc === 'calrjobs') {
                    return [
                        dtControlColumn, {
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: (_v, t, row) => renderPlusCreate(row)
                        },
                        {
                            data: 'rfcaid',
                            render: (_v, _t, row) => renderRfcaLink(row),
                            className: 'text-left'
                        },
                        {
                            data: 'ponbr',
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
                            data: 'keperluan',
                            defaultContent: '-',
                            render: $.fn.dataTable.render.text(),
                            className: 'text-left whitespace-normal'
                        },
                        {
                            data: 'rfca_step_descr',
                            className: 'text-left'
                        },
                        {
                            data: 'rfca_type',
                            className: 'text-left'
                        },
                        {
                            data: 'created_by'
                        },
                    ];
                }
                // TrCalr scopes
                return [dtControlColumn, {
                        data: 'calrid',
                        render: (_v, _t, row) => renderCalrLink(row)
                    },
                    {
                        data: 'calrdate',
                        render: (_v, _t, row) => row.calrdate_fmt ?? '',
                        className: 'text-left'
                    },
                    {
                        data: 'rfcaid',
                        className: 'text-left'
                    },
                    {
                        data: 'csid',
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
                        data: 'keperluan',
                        defaultContent: '-',
                        render: $.fn.dataTable.render.text(),
                        className: 'text-left whitespace-normal'
                    },
                    {
                        data: 'created_by'
                    },
                    {
                        data: 'status',
                        className: 'text-left',
                        render: function(data) {
                            const map = {
                                'D': {
                                    t: 'Revise',
                                    c: 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30'
                                },
                                'P': {
                                    t: 'On Progress',
                                    c: 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:ring-yellow-500/30'
                                },
                                'C': {
                                    t: 'Completed',
                                    c: 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30'
                                },
                                'X': {
                                    t: 'Cancel',
                                    c: 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30'
                                },
                                'R': {
                                    t: 'Rejected',
                                    c: 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30'
                                },
                            };
                            const it = map[data] || {
                                t: data || '-',
                                c: 'bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-500/30'
                            };
                            return `<span class="inline-flex min-w-[116px] items-center justify-center whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ${it.c}">${it.t}</span>`;
                        }
                    },
                ];
            }


            function orderFor(sc) {
                if (sc === 'calrjobs') return [
                    [2, 'desc']
                ]; // RFCA ID
                return [
                    [2, 'desc'],
                    [1, 'desc']
                ];
            }

            function updateTitle(sc) {
                $title.text(titleMap[sc] ?? 'Calr');
            }

            function resetThead(sc) {
                const $table = $('#calrTable');

                // hapus thead lama (yang mungkin sisa clone DataTables)
                $table.find('thead').remove();

                // buat ulang thead + tr
                const theadHtml = `
                        <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b  text-sm ">
                            <tr id="thead-row">${headerFor(sc)}</tr>
                            </thead>`;
                $table.prepend(theadHtml);

                // pastikan tbody ada
                if ($table.find('tbody').length === 0) {
                    $table.append(
                        ` <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody> `
                    );
                }
            }

            function rebuild(sc) {
                if ($.fn.DataTable.isDataTable('#calrTable')) {
                    $('#calrTable').DataTable().clear().destroy();
                }
                resetThead(sc);

                table = $('#calrTable').DataTable({
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
                            title: 'List_CALR',
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
                            title: 'List_CALR',
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
                        url: "{{ route('calrlist.json') }}",
                        type: "GET",
                        data: function(d) {
                            d.scope = sc;
                        }
                    },
                    columns: columnsFor(sc),
                    searchDelay: 400,
                    stateSave: false,
                });
            }

            function renderPlusCreate(row) {
                // create CALR → kirim hash id RFCA (rfca_eid dari controller)
                const url = `{{ route('calr.create') }}` + `?rfca=${encodeURIComponent(row.rfca_eid ?? '')}`;
                return `
                            <a href="${url}"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-blue-100 bg-blue-50 text-sm font-bold text-blue-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90">
                                <i class="fas fa-plus"></i>
                            </a>`;
            }

            function renderRfcaLink(row) {
                const label = row.rfcaid ?? '';
                const hash = row.rfca_eid || row.eid || row.hash || row.id;

                if (!label) return '';
                if (!hash) {
                    return `<span class="inline-flex items-center px-3 py-1.5  text-sm  font-semibold rounded bg-gray-400 text-white">${label}</span>`;
                }

                const url = `/showrfca/${encodeURIComponent(hash)}`;
                return `
                            <a href="${url}" target="_blank"
                            class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:-rotate-6 group-hover/doc:scale-125 group-hover/doc:opacity-100"><i class="fa-regular fa-file-lines"></i></span><span>${label}</span></a>`;
            }


            function renderPoLink(row) {
                const text = row.ponbr ?? '';
                if (row.ponbr_eid) {
                    const url = `/showpo/${encodeURIComponent(row.ponbr_eid)}`;
                    return `<a href="${url}" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:-rotate-6 group-hover/doc:scale-125 group-hover/doc:opacity-100"><i class="fa-regular fa-file-lines"></i></span><span>${text}</span></a>`;
                }
                return text;
            }

            function renderSppbLink(row) {
                const text = row.sppbjktid ?? '';
                if (row.sppb_route && row.sppb_eid) {
                    const url = `/${row.sppb_route}/${encodeURIComponent(row.sppb_eid)}`;
                    return `<a href="${url}" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:-rotate-6 group-hover/doc:scale-125 group-hover/doc:opacity-100"><i class="fa-regular fa-file-lines"></i></span><span>${text}</span></a>`;
                }
                return text;
            }

            function renderCalrLink(row) {
                const label = row.calrid ?? '';
                const hash = row.calrid_eid || row.eid || row.hash || row.id;

                if (!label) return '';
                if (!hash) {
                    return `<span class="inline-flex items-center px-3 py-1.5  text-sm  font-semibold rounded bg-gray-400 text-white">${label}</span>`;
                }

                const statusRaw = (row.status ?? '').toString().trim().toUpperCase();
                const creator = (row.created_by ?? '').toString();
                const isRevise = statusRaw === 'D';
                const isOwner = creator === (currentUser ?? '');

                if (isRevise && isOwner) {
                    const url = `/editcalrs/${encodeURIComponent(hash)}`;
                    return `<a href="${url}" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-amber-800 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-amber-500 hover:bg-amber-500 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300" title="Edit (Revise)"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:-rotate-6 group-hover/doc:scale-125 group-hover/doc:opacity-100"><i class="fa-solid fa-pen text-[10px]"></i></span><span>${label}</span></a>`;
                }

                const url = `/showcalr/${encodeURIComponent(hash)}`;
                return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:-rotate-6 group-hover/doc:scale-125 group-hover/doc:opacity-100"><i class="fa-regular fa-file-lines"></i></span><span>${label}</span></a>`;
            }

            // init
            updateTitle(scope);
            rebuild(scope);

            // ganti scope
            $('.scope-filter').on('click', function(e) {
                e.preventDefault();
                scope = $(this).data('scope') || 'calrjobs';
                updateTitle(scope);
                rebuild(scope);

                // active state + save
                $('.scope-filter').removeClass('active');
                $(this).addClass('active');
                localStorage.setItem('activeCalrScope', scope);
            });

            // restore scope terakhir
            const savedCalrScope = localStorage.getItem('activeCalrScope');
            if (savedCalrScope) {
                scope = savedCalrScope;
                updateTitle(scope);
                rebuild(scope);
                $('.scope-filter').removeClass('active');
                $(`.scope-filter[data-scope="${scope}"]`).addClass('active');
            } else {
                $(`.scope-filter[data-scope="calrjobs"]`).addClass('active');
            }
        });
    </script>
</x-app-layout>
