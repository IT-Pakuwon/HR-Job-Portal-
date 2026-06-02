@props([
    'title'       => 'KPI Metric',
    'subtitle'    => '',
    'value'       => '0',
    'target'      => null,
    'unit'        => '',
    'trend'       => null,
    'trendUp'     => true,
    'description' => 'vs target',
    'color'       => 'green',
    'icon'        => null,
    'barId'       => '',
    'valueId'     => '',
])

@php
    $hex = [
        'violet' => ['#8B5CF6', '#7C3AED', 'rgba(139,92,246,0.12)', '#EDE9FE'],
        'blue'   => ['#3B82F6', '#06B6D4', 'rgba(59,130,246,0.12)',  '#DBEAFE'],
        'green'  => ['#10B981', '#0D9488', 'rgba(16,185,129,0.12)',  '#D1FAE5'],
        'orange' => ['#F59E0B', '#D97706', 'rgba(245,158,11,0.12)',  '#FEF3C7'],
        'red'    => ['#EF4444', '#F43F5E', 'rgba(239,68,68,0.12)',   '#FEE2E2'],
        'pink'   => ['#EC4899', '#C026D3', 'rgba(236,72,153,0.12)',  '#FCE7F3'],
        'cyan'   => ['#06B6D4', '#3B82F6', 'rgba(6,182,212,0.12)',   '#CFFAFE'],
    ];
    $c = $hex[$color] ?? $hex['green'];

    $trendPos = $trend && $trendUp;
    $trendNeg = $trend && !$trendUp;

    $numValue  = is_numeric($value)  ? (float)$value  : null;
    $numTarget = is_numeric($target) ? (float)$target : null;
    $pct = ($numTarget && $numTarget > 0 && $numValue !== null)
        ? min(100, round(($numValue / $numTarget) * 100))
        : null;
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:shadow-lg dark:border-slate-700/60 dark:bg-slate-900']) }}>

    <div class="absolute inset-x-0 top-0 h-0.75"
         style="background: linear-gradient(to right, {{ $c[0] }}, {{ $c[1] }})"></div>

    <div class="p-5">

        {{-- Header row --}}
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                @if($subtitle)
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                        {{ $subtitle }}
                    </p>
                @endif

                {{-- Value --}}
                <div class="mt-2 flex items-baseline gap-1">
                    <p @if($valueId) id="{{ $valueId }}" @endif
                       class="text-3xl font-extrabold tracking-tight tabular-nums text-slate-900 dark:text-white">
                        {{ $value }}
                    </p>
                    @if($unit)
                        <span class="text-sm font-semibold text-slate-400 dark:text-slate-500">{{ $unit }}</span>
                    @endif
                </div>

                <p class="mt-0.5 truncate text-sm font-semibold text-slate-600 dark:text-slate-300">{{ $title }}</p>
            </div>

            @if($icon)
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl"
                     style="background: {{ $c[2] }}">
                    <span style="color: {{ $c[0] }}; font-size: 1.375rem;">{!! $icon !!}</span>
                </div>
            @endif
        </div>

        {{-- Trend badge --}}
        @if($trend)
            <div class="mt-3 flex items-center gap-2">
                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold
                    {{ $trendPos ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400' }}">
                    @if($trendPos)
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                        </svg>
                    @else
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    @endif
                    {{ $trend }}
                </span>
                @if($description)
                    <span class="text-xs text-slate-400 dark:text-slate-500">{{ $description }}</span>
                @endif
            </div>
        @endif

        {{-- Target progress bar --}}
        @if($target !== null)
            <div class="mt-4 border-t border-slate-100 pt-3.5 dark:border-slate-700/60">
                <div class="mb-1.5 flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                        Target {{ $numTarget !== null ? number_format($numTarget) . ($unit ? ' '.$unit : '') : $target }}
                    </span>
                    @if($pct !== null)
                        <span @if($barId) id="{{ $barId }}-pct" @endif
                              class="text-xs font-extrabold text-slate-700 dark:text-slate-200">
                            {{ $pct }}%
                        </span>
                    @endif
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700/60">
                    <div @if($barId) id="{{ $barId }}" @endif
                         class="h-full rounded-full transition-all duration-700 ease-out"
                         style="width:{{ $pct ?? 0 }}%;background:linear-gradient(to right,{{ $c[0] }},{{ $c[1] }})">
                    </div>
                </div>
            </div>
        @endif

    </div>

</div>
