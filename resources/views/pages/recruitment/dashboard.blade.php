<x-app-layout>

    @php
        // Builds a CSV export link for the given breakdown ("gender" | "education" | "city"),
        // carrying over whatever filters are currently applied to the dashboard.
        $exportUrl = fn (string $type) => route('recruitment.dashboard.export', array_filter(array_merge($filters, ['list' => $type])));
        $exportButton = fn (string $type, string $label) => '<a href="' . e($exportUrl($type)) . '" class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200">'
            . '<svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>'
            . e($label) . '</a>';
    @endphp

    <div class="max-w-9xl mx-auto w-full space-y-3 p-2 overflow-x-hidden">

        {{-- Page Header — title (left) + compact filter bar (right), matching the
             Reports/GM dashboard layout. --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-2">
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Career Portal Dashboard
                </h1>
                <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Last Updated: {{ $lastUpdatedAt }}
                </span>
            </div>

            <x-dashboard-filter.dashboard-filter
                :companyGroups="$companyGroups"
                :areas="$areas"
                :isGroupLocked="$isGroupLocked"
                :userGroupCpny="$userGroupCpny"
                :currentFilters="$filters"
                :departments="$departments"
                :divisions="$divisions"
                :companies="$companies"
                :locations="$locations" />
        </div>

        {{-- ═════════════════════════════════════════════════════════════════════
             ROW 1 — Applicant Overview: Requisition & Applicant Pipeline totals,
             Hiring Sources, and Age Demographics (tabbed)
            ═════════════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-12" style="align-items:stretch">

            <div class="flex flex-col gap-3 lg:col-span-3">

                <x-card-chart.stat-breakdown-card
                    class="flex-1"
                    title="Total Created PRF"
                    value="{{ number_format($totalPrf) }}"
                    color="violet"
                    icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>'
                    :breakdown="[
                        ['label' => 'On Progress', 'value' => number_format($prfOnProgressCount), 'color' => 'blue'],
                        ['label' => 'Revise', 'value' => number_format($prfReviseCount), 'color' => 'orange'],
                        ['label' => 'Completed', 'value' => number_format($prfCompletedCount), 'color' => 'green'],
                        ['label' => 'Rejected', 'value' => number_format($prfRejectedCount), 'color' => 'red'],
                    ]"
                    :insight="$insightPrf['text']" :insight-type="$insightPrf['type']" />

                <x-card-chart.stat-breakdown-card
                    class="flex-1"
                    title="Total Applicant"
                    value="{{ number_format($totalApplicantAll) }}"
                    color="blue"
                    icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z" /></svg>'
                    :breakdown="[
                        ['label' => 'Job Applicant', 'value' => number_format($careerSourced), 'color' => 'violet'],
                        ['label' => 'Self Applicant', 'value' => number_format($selfSourced), 'color' => 'cyan'],
                        ['label' => 'Total Rejected', 'value' => number_format($totalRejectedAll), 'color' => 'red'],
                        ['label' => 'Total Hired', 'value' => number_format($totalJoined), 'color' => 'green'],
                    ]"
                    :insight="$insightApplicant['text']" :insight-type="$insightApplicant['type']" />

            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:col-span-9" style="align-items:stretch">

                <div class="flex flex-col" style="display:flex">
                    <x-card-chart.bar-chart
                        class="flex-1"
                        title="Top Hiring Sources ({{ $unknownSourcePct }}% unrecorded)"
                        color="blue" height="300" :horizontal="false" :categories="$sourceLabels"
                        :series="[['name' => 'Candidates', 'data' => $sourceSeries]]">
                        <x-slot:headerEnd>
                            <x-card-chart.insight-lamp :text="$insightSource['text']" :type="$insightSource['type']" />
                        </x-slot:headerEnd>
                    </x-card-chart.bar-chart>
                </div>

                {{-- Age Distribution — Gender / Education tabbed into a single card
                     instead of two, to keep the dashboard to two rows. --}}
                <div class="flex flex-col" x-data="{ ageTab: 'gender' }">

                    @php
                        $ageTabButtons = <<<'BLADE'
                            <div class="inline-flex rounded-full bg-slate-100 p-1 text-xs font-semibold dark:bg-slate-800">
                                <button type="button" @click="ageTab = 'gender'"
                                    class="rounded-full px-3 py-1 transition"
                                    :class="ageTab === 'gender' ? 'bg-white text-pink-600 shadow-sm dark:bg-slate-900 dark:text-pink-400' : 'text-slate-500 dark:text-slate-400'">
                                    Gender
                                </button>
                                <button type="button" @click="ageTab = 'education'; $nextTick(() => window.dispatchEvent(new Event('resize')))"
                                    class="rounded-full px-3 py-1 transition"
                                    :class="ageTab === 'education' ? 'bg-white text-pink-600 shadow-sm dark:bg-slate-900 dark:text-pink-400' : 'text-slate-500 dark:text-slate-400'">
                                    Education
                                </button>
                            </div>
                        BLADE;
                    @endphp

                    <div x-show="ageTab === 'gender'" class="flex flex-1 flex-col">
                        <x-card-chart.bar-chart
                            class="flex-1"
                            title="Age Distribution by Gender"
                            color="pink" height="300" :stacked="true" :horizontal="false"
                            :categories="$ageLabels"
                            :series="$ageGenderSeries">
                            <x-slot:headerEnd>
                                <div class="flex flex-wrap items-center justify-end gap-1">
                                    {!! $ageTabButtons !!}
                                    <x-card-chart.insight-lamp :text="$insightAgeGender['text']" :type="$insightAgeGender['type']" />
                                    {!! $exportButton('gender', 'Unknown') !!}
                                </div>
                            </x-slot:headerEnd>
                        </x-card-chart.bar-chart>
                    </div>

                    <div x-show="ageTab === 'education'" x-cloak class="flex flex-1 flex-col">
                        <x-card-chart.bar-chart
                            class="flex-1"
                            title="Age Distribution by Education Level"
                            color="green" height="300" :stacked="true" :horizontal="false"
                            :categories="$ageLabels"
                            :series="$ageEducationSeries">
                            <x-slot:headerEnd>
                                <div class="flex flex-wrap items-center justify-end gap-1">
                                    {!! $ageTabButtons !!}
                                    <x-card-chart.insight-lamp :text="$insightEducation['text']" :type="$insightEducation['type']" />
                                    {!! $exportButton('education', 'Unknown') !!}
                                </div>
                            </x-slot:headerEnd>
                        </x-card-chart.bar-chart>
                    </div>

                </div>

            </div>

        </div>

        @if($applicantType !== 'self')
            {{-- ═════════════════════════════════════════════════════════════════
                 ROW 2 — Candidate Distribution (Division / City, tabbed), Total
                 Candidate per Job table, and Hiring Funnel (tabbed)
                ═════════════════════════════════════════════════════════════════ --}}
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3" style="align-items:stretch">

                <div class="flex flex-col" x-data="{ distTab: 'division' }">

                    @php
                        $distTabButtons = <<<'BLADE'
                            <div class="inline-flex rounded-full bg-slate-100 p-1 text-xs font-semibold dark:bg-slate-800">
                                <button type="button" @click="distTab = 'division'"
                                    class="rounded-full px-3 py-1 transition"
                                    :class="distTab === 'division' ? 'bg-white text-violet-600 shadow-sm dark:bg-slate-900 dark:text-violet-400' : 'text-slate-500 dark:text-slate-400'">
                                    Division
                                </button>
                                <button type="button" @click="distTab = 'city'; $nextTick(() => window.dispatchEvent(new Event('resize')))"
                                    class="rounded-full px-3 py-1 transition"
                                    :class="distTab === 'city' ? 'bg-white text-violet-600 shadow-sm dark:bg-slate-900 dark:text-violet-400' : 'text-slate-500 dark:text-slate-400'">
                                    City
                                </button>
                            </div>
                        BLADE;
                    @endphp

                    <div x-show="distTab === 'division'" class="flex flex-1 flex-col">
                        <x-card-chart.bar-chart
                            class="flex-1"
                            title="Top 10 Division by Candidate Applied"
                            color="violet" height="360" :stacked="true" :show-legend="false" :horizontal="false"
                            :categories="$divisionLabels"
                            :series="[
                                ['name' => 'Job Applicant', 'data' => $divisionCareerSeries],
                                ['name' => 'Self Applicant', 'data' => $divisionSelfSeries],
                            ]">
                            <x-slot:headerEnd>
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    {!! $distTabButtons !!}
                                    @if($insightDivision)
                                        <x-card-chart.insight-lamp :text="$insightDivision['text']" :type="$insightDivision['type']" />
                                    @endif
                                    <span class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        <span class="h-2.5 w-2.5 rounded-sm" style="background:#8B5CF6"></span> Job
                                    </span>
                                    <span class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300">
                                        <span class="h-2.5 w-2.5 rounded-sm" style="background:#3B82F6"></span> Self
                                    </span>
                                </div>
                            </x-slot:headerEnd>
                        </x-card-chart.bar-chart>
                    </div>

                    <div x-show="distTab === 'city'" x-cloak class="flex flex-1 flex-col">
                        <x-card-chart.bar-chart
                            class="flex-1"
                            title="Top 10 by Residential City"
                            color="orange" height="360" :horizontal="false" :categories="$cityLabels"
                            :series="[['name' => 'Candidates', 'data' => $citySeries]]">
                            <x-slot:headerEnd>
                                <div class="flex flex-wrap items-center justify-end gap-1">
                                    {!! $distTabButtons !!}
                                    <x-card-chart.insight-lamp :text="$insightCity['text']" :type="$insightCity['type']" />
                                    {!! $exportButton('city', 'Not in Top 10') !!}
                                </div>
                            </x-slot:headerEnd>
                        </x-card-chart.bar-chart>
                    </div>

                </div>

                <div class="flex flex-col" x-data="{ jobTableTab: 'jobs' }">

                    @php
                        $jobTableTabButtons = <<<'BLADE'
                            <div class="inline-flex rounded-full bg-slate-100 p-1 text-xs font-semibold dark:bg-slate-800">
                                <button type="button" @click="jobTableTab = 'jobs'"
                                    class="rounded-full px-3 py-1 transition"
                                    :class="jobTableTab === 'jobs' ? 'bg-white text-cyan-600 shadow-sm dark:bg-slate-900 dark:text-cyan-400' : 'text-slate-500 dark:text-slate-400'">
                                    Candidates per Job
                                </button>
                                <button type="button" @click="jobTableTab = 'prf'"
                                    class="rounded-full px-3 py-1 transition"
                                    :class="jobTableTab === 'prf' ? 'bg-white text-cyan-600 shadow-sm dark:bg-slate-900 dark:text-cyan-400' : 'text-slate-500 dark:text-slate-400'">
                                    PRF → Job Closed
                                </button>
                            </div>
                        BLADE;
                    @endphp

                    <div x-show="jobTableTab === 'jobs'" class="flex flex-1 flex-col">
                        <x-card-chart.table-card
                            class="flex-1"
                            title="Total Candidate Applied per Job"
                            color="cyan" :searchable="true" :sortable="true" search-placeholder="Search job..." max-height="360px"
                            :columns="[
                                ['label' => 'Job Title', 'key' => 'job_title'],
                                ['label' => 'Company', 'key' => 'company'],
                                ['label' => 'Total Applied', 'key' => 'total', 'numeric' => true],
                                ['label' => 'Share of Applicants', 'key' => 'pct', 'numeric' => true, 'type' => 'bar'],
                            ]"
                            :rows="$jobApplyRows">
                            <x-slot:headerEnd>
                                <div class="flex items-center gap-2">
                                    @if($insightTopJob)
                                        <x-card-chart.insight-lamp :text="$insightTopJob['text']" :type="$insightTopJob['type']" />
                                    @endif
                                    {!! $jobTableTabButtons !!}
                                </div>
                            </x-slot:headerEnd>
                        </x-card-chart.table-card>
                    </div>

                    <div x-show="jobTableTab === 'prf'" x-cloak class="flex flex-1 flex-col">
                        <x-card-chart.table-card
                            class="flex-1"
                            title="PRF Completed → Job Closed (avg {{ $avgPrfToPostingDays ?? '—' }} days)"
                            color="cyan" :searchable="true" :sortable="true" search-placeholder="Search PRF..." max-height="360px"
                            :columns="[
                                ['label' => 'PRF', 'key' => 'prf'],
                                ['label' => 'Job Title', 'key' => 'job_title'],
                                ['label' => 'Company', 'key' => 'company'],
                                ['label' => 'Days (Completed → Posted)', 'key' => 'total', 'numeric' => true],
                            ]"
                            :rows="$prfTurnaroundRows">
                            <x-slot:headerEnd>{!! $jobTableTabButtons !!}</x-slot:headerEnd>
                        </x-card-chart.table-card>
                    </div>

                </div>

                <div class="flex flex-col" x-data="{ funnelTab: 'funnel' }">

                    @php
                        $funnelTabButtons = <<<'BLADE'
                            <div class="inline-flex rounded-full bg-slate-100 p-1 text-xs font-semibold dark:bg-slate-800">
                                <button type="button" @click="funnelTab = 'funnel'"
                                    class="rounded-full px-3 py-1 transition"
                                    :class="funnelTab === 'funnel' ? 'bg-white text-orange-600 shadow-sm dark:bg-slate-900 dark:text-orange-400' : 'text-slate-500 dark:text-slate-400'">
                                    Funnel
                                </button>
                                <button type="button" @click="funnelTab = 'timing'; $nextTick(() => window.dispatchEvent(new Event('resize')))"
                                    class="rounded-full px-3 py-1 transition"
                                    :class="funnelTab === 'timing' ? 'bg-white text-orange-600 shadow-sm dark:bg-slate-900 dark:text-orange-400' : 'text-slate-500 dark:text-slate-400'">
                                    Avg Time to Stage
                                </button>
                                <button type="button" @click="funnelTab = 'prf'; $nextTick(() => window.dispatchEvent(new Event('resize')))"
                                    class="rounded-full px-3 py-1 transition"
                                    :class="funnelTab === 'prf' ? 'bg-white text-orange-600 shadow-sm dark:bg-slate-900 dark:text-orange-400' : 'text-slate-500 dark:text-slate-400'">
                                    PRF → Job Closed
                                </button>
                            </div>
                        BLADE;
                    @endphp

                    <div x-show="funnelTab === 'funnel'" class="flex flex-1 flex-col">
                        <x-card-chart.funnel-chart
                            class="flex-1"
                            title="Hiring Funnel"
                            color="orange" height="360"
                            :series="$funnelSeries">
                            <x-slot:headerEnd>
                                <div class="flex items-center gap-2">
                                    @if($insightBottleneck)
                                        <x-card-chart.insight-lamp :text="$insightBottleneck['text']" :type="$insightBottleneck['type']" />
                                    @endif
                                    {!! $funnelTabButtons !!}
                                </div>
                            </x-slot:headerEnd>
                        </x-card-chart.funnel-chart>
                    </div>

                    <div x-show="funnelTab === 'timing'" x-cloak class="flex flex-1 flex-col">
                        <x-card-chart.area-chart
                            class="flex-1"
                            title="Avg Time to Reach Stage (cumulative days since applied)"
                            color="orange" height="360"
                            :categories="$stageTimingLabels"
                            :series="[['name' => 'Cumulative days', 'data' => $stageTimingCumulative]]">
                            <x-slot:headerEnd>{!! $funnelTabButtons !!}</x-slot:headerEnd>
                        </x-card-chart.area-chart>
                    </div>

                    <div x-show="funnelTab === 'prf'" x-cloak class="flex flex-1 flex-col">
                        <x-card-chart.bar-chart
                            class="flex-1"
                            title="PRF Completed → Job Closed (avg {{ $avgPrfToPostingDays ?? '—' }} days)"
                            color="orange" height="360"
                            :categories="$prfToPostingLabels"
                            :series="[['name' => 'PRF', 'data' => $prfToPostingSeries]]">
                            <x-slot:headerEnd>{!! $funnelTabButtons !!}</x-slot:headerEnd>
                        </x-card-chart.bar-chart>
                    </div>

                </div>

            </div>
        @endif

    </div>

    @push('scripts')
        <script src="{{ asset('assets/js/recruitment/dashboard.js') }}"></script>
    @endpush

</x-app-layout>
