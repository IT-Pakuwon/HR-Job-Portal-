@props([
    'leftLabel'       => 'Total',
    'leftValueId'     => '',
    'leftValue'       => '—',
    'leftDescription' => '',
    'rightLabel'      => 'Remaining',
    'rightValueId'    => '',
    'rightValue'      => '—',
    'rightBadgeId'    => '',
    'barLabel'        => 'Utilization',
    'barPctId'        => '',
    'barPct'          => '—',
    'barId'           => '',
    'color'           => 'green',
])

@php
    $hex = [
        'violet' => ['#8B5CF6', '#7C3AED'],
        'blue'   => ['#3B82F6', '#06B6D4'],
        'green'  => ['#10B981', '#8B5CF6'],
        'orange' => ['#F59E0B', '#D97706'],
        'red'    => ['#EF4444', '#F43F5E'],
        'pink'   => ['#EC4899', '#C026D3'],
        'cyan'   => ['#06B6D4', '#3B82F6'],
    ];
    $c = $hex[$color] ?? $hex['green'];
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition duration-200 hover:shadow-lg dark:border-slate-700/60 dark:bg-slate-900']) }}>

    {{-- Gradient accent top bar --}}
    <div class="absolute inset-x-0 top-0 h-0.75"
         style="background: linear-gradient(to right, {{ $c[0] }}, {{ $c[1] }})"></div>

    {{-- Left + Right metrics --}}
    <div class="flex items-start justify-between gap-4">

        {{-- Left metric --}}
        <div class="min-w-0">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                {{ $leftLabel }}
            </p>
            <p @if($leftValueId) id="{{ $leftValueId }}" @endif
               class="mt-1 text-lg font-extrabold tabular-nums text-slate-900 sm:text-xl lg:text-2xl dark:text-white">{{ $leftValue }}</p>
            @if($leftDescription)
                <p class="mt-0.5 text-[10px] text-slate-400 dark:text-slate-500">{{ $leftDescription }}</p>
            @endif
        </div>

        {{-- Divider --}}
        <div class="h-10 w-px self-center bg-slate-100 dark:bg-slate-700/60"></div>

        {{-- Right metric --}}
        <div class="min-w-0 text-right">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                {{ $rightLabel }}
            </p>
            <p @if($rightValueId) id="{{ $rightValueId }}" @endif
               class="mt-1 text-lg font-extrabold tabular-nums text-slate-900 sm:text-xl lg:text-2xl dark:text-white">{{ $rightValue }}</p>
            @if($rightBadgeId)
                <span id="{{ $rightBadgeId }}"
                      class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold">—</span>
            @endif
        </div>

    </div>

    {{-- Utilization bar footer --}}
    @if($barId || $barPctId)
        <div class="mt-3 border-t border-slate-100 pt-2.5 dark:border-slate-700/60">
            <div class="mb-1.5 flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    {{ $barLabel }}
                </span>
                @if($barPctId)
                    <span id="{{ $barPctId }}"
                          class="text-xs font-extrabold text-slate-700 dark:text-slate-200">{{ $barPct }}</span>
                @else
                    <span class="text-xs font-extrabold text-slate-700 dark:text-slate-200">{{ $barPct }}</span>
                @endif
            </div>
            <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700/60">
                <div @if($barId) id="{{ $barId }}" @endif
                     class="h-full rounded-full transition-all duration-700 ease-out"
                     style="width: 0%; background: linear-gradient(to right, {{ $c[0] }}, {{ $c[1] }})"></div>
            </div>
        </div>
    @endif

</div>
