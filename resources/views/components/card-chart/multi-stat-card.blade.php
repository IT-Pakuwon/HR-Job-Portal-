@props([
    'title'    => '',
    'subtitle' => '',
    'color'    => 'violet',
    'cols'     => 2,
    'items'    => [],
])

{{--
  Each item: [
    'label'       => string,
    'value'       => string|number,
    'unit'        => string (opt),
    'trend'       => string (opt),
    'trendUp'     => bool (opt, default true),
    'description' => string (opt),
    'color'       => string (opt),
    'icon'        => string html (opt),
  ]
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
    $c = $hex[$color] ?? $hex['violet'];

    $palette = [
        'violet' => ['#8B5CF6', 'rgba(139,92,246,0.12)'],
        'blue'   => ['#3B82F6', 'rgba(59,130,246,0.12)'],
        'green'  => ['#10B981', 'rgba(16,185,129,0.12)'],
        'orange' => ['#F59E0B', 'rgba(245,158,11,0.12)'],
        'red'    => ['#EF4444', 'rgba(239,68,68,0.12)'],
        'pink'   => ['#EC4899', 'rgba(236,72,153,0.12)'],
        'cyan'   => ['#06B6D4', 'rgba(6,182,212,0.12)'],
    ];

    $sampleItems = [
        ['label' => 'Revenue',    'value' => '128K',  'trend' => '+12%',  'trendUp' => true,  'description' => 'this month', 'color' => 'green'],
        ['label' => 'Expenses',   'value' => '84K',   'trend' => '+5%',   'trendUp' => false,  'description' => 'this month', 'color' => 'red'],
        ['label' => 'Employees',  'value' => '342',   'trend' => '+8',    'trendUp' => true,  'description' => 'vs last month', 'color' => 'blue'],
        ['label' => 'Tickets',    'value' => '27',    'trend' => '-4',    'trendUp' => true,  'description' => 'open', 'color' => 'orange'],
    ];

    $rows    = (is_string($items) ? json_decode($items, true) : $items) ?: $sampleItems;
    $colsNum = max(1, min(4, (int)$cols));
    $gridCls = ['1' => 'grid-cols-1', '2' => 'grid-cols-2', '3' => 'grid-cols-3', '4' => 'grid-cols-4'][$colsNum] ?? 'grid-cols-2';
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:shadow-lg dark:border-slate-700/60 dark:bg-slate-900']) }}>

    <div class="absolute inset-x-0 top-0 h-0.75"
         style="background: linear-gradient(to right, {{ $c[0] }}, {{ $c[1] }})"></div>

    @if($title || $subtitle)
        <div class="px-5 pb-2 pt-5">
            @if($subtitle)
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">{{ $subtitle }}</p>
            @endif
            @if($title)
                <h3 class="mt-0.5 text-sm font-bold text-slate-800 dark:text-white">{{ $title }}</h3>
            @endif
        </div>
    @endif

    <div class="grid {{ $gridCls }} divide-x divide-y divide-slate-100 dark:divide-slate-700/60">
        @foreach($rows as $i => $item)
            @php
                $ic   = $item['color'] ?? $color;
                $pal  = $palette[$ic] ?? $palette['violet'];
                $tp   = isset($item['trend']) && ($item['trendUp'] ?? true);
                $tn   = isset($item['trend']) && !($item['trendUp'] ?? true);
            @endphp
            <div class="p-4 {{ ($i === 0 && !($title || $subtitle)) ? 'pt-5' : '' }}">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 truncate">
                    {{ $item['label'] ?? '' }}
                </p>

                <div class="mt-1.5 flex items-baseline gap-1">
                    <span class="text-2xl font-extrabold tabular-nums tracking-tight text-slate-900 dark:text-white">
                        {{ $item['value'] ?? '—' }}
                    </span>
                    @if(!empty($item['unit']))
                        <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">{{ $item['unit'] }}</span>
                    @endif
                </div>

                @if(isset($item['trend']))
                    <div class="mt-1.5 flex items-center gap-1.5 flex-wrap">
                        <span class="inline-flex items-center gap-0.5 rounded-full px-2 py-0.5 text-[10px] font-bold
                            {{ $tp ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400' }}">
                            @if($tp)
                                <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                </svg>
                            @else
                                <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            @endif
                            {{ $item['trend'] }}
                        </span>
                        @if(!empty($item['description']))
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 truncate">{{ $item['description'] }}</span>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>

</div>
