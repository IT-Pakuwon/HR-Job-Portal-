<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'cslist.index' ? 'CS' : '';
    @endphp

    <style>
        /* Active / Selected state */
        .scope-filter.active .scope-card {
            transform: scale(1.02);
        }

        /* My CS */
        .scope-filter[data-scope="my"].active .scope-card {
            background-color: rgb(254 215 170);
            /* orange-200 */
            border-color: rgb(194 65 12);
            /* orange-700 */
            color: rgb(194 65 12);
        }

        /* On Progress */
        .scope-filter[data-scope="onprogress"].active .scope-card {
            background-color: rgb(191 219 254);
            /* blue-200 */
            border-color: rgb(29 78 216);
            /* blue-700 */
            color: rgb(29 78 216);
        }

        /* Rejected */
        .scope-filter[data-scope="rejected"].active .scope-card {
            background-color: rgb(254 202 202);
            /* red-200 */
            border-color: rgb(185 28 28);
            /* red-700 */
            color: rgb(185 28 28);
        }

        /* Completed */
        .scope-filter[data-scope="completed"].active .scope-card {
            background-color: rgb(187 247 208);
            /* green-200 */
            border-color: rgb(21 128 61);
            /* green-700 */
            color: rgb(21 128 61);
        }

        /* All CS */
        .scope-filter[data-scope="all"].active .scope-card {
            background-color: rgb(229 231 235);
            /* gray-200 */
            border-color: rgb(31 41 55);
            /* gray-700 */
            color: rgb(31 41 55);
        }
    </style>


    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid-col-1 grid gap-6 xl:grid-cols-5 xl:grid-rows-1">
            {{-- My CS --}}
            <button>
                <a href="#" class="scope-filter group block" data-scope="my">
                    <div
                        class="scope-card flex items-center gap-4 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">
                        <span class="text-xl group-hover:animate-pulse">📄</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">My CS</p>
                            <p class="text-right text-xl font-extrabold">{{ $my }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- On Progress --}}
            <button>
                <a href="#" class="scope-filter group block" data-scope="onprogress">
                    <div
                        class="scope-card flex items-center gap-4 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">
                        <span class="text-xl group-hover:animate-pulse">⏳</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">On Progress</p>
                            <p class="text-right text-xl font-extrabold">{{ $onProgress }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- Rejected --}}
            <button>
                <a href="#" class="scope-filter group block" data-scope="rejected">
                    <div
                        class="scope-card flex items-center gap-4 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">
                        <span class="text-xl group-hover:animate-pulse">⛔️</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">Reject</p>
                            <p class="text-right text-xl font-extrabold">{{ $reject }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- Completed --}}
            <button>
                <a href="#" class="scope-filter group block" data-scope="completed">
                    <div
                        class="scope-card flex items-center gap-4 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">
                        <span class="text-xl group-hover:animate-pulse">✅</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">Completed</p>
                            <p class="text-right text-xl font-extrabold">{{ $completed }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- All CS --}}
            <button>
                <a href="#" class="scope-filter group block" data-scope="all">
                    <div
                        class="scope-card flex items-center gap-4 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">
                        <span class="text-xl group-hover:animate-pulse">🧾</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">All CS</p>
                            <p class="text-right text-xl font-extrabold">{{ $all }}</p>
                        </div>
                    </div>
                </a>
            </button>
        </div>

        <div class="grid">
            <style>
                .no-border {
                    border: none !important;
                }

                .grid {
                    width: 100%;
                }

                select,
                textarea,
                input {
                    width: 100%;
                }

                table.dataTable {
                    width: 100% !important;
                }

                .dataTables_wrapper {
                    width: 100%;
                }

                @media (max-width: 600px) {
                    .dataTables_wrapper {
                        padding: 0 10px;
                    }
                }

                /* CS table styles */
                #csTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    align-items: center;
                }

                #csTable_filter label {
                    margin-right: 2px;
                }

                #csTable_filter input {
                    width: auto;
                    padding: 0.25rem 0.5rem;
                    min-width: 80px;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }

                #csTable td {
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                #csTable th,
                #csTable td {
                    padding: 10px;
                    max-width: 240px;
                }

                #csTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }

                #csTable_length select {
                    width: auto;
                    padding: 0.25rem 0.5rem;
                    min-width: 80px;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }

                #csTable_info {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                .dataTables_paginate {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                #csTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }

                #csTable tbody tr:hover {
                    background-color: #8f8f8f11;
                    opacity: 100%;
                    cursor: pointer;
                }

                #csTable th:nth-child(1),
                #csTable td:nth-child(1) {
                    width: 140px;
                    text-align: left;
                }

                #csTable th:nth-child(3),
                #csTable td:nth-child(3) {
                    width: 150px;
                    text-align: center;
                }

                #csTable th:nth-child(4),
                #csTable td:nth-child(4) {
                    width: 150px;
                    text-align: center;
                }
            </style>

            <div class="mt-6 rounded-2xl bg-white dark:bg-gray-800">
                <div
                    class="flex flex-col items-start justify-between gap-4 border-b border-gray-200 p-4 sm:flex-row sm:items-center dark:border-gray-700">
                    <h1 class="text-xl font-extrabold text-gray-700 dark:text-white">Canvass Sheet (CS)</h1>
                </div>

                <div class="overflow-x-auto p-6">
                    <table id="csTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    CS ID</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    SPPB/J/K/T</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    CS Date</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Company</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Department</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Created By</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Note</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Assign Date</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Submit Date</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Days</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>
                </div>
            </div>

            <script>
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
                    const $title = $('h1.text-xl.font-extrabold'); // selector h1 kamu
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
                        pageLength: 25,
                        lengthMenu: [10, 25, 50, 100, 250],
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
                                render: (v) => renderDays(v)
                            },
                        ],
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
                        return `<a href="${url}" class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-base leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-gray-500 hover:bg-gray-700">${text}</a>`;
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
                        return `<a href="${url}"  class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-medium text-white rounded text-center transition-colors duration-200 bg-indigo-500 hover:bg-indigo-700">${docNo}</a>`;
                    }

                    function renderDays(v) {
                        return (v == null) ? '' : String(v);
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
            </script>
        </div>
    </div>
</x-app-layout>
