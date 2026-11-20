<div class="max-w-9xl mx-auto w-full overflow-hidden rounded-lg bg-white   dark:bg-gray-800 ">

    {{-- ── Document header (compact) ───────────────────────────────── --}}
    @php
        $canRemapBtn = !in_array($career->status ?? '', ['T', 'C', 'R', 'X']) && auth()->user()->hasRole('RECACCALLDEPT');
        $remapRef = $remapped_from ?: $remapped_to;
        $remapDir = $remapped_from ? 'from' : 'to';
        $remapText = $remapRef ? $remapDir.' '.$remapRef->jobid.($remapRef->job_title ? ' — '.$remapRef->job_title : '') : null;
        $applicantInitial = mb_strtoupper(mb_substr(trim((string) ($career->fullname ?? '?')), 0, 1));
    @endphp
    <div class="flex flex-wrap items-stretch gap-3 border-b border-gray-100 px-4 py-3 dark:border-gray-700/60">

        <div class="grid min-w-0 flex-1 grid-cols-1 overflow-hidden rounded-2xl border border-gray-100 bg-gray-50/70 sm:grid-cols-2 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)] lg:divide-x lg:divide-gray-100 dark:border-gray-700/60 dark:bg-gray-700/20 dark:lg:divide-gray-700/60">

            {{-- Document --}}
            <div class="flex min-w-0 items-center gap-3 px-4 py-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-indigo-600 shadow-sm ring-1 ring-gray-100 dark:bg-gray-800 dark:text-indigo-300 dark:ring-gray-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                </span>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Document</p>
                    <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1">
                        <span class="rounded-md bg-gray-800 px-2 py-0.5 text-xs font-bold tracking-wide text-white dark:bg-gray-600">{{ $career->docid ?? '-' }}</span>
                        <span class="inline-flex items-center gap-1 text-xs text-gray-400">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25"/></svg>
                            {{ $career->apply_date ?? '' }}
                        </span>
                        @if($remapRef)
                            <span class="inline-flex max-w-full items-center gap-1 rounded-full bg-violet-100 px-2 py-0.5 text-[11px] font-semibold text-violet-700 dark:bg-violet-900/40 dark:text-violet-300" title="Remapped {{ $remapText }}">
                                <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                <span class="truncate">Remapped {{ $remapText }}</span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Company --}}
            <div class="flex min-w-0 items-center gap-3 px-4 py-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-600 dark:bg-sky-500/15 dark:text-sky-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M9 9v.01M9 12v.01M9 15v.01M9 18v.01"/></svg>
                </span>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Company</p>
                    <p class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100" title="{{ $companyName ?? $career->cpnyid ?? '-' }}">{{ $companyName ?? $career->cpnyid ?? '-' }}</p>
                </div>
            </div>

            {{-- Position --}}
            <div class="flex min-w-0 items-center gap-3 px-4 py-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                </span>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Position</p>
                    <p class="truncate text-sm font-semibold text-indigo-600 dark:text-indigo-400" title="{{ $career->job_title ?? '-' }}">{{ $career->job_title ?? '-' }}</p>
                </div>
            </div>

            {{-- Applicant --}}
            <div class="flex min-w-0 items-center gap-3 px-4 py-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white ring-4 ring-indigo-100 dark:ring-indigo-500/20">{{ $applicantInitial }}</span>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Applicant</p>
                    <p class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100" title="{{ $career->fullname ?? '-' }}">{{ $career->fullname ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Remap action (kept in the same row) --}}
        @if ($canRemapBtn)
            <button id="remapBtn"
                class="inline-flex shrink-0 items-center gap-2 self-center rounded-xl border border-violet-200 bg-violet-50 px-4 py-2.5 text-sm font-semibold text-violet-700 transition hover:-translate-y-0.5 hover:bg-violet-100 hover:shadow-sm focus:outline-none active:scale-95 dark:border-violet-700/30 dark:bg-violet-900/20 dark:text-violet-300">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                Remap Position
            </button>
        @endif
    </div>

    <div x-data="{
            subtab: sessionStorage.getItem('careerSubtab_{{ $career->docid }}') || 'step',
            init: function() {
                this.$watch('subtab', (value) => {
                    sessionStorage.setItem('careerSubtab_{{ $career->docid }}', value);
                    this.$el.scrollIntoView({ behavior: 'smooth' });
                });
            }
        }" class="w-full">

        {{-- ── Sub-tabs + progress ─────────────────────────────────────── --}}
        @php
            $totalSteps    = $jobapplystep->count();
            $approvedCount = $jobapplystep->where('status', 'A')->count();
            $rejectedCount = $jobapplystep->where('status', 'R')->count();
            $skippedCount  = $jobapplystep->where('status', 'X')->count();
            // Skipped steps (e.g. a single-track interview) aren't part of what's
            // "left to approve", so exclude them from the denominator or the bar
            // could never reach 100% for an applicant who skipped a track.
            $applicableSteps = $totalSteps - $skippedCount;
            $progressPct  = $applicableSteps > 0 ? round(($approvedCount / $applicableSteps) * 100) : 0;
        @endphp
        <div class="flex items-center gap-3 border-b border-gray-100 px-3 py-2.5 dark:border-gray-700/60">

            {{-- Tab tray full width --}}
            <div class="flex flex-1 items-center gap-0.5 rounded-lg bg-gray-200 p-1 dark:bg-gray-900">
                <button @click="subtab = 'step'"
                    :class="subtab === 'step'
                        ? 'bg-white text-gray-900 shadow dark:bg-gray-700 dark:text-white'
                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300'"
                    class="flex-1 rounded-lg px-3 py-1.5 text-sm font-semibold transition-all duration-150 focus:outline-none">
                    Workflow
                </button>
                @if ($canAccessSchedule)
                <button @click="subtab = 'schedule'"
                    :class="subtab === 'schedule'
                        ? 'bg-white text-gray-900 shadow dark:bg-gray-700 dark:text-white'
                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300'"
                    class="flex-1 rounded-lg px-3 py-1.5 text-sm font-semibold transition-all duration-150 focus:outline-none">
                    Schedule
                </button>
                @endif
                @if ($canAccessChecklist)
                <button @click="subtab = 'checklist'"
                    :class="subtab === 'checklist'
                        ? 'bg-white text-gray-900 shadow dark:bg-gray-700 dark:text-white'
                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300'"
                    class="flex-1 rounded-lg px-3 py-1.5 text-sm font-semibold transition-all duration-150 focus:outline-none">
                    Checklist
                </button>
                @endif
                @if ($canAccessInterviewHC)
                <button @click="subtab = 'assessment'"
                    :class="subtab === 'assessment'
                        ? 'bg-white text-gray-900 shadow dark:bg-gray-700 dark:text-white'
                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300'"
                    class="flex-1 rounded-lg px-3 py-1.5 text-sm font-semibold transition-all duration-150 focus:outline-none">
                    Interview HC
                </button>
                @endif
                @if ($canAccessInterviewUser)
                <button @click="subtab = 'assessmentuser'"
                    :class="subtab === 'assessmentuser'
                        ? 'bg-white text-gray-900 shadow dark:bg-gray-700 dark:text-white'
                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300'"
                    class="flex-1 rounded-lg px-3 py-1.5 text-sm font-semibold transition-all duration-150 focus:outline-none">
                    Interview User
                </button>
                @endif
                @if ($canAccessPayroll)
                <button @click="subtab = 'payroll'"
                    :class="subtab === 'payroll'
                        ? 'bg-white text-gray-900 shadow dark:bg-gray-700 dark:text-white'
                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300'"
                    class="flex-1 rounded-lg px-3 py-1.5 text-sm font-semibold transition-all duration-150 focus:outline-none">
                    Payroll
                </button>
                @endif
                @if ($canAccessJoin)
                <button @click="subtab = 'join'"
                    :class="subtab === 'join'
                        ? 'bg-white text-gray-900 shadow dark:bg-gray-700 dark:text-white'
                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300'"
                    class="flex-1 rounded-lg px-3 py-1.5 text-sm font-semibold transition-all duration-150 focus:outline-none">
                    Join
                </button>
                @endif
            </div>

            {{-- Progress summary --}}
            <div class="flex items-center gap-2.5">
                <span class="text-sm font-semibold tabular-nums text-gray-400 dark:text-gray-500">
                    {{ $approvedCount }}/{{ $totalSteps }}
                    @if($rejectedCount > 0)
                        <span class="text-red-400">&nbsp;&bull; {{ $rejectedCount }}R</span>
                    @endif
                </span>
                @php
                    $barColor = $rejectedCount > 0 ? 'bg-red-400' : ($progressPct === 100 ? 'bg-emerald-500' : 'bg-gray-400');
                @endphp
                <div class="h-1 w-16 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                    <div class="h-full rounded-full transition-all duration-500 {{ $barColor }}"
                        style="width: {{ $progressPct }}%">
                    </div>
                </div>
            </div>
        </div>

        <div>
            {{-- ── Workflow tab ─────────────────────────────────────────── --}}
            <div x-show="subtab === 'step'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">

                @php
                    $firstPendingShown = false;
                    // Fallback labels for steps whose step_id has no matching row in
                    // hr_ms_job_step for this company's group (e.g. WIHC/IHC are missing
                    // from group SBY's master config), so the name never renders blank.
                    $stepLabels = [
                        'JOAPHC' => 'Job Apply HC',
                        'JOAPUS' => 'Job Apply User',
                        'WIHC'   => 'Create Schedule Interview HC',
                        'IHC'    => 'Interview HC',
                        'WIU'    => 'Create Schedule Interview User',
                        'IU'     => 'Interview User',
                        'WPT'    => 'Waiting Psycho Test',
                        'PT'     => 'Psycho Test',
                        'OFF'    => 'Offering',
                        'JOIN'   => 'Join',
                    ];
                    $step3 = $jobapplystep->firstWhere('step_order', 3);
                    $step5 = $jobapplystep->firstWhere('step_order', 5);
                    // 'X' = track skipped (e.g. recruiter chose a single-track interview
                    // type in storeAgenda), which also satisfies the gate — not just 'A'.
                    $step3Approved = $step3 ? in_array($step3->status, ['A', 'X'], true) : true;
                    $step5Approved = $step5 ? in_array($step5->status, ['A', 'X'], true) : true;
                @endphp

                <div class="grid grid-cols-12">

                    {{-- ── Step list ────────────────────────────────────── --}}
                    <div class="col-span-12 lg:col-span-6">
                        <ol class="relative px-5 py-5">
                        @foreach ($jobapplystep as $step)
                            @php
                                $order         = (int) $step->step_order;
                                $blockedByGate = (!$step3Approved && $order > 3) || (!$step5Approved && $order > 5);
                                $isGateStep    = in_array($order, [3, 5], true);
                                $shouldHideBtn = $isGateStep || $blockedByGate;
                                $isActive      = $step->status === 'P' && !$shouldHideBtn && !$firstPendingShown;
                                $isFuture      = $step->status === 'P' && ($shouldHideBtn || $firstPendingShown);
                                $isLastStep    = $loop->last;
                                $stepName      = $step->step_descr ?: ($stepLabels[$step->step_id] ?? $step->step_id);

                                // Connector below this step: green once the step itself is done.
                                $lineCls = in_array($step->status, ['A', 'X'], true) ? 'bg-emerald-300 dark:bg-emerald-500/40' : 'bg-gray-200 dark:bg-gray-700';

                                $cardCls = $isActive
                                    ? 'border-indigo-300 bg-indigo-50/60 ring-2 ring-indigo-100 dark:border-indigo-500/40 dark:bg-indigo-500/10 dark:ring-indigo-500/10'
                                    : ($step->status === 'R'
                                        ? 'border-red-200 bg-red-50/60 dark:border-red-500/30 dark:bg-red-500/10'
                                        : ($isFuture
                                            ? 'border-dashed border-gray-200 bg-transparent opacity-60 dark:border-gray-700'
                                            : 'border-gray-100 bg-white hover:shadow-md dark:border-gray-700/60 dark:bg-gray-800'));
                            @endphp

                            <li class="relative flex gap-4 {{ $isLastStep ? '' : 'pb-4' }}">
                                @unless ($isLastStep)
                                    <span class="absolute bottom-0 left-[17px] top-10 w-0.5 rounded-full {{ $lineCls }}"></span>
                                @endunless

                                {{-- Marker --}}
                                <div class="relative z-10 shrink-0">
                                    @if($step->status === 'A')
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500 text-white shadow-sm shadow-emerald-500/30 ring-4 ring-white dark:ring-gray-800">
                                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    @elseif($step->status === 'R')
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-red-500 text-white shadow-sm shadow-red-500/30 ring-4 ring-white dark:ring-gray-800">
                                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </div>
                                    @elseif($step->status === 'X')
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-300 text-white ring-4 ring-white dark:bg-gray-600 dark:ring-gray-800">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>
                                        </div>
                                    @elseif($isActive)
                                        <div class="relative flex h-9 w-9 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white ring-4 ring-white dark:ring-gray-800">
                                            <span class="absolute inset-0 animate-ping rounded-full bg-indigo-400 opacity-30"></span>
                                            <span class="relative">{{ $order }}</span>
                                        </div>
                                    @else
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full border-2 border-dashed border-gray-300 bg-white text-sm font-bold text-gray-400 ring-4 ring-white dark:border-gray-600 dark:bg-gray-800 dark:text-gray-500 dark:ring-gray-800">{{ $order }}</div>
                                    @endif
                                </div>

                                {{-- Step card --}}
                                <div class="flex min-w-0 flex-1 flex-wrap items-center justify-between gap-3 rounded-2xl border p-3.5 transition-all duration-200 {{ $cardCls }}">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-sm {{ $isActive ? 'font-bold text-gray-900 dark:text-white' : ($step->status === 'A' ? 'font-semibold text-gray-800 dark:text-gray-100' : 'font-medium text-gray-600 dark:text-gray-300') }}">{{ $stepName }}</p>
                                            @if($isActive)
                                                <span class="rounded-full bg-indigo-600 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">Current</span>
                                            @elseif($step->status === 'R')
                                                <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-red-600 dark:bg-red-500/20 dark:text-red-300">Rejected</span>
                                            @endif
                                        </div>

                                        @if($step->aprvusername || $step->aprvuserdate)
                                            <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                                                @if($step->aprvusername)
                                                    <span class="inline-flex items-center gap-1.5">
                                                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-gray-200 text-[10px] font-bold uppercase text-gray-600 dark:bg-gray-600 dark:text-gray-200">{{ mb_substr($step->aprvusername, 0, 1) }}</span>
                                                        {{ $step->aprvusername }}
                                                    </span>
                                                @endif
                                                @if($step->aprvuserdate)
                                                    <span class="inline-flex items-center gap-1">
                                                        <svg class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                                        {{ $step->aprvuserdate }}
                                                    </span>
                                                @endif
                                                @if(isset($stepDurations[$step->id]))
                                                    <span class="inline-flex rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300"
                                                        title="{{ $stepDurations[$step->id]['start'] }} → {{ $stepDurations[$step->id]['end'] }}">
                                                        {{ $stepDurations[$step->id]['days'] }} {{ (int) $stepDurations[$step->id]['days'] === 1 ? 'day' : 'days' }}
                                                    </span>
                                                @endif
                                            </div>
                                        @elseif($isActive)
                                            <p class="mt-1 text-xs text-indigo-500">Waiting for approval</p>
                                        @elseif($isFuture)
                                            <p class="mt-1 text-xs text-gray-400">Not started</p>
                                        @endif
                                    </div>

                                    {{-- Action / Status --}}
                                    <div class="flex shrink-0 items-center gap-2">
                                        @if ($career->status === 'T')
                                            {{-- Old apply (transferred) — read-only --}}
                                            @if ($step->status === 'A')
                                                <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-600">Approved</span>
                                            @elseif ($step->status === 'R')
                                                <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-500">Rejected</span>
                                            @elseif ($step->status === 'X')
                                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-400 dark:bg-gray-900">Cancelled</span>
                                            @endif
                                        @else
                                            @if ($step->status === 'P')
                                                @if (!$shouldHideBtn && !$firstPendingShown)
                                                    @php $firstPendingShown = true; @endphp
                                                    <button id="approveBtn"
                                                        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-emerald-500/30 transition hover:bg-emerald-700 focus:outline-none active:scale-95">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                        Approve
                                                    </button>
                                                    <button id="rejectBtn"
                                                        class="inline-flex items-center gap-1.5 rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50 focus:outline-none active:scale-95 dark:border-red-500/40 dark:bg-transparent dark:text-red-300 dark:hover:bg-red-500/10">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        Reject
                                                    </button>
                                                @endif
                                            @elseif ($step->status === 'A' || $step->status === 'R')
                                                <button class="rollbackBtn inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm font-semibold text-gray-500 transition hover:border-amber-300 hover:bg-amber-50 hover:text-amber-600 focus:outline-none active:scale-95 dark:border-gray-600 dark:bg-transparent dark:text-gray-400 dark:hover:border-amber-500 dark:hover:text-amber-400">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
                                                    Rollback
                                                </button>
                                            @elseif ($step->status === 'D')
                                                <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-600">Revised</span>
                                            @elseif ($step->status === 'X')
                                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-400 dark:bg-gray-900">Skipped</span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                        </ol>
                    </div>

                    {{-- ── Comments ─────────────────────────────────────── --}}
                    <div class="col-span-12 lg:col-span-6 flex flex-col border-t border-gray-100 dark:border-gray-700/40 lg:col-span-3 lg:border-l lg:border-t-0">
                        <div x-data="{ isOpen: true }" class="flex flex-1 flex-col">
                            <button class="flex w-full items-center justify-between px-5 py-3.5" @click="isOpen = !isOpen">
                                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">Comments</span>
                                <svg x-show="isOpen" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                <svg x-show="!isOpen" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                            </button>
                            <div x-show="isOpen" x-collapse.duration.200ms class="flex flex-1 flex-col border-t border-gray-100 dark:border-gray-700/40">
                                <div id="commentList" class="flex flex-col gap-1 overscroll-contain bg-gradient-to-b from-gray-50 to-white px-4 py-4 dark:from-gray-900/40 dark:to-gray-800" style="flex:1 1 0;min-height:360px;overflow-y:auto;scroll-behavior:smooth;">
                                    <p class="animate-pulse py-10 text-center text-sm italic text-gray-400">Loading...</p>
                                </div>
                                <div class="border-t border-gray-100 bg-white p-3 dark:border-gray-700/40 dark:bg-gray-800">
                                    <div class="flex items-center gap-2 rounded-full border border-gray-200 bg-gray-50 py-1.5 pl-4 pr-1.5 transition focus-within:border-indigo-400 focus-within:bg-white focus-within:ring-2 focus-within:ring-indigo-100 dark:border-gray-600 dark:bg-gray-700 dark:focus-within:ring-indigo-500/20">
                                        <input id="commentInput" type="text" placeholder="Write a message…" autocomplete="off"
                                            class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-0 dark:text-white">
                                        <button id="postCommentBtn" type="button" aria-label="Send"
                                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-fuchsia-500 text-white shadow transition hover:scale-105 hover:shadow-md active:scale-95 disabled:opacity-50">
                                            <svg class="h-4 w-4 -rotate-12" viewBox="0 0 24 24" fill="currentColor"><path d="M3.4 20.4 21 12 3.4 3.6 3.3 10l12 2-12 2z"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div x-show="subtab === 'schedule'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                class="min-h-[200px] rounded-xl bg-white p-4 dark:bg-gray-800">
                @include('pages.careers.schedule')
            </div>

            <div x-show="subtab === 'checklist'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                class="min-h-[200px] rounded-xl bg-white p-4 dark:bg-gray-800">
                @include('pages.careers.checklist')
            </div>

            <div x-show="subtab === 'assessment'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                class="min-h-[200px] rounded-xl bg-white p-4 dark:bg-gray-800">
                @include('pages.careers.assessmenthc')
            </div>

            <div x-show="subtab === 'assessmentuser'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                class="min-h-[200px] rounded-xl bg-white p-4 dark:bg-gray-800">
                @include('pages.careers.assessmentuser')
            </div>

            <div x-show="subtab === 'psychotest'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                class="min-h-[200px] rounded-xl bg-white p-4 dark:bg-gray-800">
                @include('pages.careers.psychotest')
            </div>

            <div x-show="subtab === 'payroll'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                class="min-h-[200px] w-full rounded-xl bg-white p-4 dark:bg-gray-800">
                @include('pages.careers.payroll')
            </div>

            <div x-show="subtab === 'join'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                class="min-h-[200px] rounded-xl bg-white p-4 dark:bg-gray-800">
                @include('pages.careers.join')
            </div>
        </div>
    </div>

    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>

    <!-- Reject Modal — same style as other show pages -->
    <div id="rejectTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-4 shadow-md dark:bg-gray-800">
            <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Reject Task</h2>
            <textarea id="rejectReason" class="w-full rounded-lg border border-gray-300 p-3 focus:border-red-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Enter rejection reason..." rows="4"></textarea>
            <div class="mt-4 flex justify-end gap-3">
                <button id="cancelRejectBtn" class="rounded-lg bg-gray-300 px-5 py-2 text-gray-700 hover:bg-gray-400 focus:outline-none dark:bg-gray-600 dark:text-gray-200">Cancel</button>
                <button id="confirmRejectBtn" class="rounded-lg bg-red-600 px-5 py-2 text-white hover:bg-red-700 focus:outline-none">Reject</button>
            </div>
        </div>
    </div>

    <!-- Rollback Modal — same style -->
    <div id="rollbackTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-4 shadow-md dark:bg-gray-800">
            <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Rollback Task</h2>
            <textarea id="rollbackReason" class="w-full rounded-lg border border-gray-300 p-3 focus:border-red-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Enter rollback reason..." rows="4"></textarea>
            <div class="mt-4 flex justify-end gap-3">
                <button id="cancelRollbackBtn" class="rounded-lg bg-gray-300 px-5 py-2 text-gray-700 hover:bg-gray-400 focus:outline-none dark:bg-gray-600 dark:text-gray-200">Cancel</button>
                <button id="confirmRollbackBtn" class="rounded-lg bg-red-600 px-5 py-2 text-white hover:bg-red-700 focus:outline-none">Rollback</button>
            </div>
        </div>
    </div>

    <!-- Remap Modal — same as jobapplicant list -->
    <div id="remapModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
        <div class="w-full max-w-2xl transform rounded-2xl bg-white p-8 shadow-2xl transition-all duration-300 scale-95 opacity-0 dark:bg-gray-800" id="remapModalContent">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200">Remap Applicant</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Old mapping → <strong>Transfer Candidate</strong>. New job apply will be created.</p>
                </div>
                <button id="closeRemapModal" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
            </div>
            <div class="mb-5 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 flex items-start gap-3">
                <span class="mt-0.5 text-amber-500 text-base">📌</span>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-amber-700 uppercase tracking-wide mb-2">Current Job Applied</p>
                    <div class="grid grid-cols-2 gap-x-6 gap-y-1 text-sm">
                        <div><span class="text-sm text-gray-400">Job ID</span><p class="font-medium text-gray-800 dark:text-gray-200">{{ $career->docidposting ?? '—' }}</p></div>
                        <div><span class="text-sm text-gray-400">Job Title</span><p class="font-medium text-gray-800 dark:text-gray-200">{{ $career->job_title ?? '—' }}</p></div>
                        <div><span class="text-sm text-gray-400">Company</span><p class="text-gray-700 dark:text-gray-300">{{ $career->cpnyid ?? '—' }}</p></div>
                        <div><span class="text-sm text-gray-400">Division</span><p class="text-gray-700 dark:text-gray-300">{{ $career->division_name ?? $career->division_id ?? '—' }}</p></div>
                        <div class="col-span-2"><span class="text-sm text-gray-400">Department</span><p class="text-gray-700 dark:text-gray-300">{{ $career->departementid ?? '—' }}</p></div>
                    </div>
                </div>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-300">Select New Job Posting</label>
                <select id="remapJobSelect" style="width:100%"><option value="">Select Job Posting</option></select>
            </div>
            <div class="flex justify-end gap-3">
                <button id="closeRemapModalBtn" class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-gray-400 dark:border-gray-700">Cancel</button>
                <button id="saveRemap" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">Save Remap</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>lucide.createIcons();</script>
