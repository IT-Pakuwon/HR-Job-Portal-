<x-app-layout>

    <div class="max-w-9xl mx-auto w-full space-y-3 p-2 overflow-x-hidden">

        {{-- Page Header --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <h1 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                RECRUITMENT DASHBOARD
                @if($applicantType === 'self')
                    <span class="ml-2 text-sm font-semibold text-cyan-600 dark:text-cyan-400">· Self Applicant View</span>
                @elseif($applicantType === 'career')
                    <span class="ml-2 text-sm font-semibold text-violet-600 dark:text-violet-400">· Job Applicant View</span>
                @else
                    <span class="ml-2 text-sm font-semibold text-slate-500 dark:text-slate-400">· All Sources</span>
                @endif
            </h1>
        </div>

        {{-- Filter Bar — pill-style segmented bar matching GM aesthetic --}}
        <x-dashboard-filter.dashboard-filter
            :companyGroups="$companyGroups"
            :areas="$areas"
            :isGroupLocked="$isGroupLocked"
            :userGroupCpny="$userGroupCpny"
            :currentFilters="$filters"
            :departments="$departments"
            :companies="$companies"
            :locations="$locations" />

        {{-- ═════════════════════════════════════════════════════════════════════
             ROW 1 — Requisition & Job Status strip: 5 cards
            ═════════════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">

            <x-card-chart.stat-card
                subtitle="Requisition" title="Total Created PRF"
                value="{{ number_format($totalPrf) }}"
                description="status C · approved requisitions"
                color="violet"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>' />

            <x-card-chart.stat-card
                subtitle="Job Posting" title="Unposted Job"
                value="{{ number_format($unpostedCount) }}"
                description="approved, awaiting publish"
                color="orange"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>' />

            <x-card-chart.stat-card
                subtitle="Job Posting" title="Posted Job"
                value="{{ number_format($postedCount) }}"
                description="live on career site"
                color="blue"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586" /></svg>' />

            <x-card-chart.stat-card
                subtitle="Job Posting" title="Closed Job"
                value="{{ number_format($closedCount) }}"
                description="fulfilled or ended"
                color="green"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />

            <x-card-chart.stat-card
                subtitle="Job Posting" title="Hold Job"
                value="{{ number_format($holdCount) }}"
                description="temporarily paused"
                color="red"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />

        </div>

        {{-- ═════════════════════════════════════════════════════════════════════
             ROW 2 — Applicant Funnel: Total Applicant, Total Rejected, Total Hired/Approved
            ═════════════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-3" style="align-items:stretch">

            <x-card-chart.stat-breakdown-card
                subtitle="Pipeline" title="Total Applicant"
                value="{{ number_format($totalApplicantAll) }}"
                color="blue"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z" /></svg>'
                :breakdown="[
                    ['label' => 'Job Applicant', 'value' => number_format($careerSourced), 'color' => 'violet'],
                    ['label' => 'Self Applicant', 'value' => number_format($selfSourced), 'color' => 'cyan'],
                ]" />

            <x-card-chart.stat-breakdown-card
                subtitle="Pipeline" title="Total Rejected"
                value="{{ number_format($totalRejectedAll) }}"
                description="{{ $totalApplicantAll > 0 ? round($totalRejectedAll / $totalApplicantAll * 100, 1) : 0 }}% of total applicant"
                color="red"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>'
                :breakdown="[
                    ['label' => 'Job Applicant', 'value' => number_format($totalRejected), 'color' => 'violet'],
                    ['label' => 'Self Applicant', 'value' => number_format($selfRejected), 'color' => 'cyan'],
                ]" />

            <x-card-chart.stat-card
                subtitle="Pipeline" title="Total Hired / Approved"
                value="{{ number_format($totalJoined) }}"
                description="{{ $totalApplicantAll > 0 ? round($totalJoined / $totalApplicantAll * 100, 1) : 0 }}% of total applicant · final approved headcount"
                color="green"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>' />

        </div>

        {{-- ═════════════════════════════════════════════════════════════════════
             ROW 3 — Demographics (Gender, Age, Education, Residential City)
            ═════════════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4" style="align-items:stretch">

            <x-card-chart.donut-chart
                subtitle="Demographics" title="By Gender" legend-position="right"
                color="pink" :labels="$genderLabels" :series="$genderSeries" />

            <x-card-chart.bar-chart
                subtitle="Demographics" title="By Age Bracket"
                color="cyan" :categories="$ageLabels"
                :series="[['name' => 'Candidates', 'data' => $ageSeries]]" />

            <x-card-chart.donut-chart
                subtitle="Demographics" title="By Education Level" legend-position="right"
                color="green" :labels="$educationLabels" :series="$educationSeries" />

            <x-card-chart.bar-chart
                subtitle="Demographics" title="By Residential City"
                color="orange" :categories="$cityLabels"
                :series="[['name' => 'Candidates', 'data' => $citySeries]]" />

        </div>

        {{-- ═════════════════════════════════════════════════════════════════════
             SUGGESTED ADDITIONS — Avg Time-to-Hire, Offer Acceptance Rate,
             Top 10 Division by Candidate Applied,
             PRF Completed → Job Status turnaround
            ═════════════════════════════════════════════════════════════════════ --}}
        @if($applicantType !== 'self')
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3" style="align-items:stretch">

            <div class='grid grid-rows-1 gap-3 sm:grid-cols-1 h-full justify-between' style="align-items:stretch">
                <x-card-chart.stat-card
                    class="flex-1"
                    subtitle="Velocity" title="Avg Time-to-Hire"
                    value="{{ $avgTimeToHire !== null ? $avgTimeToHire . ' days' : '—' }}"
                    description="apply → join cycle time"
                    color="orange"
                    icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />

                <x-card-chart.stat-card
                    class="flex-1"
                    subtitle="Pipeline" title="Offer Acceptance Rate"
                    value="{{ $offerAcceptanceRate }}%"
                    description="{{ number_format($totalJoined) }} of {{ number_format($totalOffered) }} offers · {{ $offerDeclineRate }}% declined"
                    color="green"
                    icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />

            </div>

            <x-card-chart.bar-chart
                subtitle="Workforce" title="Top 10 Division by Candidate Applied"
                color="violet" height="360" :stacked="true" :show-legend="false"
                :categories="$divisionLabels"
                :series="[
                    ['name' => 'Job Applicant', 'data' => $divisionCareerSeries],
                    ['name' => 'Self Applicant', 'data' => $divisionSelfSeries],
                ]">
                <x-slot:headerEnd>
                    <div class="flex items-center gap-4">
                        <span class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300">
                            <span class="h-2.5 w-2.5 rounded-sm" style="background:#8B5CF6"></span> Job Applicant
                        </span>
                        <span class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300">
                            <span class="h-2.5 w-2.5 rounded-sm" style="background:#3B82F6"></span> Self Applicant
                        </span>
                    </div>
                </x-slot:headerEnd>
            </x-card-chart.bar-chart>


            <x-card-chart.bar-chart
                subtitle="Requisition Health · avg {{ $avgPrfToPostingDays ?? '—' }} days" title="PRF Completed → Job Closed"
                color="violet" height="220"
                :categories="$prfToPostingLabels"
                :series="[['name' => 'PRF', 'data' => $prfToPostingSeries]]" />

        </div>

        {{-- ═════════════════════════════════════════════════════════════════════
             SUGGESTED ADDITIONS — Application Volume Trend, Top Postings
            ═════════════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-3" style="align-items:stretch">

            <x-card-chart.area-chart
                class="lg:col-span-2"
                subtitle="Trend" title="Application Volume Over Time"
                color="blue" height="280"
                :categories="$trendLabels"
                :series="[['name' => 'Applications', 'data' => $trendSeries]]" />

            <x-card-chart.bar-chart
                class="lg:col-span-1"
                subtitle="Job Postings" title="Top Postings by Applicants"
                color="orange" height="280"
                :categories="$topPostingLabels"
                :series="[['name' => 'Applicants', 'data' => $topPostingSeries]]" />

        </div>
        @endif

    </div>

    @push('scripts')
        <script src="{{ asset('assets/js/recruitment/dashboard.js') }}"></script>
    @endpush

</x-app-layout>
