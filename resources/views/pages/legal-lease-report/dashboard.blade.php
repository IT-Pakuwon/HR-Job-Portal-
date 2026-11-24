<x-app-layout>

    <div class="max-w-9xl mx-auto w-full space-y-3 p-2">

        {{-- ── Page Header ─────────────────────────────────────────────────────── --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    LEGAL LEASE REPORT
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    PSM / OLA, Addendum, Others &amp; Agreement FU — volume per company, status, top tenants and missing documents
                </p>
            </div>

            <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
                <span class="order-last inline-flex items-center justify-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-500 dark:bg-slate-800 dark:text-slate-400 sm:order-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Last Updated: {{ $lastUpdatedAt }}
                </span>
                <div class="flex w-full sm:w-auto">
                    <x-dashboard-filter.dashboard-filter
                        :showDepartment="false"
                        :ticketTypes="[
                            ['value' => '', 'label' => 'All Types'],
                            ['value' => 'PSM', 'label' => 'PSM'],
                            ['value' => 'OLA', 'label' => 'OLA'],
                            ['value' => 'ADDENDUM', 'label' => 'Addendum'],
                            ['value' => 'OTHERS', 'label' => 'Others'],
                        ]" />
                </div>
                <div class="relative w-full sm:w-auto" id="llrepExportWrap">
                    <button id="llrepExportBtn" type="button"
                        onclick="document.getElementById('llrepExportDropdown').classList.toggle('hidden')"
                        class="flex w-full items-center justify-center gap-1.5 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 sm:w-auto sm:justify-start dark:border-slate-700/60 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 opacity-60" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="llrepExportDropdown"
                        class="min-w-35 absolute right-0 top-full z-50 mt-1.5 hidden rounded-xl border border-slate-200/80 bg-white py-1 shadow-lg dark:border-slate-700/60 dark:bg-slate-800">
                        <a id="llrepExport_pdf" href="#"
                            class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-red-50 hover:text-red-600 dark:text-slate-300 dark:hover:bg-red-500/10 dark:hover:text-red-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0
                                         0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Export PDF
                        </a>
                        <a id="llrepExport_xlsx" href="#"
                            class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-violet-50 hover:text-violet-600 dark:text-slate-300 dark:hover:bg-violet-500/10 dark:hover:text-violet-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2
                                         2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Export Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Stat Cards ───────────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <x-card-chart.stat-card title="Total Agreements" subtitle="Period" value="0" color="blue" valueId="llrepStatTotal" />
            <x-card-chart.stat-card title="Completed" subtitle="Period" value="0" color="green" valueId="llrepStatCompleted" />
            <x-card-chart.stat-card title="In Progress" subtitle="Active, Hold & Escalated" value="0" color="orange" valueId="llrepStatProgress" />
            <x-card-chart.stat-card title="Completion Rate" subtitle="Period" value="0%" color="violet" valueId="llrepStatRate" />
            <x-card-chart.stat-card title="Incomplete Documents" subtitle="Missing tenant documents" value="0" color="red" valueId="llrepStatIncomplete" />
        </div>

        {{-- ── Activity Overview: category-by-unit chart + Key Highlights ─────────── --}}
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-3" style="align-items:stretch">

            <x-card-chart.stacked-bar-card class="lg:col-span-2" subtitle="Agreement Overview"
                title="Agreement Type by Company" titleId="llrepCategoryByUnitTitle"
                gradient="linear-gradient(to right,#3B82F6,#8B5CF6,#EC4899)"
                chartId="llrepCategoryByUnitChart" :minHeight="340" />

            <x-card-chart.card-shell class="flex flex-col" subtitle="Summary" title="Key Highlights"
                gradient="linear-gradient(to right,#8B5CF6,#06B6D4)">
                <div id="llrepKeyHighlights" class="flex-1 space-y-3 px-5 pb-5 pt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-300">
                    <p class="text-slate-400 dark:text-slate-500">Loading…</p>
                </div>
            </x-card-chart.card-shell>

        </div>

        {{-- ── Memo & LoI documents by property type ──────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2" style="align-items:stretch">
            <x-card-chart.stacked-bar-card subtitle="PSM / OLA" title="New LOI & Memo"
                gradient="linear-gradient(to right,#06B6D4,#3B82F6)"
                chartId="llrepNewLoiMemoChart" :minHeight="300" />

            <x-card-chart.stacked-bar-card subtitle="Addendum" title="Change Memo"
                gradient="linear-gradient(to right,#8B5CF6,#EC4899)"
                chartId="llrepAddendumMemoChart" :minHeight="300" />
        </div>

        {{-- ── Tenants sent their agreement + Surat 1 / Surat 2 / Escalation ───────── --}}
        <x-card-chart.stacked-bar-card subtitle="Agreement FU" title="Tenants Sent, Surat 1, Surat 2 &amp; Escalation"
            description="Number of tenants per document type. 'Sent' = the agreement has been delivered to the tenant; the letters follow the H+14 / H+14 / H+7 reminder cycle."
            gradient="linear-gradient(to right,#06B6D4,#F59E0B,#EF4444)"
            summaryId="llrepSentLettersSummary" chartId="llrepSentLettersChart" :minHeight="280" />

        {{-- ── Status by Category + Top Breakdown ──────────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2" style="align-items:stretch">

            <x-card-chart.stacked-bar-card subtitle="Breakdown" title="Status by Agreement Type"
                gradient="linear-gradient(to right,#10B981,#F59E0B)"
                chartId="llrepStatusByCategoryChart" />

            <x-card-chart.stacked-bar-card subtitle="Breakdown" title="Top Tenants" titleId="llrepTopBreakdownTitle"
                gradient="linear-gradient(to right,#3B82F6,#06B6D4)"
                chartId="llrepTopBreakdownChart" />

        </div>

        {{-- ── List Table ───────────────────────────────────────────────────────────── --}}
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900">
            <div class="absolute inset-x-0 top-0 h-0.75" style="background:linear-gradient(to right,#3B82F6,#8B5CF6,#EC4899)"></div>

            <div class="flex flex-wrap items-center justify-between gap-3 px-5 pb-3 pt-5">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500" id="llrepTableSubtitle">Agreements</p>
                    <h3 class="mt-0.5 text-sm font-bold text-slate-800 dark:text-white">List</h3>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input id="llrepTableSearch" type="text" placeholder="Search…"
                            class="w-48 rounded-lg border border-slate-200 bg-white py-1.5 pl-8 pr-3 text-xs outline-none focus:border-violet-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    </div>
                    <div class="relative">
                        <select id="llrepTablePageSize"
                            class="h-7.5 cursor-pointer appearance-none rounded-lg border border-slate-200 bg-white py-1.5 pl-3 pr-7 text-xs outline-none focus:border-violet-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                            <option value="100">100 / page</option>
                        </select>
                        <svg class="pointer-events-none absolute right-2 top-1/2 h-3 w-3 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="llrepTableBody-tbl" class="w-full min-w-[1100px] text-xs">
                    <thead>
                        <tr class="border-t border-slate-100 dark:border-slate-700/60">
                            <th data-sort-key="date_sort" class="select-none cursor-pointer whitespace-nowrap bg-slate-50 px-5 py-2.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 transition-colors hover:text-slate-600 dark:bg-slate-800/50 dark:text-slate-500 dark:hover:text-slate-300">Date <span class="sort-icon ml-0.5 opacity-30">↕</span></th>
                            <th data-sort-key="docid" class="select-none cursor-pointer whitespace-nowrap bg-slate-50 px-4 py-2.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 transition-colors hover:text-slate-600 dark:bg-slate-800/50 dark:text-slate-500 dark:hover:text-slate-300">Agreement <span class="sort-icon ml-0.5 opacity-30">↕</span></th>
                            <th data-sort-key="category" class="select-none cursor-pointer whitespace-nowrap bg-slate-50 px-4 py-2.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 transition-colors hover:text-slate-600 dark:bg-slate-800/50 dark:text-slate-500 dark:hover:text-slate-300">Type <span class="sort-icon ml-0.5 opacity-30">↕</span></th>
                            <th data-sort-key="company" class="select-none cursor-pointer whitespace-nowrap bg-slate-50 px-4 py-2.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 transition-colors hover:text-slate-600 dark:bg-slate-800/50 dark:text-slate-500 dark:hover:text-slate-300">Company <span class="sort-icon ml-0.5 opacity-30">↕</span></th>
                            <th data-sort-key="tenant" class="select-none cursor-pointer whitespace-nowrap bg-slate-50 px-4 py-2.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 transition-colors hover:text-slate-600 dark:bg-slate-800/50 dark:text-slate-500 dark:hover:text-slate-300">Tenant <span class="sort-icon ml-0.5 opacity-30">↕</span></th>
                            <th class="whitespace-nowrap bg-slate-50 px-4 py-2.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:bg-slate-800/50 dark:text-slate-500">Unit</th>
                            <th class="whitespace-nowrap bg-slate-50 px-4 py-2.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:bg-slate-800/50 dark:text-slate-500">PIC Legal</th>
                            <th class="whitespace-nowrap bg-slate-50 px-4 py-2.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:bg-slate-800/50 dark:text-slate-500">Docs</th>
                            <th data-sort-key="status" class="select-none cursor-pointer whitespace-nowrap bg-slate-50 px-4 py-2.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 transition-colors hover:text-slate-600 dark:bg-slate-800/50 dark:text-slate-500 dark:hover:text-slate-300">Status <span class="sort-icon ml-0.5 opacity-30">↕</span></th>
                            <th class="whitespace-nowrap bg-slate-50 px-5 py-2.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:bg-slate-800/50 dark:text-slate-500 text-center">Operation</th>
                        </tr>
                    </thead>
                    <tbody id="llrepTableBody" class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        <tr>
                            <td colspan="10" class="px-5 py-8 text-center text-slate-400 dark:text-slate-500">Loading…</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="llrepTablePagination" class="hidden flex items-center justify-between border-t border-slate-100 px-5 py-3 dark:border-slate-700/60">
                <span id="llrepTablePageInfo" class="text-xs text-slate-500 dark:text-slate-400"></span>
                <div class="flex items-center gap-1">
                    <button id="llrepTablePrev" type="button"
                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">‹</button>
                    <div id="llrepTablePageNums" class="flex items-center gap-1"></div>
                    <button id="llrepTableNext" type="button"
                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">›</button>
                </div>
            </div>
        </div>

    </div>

    <script>
        // gm-filter.js (shared company/date filter) reads window.gmRoutes —
        // this page has no department filter (:showDepartment="false"), so only
        // `companies` is needed here.
        window.gmRoutes = {
            companies: "{{ route('legal-lease-report.companies') }}",
        };

        window.legalLeaseReportRoutes = {
            companies: "{{ route('legal-lease-report.companies') }}",
            summary: "{{ route('legal-lease-report.summary') }}",
            categoryByUnit: "{{ route('legal-lease-report.category-by-unit') }}",
            memoLoi: "{{ route('legal-lease-report.memo-loi') }}",
            sentLetters: "{{ route('legal-lease-report.sent-letters') }}",
            statusByCategory: "{{ route('legal-lease-report.status-by-category') }}",
            topBreakdown: "{{ route('legal-lease-report.top-breakdown') }}",
            table: "{{ route('legal-lease-report.table') }}",
        };
    </script>

    <script src="{{ asset('assets/js/gm-report/gm-core.js') }}"></script>
    <script src="{{ asset('assets/js/gm-report/gm-filter.js') }}"></script>
    <script src="{{ asset('assets/js/legal-lease-report/report.js') }}?v={{ filemtime(public_path('assets/js/legal-lease-report/report.js')) }}"></script>

    <script>
        (function () {
            var exportRoutes = {
                pdf: '{{ route('legal-lease-report.export.pdf') }}',
                xlsx: '{{ route('legal-lease-report.export.xlsx') }}',
            };

            function updateExportLinks() {
                var params = window.gmUtils ? window.gmUtils.buildParams() : '';
                ['pdf', 'xlsx'].forEach(function (fmt) {
                    var el = document.getElementById('llrepExport_' + fmt);
                    if (el) el.href = exportRoutes[fmt] + params;
                });
            }

            document.addEventListener('gm:filter', updateExportLinks);

            document.addEventListener('click', function (e) {
                var wrap = document.getElementById('llrepExportWrap');
                var dd = document.getElementById('llrepExportDropdown');
                if (wrap && dd && !wrap.contains(e.target)) {
                    dd.classList.add('hidden');
                }
            });
        })();
    </script>

</x-app-layout>
