@php
    $infoTiles = [
        ['label' => 'Company', 'value' => $companyName ?? $jobposting->cpnyid ?? null, 'tone' => 'indigo',
            'icon' => 'M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M9 9v.01M9 12v.01M9 15v.01M9 18v.01'],
        ['label' => 'Department', 'value' => $departmentName ?? $jobposting->departementid ?? null, 'tone' => 'sky',
            'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
        ['label' => 'Immediate Superior', 'value' => ($superiorName ?? null) ?: ($jobposting->immediate_superior ?: null), 'tone' => 'violet',
            'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
        ['label' => 'Job Level', 'value' => $jobposting->job_level ?: null, 'tone' => 'amber',
            'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
    ];
    $tones = [
        'indigo' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300',
        'sky'    => 'bg-sky-50 text-sky-600 dark:bg-sky-500/15 dark:text-sky-300',
        'violet' => 'bg-violet-50 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300',
        'amber'  => 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300',
    ];
@endphp

<div class="max-w-9xl mx-auto flex w-full flex-col gap-4">

    {{-- ── Hero ─────────────────────────────────────────────────── --}}
    <div class="relative overflow-hidden rounded-2xl bg-indigo-600 p-6 text-white shadow-lg shadow-indigo-500/20">
        <div class="pointer-events-none absolute -right-10 -top-12 h-48 w-48 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -bottom-16 right-32 h-40 w-40 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute left-1/3 top-0 h-24 w-24 rounded-full bg-white/5"></div>

        <div class="relative flex flex-wrap items-start justify-between gap-4">
            <div class="flex min-w-0 items-start gap-4">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/20 text-white shadow-inner backdrop-blur">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <span class="inline-flex items-center rounded-full bg-white/20 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-widest backdrop-blur">{{ $jobposting->docid }}</span>
                    <h2 class="mt-1.5 text-2xl font-bold leading-tight tracking-tight">{{ $jobposting->job_title }}</h2>
                    <p class="mt-1 text-sm text-white/80">
                        {{ $companyName ?? $jobposting->cpnyid ?? '—' }}
                        @if ($departmentName ?? $jobposting->departementid) · {{ $departmentName ?? $jobposting->departementid }} @endif
                    </p>
                </div>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                @if ($jobposting->job_type)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/20 px-3 py-1 text-xs font-semibold backdrop-blur">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>{{ $jobposting->job_type }}
                    </span>
                @endif
                @if ($jobposting->state_position)
                    <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-bold text-indigo-600 shadow">{{ $jobposting->state_position }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Info tiles ───────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($infoTiles as $t)
            <div class="group flex items-center gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-gray-700/60 dark:bg-gray-800">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $tones[$t['tone']] }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $t['icon'] }}"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">{{ $t['label'] }}</p>
                    <p class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100" title="{{ $t['value'] }}">{{ $t['value'] ?: '—' }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── Responsibilities + Qualification (side by side) ───────── --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

        {{-- Responsibilities --}}
        <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
            <header class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5 dark:border-gray-700/60">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </span>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Responsibilities</h3>
                </div>
                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300">{{ $jobres->count() }}</span>
            </header>

            <div class="p-3">
                @if ($jobres->isEmpty())
                    <div class="flex flex-col items-center gap-2 py-10 text-center">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-xl dark:bg-gray-700">📋</span>
                        <p class="text-sm text-gray-400">No job responsibilities listed.</p>
                    </div>
                @else
                    <ol class="space-y-1">
                        @foreach ($jobres as $jr)
                            <li class="flex items-start gap-3 rounded-xl px-3 py-2.5 transition hover:bg-emerald-50/60 dark:hover:bg-white/5">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">{{ $loop->iteration }}</span>
                                <span class="text-sm leading-relaxed text-gray-700 dark:text-gray-300">{{ $jr->job_responsibilities_descr }}</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </section>

        {{-- Qualification --}}
        <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
            <header class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5 dark:border-gray-700/60">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                    </span>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Qualification</h3>
                </div>
                <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-bold text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">{{ $jobqua->count() }}</span>
            </header>

            <div class="p-3">
                @if ($jobqua->isEmpty())
                    <div class="flex flex-col items-center gap-2 py-10 text-center">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-xl dark:bg-gray-700">🎓</span>
                        <p class="text-sm text-gray-400">No job qualifications listed.</p>
                    </div>
                @else
                    <ol class="space-y-1">
                        @foreach ($jobqua as $jq)
                            <li class="flex items-start gap-3 rounded-xl px-3 py-2.5 transition hover:bg-indigo-50/60 dark:hover:bg-white/5">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">{{ $loop->iteration }}</span>
                                <span class="text-sm leading-relaxed text-gray-700 dark:text-gray-300">{{ $jq->job_qualification_descr }}</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </section>

    </div>
</div>
