<div class="max-w-9xl mx-auto w-full overflow-hidden rounded-lg bg-white   dark:bg-gray-800 ">

    {{-- ── Document header grid ────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-2 border-b border-gray-100 px-4 py-2.5 dark:border-gray-700/60 lg:grid-cols-4">
        <div class="rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-700/30">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Document</p>
            <div class="flex items-center gap-2">
                <p class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $career->docid ?? '-' }}</p>
                @if($remapped_from || $remapped_to)
                    <span class="inline-flex items-center gap-1 rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-semibold text-violet-700 dark:bg-violet-900/40 dark:text-violet-300">
                        🔄 Remapped
                    </span>
                @endif
            </div>
            <p class="text-[10px] text-gray-400">{{ $career->apply_date ?? '' }}</p>
            @if($remapped_from)
                <p class="mt-0.5 text-[10px] text-violet-500">from {{ $remapped_from->jobid }}
                    @if($remapped_from->job_title) — {{ $remapped_from->job_title }}@endif
                </p>
            @elseif($remapped_to)
                <p class="mt-0.5 text-[10px] text-violet-500">to {{ $remapped_to->jobid }}
                    @if($remapped_to->job_title) — {{ $remapped_to->job_title }}@endif
                </p>
            @endif
        </div>
        <div class="rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-700/30">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Company</p>
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $career->cpnyid ?? '-' }}</p>
        </div>
        <div class="rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-700/30">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Position</p>
            <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">{{ $career->job_title ?? '-' }}</p>
        </div>
        <div class="rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-700/30">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Applicant</p>
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $career->fullname ?? '-' }}</p>
        </div>
    </div>

    {{-- ── Document Actions ─────────────────────────────────────── --}}
    @if (!in_array($career->status ?? '', ['T', 'C', 'R', 'X']))
    <div class="flex justify-end border-b border-gray-100 px-4 py-2 dark:border-gray-700/60">
        <button id="remapBtn"
            class="inline-flex items-center gap-1.5 rounded-lg border border-violet-200 bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700 transition hover:bg-violet-100 dark:border-violet-700/30 dark:bg-violet-900/20 dark:text-violet-300">
            🔄 Remap Position
        </button>
    </div>
    @endif

    <div x-data="{ subtab: 'step', init: function() { this.$watch('subtab', () => { this.$el.scrollIntoView({ behavior: 'smooth' }); }); } }" class="w-full">

        {{-- ── Sub-tabs + progress ─────────────────────────────────────── --}}
        @php
            $totalSteps   = $jobapplystep->count();
            $approvedCount = $jobapplystep->where('status', 'A')->count();
            $rejectedCount = $jobapplystep->where('status', 'R')->count();
            $progressPct  = $totalSteps > 0 ? round(($approvedCount / $totalSteps) * 100) : 0;
        @endphp
        <div class="flex items-center gap-3 border-b border-gray-100 px-3 py-2.5 dark:border-gray-700/60">

            {{-- Tab tray full width --}}
            <div class="flex flex-1 items-center gap-0.5 rounded-lg bg-gray-200 p-1 dark:bg-gray-900">
                <button @click="subtab = 'step'"
                    :class="subtab === 'step'
                        ? 'bg-white text-gray-900 shadow dark:bg-gray-700 dark:text-white'
                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300'"
                    class="flex-1 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150 focus:outline-none">
                    Workflow
                </button>
                @if ($canAccessSchedule)
                <button @click="subtab = 'schedule'"
                    :class="subtab === 'schedule'
                        ? 'bg-white text-gray-900 shadow dark:bg-gray-700 dark:text-white'
                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300'"
                    class="flex-1 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150 focus:outline-none">
                    Schedule
                </button>
                @endif
                @if ($canAccessChecklist)
                <button @click="subtab = 'checklist'"
                    :class="subtab === 'checklist'
                        ? 'bg-white text-gray-900 shadow dark:bg-gray-700 dark:text-white'
                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300'"
                    class="flex-1 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150 focus:outline-none">
                    Checklist
                </button>
                @endif
                @if ($canAccessInterviewHC)
                <button @click="subtab = 'assessment'"
                    :class="subtab === 'assessment'
                        ? 'bg-white text-gray-900 shadow dark:bg-gray-700 dark:text-white'
                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300'"
                    class="flex-1 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150 focus:outline-none">
                    Interview HC
                </button>
                @endif
                @if ($canAccessInterviewUser)
                <button @click="subtab = 'assessmentuser'"
                    :class="subtab === 'assessmentuser'
                        ? 'bg-white text-gray-900 shadow dark:bg-gray-700 dark:text-white'
                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300'"
                    class="flex-1 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150 focus:outline-none">
                    Interview User
                </button>
                @endif
                @if ($canAccessPayroll)
                <button @click="subtab = 'payroll'"
                    :class="subtab === 'payroll'
                        ? 'bg-white text-gray-900 shadow dark:bg-gray-700 dark:text-white'
                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300'"
                    class="flex-1 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150 focus:outline-none">
                    Payroll
                </button>
                @endif
                @if ($canAccessJoin)
                <button @click="subtab = 'join'"
                    :class="subtab === 'join'
                        ? 'bg-white text-gray-900 shadow dark:bg-gray-700 dark:text-white'
                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300'"
                    class="flex-1 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150 focus:outline-none">
                    Join
                </button>
                @endif
            </div>

            {{-- Progress summary --}}
            <div class="flex items-center gap-2.5">
                <span class="text-[10px] font-semibold tabular-nums text-gray-400 dark:text-gray-500">
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
                    $step3 = $jobapplystep->firstWhere('step_order', 3);
                    $step5 = $jobapplystep->firstWhere('step_order', 5);
                    $step3Approved = $step3 ? $step3->status === 'A' : true;
                    $step5Approved = $step5 ? $step5->status === 'A' : true;
                @endphp

                <div class="grid grid-cols-12">

                    {{-- ── Step list ────────────────────────────────────── --}}
                    <div class="col-span-12 lg:col-span-6">
                        @foreach ($jobapplystep as $step)
                            @php
                                $order         = (int) $step->step_order;
                                $blockedByGate = (!$step3Approved && $order > 3) || (!$step5Approved && $order > 5);
                                $isGateStep    = in_array($order, [3, 5], true);
                                $shouldHideBtn = $isGateStep || $blockedByGate;
                                $isActive      = $step->status === 'P' && !$shouldHideBtn && !$firstPendingShown;
                                $isFuture      = $step->status === 'P' && ($shouldHideBtn || $firstPendingShown);
                            @endphp

                            <div class="flex items-center gap-4 border-b border-gray-100 px-5 py-3.5 transition-colors last:border-0 dark:border-gray-700/40
                                @if($isActive) bg-gray-50 dark:bg-gray-700/20
                                @elseif($isFuture) opacity-40
                                @endif">

                                {{-- Status icon --}}
                                <div class="shrink-0">
                                    @if($step->status === 'A')
                                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500">
                                            <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    @elseif($step->status === 'R')
                                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-red-500">
                                            <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </div>
                                    @elseif($isActive)
                                        <div class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-gray-900 dark:border-white">
                                            <span class="h-2 w-2 rounded-full bg-gray-900 dark:bg-white"></span>
                                        </div>
                                    @else
                                        <div class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-gray-200 dark:border-gray-600">
                                            <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500">{{ $order }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Step name + meta --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm @if($isActive) font-semibold text-gray-900 dark:text-white @elseif($step->status === 'A') font-medium text-gray-600 dark:text-gray-300 @else font-normal text-gray-500 @endif">
                                        {{ $step->step_descr }}
                                    </p>
                                    @if($step->aprvusername || $step->aprvuserdate)
                                        <p class="mt-0.5 text-xs text-gray-400">
                                            {{ $step->aprvusername }}@if($step->aprvusername && $step->aprvuserdate) &nbsp;·&nbsp; @endif{{ $step->aprvuserdate }}
                                        </p>
                                    @endif
                                </div>

                                {{-- Action / Status --}}
                                <div class="shrink-0 flex items-center gap-2">
                                    @if ($career->status === 'T')
                                        {{-- Old apply (transferred) — read-only --}}
                                        @if ($step->status === 'A')
                                            <span class="rounded-full bg-green-100 px-2.5 py-1 text-[11px] font-semibold text-green-600">Approved</span>
                                        @elseif ($step->status === 'R')
                                            <span class="rounded-full bg-red-100 px-2.5 py-1 text-[11px] font-semibold text-red-500">Rejected</span>
                                        @elseif ($step->status === 'X')
                                            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-400">Cancelled</span>
                                        @endif
                                    @else
                                        @if ($step->status === 'P')
                                            @if (!$shouldHideBtn && !$firstPendingShown)
                                                @php $firstPendingShown = true; @endphp
                                                <button id="approveBtn"
                                                    class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                    Approve
                                                </button>
                                                <button id="rejectBtn"
                                                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3.5 py-1.5 text-xs font-semibold text-gray-500 transition hover:border-red-300 hover:text-red-500 focus:outline-none active:scale-95 dark:border-gray-600 dark:text-gray-400">
                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    Reject
                                                </button>
                                            @endif
                                        @elseif ($step->status === 'A')
                                            <button class="rollbackBtn inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3.5 py-1.5 text-xs font-semibold text-gray-500 transition hover:border-amber-300 hover:text-amber-600 focus:outline-none active:scale-95 dark:border-gray-600 dark:text-gray-400 dark:hover:border-amber-500 dark:hover:text-amber-400">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
                                                Rollback
                                            </button>
                                        @elseif ($step->status === 'R')
                                            <button class="rollbackBtn inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3.5 py-1.5 text-xs font-semibold text-gray-500 transition hover:border-amber-300 hover:text-amber-600 focus:outline-none active:scale-95 dark:border-gray-600 dark:text-gray-400 dark:hover:border-amber-500 dark:hover:text-amber-400">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
                                                Rollback
                                            </button>
                                        @elseif ($step->status === 'D')
                                            <span class="rounded-full bg-blue-100 px-2.5 py-1 text-[11px] font-semibold text-blue-600">Revised</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- ── Comments ─────────────────────────────────────── --}}
                    <div class="col-span-12 lg:col-span-6 border-t border-gray-100 dark:border-gray-700/40 lg:col-span-3 lg:border-l lg:border-t-0">
                        <div x-data="{ isOpen: true }">
                            <button class="flex w-full items-center justify-between px-5 py-3.5" @click="isOpen = !isOpen">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Comments</span>
                                <svg x-show="isOpen" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                <svg x-show="!isOpen" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                            </button>
                            <div x-show="isOpen" x-collapse.duration.200ms class="flex flex-col border-t border-gray-100 dark:border-gray-700/40">
                                <div id="commentList" class="flex flex-col space-y-2 px-4 py-3 dark:bg-gray-800" style="max-height:420px;overflow-y:auto;">
                                    <p class="animate-pulse text-center text-xs italic text-gray-400">Loading...</p>
                                </div>
                                <div class="flex gap-2 border-t border-gray-100 p-3 dark:border-gray-700/40">
                                    <input id="commentInput" type="text" placeholder="Write a comment..."
                                        class="flex-1 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs placeholder-gray-400 focus:border-gray-400 focus:bg-white focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <button id="postCommentBtn" type="button"
                                        class="shrink-0 rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-gray-700 active:scale-95 dark:bg-white dark:text-gray-900">
                                        Post
                                    </button>
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

    <div id="loadingSpinnerContainer" class="flex h-16 items-center justify-center pt-8">
        <svg class="h-10 w-10 animate-spin text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
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
        <div class="w-full max-w-2xl transform rounded-2xl bg-white p-8 shadow-2xl transition-all duration-300 scale-95 opacity-0" id="remapModalContent">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Remap Applicant</h2>
                    <p class="text-sm text-gray-500">Old mapping → <strong>Transfer Candidate</strong>. New job apply will be created.</p>
                </div>
                <button id="closeRemapModal" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
            </div>
            <div class="mb-5 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 flex items-start gap-3">
                <span class="mt-0.5 text-amber-500 text-base">📌</span>
                <div class="flex-1">
                    <p class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-2">Current Job Applied</p>
                    <div class="grid grid-cols-2 gap-x-6 gap-y-1 text-sm">
                        <div><span class="text-xs text-gray-400">Job ID</span><p class="font-medium text-gray-800">{{ $career->docidposting ?? '—' }}</p></div>
                        <div><span class="text-xs text-gray-400">Job Title</span><p class="font-medium text-gray-800">{{ $career->job_title ?? '—' }}</p></div>
                        <div><span class="text-xs text-gray-400">Company</span><p class="text-gray-700">{{ $career->cpnyid ?? '—' }}</p></div>
                        <div><span class="text-xs text-gray-400">Division</span><p class="text-gray-700">{{ $career->division_name ?? $career->division_id ?? '—' }}</p></div>
                        <div class="col-span-2"><span class="text-xs text-gray-400">Department</span><p class="text-gray-700">{{ $career->departementid ?? '—' }}</p></div>
                    </div>
                </div>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Select New Job Posting</label>
                <select id="remapJobSelect" style="width:100%"><option value="">Select Job Posting</option></select>
            </div>
            <div class="flex justify-end gap-3">
                <button id="closeRemapModalBtn" class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
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

        // **Fungsi untuk Memuat Komentar**
        function loadComments(docid) {
            console.log("Loading comments for Doc ID:", docid);
            let commentList = $('#commentList');
            commentList.html('<p class="text-gray-500 italic">Loading comments...</p>');

            $.ajax({
                url: `/career/${docid}/comments`,
                type: 'GET',
                success: function(response) {
                    console.log("Comments Loaded:", response);
                    commentList.empty();

                    if (!response.comments || response.comments.length === 0) {
                        commentList.append(
                            '<p class="text-gray-500 text-sm italic">No comments yet. Be the first to comment!</p>'
                        );
                    } else {
                        response.comments.forEach(comment => {
                            let timeAgo = moment(comment.created_at).fromNow();

                            commentList.append(`
                                <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2 border border-gray-300 dark:border-gray-700">
                                    <p class=" text-sm  font-semibold">${comment.username}
                                        <span class=" text-sm  text-gray-500">(${timeAgo})</span>
                                    </p>
                                    <p class="text-gray-800 dark:text-gray-200">${comment.message}</p>
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

            $('#postCommentBtn').prop('disabled', true).text('Posting...');

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
                    $('#postCommentBtn').prop('disabled', false).text('Post 🚀');
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

                if (xhr.status === 403) {
                    toastr.error("You are not authorized to approve this career.");
                } else {
                    //    toastr.error("Error: Unable to approve career.");
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
            $.post(`/career/${docid}/reject`, { _token: "{{ csrf_token() }}", docid, reason })
                .done(function(r) { if (r.success) { location.reload(); } else { toastr.error("Failed to reject."); } })
                .fail(function(xhr) { toastr.error(xhr.status === 403 ? "You can't reject!" : "Error rejecting."); });
        });

        // Rollback
        $(document).on("click", ".rollbackBtn", function() {
            $("#rollbackReason").val("");
            $.get(`/career/${docid}/check-rollback-permission`, function(res) {
                if (res.canRollback) checkApproval(docid, "rollback");
                else toastr.warning("You are not allowed to rollback at this step.");
            }).fail(function() { toastr.error("Failed to verify rollback permission."); });
        });
        $(document).on("click", "#cancelRollbackBtn", function() { $("#rollbackTaskModal").addClass("hidden"); });
        $(document).on("click", "#confirmRollbackBtn", function() {
            let reason = $("#rollbackReason").val().trim();
            if (!reason) { toastr.error("Please provide a reason for rollback."); return; }
            $.post(`/career/${docid}/rollback`, { _token: "{{ csrf_token() }}", docid, reason })
                .done(function(r) { if (r.success) { location.reload(); } else { toastr.error("Failed to rollback."); } })
                .fail(function(xhr) { toastr.error(xhr.status === 403 ? "You can't rollback!" : "Error rolling back."); });
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
<style>
    /* Styling untuk loading spinner di kanan bawah */
    #loadingSpinnerContainer {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: rgba(0, 0, 0, 0.7);
        padding: 10px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 50px;
        height: 50px;
        z-index: 1000;
        display: none;
        /* Tersembunyi saat tidak digunakan */
    }

    #loadingSpinnerContainer svg {
        width: 30px;
        height: 30px;
        color: white;
    }
</style>

