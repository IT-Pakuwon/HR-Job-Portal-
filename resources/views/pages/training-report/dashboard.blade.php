<x-app-layout>

    <div class="max-w-9xl mx-auto w-full space-y-3 p-2">

        {{-- ── Page Header ─────────────────────────────────────────────────────── --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    TRAINING REPORT
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Learning &amp; Development activity — attendance, satisfaction and participant mix
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
                    <x-dashboard-filter.dashboard-filter :showDepartment="false" />
                </div>
                <div class="flex w-full sm:w-auto" id="trnrepExtraFilterWrap">
                    <div class="flex flex-1 flex-col rounded-2xl border border-slate-200 bg-white shadow-sm sm:flex-none sm:flex-row divide-y divide-slate-200 sm:divide-y-0 sm:divide-x sm:divide-slate-200 dark:border-slate-700/60 dark:bg-slate-900 dark:divide-slate-700/60">
                        <div class="flex items-center gap-1.5 px-3 py-1.5">
                            <svg class="h-3.5 w-3.5 shrink-0 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>
                            <select id="trnrepTrainingFilter">
                                <option value="">All Trainings</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-1.5 px-3 py-1.5">
                            <svg class="h-3.5 w-3.5 shrink-0 text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <select id="trnrepScheduleFilter">
                                <option value="">All Schedules</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-1.5 px-3 py-1.5">
                            <svg class="h-3.5 w-3.5 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5l7.5-6 7.5 6m-15 0v9a1.5 1.5 0 001.5 1.5h12a1.5 1.5 0 001.5-1.5v-9m-15 0l7.5 4.5 7.5-4.5" />
                            </svg>
                            <select id="trnrepLevelFilter">
                                <option value="">All Levels</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="relative w-full sm:w-auto" id="trnrepExportWrap">
                    <button id="trnrepExportBtn" type="button"
                        onclick="document.getElementById('trnrepExportDropdown').classList.toggle('hidden')"
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
                    <div id="trnrepExportDropdown"
                        class="min-w-35 absolute right-0 top-full z-50 mt-1.5 hidden rounded-xl border border-slate-200/80 bg-white py-1 shadow-lg dark:border-slate-700/60 dark:bg-slate-800">
                        <a id="trnrepExport_pdf" href="#"
                            class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-red-50 hover:text-red-600 dark:text-slate-300 dark:hover:bg-red-500/10 dark:hover:text-red-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0
                                         0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Export PDF
                        </a>
                        <a id="trnrepExport_xlsx" href="#"
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
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <x-card-chart.stat-card title="Total Attendance" subtitle="Period" value="0" color="blue" valueId="trnrepStatAttendance" />
            <x-card-chart.stat-card title="Avg. Satisfaction" subtitle="Feedback rating" value="–" color="green" valueId="trnrepStatSatisfaction" />
            <x-card-chart.stat-card title="Completion Rate" subtitle="Attended / Registered" value="0%" color="violet" valueId="trnrepStatRate" />
        </div>

        {{-- ── Quota vs Registered vs Attended ─────────────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-3" style="align-items:stretch">

            <x-card-chart.card-shell class="lg:col-span-2 flex flex-col" subtitle="Capacity" title="Quota vs Registered vs Attended"
                gradient="linear-gradient(to right,#06B6D4,#3B82F6)">
                <div class="flex-1 px-2 pb-3 pt-1">
                    <div id="trnrepQuotaFunnelChart" style="min-height:260px"></div>
                </div>
            </x-card-chart.card-shell>

            <x-card-chart.card-shell class="flex flex-col" subtitle="Capacity" title="Highlights"
                gradient="linear-gradient(to right,#8B5CF6,#06B6D4)">
                <div class="flex-1 space-y-3 px-5 pb-5 pt-1">
                    <div class="rounded-xl border border-slate-100 bg-slate-50 px-3.5 py-3 dark:border-slate-700/60 dark:bg-slate-800/50">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Fill Rate</p>
                        <p class="mt-1 text-xl font-extrabold text-slate-900 dark:text-white" id="trnrepFillRate">0%</p>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Registered ÷ Quota offered</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 px-3.5 py-3 dark:border-slate-700/60 dark:bg-slate-800/50">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">No-show Rate</p>
                        <p class="mt-1 text-xl font-extrabold text-slate-900 dark:text-white" id="trnrepNoShowRate">0%</p>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Registered but never checked in</p>
                    </div>
                </div>
            </x-card-chart.card-shell>

        </div>

        {{-- ── Trend + Top Trainings ───────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-3" style="align-items:stretch">

            <x-card-chart.card-shell class="lg:col-span-2 flex flex-col" subtitle="Trend" title="Attendance &amp; Satisfaction Over Time"
                gradient="linear-gradient(to right,#3B82F6,#8B5CF6,#EC4899)">
                <div class="flex-1 px-2 pb-3 pt-1">
                    <div id="trnrepTrendChart" style="min-height:320px"></div>
                </div>
            </x-card-chart.card-shell>

            <x-card-chart.card-shell class="flex flex-col" subtitle="Ranking" title="Top 5 Training"
                gradient="linear-gradient(to right,#8B5CF6,#06B6D4)">
                <div class="flex-1 px-2 pb-3 pt-1">
                    <div id="trnrepTopTrainingsChart" style="min-height:320px"></div>
                </div>
            </x-card-chart.card-shell>

        </div>

        {{-- ── Department + Level breakdown ────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2" style="align-items:stretch">

            <x-card-chart.card-shell class="flex flex-col" subtitle="Participant Mix" title="Top 10 Attendance by Department"
                gradient="linear-gradient(to right,#10B981,#06B6D4)">
                <div class="flex-1 px-2 pb-3 pt-1">
                    <div id="trnrepByDepartmentChart" style="min-height:280px"></div>
                </div>
            </x-card-chart.card-shell>

            <x-card-chart.card-shell class="flex flex-col" subtitle="Participant Mix" title="Attendance by Level/Grade"
                gradient="linear-gradient(to right,#F59E0B,#EC4899)">
                <div class="flex-1 px-2 pb-3 pt-1">
                    <div id="trnrepByLevelChart" style="min-height:280px"></div>
                </div>
            </x-card-chart.card-shell>

        </div>

        {{-- ── Session List Table ───────────────────────────────────────────────────── --}}
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900">
            <div class="absolute inset-x-0 top-0 h-0.75" style="background:linear-gradient(to right,#3B82F6,#8B5CF6,#EC4899)"></div>

            <div class="flex flex-wrap items-center justify-between gap-3 px-5 pb-3 pt-5">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Sessions</p>
                    <h3 class="mt-0.5 text-sm font-bold text-slate-800 dark:text-white">List</h3>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input id="trnrepTableSearch" type="text" placeholder="Search…"
                            class="w-48 rounded-lg border border-slate-200 bg-white py-1.5 pl-8 pr-3 text-xs outline-none focus:border-violet-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    </div>
                    <div class="relative">
                        <select id="trnrepTablePageSize"
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
                <table id="trnrepTableBody-tbl" class="w-full min-w-[820px] text-xs">
                    <thead>
                        <tr class="border-t border-slate-100 dark:border-slate-700/60">
                            <th data-sort-key="date" class="select-none cursor-pointer whitespace-nowrap bg-slate-50 px-5 py-2.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 transition-colors hover:text-slate-600 dark:bg-slate-800/50 dark:text-slate-500 dark:hover:text-slate-300">Date <span class="sort-icon ml-0.5 opacity-30">↕</span></th>
                            <th data-sort-key="training_name" class="select-none cursor-pointer whitespace-nowrap bg-slate-50 px-4 py-2.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 transition-colors hover:text-slate-600 dark:bg-slate-800/50 dark:text-slate-500 dark:hover:text-slate-300">Training <span class="sort-icon ml-0.5 opacity-30">↕</span></th>
                            <th data-sort-key="level_name" class="select-none cursor-pointer whitespace-nowrap bg-slate-50 px-4 py-2.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 transition-colors hover:text-slate-600 dark:bg-slate-800/50 dark:text-slate-500 dark:hover:text-slate-300">Level <span class="sort-icon ml-0.5 opacity-30">↕</span></th>
                            <th data-sort-key="attendees" data-sort-numeric="true" class="select-none cursor-pointer whitespace-nowrap bg-slate-50 px-4 py-2.5 text-right text-[11px] font-bold uppercase tracking-wider text-slate-400 transition-colors hover:text-slate-600 dark:bg-slate-800/50 dark:text-slate-500 dark:hover:text-slate-300">Attendees <span class="sort-icon ml-0.5 opacity-30">↕</span></th>
                            <th data-sort-key="avg_satisfaction" data-sort-numeric="true" class="select-none cursor-pointer whitespace-nowrap bg-slate-50 px-4 py-2.5 text-right text-[11px] font-bold uppercase tracking-wider text-slate-400 transition-colors hover:text-slate-600 dark:bg-slate-800/50 dark:text-slate-500 dark:hover:text-slate-300">Satisfaction <span class="sort-icon ml-0.5 opacity-30">↕</span></th>
                            <th data-sort-key="avg_stars" data-sort-numeric="true" class="select-none cursor-pointer whitespace-nowrap bg-slate-50 px-5 py-2.5 text-right text-[11px] font-bold uppercase tracking-wider text-slate-400 transition-colors hover:text-slate-600 dark:bg-slate-800/50 dark:text-slate-500 dark:hover:text-slate-300">Stars <span class="sort-icon ml-0.5 opacity-30">↕</span></th>
                        </tr>
                    </thead>
                    <tbody id="trnrepTableBody" class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400 dark:text-slate-500">Loading…</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="trnrepTablePagination" class="hidden flex items-center justify-between border-t border-slate-100 px-5 py-3 dark:border-slate-700/60">
                <span id="trnrepTablePageInfo" class="text-xs text-slate-500 dark:text-slate-400"></span>
                <div class="flex items-center gap-1">
                    <button id="trnrepTablePrev" type="button"
                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">‹</button>
                    <div id="trnrepTablePageNums" class="flex items-center gap-1"></div>
                    <button id="trnrepTableNext" type="button"
                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">›</button>
                </div>
            </div>
        </div>

    </div>

    <script>
        window.gmRoutes = {
            companies: "{{ route('training-report.companies') }}",
        };

        window.trainingReportRoutes = {
            companies: "{{ route('training-report.companies') }}",
            filters: "{{ route('training-report.filters') }}",
            summary: "{{ route('training-report.summary') }}",
            quotaFunnel: "{{ route('training-report.quota-funnel') }}",
            byDepartment: "{{ route('training-report.by-department') }}",
            byLevel: "{{ route('training-report.by-level') }}",
            topTrainings: "{{ route('training-report.top-trainings') }}",
            trend: "{{ route('training-report.trend') }}",
            table: "{{ route('training-report.table') }}",
        };
    </script>

    {{-- Select2 (Training/Schedule/Level filters) --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        #trnrepExtraFilterWrap .select2-container { display: inline-block; vertical-align: middle; }
        #trnrepExtraFilterWrap .select2-selection--single {
            height: 24px; display: flex; align-items: center;
            border: none !important; background: transparent !important;
            padding: 0 1.25rem 0 0;
        }
        #trnrepExtraFilterWrap .select2-container--open .select2-selection--single { box-shadow: none; }
        #trnrepExtraFilterWrap .select2-selection__rendered {
            padding: 0; line-height: 24px; color: #334155; font-weight: 600;
            font-size: 0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        #trnrepExtraFilterWrap .select2-selection__arrow { height: 24px; right: 0; }
        #trnrepExtraFilterWrap .select2-selection__arrow b { border-color: #94a3b8 transparent transparent transparent; }
        #trnrepExtraFilterWrap .select2-dropdown {
            border-radius: 0.75rem; border: 1px solid #e2e8f0;
            overflow: hidden; margin-top: 4px;
            min-width: 240px; max-width: min(320px, calc(100vw - 2rem));
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.05);
        }
        #trnrepExtraFilterWrap .select2-search__field {
            border-radius: 0.5rem; border: 1px solid #e2e8f0;
            padding: 0.375rem 0.5rem; font-size: 0.75rem; outline: none;
        }
        #trnrepExtraFilterWrap .select2-search__field:focus { border-color: #8B5CF6; box-shadow: 0 0 0 3px rgba(139,92,246,0.12); }
        #trnrepExtraFilterWrap .select2-results__option { font-size: 0.75rem; padding: 0.5rem 0.75rem; transition: background .1s; white-space: normal; word-break: break-word; }
        #trnrepExtraFilterWrap .select2-results__option--highlighted[aria-selected] { background-color: #F5F3FF; color: #7C3AED; }
        #trnrepExtraFilterWrap .select2-results__option[aria-selected="true"] { background-color: #EDE9FE; color: #6D28D9; font-weight: 600; }

        .dark #trnrepExtraFilterWrap .select2-selection__rendered { color: #e2e8f0; }
        .dark #trnrepExtraFilterWrap .select2-selection__arrow b { border-color: #64748b transparent transparent transparent; }
        .dark #trnrepExtraFilterWrap .select2-dropdown { background-color: #1e293b; border-color: #475569; }
        .dark #trnrepExtraFilterWrap .select2-search__field { background-color: #334155; border-color: #475569; color: #e2e8f0; }
        .dark #trnrepExtraFilterWrap .select2-results__option { color: #e2e8f0; }
        .dark #trnrepExtraFilterWrap .select2-results__option--highlighted[aria-selected] { background-color: #4C1D95; color: #fff; }
        .dark #trnrepExtraFilterWrap .select2-results__option[aria-selected="true"] { background-color: #5B21B6; color: #fff; }
        .dark #trnrepExtraFilterWrap .select2-search__field:focus { border-color: #8B5CF6; }
    </style>

    <script src="{{ asset('assets/js/gm-report/gm-core.js') }}"></script>
    <script src="{{ asset('assets/js/gm-report/gm-filter.js') }}"></script>
    <script src="{{ asset('assets/js/training-report/report.js') }}?v={{ filemtime(public_path('assets/js/training-report/report.js')) }}"></script>

    <script>
        (function () {
            var exportRoutes = {
                pdf: '{{ route('training-report.export.pdf') }}',
                xlsx: '{{ route('training-report.export.xlsx') }}',
            };

            function updateExportLinks() {
                var params = window.trainingReportCombinedParams
                    ? window.trainingReportCombinedParams()
                    : (window.gmUtils ? window.gmUtils.buildParams() : '');
                ['pdf', 'xlsx'].forEach(function (fmt) {
                    var el = document.getElementById('trnrepExport_' + fmt);
                    if (el) el.href = exportRoutes[fmt] + params;
                });
            }

            document.addEventListener('gm:filter', updateExportLinks);

            document.addEventListener('click', function (e) {
                var wrap = document.getElementById('trnrepExportWrap');
                var dd = document.getElementById('trnrepExportDropdown');
                if (wrap && dd && !wrap.contains(e.target)) {
                    dd.classList.add('hidden');
                }
            });
        })();
    </script>

</x-app-layout>
