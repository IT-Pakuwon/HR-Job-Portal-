@props([
    'title'       => 'Metric',
    'subtitle'    => '',
    'value'       => '0',
    'description' => '',
    'color'       => 'violet',
    'icon'        => null,
    'breakdown'   => [], // [['label' => 'Job Applicant', 'value' => '2,296', 'color' => 'violet'], ...]
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
    $dots = [
        'violet' => '#8B5CF6', 'blue' => '#3B82F6', 'green' => '#10B981',
        'orange' => '#F59E0B', 'red' => '#EF4444', 'pink' => '#EC4899', 'cyan' => '#06B6D4',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:shadow-lg dark:border-slate-700/60 dark:bg-slate-900']) }}>

    <div class="absolute inset-x-0 top-0 h-0.75"
         style="background: linear-gradient(to right, {{ $c[0] }}, {{ $c[1] }})"></div>

    <div class="p-5 pb-4">
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
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                     style="background: {{ $c[2] }}">
                    <span style="color: {{ $c[0] }}; font-size: 1.25rem;">
                        {!! $icon !!}
                    </span>
                </div>
            @endif
        </div>

        @if($description)
            <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">{{ $description }}</p>
        @endif
    </div>

    @if(count($breakdown))
        <div class="grid gap-2 px-5 pb-5" style="grid-template-columns: repeat({{ count($breakdown) }}, minmax(0,1fr));">
            @foreach($breakdown as $item)
                @php $dotColor = $dots[$item['color'] ?? $color] ?? $dots['violet']; @endphp
                <div class="flex items-center gap-2 rounded-lg border border-slate-100 bg-slate-50 px-2.5 py-2 dark:border-slate-700/60 dark:bg-slate-800/50">
                    <span class="h-2 w-2 shrink-0 rounded-full" style="background: {{ $dotColor }}"></span>
                    <span class="min-w-0 flex-1 truncate text-[11px] font-semibold text-slate-500 dark:text-slate-400">{{ $item['label'] }}</span>
                    <span class="shrink-0 text-xs font-extrabold tabular-nums text-slate-900 dark:text-white">{{ $item['value'] }}</span>
                </div>
            @endforeach
        </div>
    @endif

</div>
