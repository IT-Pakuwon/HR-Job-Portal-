<x-app-layout>
    <x-spp-list-styles />
    @php
        $currentPage = Route::currentRouteName() == 'cslist.index' ? 'CS' : '';
    @endphp

    <style>

        #csTable th.cs-compact,
        #csTable td.cs-compact {
            padding-left: 8px !important;
            padding-right: 8px !important;
            white-space: nowrap;
        }

        #csTable th.cs-compact {
            padding-right: 20px !important;
        }

        #csTable .cs-purpose {
            min-width: 240px;
            white-space: normal;
            overflow-wrap: anywhere;
            text-align: left;
        }
    </style>


    <div class="max-w-9xl mx-auto w-full p-2">
        @php
            $statusCards = [
                [['data-scope' => 'my'], 'My CS', '📄', 'all', $my, false, null, 'scope-filter'],
                [['data-scope' => 'onprogress'], 'On Progress', '⏳', 'yellow', $onProgress, false, null, 'scope-filter'],
                [['data-scope' => 'rejected'], 'Reject', '⛔', 'red', $reject, false, null, 'scope-filter'],
                [['data-scope' => 'completed'], 'Completed', '✅', 'green', $completed, false, null, 'scope-filter'],
                [['data-scope' => 'all'], 'All CS', '🧾', 'purple', $all, false, null, 'scope-filter'],
            ];
        @endphp
        <x-status-filter-cards :custom="$statusCards" />

        <div class="dt-pretty mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="Canvass Sheet (CS)" title-id="pageTitle" subtitle="Track canvass sheets and their approval progress">
                <div class="sppb-f relative w-44">
                    <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg></span>
                    <select id="filterCpny">
                        <option value="">All Company</option>
                        @foreach ($companies ?? [] as $c)
                            <option value="{{ $c }}">{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="statusFilterWrapper" class="sppb-f relative hidden w-44">
                    <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>
                    <select id="filterStatus">
                        <option value="">All Status</option>
                        <option value="H">Hold</option>
                        <option value="D">Revise</option>
                        <option value="P">On Progress</option>
                        <option value="R">Rejected</option>
                        <option value="C">Completed</option>
                        <option value="X">Canceled</option>
                    </select>
                </div>
            </x-list-page-header>

            <div class="relative overflow-hidden">
                <table id="csTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th class="px-4 py-3 text-left font-medium">
                                CS ID</th>
                            <th class="px-4 py-3 text-left font-medium">
                                SPPB/J/K/T</th>
                            <th class="px-4 py-3 text-left font-medium">
                                Assign Date</th>
                            <th class="px-4 py-3 text-left font-medium">
                                CS Date</th>
                            <th class="px-4 py-3 text-left font-medium">
                                Process Day</th>
                            <th class="px-4 py-3 text-left font-medium">
                                Company</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Department</th>
                            <th class="px-4 py-3 text-left font-medium">
                                Purchaser </th>
                            <th class="px-4 py-3 text-left font-medium">
                                Purpose</th>
                            <th class="px-4 py-3 text-left font-medium">
                                Next Approval</th>
                            <th class="px-4 py-3 text-left font-medium">
                                Day Approve</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Status
                            </th>

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
        $(document).ready(function() {
            // ====== STATE (persist) ======
            let scope = localStorage.getItem('activeCsScope') || 'onprogress';
            let cpnyId = localStorage.getItem('csFilterCpny') || '';
            let statusFilter = localStorage.getItem('csFilterStatus') || '';

            const titleMap = {
                my: 'Canvass Sheet - My CS',
                onprogress: 'Canvass Sheet - On Progress',
                rejected: 'Canvass Sheet - Rejected',
                completed: 'Canvass Sheet - Completed',
                all: 'Canvass Sheet - All CS',
            };

            const $title = $('#pageTitle');

            function updateTitle(sc) {
                $title.text(titleMap[sc] ?? 'Canvass Sheet');
            }

            function highlightActive(sc) {
                $('.scope-filter').removeClass('active');
                $(`.scope-filter[data-scope="${sc}"]`).addClass('active');
            }

            function toggleStatusFilter(sc) {
                if (sc === 'all') {
                    $('#statusFilterWrapper').removeClass('hidden');
                } else {
                    $('#statusFilterWrapper').addClass('hidden');
                    statusFilter = '';
                    $('#filterStatus').val('').trigger('change.select2');
                    localStorage.removeItem('csFilterStatus');
                }
            }

            // init UI
            updateTitle(scope);
            highlightActive(scope);
            $('#filterCpny').val(cpnyId);
            $('#filterStatus').val(statusFilter);
            $('#filterCpny, #filterStatus').select2({ width: '100%', minimumResultsForSearch: 8, dropdownCssClass: 'sppb-s2-dropdown' });
            toggleStatusFilter(scope);

            const table = $('#csTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],
                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'List_CS',
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
                        title: 'List_CS',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
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
                    },
                    {
                        targets: [3, 4],
                        width: '92px',
                        className: 'cs-compact'
                    },
                    {
                        targets: 6,
                        width: '65px',
                        className: 'cs-compact'
                    },
                    {
                        targets: [5, 11],
                        width: '48px',
                        className: 'cs-compact'
                    },
                    {
                        targets: 9,
                        width: '32%',
                        responsivePriority: 2
                    },
                    {
                        targets: '_all',
                        className: 'text-left'
                    }
                ],
                order: [
                    [1, 'desc']
                ],
                ajax: {
                    url: "{{ route('cslist.json') }}",
                    type: "GET",
                    // data: function(d) {
                    //     d.scope = scope;
                    //     d.cpny_id = cpnyId; // ✅ filter company
                    // }
                    data: function(d) {
                        d.scope = scope;
                        d.cpny_id = cpnyId;
                        d.status = scope === 'all' ? statusFilter : '';
                    }
                },
                columns: [{
                        data: null,
                        defaultContent: ''
                    },
                    {
                        data: 'csid',
                        className: 'text-left',
                        render: (_v, t, row) => renderCSBtn(_v, row)
                    },
                    {
                        data: 'sppbjktid',
                        className: 'text-left',
                        render: (v, t, row) => renderSPPBtn(v, row)
                    },
                    {
                        data: 'assigndate',
                        className: 'text-center',
                        render: (v) => fmtDate(v)
                    },
                    {
                        data: 'submitdate',
                        className: 'text-center',
                        render: (v) => fmtDate(v)
                    },
                    {
                        data: 'days',
                        className: 'text-center',
                        render: (v, t, row) => renderDays(v, row)
                    },
                    {
                        data: 'cpny_id',
                        className: 'text-center'
                    },
                    {
                        data: 'department_id',
                        className: 'text-center'
                    },
                    {
                        data: 'created_by',
                        className: 'text-left'
                    },
                    // {
                    //     data: 'csnote',
                    //     className: 'text-left',
                    //     defaultContent: '-',
                    //     render: function (data) {
                    //         if (!data) return '-';

                    //         return `
                //             <div
                //                 title="${escapeHtml(data)}"
                //                 style="
                //                     max-width:220px;
                //                     white-space:nowrap;
                //                     overflow:hidden;
                //                     text-overflow:ellipsis;
                //                     cursor:help;
                //                     text-align:left;
                //                 "
                //             >
                //                 ${escapeHtml(data)}
                //             </div>
                //         `;
                    //     }
                    // },
                    {
                        data: 'keperluan',
                        defaultContent: '-',
                        createdCell: function(td) {
                            td.style.setProperty('text-align', 'left',
                            'important'); // 💥 strongest override
                        },
                        render: function(data, type) {
                            if (!data) return '-';
                            if (type !== 'display') return data;

                            return `
                                <div
                                    class="cs-purpose"
                                    title="${escapeHtml(data)}"
                                >
                                    ${escapeHtml(data)}
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'next_approval',
                        className: 'text-left',
                        orderable: true,
                        searchable: true,
                        defaultContent: '-',
                        render: function(value, type) {
                            const text = value || '-';
                            return type === 'display' ? $('<span>').text(text).html() : text;
                        }
                    },
                    {
                        data: 'days_waiting',
                        className: 'text-center',
                        orderable: true,
                        searchable: true,
                        render: (value) => value == null ? '-' : value
                    },
                    {
                        data: 'status',
                        className: 'text-center',
                        render: (_v, _t, row) => renderStatusBadge(row)
                    },
                ],
                createdRow: function(row, data) {
                    if (data && data.is_overdue) {
                        row.style.backgroundColor = '#ffe4e6';
                        row.style.color = '#b91c1c';
                        row.style.fontWeight = '700';
                    }
                },
                searchDelay: 400,
                stateSave: true,
                responsive: true
            });

            // klik scope card
            $('.scope-filter').on('click', function(e) {
                e.preventDefault();
                scope = $(this).data('scope') || 'onprogress';
                localStorage.setItem('activeCsScope', scope);

                updateTitle(scope);
                highlightActive(scope);
                toggleStatusFilter(scope);
                table.ajax.reload(null, true);
            });

            // change company
            $('#filterCpny').on('change', function() {
                cpnyId = $(this).val() || '';
                localStorage.setItem('csFilterCpny', cpnyId);
                table.ajax.reload(null, true);
            });

            $('#filterStatus').on('change', function() {
                statusFilter = $(this).val() || '';
                localStorage.setItem('csFilterStatus', statusFilter);
                table.ajax.reload(null, true);
            });

            function escapeHtml(text) {
                return text
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }



            function fmtDate(v) {
                if (!v) return '';
                const d = new Date(v);
                return Number.isNaN(d.getTime()) ? v : d.toLocaleDateString('id-ID');
            }

            function csDocBtn(url, text) {
                return `<a href="${url}" target="_blank" title="Open" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide tabular-nums text-blue-700 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 group-hover/doc:-rotate-6"><i class="fa-regular fa-file-lines"></i></span><span>${text}</span></a>`;
            }

            function renderCSBtn(_v, row) {
                const url = `/showcs/${row.eid}`;
                const text = row.csid || row.eid;
                return csDocBtn(url, text);
            }

            const showMap = {
                PB: 'showsppbs',
                PJ: 'showsppjs',
                PK: 'showsppks',
                PT: 'showsppts'
            };

            function renderSPPBtn(_v, row) {
                const prefix = (row.sppbjkt_prefix || '').toUpperCase();
                const base = showMap[prefix];
                const docNo = row.sppbjktid || '';
                const src_eid = row.sppbjkid_eid;
                if (!prefix || !base || !src_eid) return docNo;
                const url = `/${base}/${src_eid}`;
                return csDocBtn(url, docNo);
            }

            function renderDays(v, row) {
                if (v == null) return '';
                const limit = row?.doctype_limit ?? null;
                if (row?.is_overdue) {
                    return `<span class="font-extrabold text-red-700">${v}${limit ? ` / ${limit}` : ''}</span>`;
                }
                return `${v}${limit ? ` / ${limit}` : ''}`;
            }

            function renderStatusBadge(row) {
                const label = row.status_label ?? row.status ?? '-';
                const cls = row.status_class ?? 'bg-gray-200/60 text-gray-700 border border-gray-500/40';
                return `<span class="inline-flex items-center rounded-full border px-3 py-1 text-sm font-semibold ${cls}">${label}</span>`;
            }
        });
    </script>

    {{-- <script>
        $(document).ready(function() {
            let scope = 'onprogress';

            // 1) Peta scope → judul yang ditampilkan
            const titleMap = {
                my: 'Canvass Sheet - My CS',
                onprogress: 'Canvass Sheet - On Progress',
                rejected: 'Canvass Sheet - Rejected',
                completed: 'Canvass Sheet - Completed',
                all: 'Canvass Sheet - All CS',
            };

            // 2) Helper untuk set judul
            const $title = $('#pageTitle'); // selector h1 kamu
            function updateTitle(sc) {
                const label = titleMap[sc] ?? 'Canvass Sheet';
                $title.text(label);
            }

            // 3) (Opsional) highlight kartu aktif
            function highlightActive(sc) {
                $('.scope-filter')
                    .removeClass('#')
                    .each(function() {
                        if ($(this).data('scope') === sc) {
                            $(this).addClass('#');
                        }
                    });
            }

            // panggil sekali di awal
            updateTitle(scope);
            highlightActive(scope);

            // === DataTable kamu tetap sama ===
            const table = $('#csTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],

                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'List_CS',
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
                        title: 'List_CS',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                responsive: {
                    details: {
                        type: 'column',
                        target: 0 // 👈 this is REQUIRED
                    }
                },

                columnDefs: [
    {
        targets: '_all',
        className: 'whitespace-normal break-words'
    },{
                    targets: 0,
                    width: '28px',
                    className: 'dtr-control',
                    orderable: false
                }],
                order: [
                    [2, 'desc'],
                    [0, 'desc']
                ],
                ajax: {
                    url: "{{ route('cslist.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.scope = scope;
                    }
                },
                columns: [{
                        data: null,
                        defaultContent: ''
                    }, {
                        data: 'csid',
                        className: 'text-left',
                        render: (_v, t, row) => renderCSBtn(_v, row)
                    },
                    {
                        data: 'sppbjktid',
                        className: 'text-left',
                        render: (v, t, row) => renderSPPBtn(v, row)
                    },
                    {
                        data: 'csdate',
                        className: 'text-center',
                        render: (v) => fmtDate(v)
                    },
                    {
                        data: 'cpny_id',
                        className: 'text-center'
                    },
                    {
                        data: 'department_id',
                        className: 'text-center'
                    },
                    {
                        data: 'created_by',
                        className: 'text-left'
                    },
                    {
                        data: 'csnote',
                        className: 'text-left',
                        defaultContent: '-'
                    },
                    {
                        data: 'assigndate',
                        className: 'text-center',
                        render: (v) => fmtDate(v)
                    },
                    {
                        data: 'submitdate',
                        className: 'text-center',
                        render: (v) => fmtDate(v)
                    },
                    {
                        data: 'days',
                        className: 'text-center',
                        render: (v, t, row) => renderDays(v, row)
                    },

                    {
                        data: 'status',
                        className: 'text-center',
                        render: (_v, _t, row) => renderStatusBadge(row)
                    },

                ],

                createdRow: function(row, data) {
                    if (data && data.is_overdue) {
                        row.style.backgroundColor = '#ffe4e6'; // merah muda
                        row.style.color = '#b91c1c'; // teks merah
                        row.style.fontWeight = '700';
                    }
                },


                searchDelay: 400,
                stateSave: true,
                responsive: true
            });

            // Klik kartu → ubah scope, judul, highlight, lalu reload tabel
            $('.scope-filter').on('click', function(e) {
                e.preventDefault();
                scope = $(this).data('scope') || 'my';
                updateTitle(scope);
                highlightActive(scope);
                table.ajax.reload(null, true);
            });

            // --- helper yg sudah ada di script-mu ---
            function fmtDate(v) {
                if (!v) return '';
                const d = new Date(v);
                return Number.isNaN(d.getTime()) ? v : d.toLocaleDateString('id-ID');
            }

            function renderCSBtn(_v, row) {
                const url = `/showcs/${row.eid}`;
                const text = row.csid || row.eid;
                return `<a href="${url}" class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200  bg-gray-600 hover:bg-gray-700 ">${text}</a>`;
            }
            const showMap = {
                PB: 'showsppbs',
                PJ: 'showsppjs',
                PK: 'showsppks',
                PT: 'showsppts'
            };

            function renderSPPBtn(_v, row) {
                const prefix = (row.sppbjkt_prefix || '').toUpperCase();
                const base = showMap[prefix];
                const docNo = row.sppbjktid || '';
                const src_eid = row.sppbjkid_eid;
                if (!prefix || !base || !src_eid) return docNo;
                const url = `/${base}/${src_eid}`;
                return `<a href="${url}"  class="inline-flex justify-center items-center w-[120px] px-3 py-1.5  text-sm  leading-tight font-medium text-white rounded text-center transition-colors duration-200 bg-indigo-500 hover:bg-indigo-700">${docNo}</a>`;
            }

            // function renderDays(v) {
            //     return (v == null) ? '' : String(v);
            // }
            function renderDays(v, row) {
                if (v == null) return '';
                const limit = row?.doctype_limit ?? null;

                if (row?.is_overdue) {
                    return `<span class="font-extrabold text-red-700">${v}${limit ? ` / ${limit}` : ''}</span>`;
                }
                return `${v}${limit ? ` / ${limit}` : ''}`;
            }


            function renderStatusBadge(row) {
                const label = row.status_label ?? row.status ?? '-';
                const cls = row.status_class ?? 'bg-gray-200/60 text-gray-700 border border-gray-500/40'
                return `<span class="inline-flex items-center rounded-full border px-3 py-1  text-sm  font-semibold ${cls}">${label}</span>`;
            }


        });
        // Toggle .active class and remember selected CS scope
        const csScopes = document.querySelectorAll('.scope-filter');
        const savedCsScope = localStorage.getItem('activeCsScope');

        if (savedCsScope) {
            const activeScope = document.querySelector(`.scope-filter[data-scope="${savedCsScope}"]`);
            if (activeScope) activeScope.classList.add('active');
        }

        csScopes.forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                csScopes.forEach(s => s.classList.remove('active'));
                btn.classList.add('active');
                localStorage.setItem('activeCsScope', btn.dataset.scope);
            });
        });
    </script> --}}
</x-app-layout>
