@props([
    'title'       => 'Customer Zone Movement',
    'subtitle'    => '',
    'chartId'     => 'sankey-' . uniqid(),
    'height'      => 380,
    'color'       => 'blue',
    'times'       => [],
    'zones'       => [],
    'counts'      => [],
    'transitions' => [],
])

@php
    $hex = ['violet'=>['#8B5CF6','#7C3AED'],'blue'=>['#3B82F6','#06B6D4'],'green'=>['#10B981','#0D9488'],'orange'=>['#F59E0B','#D97706'],'red'=>['#EF4444','#F43F5E'],'pink'=>['#EC4899','#C026D3'],'cyan'=>['#06B6D4','#3B82F6']];
    $c = $hex[$color] ?? $hex['blue'];
    $config = [
        'height'      => (int)$height,
        'color'       => $color,
        'times'       => is_string($times)       ? json_decode($times, true)       : $times,
        'zones'       => is_string($zones)       ? json_decode($zones, true)       : $zones,
        'counts'      => is_string($counts)      ? json_decode($counts, true)      : $counts,
        'transitions' => is_string($transitions) ? json_decode($transitions, true) : $transitions,
    ];
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:shadow-lg dark:border-slate-700/60 dark:bg-slate-900']) }}>
    <div class="absolute inset-x-0 top-0 h-0.75" style="background:linear-gradient(to right,{{ $c[0] }},{{ $c[1] }})"></div>

    <div class="flex items-start justify-between px-5 pt-5 pb-2">
        <div>
            @if($subtitle)<p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">{{ $subtitle }}</p>@endif
            <h3 class="mt-0.5 text-base font-bold text-slate-800 dark:text-white">{{ $title }}</h3>
        </div>
        <span class="mt-1 inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-semibold text-slate-500 dark:bg-slate-700/60 dark:text-slate-300">
            <svg class="h-3 w-3" fill="none" viewBox="0 0 16 16"><path d="M2 8h4m4 0h4M6 5l2 3-2 3M10 5l2 3-2 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Sankey Flow
        </span>
    </div>

    {{-- Chart container — JS writes SVG directly into this div --}}
    <div id="{{ $chartId }}"
         data-chart-type="sankey"
         data-config="{{ json_encode($config) }}"
         style="min-height:{{ $height }}px">
    </div>
</div>

@once
    @push('scripts')
        <script src="{{ asset('assets/js/card-chart/sankey-chart.js') }}"></script>
    @endpush
@endonce
