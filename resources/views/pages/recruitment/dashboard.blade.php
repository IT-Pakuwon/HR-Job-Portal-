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
             ROW 1 — Strategic KPI Strip: 6 executive metric cards
            ═════════════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">

            @if($applicantType === 'self')
                {{-- Self Applicant Talent Pool KPIs --}}
                <x-card-chart.stat-card
                    subtitle="Talent Pool" title="Total Self-Registered"
                    value="{{ number_format($totalSelfApplicant) }}"
                    description="direct signups"
                    color="violet"
                    icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>' />

                <x-card-chart.stat-card
                    subtitle="Talent Pool" title="Profile Completion"
                    value="{{ $profileCompletenessRate }}%"
                    description="with completed CV / documents"
                    color="blue"
                    icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" /></svg>' />

                <x-card-chart.stat-card
                    subtitle="Talent Pool" title="Unassigned"
                    value="{{ number_format($totalUnassignedCandidates) }}"
                    description="not yet matched to any job"
                    color="cyan"
                    icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z" /></svg>' />

                <x-card-chart.stat-card
                    subtitle="Talent Pool" title="Matched"
                    value="{{ number_format($totalMatchedCandidates) }}"
                    description="linked to active job openings"
                    color="green"
                    icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />

                <x-card-chart.stat-card
                    subtitle="Talent Pool" title="Direct Signups"
                    value="{{ number_format($totalSelfApplicant) }}"
                    description="self-registered applicants"
                    color="emerald"
                    icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>' />

                <x-card-chart.stat-card
                    subtitle="Talent Pool" title="Total Pool"
                    value="{{ number_format($totalTalentPool) }}"
                    description="career + self-registered"
                    color="orange"
                    icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />
            @else
                {{-- Job Applicant / All View KPIs --}}
                <x-card-chart.stat-card
                    subtitle="Talent Pool" title="Total Candidates"
                    value="{{ number_format($totalTalentPool) }}"
                    description="career + self-registered applicants"
                    color="violet"
                    icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>' />

                <x-card-chart.stat-card
                    subtitle="Requisitions" title="Active PRF"
                    value="{{ number_format($activeRequisitions) }}"
                    description="posted + unposted requisitions"
                    color="blue"
                    icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" /></svg>' />

                <x-card-chart.stat-card
                    subtitle="Pipeline" title="Total Applied"
                    value="{{ number_format($totalApplied) }}"
                    description="job applications received"
                    color="cyan"
                    icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z" /></svg>' />

                <x-card-chart.stat-card
                    subtitle="Pipeline" title="Offers Made"
                    value="{{ number_format($totalOffered) }}"
                    description="{{ $offerAcceptanceRate }}% accepted · {{ $offerDeclineRate }}% declined"
                    color="green"
                    icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />

                <x-card-chart.stat-card
                    subtitle="Pipeline" title="Joined"
                    value="{{ number_format($totalJoined) }}"
                    description="{{ $totalApplied > 0 ? round($totalJoined / $totalApplied * 100, 1) : 0 }}% of applied"
                    color="emerald"
                    icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>' />

                <x-card-chart.stat-card
                    subtitle="Velocity" title="Avg Time-to-Hire"
                    value="{{ $avgTimeToHire !== null ? $avgTimeToHire . ' days' : '—' }}"
                    description="apply → join cycle time"
                    color="orange"
                    icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />
            @endif

        </div>

        {{-- ═════════════════════════════════════════════════════════════════════
             ROW 2 — Trend (Application Volume or Talent Pool Growth)
            ═════════════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-3" style="align-items:stretch">

            @if($applicantType === 'self')
                <x-card-chart.area-chart
                    class="lg:col-span-2"
                    subtitle="Trend" title="Talent Pool Growth Over Time"
                    color="cyan" height="280"
                    :categories="$talentPoolGrowthLabels"
                    :series="[['name' => 'Self Registrations', 'data' => $talentPoolGrowthSeries]]" />

                <x-card-chart.bar-chart
                    class="lg:col-span-1"
                    subtitle="By Department" title="Self-Registrants by Dept (Top 10)"
                    color="violet" height="280"
                    :categories="$selfByDeptLabels"
                    :series="[['name' => 'Applicants', 'data' => $selfByDeptSeries]]" />
            @else
                <x-card-chart.area-chart
                    class="lg:col-span-2"
                    subtitle="Trend" title="Application Volume Over Time"
                    color="blue" height="280"
                    :categories="$trendLabels"
                    :series="[['name' => 'Applications', 'data' => $trendSeries]]" />

                <x-card-chart.card-shell subtitle="MoM Change" title="Month-over-Month %" color="violet">
                    <div class="space-y-2 px-5 pb-5">
                        @php
                            $lastMoM = count($trendMoM) >= 2 ? $trendMoM[count($trendMoM) - 1] : 0;
                            $prevMoM = count($trendMoM) >= 3 ? $trendMoM[count($trendMoM) - 2] : 0;
                            $momLabels = array_slice($trendLabels, -6);
                            $momValues = array_slice($trendMoM, -6);
                        @endphp
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-extrabold tabular-nums {{ $lastMoM >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $lastMoM >= 0 ? '+' : '' }}{{ $lastMoM }}%
                            </span>
                            <span class="text-xs text-slate-400 dark:text-slate-500">vs previous month</span>
                        </div>
                        <div class="mt-4 space-y-1.5">
                            @foreach(array_reverse($momLabels) as $i => $label)
                                @php $val = $momValues[count($momValues) - 1 - $i] ?? 0; @endphp
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-medium text-slate-600 dark:text-slate-300">{{ $label }}</span>
                                    <span class="font-bold tabular-nums {{ $val >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ $val > 0 ? '+' : '' }}{{ $val }}%
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-card-chart.card-shell>
            @endif

        </div>

        {{-- ═════════════════════════════════════════════════════════════════════
             ROW 3 — Source Efficiency (always visible); Funnel (hidden in self mode)
            ═════════════════════════════════════════════════════════════════════ --}}
        @if($applicantType !== 'self')
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2" style="align-items:stretch">

            <x-card-chart.funnel-chart
                subtitle="Pipeline Conversion" title="Recruitment Funnel"
                color="violet" height="320"
                :series="[
                    ['name' => 'Pipeline', 'data' => collect($funnelSeries)->map(fn($s) => ['x' => $s['x'], 'y' => $s['y']])->all()]
                ]" />

            <x-card-chart.bar-chart
                subtitle="Source" title="Source Efficiency (Applied vs Hired)"
                color="green" height="320"
                :categories="collect($sourceEfficiency)->pluck('source')->all()"
                :series="[
                    ['name' => 'Applied', 'data' => collect($sourceEfficiency)->pluck('applied')->all()],
                    ['name' => 'Hired', 'data' => collect($sourceEfficiency)->pluck('hired')->all()],
                ]" />

        </div>
        @else
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-1" style="align-items:stretch">

            <x-card-chart.bar-chart
                subtitle="Source" title="Source Distribution"
                color="green" height="320"
                :categories="collect($sourceEfficiency)->pluck('source')->all()"
                :series="[
                    ['name' => 'Applicants', 'data' => collect($sourceEfficiency)->pluck('applied')->all()],
                ]" />

        </div>
        @endif

        {{-- ═════════════════════════════════════════════════════════════════════
             ROW 5 — Demographics (3 cols)
            ═════════════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-3" style="align-items:stretch">

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

        </div>

        {{-- ═════════════════════════════════════════════════════════════════════
             ROW 7 — Workforce, Regional, Postings & Status (4 cols, hide postings in self mode)
            ═════════════════════════════════════════════════════════════════════ --}}
        @if($applicantType === 'self')
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2" style="align-items:stretch">

            <x-card-chart.bar-chart
                subtitle="Workforce" title="By Division (Top 10)"
                color="pink" :categories="$divisionLabels"
                :series="[['name' => 'Applicants', 'data' => $divisionSeries]]" />

            <x-card-chart.donut-chart
                subtitle="Regional" title="Applicants by Area" legend-position="right"
                color="blue"
                :labels="count($areaLabels) ? $areaLabels : ['No Data']"
                :series="count($areaSeries) ? $areaSeries : [0]" />

        </div>
        @else
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4" style="align-items:stretch">

            <x-card-chart.bar-chart
                subtitle="Workforce" title="By Division (Top 10)"
                color="pink" :categories="$divisionLabels"
                :series="[['name' => 'Applicants', 'data' => $divisionSeries]]" />

            <x-card-chart.donut-chart
                subtitle="Regional" title="Applicants by Area" legend-position="right"
                color="blue"
                :labels="count($areaLabels) ? $areaLabels : ['No Data']"
                :series="count($areaSeries) ? $areaSeries : [0]" />

            <x-card-chart.bar-chart
                subtitle="Job Postings" title="Top Postings by Applicants (Top 10)"
                color="orange" :categories="$topPostingLabels"
                :series="[['name' => 'Applicants', 'data' => $topPostingSeries]]" />

            <x-card-chart.donut-chart
                subtitle="Postings" title="Candidates by Posting Status" legend-position="right"
                color="violet" :labels="$jobpostingLabels" :series="$jobpostingSeries" />

        </div>
        @endif

        {{-- ═════════════════════════════════════════════════════════════════════
             ROW 9 — PRF Requisition Health (hidden in self mode)
            ═════════════════════════════════════════════════════════════════════ --}}
        @if($applicantType !== 'self')
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

            <x-card-chart.stat-card
                subtitle="Requisition" title="Total PRF Approved"
                value="{{ number_format($totalPrf) }}"
                description="completed personnel requisitions"
                color="violet"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>' />

            <x-card-chart.stat-card
                subtitle="Requisition" title="PRF → Posted"
                value="{{ number_format($totalPrfPosted) }}"
                description="{{ $totalPrf > 0 ? round($totalPrfPosted / $totalPrf * 100, 1) : 0 }}% of approved PRF"
                color="green"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />

            <x-card-chart.stat-card
                subtitle="Requisition" title="PRF Unposted"
                value="{{ number_format($totalPrfUnposted) }}"
                description="{{ $totalPrf > 0 ? round($totalPrfUnposted / $totalPrf * 100, 1) : 0 }}% of approved PRF"
                color="orange"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>' />

        </div>
        @endif

    </div>

    @push('scripts')
        <script src="{{ asset('assets/js/recruitment/dashboard.js') }}"></script>
    @endpush

</x-app-layout>
