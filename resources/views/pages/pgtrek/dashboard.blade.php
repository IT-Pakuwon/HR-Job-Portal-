<x-app-layout>

    <div class="max-w-9xl mx-auto w-full space-y-4 p-2"
        x-data="pgtrekDashboard({
            start: '{{ now()->subDays(6)->format('Y-m-d') }}',
            end: '{{ now()->format('Y-m-d') }}',
            vendor: '',
            site: ''
        })" x-init="load()" @click="closeAllPanels()">

        {{-- ── Page Header ──────────────────────────────────────────────────── --}}
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Dashboard PGTrek
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Security Tracking Performance Report
                </p>
            </div>

            <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:flex-wrap sm:items-center">

            {{-- ── Filter Pill (styled after the Dashboard GM date filter) ────── --}}
            <div class="flex flex-1 flex-col rounded-2xl border border-slate-200 bg-white shadow-sm sm:flex-none sm:flex-row dark:border-slate-700/60 dark:bg-slate-900">

                <div class="flex items-center divide-x divide-slate-200 border-b border-slate-200 sm:border-b-0 dark:divide-slate-700/60 dark:border-slate-700/60">

                    {{-- Vendor --}}
                    <div class="flex items-center gap-1.5 px-3 py-2">
                        <svg class="h-3.5 w-3.5 shrink-0 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <select x-model="filters.vendor"
                            class="cursor-pointer appearance-none border-0 bg-transparent text-xs font-semibold text-slate-700 outline-none dark:text-slate-200">
                            <option value="">All Vendors</option>
                            @foreach ($vendors as $v)
                                <option value="{{ $v }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Location --}}
                    <div class="flex items-center gap-1.5 px-3 py-2">
                        <svg class="h-3.5 w-3.5 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s6-5.25 6-10a6 6 0 10-12 0c0 4.75 6 10 6 10z M12 9a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                        <select x-model="filters.site"
                            class="cursor-pointer appearance-none border-0 bg-transparent text-xs font-semibold text-slate-700 outline-none dark:text-slate-200">
                            <option value="">All Locations</option>
                            @foreach ($sites as $s)
                                <option value="{{ $s->site_code }}">{{ $s->site_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date --}}
                    <div class="relative" @click.stop>
                        <button type="button" @click="dateOpen = !dateOpen"
                            class="flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800/50"
                            style="white-space:nowrap">
                            <svg class="h-3.5 w-3.5 shrink-0 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span x-text="dateLabel"></span>
                            <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="dateOpen" x-cloak
                            class="absolute right-0 top-full z-50 mt-1.5 w-72 max-w-[calc(100vw-1rem)] rounded-2xl border border-slate-200 bg-white p-4 shadow-xl dark:border-slate-700/60 dark:bg-slate-900">

                            <p class="mb-2.5 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Quick Select</p>
                            <div class="grid grid-cols-2 gap-1.5">
                                <template x-for="preset in presets" :key="preset.id">
                                    <button type="button" @click="applyPreset(preset.id)"
                                        class="rounded-xl border px-3 py-2 text-left text-xs font-semibold transition hover:border-violet-400 hover:bg-violet-50 hover:text-violet-700 dark:hover:bg-violet-500/10"
                                        :class="preset.id === activePreset
                                            ? 'border-violet-500 bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300'
                                            : 'border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300'"
                                        x-text="preset.label"></button>
                                </template>
                            </div>

                            <div class="mt-3 border-t border-slate-100 pt-3 dark:border-slate-700/60">
                                <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Custom Range</p>
                                <div class="flex flex-col gap-2">
                                    <div>
                                        <p class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">From</p>
                                        <input type="date" x-model="filters.start"
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs font-semibold text-slate-700 outline-none focus:border-violet-400 focus:ring-1 focus:ring-violet-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                    </div>
                                    <div>
                                        <p class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">To</p>
                                        <input type="date" x-model="filters.end"
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs font-semibold text-slate-700 outline-none focus:border-violet-400 focus:ring-1 focus:ring-violet-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                    </div>
                                </div>
                                <div class="mt-2 flex gap-2">
                                    <button type="button" @click="applyPreset('last-7-days')"
                                        class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-600 transition hover:border-slate-300 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                                        Clear
                                    </button>
                                    <button type="button" @click="activePreset = 'custom'; dateOpen = false; load()"
                                        class="flex-1 rounded-xl bg-violet-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-violet-700">
                                        Apply
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Refresh --}}
                <button type="button" @click="load()"
                    class="flex items-center gap-1.5 border-t border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 disabled:opacity-50 sm:border-l sm:border-t-0 dark:border-slate-700/60 dark:text-slate-300 dark:hover:bg-slate-800/50"
                    :disabled="loading">
                    <svg class="h-3.5 w-3.5" :class="{ 'animate-spin': loading }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span x-text="loading ? 'Loading…' : 'Refresh'"></span>
                </button>
            </div>

            {{-- Export PDF --}}
            <a :href="exportPdfUrl" target="_blank"
                class="flex items-center justify-center gap-1.5 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 dark:border-slate-700/60 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/50">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export PDF
            </a>

            </div>
        </div>

        <template x-if="error">
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400"
                x-text="error"></div>
        </template>

        {{-- ══════════════════════════════════════════════════════════════════
             REPORT SHEET — styled after the Security Tracking Performance
             Report Excel template ("SPOTILY SECURITY 3.1.xlsx"). Bobot,
             the 10% beacon buffer, and per-aspect Agreement Points are
             hardcoded to match that workbook's formulas (see `scoring`
             getter below and PgTrekDashboardController::computeScoring).
             ══════════════════════════════════════════════════════════════════ --}}
        <div class="flex flex-col gap-4 lg:flex-row">

            {{-- RESULT / PERFORMANCE --}}
            <div class="flex shrink-0 flex-col gap-4 lg:w-56">
                <div class="rounded-xl border-2 border-slate-300 bg-white p-4 text-center dark:border-slate-600 dark:bg-slate-900">
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-200">RESULT</p>
                    <p class="mt-1 text-4xl font-extrabold text-slate-900 dark:text-white" x-text="(scoring.result ?? '—') + '%'"></p>
                </div>
                <div class="rounded-xl border-2 border-slate-300 bg-white p-4 text-center dark:border-slate-600 dark:bg-slate-900">
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-200">PERFORMANCE</p>
                    <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-100" x-text="scoring.performance ?? '—'"></p>
                </div>

                {{-- Performance Rating Scale (from "Summary Vendor Report") --}}
                <div class="overflow-hidden rounded-xl border-2 border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-900">
                    <p class="border-b-2 border-slate-300 bg-slate-50 px-3 py-1.5 text-center text-xs font-bold text-slate-600 dark:border-slate-600 dark:bg-slate-800/50 dark:text-slate-300">Performance Rating Scale</p>
                    <table class="w-full text-xs">
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <template x-for="tier in performanceTiers" :key="tier.label">
                                <tr :class="scoring.performance === tier.label ? 'bg-violet-50 dark:bg-violet-500/10' : ''">
                                    <td class="px-3 py-1.5 text-slate-600 dark:text-slate-300" x-text="tier.label"></td>
                                    <td class="whitespace-nowrap px-3 py-1.5 text-right font-semibold text-slate-700 dark:text-slate-200" x-text="tier.range"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="min-w-0 flex-1 overflow-hidden rounded-xl border-2 border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-900">

            {{-- Title bar --}}
            <div class="border-b-2 border-slate-300 bg-white px-5 py-3 dark:border-slate-600 dark:bg-slate-900">
                <h2 class="text-lg font-extrabold text-slate-800 dark:text-white">Security Tracking Performance Report</h2>
            </div>

            {{-- Period / Vendor / Location --}}
            <table class="w-full text-sm">
                <tbody>
                    <tr class="border-b border-slate-200 dark:border-slate-700">
                        <td class="w-40 border-r border-slate-200 bg-slate-50 px-4 py-1.5 font-semibold text-slate-500 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-400">Period</td>
                        <td class="px-4 py-1.5 text-slate-700 dark:text-slate-200" x-text="dateLabel + ' (' + filters.start + ' – ' + filters.end + ')'"></td>
                    </tr>
                    <tr class="border-b border-slate-200 dark:border-slate-700">
                        <td class="w-40 border-r border-slate-200 bg-slate-50 px-4 py-1.5 font-semibold text-slate-500 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-400">Vendor</td>
                        <td class="px-4 py-1.5 text-slate-700 dark:text-slate-200" x-text="filters.vendor || 'All Vendors'"></td>
                    </tr>
                    <tr>
                        <td class="w-40 border-r border-slate-200 bg-slate-50 px-4 py-1.5 font-semibold text-slate-500 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-400">Location</td>
                        <td class="px-4 py-1.5 text-slate-700 dark:text-slate-200" x-text="siteLabel"></td>
                    </tr>
                </tbody>
            </table>

            {{-- Personnel --}}
            <table class="w-full border-t-2 border-slate-300 text-sm dark:border-slate-600">
                <thead>
                    <tr>
                        <th colspan="2" class="border-b border-r border-slate-200 bg-violet-100 px-4 py-2 text-center font-bold text-slate-700 dark:border-slate-700 dark:bg-violet-500/15 dark:text-slate-200">Personel</th>
                        <th class="border-b border-slate-200 bg-violet-100 px-4 py-2 text-center font-bold text-slate-700 dark:border-slate-700 dark:bg-violet-500/15 dark:text-slate-200">Total Beacon User</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-slate-200 dark:border-slate-700">
                        <td class="border-r border-slate-200 px-4 py-2 text-slate-600 dark:border-slate-700 dark:text-slate-300">Duty Officer (Active Track User)</td>
                        <td class="w-24 border-r border-slate-200 px-4 py-2 text-center font-bold text-slate-800 dark:border-slate-700 dark:text-white" x-text="personnel.active_track_user ?? '—'"></td>
                        <td rowspan="2" class="w-40 px-4 py-2 text-center text-2xl font-extrabold text-slate-800 dark:text-white" x-text="personnel.total_beacon_user ?? '—'"></td>
                    </tr>
                    <tr>
                        <td class="border-r border-slate-200 px-4 py-2 text-slate-600 dark:border-slate-700 dark:text-slate-300">Substitute (Non Track User)</td>
                        <td class="border-r border-slate-200 px-4 py-2 text-center font-bold text-slate-800 dark:border-slate-700 dark:text-white" x-text="personnel.non_track_user ?? '—'"></td>
                    </tr>
                </tbody>
            </table>

            {{-- Absent Discipline --}}
            <table class="w-full border-t-2 border-slate-300 text-sm dark:border-slate-600">
                <thead>
                    <tr>
                        <th colspan="2" class="border-b border-r border-slate-200 bg-violet-100 px-4 py-2 text-center font-bold text-slate-700 dark:border-slate-700 dark:bg-violet-500/15 dark:text-slate-200">Absent Discipline</th>
                        <th class="w-28 border-b border-r border-slate-200 bg-violet-100 px-4 py-2 text-center font-bold text-slate-700 dark:border-slate-700 dark:bg-violet-500/15 dark:text-slate-200">Target</th>
                        <th class="w-28 border-b border-r border-slate-200 bg-violet-100 px-4 py-2 text-center font-bold text-slate-700 dark:border-slate-700 dark:bg-violet-500/15 dark:text-slate-200">Actual</th>
                        <th class="w-20 border-b border-slate-200 bg-violet-100 px-4 py-2 text-center font-bold text-slate-700 dark:border-slate-700 dark:bg-violet-500/15 dark:text-slate-200">%</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-slate-200 dark:border-slate-700">
                        <td class="w-10 border-r border-slate-200 px-3 py-2 text-center text-slate-400 dark:border-slate-700">1</td>
                        <td class="border-r border-slate-200 px-4 py-2 text-slate-600 dark:border-slate-700 dark:text-slate-300">Work Days</td>
                        <td class="border-r border-slate-200 px-4 py-2 text-right text-slate-700 dark:border-slate-700 dark:text-slate-300" x-text="absentDiscipline.work_days_target ?? '—'"></td>
                        <td class="border-r border-slate-200 px-4 py-2 text-right text-slate-700 dark:border-slate-700 dark:text-slate-300" x-text="absentDiscipline.work_days_actual ?? '—'"></td>
                        <td rowspan="2" class="bg-violet-50 px-4 py-2 text-center font-bold text-violet-700 dark:bg-violet-500/10 dark:text-violet-300"
                            x-text="absentDiscipline.work_days_pct !== null ? absentDiscipline.work_days_pct + '%' : '—'"></td>
                    </tr>
                    <tr>
                        <td class="border-r border-slate-200 px-3 py-2 text-center text-slate-400 dark:border-slate-700">2</td>
                        <td class="border-r border-slate-200 px-4 py-2 text-slate-400 dark:border-slate-700 dark:text-slate-500">
                            Substitute Used
                            <span class="ml-1 text-[10px] italic text-slate-400 dark:text-slate-500">(no reliable source found)</span>
                        </td>
                        <td class="border-r border-slate-200 px-4 py-2 text-right text-slate-300 dark:border-slate-700 dark:text-slate-600">—</td>
                        <td class="border-r border-slate-200 px-4 py-2 text-right text-slate-300 dark:border-slate-700 dark:text-slate-600">—</td>
                    </tr>
                </tbody>
            </table>

            {{-- Beacon Tracking Performance --}}
            <table class="w-full border-t-2 border-slate-300 text-sm dark:border-slate-600">
                <thead>
                    <tr>
                        <th colspan="3" class="border-b border-r border-slate-200 bg-violet-100 px-4 py-2 text-center font-bold text-slate-700 dark:border-slate-700 dark:bg-violet-500/15 dark:text-slate-200">Beacon Tracking Performance</th>
                        <th class="w-28 border-b border-r border-slate-200 bg-violet-100 px-4 py-2 text-center font-bold text-slate-700 dark:border-slate-700 dark:bg-violet-500/15 dark:text-slate-200">Target</th>
                        <th class="w-28 border-b border-r border-slate-200 bg-violet-100 px-4 py-2 text-center font-bold text-slate-700 dark:border-slate-700 dark:bg-violet-500/15 dark:text-slate-200">Actual</th>
                        <th class="w-20 border-b border-slate-200 bg-violet-100 px-4 py-2 text-center font-bold text-slate-700 dark:border-slate-700 dark:bg-violet-500/15 dark:text-slate-200">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    {{-- Group 1: Track Point Activities Completion --}}
                    <template x-for="(row, i) in pointCompletion.rows" :key="'pt-' + row.activity">
                        <tr>
                            <template x-if="i === 0">
                                <td class="w-10 border-r border-slate-200 px-3 py-2 text-center align-top text-slate-400 dark:border-slate-700" :rowspan="pointCompletion.rows.length">1</td>
                            </template>
                            <template x-if="i === 0">
                                <td class="w-40 border-r border-slate-200 px-4 py-2 align-top font-semibold text-slate-600 dark:border-slate-700 dark:text-slate-300" :rowspan="pointCompletion.rows.length">Track Point Activities Completion</td>
                            </template>
                            <td class="border-r border-slate-200 px-4 py-2 text-slate-600 dark:border-slate-700 dark:text-slate-300" x-text="'Track - ' + row.activity"></td>
                            <td class="border-r border-slate-200 px-4 py-2 text-right text-slate-700 dark:border-slate-700 dark:text-slate-300" x-text="row.target"></td>
                            <td class="border-r border-slate-200 px-4 py-2 text-right text-slate-700 dark:border-slate-700 dark:text-slate-300" x-text="row.actual"></td>
                            <template x-if="i === 0">
                                <td class="bg-violet-50 px-4 py-2 text-center align-top font-bold text-violet-700 dark:bg-violet-500/10 dark:text-violet-300" :rowspan="pointCompletion.rows.length"
                                    x-text="pointCompletion.overall_pct !== null ? pointCompletion.overall_pct + '%' : '—'"></td>
                            </template>
                        </tr>
                    </template>
                    <template x-if="!loading && pointCompletion.rows.length === 0">
                        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">No data available</td></tr>
                    </template>

                    {{-- Group 2: Track Time Implement --}}
                    <template x-for="(row, i) in timeImplement.rows" :key="'tm-' + row.activity">
                        <tr>
                            <template x-if="i === 0">
                                <td class="w-10 border-r border-slate-200 px-3 py-2 text-center align-top text-slate-400 dark:border-slate-700" :rowspan="timeImplement.rows.length">2</td>
                            </template>
                            <template x-if="i === 0">
                                <td class="w-40 border-r border-slate-200 px-4 py-2 align-top font-semibold text-slate-600 dark:border-slate-700 dark:text-slate-300" :rowspan="timeImplement.rows.length">Track Time Implement (Minutes)</td>
                            </template>
                            <td class="border-r border-slate-200 px-4 py-2 text-slate-600 dark:border-slate-700 dark:text-slate-300" x-text="'Time - ' + row.activity"></td>
                            <td class="border-r border-slate-200 px-4 py-2 text-right text-slate-700 dark:border-slate-700 dark:text-slate-300" x-text="row.target_minutes.toLocaleString() + ' min'"></td>
                            <td class="border-r border-slate-200 px-4 py-2 text-right text-slate-700 dark:border-slate-700 dark:text-slate-300" x-text="row.actual_minutes.toLocaleString() + ' min'"></td>
                            <template x-if="i === 0">
                                <td class="bg-violet-50 px-4 py-2 text-center align-top font-bold text-violet-700 dark:bg-violet-500/10 dark:text-violet-300" :rowspan="timeImplement.rows.length"
                                    x-text="timeImplement.overall_pct !== null ? timeImplement.overall_pct + '%' : '—'"></td>
                            </template>
                        </tr>
                    </template>
                    <template x-if="!loading && timeImplement.rows.length === 0">
                        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">No data available</td></tr>
                    </template>
                </tbody>
            </table>

            {{-- Alert Point --}}
            <table class="w-full border-t-2 border-slate-300 text-sm dark:border-slate-600">
                <thead>
                    <tr>
                        <th colspan="3" class="border-b border-r border-slate-200 bg-violet-100 px-4 py-2 text-center font-bold text-slate-700 dark:border-slate-700 dark:bg-violet-500/15 dark:text-slate-200">Alert Point</th>
                        <th class="w-16 border-b border-r border-slate-200 bg-violet-100 px-4 py-2 text-center font-bold text-slate-700 dark:border-slate-700 dark:bg-violet-500/15 dark:text-slate-200">Qty</th>
                        <th class="w-20 border-b border-slate-200 bg-violet-100 px-4 py-2 text-center font-bold text-slate-700 dark:border-slate-700 dark:bg-violet-500/15 dark:text-slate-200">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <template x-for="(row, i) in alertRows" :key="row.aspect + '-' + row.reason + '-' + i">
                        <tr>
                            <template x-if="row.isFirst">
                                <td class="w-10 border-r border-slate-200 px-3 py-2 text-center align-top text-slate-400 dark:border-slate-700" :rowspan="row.groupSpan" x-text="row.groupIndex"></td>
                            </template>
                            <template x-if="row.isFirst">
                                <td class="w-44 border-r border-slate-200 px-4 py-2 align-top font-semibold uppercase text-slate-600 dark:border-slate-700 dark:text-slate-300" :rowspan="row.groupSpan" x-text="row.aspect"></td>
                            </template>
                            <td class="border-r border-slate-200 px-4 py-2 text-slate-600 dark:border-slate-700 dark:text-slate-300" x-text="row.reason"></td>
                            <td class="border-r border-slate-200 px-4 py-2 text-right text-slate-700 dark:border-slate-700 dark:text-slate-300" x-text="row.qty"></td>
                            <template x-if="row.isFirst">
                                <td class="bg-violet-50 px-4 py-2 text-center align-top font-bold text-violet-700 dark:bg-violet-500/10 dark:text-violet-300" :rowspan="row.groupSpan" x-text="row.groupQty"></td>
                            </template>
                        </tr>
                    </template>
                    <template x-if="!loading && alertRows.length === 0">
                        <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">No data available</td></tr>
                    </template>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="border-t-2 border-slate-300 bg-slate-50 px-4 py-2 text-right font-bold uppercase text-slate-600 dark:border-slate-600 dark:bg-slate-800/50 dark:text-slate-300">Alert Raised</td>
                        <td class="border-t-2 border-slate-300 bg-slate-50 px-4 py-2 text-center font-bold text-slate-800 dark:border-slate-600 dark:bg-slate-800/50 dark:text-white" x-text="alertPoint.total_raised ?? 0"></td>
                    </tr>
                </tfoot>
            </table>

            {{-- Scoring Breakdown (Bobot / contribution) --}}
            <table class="w-full border-t-2 border-slate-300 text-sm dark:border-slate-600">
                <thead>
                    <tr>
                        <th class="border-b border-r border-slate-200 bg-slate-100 px-4 py-2 text-left font-bold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">Scoring Breakdown</th>
                        <th class="w-20 border-b border-r border-slate-200 bg-slate-100 px-4 py-2 text-center font-bold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">%</th>
                        <th class="w-20 border-b border-r border-slate-200 bg-slate-100 px-4 py-2 text-center font-bold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">Bobot</th>
                        <th class="w-24 border-b border-slate-200 bg-slate-100 px-4 py-2 text-center font-bold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">Contribution</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <template x-for="s in scoring.sections" :key="s.label">
                        <tr>
                            <td class="border-r border-slate-200 px-4 py-2 text-slate-600 dark:border-slate-700 dark:text-slate-300" x-text="s.label"></td>
                            <td class="border-r border-slate-200 px-4 py-2 text-center text-slate-700 dark:border-slate-700 dark:text-slate-300" x-text="s.pct + '%'"></td>
                            <td class="border-r border-slate-200 px-4 py-2 text-center text-slate-700 dark:border-slate-700 dark:text-slate-300" x-text="s.bobot + '%'"></td>
                            <td class="px-4 py-2 text-center font-semibold text-slate-800 dark:text-white" x-text="s.contribution + '%'"></td>
                        </tr>
                    </template>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="border-t-2 border-slate-300 bg-slate-50 px-4 py-2 text-right font-bold uppercase text-slate-600 dark:border-slate-600 dark:bg-slate-800/50 dark:text-slate-300">Result</td>
                        <td class="border-t-2 border-slate-300 bg-slate-50 px-4 py-2 text-center font-bold text-slate-800 dark:border-slate-600 dark:bg-slate-800/50 dark:text-white" x-text="(scoring.result ?? '—') + '%'"></td>
                    </tr>
                </tfoot>
            </table>
            </div>

        </div>

    </div>

    <script>
        function pgtrekDashboard(initial) {
            return {
                filters: {
                    start: initial.start,
                    end: initial.end,
                    vendor: initial.vendor,
                    site: initial.site,
                },
                loading: false,
                error: null,
                dateOpen: false,
                activePreset: 'last-7-days',
                pointCompletion: { rows: [], overall_pct: null },
                timeImplement: { rows: [], overall_pct: null },
                personnel: { active_track_user: null, non_track_user: null, total_beacon_user: null },
                absentDiscipline: { work_days_target: null, work_days_actual: null, work_days_pct: null },
                alertPoint: { groups: [], total_raised: 0 },
                alertRows: [],

                performanceTiers: [
                    { label: 'Fully Compliant with Standard', range: '>90%' },
                    { label: 'Substantially Compliant with Standard', range: '80% - 90%' },
                    { label: 'Moderately Compliant with Standard', range: '70% - 80%' },
                    { label: 'Minimum Standard Performance', range: '40% - 70%' },
                    { label: 'Below Standard Performance', range: '25% - 40%' },
                    { label: 'Unacceptable Performance', range: '<25%' },
                ],

                // Mirrors PgTrekDashboardController::computeScoring() — Bobot and
                // Agreement Points are the fixed constants from the Excel workbook.
                // pointCompletion/timeImplement overall_pct is already buffer-adjusted server-side.
                get scoring() {
                    const BOBOT = { absent: 10, point: 30, time: 30, alert: 30 };
                    const AGREEMENT_POINTS = { 'Supportive Alert': 0.2, 'Technical Alert': 0.2, 'Neutral Alert': 0.2, 'Negligence Alert': -5 };

                    const absentPct = this.absentDiscipline.work_days_pct ?? 0;
                    const pointPct = this.pointCompletion.overall_pct ?? 0;
                    const timePct = this.timeImplement.overall_pct ?? 0;

                    let alertContribution = 0;
                    (this.alertPoint.groups || []).forEach(g => {
                        alertContribution += g.qty * (AGREEMENT_POINTS[g.aspect] ?? 0);
                    });
                    const alertPct = Math.min(100, 100 + alertContribution);

                    const sections = [
                        { label: 'Absent Dicipline', pct: absentPct, bobot: BOBOT.absent },
                        { label: 'Beacon Tracking Performance - Track Point Activities Completion', pct: pointPct, bobot: BOBOT.point },
                        { label: 'Beacon Tracking Performance - Track Time Implement (Minutes)', pct: timePct, bobot: BOBOT.time },
                        { label: 'Alert Point', pct: alertPct, bobot: BOBOT.alert },
                    ].map(s => ({ ...s, contribution: Math.round(s.pct * s.bobot) / 100 }));

                    const result = Math.round(sections.reduce((sum, s) => sum + s.contribution, 0) * 100) / 100;

                    let performance = 'Fully Compliant with Standard';
                    if (result <= 25) performance = 'Unacceptable Performance';
                    else if (result <= 40) performance = 'Below Standard Performance';
                    else if (result <= 70) performance = 'Minimum Standard Performance';
                    else if (result <= 80) performance = 'Moderately Compliant with Standard';
                    else if (result <= 90) performance = 'Substantially Compliant with Standard';

                    return { sections, result, performance };
                },

                presets: [
                    { id: 'today', label: 'Today' },
                    { id: 'this-week', label: 'This Week' },
                    { id: 'last-7-days', label: 'Last 7 Days' },
                    { id: 'this-month', label: 'This Month' },
                    { id: 'last-month', label: 'Last Month' },
                    { id: 'this-year', label: 'This Year' },
                ],

                get dateLabel() {
                    const found = this.presets.find(p => p.id === this.activePreset);
                    if (found) return found.label;
                    return this.filters.start === this.filters.end
                        ? this.filters.start
                        : this.filters.start + ' – ' + this.filters.end;
                },

                get siteLabel() {
                    if (!this.filters.site) return 'All Locations';
                    const opt = document.querySelector(`option[value="${this.filters.site}"]`);
                    return opt ? opt.textContent : this.filters.site;
                },

                get exportPdfUrl() {
                    const params = new URLSearchParams({
                        start: this.filters.start,
                        end: this.filters.end,
                        vendor: this.filters.vendor,
                        site: this.filters.site,
                    });
                    return `{{ route('pgtrek.export.pdf') }}?${params.toString()}`;
                },

                closeAllPanels() {
                    this.dateOpen = false;
                },

                fmt(d) {
                    return d.toISOString().slice(0, 10);
                },

                applyPreset(id) {
                    const now = new Date();
                    let start = new Date(now);
                    let end = new Date(now);

                    switch (id) {
                        case 'today':
                            break;
                        case 'this-week': {
                            const dow = (now.getDay() + 6) % 7;
                            start.setDate(now.getDate() - dow);
                            end = new Date(start);
                            end.setDate(start.getDate() + 6);
                            break;
                        }
                        case 'last-7-days':
                            start.setDate(now.getDate() - 6);
                            break;
                        case 'this-month':
                            start = new Date(now.getFullYear(), now.getMonth(), 1);
                            end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                            break;
                        case 'last-month':
                            start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                            end = new Date(now.getFullYear(), now.getMonth(), 0);
                            break;
                        case 'this-year':
                            start = new Date(now.getFullYear(), 0, 1);
                            end = new Date(now.getFullYear(), 11, 31);
                            break;
                    }

                    this.filters.start = this.fmt(start);
                    this.filters.end = this.fmt(end);
                    this.activePreset = id;
                    this.dateOpen = false;
                    this.load();
                },

                async load() {
                    this.loading = true;
                    this.error = null;

                    const params = new URLSearchParams({
                        start: this.filters.start,
                        end: this.filters.end,
                        vendor: this.filters.vendor,
                        site: this.filters.site,
                    });

                    try {
                        const headers = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

                        const [res, summaryRes] = await Promise.all([
                            fetch(`{{ route('pgtrek.beacon-performance') }}?${params.toString()}`, { headers }),
                            fetch(`{{ route('pgtrek.report-summary') }}?${params.toString()}`, { headers }),
                        ]);

                        if (!res.ok) {
                            throw new Error('Failed to load report (HTTP ' + res.status + ')');
                        }
                        if (!summaryRes.ok) {
                            throw new Error('Failed to load report summary (HTTP ' + summaryRes.status + ')');
                        }

                        const json = await res.json();
                        this.pointCompletion = json.data.point_completion;
                        this.timeImplement = json.data.time_implement;

                        const summaryJson = await summaryRes.json();
                        this.personnel = summaryJson.data.personnel;
                        this.absentDiscipline = summaryJson.data.absent_discipline;
                        this.alertPoint = summaryJson.data.alert_point;

                        this.alertRows = [];
                        (this.alertPoint.groups || []).forEach((g, gi) => {
                            const reasons = g.reasons.length ? g.reasons : [{ reason: '—', qty: 0 }];
                            reasons.forEach((r, ri) => {
                                this.alertRows.push({
                                    groupIndex: gi + 1,
                                    aspect: g.aspect,
                                    groupQty: g.qty,
                                    groupSpan: reasons.length,
                                    isFirst: ri === 0,
                                    reason: r.reason,
                                    qty: r.qty,
                                });
                            });
                        });
                    } catch (e) {
                        this.error = e.message || 'Failed to load report.';
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }
    </script>
</x-app-layout>