@endpush

<script>
    $(document).ready(function() {
        let docid = "{{ $career->docid }}"; // Ambil docid ke JS
        loadComments(docid);
        window.addEventListener('career-assessment-result-updated', () => loadComments(docid));

        // **Fungsi untuk Memuat Komentar**
        function loadComments(docid) {
            console.log("Loading comments for Doc ID:", docid);
            let commentList = $('#commentList');
            commentList.html('<p class="text-gray-500 italic dark:text-gray-400">Loading comments...</p>');

            $.ajax({
                url: `/career/${docid}/comments`,
                type: 'GET',
                success: function(response) {
                    console.log("Comments Loaded:", response);
                    commentList.empty();

                    if (!response.comments || response.comments.length === 0) {
                        commentList.append(`
                            <div class="flex flex-1 flex-col items-center justify-center gap-2 py-10 text-center">
                                <span class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-indigo-100 to-fuchsia-100 text-2xl dark:from-indigo-500/20 dark:to-fuchsia-500/20">💬</span>
                                <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">No messages yet</p>
                                <p class="text-xs text-gray-400">Start the conversation below.</p>
                            </div>`);
                    } else {
                        const me = @json(auth()->user()->username ?? '');
                        const esc = s => $('<div>').text(s ?? '').html();
                        const palette = ['from-rose-400 to-orange-400', 'from-sky-400 to-indigo-500', 'from-emerald-400 to-teal-500',
                            'from-amber-400 to-pink-500', 'from-violet-400 to-fuchsia-500', 'from-cyan-400 to-blue-500'];
                        const colorFor = n => palette[[...String(n)].reduce((a, c) => a + c.charCodeAt(0), 0) % palette.length];
                        let lastDay = null, lastUser = null;

                        response.comments.forEach(comment => {
                            const m = moment(comment.created_at);
                            const day = m.format('YYYY-MM-DD');
                            const mine = comment.username === me;

                            if (day !== lastDay) {
                                const label = m.isSame(moment(), 'day') ? 'Today' : (m.isSame(moment().subtract(1, 'day'), 'day') ? 'Yesterday' : m.format('D MMM YYYY'));
                                commentList.append(`<div class="my-3 flex items-center gap-3 text-[11px] font-medium uppercase tracking-wide text-gray-400"><span class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></span>${label}<span class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></span></div>`);
                                lastDay = day; lastUser = null;
                            }

                            const first = lastUser !== comment.username;
                            lastUser = comment.username;
                            const initial = esc(String(comment.username || '?').charAt(0).toUpperCase());
                            const avatar = mine ? '' : (first
                                ? `<span class="mb-0.5 flex h-8 w-8 shrink-0 items-center justify-center self-end rounded-full bg-gradient-to-br ${colorFor(comment.username)} text-xs font-bold text-white shadow">${initial}</span>`
                                : '<span class="w-8 shrink-0"></span>');
                            const name = (!mine && first) ? `<p class="mb-0.5 ml-1 text-xs font-semibold text-gray-500 dark:text-gray-400">${esc(comment.username)}</p>` : '';
                            const rej = !!comment.is_reject;
                            const bubble = rej
                                ? `rounded-2xl ${mine ? 'rounded-br-md' : 'rounded-bl-md'} border border-red-300 bg-red-50 text-red-800 shadow-sm shadow-red-500/10 dark:border-red-500/40 dark:bg-red-500/10 dark:text-red-200`
                                : (mine
                                    ? 'rounded-2xl rounded-br-md bg-gradient-to-br from-indigo-500 to-fuchsia-500 text-white shadow-md shadow-indigo-500/20'
                                    : 'rounded-2xl rounded-bl-md border border-gray-200 bg-white text-gray-800 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100');
                            const rejTag = rej ? '<p class="mb-1 flex items-center gap-1 text-[11px] font-bold uppercase tracking-wide text-red-600 dark:text-red-300">⛔ Reject reason</p>' : '';
                            const assessmentLabel = comment.assessment_result
                                ? `<p class="mb-1 text-[11px] font-bold uppercase tracking-wide ${comment.assessment_result === 'PASS' ? 'text-emerald-600 dark:text-emerald-300' : 'text-red-600 dark:text-red-300'}">Interview ${comment.assessment_type === 'hc' ? 'HC' : 'User'} · ${comment.assessment_result === 'PASS' ? 'Pass (Lanjut)' : 'Failed (Tidak lanjut)'}</p>`
                                : '';

                            commentList.append(`
                                <div class="drawer-card flex items-end gap-2 ${mine ? 'justify-end' : 'justify-start'} ${first ? 'mt-2' : ''}">
                                    ${avatar}
                                    <div class="max-w-[80%] ${mine ? 'items-end' : 'items-start'} flex flex-col">
                                        ${name}
                                        <div class="${bubble} px-3.5 py-2 text-sm leading-relaxed break-words whitespace-pre-wrap">${rejTag}${assessmentLabel}${esc(comment.message)}</div>
                                        <span class="mx-1 mt-0.5 text-[10px] text-gray-400" title="${m.format('D MMM YYYY HH:mm')}">${m.fromNow()}</span>
                                    </div>
                                </div>
                            `);
                        });

                        // Auto scroll ke bawah setelah load
                        commentList.scrollTop(commentList[0].scrollHeight);
                    }
                },
                error: function(xhr) {
                    console.error("Error fetching comments:", xhr.responseText);
                    commentList.html('<p class="text-red-500 italic">Failed to load comments.</p>');
                }
            });
        }

        // **Fungsi untuk Menambahkan Komentar**
        function addComment() {
            let input = $('#commentInput').val().trim();

            if (input === "") {
                toastr.error("Please enter a comment.");
                return;
            }

            $('#postCommentBtn').prop('disabled', true);

            $.ajax({
                url: `/career/${docid}/comments`,
                type: 'POST',
                data: {
                    docid: docid,
                    comment: input,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log('Comment added successfully:', response);

                    if (response.status === "success") {
                        $('#commentInput').val('');
                        loadComments(docid); // Reload komentar
                    } else {
                        toastr.error(response.message || "Failed to add comment.");
                    }
                },
                error: function(xhr) {
                    console.error("Error adding comment:", xhr);
                    toastr.error("Error: " + (xhr.responseJSON ? xhr.responseJSON.message :
                        "Unknown Error"));
                },
                complete: function() {
                    $('#postCommentBtn').prop('disabled', false);
                }
            });
        }

        // **Event untuk Tombol Post**
        $('#postCommentBtn').click(function() {
            addComment();
        });

        // **Event Enter di Input**
        $('#commentInput').keypress(function(event) {
            if (event.which === 13 && !event.shiftKey) {
                event.preventDefault();
                addComment();
            }
        });
    });
