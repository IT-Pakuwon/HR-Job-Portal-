@props([
    'title'    => 'Progress Overview',
    'subtitle' => '',
    'color'    => 'blue',
    'items'    => [],
])

{{--
  Each item: ['label' => string, 'value' => number (0-100), 'badge' => string (opt), 'color' => string (opt)]
--}}

@php
    $hex = [
        'violet' => ['#8B5CF6', '#7C3AED'],
        'blue'   => ['#3B82F6', '#06B6D4'],
        'green'  => ['#10B981', '#0D9488'],
        'orange' => ['#F59E0B', '#D97706'],
        'red'    => ['#EF4444', '#F43F5E'],
        'pink'   => ['#EC4899', '#C026D3'],
        'cyan'   => ['#06B6D4', '#3B82F6'],
    ];
    $c = $hex[$color] ?? $hex['blue'];

    $itemColors = [
        'violet' => ['bg' => '#8B5CF6', 'light' => '#EDE9FE'],
        'blue'   => ['bg' => '#3B82F6', 'light' => '#DBEAFE'],
        'green'  => ['bg' => '#10B981', 'light' => '#D1FAE5'],
        'orange' => ['bg' => '#F59E0B', 'light' => '#FEF3C7'],
        'red'    => ['bg' => '#EF4444', 'light' => '#FEE2E2'],
        'pink'   => ['bg' => '#EC4899', 'light' => '#FCE7F3'],
        'cyan'   => ['bg' => '#06B6D4', 'light' => '#CFFAFE'],
    ];

    $sampleItems = [
        ['label' => 'Marketing',   'value' => 82,  'badge' => '82%',  'color' => 'violet'],
        ['label' => 'Engineering', 'value' => 67,  'badge' => '67%',  'color' => 'blue'],
        ['label' => 'Operations',  'value' => 91,  'badge' => '91%',  'color' => 'green'],
        ['label' => 'HR',          'value' => 55,  'badge' => '55%',  'color' => 'orange'],
        ['label' => 'Finance',     'value' => 74,  'badge' => '74%',  'color' => 'cyan'],
    ];

    $rows = (is_string($items) ? json_decode($items, true) : $items) ?: $sampleItems;
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:shadow-lg dark:border-slate-700/60 dark:bg-slate-900']) }}>

    <div class="absolute inset-x-0 top-0 h-0.75"
         style="background: linear-gradient(to right, {{ $c[0] }}, {{ $c[1] }})"></div>

    <div class="px-5 pb-2 pt-5">
        @if($subtitle)
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">{{ $subtitle }}</p>
        @endif
        <h3 class="mt-0.5 text-sm font-bold text-slate-800 dark:text-white">{{ $title }}</h3>
    </div>

    <div class="space-y-3 px-5 pb-5">
        @foreach($rows as $item)
            @php
                $ic  = $item['color'] ?? $color;
                $pal = $itemColors[$ic] ?? $itemColors['blue'];
                $pct = min(100, max(0, (int)($item['value'] ?? 0)));
                $badge = $item['badge'] ?? ($pct . '%');
            @endphp
            <div>
                <div class="mb-1.5 flex items-center justify-between gap-2">
                    <span class="min-w-0 truncate text-xs font-semibold text-slate-700 dark:text-slate-200">
                        {{ $item['label'] ?? '' }}
                    </span>
                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold"
                          style="background:{{ $pal['light'] }};color:{{ $pal['bg'] }}">
                        {{ $badge }}
                    </span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700/60">
                    <div class="h-full rounded-full transition-all duration-700 ease-out"
                         style="width:{{ $pct }}%;background:{{ $pal['bg'] }}"></div>
                </div>
            </div>
        @endforeach
    </div>

</div>
