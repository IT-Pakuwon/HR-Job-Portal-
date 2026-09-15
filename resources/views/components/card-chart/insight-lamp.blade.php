@props([
    'text' => '',
    'type' => 'info', // info|positive|warning|critical
    'align' => 'right', // right|left — which side the tooltip drops toward
])

@php
    $colors = [
        'positive' => '#10B981',
        'warning'  => '#F59E0B',
        'critical' => '#EF4444',
        'info'     => '#6366F1',
    ];
    $color = $colors[$type] ?? $colors['info'];
@endphp

@if($text)
    <div class="relative shrink-0" x-data="{ open: false }">
        <button type="button"
            @mouseenter="open = true" @mouseleave="open = false"
            @click="open = !open"
            class="flex h-7 w-7 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200"
            aria-label="Insight">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="{{ $color }}" stroke-width="2" class="h-4.5 w-4.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 18h6m-5 3h4m-7-9a6 6 0 1110 4.243V17a1 1 0 01-1 1H10a1 1 0 01-1-1v-1.757A6 6 0 016 12z" />
            </svg>
        </button>
        <div x-show="open" x-cloak x-transition.opacity.duration.150ms
            @if($align === 'right') style="right:0" @else style="left:0" @endif
            class="absolute top-full z-30 mt-1.5 w-64 rounded-xl border border-slate-200 bg-white p-3 text-xs leading-relaxed text-slate-700 shadow-lg dark:border-slate-700/60 dark:bg-slate-800 dark:text-slate-200">
            {!! $text !!}
        </div>
    </div>
@endif
