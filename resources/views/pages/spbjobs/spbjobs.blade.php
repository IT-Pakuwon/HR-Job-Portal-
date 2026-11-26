<x-app-layout>
    <x-spp-list-styles />
    <x-spp-list-scripts />
    <div class="max-w-9xl mx-auto w-full p-2">
        @php
            $statusCards = [
                [['data-scope' => 'issuejobsnew'], 'SPB Open', '🆕', 'blue', $issuejobsnew, false, null, 'scope-filter'],
                [['data-scope' => 'issuejobs'], 'Issue Partial', '📦', 'purple', $issuejobs, false, null, 'scope-filter'],
                [['data-scope' => 'onprogress'], 'SPPB Jobs', '📑', 'sky', $sppbjobs, false, null, 'scope-filter'],
                [['data-scope' => 'issueprogress'], 'Issue On Progress', '⏳', 'yellow', $issueprogress, false, null, 'scope-filter'],
                [['data-scope' => 'sppbprogress'], 'SPPB On Progress', '📌', 'yellow', $sppbprogress, false, null, 'scope-filter'],
                [['data-scope' => 'spbprogress'], 'SPB On Progress', '📦', 'yellow', $spbprogress, false, null, 'scope-filter'],
                [['data-scope' => 'spball'], 'SPB Summary', '📊', 'all', $spball, false, null, 'scope-filter'],
                [['data-scope' => 'woflow'], 'SPB by WO', '🛠️', 'all', $woflow, false, null, 'scope-filter'],
                [['data-scope' => 'spbflow'], 'SPB → SPPB', '🔁', 'purple', $spbflow, false, null, 'scope-filter'],
            ];
        @endphp
        <x-status-filter-cards :custom="$statusCards" />
        <div
            class="dt-pretty mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="Issue" subtitle="SPB jobs, issues and SPPB flow" title-id="pageTitle">
                <div class="flex h-10 items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 shadow-sm dark:border-gray-600 dark:bg-gray-800">
                    <i class="fa-regular fa-calendar text-xs text-gray-400"></i>
                    <input type="date" id="dateFrom" class="w-32 border-0 bg-transparent p-0 text-sm text-gray-700 focus:outline-none focus:ring-0 dark:text-white" />
                    <span class="text-xs text-gray-400">–</span>
                    <input type="date" id="dateTo" class="w-32 border-0 bg-transparent p-0 text-sm text-gray-700 focus:outline-none focus:ring-0 dark:text-white" />
                </div>
                <button id="filterDate" type="button" class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500"><i class="fa-solid fa-filter text-xs"></i>Filter</button>
                <button id="resetDate" type="button" class="inline-flex h-10 items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-gray-50 hover:shadow-md active:translate-y-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"><i class="fas fa-rotate-right text-xs"></i>Reset</button>
            </x-list-page-header>

            <div class="relative overflow-hidden">
                <table id="issueTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead
                        class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                        <tr id="thead-row"></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-[#0f172a]"></tbody>
                </table>
            </div>
        </div>
    </div>


    <script>
        const currentUser = @json(auth()->user()->username ?? '');

        $(function() {
            let scope = 'issuejobsnew';
            const $title = $('#pageTitle');
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
                                <th class="px-4 py-3 text-left font-medium">
                                Action
                                </th>` : ``}

        <th class="px-4 py-3 text-left font-medium">
        SPB ID
        </th>

        ${isWoFlow ? `
                                <th class="px-4 py-3 text-left font-medium">
                                WO ID
                                </th>` : ``}

        ${isSpbFlow ? `
                                <th class="px-4 py-3 text-left font-medium">
                                SPPB ID
                                </th>` : ``}

        <th class="px-4 py-3 text-center font-medium">
        SPB Date
        </th>

        <th class="px-4 py-3 text-center font-medium">
        Company
        </th>

        <th class="px-4 py-3 text-center font-medium">
        Department
        </th>

        <th class="px-4 py-3 text-left font-medium">
        Keperluan
        </th>

        <th class="px-4 py-3 text-left font-medium">
        Created By
        </th>

        ${isSpbAll ? `
                                <th class="px-4 py-3 text-left font-medium">
                                Status SPB
                                </th>
                                <th class="px-4 py-3 text-left font-medium">
                                Status Issue
                                </th>
                                ` : !hideStatus ? `
                                <th class="px-4 py-3 text-left font-medium">
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

        <th class="px-4 py-3 text-left font-medium">
        Issue ID
        </th>

        <th class="px-4 py-3 text-center font-medium">
        Issue Date
        </th>

        <th class="px-4 py-3 text-left font-medium">
        Issue Type
        </th>

        <th class="px-4 py-3 text-left font-medium">
        SPB ID
        </th>

        <th class="px-4 py-3 text-center font-medium">
        Company
        </th>

        <th class="px-4 py-3 text-left font-medium">
        Created By
        </th>

        <th class="px-4 py-3 text-left font-medium">
        Status
        </th>
        `;
                }

                // =========================
                // SPPB HEADER
                // =========================
                return `
        <th></th>

        <th class="px-4 py-3 text-left font-medium">
        SPPB ID
        </th>

        <th class="px-4 py-3 text-center font-medium">
        SPPB Date
        </th>

        <th class="px-4 py-3 text-center font-medium">
        Company
        </th>

        <th class="px-4 py-3 text-center font-medium">
        Department
        </th>

        <th class="px-4 py-3 text-left font-medium">
        Request Type
        </th>

        <th class="px-4 py-3 text-left font-medium">
        Description
        </th>

        <th class="px-4 py-3 text-left font-medium">
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
       class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 group-hover/doc:-rotate-6"><i class="fa-regular fa-file-lines"></i></span><span>${label}</span></a>
    `;
            }

            function renderSppbLink(row) {
                const label = row.sppbid ?? '';
                const hash = row.eid || row.sppb_hash || row.hash || row.id;
                if (!label) return '';
                const url =
                    `/showsppbs/${encodeURIComponent(hash ?? '')}`; // sesuaikan dengan route detail SPPB-mu
                return `<a href="${url}" target="_blank" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 group-hover/doc:-rotate-6"><i class="fa-regular fa-file-lines"></i></span><span>${label}</span></a>`;
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
                    return `<a href="${url}" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-amber-800 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-amber-500 hover:bg-amber-500 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 group-hover/doc:rotate-12"><i class="fa-solid fa-pen text-[10px]"></i></span><span>${label}</span></a>`;
                }
                const url = `/showissue/${encodeURIComponent(hash)}`;
                return `<a href="${url}" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 group-hover/doc:-rotate-6"><i class="fa-regular fa-file-lines"></i></span><span>${label}</span></a>`;
            }

            // function renderPlusCreate(row) {
            //     const url = `{{ route('issue.create') }}` + `?spbid=${encodeURIComponent(row.spb_eid ?? '')}`;
            //     return `<a href="${url}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-blue-100 bg-blue-50 text-sm font-bold text-blue-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90">
        //         <i class="fas fa-plus"></i>
        //     </a>`;
            // }
            function renderIssueCreate(row) {
                const url = `{{ route('issue.create') }}` + `?spbid=${encodeURIComponent(row.spb_eid ?? '')}`;
                return `<a href="${url}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-blue-100 bg-blue-50 text-sm font-bold text-blue-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90">
                                                    <i class="fas fa-plus"></i>
                                                </a>`;
            }

            function renderSppbCreate(row) {
                const url = `{{ route('sppb.create') }}` + `?spbid=${encodeURIComponent(row.spb_eid ?? '')}`;
                return `<a href="${url}" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-amber-100 bg-amber-50 text-sm font-bold text-amber-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-amber-600 hover:bg-amber-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90">
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
                        class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 group-hover/doc:-rotate-6"><i class="fa-regular fa-file-lines"></i></span><span>${row.woid}</span></a>`;
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
                        class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 group-hover/doc:-rotate-6"><i class="fa-regular fa-file-lines"></i></span><span>${data}</span></a>`;
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
                                            c: 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30'
                                        },
                                        P: {
                                            t: 'On Progress',
                                            c: 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:ring-yellow-500/30'
                                        },
                                        D: {
                                            t: 'Revise',
                                            c: 'bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-500/30'
                                        },
                                        X: {
                                            t: 'Cancel',
                                            c: 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30'
                                        },
                                        R: {
                                            t: 'Rejected',
                                            c: 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30'
                                        }
                                    };

                                    const it = map[data] ?? {
                                        t: data ?? '-',
                                        c: 'bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-500/30'
                                    };

                                    return `<span class="inline-flex min-w-[116px] items-center justify-center whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ${it.c}">${it.t}</span>`;
                                }
                            });

                            // STATUS ISSUE
                            cols.push({
                                data: 'status_issue',
                                render: function(data) {

                                    const map = {
                                        Open: {
                                            t: 'Open',
                                            c: 'bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-500/30'
                                        },
                                        Partial: {
                                            t: 'Partial',
                                            c: 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30'
                                        },
                                        Completed: {
                                            t: 'Completed',
                                            c: 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30'
                                        },
                                        Full: {
                                            t: 'Full',
                                            c: 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30'
                                        }
                                    };

                                    const it = map[data] ?? {
                                        t: data ?? '-',
                                        c: 'bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-500/30'
                                    };

                                    return `<span class="inline-flex min-w-[116px] items-center justify-center whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ${it.c}">${it.t}</span>`;
                                }
                            });

                        } else {

                            cols.push({
                                data: null,
                                render: (_v, _t, row) => {

                                    const map = {
                                        C: {
                                            t: 'Completed',
                                            c: 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30'
                                        },
                                        P: {
                                            t: 'On Progress',
                                            c: 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:ring-yellow-500/30'
                                        },
                                        D: {
                                            t: 'Revise',
                                            c: 'bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-500/30'
                                        },
                                        X: {
                                            t: 'Cancel',
                                            c: 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30'
                                        },
                                        R: {
                                            t: 'Rejected',
                                            c: 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30'
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
                                        c: 'bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-500/30'
                                    };

                                    return `<span class="inline-flex min-w-[116px] items-center justify-center whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ${it.c}">${it.t}</span>`;
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
                                        c: 'bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-500/30'
                                    },
                                    P: {
                                        t: 'On Progress',
                                        c: 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:ring-yellow-500/30'
                                    },
                                    C: {
                                        t: 'Completed',
                                        c: 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30'
                                    },
                                    X: {
                                        t: 'Cancel',
                                        c: 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30'
                                    },
                                    R: {
                                        t: 'Rejected',
                                        c: 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30'
                                    }
                                };

                                const it = map[data] ?? {
                                    t: data ?? '-',
                                    c: 'bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-500/30'
                                };

                                return `<span class="inline-flex min-w-[116px] items-center justify-center whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ${it.c}">${it.t}</span>`;
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
                                    c: 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30'
                                },
                                P: {
                                    t: 'On Progress',
                                    c: 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:ring-yellow-500/30'
                                },
                                C: {
                                    t: 'Completed',
                                    c: 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30'
                                },
                                X: {
                                    t: 'Cancel',
                                    c: 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30'
                                },
                                R: {
                                    t: 'Rejected',
                                    c: 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30'
                                }
                            };

                            const it = map[data] ?? {
                                t: data ?? '-',
                                c: 'bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-500/30'
                            };

                            return `<span class="inline-flex min-w-[116px] items-center justify-center whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ${it.c}">${it.t}</span>`;
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

                // =========================
                // SPB TABLE
                // =========================
                if (type === 'spb') {

                    const isSpbAll = (sc === 'spball');
                    const isWoFlow = (sc === 'woflow');
                    const isSpbFlow = (sc === 'spbflow');

                    // scopes that hide action column
                    const hideAction = (sc === 'spbprogress' || isSpbAll || isWoFlow || isSpbFlow);

                    // column index shifts depending on action column
                    const dateIndex = hideAction ? 2 : 3;
                    const idIndex = hideAction ? 1 : 2;

                    return [
                        [dateIndex, 'desc'], // newest date first
                        [idIndex, 'desc'] // fallback by ID
                    ];
                }

                // =========================
                // ISSUE TABLE
                // =========================
                if (type === 'issue') {

                    return [
                        [2, 'desc'], // issuedate
                        [1, 'desc'] // issueid
                    ];
                }

                // =========================
                // SPPB TABLE
                // =========================
                return [
                    [2, 'desc'], // sppbdate
                    [1, 'desc'] // sppbid
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
                            text: '<i class="fa-solid fa-file-excel"></i> Excel',
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
                            text: '<i class="fa-solid fa-file-csv"></i> CSV',
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
