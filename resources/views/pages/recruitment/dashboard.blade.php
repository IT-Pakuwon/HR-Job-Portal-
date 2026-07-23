<x-app-layout>

    <div class="max-w-9xl mx-auto w-full space-y-3 p-2">

        {{-- ── Page Header ──────────────────────────────────────────────────────── --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <h1 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                RECRUITMENT DASHBOARD
            </h1>
        </div>

        {{-- ── KPI Strip ────────────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">

            <x-card-chart.stat-card
                subtitle="Applicants" title="Total Applicant"
                value="{{ number_format($totalApplicant) }}"
                description="unique by KTP + DOB"
                color="violet"
                icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>' />

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

        {{-- ── Row 2: Gender · Age · Job Posting Status ────────────────────────── --}}
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-3" style="align-items:stretch">

            <x-card-chart.donut-chart
                subtitle="Applicants" title="By Gender"
                color="pink" :labels="$genderLabels" :series="$genderSeries" />

            <x-card-chart.bar-chart
                subtitle="Applicants" title="By Age"
                color="cyan" :categories="$ageLabels"
                :series="[['name' => 'Applicants', 'data' => $ageSeries]]" />

            <x-card-chart.donut-chart
                subtitle="Job Posting" title="By Status"
                color="blue" :labels="$jobpostingLabels" :series="$jobpostingSeries" />

        </div>

        {{-- ── Row 3: Recruitment Funnel ────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-3">

            <x-card-chart.funnel-chart
                subtitle="Pipeline Conversion" title="Recruitment Funnel"
                color="violet" :series="[['name' => 'Applicants', 'data' => $funnelSeries]]" />

        </div>

    </div>

</x-app-layout>
