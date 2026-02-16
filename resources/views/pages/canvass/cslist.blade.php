<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'cslist.index' ? 'CS' : '';
    @endphp

    <style>
    .scope-filter.active .scope-card{
        outline: 2px solid rgba(99,102,241,.6);
        box-shadow: 0 8px 18px rgba(0,0,0,.08);
        transform: translateY(-2px);
    }
    </style>


    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid-col-1 grid gap-6 xl:grid-cols-5 xl:grid-rows-1">
            {{-- My CS --}}
            <button>
                <a href="#" class="scope-filter group block" data-scope="my">
                    <div
                        class="scope-card flex items-center gap-4 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">
                        <span class="text-base group-hover:animate-pulse">📄</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-sm font-medium">My CS</p>
                            <p class="text-right text-base font-extrabold">{{ $my }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- On Progress --}}
            <button>
                <a href="#" class="scope-filter group block" data-scope="onprogress">
                    <div
                        class="scope-card flex items-center gap-4 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">
                        <span class="text-base group-hover:animate-pulse">⏳</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-sm font-medium">On Progress</p>
                            <p class="text-right text-base font-extrabold">{{ $onProgress }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- Rejected --}}
            <button>
                <a href="#" class="scope-filter group block" data-scope="rejected">
                    <div
                        class="scope-card flex items-center gap-4 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">
                        <span class="text-base group-hover:animate-pulse">⛔️</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-sm font-medium">Reject</p>
                            <p class="text-right text-base font-extrabold">{{ $reject }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- Completed --}}
            <button>
                <a href="#" class="scope-filter group block" data-scope="completed">
                    <div
                        class="scope-card flex items-center gap-4 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">
                        <span class="text-base group-hover:animate-pulse">✅</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-sm font-medium">Completed</p>
                            <p class="text-right text-base font-extrabold">{{ $completed }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- All CS --}}
            <button>
                <a href="#" class="scope-filter group block" data-scope="all">
                    <div
                        class="scope-card flex items-center gap-4 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">
                        <span class="text-base group-hover:animate-pulse">🧾</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-sm font-medium">All CS</p>
                            <p class="text-right text-base font-extrabold">{{ $all }}</p>
                        </div>
                    </div>
                </a>
            </button>
        </div>

        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Canvass Sheet (CS)</h1>
            </div>
            <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12">
                <div class="lg:col-span-3">
                    <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Company</label>
                    <select id="filterCpny"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">All Company</option>
                        @foreach(($companies ?? []) as $c)
                            <option value="{{ $c }}">{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
            </div>


            <div class="rounded-base relative overflow-x-auto">
                <table id="csTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                CS ID</th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                SPPB/J/K/T</th>
                            <th class="w-32 px-6 py-2 font-medium">
                                CS Date</th>
                            <th class="w-32 px-6 py-2 font-medium">
                                Company</th>
                            <th class="w-32 px-6 py-2 font-medium">
                                Department</th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Created By</th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Note</th>
                            <th class="w-32 px-6 py-2 font-medium">
                                Assign Date</th>
                            <th class="w-32 px-6 py-2 font-medium">
                                Submit Date</th>
                            <th class="w-32 px-6 py-2 font-medium">
                                Days</th>
                            <th class="w-32 px-6 py-2 font-medium">
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
    let scope  = localStorage.getItem('activeCsScope') || 'onprogress';
    let cpnyId = localStorage.getItem('csFilterCpny') || '';

    const titleMap = {
        my: 'Canvass Sheet - My CS',
        onprogress: 'Canvass Sheet - On Progress',
        rejected: 'Canvass Sheet - Rejected',
        completed: 'Canvass Sheet - Completed',
        all: 'Canvass Sheet - All CS',
    };

    const $title = $('h1.text-base.font-extrabold');

    function updateTitle(sc) {
        $title.text(titleMap[sc] ?? 'Canvass Sheet');
    }

    function highlightActive(sc) {
        $('.scope-filter').removeClass('active');
        $(`.scope-filter[data-scope="${sc}"]`).addClass('active');
    }

    // init UI
    updateTitle(scope);
    highlightActive(scope);
    $('#filterCpny').val(cpnyId);

    const table = $('#csTable').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, 250, -1], [10, 25, 50, 100, 250, 'All']],
        dom: '<"dt-toolbar"l B f>rtip',
        buttons: [
            { extend:'excelHtml5', text:'↓ Excel', title:'List_CS', className:'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
              exportOptions:{ columns:':visible', modifier:{ page:'current' } } },
            { extend:'csvHtml5', text:'↓ CSV', title:'List_CS', className:'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
              exportOptions:{ columns:':visible', modifier:{ page:'current' } } }
        ],
        responsive: { details: { type: 'column', target: 0 } },
        columnDefs: [{ targets: 0, width: '28px', className: 'dtr-control', orderable: false }],
        order: [[2, 'desc'], [0, 'desc']],
        ajax: {
            url: "{{ route('cslist.json') }}",
            type: "GET",
            data: function(d) {
                d.scope   = scope;
                d.cpny_id = cpnyId; // ✅ filter company
            }
        },
        columns: [
            { data: null, defaultContent: '' },
            { data: 'csid', className: 'text-left', render: (_v, t, row) => renderCSBtn(_v, row) },
            { data: 'sppbjktid', className: 'text-left', render: (v, t, row) => renderSPPBtn(v, row) },
            { data: 'csdate', className: 'text-center', render: (v) => fmtDate(v) },
            { data: 'cpny_id', className: 'text-center' },
            { data: 'department_id', className: 'text-center' },
            { data: 'created_by', className: 'text-left' },
            { data: 'csnote', className: 'text-left', defaultContent: '-' },
            { data: 'assigndate', className: 'text-center', render: (v) => fmtDate(v) },
            { data: 'submitdate', className: 'text-center', render: (v) => fmtDate(v) },
            { data: 'days', className: 'text-center', render: (v, t, row) => renderDays(v, row) },
            { data: 'status', className: 'text-center', render: (_v, _t, row) => renderStatusBadge(row) },
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
        table.ajax.reload(null, true);
    });

    // change company
    $('#filterCpny').on('change', function() {
        cpnyId = $(this).val() || '';
        localStorage.setItem('csFilterCpny', cpnyId);
        table.ajax.reload(null, true);
    });

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

    const showMap = { PB:'showsppbs', PJ:'showsppjs', PK:'showsppks', PT:'showsppts' };

    function renderSPPBtn(_v, row) {
        const prefix = (row.sppbjkt_prefix || '').toUpperCase();
        const base = showMap[prefix];
        const docNo = row.sppbjktid || '';
        const src_eid = row.sppbjkid_eid;
        if (!prefix || !base || !src_eid) return docNo;
        const url = `/${base}/${src_eid}`;
        return `<a href="${url}" class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-medium text-white rounded text-center transition-colors duration-200 bg-indigo-500 hover:bg-indigo-700">${docNo}</a>`;
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
            const $title = $('h1.text-base.font-extrabold'); // selector h1 kamu
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

                columnDefs: [{
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
