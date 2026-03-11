<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-7">

            {{-- 1. Issue New Jobs (TrSPB) --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="issuejobsnew">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🆕</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium leading-tight">
                                SPB Open
                            </p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $issuejobsnew }}</p>
                    </div>
                </a>
            </button>

            {{-- 2. Issue Jobs (TrSPB) --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="issuejobs">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📦</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium leading-tight">
                                Issue Partial
                            </p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $issuejobs }}</p>
                    </div>
                </a>
            </button>

            {{-- 3. SPPB Jobs (TrSPB) --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="onprogress">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📑</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium leading-tight">
                                SPB To SPPB
                            </p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $sppbjobs }}</p>
                    </div>
                </a>
            </button>

            {{-- 4. Issue On Progress (TrIssue) --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="issueprogress">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⏳</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium leading-tight">
                                Issue On Progress
                            </p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $issueprogress }}</p>
                    </div>
                </a>
            </button>

            {{-- 5. SPPB On Progress (TrIssue) --}}
            {{-- <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="sppbprogress">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-yellow-700 bg-yellow-200/20 p-3 text-yellow-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-yellow-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📌</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium leading-tight">
                                SPPB On Progress
                            </p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $sppbprogress }}</p>
                    </div>
                </a>
            </button> --}}

            {{-- 6. SPB In Progress --}}
            {{-- <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="spbprogress">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-indigo-700 bg-indigo-200/20 p-3 text-indigo-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-indigo-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📦</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium leading-tight">
                                SPB On Progress
                            </p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $spbprogress }}</p>
                    </div>
                </a>
            </button> --}}

            {{-- 7. SPB All (Completed + On Progress) --}}
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="spball">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-emerald-700 bg-emerald-200/20 p-3 text-emerald-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-emerald-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📊</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium leading-tight">
                                SPB Summary
                            </p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $spball }}</p>
                    </div>
                </a>
            </button>

            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="woflow">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-sky-700 bg-sky-200/20 p-3 text-sky-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-sky-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🛠️</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium leading-tight">
                                SPB by WO
                            </p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $woflow }}</p>
                    </div>
                </a>
            </button>
            <button type="button" class="text-left">
                <a href="#" class="scope-filter group block h-full" data-scope="spbflow">
                    <div
                        class="scope-card flex items-center gap-3 rounded-lg border border-indigo-700 bg-indigo-200/20 p-3 text-indigo-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-indigo-100 hover:shadow-lg active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🔁</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium leading-tight">
                                SPB → SPPB
                            </p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $spbflow }}</p>
                    </div>
                </a>
            </button>
        </div>
        <div class="mt-6 rounded-xl bg-white dark:bg-gray-800">
            <div class="flex items-center justify-between gap-4 border-b border-gray-200 p-4 dark:border-gray-700">

                <h1 class="whitespace-nowrap text-base font-extrabold text-gray-700 dark:text-white">
                    Issue
                </h1>

                <div class="flex items-center gap-2 whitespace-nowrap">

                    <input type="date" id="dateFrom"
                        class="h-9 rounded border px-2 text-sm dark:bg-gray-700 dark:text-white">

                    <span class="text-sm text-gray-500">to</span>

                    <input type="date" id="dateTo"
                        class="h-9 rounded border px-2 text-sm dark:bg-gray-700 dark:text-white">

                    <button id="filterDate" class="h-9 rounded bg-blue-600 px-3 text-sm text-white hover:bg-blue-700">
                        Filter
                    </button>

                    <button id="resetDate" class="h-9 rounded bg-gray-500 px-3 text-sm text-white hover:bg-gray-600">
                        Reset
                    </button>

                </div>
            </div>

            <div class="overflow-x-auto p-4">
                <table id="issueTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr id="thead-row"></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                </table>
            </div>
        </div>
    </div>


    <script>
        const currentUser = @json(auth()->user()->username ?? '');

        $(function() {
            let scope = 'issuejobsnew';
            const $title = $('h1.text-base.font-extrabold');
            const $thead = $('#issueTable thead');
            const dtControlColumn = {
                data: null,
                width: '28px',
                className: 'dtr-control',
                orderable: false,
                searchable: false,
                defaultContent: ''
            };
            const titleMap = {
                issuejobsnew: 'Issue - New Jobs',
                issuejobs: 'Issue - Jobs',
                onprogress: 'SPPB - Jobs',
                issueprogress: 'Issue - On Progress',
                sppbprogress: 'SPPB - On Progress',
                spbprogress: 'SPB - On Progress',
                spball: 'SPB - All',
                woflow: 'WO → SPB / SPPB',
                spbflow: 'SPB → SPPB',
            };

            const spbScopes = ['issuejobsnew', 'issuejobs', 'onprogress', 'spbprogress', 'spball', 'woflow',
                'spbflow'
            ];
            const issueScopes = ['issueprogress'];
            const sppbScopes = ['sppbprogress'];
            const allowedScopes = [...spbScopes, ...issueScopes, ...sppbScopes];

            function scopeType(sc) {
                if (spbScopes.includes(sc)) return 'spb';
                if (issueScopes.includes(sc)) return 'issue';
                if (sppbScopes.includes(sc)) return 'sppb';
                return 'spb';
            }

            function headerFor(sc) {

                const type = scopeType(sc);

                // =========================
                // SPB HEADER
                // =========================
                if (type === 'spb') {

                    const isSpbAll = (sc === 'spball');
                    const isWoFlow = (sc === 'woflow');
                    const isSpbFlow = (sc === 'spbflow'); // ⭐ NEW
                    const hideAction = (sc === 'spbprogress' || isSpbAll || isWoFlow || isSpbFlow);
                    const isSppbJobs = (sc === 'onprogress');
                    const hideStatus = (sc === 'spbprogress');

                    return `
        <th></th>

        ${!hideAction ? `
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
                        Action
                        </th>` : ``}

        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
        SPB ID
        </th>

        ${isWoFlow ? `
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
                        WO ID
                        </th>` : ``}

        ${isSpbFlow ? `
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
                        SPPB ID
                        </th>` : ``}

        <th class="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider">
        SPB Date
        </th>

        <th class="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider">
        Company
        </th>

        <th class="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider">
        Department
        </th>

        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
        Keperluan
        </th>

        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
        Created By
        </th>

        ${isSpbAll ? `
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
                        Status SPB
                        </th>
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
                        Status Issue
                        </th>
                        ` : !hideStatus ? `
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
                        ${isSppbJobs ? 'Status SPPB' : 'Issue Status'}
                        </th>` : ``}
        `;
                }

                // =========================
                // ISSUE HEADER
                // =========================
                if (type === 'issue') {

                    return `
        <th></th>

        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
        Issue ID
        </th>

        <th class="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider">
        Issue Date
        </th>

        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
        Issue Type
        </th>

        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
        SPB ID
        </th>

        <th class="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider">
        Company
        </th>

        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
        Created By
        </th>

        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
        Status
        </th>
        `;
                }

                // =========================
                // SPPB HEADER
                // =========================
                return `
        <th></th>

        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
        SPPB ID
        </th>

        <th class="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider">
        SPPB Date
        </th>

        <th class="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider">
        Company
        </th>

        <th class="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider">
        Department
        </th>

        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
        Request Type
        </th>

        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
        Description
        </th>

        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">
        Status
        </th>
        `;
            }

            function renderSpbLink(row) {

                const label = row.spbid ?? '';
                const hash = row.spb_eid || row.spb_hash || row.hash || row.id;

                if (!label || !hash) return label ?? '';

                const url = `/showspbs/${encodeURIComponent(hash)}`;

                return `
    <a href="${url}"
       target="_blank"
       class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-semibold rounded bg-gray-600 text-white hover:bg-gray-700">
       ${label}
    </a>
    `;
            }

            function renderSppbLink(row) {
                const label = row.sppbid ?? '';
                const hash = row.eid || row.sppb_hash || row.hash || row.id;
                if (!label) return '';
                const url =
                    `/showsppbs/${encodeURIComponent(hash ?? '')}`; // sesuaikan dengan route detail SPPB-mu
                return `<a href="${url}" target="_blank" class="inline-flex items-center justify-center px-3 py-1.5  text-sm  font-semibold rounded bg-gray-600 text-white hover:bg-gray-700">${label}</a>`;
            }

            function renderIssueLinkCell(_value, _type, row) {
                const label = row.issueid ?? '';
                const hash = row.eid || row.issue_eid || row.issue_hash || row.hash || row.id;

                if (!label) return '';
                if (!hash) {
                    return `<span class="inline-flex items-center px-3 py-1.5  text-sm  font-semibold rounded bg-gray-400 text-white">${label}</span>`;
                }

                const statusRaw = (row.status ?? row.xstatus ?? '').toString().trim().toUpperCase();
                const creator = (row.created_by ?? row.createdby ?? '').toString();

                const isRevise = statusRaw === 'D';
                const isOwner = creator === (currentUser ?? '');
                if (isRevise && isOwner) {
                    const url = `/editissues/${encodeURIComponent(hash)}`;
                    return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5  text-sm  font-semibold rounded bg-amber-600 text-white hover:bg-amber-700">${label}</a>`;
                }
                const url = `/showissue/${encodeURIComponent(hash)}`;
                return `<a href="${url}" class="inline-flex items-center justify-center px-3 py-1.5  text-sm  font-semibold rounded bg-gray-600 text-white hover:bg-gray-700">${label}</a>`;
            }

            // function renderPlusCreate(row) {
            //     const url = `{{ route('issue.create') }}` + `?spbid=${encodeURIComponent(row.spb_eid ?? '')}`;
            //     return `<a href="${url}" class="inline-flex justify-center items-center px-3 py-1.5  text-sm  font-medium text-white rounded bg-blue-600 hover:bg-blue-700">
        //         <i class="fas fa-plus"></i>
        //     </a>`;
            // }
            function renderIssueCreate(row) {
                const url = `{{ route('issue.create') }}` + `?spbid=${encodeURIComponent(row.spb_eid ?? '')}`;
                return `<a href="${url}" class="inline-flex justify-center items-center px-3 py-1.5  text-sm  font-medium text-white rounded bg-blue-600 hover:bg-blue-700">
                                                    <i class="fas fa-plus"></i>
                                                </a>`;
            }

            function renderSppbCreate(row) {
                const url = `{{ route('sppb.create') }}` + `?spbid=${encodeURIComponent(row.spb_eid ?? '')}`;
                return `<a href="${url}" class="inline-flex justify-center items-center px-3 py-1.5  text-sm  font-medium text-white rounded bg-amber-600 hover:bg-amber-700">
                                                    <i class="fas fa-plus"></i>
                                                </a>`;
            }


            function columnsFor(sc) {

                const type = scopeType(sc);

                if (type === 'spb') {

                    const isSpbAll = (sc === 'spball');
                    const isWoFlow = (sc === 'woflow');
                    const isSpbFlow = (sc === 'spbflow'); // ⭐ NEW
                    const isSppbJobs = (sc === 'onprogress');

                    const hideAction = (sc === 'spbprogress' || isSpbAll || isWoFlow || isSpbFlow);
                    const hideStatus = (sc === 'spbprogress');

                    const cols = [dtControlColumn];

                    // =========================
                    // ACTION
                    // =========================
                    if (!hideAction) {
                        cols.push({
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: (_v, _t, row) => {
                                if (isSppbJobs) return renderSppbCreate(row);
                                return renderIssueCreate(row);
                            }
                        });
                    }

                    // =========================
                    // SPB ID
                    // =========================
                    cols.push({
                        data: 'spbid',
                        render: (_v, _t, row) => renderSpbLink(row)
                    });

                    // =========================
                    // WO FLOW
                    // =========================
                    if (isWoFlow) {
                        cols.push({
                            data: 'wo_hash',
                            render: function(data, type, row) {

                                if (!data) return '';

                                return `<a href="/showwos/${encodeURIComponent(data)}"
                        target="_blank"
                        class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-semibold rounded bg-sky-600 text-white hover:bg-sky-700">
                        ${row.woid}
                    </a>`;
                            }
                        });
                    }

                    // =========================
                    // ⭐ SPB FLOW (SPB → SPPB)
                    // =========================
                    if (isSpbFlow) {
                        cols.push({
                            data: 'sppbid',
                            render: function(data, type, row) {

                                if (!data) return '-';

                                const hash = row.sppb_hash || row.sppb_eid || row.sppb_id;

                                return `<a href="/showsppbs/${encodeURIComponent(hash)}"
                        target="_blank"
                        class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-semibold rounded bg-indigo-600 text-white hover:bg-indigo-700">
                        ${data}
                    </a>`;
                            }
                        });
                    }

                    // =========================
                    // BASIC COLUMNS
                    // =========================
                    cols.push({
                        data: 'spbdate',
                        className: 'text-center'
                    }, {
                        data: 'cpny_id',
                        className: 'text-center'
                    }, {
                        data: 'department_name',
                        className: 'text-center'
                    }, {
                        data: 'keperluan'
                    }, {
                        data: 'created_by'
                    });

                    // =========================
                    // STATUS
                    // =========================
                    if (!hideStatus) {

                        if (sc === 'spball') {

                            // STATUS SPB
                            cols.push({
                                data: 'status',
                                render: function(data) {

                                    const map = {
                                        C: {
                                            t: 'Completed',
                                            c: 'bg-green-200/60 text-green-800'
                                        },
                                        P: {
                                            t: 'On Progress',
                                            c: 'bg-orange-200/60 text-orange-800'
                                        },
                                        D: {
                                            t: 'Revise',
                                            c: 'bg-gray-200/60 text-gray-700'
                                        },
                                        X: {
                                            t: 'Cancel',
                                            c: 'bg-red-200/60 text-red-800'
                                        },
                                        R: {
                                            t: 'Rejected',
                                            c: 'bg-red-200/60 text-red-800'
                                        }
                                    };

                                    const it = map[data] ?? {
                                        t: data ?? '-',
                                        c: 'bg-gray-200/60 text-gray-700'
                                    };

                                    return `<span class="${it.c} font-semibold px-3 py-1.5 text-sm rounded">${it.t}</span>`;
                                }
                            });

                            // STATUS ISSUE
                            cols.push({
                                data: 'status_issue',
                                render: function(data) {

                                    const map = {
                                        Open: {
                                            t: 'Open',
                                            c: 'bg-gray-200/60 text-gray-700'
                                        },
                                        Partial: {
                                            t: 'Partial',
                                            c: 'bg-amber-200/60 text-amber-800'
                                        },
                                        Completed: {
                                            t: 'Completed',
                                            c: 'bg-green-200/60 text-green-800'
                                        },
                                        Full: {
                                            t: 'Full',
                                            c: 'bg-green-200/60 text-green-800'
                                        }
                                    };

                                    const it = map[data] ?? {
                                        t: data ?? '-',
                                        c: 'bg-gray-200/60 text-gray-700'
                                    };

                                    return `<span class="${it.c} font-semibold px-3 py-1.5 text-sm rounded">${it.t}</span>`;
                                }
                            });

                        } else {

                            cols.push({
                                data: null,
                                render: (_v, _t, row) => {

                                    const map = {
                                        C: {
                                            t: 'Completed',
                                            c: 'bg-green-200/60 text-green-800'
                                        },
                                        P: {
                                            t: 'On Progress',
                                            c: 'bg-orange-200/60 text-orange-800'
                                        },
                                        D: {
                                            t: 'Revise',
                                            c: 'bg-gray-200/60 text-gray-700'
                                        },
                                        X: {
                                            t: 'Cancel',
                                            c: 'bg-red-200/60 text-red-800'
                                        },
                                        R: {
                                            t: 'Rejected',
                                            c: 'bg-red-200/60 text-red-800'
                                        }
                                    };

                                    let statusValue = '-';

                                    if (sc === 'woflow') {

                                        if (row.sppbid) {
                                            statusValue = row.status_sppb;
                                        } else {
                                            statusValue = row.status_issue;
                                        }

                                    } else if (isSppbJobs) {
                                        statusValue = row.status_sppb;
                                    } else {
                                        statusValue = row.status_issue;
                                    }

                                    const it = map[statusValue] ?? {
                                        t: statusValue ?? '-',
                                        c: 'bg-gray-200/60 text-gray-700'
                                    };

                                    return `<span class="${it.c} font-semibold px-3 py-1.5 text-sm rounded">${it.t}</span>`;
                                }
                            });

                        }

                    }

                    return cols;
                }

                // =========================
                // ISSUE TABLE
                // =========================
                if (type === 'issue') {

                    return [
                        dtControlColumn,
                        {
                            data: 'issueid',
                            render: renderIssueLinkCell
                        },
                        {
                            data: 'issuedate',
                            render: (_v, _t, row) => row.issuedate_fmt ?? row.issuedate ?? '',
                            className: 'text-center'
                        },
                        {
                            data: 'issuetype'
                        },
                        {
                            data: 'spbid'
                        },
                        {
                            data: 'cpny_id',
                            className: 'text-center'
                        },
                        {
                            data: 'created_by'
                        },
                        {
                            data: 'status',
                            render: function(data) {

                                const map = {
                                    D: {
                                        t: 'Revise',
                                        c: 'bg-gray-200/60 text-gray-700 border border-gray-500/40'
                                    },
                                    P: {
                                        t: 'On Progress',
                                        c: 'bg-orange-200/60 text-orange-800 border border-orange-600/40'
                                    },
                                    C: {
                                        t: 'Completed',
                                        c: 'bg-green-200/60 text-green-800 border border-green-600/40'
                                    },
                                    X: {
                                        t: 'Cancel',
                                        c: 'bg-red-200/60 text-red-800 border border-red-600/40'
                                    },
                                    R: {
                                        t: 'Rejected',
                                        c: 'bg-red-200/60 text-red-800 border border-red-600/40'
                                    }
                                };

                                const it = map[data] ?? {
                                    t: data ?? '-',
                                    c: 'bg-gray-200/60 text-gray-700 border border-gray-500/40'
                                };

                                return `<span class="w-32 inline-block ${it.c} font-semibold px-3 py-1.5 text-sm text-center rounded">${it.t}</span>`;
                            }
                        }
                    ];
                }

                // =========================
                // SPPB TABLE
                // =========================
                return [
                    dtControlColumn,
                    {
                        data: 'sppbid',
                        render: (_v, _t, row) => renderSppbLink(row)
                    },
                    {
                        data: 'sppbdate',
                        render: (_v, _t, row) => row.sppbdate_fmt ?? row.sppbdate ?? '',
                        className: 'text-center'
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
                        data: 'requesttype_name'
                    },
                    {
                        data: 'keperluan'
                    },
                    {
                        data: 'status',
                        render: function(data) {

                            const map = {
                                D: {
                                    t: 'Revise',
                                    c: 'bg-amber-200/60 text-amber-800 border border-amber-600/40'
                                },
                                P: {
                                    t: 'On Progress',
                                    c: 'bg-orange-200/60 text-orange-800 border border-orange-600/40'
                                },
                                C: {
                                    t: 'Completed',
                                    c: 'bg-green-200/60 text-green-800 border border-green-600/40'
                                },
                                X: {
                                    t: 'Cancel',
                                    c: 'bg-red-200/60 text-red-800 border border-red-600/40'
                                },
                                R: {
                                    t: 'Rejected',
                                    c: 'bg-red-200/60 text-red-800 border border-red-600/40'
                                }
                            };

                            const it = map[data] ?? {
                                t: data ?? '-',
                                c: 'bg-gray-200/60 text-gray-700 border border-gray-500/40'
                            };

                            return `<span class="w-32 inline-block ${it.c} font-semibold px-3 py-1.5 text-sm text-center rounded">${it.t}</span>`;
                        }
                    }
                ];
            }

            // function orderFor(sc) {
            //     const type = scopeType(sc);
            //     if (type === 'spb') {
            //         return [
            //             [3, 'desc'], // spbdate
            //             [2, 'desc'] // spbid
            //         ];
            //     }
            //     if (type === 'issue') {
            //         return [
            //             [2, 'desc'], // issuedate
            //             [1, 'desc'] // issueid
            //         ];
            //     }
            //     // sppb
            //     return [
            //         [2, 'desc'], // sppbdate
            //         [1, 'desc'] // sppbid
            //     ];
            // }

            function orderFor(sc) {
                const type = scopeType(sc);

                if (type === 'spb') {
                    // scope yang diminta: order by SPBID desc
                    if (['issuejobsnew', 'issuejobs', 'onprogress'].includes(sc)) {
                        return [
                            [2, 'desc'] // spbid
                        ];
                    }

                    // scope flow / all tetap bisa pakai tanggal dulu
                    if (sc === 'spball') {
                        return [
                            [2, 'desc'] // spbid (karena spball hide action, index spbid jadi 1? lihat catatan bawah)
                        ];
                    }

                    return [
                        [3, 'desc'], // spbdate
                        [2, 'desc']  // spbid
                    ];
                }

                if (type === 'issue') {
                    return [
                        [2, 'desc'], // issuedate
                        [1, 'desc']  // issueid
                    ];
                }

                // sppb
                return [
                    [2, 'desc'], // sppbdate
                    [1, 'desc']  // sppbid
                ];
            }

            function updateTitle(sc) {
                $title.text(titleMap[sc] ?? 'Issue');
            }

            function highlightActive(sc) {
                $('.scope-filter').removeClass('active')
                    .each(function() {
                        if ($(this).data('scope') === sc) $(this).addClass('active');
                    });
            }

            function resetThead(sc) {
                $thead.empty().append('<tr id="thead-row"></tr>');
                $('#thead-row').html(headerFor(sc));
            }

            function rebuild(sc) {
                if ($.fn.DataTable.isDataTable('#issueTable')) {
                    $('#issueTable').DataTable().clear().destroy();
                }
                resetThead(sc);

                $('#issueTable').DataTable({
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

                    // 🔥 ADD THIS
                    dom: '<"dt-toolbar"l B f>rtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'SPB_Jobs',
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
                            title: 'SPB_Jobs',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        }
                    ],
                    // 🔥 END ADD
                    order: orderFor(sc),
                    ajax: {
                        url: "{{ route('spbjobs.json') }}",
                        type: "GET",
                        data: function(d) {
                            d.scope = sc;

                            d.date_from = $('#dateFrom').val();
                            d.date_to = $('#dateTo').val();
                        }
                    },
                    columns: columnsFor(sc),
                    searchDelay: 400,
                    stateSave: false,
                });
            }

            // init
            const savedScope = localStorage.getItem('activeIssueScope');
            if (savedScope && allowedScopes.includes(savedScope)) {
                scope = savedScope;
            }

            updateTitle(scope);
            highlightActive(scope);
            rebuild(scope);


            // switch scope
            $('.scope-filter').on('click', function(e) {
                e.preventDefault();
                scope = $(this).data('scope') || 'issuejobsnew';
                if (!allowedScopes.includes(scope)) scope = 'issuejobsnew';
                localStorage.setItem('activeIssueScope', scope);
                updateTitle(scope);
                highlightActive(scope);
                rebuild(scope);
            });

            $('#filterDate').on('click', function() {
                $('#issueTable').DataTable().ajax.reload();
            });

            $('#resetDate').on('click', function() {
                $('#dateFrom').val('');
                $('#dateTo').val('');
                $('#issueTable').DataTable().ajax.reload();
            });
        });
    </script>
</x-app-layout>
