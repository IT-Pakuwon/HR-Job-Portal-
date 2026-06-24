<div class="max-w-9xl mx-auto w-full overflow-hidden rounded-lg bg-white   dark:bg-gray-800 ">

        {{-- ── Header ───────────────────────────────────────────────── --}}
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700/60">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">{{ $jobposting->docid }}</p>
                    <h2 class="mt-0.5 text-base font-bold text-gray-900 dark:text-white">{{ $jobposting->job_title }}</h2>
                </div>
                <div class="flex shrink-0 items-center gap-1.5 pt-0.5">
                    @if ($jobposting->job_type)
                        <span class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $jobposting->job_type }}</span>
                    @endif
                    @if ($jobposting->state_position)
                        <span class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $jobposting->state_position }}</span>
                    @endif
                </div>
            </div>

            {{-- Inline metadata --}}
            <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1.5">
                <div class="flex items-center gap-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Company</span>
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $jobposting->cpnyid ?: '—' }}</span>
                </div>
                <span class="h-3 w-px bg-gray-200 dark:bg-gray-600"></span>
                <div class="flex items-center gap-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Department</span>
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $jobposting->departementid ?: '—' }}</span>
                </div>
                <span class="h-3 w-px bg-gray-200 dark:bg-gray-600"></span>
                <div class="flex items-center gap-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Superior</span>
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $jobposting->immediate_superior ?: '—' }}</span>
                </div>
                <span class="h-3 w-px bg-gray-200 dark:bg-gray-600"></span>
                <div class="flex items-center gap-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Level</span>
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $jobposting->job_level ?: '—' }}</span>
                </div>
            </div>
        </div>

        {{-- ── Tabs ─────────────────────────────────────────────────── --}}
        <div x-data="{ activeTab: 'Responsibilities' }">

            <div class="flex items-center border-b border-gray-100 px-4 py-2.5 dark:border-gray-700/60">
                <div class="flex items-center gap-0.5 rounded-lg bg-gray-200 p-1 dark:bg-gray-900">
                    <button @click="activeTab = 'Responsibilities'"
                        :class="activeTab === 'Responsibilities'
                            ? 'bg-white text-gray-900   dark:bg-gray-700 dark:text-white'
                            : 'text-gray-500 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300'"
                        class="rounded-lg px-4 py-1.5 text-xs font-semibold transition-all duration-150 focus:outline-none">
                        Responsibilities
                    </button>
                    <button @click="activeTab = 'Qualification'"
                        :class="activeTab === 'Qualification'
                            ? 'bg-white text-gray-900   dark:bg-gray-700 dark:text-white'
                            : 'text-gray-500 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300'"
                        class="rounded-lg px-4 py-1.5 text-xs font-semibold transition-all duration-150 focus:outline-none">
                        Qualification
                    </button>
                </div>
            </div>

            <div class="px-5 py-4">

                {{-- Responsibilities --}}
                <div x-show="activeTab === 'Responsibilities'"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0">
                    @if ($jobres->isEmpty())
                        <p class="py-6 text-center text-xs italic text-gray-400">No job responsibilities listed.</p>
                    @else
                        <ul class="space-y-0.5">
                            @foreach ($jobres as $jr)
                                <li class="flex items-start gap-3 rounded-lg px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $jr->job_responsibilities_descr }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                {{-- Qualification --}}
                <div x-show="activeTab === 'Qualification'" x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0">

                    @if ($jobqua->isEmpty())
                        <p class="py-6 text-center text-xs italic text-gray-400">No job qualifications listed.</p>
                    @else
                        <ul class="space-y-0.5">
                            @foreach ($jobqua as $jq)
                                <li class="flex items-start gap-3 rounded-lg px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $jq->job_qualification_descr }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

            </div>
        </div>

</div>
