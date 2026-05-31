@props([
    'title'         => 'Metric',
    'subtitle'      => '',
    'value'         => '0',
    'trend'         => null,
    'trendUp'       => true,
    'description'   => 'vs last period',
    'color'         => 'violet',
    'icon'          => null,
])

@php
    $hex = [
        'violet' => ['#8B5CF6','#7C3AED','rgba(139,92,246,0.12)'],
        'blue'   => ['#3B82F6','#06B6D4','rgba(59,130,246,0.12)'],
        'green'  => ['#10B981','#0D9488','rgba(16,185,129,0.12)'],
        'orange' => ['#F59E0B','#D97706','rgba(245,158,11,0.12)'],
        'red'    => ['#EF4444','#F43F5E','rgba(239,68,68,0.12)'],
        'pink'   => ['#EC4899','#C026D3','rgba(236,72,153,0.12)'],
        'cyan'   => ['#06B6D4','#3B82F6','rgba(6,182,212,0.12)'],
    ];
    $c = $hex[$color] ?? $hex['violet'];

    $trendPositive = $trend && $trendUp;
    $trendNegative = $trend && !$trendUp;
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:shadow-lg dark:border-slate-700/60 dark:bg-slate-900']) }}>

    {{-- Gradient accent top bar --}}
    <div class="absolute inset-x-0 top-0 h-0.75"
         style="background: linear-gradient(to right, {{ $c[0] }}, {{ $c[1] }})"></div>

    <div class="p-5">

        <div class="flex items-start justify-between gap-3">

            <div class="min-w-0 flex-1">

                @if($subtitle)
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                        {{ $subtitle }}
                    </p>
                @endif

                <p class="mt-2.5 text-3xl font-extrabold tracking-tight text-slate-900 tabular-nums dark:text-white">
                    {{ $value }}
                </p>

                <p class="mt-1 truncate text-sm font-semibold text-slate-600 dark:text-slate-300">
                    {{ $title }}
                </p>

            </div>

            @if($icon)
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl"
                     style="background: {{ $c[2] }}">
                    <span style="color: {{ $c[0] }}; font-size: 1.375rem;">
                        {!! $icon !!}
                    </span>
                </div>
            @endif

        </div>

        @if($trend)
            <div class="mt-4 flex items-center gap-2">

                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold
                    {{ $trendPositive ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400' }}">

                    @if($trendPositive)
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                        </svg>
                    @else
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    @endif

                    {{ $trend }}
                </span>

                @if($description)
                    <span class="text-xs text-slate-400 dark:text-slate-500">{{ $description }}</span>
                @endif

            </div>
        @endif

    </div>

</div>
