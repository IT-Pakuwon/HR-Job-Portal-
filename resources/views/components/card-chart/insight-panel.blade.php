@props([
    'listId' => null,
    'items' => [], // static server-rendered mode: [['type' => 'info|positive|warning|critical', 'text' => '<b>html</b> allowed'], ...]
    'collapsible' => false,
])

@php
    $icons = [
        'positive' => ['color' => '#10B981', 'path' => 'M5 13l4 4L19 7'],
        'warning'  => ['color' => '#F59E0B', 'path' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
        'critical' => ['color' => '#EF4444', 'path' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        'info'     => ['color' => '#6366F1', 'path' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm dark:border-slate-700/60 dark:bg-slate-900']) }}
    @if($collapsible) x-data="{ open: true }" @endif>

    <div class="mb-1.5 flex items-center justify-between {{ $collapsible ? 'cursor-pointer' : '' }}"
        @if($collapsible) @click="open = !open" @endif>
        <div class="flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0c-.943.945-1.657 1.657-1.657 3.657h-3.758c0-2-.714-2.712-1.657-3.657z" />
            </svg>
            <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Summary Insight</span>
        </div>
        <div class="flex items-center gap-2">
            @if($listId)
                <span id="{{ $listId }}-count" class="text-[10.5px] font-semibold tabular-nums text-slate-400 dark:text-slate-500"></span>
            @elseif(count($items))
                <span class="text-[10.5px] font-semibold tabular-nums text-slate-400 dark:text-slate-500">{{ count($items) }}</span>
            @endif
            @if($collapsible)
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-indigo-400 transition-transform" :class="open ? '' : '-rotate-90'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            @endif
        </div>
    </div>

    <ul @if($collapsible) x-show="open" x-collapse @endif
        @if($listId) id="{{ $listId }}" @endif class="grid grid-cols-1 gap-x-6 gap-y-1 sm:grid-cols-2 xl:grid-cols-3">
        @if(count($items))
            @foreach($items as $item)
                @php $ic = $icons[$item['type'] ?? 'info'] ?? $icons['info']; @endphp
                <li class="flex items-start gap-1.5 py-0.5 text-xs leading-relaxed text-slate-700 dark:text-slate-200">
                    <svg class="mt-0.5 h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="{{ $ic['color'] }}" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ic['path'] }}"/>
                    </svg>
                    <span>{!! $item['text'] !!}</span>
                </li>
            @endforeach
        @else
            <li class="col-span-full text-xs text-slate-400 dark:text-slate-500">Loading insights…</li>
        @endif
    </ul>
</div>
