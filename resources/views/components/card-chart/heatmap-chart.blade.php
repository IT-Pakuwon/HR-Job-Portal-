@props([
    'title'      => 'Heatmap Chart',
    'subtitle'   => '',
    'chartId'    => 'heatmap-' . uniqid(),
    'height'     => 280,
    'color'      => 'blue',
    'series'     => [],
    'categories' => [],
])

@php
    $hex = ['violet'=>['#8B5CF6','#7C3AED'],'blue'=>['#3B82F6','#06B6D4'],'green'=>['#10B981','#0D9488'],'orange'=>['#F59E0B','#D97706'],'red'=>['#EF4444','#F43F5E'],'pink'=>['#EC4899','#C026D3'],'cyan'=>['#06B6D4','#3B82F6']];
    $c = $hex[$color] ?? $hex['blue'];
    $config = ['series' => is_string($series) ? json_decode($series,true) : $series, 'categories' => is_string($categories) ? json_decode($categories,true) : $categories, 'height' => (int)$height, 'color' => $color];
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:shadow-lg dark:border-slate-700/60 dark:bg-slate-900']) }}>
    <div class="absolute inset-x-0 top-0 h-0.75" style="background:linear-gradient(to right,{{ $c[0] }},{{ $c[1] }})"></div>

    <div class="flex items-start justify-between px-5 pt-5 pb-1">
        <div>
            @if($subtitle)<p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">{{ $subtitle }}</p>@endif
            <h3 class="mt-0.5 text-base font-bold text-slate-800 dark:text-white">{{ $title }}</h3>
        </div>
    </div>

    <div class="px-2 pb-3 pt-1">
        <div id="{{ $chartId }}" data-chart-type="heatmap" data-config="{{ json_encode($config) }}"></div>
    </div>
</div>

@once
    @push('scripts')
        <script src="{{ asset('assets/js/card-chart/heatmap-chart.js') }}"></script>
    @endpush
@endonce