</script>

<script>
    $(document).on("click", "#approveBtn", function() {
        let docid = "{{ $career->docid }}"; // Ambil Task ID dari modal
        approveCareer(docid);
    });

    function approveCareer(docid) {
        let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner

        // Tampilkan spinner di kanan bawah
        $spinner.fadeIn();

        $.ajax({
            url: `/career/${docid}/approve`,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                docid: docid
            },
            success: function(response) {
                if (response.success) {
                    // Update status di UI
                    $("#xstatus").text("Approved")
                        .removeClass()
                        .addClass(
                            "w-full max-w-32 bg-green-300/30 dark:bg-green-300 text-green-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded"
                        );

                    // Tampilkan alert sukses
                    toastr.success("Career approved successfully!");
                    // window.location.href = "/careers";
                    location.reload();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);

                let msg = xhr.responseJSON && xhr.responseJSON.message;
                if (xhr.status === 403) {
                    toastr.error(msg || "You are not authorized to approve this career.");
                } else {
                    toastr.error(msg || "Error: Unable to approve career (HTTP " + xhr.status + ").");
                }
            },
            complete: function() {
                // Sembunyikan spinner setelah request selesai
                $spinner.fadeOut();
            }
        });
    }
</script>


<script>
    $(document).ready(function() {
        let docid = "{{ $career->docid }}";

        // Reject
        $(document).on("click", "#rejectBtn", function() {
            $("#rejectReason").val("");
            $.get(`/career/${docid}/check-reject-permission`, function(res) {
                if (res.canReject) checkApproval(docid, "reject");
                else toastr.warning("You are not allowed to reject at this step.");
            }).fail(function() { toastr.error("Failed to verify reject permission."); });
        });
        $(document).on("click", "#cancelRejectBtn", function() { $("#rejectTaskModal").addClass("hidden"); });
        $(document).on("click", "#confirmRejectBtn", function() {
            let reason = $("#rejectReason").val().trim();
            if (!reason) { toastr.error("Please provide a reason for rejection."); return; }
            let $spinner = $("#loadingSpinnerContainer");
            $("#rejectTaskModal").addClass("hidden");
            $spinner.fadeIn();
            $.post(`/career/${docid}/reject`, { _token: "{{ csrf_token() }}", docid, reason })
                .done(function(r) { if (r.success) { location.reload(); } else { toastr.error("Failed to reject."); } })
                .fail(function(xhr) { toastr.error(xhr.status === 403 ? "You can't reject!" : "Error rejecting."); })
                .always(function() { $spinner.fadeOut(); });
        });

        // Rollback
        $(document).on("click", ".rollbackBtn", function() {
            $("#rollbackReason").val("");
            $.get(`/career/${docid}/check-rollback-permission`, function(res) {
                if (res.canRollback) checkApproval(docid, "rollback");
                else toastr.warning(res.message || "You are not allowed to rollback at this step.");
            }).fail(function() { toastr.error("Failed to verify rollback permission."); });
        });
        $(document).on("click", "#cancelRollbackBtn", function() { $("#rollbackTaskModal").addClass("hidden"); });
        $(document).on("click", "#confirmRollbackBtn", function() {
            let reason = $("#rollbackReason").val().trim();
            if (!reason) { toastr.error("Please provide a reason for rollback."); return; }
            let $spinner = $("#loadingSpinnerContainer");
            $("#rollbackTaskModal").addClass("hidden");
            $spinner.fadeIn();
            $.post(`/career/${docid}/rollback`, { _token: "{{ csrf_token() }}", docid, reason })
                .done(function(r) { if (r.success) { location.reload(); } else { toastr.error("Failed to rollback."); } })
                .fail(function(xhr) { toastr.error(xhr.status === 403 ? "You can't rollback!" : "Error rolling back."); })
                .always(function() { $spinner.fadeOut(); });
        });

        // checkApproval
        function checkApproval(docid, action) {
            $.get(`/career/${docid}/check-approval/${action}`, function(res) {
                if (res.canPerformAction) {
                    if (action === "reject") { $("#rejectTaskModal").removeClass("hidden").css("z-index", "60"); }
                    else if (action === "rollback") { $("#rollbackTaskModal").removeClass("hidden").css("z-index", "60"); }
                } else {
                    toastr.error("You are not authorized to " + action + " this career.");
                }
            }).fail(function() { toastr.error("Error checking approval status."); });
        }

        // Remap — same functions as jobapplicant list
        function openRemapModal() {
            $('#remapModal').removeClass('hidden').addClass('flex');
            setTimeout(() => { $('#remapModalContent').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100'); }, 10);
        }
        function closeRemapModal() {
            $('#remapModalContent').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
            setTimeout(() => { $('#remapModal').addClass('hidden').removeClass('flex'); }, 200);
        }

        $('#remapBtn').on('click', function() {
            let $sel = $('#remapJobSelect');
            if ($sel.find('option').length <= 1) {
                if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
                $.get("{{ route('jobposting.list') }}", function(data) {
                    $sel.empty().append('<option value="">Select Job Posting</option>');
                    data.forEach(jp => $sel.append(`<option value="${jp.docid}">${jp.job_name || jp.docid}</option>`));
                    $sel.select2({ dropdownParent: $('#remapModal'), placeholder: '🔍 Search Job Posting...', width: '100%', allowClear: true });
                    openRemapModal();
                });
            } else { openRemapModal(); }
        });

        $('#closeRemapModal, #closeRemapModalBtn').on('click', closeRemapModal);

        $('#saveRemap').on('click', function() {
            let newJobid = $('#remapJobSelect').val();
            if (!newJobid) { toastr.error('Please select a job posting.'); return; }
            $(this).prop('disabled', true).text('Saving...');
            $.post("{{ route('jobapplicant.remap') }}", { apply_id: @json($hash), new_jobid: newJobid, _token: "{{ csrf_token() }}" })
                .done(function() { toastr.success('Remapped!'); closeRemapModal(); setTimeout(() => location.reload(), 800); })
                .fail(function(xhr) { toastr.error(xhr.responseJSON?.error || 'Failed to remap.'); })
                .always(function() { $('#saveRemap').prop('disabled', false).text('Save Remap'); });
        });
    });
</script>
