<x-app-layout>

    <div class="max-w-9xl mx-auto w-full space-y-3 p-2">

        {{-- ── Page Header ──────────────────────────────────────────────────────── --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <h1 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                RECRUITMENT DASHBOARD
            </h1>
        </div>

        {{-- ── Filter Bar ───────────────────────────────────────────────────────── --}}
        <form method="GET" action="{{ route('recruitment.dashboard') }}"
              class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700/60 dark:bg-slate-900">

            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Applied From</label>
                <input type="date" name="from" value="{{ $filters['from'] }}"
                       class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100" />
            </div>

            <div>
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Applied To</label>
                <input type="date" name="to" value="{{ $filters['to'] }}"
                       class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100" />
            </div>

            <div class="min-w-55">
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Department</label>
                <select id="filterDepartment" name="department" class="w-full">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->department_id }}" @selected($filters['department'] === $dept->department_id)>
                            {{ $dept->department_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-55">
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Company</label>
                <select id="filterCompany" name="company" class="w-full">
                    <option value="">All Companies</option>
                    @foreach($companies as $cpny)
                        <option value="{{ $cpny->cpnyid }}" @selected($filters['company'] === $cpny->cpnyid)>
                            {{ $cpny->cpnyname }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-48">
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Location</label>
                <select id="filterLocation" name="location" class="w-full">
                    <option value="">All Locations</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc }}" @selected($filters['location'] === $loc)>{{ $loc }}</option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-48">
                <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Application Type</label>
                <select id="filterSource" name="source" class="w-full">
                    <option value="">All Applicants</option>
                    <option value="career" @selected($filters['source'] === 'career')>Job Applicant</option>
                    <option value="self" @selected($filters['source'] === 'self')>Self Applicant</option>
                </select>
            </div>

            <button type="submit"
                    class="rounded-lg bg-violet-600 px-4 py-1.5 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700">
                Apply Filter
            </button>

            @if($filters['from'] || $filters['to'] || $filters['department'] || $filters['company'] || $filters['location'] || $filters['source'])
                <a href="{{ route('recruitment.dashboard') }}"
                   class="rounded-lg border border-slate-300 px-4 py-1.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800">
                    Reset
                </a>
            @endif

        </form>

        <style>
            .select2-container .select2-selection--single {
                height: 34px !important;
                border-radius: 0.5rem !important;
                border-color: #cbd5e1 !important;
                display: flex;
                align-items: center;
            }
            .select2-container .select2-selection--single .select2-selection__rendered {
                line-height: normal !important;
                color: #334155;
                padding-left: 0.75rem !important;
            }
            .select2-container .select2-selection--single .select2-selection__arrow {
                height: 32px !important;
            }
            .dark .select2-container .select2-selection--single {
                background-color: #1e293b !important;
                border-color: #475569 !important;
            }
            .dark .select2-container .select2-selection--single .select2-selection__rendered {
                color: #f1f5f9 !important;
            }
            .dark .select2-selection__arrow b {
                border-color: #94a3b8 transparent transparent transparent !important;
            }
            .dark .select2-dropdown {
                background-color: #1e293b !important;
                border-color: #475569 !important;
            }
            .dark .select2-search__field {
                background-color: #0f172a !important;
                color: #f1f5f9 !important;
                border-color: #475569 !important;
            }
            .dark .select2-results__option {
                color: #e2e8f0 !important;
            }
            .dark .select2-container--default .select2-results__option--highlighted[aria-selected] {
                background-color: #7c3aed !important;
                color: #fff !important;
            }
            .dark .select2-container--default .select2-results__option[aria-selected=true] {
                background-color: #334155 !important;
            }
        </style>

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    $('#filterDepartment').select2({ placeholder: 'All Departments', allowClear: true, width: '100%' });
                    $('#filterCompany').select2({ placeholder: 'All Companies', allowClear: true, width: '100%' });
                    $('#filterLocation').select2({ placeholder: 'All Locations', allowClear: true, width: '100%' });
                    $('#filterSource').select2({ placeholder: 'All Applicants', minimumResultsForSearch: -1, width: '100%' });
                });
            </script>
        @endpush

        {{-- ── KPI Strip ────────────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">

            <x-card-chart.stat-card
                subtitle="Applicants" title="Self Applicant"
                value="{{ number_format($totalSelfApplicant) }}"
                description="via self-registration, duplicates excluded"
                color="cyan"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z" /></svg>' />

            <x-card-chart.stat-card
                subtitle="Pipeline" title="Total Applied"
                value="{{ number_format($totalApplied) }}"
                description="job applications submitted"
                color="blue"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" /></svg>' />

            <x-card-chart.stat-card
                subtitle="Pipeline" title="Total Rejected"
                value="{{ number_format($totalRejected) }}"
                description="{{ $totalApplied > 0 ? round($totalRejected / $totalApplied * 100) . '% of applied' : '—' }}"
                color="red"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>' />

            <x-card-chart.stat-card
                subtitle="Pipeline" title="Avg. Time to Hire"
                value="{{ $avgTimeToHire !== null ? $avgTimeToHire . ' days' : '—' }}"
                description="apply date to joined date"
                color="violet"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />

            <x-card-chart.stat-card
                subtitle="PRF" title="Total PRF"
                value="{{ number_format($totalPrf) }}"
                description="approved requisitions"
                color="violet"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>' />

            <x-card-chart.stat-card
                subtitle="PRF" title="PRF → Posted"
                value="{{ number_format($totalPrfPosted) }}"
                description="of {{ number_format($totalPrf) }} approved PRF"
                color="green"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />

            <x-card-chart.stat-card
                subtitle="PRF" title="PRF → Unposted"
                value="{{ number_format($totalPrfUnposted) }}"
                description="of {{ number_format($totalPrf) }} approved PRF"
                color="orange"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>' />

            <x-card-chart.stat-card
                subtitle="Job Posting" title="Posted"
                value="{{ number_format($postedCount) }}"
                description="of {{ number_format($totalJobposting) }} total postings"
                color="green"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />

            <x-card-chart.stat-card
                subtitle="Job Posting" title="Unposted"
                value="{{ number_format($unpostedCount) }}"
                description="of {{ number_format($totalJobposting) }} total postings"
                color="orange"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>' />

        </div>

        {{-- ── Row 2: Gender · Age · Candidates by Posting Status ──────────────── --}}
        {{-- Scoped to candidates who actually applied (career + self-register), not every raw applicant record. --}}
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-3" style="align-items:stretch">

            <x-card-chart.donut-chart
                subtitle="Candidates" title="By Gender"
                color="pink" :labels="$genderLabels" :series="$genderSeries" />

            <x-card-chart.bar-chart
                subtitle="Candidates" title="By Age"
                color="cyan" :categories="$ageLabels"
                :series="[['name' => 'Candidates', 'data' => $ageSeries]]" />

            <x-card-chart.donut-chart
                subtitle="Candidates" title="By Posting Status"
                color="blue" :labels="$jobpostingLabels" :series="$jobpostingSeries" />

        </div>

        {{-- ── Row 3: Education Level · Job Level ───────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2" style="align-items:stretch">

            <x-card-chart.donut-chart
                subtitle="Candidates" title="By Education Level"
                color="green" :labels="$educationLabels" :series="$educationSeries" />

            <x-card-chart.donut-chart
                subtitle="Career Applicants" title="By Job Level"
                color="orange" :labels="$jobLevelLabels" :series="$jobLevelSeries" />

        </div>

        {{-- ── Row 4: Applicants by Department · Top Job Postings ──────────────── --}}
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2" style="align-items:stretch">

            <x-card-chart.bar-chart
                subtitle="Candidates" title="By Division (Top 10)"
                color="pink" :categories="$divisionLabels"
                :series="[['name' => 'Applicants', 'data' => $divisionSeries]]" />

            <x-card-chart.bar-chart
                subtitle="Job Posting" title="Top Postings by Applicants (Top 10)"
                color="orange" :categories="$topPostingLabels"
                :series="[['name' => 'Applicants', 'data' => $topPostingSeries]]" />

        </div>

        {{-- ── Row 5: Applications Over Time ────────────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-3">

            <x-card-chart.area-chart
                subtitle="Trend" title="Applications Over Time"
                color="blue" height="260"
                :categories="$trendLabels"
                :series="[['name' => 'Applications', 'data' => $trendSeries]]" />

        </div>

        {{-- ── Row 6: Recruitment Funnel ────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-3">

            <x-card-chart.bar-chart
                subtitle="Pipeline Conversion" title="Recruitment Funnel"
                color="violet" height="260"
                :categories="collect($funnelSeries)->pluck('x')->all()"
                :series="[['name' => 'Applicants', 'data' => collect($funnelSeries)->pluck('y')->all()]]" />

        </div>

    </div>

</x-app-layout>
